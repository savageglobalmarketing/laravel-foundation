<?php

namespace Maxcelos\Foundation\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Symfony\Component\Finder\Finder;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array $bindings
     */
    public $bindings = [];

    /**
     * @var array $polices
     */
    protected $policies = [];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->injectPolicies();
        $this->registerPolicies();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepositories();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Auto discovery for binding models to policies
     */
    private function injectPolicies()
    {
        $policiesDir = module_path($this->moduleName, 'Policies');
        $modelsDir = module_path($this->moduleName, 'Models');

        if (!is_dir($modelsDir) || !is_dir($policiesDir)) {
            return;
        }

        $policyClasses = [];
        $policyFiles = Finder::create()->files()->in($policiesDir)->name('*.php');

        foreach ($policyFiles as $policyFile) {
            $policyName = $policyFile->getBasename('.php');
            $policyNamespace = $this->getModuleNamespace() . 'Policies\\' . $policyName;
            $policyClasses[$policyName] = $policyNamespace;
        }

        $modelFiles = Finder::create()->files()->in($modelsDir)->name('*.php');

        foreach ($modelFiles as $key => $modelFile) {
            $modelName = $modelFile->getBasename('.php');
            $modelNamespace = $this->getModuleNamespace() . 'Models\\' . $modelName;

            if (in_array($modelNamespace, array_keys($this->policies))) {
                continue;
            }

            $policy = array_filter($policyClasses, function ($namespace, $class) use ($modelName) {
                return $class == $modelName . 'Policy';
            }, ARRAY_FILTER_USE_BOTH);

            if (count($policy)) {
                $this->policies[$modelNamespace] = array_values($policy)[0];
            }
        }
    }

    /**
     * Auto discovery for binding repositories to contracts
     */
    private function registerRepositories()
    {
        $contractsDir = module_path($this->moduleName, 'Contracts');
        $repoDir = module_path($this->moduleName, 'Repositories');

        if (! file_exists($contractsDir) || ! file_exists($repoDir))
            return;

        $contractFiles = Finder::create()->files()->in($contractsDir)->name('*.php');

        $repoFiles = Finder::create()->files()->in($repoDir)->name('*.php');
        $repoClasses = [];

        foreach ($repoFiles as $repoFile) {
            $name = $repoFile->getBasename('.php');
            $repoNamespace = $this->getModuleNamespace() . 'Repositories\\' . $name;
            $repoClasses[$name] = $repoNamespace;
        }

        foreach ($contractFiles as $contractFile) {
            $name = $contractFile->getBasename('.php');
            $contractNamespace = $this->getModuleNamespace() . 'Contracts\\' . $name;

            if (! in_array($contractNamespace, array_keys($this->bindings))) {
                $repo = str_replace('Contract', 'Repository', $name);

                $repo = array_filter($repoClasses, function ($namespace, $class) use ($repo) {
                    return $class == $repo;
                }, ARRAY_FILTER_USE_BOTH);

                if (count($repo)) {
                    $this->app->bind($contractNamespace, array_values($repo)[0]);
                }
            }
        }
    }

    /**
     * Get module namespace from composer.json
     *
     * @return string|null
     */
    private function getModuleNamespace()
    {
        $psr4 = $this->app['modules']->findOrFail($this->moduleName)->getComposerAttr('autoload')['psr-4'];
        return array_key_first($psr4);
    }
}
