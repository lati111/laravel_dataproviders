<?php

namespace Lati111\LaravelDataproviders;

use Illuminate\Database\Eloquent\Builder;

interface DataproviderInterface {

    /**
     * Apply the filter to a query
     * @param Builder $builder The query to be modified
     * @param string $operator The operator used in the filter
     * @param string $value The value to filter on
     * @return Builder The modified query
     */
    public function handle(Builder $builder, string $operator, string $value): Builder;

    /**
     * Gets the info data for this filter
     * @return array{option:string, operators:array<string>, options:array<string>}
     */
    public function getInfo(): array;
}