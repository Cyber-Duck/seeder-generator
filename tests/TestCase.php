<?php

namespace Tests;

use CyberDuck\Seeder\Providers\SeederGeneratorProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function getPackageProviders($app)
    {
        return [
            SeederGeneratorProvider::class,
        ];
    }

    protected function setConfig(array $config)
    {
        $this->afterApplicationCreated(function () use ($config) {
            $this->app->config->set($config);
        });
    }

    protected function assertSnapshotMatches($expectedSnapshot, $generatedCode)
    {
        $path = __DIR__."/snapshots/{$expectedSnapshot}.php";

        if (! file_exists($path)) {
            file_put_contents($path, $generatedCode);
            $this->markTestIncomplete("Code was added to path: {$path}. Please review manually.");
        }

        $this->assertSame(file_get_contents($path), $generatedCode);
    }
}
