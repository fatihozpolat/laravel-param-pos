<?php

namespace FatihOzpolat\Param\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FatihOzpolat\Param\Param
 */
class Param extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FatihOzpolat\Param\Param::class;
    }
}
