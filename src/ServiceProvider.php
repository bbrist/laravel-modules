<?php

namespace Async\Modules;

use Async\Modules\Facades\Module;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ClearCompiledCommand as ClearCompiledCommandAlias;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ServiceProvider extends LaravelServiceProvider
{

    /**
     * Register Services
     *
     * @return void
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/modules.php';
        $this->mergeConfigFrom($configPath, 'modules');

        $this->app->singleton(Module::class, ModuleService::class);
        $this->app->extend(ClearCompiledCommandAlias::class, function () {
            return new ClearCompiledCommand();
        });

        $dir = App::basePath(Config::get('modules.module.dir', 'modules'));
        $filename = Config::get('modules.module.file', 'module.php');
        $namespace = Config::get('modules.module.namespace', 'Modules');
        $depth = Config::get('modules.module.search_depth', 1);

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }

        $this->modules($dir, $filename, $depth)
            ->each(fn ($module) => $this->registerModule($module, $namespace));
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $configPath = __DIR__ . '/../config/modules.php';
        $this->publishes([ $configPath => $this->getConfigPath() ], 'modules');

        //
    }

    protected function getConfigPath(): string
    {
        return config_path('modules.php');
    }

    /**
     * Resolve Module Configuration
     *
     * @param string $dir
     * @param string $filename
     * @param int $depth
     * @return Collection
     */
    protected function modules(string $dir, string $filename, int $depth): Collection
    {
        $depth = $depth + 1; // Adjust for config file readability

        return $this->files($dir, fn ($finder) => $finder->name($filename)->depth("<$depth"), function ($file) use ($dir) {
            $relative = trim(Str::replace($dir, '', $file->getPath()), DIRECTORY_SEPARATOR);

            return [
                'dir' => $file->getPath(),
                'path' => explode(DIRECTORY_SEPARATOR, $relative),
                'module' => include $file->getPathname(),
            ];
        });
    }

    /**
     * Register a module configuration
     *
     * @param array $data
     * @param string $namespace
     * @return void
     */
    protected function registerModule(array $data, string $namespace): void
    {
        $namespace = implode("\\", array_merge([$namespace], Arr::get($data, 'path', [])));
        $module = Arr::get($data, 'module', []);
        $providers = Arr::get($module, 'providers', []);
        $aliases = Arr::get($module, 'aliases', []);

        (new ProviderRepository(App::getFacadeApplication(), new Filesystem, Module::getCachedModulesPath()))->load($providers);

        collect($aliases)->each(fn ($alias, $name) => App::getFacadeApplication()->alias($alias, $name));

        $directory = $data['dir'];
        $this->registerCommands($namespace, $directory);
        $this->registerRoutes($directory);
    }

    /**
     * Register commands in a given directory
     *
     * @param string $namespace
     * @param string $dir
     * @return void
     */
    protected function registerCommands(string $namespace, string $dir): void
    {
        $path = Config::get('modules.dirs.commands', 'Commands');
        $dir = $this->getDirectoryPath($dir, $path);

        if (!file_exists($dir)) {
            return;
        }

        $this->files($dir, function ($file) use ($namespace, $path, $dir) {
                $relative = str_replace(['/', '.php'], ['\\', ''], Str::after($file->getRealPath(), realpath($dir).DIRECTORY_SEPARATOR));

                return implode('\\', [ $namespace, $path, $relative ]);
            })
            ->filter(fn (string $command) => is_subclass_of($command, Command::class))
            ->reject(fn (string $command) => (new ReflectionClass($command))->isAbstract())
            ->each(fn (string $command) => $this->registerCommand($command));
    }

    /**
     * @param string $command
     * @return void
     */
    protected function registerCommand(string $command): void
    {
        Artisan::starting(fn ($artisan) => $artisan->resolve($command));
    }

    /**
     * Register route files in a given directory
     *
     * @param string $dir
     * @return void
     */
    protected function registerRoutes(string $dir): void
    {
        $path = Config::get('modules.dirs.routes', 'Routes');
        $dir = $this->getDirectoryPath($dir, $path);

        if (!file_exists($dir)) {
            return;
        }

        $this->files($dir, function ($file){
            return $file->getRealPath();
        })->each(fn ($filepath) => include $filepath);
    }

    /**
     * Format a directory path using the directory separator, base directory, and path
     *
     * @param string $dir
     * @param string $path
     * @return string
     */
    protected function getDirectoryPath(string $dir, string $path): string
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Iterate over files in a directory and return the collection
     *
     * @param string $dir
     * @param callable $modifier
     * @param callable|null $callback
     * @return Collection
     */
    protected function files(string $dir, callable $modifier, callable $callback = null): Collection
    {
        $items = collect();

        if ($callback === null) {
            $callback = $modifier;
            $modifier = null;
        }

        $finder = (new Finder())->in($dir)->files();

        if ($modifier !== null && is_callable($modifier)) {
            $finder = $modifier($finder);
        }

        if (!is_callable($callback)) {
            return $items;
        }

        foreach($finder as $file) {
            $items->push($callback($file));
        }

        return $items;
    }

}