<?php

namespace Asoft\Modules\Commands;

use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class ModuleProviderMakeCommand extends ProviderMakeCommand
{
    use ModuleMakeTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:provider';

    /**
     * The name of the console command.
     *
     * This name is used to identify the command during lazy loading.
     *
     * @var string|null
     *
     * @deprecated
     */
    protected static $defaultName = 'module:provider';

    public function handle()
    {
        if (! $this->checkModuleExists()) {
            return Command::FAILURE;
        }

        return parent::handle();
    }

    protected function getCustomNamespace(): string
    {
        return Config::get('modules.dirs.providers', 'Providers');
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
            ['name', InputArgument::REQUIRED, 'The name of the provider'],
        ];
    }

}