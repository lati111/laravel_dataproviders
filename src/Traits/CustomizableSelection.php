<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Lati111\LaravelDataproviders\Exceptions\DataproviderFilterException;

trait CustomizableSelection
{
    /**
     * Apply dataprovider select customization to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder|Collection $query The query to be modified
     * @return Builder|Collection The modified query
     */
    protected function applySelectCustomization(Request $request, Builder|Collection $query): Builder|Collection {
        $validator = Validator::make($request->all(), [
            'columns' => 'nullable|regex:/^(([a-zA-Z0-9_)]+\=[01]),?)+$/',
        ]);

        if ($validator->fails()) {
            new DataproviderFilterException($validator->errors()->first(), 400);
        }

        if ($request->get('columns') === null) {
            return $query;
        }

        $columns = explode(',', $request->get('columns', ''));
        foreach ($columns as $val) {
            $val = explode('=', trim($val));
            $column = trim($val[0]);
            $show = trim($val[1]) === '1';

            $query = $this->setColumnSelection($query, $column, $show);
            foreach ($this->unions as $union) {
                $union = $this->setColumnSelection($query, $column, $show);
            }
        }

        return $query;
    }

    /**
     * Enable or disable an optional column on the dataprovider
     * @param Builder $query The query to modify
     * @param string $column The column being added or removed from the query
     * @param bool $show Whether to show the column or not
     * @return Builder The modified query
     */
    abstract protected function setColumnSelection(Builder $query, string $column, bool $show = true): Builder;
}
