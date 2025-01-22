<?php

namespace Lati111\LaravelDataproviders\Filters;

/**
 * @class Filter by a standard string
 */
class SelectFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    public string $type = 'select';

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => '=', 'text' => 'is'],
            ['operator' => '!=', 'text' => 'is not'],
        ];
    }

    /** {@inheritdoc} */
    protected function getOptions(): array {
        return $this->getValues($this->getBasicOptionQuery()->distinct());
    }
}
