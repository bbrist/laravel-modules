<?php

namespace Async\Modules;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleService
{

    public function exists(string $module, bool $strict = true): bool
    {
        $dir = $this->getModuleDirectory($module);

        if (! File::exists($dir)) {
            return false;
        }

        if (! $strict) {
            return true;
        }

        $filename = $this->getModuleFilename($module);
        return File::exists($filename);
    }

    public function path(string $module, string|array|null $path = null): string
    {
        $root = $this->getModuleDirectory($module);

        return $this->createPath(DIRECTORY_SEPARATOR, [ base_path(), $root ], $path);
    }

    public function getModuleDirectory(?string $module = null): string
    {
        $root = Config::get('modules.module.dir', 'modules');

        if ($module === null) {
            return $root;
        }

        return implode(DIRECTORY_SEPARATOR, [
            $root,
            $module
        ]);
    }

    public function getModuleFilename(string $module): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->getModuleDirectory($module),
            Config::get('modules.module.file', 'module.php')
        ]);
    }

    public function getModuleNamespace(?string $module = null, string|array|null $path = null): string
    {
        $root = Config::get('modules.module.namespace', 'Modules');
        return $this->createPath('\\', $module === null ? [ $root ] : [ $root, $module ], $path);
    }

    public function createPath(string $separator, array $root, string|array|null $path = null): string
    {
        $parts = $root;

        if (gettype($path) === 'string') {
            $parts[] = $path;
        }
        else if (gettype($path) === 'array') {
            array_push($parts, ...$path);
        }

        return implode($separator, $parts);
    }

    public function getCachedModulesPath(): string
    {
        $key = 'APP_MODULES_CACHE';
        $default = 'cache/modules.php';
        $absoluteCachePathPrefixes = ['/', '\\'];

        if (is_null($env = Env::get($key))) {
            return App::bootstrapPath($default);
        }

        return Str::startsWith($env, $absoluteCachePathPrefixes) ? $env : App::basePath($env);
    }

}