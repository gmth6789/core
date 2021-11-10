<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Script\Execution;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Script\Execution\Hook;

class TestHook extends Hook
{
    private string $name;

    private array $serviceIds;

    public function __construct(string $name, Context $salesChannelContext, array $data, array $serviceIds = [])
    {
        parent::__construct($salesChannelContext);
        $this->name = $name;
        $this->serviceIds = $serviceIds;

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getServiceIds(): array
    {
        return $this->serviceIds;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
