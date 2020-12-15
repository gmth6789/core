<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Store\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\App\AppCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Store\Exception\ExtensionInstallException;
use Shopware\Core\Framework\Store\Exception\ExtensionNotFoundException;
use Shopware\Core\Framework\Store\Exception\ExtensionThemeStillInUseException;
use Shopware\Core\Framework\Store\Services\ExtensionLifecycleService;
use Shopware\Core\Framework\Test\Store\ExtensionBehaviour;
use Shopware\Core\Framework\Test\Store\StoreClientBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class ExtensionLifecycleServiceTest extends TestCase
{
    use IntegrationTestBehaviour;
    use ExtensionBehaviour;
    use StoreClientBehaviour;

    /**
     * @var ExtensionLifecycleService
     */
    private $lifecycleService;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $pluginRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $themeRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var Context
     */
    private $context;

    public function setUp(): void
    {
        Feature::skipTestIfInActive('FEATURE_NEXT_12608', $this);
        $this->lifecycleService = $this->getContainer()->get(ExtensionLifecycleService::class);

        $this->appRepository = $this->getContainer()->get('app.repository');
        $this->pluginRepository = $this->getContainer()->get('plugin.repository');
        $this->themeRepository = $this->getContainer()->get('theme.repository');
        $this->salesChannelRepository = $this->getContainer()->get('sales_channel.repository');

        $userId = Uuid::randomHex();
        $storeToken = Uuid::randomHex();

        $data = [
            [
                'id' => $userId,
                'localeId' => $this->getLocaleIdOfSystemLanguage(),
                'username' => 'foobar',
                'password' => 'asdasdasdasd',
                'firstName' => 'Foo',
                'lastName' => 'Bar',
                'email' => 'foo@bar.com',
                'storeToken' => $storeToken,
            ],
        ];
        $this->getContainer()->get('user.repository')->create($data, Context::createDefaultContext());
        $source = new AdminApiSource($userId);
        $source->setIsAdmin(true);
        $this->context = Context::createDefaultContext($source);
    }

    public function tearDown(): void
    {
        $this->removeApp(__DIR__ . '/../_fixtures/TestApp');
        $this->removeApp(__DIR__ . '/../_fixtures/TestAppTheme');
        $this->removePlugin(__DIR__ . '/../_fixtures/AppStoreTestPlugin');
    }

    public function testInstallExtension(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestApp', false);

        $this->lifecycleService->install('app', 'TestApp', $this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('TestApp', $apps->first()->getName());
        static::assertTrue($apps->first()->isActive());
    }

    public function testUninstallWithInvalidName(): void
    {
        $this->lifecycleService->uninstall('app', 'notExisting', false, $this->context);
    }

    public function testInstallAppNotExisting(): void
    {
        static::expectException(ExtensionInstallException::class);
        $this->lifecycleService->install('app', 'notExisting', $this->context);
    }

    public function testRemoveExtension(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestApp');

        $this->lifecycleService->uninstall('app', 'TestApp', false, $this->context);
        $this->lifecycleService->remove('app', 'TestApp', $this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(0, $apps);
    }

    public function testActivateExtension(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestApp');

        $this->lifecycleService->activate('app', 'TestApp', $this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('TestApp', $apps->first()->getName());
        static::assertTrue($apps->first()->isActive());
    }

    public function testDeactivateExtension(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestApp');

        $this->lifecycleService->activate('app', 'TestApp', $this->context);
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertTrue($apps->first()->isActive());

        $this->lifecycleService->deactivate('app', 'TestApp', $this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('TestApp', $apps->first()->getName());
        static::assertFalse($apps->first()->isActive());
    }

    public function testUpdateExtensionNotExisting(): void
    {
        static::expectException(ExtensionInstallException::class);
        $this->lifecycleService->update('app', 'foo', $this->context);
    }

    public function testUpdateExtensionNotInstalled(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestApp', false);
        static::expectException(ExtensionNotFoundException::class);
        $this->lifecycleService->update('app', 'TestApp', $this->context);
    }

    public function testUpdateExtension(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestApp');

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertSame('1.0.0', $apps->first()->getVersion());

        $appManifestPath = $this->getContainer()->getParameter('kernel.app_dir') . '/TestApp/manifest.xml';
        file_put_contents($appManifestPath, str_replace('1.0.0', '1.0.1', file_get_contents($appManifestPath)));

        $this->lifecycleService->update('app', 'TestApp', $this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertSame('1.0.1', $apps->first()->getVersion());
    }

    public function testExtensionCantBeRemovedIfAThemeIsAssigned(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestAppTheme');

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        $theme = $this->themeRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', 'TestAppTheme')),
            $this->context
        )->first();

        $defaultSalesChannelId = $this->salesChannelRepository->searchIds(new Criteria(), $this->context)
            ->firstId();

        $this->salesChannelRepository->update([[
            'id' => $defaultSalesChannelId,
            'themes' => [
                ['id' => $theme->getId()],
            ],
        ]], $this->context);

        static::expectException(ExtensionThemeStillInUseException::class);
        $this->lifecycleService->uninstall(
            'app',
            $apps->first()->getName(),
            false,
            $this->context
        );
    }

    public function testExtensionCantBeRemovedIfAChildThemeIsAssigned(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestAppTheme');

        $theme = $this->themeRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('technicalName', 'TestAppTheme')),
            $this->context
        )->first();

        $childThemeId = Uuid::randomHex();
        $this->themeRepository->create([[
            'id' => $childThemeId,
            'name' => 'SwagTest',
            'author' => 'Shopware',
            'active' => true,
            'parentThemeId' => $theme->getId(),
        ]], $this->context);

        $defaultSalesChannelId = $this->salesChannelRepository->searchIds(new Criteria(), $this->context)
            ->firstId();

        $this->salesChannelRepository->update([[
            'id' => $defaultSalesChannelId,
            'themes' => [
                ['id' => $childThemeId],
            ],
        ]], $this->context);

        static::expectException(ExtensionThemeStillInUseException::class);
        $this->lifecycleService->uninstall(
            'app',
            'TestAppTheme',
            false,
            $this->context
        );
    }

    public function testExtensionCanBeRemovedIfThemeIsNotAssigned(): void
    {
        $this->installApp(__DIR__ . '/../_fixtures/TestAppTheme');

        $themeCriteria = new Criteria();
        $themeCriteria->addFilter(new EqualsFilter('technicalName', 'TestAppTheme'))
            ->addAssociation('salesChannels');

        $theme = $this->themeRepository->search($themeCriteria, $this->context)->first();

        static::assertEquals(0, $theme->getSalesChannels()->count());

        $this->lifecycleService->uninstall(
            'type',
            'TestAppTheme',
            false,
            $this->context
        );

        $removedApp = $this->appRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'TestAppTheme')),
            $this->context
        )->first();

        static::assertNull($removedApp);
    }
}
