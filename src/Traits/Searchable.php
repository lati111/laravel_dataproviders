<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\LaravelDataproviders\Exceptions\DataproviderSearchException;

/**
 * Dataproviders with this trait are searchable. Requires Dataprovider trait to be present.
 */
trait Searchable
{
    /** @var bool Whether searching for an aliased column should be allowed. Is slower when enabled */
    protected bool $aliasSearch = false;

    /** @var array An array containing the aliased columns for searching */
    public array $searchAliases = [];

    /**
     * Apply dataprovider searching to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $builder The query to be modified
     * @return Builder The modified query
     */
    protected function applySearch(Request $request, Builder $builder): Builder
    {
        $validator = Validator::make($request->all(), [
            "search" => "string|nullable"
        ]);

        if ($validator->fails()) {
            new DataproviderSearchException($validator->errors()->first(), 400);
        }

        $searchfields = $this->getSearchFields();
        $searchterm = $request->get("search");
        if ($searchterm === null) {
            return $builder;
        }

        //apply query
        if ($this->aliasSearch === true) {
            $builder->having(function($query) use ($searchfields, $searchterm) {
                foreach($searchfields as $searchfield) {
                    //set custom column or column alias
                    if (isset($this->customColumns[$searchfield])) {
                        $searchfield = sprintf('(%)', $this->customColumns[$searchfield]);
                    } else if (isset($this->searchAliases[$searchfield])) {
                        $searchfield = $this->searchAliases[$searchfield];
                    }

                    $query->orHaving($searchfield, "LIKE", '%'.$searchterm.'%');
                }
            });
        } else {
            $builder->where(function($query) use ($searchfields, $searchterm) {
                foreach($searchfields as $searchfield) {
                    $query->orWhere($searchfield, "LIKE", '%'.$searchterm.'%');
                }
            });
        }

        return $builder;
    }

    /**
     * Gets a list of fields that should be searched
     * @return array List of searchable fields
     */
    abstract function getSearchFields(): array;

    /**
     * Set whether searching in an alias column should be allowed. Search is slower when enabled.
     * @param bool $aliasSearch Whether it is allowed or not
     * @return void
     */
    public function setAliasSearch(bool $aliasSearch): void
    {
        $this->aliasSearch = $aliasSearch;
    }
}
