<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\Aggregate\ProductContextPrice;

use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductContextPrice\Collection\ProductContextPriceBasicCollection;
use Shopware\Core\Content\Product\Aggregate\ProductContextPrice\Collection\ProductContextPriceDetailCollection;
use Shopware\Core\Content\Product\Aggregate\ProductContextPrice\Event\ProductContextPriceDeletedEvent;
use Shopware\Core\Content\Product\Aggregate\ProductContextPrice\Event\ProductContextPriceWrittenEvent;
use Shopware\Core\Content\Product\Aggregate\ProductContextPrice\Struct\ProductContextPriceBasicStruct;
use Shopware\Core\Content\Product\Aggregate\ProductContextPrice\Struct\ProductContextPriceDetailStruct;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\ORM\EntityDefinition;
use Shopware\Core\Framework\ORM\EntityExtensionInterface;
use Shopware\Core\Framework\ORM\Field\DateField;
use Shopware\Core\Framework\ORM\Field\FkField;
use Shopware\Core\Framework\ORM\Field\IdField;
use Shopware\Core\Framework\ORM\Field\IntField;
use Shopware\Core\Framework\ORM\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\ORM\Field\PriceField;
use Shopware\Core\Framework\ORM\Field\ReferenceVersionField;
use Shopware\Core\Framework\ORM\Field\TenantIdField;
use Shopware\Core\Framework\ORM\Field\VersionField;
use Shopware\Core\Framework\ORM\FieldCollection;
use Shopware\Core\Framework\ORM\Write\Flag\PrimaryKey;
use Shopware\Core\Framework\ORM\Write\Flag\Required;
use Shopware\Core\System\Currency\CurrencyDefinition;

class ProductContextPriceDefinition extends EntityDefinition
{
    /**
     * @var FieldCollection
     */
    protected static $primaryKeys;

    /**
     * @var FieldCollection
     */
    protected static $fields;

    /**
     * @var EntityExtensionInterface[]
     */
    protected static $extensions = [];

    public static function getEntityName(): string
    {
        return 'product_context_price';
    }

    public static function getFields(): FieldCollection
    {
        if (self::$fields) {
            return self::$fields;
        }

        self::$fields = new FieldCollection([
            new TenantIdField(),
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            (new FkField('product_id', 'productId', ProductDefinition::class))->setFlags(new Required()),
            new ReferenceVersionField(ProductDefinition::class),
            (new FkField('currency_id', 'currencyId', CurrencyDefinition::class))->setFlags(new Required()),
            new ReferenceVersionField(CurrencyDefinition::class),
            (new FkField('rule_id', 'ruleId', RuleDefinition::class))->setFlags(new Required()),
            (new PriceField('price', 'price'))->setFlags(new Required()),
            (new IntField('quantity_start', 'quantityStart'))->setFlags(new Required()),
            new IntField('quantity_end', 'quantityEnd'),
            (new DateField('created_at', 'createdAt'))->setFlags(new Required()),
            new DateField('updated_at', 'updatedAt'),
            new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, false, 'context_price_join_id'),
            new ManyToOneAssociationField('currency', 'currency_id', CurrencyDefinition::class, false),
            new ManyToOneAssociationField('rule', 'rule_id', RuleDefinition::class, false),
        ]);

        foreach (self::$extensions as $extension) {
            $extension->extendFields(self::$fields);
        }

        return self::$fields;
    }

    public static function getRepositoryClass(): string
    {
        return ProductContextPriceRepository::class;
    }

    public static function getBasicCollectionClass(): string
    {
        return ProductContextPriceBasicCollection::class;
    }

    public static function getDeletedEventClass(): string
    {
        return ProductContextPriceDeletedEvent::class;
    }

    public static function getWrittenEventClass(): string
    {
        return ProductContextPriceWrittenEvent::class;
    }

    public static function getBasicStructClass(): string
    {
        return ProductContextPriceBasicStruct::class;
    }

    public static function getTranslationDefinitionClass(): ?string
    {
        return null;
    }

    public static function getDetailStructClass(): string
    {
        return ProductContextPriceDetailStruct::class;
    }

    public static function getDetailCollectionClass(): string
    {
        return ProductContextPriceDetailCollection::class;
    }
}
