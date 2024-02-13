<?php

namespace Lati111\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\Exceptions\DataproviderSearchException;

/**
 * Dataproviders with this trait are searchable. Requires Dataprovider trait to be present.
 */
trait Searchable
{
    /**
     * Apply dataprovider searching to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $builder The query to be modified
     * @return Builder The modified query
     */
    protected function applySearch(Request $request, Builder $builder,): Builder
    {
        $validator = Validator::make($request->all(), [
            "search" => "string|nullable"
        ]);

        if ($validator->fails()) {
            new DataproviderSearchException($validator->errors()->first(), 400);
        }

        $searchfields = $this->getSearchFields();
        $searchterm = $request->get("search");
        if ($searchterm === null) {
            return $builder;
        }

        $builder->where(function($query) use ($searchfields, $searchterm) {
            foreach($searchfields as $searchfield) {
                $query->orWhere($searchfield, "LIKE", '%'.$searchterm.'%');
            }
        });

        return $builder;
    }

    /**
     * Gets a list of fields that should be searched
     * @return array List of searchable fields
     */
    abstract function getSearchFields(): array;
}