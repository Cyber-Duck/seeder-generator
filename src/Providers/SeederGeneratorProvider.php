<?php

namespace CyberDuck\Seeder\Providers;

use CyberDuck\Seeder\Commands\GenerateSeederFromTelescopeEntries;
use CyberDuck\Seeder\Commands\GetLastTelescopeEntryUuid;
use CyberDuck\Seeder\EloquentGenerator;
use CyberDuck\Seeder\QueryParser;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SeederGeneratorProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom($this->packageRoot('config/seeder-generator.php'), 'cyber-duck');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSeederFromTelescopeEntries::class,
                GetLastTelescopeEntryUuid::class,
            ]);

            $this->publishes([
                $this->packageRoot('config') => base_path('config'),
            ], ['seeder-generator']);
        }
    }

    public function register()
    {
        $this->app->singleton(EloquentGenerator::class, function (Application $app) {
            return new EloquentGenerator(
                new QueryParser,
                $app->make('config')->get('seeder-generator', [])
            );
        });
    }

    private function packageRoot(string $path): string
    {
        return __DIR__.'/../../'.$path;
    }
}
