<?php

namespace SavageGlobalMarketing\Foundation\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Cache;

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

    protected string $modulePath = __DIR__ . '/../';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->injectPolicies();
        $this->registerPolicies();
        $this->registerRepositories();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, config('foundation.developers'));
        });
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
        $modelPoliciesToBind = Cache::rememberForever($this->moduleName . '_policies_list', function () {
            return $this->getModelPoliciesList();
        });

        if (! $modelPoliciesToBind)
            return;

        foreach ($modelPoliciesToBind as $model => $policy) {
            $this->policies[$model] = $policy;
        }
    }

    protected function getModelPoliciesList()
    {
        $modelPoliciesToBind = [];

        $policiesDir =  $this->modulePath . '/Policies';
        $modelsDir =  $this->modulePath . '/Models';

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
                $modelPoliciesToBind[$modelNamespace] = array_values($policy)[0];
            }
        }

        return $modelPoliciesToBind;
    }

    /**
     * Auto discovery for binding repositories to contracts
     */
    private function registerRepositories()
    {
        $reposToBind = Cache::rememberForever($this->moduleName . '_repo_list', function () {
            return $this->getRepoContractsList();
        });

        if (! $reposToBind)
            return;

        foreach ($reposToBind as $contract => $repo) {
            $this->app->bind($contract, $repo);
        }
    }

    protected function getRepoContractsList()
    {
        $reposToBind = [];

        $contractsDir =  $this->modulePath . '/Contracts';
        $repoDir = $this->modulePath . '/Repositories';

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
                    $reposToBind[$contractNamespace] = array_values($repo)[0];
                }
            }
        }

        return $reposToBind;
    }

    /**
     * Get module namespace from composer.json
     *
     * @return string|null
     */
    protected function getModuleNamespace()
    {
        return Cache::rememberForever($this->moduleName . '_namespace', function () {
            $psr4 = $this->app['modules']->findOrFail($this->moduleName)->getComposerAttr('autoload')['psr-4'];
            return array_key_first($psr4);
        });
    }
}
