<?php declare(strict_types=1);

namespace Shopware\Api\Order\Event\OrderDelivery;

use Shopware\Api\Order\Struct\OrderDeliverySearchResult;
use Shopware\Context\Struct\ApplicationContext;
use Shopware\Framework\Event\NestedEvent;

class OrderDeliverySearchResultLoadedEvent extends NestedEvent
{
    public const NAME = 'order_delivery.search.result.loaded';

    /**
     * @var OrderDeliverySearchResult
     */
    protected $result;

    public function __construct(OrderDeliverySearchResult $result)
    {
        $this->result = $result;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): ApplicationContext
    {
        return $this->result->getContext();
    }
}
