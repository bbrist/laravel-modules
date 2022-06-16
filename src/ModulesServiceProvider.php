<?php

namespace Asoft\Modules;

use Asoft\Modules\Commands\ModuleConsoleMakeCommand;
use Asoft\Modules\Commands\ModuleControllerMakeCommand;
use Asoft\Modules\Commands\ModuleInitCommand;
use Asoft\Modules\Commands\ModuleModelMakeCommand;
use Asoft\Modules\Commands\ModuleProviderMakeCommand;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ModulesServiceProvider extends LaravelServiceProvider implements DeferrableProvider
{

    protected array $commands = [
        ModuleInitCommand::class,
        ModuleConsoleMakeCommand::class,
        ModuleProviderMakeCommand::class,
        ModuleControllerMakeCommand::class,
        ModuleModelMakeCommand::class,
    ];

    public function register()
    {
        collect($this->commands)->each(fn ($cmd) => $this->registerCommand($cmd));
    }

    /**
     * @param string $command
     * @return void
     */
    protected function registerCommand(string $command): void
    {
        Artisan::starting(fn ($artisan) => $artisan->resolve($command));
    }

    public function provides(): array
    {
        return $this->commands;
    }

}