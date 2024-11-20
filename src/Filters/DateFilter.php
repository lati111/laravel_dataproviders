<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * @class Filter by a standard date
 */
class DateFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    protected string $type = 'date';

    /** {@inheritdoc} */
    protected function addWhereStatement(Builder $builder, string $column, string $operator, mixed $value): Builder {
        return $builder->whereDate($column, $operator, $value);
    }

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => '=', 'text' => 'is'],
            ['operator' => '!=', 'text' => 'is not'],
            ['operator' => '>', 'text' => 'is after'],
            ['operator' => '>=', 'text' => 'is after or on'],
            ['operator' => '<=', 'text' => 'is before or on'],
            ['operator' => '<', 'text' => 'is before'],
        ];
    }

    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [
            'min' => $this->getBasicOptionQuery()->min($this->column),
            'max' => $this->getBasicOptionQuery()->max($this->column)
        ];
    }
}
