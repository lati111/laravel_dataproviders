<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @class A custom column made from a raw sql statement and an alias for a filter
 */

class CustomColumn
{
    /** @var string $selectStatement The SQL statement that should be used instead of a column */
    private readonly string $selectStatement;

    /** @var string $alias The alias that should become the name of this custom column */
    private readonly string $alias;

    /**
     * @param string $selectStatement The SQL statement that should be used instead of a column
     * @param string $alias The alias that should become the name of this custom column
     */
    public function __construct(string $selectStatement, string $alias) {
        $this->selectStatement = $selectStatement;
        $this->alias = $alias;
    }

    /**
     * Apply the custom select statement for this column to the given query
     * @param Builder $builder The query that should be modified
     * @return Builder The modified selector
     */
    public function applySelector(Builder $builder): Builder {
        return $builder->addSelect(DB::raw(sprintf('%s as %s', $this->selectStatement, $this->alias)));
    }

    /**
     * Apply the custom where statement for this column to the given query
     * @param Builder $builder The query that should be modified
     * @return Builder The modified selector
     */
    public function applyWhere(Builder $builder, string $operator, string $value, bool $isOrWhere): Builder {
        $builder = $this->applySelector($builder);
        $where = DB::raw(sprintf('(%s)', $this->selectStatement));

        if ($isOrWhere) {
            return $builder->orWhere($where, $operator, $value);
        } else {
            return $builder->where($where, $operator, $value);
        }
    }

    /**
     * Gets the name string of the alias given to this column
     * @return string The column alias
     */
    public function getAlias(): string {
        return $this->alias;
    }
}
