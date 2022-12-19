<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult;

use Shopware\Core\Framework\Struct\Struct;

/**
 * @internal
 *
 * @package core
 */
abstract class AggregationResult extends Struct
{
    public function __construct(protected string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getApiAlias(): string
    {
        return $this->name . '_aggregation';
    }
}
