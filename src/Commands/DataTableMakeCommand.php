<?php

namespace ThreeEightThree\LaravelDataTable\Commands;

use Illuminate\Console\GeneratorCommand;

class DataTableMakeCommand extends GeneratorCommand
{
    public $signature = 'make:datatable
        {--model= : The eloquent model class to use}
        {--react : Generate a react component}
        {name : The name of the datatable}';

    public $description = 'Create a new laravel data table';

    protected $type = 'Data Table Controller';

    protected function getStub()
    {
        return $this->resolveStubPath('Http/Controllers/DataTableController.php.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers\DataTables';
    }


    protected function replaceNamespace(&$stub, $name)
    {
        $namespace = $this->getNamespace("$name");
        $replacements = [
            '{{namespace}}' => $namespace,
            '{{ namespace }}' => $namespace,
        ];

        $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);

        return $this;
    }

    protected function replaceClass($stub, $name)
    {
        $class = class_basename($name);

        if ($class === "DataTableController") {
            $asDatatableController = " as BaseDataTableController";
            $datatableController = "BaseDataTableController";
        } else {
            $asDatatableController = "";
            $datatableController = "DataTableController";
        }

        $replacements = [
            '{{class}}' => $class,
            '{{ class }}' => $class,
            '{{datatable_controller}}' => $datatableController,
            '{{ datatable_controller }}' => $datatableController,
            '{{as_datatable_controller}}' => $asDatatableController,
            '{{ as_datatable_controller }}' => $asDatatableController,
        ];

        $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);

        $stub = $this->replaceModel($stub, $this->option('model') ?? "Model");

        return $stub;
    }

    protected function replaceModel($stub, $model)
    {
        $model = str_replace('/', '\\', $model);

        if (str_starts_with($model, '\\')) {
            $namespacedModel = trim($model, '\\');
        } else {
            $namespacedModel = $this->qualifyModel($model);
        }

        $model = class_basename(trim($model, '\\'));

        $replacements = [
            '{{model}}' => $model,
            '{{ model }}' => $model,
            '{{namespaced_model}}' => $namespacedModel,
            '{{ namespaced_model }}' => $namespacedModel,
        ];

        $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);

        return $stub;
    }

    protected function resolveStubPath($stub)
    {
        $paths = [
            realpath(__DIR__ . '/../stubs/' . $stub . '.stub'),
            realpath(__DIR__ . '/../stubs/' . $stub),
            realpath(__DIR__ . '/../' . $stub . '.php'),
            realpath(__DIR__ . '/../' . $stub),
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return realpath(__DIR__ . '/../' . $stub . '.stub');
    }
}
