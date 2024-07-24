<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @class Filter by a standard string
 */
class NullFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    protected string $type = 'bool';

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => '=', 'text' => 'is'],
            ['operator' => '!=', 'text' => 'is not'],
        ];
    }
    /** {@inheritdoc} */
    public function handle(Builder $builder, string $operator, mixed $value = null): Builder {
        return parent::handle($builder, $operator, null);
    }


    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [null];
    }
}
