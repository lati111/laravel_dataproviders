<?php

namespace Lati111\LaravelDataproviders;

use Illuminate\Database\Eloquent\Builder;


interface DataproviderInterface {
    public function handle(Builder $builder, string $operator, string $value): Builder;
    public function getJson(): array;
}