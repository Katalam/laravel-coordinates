<?php

declare(strict_types=1);

namespace Katalam\Coordinates;

use Katalam\Coordinates\Commands\CoordinatesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CoordinatesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-coordinates')
            ->hasConfigFile();
    }
}
