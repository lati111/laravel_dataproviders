<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Model;

/**
 * @class Filter by a standard string
 */
class DataSelectFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    public string $type = 'data-select';

    /**
     * @inheritdoc
     * @param string $url The url leading to the dataselect
     * @param string $itemIdentifier The primary identifier used in the dataselect
     * @param string $itemLabel The primary label used in the dataselect
     */
    public function __construct(
        Model $model, string|CustomColumn $column,public string $url, public string $itemIdentifier, public string $itemLabel, ?ForeignTable $columnTable = null
    ) {
        parent::__construct($model, $column, $columnTable);
    }

    /** {@inheritdoc} */
    protected function getOperators(): array {
        return [
            ['operator' => '=', 'text' => 'is'],
            ['operator' => '!=', 'text' => 'is not'],
        ];
    }

    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [
            'url' => $this->url,
            'identifier' => $this->itemIdentifier,
            'label' => $this->itemLabel,
        ];
    }
}
