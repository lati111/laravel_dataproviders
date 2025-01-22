<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lati111\LaravelDataproviders\Exceptions\DataproviderException;
use Lati111\LaravelDataproviders\Filters\Conditions\FilterConditionInterface;
use Lati111\LaravelDataproviders\Interfaces\DataproviderFilterInterface;

/**
 * @class The base class for every filter
 */

abstract class AbstractFilter implements DataproviderFilterInterface
{
    /** @var string $type The string representation of this filter type */
    public string $type;

    /** @var Model $model The model that serves as the base for this operation */
    protected Model $model;

    /** @var ForeignTable|string|null $columnTable The table where the column is located in, if it's not the default model. By default uses $model's table instead */
    protected ForeignTable|string|null $columnTable;

    /** @var string|CustomColumn $column The column where the filter options come from. Use a CustomColumn if you need a complex column in the form of an sql statement */
    protected string|CustomColumn $column;

    /** @var ForeignTable[] $links The tables linked to this one through join statements for options */
    protected array $links = [];

    /** @var FilterConditionInterface[] $conditions The conditions to be applied the this filter */
    protected array $conditions = [];

    /** @var bool $isOrWhere If the where statement added to the query should be an or where */
    public bool $isOrWhere = false;

    /**
     * Apply the filter to a query
     * @param Model $model The model that served as the base of the options
     * @param string|CustomColumn $column The column that the options come from. Can be a complex sql query such as a subquery or concat using a CustomColumn
     * @param ForeignTable|string|null $columnTable Which table the column is in if it's in a different table. By default uses $model
     * @return void
     */
    public function __construct(Model $model, string|CustomColumn $column, ForeignTable|string|null $columnTable = null) {
        $this->model = $model;
        $this->column = $column;
        $this->columnTable = $columnTable;
    }

    /**
     * Apply the filter to a query
     * @param Builder $builder The query to be modified
     * @param string $operator The operator used in the filter
     * @param mixed $value The value to filter on
     * @return Builder The modified query
     * @throws DataproviderException When operator does not exist
     */
    public function handle(Builder $builder, string $operator, mixed $value): Builder
    {
        if ($this->validateOperator($operator) === false) {
            throw new DataproviderException(sprintf('Operator %s does not exist on filter %s', $operator, self::class));
        }

        $links = $this->links;
        if ($this->columnTable instanceOf ForeignTable) {
            $links[] = $this->columnTable;
        }

        //add in linked tables missing from main query
        foreach ($links as $link) {
            $exists = false;
            foreach (($builder->getQuery()->joins ?? []) as $join) {
                if ($exists) {
                    continue;
                }

                if (
                    (
                        $join->wheres[0]['first'] === sprintf('%s.%s', $link->localTable, $link->localKey) &&
                        $join->wheres[0]['second'] === sprintf('%s.%s', $link->foreignTable, $link->foreignKey)
                    ) || (
                        $join->wheres[0]['second'] === sprintf('%s.%s', $link->localTable, $link->localKey) &&
                        $join->wheres[0]['first'] === sprintf('%s.%s', $link->foreignTable, $link->foreignKey)
                    )
                ) {
                    $exists = true;
                }
            }

            if ($exists === false) {
                $builder = $link->linkForeignTable($builder);
            }
        }

        if ($this->column instanceof CustomColumn) {
            return $this->column->applyWhere($builder, $operator, $value);
        }

        $column = $this->column;
        if (is_string($this->column) && is_string($this->columnTable)) {
            $column = sprintf('%s.%s', $this->columnTable, $this->column);
        }

        if ($this->columnTable instanceof ForeignTable && is_string($this->column) === true) {
            $column = sprintf('%s.%s', $this->columnTable->getForeignTableName(), $this->column);
        }

        return $this->addWhereStatement($builder, $column, $operator, $value);
    }

    /**
     * Add the where statement to the given query
     * @param Builder $builder The query
     * @return Builder The modified query
     */
    protected function addWhereStatement(Builder $builder, string $column, string $operator, mixed $value): Builder {
        if ($this->isOrWhere === false) {
            $builder->where($column, $operator, $value);
        } else {
            $builder->orWhere($column, $operator, $value);
        }

        return $builder;
    }

    /**
     * Attempts to see if the specific operator is valid
     * @param string $operator The operator used in the filter
     * @return boolean
     */
    protected function validateOperator(string $operator): bool
    {
        $operators = $this->getOperators();
        foreach ($operators as $operatorData) {
            if ($operatorData['operator'] === $operator) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the info data for this filter
     * @return array{option:string, operators:array<string>, options:array<string>}
     */
    public function getInfo(): array {
        return [
            'type' => $this->type,
            'operators' => $this->getOperators(),
            'options' => $this->getOptions()
        ];
    }

    /**
     * Gets a list of available operators for this filter, eg '=', '!=' or '>'
     * @return array
     */
    abstract protected function getOperators(): array;

    /**
     * Gets a list of available options to filter by
     * @return array
     */
    abstract protected function getOptions(): array;

    /**
     * Add a linked table through the join table command
     * @param ForeignTable $foreignTable The foreign table to be linked
     * @return void
     */
    public function addLinkedTable(ForeignTable $foreignTable): void {
        $this->links[] = $foreignTable;
    }

    /**
     * Add a condition to the filter query
     * @param FilterConditionInterface $condition The condition to add
     * @return void
     */
    public function addCondition(FilterConditionInterface $condition): void {
        $this->conditions[] = $condition;
    }

    /**
     * Gets the basic query for getting the options for this filter
     * @return Builder The base of the query
     */
    protected function getBasicOptionQuery(): Builder {
        $query = $this->model::select();

        // set selector
        if ($this->column instanceof CustomColumn) {
            $query = $this->column->applySelector($query);
        } else if ($this->columnTable instanceof ForeignTable) {
            $query = $this->model->addSelect(sprintf('%s.%s', $this->columnTable->getForeignTableName(), $this->column));
        } else {
            $query =  $this->model->addSelect($this->column);
        }

        // link tables
        foreach ($this->links as $linkedTable) {
            $linkedTable->linkForeignTable($query);
        }

        if ($this->columnTable instanceof ForeignTable) {
            $this->columnTable->linkForeignTable($query);
        }

        // apply conditions
        foreach ($this->conditions as $condition) {
            $condition->apply($query);
        }

        return $query;
    }

    /**
     * Gets the available options from the passed query
     * @param Builder $builder The base query to get options from
     * @return array The array filled with all the options
     */
    protected function getValues(Builder $builder): array {
        if ($this->column instanceof CustomColumn) {
            return $builder->get()->pluck($this->column->getAlias())->toArray();
        }

        return $builder->get()->pluck($this->column)->toArray();
    }
}
