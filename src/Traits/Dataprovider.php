<?php

namespace Lati111\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

/**
 * @method applyPagination(Request $request, Builder $builder) from Paginatable
 * @method applySearch(Request $request, Builder $builder) from Searchable
 * @method applyFilters(Request $request, Builder $builder) from Filterable
 */
trait Dataprovider
{
    protected function getData(Request $request): Builder
    {
        $traits = class_uses(self::class);
        $builder = $this->getContent($request);

        if (in_array(Paginatable::class, $traits)) {
            $builder = $this->applyPagination($request, $builder);
        }

        if (in_array(Searchable::class, $traits)) {
            $builder = $this->applySearch($request, $builder);
        }

        if (in_array(Filterable::class, $traits)) {
            $builder = $this->applyFilters($request, $builder);
        }

        return $builder;
    }

    abstract protected function getContent(Request $request): Builder;
}