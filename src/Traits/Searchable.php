<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
     * @param Builder|Collection $query The query to be modified
     * @return Builder|Collection The modified query
     */
    protected function applySearch(Request $request, Builder|Collection $query): Builder|Collection
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
            return $query;
        }

        if ($query instanceof Builder) {
            return $this->applySearchToQuery($query, $searchterm, $searchfields);
        } else if ($query instanceof Collection) {
            return $this->applySearchToCollection($query, $searchterm, $searchfields);
        }
    }

    /**
     * Apply the search parameters to a query
     * @param Builder $builder The query to modify
     * @param string $needle The term to search for in the given fields
     * @param array $searchFields The columns to search on
     * @return Builder The modified query
     */
    private function applySearchToQuery(Builder $builder, string $needle, array $searchfields): Builder {
        if ($this->aliasSearch === true) {
            $builder->having(function($query) use ($searchfields, $needle) {
                foreach($searchfields as $searchfield) {
                    //set custom column or column alias
                    if (isset($this->customColumns[$searchfield])) {
                        $searchfield = sprintf('(%s)', $this->customColumns[$searchfield]);
                    } else if (isset($this->searchAliases[$searchfield])) {
                        $searchfield = $this->searchAliases[$searchfield];
                    }

                    $condition = sprintf("%s LIKE '%%%s%%'", $searchfield, $needle);
                    $query->orHavingRaw($condition);
                }
            });
        } else {
            $builder->where(function($query) use ($searchfields, $needle) {
                foreach($searchfields as $searchfield) {
                    if (isset($this->customColumns[$searchfield])) {
                        $searchfield = sprintf('(%s)', $this->customColumns[$searchfield]);
                    } else if (isset($this->searchAliases[$searchfield])) {
                        $searchfield = $this->searchAliases[$searchfield];
                    }

                    $condition = sprintf("%s LIKE '%%%s%%'", $searchfield, $needle);
                    $query->orWhereRaw($condition);
                }
            });
        }

        return $builder;
    }

    /**
     * Apply the search parameters to a collection
     * @param Collection $query The collection to modify
     * @param string $needle The term to search for in the given fields
     * @param array $searchFields The columns to search on
     * @return Collection The modified collection
     */
    private function applySearchToCollection(Collection $query, string $needle, ?array $searchFields): Collection {
        return $query->filter(function(array|string $data) use ($needle, $searchFields) {
            if (is_string($data)) {
                return str_contains(trim(strtolower($data)), trim(strtolower($needle)));
            }

            $foundMatch = false;
            foreach ($data as $item) {
                if (str_contains(trim(strtolower($item)), trim(strtolower($needle)))) {
                    $foundMatch;
                }
            }

            return $foundMatch;
        });
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
