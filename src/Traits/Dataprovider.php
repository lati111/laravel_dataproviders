<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The base trait for operating a dataprovider
 */
trait Dataprovider
{
    /** @var array An array containing custom columns used in selection */
    protected array $customColumns = [];

    /**
     * Get the dataprovider content with the selected dataprovider traits applied
     * @param Request $request The request parameters as passed by Laravel
     * @param boolean $skipPagination Whether or not to skip applying pagination variables (for getting a page count)
     * @param boolean $dataQuery Whether or not this is the data query, or a pagination or filter query
     * @return Builder The newly created query
     */
    protected function getData(Request $request, bool $skipPagination = false, bool $dataQuery = true): Builder
    {
        $traits = class_uses(self::class);
        $query = $this->getContent($request, $dataQuery);
        if ($dataQuery === true) {
            $query = $this->applyCustomColumnSelects($query);
        }

        if (in_array(Paginatable::class, $traits) && $skipPagination === false) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query = $this->applyPagination($request, $query);
        }

        if (in_array(Searchable::class, $traits)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query = $this->applySearch($request, $query);
        }

        if (in_array(Sortable::class, $traits) && $dataQuery === true) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query = $this->applySorting($request, $query);
        }

        if (in_array(Filterable::class, $traits)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query = $this->applyFilters($request, $query);
        }

        return $query;
    }

    /**
     * Applies the custom columns selects to the query
     */
    protected function applyCustomColumnSelects(Builder $query): Builder {
        foreach ($this->customColumns as $alias => $sql) {
            $query->selectRaw(sprintf('(%s) as %s', $sql, $alias));
        }

        return $query;
    }

    /**
     * Add a custom column based on raw sql
     * @param string $alias The alias used to identify the column
     * @param string $sql The raw sql used to create the column
     */
    protected function addCustomColumn(string $alias, string $sql): void {
        $this->customColumns[$alias] = $sql;
    }

    /**
     * Gets the content query before it is modified further
     * @param Request $request The request parameters as passed by Laravel
     * @param boolean $dataQuery Whether or not this is the data query, or a pagination or filter query
     * @return Builder The newly created query
     */
    abstract protected function getContent(Request $request, bool $dataQuery = true): Builder;
}
