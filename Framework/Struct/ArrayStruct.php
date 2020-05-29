<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Struct;

class ArrayStruct extends Struct implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string|null
     */
    protected $apiAlias;

    public function __construct(array $data = [], ?string $apiAlias = null)
    {
        $this->data = $data;
        $this->apiAlias = $apiAlias;
    }

    public function has(string $property): bool
    {
        return \array_key_exists($property, $this->data);
    }

    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function get(string $key)
    {
        return $this->offsetGet($key);
    }

    public function set($key, $value)
    {
        return $this->data[$key] = $value;
    }

    public function assign(array $options)
    {
        $this->data = array_replace_recursive($this->data, $options);

        return $this;
    }

    public function all()
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset($data['data']);

        foreach ($this->data as $property => $value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format(\DateTime::ATOM);
            }

            $data[$property] = $value;
        }

        return $data;
    }

    public function getApiAlias(): string
    {
        return $this->apiAlias ?? 'array_struct';
    }
}
