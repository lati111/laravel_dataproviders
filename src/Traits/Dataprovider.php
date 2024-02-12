<?php

namespace Lati111\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait Dataprovider
{
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

    abstract protected function getContent(Request $request): Builder;
}