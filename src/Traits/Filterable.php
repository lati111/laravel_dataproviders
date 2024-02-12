<?php

namespace Lati111\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\Exceptions\DataproviderFilterException;
use Lati111\Exceptions\DataproviderSearchException;

trait Filterable
{
    protected function applyFilters(Request $request, Builder $builder): Builder
    {
        if ($request->get('filters', '[]') === '[]') {
            return $builder;
        }

        $filters = json_decode($request->get('filters'), true);
        $validator = Validator::make($filters, [
            '*.filter' => 'required|string',
            '*.operator' => 'required|string',
            '*.value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            new DataproviderFilterException($validator->errors()->first(), 400);
        }

        $filterlist = $this->getFilterList();
        foreach($filters as $filterdata) {
            if (array_key_exists($filterdata['filter'], $filterlist) === false) {
                new DataproviderFilterException(sprintf(
                    'Filter %s does not exist on %s',
                    $filterdata['filter'],
                    self::class
                ), 400);
            }

            $filterlist[$filterdata['filter']]->handle($builder, $filterdata['operator'], $filterdata['value']);
        }

        return $builder;
    }

    public function getFilterData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            new DataproviderFilterException($validator->errors()->first(), 400);
        }

        $filters = $this->getFilterList();
        if ($request->get('filter') === null) {
            return $filters;
        }

        if (isset($filters[$request->get('filter')]) === false) {
            new DataproviderFilterException(sprintf(
                'Filter %s does not exist on %s',
                $request->get('filter'),
                self::class
            ), 400);
        }

        return $filters[$request->get('filter')]->getJson();
    }

    abstract protected function getFilterList(): array;
}