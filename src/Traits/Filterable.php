<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\LaravelDataproviders\Exceptions\DataproviderFilterException;
use Lati111\LaravelDataproviders\Exceptions\DataproviderSearchException;

/**
 * Dataproviders with this trait are filterable. Requires Dataprovider trait to be present.
 */
trait Filterable
{
    /**
     * Apply dataprovider filters to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $builder The query to be modified
     * @return Builder The modified query
     */
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

        $builder->where(function($q) use ($builder, $filterlist, $filters) {
            foreach($filters as $filterdata) {
                if (array_key_exists($filterdata['filter'], $filterlist) === false) {
                    new DataproviderFilterException(sprintf(
                        'Filter %s does not exist on %s',
                        $filterdata['filter'],
                        self::class
                    ), 400);
                }

                $filter = $filterlist[$filterdata['filter']];
                $filter->handle($filter->affectsBaseQuery ? $builder : $q, $filterdata['operator'], $filterdata['value'] ?? '');
            }
        });

        foreach ($this->unions as $union) {
            $builder->where(function($q) use ($builder, $filterlist, $filters) {
                foreach($filters as $filterdata) {
                    if (array_key_exists($filterdata['filter'], $filterlist) === false) {
                        new DataproviderFilterException(sprintf(
                            'Filter %s does not exist on %s',
                            $filterdata['filter'],
                            self::class
                        ), 400);
                    }

                    $filter = $filterlist[$filterdata['filter']];
                    $filter->handle($filter->affectsBaseQuery ? $builder : $q, $filterdata['operator'], $filterdata['value'] ?? '');
                }
            });
        }

        return $builder;
    }

    /**
     * Gets the data for a filter. If no filter is specified, returs a list of all filters
     * @param Request $request The request parameters as passed by Laravel
     * @return array Returns a list of filters, or the specific data of a filter
     */
    public function getFilterData(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'filter' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            new DataproviderFilterException($validator->errors()->first(), 400);
        }

        $filters = $this->getFilterList();
        if ($request->get('filter') === null) {
            return array_keys($filters);
        }

        if (isset($filters[$request->get('filter')]) === false) {
            new DataproviderFilterException(sprintf(
                'Filter %s does not exist on %s',
                $request->get('filter'),
                self::class
            ), 400);
        }

        return $filters[$request->get('filter')]->getInfo();
    }

    /**
     * Gets a list of all available filters
     * @return array Returns an array of available filters
     */
    abstract protected function getFilterList(): array;
}