<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Builder;
use Lati111\Traits\Dataprovider;
use Lati111\Traits\Filterable;
use Lati111\Traits\Paginatable;
use Lati111\Traits\Searchable;
use Lati111\Traits\Sortable;
use Symfony\Component\HttpFoundation\Response;

class Datatable extends Controller
{
    // Use the dataprovider trait to indicate that this is a dataprovider
    use Dataprovider;
    // Use the paginatable trait to indicate that this dataprovider should be paginatable
    use Filterable;

    // Method to be called from a route
    public function data(Request $request) {
        // Get the data from the dataprovider trait.
        $data = $this->getData($request);

        // Return the data as a JsonResponse
        response()->json($data, Response::HTTP_OK);
    }

    // Implement the getFilterList method from the Filterable trait to set what filters exist
    function getFilterList(): array
    {
        return [
            'customer' => new CustomerFilter(),
            'product' => new ProductFilter(),
        ];
    }

    // Implement the getContent method from the dataprovider trait to set the unmodifed data
    protected function getContent(Request $request): Builder
    {
        // Create a query for orders
        return Order::select();
    }
}