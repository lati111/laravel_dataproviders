<?php

namespace Lati111\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\Exceptions\DataproviderSearchException;
use Lati111\Exceptions\DataproviderSortException;

trait Sortable
{
    protected function ApplySorting(Request $request, Builder $builder): Builder
    {
        $validator = Validator::make($request->all(), [
            "sort" => "array|nullable",
            "sort.column" => "string|required",
            "sort.direction" => "required|in:asc,desc",
        ]);

        if ($validator->fails()) {
            new DataproviderSortException($validator->errors()->first(), 400);
        }

        $sortArray = $request->get('sort');
        if ($sortArray === null) {
            return $builder;
        }

        $columnWhitelist = $this->getAllowedSortColumns();
        foreach ($sortArray as $sortData) {
            if (in_array($sortData['column'], $columnWhitelist)) {
                new DataproviderSortException('Sorting in this column is not allowed', 400);
            }

            $builder->orderBy($sortData['column'], $sortData['direction']);
        }

        return $builder;
    }

    abstract function getAllowedSortColumns(): array;
}