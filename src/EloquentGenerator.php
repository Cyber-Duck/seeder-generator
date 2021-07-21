<?php

namespace CyberDuck\Seeder;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EloquentGenerator
{
    protected $tablesDictionary = [];

    /**
     * @var array
     */
    protected $expectedVariables = [];

    /**
     * @var array
     */
    protected $morphVariables = [];

    /**
     * @var QueryParser
     */
    private $queryParser;

    /**
     * @var Collection
     */
    protected $availableModels;

    public function __construct(QueryParser $queryParser, array $config = [])
    {
        $this->queryParser = $queryParser;

        $this->tablesDictionary = Arr::get($config, 'tablesDictionary', []);
        $this->expectedVariables = Arr::get($config, 'expectedVariables', []);
        $this->morphVariables = Arr::get($config, 'morphVariables', []);

        $this->availableModels = collect(Arr::get($config, 'availableModels'));

        if ($this->availableModels->isEmpty()) {
            $this->availableModels = $this->guessAvailableModels();
        }
    }

    protected function guessAvailableModels()
    {
        $classMapPath = base_path('vendor/composer/autoload_classmap.php');

        if (! file_exists($classMapPath)) {
            return collect();
        }

        return collect(include($classMapPath))
            ->keys()
            ->filter(function ($model) {
                return Str::startsWith($model, 'App\\')
                    && Str::contains($model, 'Models');
            })
            ->values();
    }

    public function generate($entries)
    {
        $this->models = [];

        return collect($entries)
            ->map(fn(Entry $entry) => $this->generateOne($entry))
            ->join("\n\n");
    }

    public function generateOne(Entry $entry): string
    {
        return $this->makeCode($this->queryParser->parse($entry));
    }

    private function makeCode(Query $query): string
    {
        return CodeFormatter::normalizeBlock([
            "// {$query->entry->sql}",
            $this->relatedModelDescription($query->entry),
            $this->instruction($query),
        ]);
    }

    private function relatedModelDescription(Entry $entry): string
    {
        if (! $entry->relatedModel) {
            return '';
        }

        return "// {$entry->relatedModelDescription()}";
    }

    private function instruction(Query $query): string
    {
        $model = $this->guessModelFQN($this->getModelName($query->table));

        if ($query->type === 'insert') {
            return CodeFormatter::normalizeBlock([
                "{$query->entry->generateVariableName()} = {$model}::create([",
                $this->fields($query),
                $this->closeMethod()
            ]);
        }

        if ($query->type === 'update') {
            if ($query->conditions->isEmpty()) {
                return CodeFormatter::normalizeBlock([
                    "{$model}::update([",
                    $this->fields($query),
                    $this->closeMethod()
                ]);
            }

            return CodeFormatter::normalizeBlock([
                "{$model}::query()",
                CodeFormatter::indent(1, array_merge(
                    $this->wheres($query),
                    [
                        "->update([",
                        $this->fields($query),
                        $this->closeMethod()
                    ]
                )),
            ]);
        }

        if ($query->type === 'delete') {
            if ($query->conditions->isEmpty()) {
                return "{$model}::delete();";
            }

            return CodeFormatter::normalizeBlock([
                "{$model}::query()",
                CodeFormatter::indent(1, array_merge(
                    $this->wheres($query),
                    ["->delete();"]
                )),
            ]);
        }

        throw new InvalidArgumentException("Cannot convert query type to Eloquent: {$query->type}");
    }

    private function wheres(Query $query): array
    {
        return $query->conditions->map(fn ($where) => sprintf(
            "->where('%s', '%s', %s)",
            $where->field, $where->expr, $this->transformValue($query, $where->field, $where->value),
        ))->all();
    }

    private function fields(Query $query): string
    {
        return CodeFormatter::indent(1, $query->fields->map(fn ($value, $field) => sprintf(
            "'%s' => %s,", $field, $this->transformValue($query, $field, $value)
        )));
    }

    private function closeMethod()
    {
        return "]);";
    }

    private function getModelName($table): string
    {
        return $this->tablesDictionary[$table]
            ?? Str::studly(Str::singular($table));
    }

    private function guessModelFQN($model)
    {
        return '\\'.$this->availableModels->first(fn ($class) => Str::endsWith($class, $model), $model);
    }

    private function transformValue(Query $query, $field, $value): string
    {
        if ($value === 'null') {
            return $value;
        }

        if ($field == 'id') {
            return $this->getModelKeyString($this->getModelName($query->table).$value);
        }

        if (isset($this->expectedVariables[$field])) {
            if (Str::startsWith($this->expectedVariables[$field], 'morph:')) {
                $morphField = trim(Str::substr($this->expectedVariables[$field], 6));

                return $this->getModelKeyString(
                    $this->morphVariables[trim($query->fields[$morphField] ?? $query->conditions[$morphField], "'")].$value
                );
            }

            return $this->getModelKeyString($this->expectedVariables[$field].$value);
        }

        return $value;
    }

    private function getModelKeyString($variable): string
    {
        return '$'.Str::camel($variable).'->getKey()';
    }
}
