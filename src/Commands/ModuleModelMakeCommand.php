<?php

namespace Bbrist\Modules\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class ModuleModelMakeCommand extends ModelMakeCommand
{
    use ModuleMakeTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:model';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'module:model';

    public function handle()
    {
        if (! $this->checkModuleExists()) {
            return Command::FAILURE;
        }

        return parent::handle();
    }

    protected function getCustomNamespace(): string
    {
        return Config::get('modules.dirs.models', 'Models');
    }

    protected function module(): string
    {
        return $this->argument('module');
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('module:controller', array_filter([
            'module' => $this->module(),
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
            '--api' => $this->option('api'),
            '--requests' => $this->option('requests') || $this->option('all'),
        ]));
    }

    /**
     * Create a policy file for the model.
     *
     * @return void
     */
    // TODO: To add later when implementing module:policy command
    /*protected function createPolicy()
    {
        $policy = Str::studly(class_basename($this->argument('name')));

        $this->call('module:policy', [
            'module' => $this->module(),
            'name' => "{$policy}Policy",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }*/

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the module'],
            ['name', InputArgument::REQUIRED, 'The name of the command'],
        ];
    }

}