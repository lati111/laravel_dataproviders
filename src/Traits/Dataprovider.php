<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The base trait for operating a dataprovider
 */
trait Dataprovider
{
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
        $builder = $this->getContent($request, $dataQuery);

        if (in_array(Paginatable::class, $traits) && $skipPagination === false) {
            /** @noinspection PhpUndefinedMethodInspection */
            $builder = $this->applyPagination($request, $builder);
        }

        if (in_array(Searchable::class, $traits)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $builder = $this->applySearch($request, $builder);
        }

        if (in_array(Sortable::class, $traits)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $builder = $this->applySorting($request, $builder);
        }

        if (in_array(Filterable::class, $traits)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $builder = $this->applyFilters($request, $builder);
        }

        return $builder;
    }

    /**
     * Gets the content query before it is modified further
     * @param Request $request The request parameters as passed by Laravel
     * @param boolean $dataQuery Whether or not this is the data query, or a pagination or filter query
     * @return Builder The newly created query
     */
    abstract protected function getContent(Request $request, bool $dataQuery = true): Builder;
}