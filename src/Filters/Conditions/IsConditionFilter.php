<?php

namespace Lati111\LaravelDataproviders\Filters\Conditions;
use Lati111\LaravelDataproviders\Filters\AbstractFilter;
use Lati111\LaravelDataproviders\Filters\Conditions\FilterConditionInterface;

class IsConditionFilter implements FilterConditionInterface
{
    private string $column;
    private string $value;

    public function __construct(string $column, string $value) {
        $this->column = $column;
        $this->value = $value;
    }

    public function apply($builder) {
        return $builder->where($this->column, $this->value);
    }
}
