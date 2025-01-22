<?php

namespace Lati111\LaravelDataproviders\Filters;

use Illuminate\Database\Eloquent\Model;

/**
 * @class Filter by a standard boolean
 */
class BoolFilter extends AbstractFilter
{
    /** {@inheritdoc} */
    public string $type = 'bool';

    /** @var string $operatorTypes The set of operators used for this filter */
    private string $operatorTypes = '';

    /**
     * {@inheritdoc}
     * @param string $operatorType which set of operators that should be used for this filter
     */
    public function __construct(string $operatorType, Model $model, string|CustomColumn $column, ?ForeignTable $columnTable = null) {
        $this->operatorTypes = $operatorType;
        parent::__construct($model, $column, $columnTable);
    }

    /** {@inheritdoc} */
    protected function getOperators(): array {
        switch($this->operatorTypes) {
            default:
            case 'is':
                return [
                    ['operator' => '=', 'text' => 'is'],
                    ['operator' => '!=', 'text' => 'is not'],
                ];
            case 'has':
                return [
                    ['operator' => '=', 'text' => 'has'],
                    ['operator' => '!=', 'text' => 'does not have'],
                ];
            case 'can':
                return [
                    ['operator' => '=', 'text' => 'can'],
                    ['operator' => '!=', 'text' => 'can not'],
                ];
        }

    }

    /** {@inheritdoc} */
    protected function getOptions(): array {
        return [false, true];
    }
}
