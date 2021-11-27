<?php

namespace Tiagoandrepro\Lmsquid\Providers;

use Inertia\Inertia;
use Tiagoandrepro\Lmsquid\Console\InstallCommand;
use Tiagoandrepro\Lmsquid\Interfaces\ModuleRegister;
use Tiagoandrepro\Lmsquid\PackageManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PackageManagerServiceProvider extends ServiceProvider
{

    protected $modulesConfiguration = [];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*Inertia::share([
            'flash' => [
                'message' => fn() => request()->session()->get('message')
            ]
        ]);*/
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->bootModules();

        $this->loadRoutesFrom(__DIR__.'/../routes.php');

        $this->registerBladeDirective();
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class
            ]);
        }
    }

    protected function bootModules()
    {
        if (config('modules.enabled')) {
            foreach ($modules = config('modules.enabled') as $moduleClass) {

                $instance = app($moduleClass);

                if (!$instance instanceof ModuleRegister) {
                    throw new \Exception("A classe {$moduleClass} não é um módulo.");
                }

                if (!$instance->depends()) {
                    $this->modulesConfiguration[] = $instance->configure();
                    continue;
                }

                foreach ($instance->depends() as $dependencyClass) {
                    if (!in_array($dependencyClass, $modules)) {
                        throw new \Exception("Modulo {$moduleClass} depends from {$dependencyClass}.");
                    }
                }

                $this->modulesConfiguration[] = $instance->configure();
            }
        }
    }

    protected function registerBladeDirective()
    {
        $enabledModules = json_encode($this->modulesConfiguration, true);
        Blade::directive('enabledModules', fn() => "<script>window.applicationModules = $enabledModules</script>");
    }
}
