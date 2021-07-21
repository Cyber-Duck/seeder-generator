<?php

namespace CyberDuck\Seeder;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class SeederGenerator
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var EloquentGenerator
     */
    private $eloquentGenerator;

    public function __construct(Filesystem $files, EloquentGenerator $eloquentGenerator)
    {
        $this->files = $files;
        $this->eloquentGenerator = $eloquentGenerator;
    }

    public function generate($className, Collection $queries)
    {
        $this->files->put($this->path($className), $this->generateCode($className, $queries));
    }

    public function path($className)
    {
        return config('seeder-generator.seeders_directory', database_path('seeders/'))."{$className}.php";
    }

    public function generateCode($className, Collection $queries)
    {
        return $this->replaceVariables($this->files->get($this->getStub()), [
            'class' => $className,
            'eloquent' => $this->eloquentInstructions($queries),
        ]);
    }

    private function eloquentInstructions($queries)
    {
        return CodeFormatter::indent(2, $this->eloquentGenerator->generate($queries));
    }

    private function getStub()
    {
        return __DIR__ . '/../stubs/seeder.stub';
    }

    private function replaceVariables(string $stub, $variables)
    {
        return str_replace(
            $this->templateFields($variables),
            array_values($variables),
            $stub
        );
    }

    private function templateFields($variables): array
    {
        return collect($variables)
            ->keys()
            ->map(function ($field) {
                return "{{ {$field} }}";
            })
            ->all();
    }
}
