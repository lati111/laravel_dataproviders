# Laravel dataproviders
Adds a base for easy dataprovider manipulation to Laravel. Dataproviders are the API side of a data table, allowing you to search, split by page, sort or filter a model which can then be loaded into a client side table. We recommend user our [TypeScript receiver scripts](https://github.com/lati111/laravel_datatables) for this, but you can also build your own.

## Contents
- [Installation](#installation)
- [Usage](#usage)
  - [Basic](#usage)
  - [Pagination](#paginatable)
  - [Searching](#searchable)
  - [Sorting](#sortable)
  - [Filters](#filterable)
- [Requirements](#requirements)

## Installation
```
composer require lati111/laravel_dataproviders
```

## Usage
To create a basic dataprovider with no additional functions:
```php
class Datatable extends Controller
{
    // Use the dataprovider trait to indicate that this is a dataprovider
    use Dataprovider;

    // Method to be called from a route
    public function data(Request $request) {
        // Get the data from the dataprovider trait.
        $data = $this->getData($request);

        // Return the data as a JsonResponse
        response()->json($data, Response::HTTP_OK);
    }

    // Implement the getContent method from the dataprovider trait to set the unmodifed data
    protected function getContent(Request $request): Builder
    {
        // Create a query for orders
        return Order::select();
    }
}
```

### Paginatable
A dataprovider can be made paginatable, meaning the content in seperated into different pages which can be navigated through. To create a paginatable dataprovider, do as follows:

```php
class Datatable extends Controller
{
    // Use the dataprovider trait to indicate that this is a dataprovider
    use Dataprovider;
    // Use the paginatable trait to indicate that this dataprovider should be paginatable
    use Paginatable;

    // Method to be called from a route
    public function data(Request $request) {
        // Set the default amount of items per page (normally 10)
        $this->setDefaultPerPage(20);

        // Get the data from the dataprovider trait.
        $data = $this->getData($request);

        // Return the data as a JsonResponse
        response()->json($data, Response::HTTP_OK);
    }

    ...
}
```

You can pass the `page` and `perpage` variables along the request. Page indicates which page should be loaded, while perpage sets the amount of entries that should be loaded.
```json
{
  "page": 4,
  "perpage": 10
}
```

### Searchable
A dataprovider can be made to be searchable, allowing the user to search the dataprovider for certain values. To make a dataprovider paginatable, you can do the following:
```php
class Datatable extends Controller
{
    // Use the dataprovider trait to indicate that this is a dataprovider
    use Dataprovider;
    // Use the paginatable trait to indicate that this dataprovider should be paginatable
    use Searchable;

    // Method to be called from a route
    public function data(Request $request) {
        // Get the data from the dataprovider trait.
        $data = $this->getData($request);

        // Return the data as a JsonResponse
        response()->json($data, Response::HTTP_OK);
    }

    // Implement the getSearchFields method from the Searchable trait to set what columns can be searched on
    function getSearchFields(): array
    {
        // Return an array of column names belonging to the model this dataprovider is searching on
        return ['id', 'product_name'];
    }

    ...
}
```

You can pass the `search` variable along the request, indicating which term to search for in the selected columns.
```json
{
  "search": "USB"
}
```

### Sortable
A dataprovider can be made to be searchable, allowing the user to indicate which columns should be sorted on. To make a dataprovider sortable, you can do the following:

```php
class Datatable extends Controller
{
    // Use the dataprovider trait to indicate that this is a dataprovider
    use Dataprovider;
    // Use the paginatable trait to indicate that this dataprovider should be paginatable
    use Sortable;

    // Method to be called from a route
    public function data(Request $request) {
        // Get the data from the dataprovider trait.
        $data = $this->getData($request);

        // Return the data as a JsonResponse
        response()->json($data, Response::HTTP_OK);
    }

    // Implement the getAllowedSortColumns method from the Sortable trait to set what columns can be sorted on
    function getAllowedSortColumns(): array
    {
        // Return an array of column names belonging to the model this dataprovider is are allowed to be sorted on
        return ['total_price', 'price', 'amount', 'created_at'];
    }

    ...

}
```

You can pass the a json array of sortables along through the request variables under the key 'sort'. The array should be as follows 
```json
{
  "sort": {
    "price": "desc",
    "created_at": "desc"
  }
}
```

### Filterable
A dataprovider can be made to be filterable, allowing the user to filter on certain values. To make a dataprovider sortable, you can do the following:
```php
class Datatable extends Controller
{
    // Use the dataprovider trait to indicate that this is a dataprovider
    use Dataprovider;
    // Use the paginatable trait to indicate that this dataprovider should be paginatable
    use Filterable;

    // Method to be called from a route to get the data
    public function data(Request $request) {
        // Get the data from the dataprovider trait.
        $data = $this->getData($request);

        // Return the data as a JsonResponse
        response()->json($data, Response::HTTP_OK);
    }
    
    // Method to be called from a route to get filter options
    public function getFilters(Request $request) {
        // Gets either a list of available filters, or a list of available options for a filter if one is specified
        $data = $this->getFilterData($request);

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

    ...
}
```

Filters must implement the `DataproviderFilterInterface` interface to work, and be constructed as follows:

```php
class CustomerFilter implements DataproviderFilterInterface
{
    // Apply the filter to a query
    public function handle(Builder $builder, string $operator, string $value): Builder
    {
        // Perform query actions necessary to enforce the filter
        $builder->where('customer_id', $operator, $value);

        return $builder;
    }

    // Get the details about this filter matching the right format
    public function getInfo(): array
    {
        return [
            'option' => 'customer',
            'operators' => [
                ['operator' => '=', 'text' => 'is'],
                ['operator' => '!=', 'text' => 'is not'],
            ],
            'options' => Product::distinct()->pluck('customer_id'),
        ];
    }
}
```

You can pass the a json array of filters along through the request variables under the key 'sort'. The array should be as follows
```json
{
  "filters": {
    "customer": {
      "filter": "customer",
      "operator": "!=",
      "value": "34"
    }
  }
}
```

## Requirements
- PHP ^8.1
- Laravel ^10.0


