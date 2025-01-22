<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lati111\LaravelDataproviders\Exceptions\DataproviderException;

/**
 * @class Filter by a standard boolean
 */
class IsSoftDeletedFilter extends AbstractFilter
{
    /** Declare that only active items should be shown */
    public const ONLY_ACTIVE_ENTRIES_OPERATOR = 'only_active';

    /** Declare that only inactive items should be shown */
    public const ONLY_INACTIVE_ENTRIES_OPERATOR = 'only_inactive';

    /** Declare that all items should be shown */
    public const ALL_ENTRIES_OPERATOR = 'both';

    /** {@inheritdoc} */
    public string $type = 'soft_delete';

    /**
     * {@inheritdoc}
     * @param string $operatorType which set of operators that should be used for this filter
     */
    public function __construct(Model $model, string|CustomColumn $column, ?ForeignTable $columnTable = null) {
        parent::__construct($model, $column, $columnTable);
    }

    /** {@inheritdoc} */
    public function handle(Builder $builder, string $operator, mixed $value): Builder
    {
        if ($this->validateOperator($operator) === false) {
            throw new DataproviderException(sprintf('Operator %s does not exist on filter %s', $operator, self::class));
        }

        if ($operator === self::ONLY_ACTIVE_ENTRIES_OPERATOR) {
            return $builder;
        }

        $builder->withTrashed();
        if ($operator === self::ONLY_INACTIVE_ENTRIES_OPERATOR) {
            $builder->where($this->column, '!=', null);
        }

        return $builder;
    }

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => self::ONLY_ACTIVE_ENTRIES_OPERATOR, 'text' => 'toon alleen actief'],
            ['operator' => self::ONLY_INACTIVE_ENTRIES_OPERATOR, 'text' => 'toon alleen inactief'],
            ['operator' => self::ALL_ENTRIES_OPERATOR, 'text' => 'toon beide'],
        ];
    }

    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [];
    }
}
