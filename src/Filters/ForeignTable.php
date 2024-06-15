<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * @class A foreign table to be linked in a filter operation
 */

class ForeignTable
{
    /** @var string $localTable The local table that should be linked from */
    public readonly string $localTable;

    /** @var string $localTable The column on the local table used in the join statement */
    public readonly string $localKey;

    /** @var string $localTable The foreign table that should be linked to */
    public readonly string $foreignTable;

    /** @var string|null $tableAlias The alias used for the foreign table */
    public readonly string|null $tableAlias;

    /** @var string $localTable The column on the foreign table used in the join statement */
    public readonly string $foreignKey;

    /**
     * @param class-string $localModel The model that will be linked from
     * @param string $localKey The column on the local table used in the join statement
     * @param class-string $foreignModel The model that will be linked to
     * @param string $foreignKey The column on the foreign table used in the join statement
     */
    public function __construct(string $localModel, string $localKey, string $foreignModel, string $foreignKey, ?string $tableAlias = null) {
        $this->localTable = app($localModel)->getTable();
        $this->localKey = $localKey;
        $this->foreignTable = app($foreignModel)->getTable();
        $this->foreignKey = $foreignKey;
        $this->tableAlias = $tableAlias;
    }

    /**
     * Links a foreign table to the given query
     * @param Builder $query The query to be modified
     * @return Builder The modified query
     */
    public function linkForeignTable(Builder $query): Builder
    {
        return $query->join(
            ($this->tableAlias !== null) ? sprintf('%s as %s', $this->foreignTable, $this->tableAlias) : $this->foreignTable,
            sprintf('%s.%s', $this->localTable, $this->localKey),
            '=',
            sprintf('%s.%s', $this->getForeignTableName(), $this->foreignKey)
        );
    }

    /**
     * Returns the name of the linked table
     * @return string The name of the table
     */
    public function getForeignTableName(): string {
        return $this->tableAlias ?? $this->foreignTable;
    }
}
