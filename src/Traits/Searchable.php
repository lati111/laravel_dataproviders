<?php

namespace Lati111\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\Exceptions\DataproviderSearchException;

trait Searchable
{
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

    abstract function getSearchFields(): array;
}