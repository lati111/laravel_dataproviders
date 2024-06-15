<?php

namespace Lati111\LaravelDataproviders\Filters;

/**
 * @class Filter by a standard number
 */
class NumberFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    protected string $type = 'number';

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => '=', 'text' => 'is'],
            ['operator' => '!=', 'text' => 'is not'],
            ['operator' => '>', 'text' => 'is higher than'],
            ['operator' => '>=', 'text' => 'is higher or equal to'],
            ['operator' => '<=', 'text' => 'is lower or equal to'],
            ['operator' => '<', 'text' => 'is lower than'],
        ];
    }

    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [
            'min' => $this->getBasicOptionQuery()->min($this->column),
            'max' => $this->getBasicOptionQuery()->max($this->column)
        ];
    }
}
