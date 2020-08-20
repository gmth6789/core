<?php declare(strict_types=1);

namespace Shopware\Core\System\Snippet\Files;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\App\ActiveAppsLoader;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use function Flag\next10286;

/**
 * @Decoratable
 */
class SnippetFileLoader implements SnippetFileLoaderInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $pluginAuthors;

    /**
     * @var AppSnippetFileLoader|null
     */
    private $appSnippetFileLoader;

    /**
     * @var ActiveAppsLoader|null
     */
    private $activeAppsLoader;

    public function __construct(
        KernelInterface $kernel,
        Connection $connection,
        ?AppSnippetFileLoader $appSnippetFileLoader,
        ?ActiveAppsLoader $activeAppsLoader
    ) {
        $this->kernel = $kernel;
        // use Connection directly as this gets executed so early on kernel boot
        // using the DAL would result in CircularReferences
        $this->connection = $connection;
        $this->appSnippetFileLoader = $appSnippetFileLoader;
        $this->activeAppsLoader = $activeAppsLoader;
    }

    public function loadSnippetFilesIntoCollection(SnippetFileCollection $snippetFileCollection): void
    {
        $this->loadPluginSnippets($snippetFileCollection);
        // remove nullable prop and on-invalid=null behaviour in service declaration
        // when removing the feature flag
        if (!$this->appSnippetFileLoader || !$this->activeAppsLoader || !next10286()) {
            return;
        }

        $this->loadAppSnippets($snippetFileCollection);
    }

    private function loadPluginSnippets(SnippetFileCollection $snippetFileCollection): void
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!$bundle instanceof Bundle) {
                continue;
            }

            $snippetDir = $bundle->getPath() . '/Resources/snippet';

            if (!is_dir($snippetDir)) {
                continue;
            }

            foreach ($this->loadSnippetFilesInDir($snippetDir, $bundle) as $snippetFile) {
                if ($snippetFileCollection->hasFileForPath($snippetFile->getPath())) {
                    continue;
                }

                $snippetFileCollection->add($snippetFile);
            }
        }
    }

    private function loadAppSnippets(SnippetFileCollection $snippetFileCollection): void
    {
        foreach ($this->activeAppsLoader->getActiveApps() as $app) {
            $snippetFiles = $this->appSnippetFileLoader->loadSnippetFilesFromApp($app['author'] ?? '', $app['path']);
            foreach ($snippetFiles as $snippetFile) {
                $snippetFileCollection->add($snippetFile);
            }
        }
    }

    /**
     * @return SnippetFileInterface[]
     */
    private function loadSnippetFilesInDir(string $snippetDir, Bundle $bundle): array
    {
        $finder = new Finder();
        $finder->in($snippetDir)
            ->files()
            ->name('*.json');

        $snippetFiles = [];

        foreach ($finder->getIterator() as $fileInfo) {
            $nameParts = explode('.', $fileInfo->getFilenameWithoutExtension());

            $snippetFile = null;
            switch (count($nameParts)) {
                case 2:
                    $snippetFile = new GenericSnippetFile(
                        implode('.', $nameParts),
                        $fileInfo->getPathname(),
                        $nameParts[1],
                        $this->getAuthorFromBundle($bundle),
                        false
                    );

                    break;
                case 3:
                    $snippetFile = new GenericSnippetFile(
                        implode('.', [$nameParts[0], $nameParts[1]]),
                        $fileInfo->getPathname(),
                        $nameParts[1],
                        $this->getAuthorFromBundle($bundle),
                        $nameParts[2] === 'base'
                    );

                    break;
            }

            if ($snippetFile) {
                $snippetFiles[] = $snippetFile;
            }
        }

        return $snippetFiles;
    }

    private function getAuthorFromBundle(Bundle $bundle): string
    {
        if (!$bundle instanceof Plugin) {
            return 'Shopware';
        }

        return $this->getPluginAuthors()[get_class($bundle)] ?? '';
    }

    private function getPluginAuthors(): array
    {
        if (!$this->pluginAuthors) {
            $authors = $this->connection->fetchAll('
            SELECT `base_class` AS `baseClass`, `author`
            FROM `plugin`
        ');

            $this->pluginAuthors = FetchModeHelper::keyPair($authors);
        }

        return $this->pluginAuthors;
    }
}
