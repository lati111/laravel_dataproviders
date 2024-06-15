<?php

namespace Lati111\LaravelDataproviders\Filters\Conditions;
use Lati111\LaravelDataproviders\Filters\AbstractFilter;

interface FilterConditionInterface
{
    public function apply($builder);
}
