<?php declare(strict_types=1);

namespace Shopware\Core\System\CustomEntity\Xml\Config\AdminUi\XmlElements;

use Shopware\Core\System\CustomEntity\Xml\Config\CustomEntityFlag;

/**
 * Represents the XML listing element
 *
 * admin-ui > entity > listing
 *
 * @package content
 *
 * @internal
 */
class Listing extends CustomEntityFlag
{
    private const MAPPING = [
        'columns' => Columns::class,
    ];

    protected Columns $columns;

    public static function fromXml(\DOMElement $element): CustomEntityFlag
    {
        $self = new self();
        $self->assign($self->parse($element));

        return $self;
    }

    public function getColumns(): Columns
    {
        return $this->columns;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    protected function parseChild(\DOMElement $child, array $values): array
    {
        /** @var Columns|null $class */
        $class = self::MAPPING[$child->tagName] ?? null;

        if (!$class) {
            throw new \RuntimeException(\sprintf('Flag type "%s" not found', $child->tagName));
        }

        $values[$child->tagName] = $class::fromXml($child);

        return $values;
    }
}
