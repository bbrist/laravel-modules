<?php

namespace Bbrist\Modules\Commands;

use Bbrist\Modules\Facades\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class ModuleInitCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:init {name} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name = $this->argument("name");
        if (Module::exists($name) && ! $this->option('force')) {
            $this->warn("Module $name already exists");
            return 1;
        }

        $this->initModule($name);
        $this->createRoutesFile($name);

        $this->info("Initialized new module: $name");
        return 0;
    }

    protected function initModule(string $name): void
    {
        $directory = Module::getModuleDirectory($name);
        $filename = Module::getModuleFilename($name);

        File::ensureDirectoryExists($directory, 0777);
        File::copy(__DIR__ . "/../../files/module.php", $filename);

    }

    protected function createRoutesFile(string $module): void
    {
        $controllers = Config::get('modules.dirs.controllers', 'Controllers');

        $stub = File::get(__DIR__ . "/../../stubs/routes.stub");
        $namespace = Module::getModuleNamespace($module, explode(DIRECTORY_SEPARATOR, $controllers));

        $stub = str_replace(['DummyNamespace', '{{ namespace }}', '{{namespace}}'], $namespace, $stub);

        $directory = Module::path($module, Config::get('modules.dirs.routes', 'Routes'));
        $path = implode(DIRECTORY_SEPARATOR, [ $directory, "routes.php" ]);

        File::ensureDirectoryExists($directory, 0777);
        File::put($path, $stub);
    }

}