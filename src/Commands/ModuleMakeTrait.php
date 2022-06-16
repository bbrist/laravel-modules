<?php

namespace Bbrist\Modules\Commands;

use Bbrist\Modules\Facades\Module;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * Note: While it is bad practice to use a trait to override functionality, this provides
 * the most straightforward way to proxy the "Make" commands for Modules.
 * We may want to revisit this solution at a later time to determine if there is a
 * better way to do this.
 */
trait ModuleMakeTrait
{

    abstract public function argument($key = null);

    abstract public function error($string, $verbosity = null);

    abstract public function getCustomNamespace(): string;

    public function checkModuleExists(): bool
    {
        $module = $this->argument("module");
        if (! Module::exists($module)) {
            $this->error("Module $module does not exist. Initialize it using the module:init command");
            return false;
        }

        return true;
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @return string
     */
    protected function getPath($name): string
    {
        $module = $this->argument('module');

        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        $name = Str::replaceFirst($module, '', $name);

        $name .= '.php';

        return Module::path($module, explode("\\", $name));
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->getNamedModuleNamespace(),
            $this->getCustomNamespace(),
        ]);
    }

    protected function getNamedModuleNamespace(): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            rtrim($this->rootNamespace(), '\\'),
            $this->argument('module'),
        ]);
    }

    protected function getModulesDirectory(): string
    {
        return Config::get('modules.module.dir', 'modules');
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        return Config::get('modules.module.namespace', 'Modules') . "\\";
    }

}