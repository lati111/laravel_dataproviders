<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\LaravelDataproviders\Exceptions\DataproviderSearchException;
use Lati111\LaravelDataproviders\Exceptions\DataproviderSortException;

/**
 * Dataproviders with this trait are sortable. Requires Dataprovider trait to be present.
 */
trait Sortable
{
    public array $columnAliases = [];

    /**
     * Apply dataprovider sorting to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $builder The query to be modified
     * @return Builder The modified query
     */
    protected function ApplySorting(Request $request, Builder $builder): Builder
    {
        if ($request->get('sort') === null) {
            return $builder;
        }

        $sortData = json_decode($request->get('sort'), true);
        $validator = Validator::make($sortData, [
            ".*" => "required|in:asc,desc",
        ]);

        if ($validator->fails()) {
            new DataproviderSortException($validator->errors()->first(), 400);
        }

        $columnAliases = $this->columnAliases;
        $columnWhitelist = $this->getAllowedSortColumns();
        foreach ($sortData as $column => $direction) {
            if (isset($columnAliases[$column])) {
                $column = $columnAliases[$column];
            }

            if (in_array($column, $columnWhitelist) && !empty($columnWhitelist)) {
                new DataproviderSortException('Sorting in this column is not allowed', 400);
            }

            $builder->orderBy($column, $direction);
        }

        return $builder;
    }

    /**
     * Gets a list of fields that are allowed to be sorted on
     * @return array List of fields
     */
    abstract function getAllowedSortColumns(): array;
}