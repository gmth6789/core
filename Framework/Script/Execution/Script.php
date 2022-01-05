<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Script\Execution;

use Shopware\Core\Framework\Struct\Struct;

/**
 * @internal only for use by the app-system
 */
class Script extends Struct
{
    protected string $name;

    protected string $script;

    protected array $twigOptions;

    protected array $includes = [];

    protected \DateTimeInterface $lastModified;

    private ?string $appId;

    private ?string $appName;

    public function __construct(string $name, string $script, \DateTimeInterface $lastModified, ?string $appId, ?string $appName, array $twigOptions = [], array $includes = [])
    {
        $this->name = $name;
        $this->script = $script;
        $this->twigOptions = $twigOptions;
        $this->lastModified = $lastModified;
        $this->includes = $includes;
        $this->appId = $appId;
        $this->appName = $appName;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScript(): string
    {
        return $this->script;
    }

    public function getTwigOptions(): array
    {
        return $this->twigOptions;
    }

    public function getLastModified(): \DateTimeInterface
    {
        return $this->lastModified;
    }

    /**
     * @return Script[]
     */
    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function getAppName(): ?string
    {
        return $this->appName;
    }
}
