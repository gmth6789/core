<?php declare(strict_types=1);

namespace Shopware\Core\Framework\ORM\Search\Term;

use Shopware\Core\Framework\ORM\EntityDefinition;
use Shopware\Core\Framework\ORM\Field\AssociationInterface;
use Shopware\Core\Framework\ORM\Field\Field;
use Shopware\Core\Framework\ORM\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\ORM\Field\StringField;
use Shopware\Core\Framework\ORM\Field\TranslatedField;
use Shopware\Core\Framework\ORM\FieldCollection;
use Shopware\Core\Framework\ORM\Search\Query\MatchQuery;
use Shopware\Core\Framework\ORM\Search\Query\ScoreQuery;
use Shopware\Core\Framework\ORM\Search\Query\TermQuery;
use Shopware\Core\Framework\ORM\Write\Flag\SearchRanking;

class EntityScoreQueryBuilder
{
    /**
     * @param SearchPattern $term
     * @param string        $definition
     * @param string        $root
     * @param float         $multiplier
     *
     * @return ScoreQuery[]
     */
    public function buildScoreQueries(SearchPattern $term, string $definition, string $root, float $multiplier = 1): array
    {
        static $counter = 0;
        ++$counter;

        $fields = $this->getQueryFields($definition);

        $queries = [];
        /** @var Field $field */
        foreach ($fields->getElements() as $field) {
            /** @var SearchRanking|null $flag */
            $flag = $field->getFlag(SearchRanking::class);

            $ranking = 1 * $multiplier;
            if ($flag) {
                $ranking = $flag->getRanking() * $multiplier;
            }

            /** @var SearchRanking $flag */
            $select = $root . '.' . $field->getPropertyName();

            if ($field instanceof ManyToManyAssociationField) {
                $queries = array_merge(
                    $queries,
                    $this->buildScoreQueries($term, $field->getReferenceDefinition(), $select, $ranking)
                );
                continue;
            }

            if ($field instanceof AssociationInterface) {
                $queries = array_merge(
                    $queries,
                    $this->buildScoreQueries($term, $field->getReferenceClass(), $select, $ranking)
                );
                continue;
            }

            $queries[] = new ScoreQuery(
                new TermQuery($select, $term->getOriginal()->getTerm()),
                $ranking * $term->getOriginal()->getScore()
            );

            $queries[] = new ScoreQuery(
                new MatchQuery($select, $term->getOriginal()->getTerm()),
                $ranking * $term->getOriginal()->getScore() * 0.5
            );

            foreach ($term->getTerms() as $part) {
                $queries[] = new ScoreQuery(
                    new TermQuery($select, $part->getTerm()),
                    $ranking * $part->getScore()
                );

                $queries[] = new ScoreQuery(
                    new MatchQuery($select, $part->getTerm()),
                    $ranking * $part->getScore() * 0.5
                );
            }
        }

        return $queries;
    }

    private function getQueryFields(string $definition): FieldCollection
    {
        /** @var EntityDefinition $definition */
        $fields = $definition::getFields()->filterByFlag(SearchRanking::class);

        if ($fields->count() > 0) {
            return $fields;
        }

        $fields = $definition::getFields()->filterInstance(TranslatedField::class);
        if ($fields->count() > 0) {
            return $fields;
        }

        return $definition::getFields()->filterInstance(StringField::class);
    }
}
