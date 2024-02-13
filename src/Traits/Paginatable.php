<?php

namespace Lati111\LaravelDataproviders\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\LaravelDataproviders\Exceptions\DataproviderPaginationException;

/**
 * Dataproviders with this trait are split into seperate pages and is paginatable. Requires Dataprovider trait to be present.
 */
trait Paginatable
{
    /** @var int The amount of items per page if no amount was specified */
    private int $defaultPerPage = 10;

    /**
     * Apply dataprovider pagination to a query
     * @param Request $request The request parameters as passed by Laravel
     * @param Builder $builder The query to be modified
     * @return Builder The modified query
     */
    protected function applyPagination(Request $request, Builder $builder): Builder
    {
        $validator = Validator::make($request->all(), [
            "page" => "integer|nullable",
            "perpage" => "integer|nullable"
        ]);

        if ($validator->fails()) {
            new DataproviderPaginationException($validator->errors()->first(), 400);
        }

        $page = $request->get('page', 1);
        $perpage = $request->get('perpage', $this->defaultPerPage);

        return $builder
            ->offset(($page - 1) * $perpage)
            ->take($perpage);
    }

    /**
     * Get the amount of pages the dataprovider has with the current options
     * @param Request $request The request parameters as passed by Laravel
     * @return float The amount of pages
     */
    protected function getPages(Request $request): float
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $count = $this->getData($request)->count();
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