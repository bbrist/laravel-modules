<?php

namespace Bbrist\Modules;

use Bbrist\Modules\Facades\Module;

class ClearCompiledCommand extends \Illuminate\Foundation\Console\ClearCompiledCommand
{

    public function handle()
    {
        if (is_file($modulesPath = Module::getCachedModulesPath())) {
            @unlink($modulesPath);
        }

        parent::handle();
    }

}