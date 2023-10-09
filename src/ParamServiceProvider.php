<?php

namespace FatihOzpolat\Param;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use FatihOzpolat\Param\Commands\ParamCommand;

class ParamServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-param-pos')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-param-pos_table')
            ->hasCommand(ParamCommand::class);
    }
}
