<?php

namespace Tiagoandrepro\Lmsquid\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Package Manager with Inertiajs';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->installInertiaVueStack();
        $this->updateNodePackages(function ($packages) {
            return [
                    'postcss' => '^8.2.1',
                    'postcss-import' => '^12.0.1',
                ] + $packages;
        });



        copy(__DIR__.'/../config.php', config_path('modules.php'));

        copy(__DIR__.'/../webpack.mix.js', base_path('webpack.mix.js'));

        copy(__DIR__.'/../resources/views/app.blade.php', resource_path('views/app.blade.php'));
        copy(__DIR__.'/../resources/js/app.js', resource_path('js/app.js'));

        if(!(new Filesystem)->exists(resource_path('js/Pages'))){
            (new Filesystem)->makeDirectory(resource_path('js/Pages'));
        }



        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    /**
     * Install the Inertia Vue Breeze stack.
     *
     * @return void
     */
    protected function installInertiaVueStack()
    {
        // Install Inertia...
        $this->requireComposerPackages('inertiajs/inertia-laravel:^0.4.3', 'tightenco/ziggy:^1.0');

        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                    '@inertiajs/inertia' => '^0.10.0',
                    '@inertiajs/inertia-vue3' => '^0.5.1',
                    '@inertiajs/progress' => '^0.2.6',
                    '@vue/compiler-sfc' => '^3.0.5',
                    'autoprefixer' => '^10.2.4',
                    'postcss' => '^8.2.13',
                    'postcss-import' => '^14.0.1',
                    'vue' => '^3.0.5',
                    'vue-loader' => '^16.1.2',
                ] + $packages;
        });

    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        //$composer = 'global';
//
        //if ($composer !== 'global') {
        //    $command = ['php', 'global', 'require'];
        //}

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Delete the "node_modules" directory and remove the associated lock files.
     *
     * @return void
     */
    protected static function flushNodeModules()
    {
        tap(new Filesystem, function ($files) {
            $files->deleteDirectory(base_path('node_modules'));

            $files->delete(base_path('yarn.lock'));
            $files->delete(base_path('package-lock.json'));
        });
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

}
