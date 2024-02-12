<?php

namespace Lati111\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\Exceptions\DataproviderSearchException;
use Lati111\Exceptions\DataproviderSortException;

/**
 * Dataproviders with this trait are sortable. Requires Dataprovider trait to be present.
 */
trait Sortable
{
    /**
     * Apply dataprovider sorting to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $builder The query to be modified
     * @return Builder The modified query
     */
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
            if (in_array($sortData['column'], $columnWhitelist) && !empty($columnWhitelist)) {
                new DataproviderSortException('Sorting in this column is not allowed', 400);
            }

            $builder->orderBy($sortData['column'], $sortData['direction']);
        }

        return $builder;
    }

    /**
     * Gets a list of fields that are allowed to be sorted on
     * @return array List of fields
     */
    abstract function getAllowedSortColumns(): array;
}