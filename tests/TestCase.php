<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Katalam\Coordinates\CoordinatesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Katalam\\Coordinates\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            CoordinatesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-coordinates_table.php.stub';
        $migration->up();
        */
    }
}
