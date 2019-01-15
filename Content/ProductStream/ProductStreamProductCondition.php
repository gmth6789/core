<?php declare(strict_types=1);

namespace Shopware\Core\Content\ProductStream;

use Shopware\Core\Framework\ProductStream\ProductStream;
use Shopware\Core\Framework\Validation\Constraint\ArrayOfUuid;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductStreamProductCondition extends ProductStream
{
    /**
     * @var string[]
     */
    protected $identifiers;

    /**
     * @var string
     */
    protected $operator;

    public function __construct()
    {
        $this->operator = self::OPERATOR_EQ;
    }

//    public function match(ProductStreamScope $scope): Match
//    {
//        if (!$scope instanceof LineItemScope) {
//            return new Match(
//                false,
//                ['Invalid Match Context. CartRuleScope expected']
//            );
//        }
//
//        switch ($this->operator) {
//            case self::OPERATOR_EQ:
//                return new Match(
//                    \in_array($scope->getLineItem()->getKey(), $this->identifiers, true),
//                    ['Line item not in cart']
//                );
//            case self::OPERATOR_NEQ:
//                return new Match(
//                    !\in_array($scope->getLineItem()->getKey(), $this->identifiers, true),
//                    ['Line item in cart']
//                );
//            default:
//                throw new UnsupportedOperatorException($this->operator, __CLASS__);
//        }
//    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function getConstraints(): array
    {
        return [
            'identifiers' => [new NotBlank(), new ArrayOfUuid()],
            'operator' => [new Choice([self::OPERATOR_EQ, self::OPERATOR_NEQ])],
        ];
    }

    public function getName(): string
    {
        return 'cartLineItem';
    }
}
