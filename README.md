# 383's Data Table (Laravel Package)

This package provides a simple way to create the back end for a data table in Laravel. It is designed to work with our [React Data Table](https://github.com/383Project/data-table-react) package, and could later include other front end packages as well.

The purpose of the data table is relatively simple and common; to provide a table of data that can be sorted, filtered, searched and paginated.

This back end portion focusses on providing a good Laravel-esque interface you can use with your Eloquent models in order to provide controllers for your data tables in a uniform way.

## Installation

Because this is a private repository, you will need to add the following to your `composer.json` file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:383Project/data-table-laravel.git"
        }
    ]
}
```

Then you can install the package via composer:

```bash
composer require SteJaySulli/laravel-react-data-table
```

## Data Table Controllers

The data table controller is the core of this package. Each data table needs a controller in order to define how the data is retrieved. This package provides a [base data table controller](./src/Http/Controllers/DataTableController.php) that you can extend to create your own data table controllers.

The only feature you **must** provide is the `query` method, which should return a query builder instance that will be used to retrieve the data for the data table, but there are a number of other properties and methods you can override or provide to customise the behaviour of the data table.

## Hidden Fields

Sometimes you need data to be available to the front end code, but do not want it to be displayed as a column in the table. You can define hidden fields in your data table controller either by providing a `$hidden_fields` property or, if you require more complex behaviour, by overriding the `getHiddenFields` method.

## Field Labels

By default, the field labels will be taken from the field names; these will be have all non-alphanumeric characters converted to single spaces and the string will be converted to title case (for example `my-weird_field&&&name` => `My Wierd Field Name`). You can override this behaviour by providing a `$field_labels` property or by overriding the `getFieldLabels` method.

It's worth noting that the default implementation of `getFieldLabels` contains the logic which converts any field names missing from the `$field_labels` property to title case, so if you override this method you may want to provide an alternative implementation or you will be at the mercy of the front end's fallback.

## Sortable, Searchable and Filterable Fields

This package allows you a great deal of control over which fields are sortable, searchable and filterable; let's define what these terms mean:

-   Sortable means that the user can click on the column header to sort the data by that column in order to select it for sorting, either in ascending or descending order
-   Searchable means that the user can type into a search box to filter the data shown. Searches `OR` the field filters, so records are shown if they match any fields against the given search term
-   Filterable means the user can select one or more filters. Filters `AND` the field filters, so records are shown if they match all fields against the given filter options.

Note that by default the search functionality uses a text input, whereas the filters use an array structure

### Sorting

In order to make a field sortable, simply add it to the `$sortable_fields` property of the controller; this will work automatically for simple sorting (ie where the corresponding database query is just `ORDER BY field ASC/DESC`), but for more complex queries you will need to provide a sort method.

This is done by creating a method that begins with `sort` followed by the field name of the field in pascal case (making the function name camel case); this method should take two arguments, the query builder and the direction of the sort (either `asc` or `desc`); for example a common case may be where we have concatenated first and last names, but we need to sort against the full name - for this example let's assume we also want to sort by last_name first as well:

```php
    public function sortFullName(Builder $query, string $direction): Builder
    {
        return $query->orderBy('last_name', $direction)->orderBy('first_name', $direction);
    }
```

### Searching

In order to make a field searchable, simply add it to the `$searchable_fields` property of the controller; this will work automatically for simple searches (ie where the corresponding database query is just `WHERE field LIKE %search_term%`), but for more complex queries you will need to provide a search method.

This is done by creating a method that begins with `search` followed by the field name of the field in pascal case (making the function name camel case); this method should take two arguments, the query builder and the search term; for example a common case may be where we have concatenated first and last names, but we need to search against the full name:

```php
    public function searchFullName(Builder $query, string $search_term): Builder
    {
        return $query->whereRaw('CONCAT(first_name, " ", last_name) LIKE ?', ["%$search_term%"]);
    }
```

### Filtering

In order to make a field filterable, simply add it to the `$filterable_fields` property of the controller; this will work automatically for simple filters (ie where the corresponding database query is just `WHERE field = filter_value` for a simple string, or `WHERE field IN (values)` where an array is given), but for more complex queries you will need to provide a filter method.

This is done by creating a method that begins with `filter` followed by the field name of the field in pascal case (making the function name camel case); this method should take two arguments, the query builder and the filter value; for example a common case may be where we have a status field that is an integer, but we want to filter against a string representation of a list of statuses:

```php
    public function filterStatus(Builder $query, string $statuses): Builder
    {
        return $query->whereIn('status', explode(',', $statuses));
    }
```

When the front end requests filters, this should be provided in a filter array, where the key is the field name and the value is the filter value; for example:

```json
{
    "filters": {
        "brand": "Honda"
        "type":[
            "car",
            "motorcycle"
        ],
        "status": "new, used, damaged"
    }
}
```

This example would filter the data to only show records where the brand is Honda, the type is either car or motorcycle, and the status is new, used or damaged (according to the example custom method above)

## Resource Classes

By default, the controller will use the `DefaultDataTableResource` to format its output, but you can provide a custom resource class by setting the `$resource_class` property or overriding the `getResourceClass` method of the controller.

Whichever resource class you use, the response will have a few properties added to its metadata which are used by the front end:

-   `hidden_fields`
-   `field_labels`
-   `sortable_fields`
-   `searchable_fields`
-   `filterable_fields`
-   `sort`
-   `direction`

These properties are used by the front end to determine how to display the data table and what options to provide to the user.
