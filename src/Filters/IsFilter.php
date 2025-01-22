<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class Filter by a standard string
 */
class IsFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    public string $type = 'bool';

    /** @var mixed The value to filter on */
    protected mixed $value;

    /**
     * Apply the filter to a query
     * @param Model $model The model that served as the base of the options
     * @param string|CustomColumn $column The column that the options come from. Can be a complex sql query such as a subquery or concat using a CustomColumn
     * @param mixed $value The value to filter on
     * @param ForeignTable|string|null $columnTable Which table the column is in if it's in a different table. By default uses $model
     * @return void
     */
    public function __construct(Model $model, string|CustomColumn $column, $value, ForeignTable|string|null $columnTable = null) {
        $this->model = $model;
        $this->column = $column;
        $this->value = $value;
        $this->columnTable = $columnTable;
    }

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => '=', 'text' => 'is'],
            ['operator' => '!=', 'text' => 'is not'],
        ];
    }
    /** {@inheritdoc} */
    public function handle(Builder $builder, string $operator, mixed $value = null): Builder {
        return parent::handle($builder, $operator, $this->value);
    }


    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [$this->value];
    }
}
