<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Lati111\LaravelDataproviders\Exceptions\DataproviderPaginationException;

/**
 * Dataproviders with this trait are split into seperate pages and is paginatable. Requires Dataprovider trait to be present.
 */
trait Paginatable
{
    /** @var int The requested page */
    protected int $page = 1;

    /** @var int The requested offset */
    protected int $offset = 0;

    /** @var int The requested amount of items per page */
    protected int $perPage = 10;

    /** @var int The amount of items per page if no amount was specified */
    private int $defaultPerPage = 10;

    /**
     * Apply dataprovider pagination to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $query The query to be modified
     * @return Builder The modified query
     * @throws DataproviderPaginationException
     */
    protected function applyPaginationToQuery(Request $request, Builder $query): Builder
    {
        $this->loadPaginationDetails($request);

        if ($request->get('offset') === null && $request->get('perpage') === null && $request->get('page') === null) {
            return $query;
        }

        return $query
            ->offset($this->offset)
            ->take($this->perPage);
    }

    /**
     * Apply dataprovider pagination to a collection
     * @param Request $request The request parameters as passed by Laravel
     * @param Collection $query The query to be modified
     * @return Collection The modified collection
     * @throws DataproviderPaginationException
     */
    protected function applyPaginationToCollection(Request $request, Collection $query): Collection
    {
        $this->loadPaginationDetails($request);

        if ($request->get('offset') === null && $request->get('perpage') === null && $request->get('page') === null) {
            return $query;
        }

        return $query
            ->skip($this->offset)
            ->take($this->perPage);
    }

    /**
     * Get the pagination details from
     * @param Request $request The request parameters as passed by Laravel
     * @throws DataproviderPaginationException
     */
    private function loadPaginationDetails(Request $request) {
        $validator = Validator::make($request->all(), [
            "page" => "integer|nullable",
            "perpage" => "integer|nullable",
            "offset" => "integer|nullable"
        ]);

        if ($validator->fails()) {
            new DataproviderPaginationException($validator->errors()->first(), 400);
        }

        $this->page = $request->get('page', 1);
        $this->perPage = $request->get('perpage', $this->defaultPerPage);
        $this->offset = ($this->page - 1) * $this->perPage;
        if ($request->get('offset') !== null && $request->get('page') === null) {
            $this->offset = $request->get('offset');
        }
    }

    /**
     * Get the amount of pages the dataprovider has with the current options
     * @param Request $request The request parameters as passed by Laravel
     * @return float The amount of pages
     */
    protected function getPages(Request $request): float
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $count = $this->getData($request, true, false)->count();
        $perpage = $request->get('perpage', $this->defaultPerPage);

        $pages = $count / $perpage;
        return ceil($pages);
    }

    /**
     * Set the amount of items per page when no amount is specified
     * @param int $perpage Amount of items
     * @return void
     */
    protected function setDefaultPerPage(int $perpage): void
    {
        $this->defaultPerPage = $perpage;
    }
}
