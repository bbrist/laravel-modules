<?php

namespace Async\Modules\Facades;

use Async\Modules\ModuleService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool exists(string $module)
 * @method static string path(string $module, string|array|null $path = null)
 * @method static string getModuleDirectory(?string $module = null)
 * @method static string getModuleFilename(string $module)
 * @method static string getModuleNamespace(?string $module = null, string|array|null $suffix = null)
 * @method static string getCachedModulesPath()
 *
 * @see ModuleService
 */
class Module extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return Module::class;
    }

}