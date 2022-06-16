<?php

namespace Asoft\Modules\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class ModuleControllerMakeCommand extends ControllerMakeCommand
{
    use ModuleMakeTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:controller';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'module:controller';

    public function handle()
    {
        if (! $this->checkModuleExists()) {
            return Command::FAILURE;
        }

        return parent::handle();
    }

    protected function getCustomNamespace(): string
    {
        return Config::get('modules.dirs.controllers', 'Controllers');
    }

    protected function buildClass($name)
    {
        $rootNamespace = $this->rootNamespace();

        $replace = [];
        $replace["use {$rootNamespace}Http\Controllers\Controller;\n"] = "use App\\Http\\Controllers\\Controller;\n";

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the module'],
            ['name', InputArgument::REQUIRED, 'The name of the controller'],
        ];
    }

}