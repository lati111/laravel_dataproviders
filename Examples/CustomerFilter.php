<?php

use Illuminate\Database\Eloquent\Builder;
use Lati111\LaravelDataproviders\DataproviderFilterInterface;

class CustomerFilter implements DataproviderFilterInterface
{
    // Apply the filter to a query
    public function handle(Builder $builder, string $operator, string $value): Builder
    {
        // Perform query actions necessary to enforce the filter
        $builder->where('customer_id', $operator, $value);

        return $builder;
    }

    // Get the details about this filter matching the right format
    public function getInfo(): array
    {
        return [
            'option' => 'customer',
            [
                ['operator' => '=', 'text' => 'is'],
                ['operator' => '!=', 'text' => 'is not'],
            ],
            Product::distinct()->pluck('customer_id'),
        ];
    }
}