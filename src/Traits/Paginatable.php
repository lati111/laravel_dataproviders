<?php

namespace Lati111\Traits;

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Lati111\Exceptions\DataproviderPaginationException;

/**
 * @method getData(Request $request) from Dataprovider
 */
trait Paginatable
{
    private int $defaultPerPage = 10;

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

    protected function getCount(Request $request): float
    {
        $count = $this->getData($request)->count();
        $perpage = $request->get('perpage', $this->defaultPerPage);

        $pages = $count / $perpage;
        return ceil($pages);
    }

    protected function setDefaultPerPage(int $perpage): void
    {
        $this->defaultPerPage = $perpage;
    }
}