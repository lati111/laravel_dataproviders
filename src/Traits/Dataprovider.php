<?php

namespace Lati111\Traits;

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
     * @return Builder The newly created query
     */
    protected function getData(Request $request): Builder
    {
        $traits = class_uses(self::class);
        $builder = $this->getContent($request);

        if (in_array(Paginatable::class, $traits)) {
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
     * @return Builder The newly created query
     */
    abstract protected function getContent(Request $request): Builder;
}