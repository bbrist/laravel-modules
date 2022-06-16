<?php

namespace Async\Modules;

use Async\Modules\Commands\ModuleConsoleMakeCommand;
use Async\Modules\Commands\ModuleControllerMakeCommand;
use Async\Modules\Commands\ModuleInitCommand;
use Async\Modules\Commands\ModuleModelMakeCommand;
use Async\Modules\Commands\ModuleProviderMakeCommand;
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