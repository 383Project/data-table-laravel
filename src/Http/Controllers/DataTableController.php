<?php

namespace SteJaySulli\LaravelReactDataTable\Http\Controllers\DataTable;

use App\Http\Resources\DataTable\DefaultDataTableResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

abstract class DataTableController extends Controller
{
    // In the simplest use case, set these up in your derived class
    protected $hidden_fields = [];
    protected $sortable_fields = [];
    protected $searchable_fields = [];
    protected $filterable_fields = [];
    protected $field_labels = [];
    protected $resource_class = null;

    // You must provide this, which should return the base eloquent query object for your datatable
    abstract public function dataQuery(Request $request): Builder;

    // You can override these functions in your derived class to provide custom search and sort functionality
    public function getHiddenFields(): array
    {
        return $this->hidden_fields;
    }

    public function getSortableFields(): array
    {
        return $this->sortable_fields;
    }

    public function getSearchableFields(): array
    {
        return $this->searchable_fields;
    }

    public function getFilterableFields(array $fields = []): array
    {
        $labels = $this->filterable_fields;
        foreach ($fields as $field) {
            if (!in_array($field, $labels)) {
                $labels[$field] = Str::title(
                    preg_replace('/\s{2,}/', ' ', preg_replace("/[^a-z0-9]/", " ", Str::snake($field)))
                );
            }
        }
        return $labels;
    }

    public function getFieldLabels(): array
    {
        return $this->field_labels;
    }

    public function getResourceClass(): string
    {
        if (!is_null($this->resource_class)) {
            return $this->resource_class;
        }
        return DefaultDataTableResource::class;
    }

    public function dataSearch($query, $search): void
    {
        $query->where(function ($query) use ($search) {
            foreach ($this->getSearchableFields() as $field) {
                $functionName = Str::camel('search_' . $field);
                if (method_exists($this, $functionName)) {
                    $this->{$functionName}($query, $search);
                } else {
                    $query->orWhere($field, 'like', '%' . $search . '%');
                }
            }
        });
    }

    public function defaultFilter($query, $field, $value): void
    {
        if (is_array($value)) {
            $query->whereIn($field, $value);
        } else {
            $query->where($field, $value);
        }
    }

    public function dataFilter($query, $filters): void
    {
        $query->where(function ($query) use ($filters) {
            $filterableFields = $this->getFilterableFields();
            foreach ($filters as $field => $value) {
                if (!in_array($field, $filterableFields)) {
                    continue;
                }

                $functionName = Str::camel('filter_' . $field);
                if (method_exists($this, $functionName)) {
                    $this->{$functionName}($query, $value);
                } else {
                    $this->defaultFilter($query, $field, $value);
                }
            }
        });
    }

    public function dataSort($query, $sort, $direction = "ASC"): void
    {
        if (in_array($sort, $this->getSortableFields())) {
            $functionName = Str::camel('sort_by_' . $sort);
            if (method_exists($this, $functionName)) {
                $this->{$functionName}($query, $direction);
            } else {
                $query->orderBy($sort, $direction);
            }
        }
    }

    public function __invoke(Request $request)
    {
        $per_page = $request->input('per_page', 5);
        $data = $this->dataQuery($request);

        if ($search = $request->input('search')) {
            $this->dataSearch($data, $search);
        }

        if ($filters = $request->input('filters')) {
            $this->dataFilter($data, $filters);
        }

        $sort = $request->input('sort', count($this->getSortableFields()) > 0 ? $this->getSortableFields()[0] : null);
        if (strtoupper($request->input('direction', "ASC")) == "DESC") {
            $direction = "DESC";
        } else {
            $direction = "ASC";
        }

        if ($sort) {
            $this->dataSort($data, $sort, $direction);
        }

        return $this->getResourceClass()::collection($data->paginate($per_page))
            ->additional([
                "meta" => [
                    "hidden_fields" => $this->getHiddenFields(),
                    "field_labels" => $this->getFieldLabels(),
                    "sortable_fields" => $this->getSortableFields(),
                    "sort" =>  $sort,
                    "direction" => $direction,
                ]
            ]);
    }
}
