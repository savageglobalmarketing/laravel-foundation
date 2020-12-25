<?php

namespace SavageGlobalMarketing\Foundation\Providers;

use Dyrynda\Database\LaravelEfficientUuidServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder as ScoutBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use SavageGlobalMarketing\Foundation\Console\FactoryMakeCommand;
use SavageGlobalMarketing\Foundation\Console\GenericInstallCommand;
use SavageGlobalMarketing\Foundation\Console\MakeCrudCommand;
use SavageGlobalMarketing\Foundation\Console\SetupCommand;
use SavageGlobalMarketing\Foundation\Exceptions\Handler as ExceptionHandler;
use TeamTNT\Scout\TNTSearchScoutServiceProvider;

class FoundationServiceProvider extends AuthServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Foundation';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'foundation';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();

        $this->registerPolicies();

        $this->app->bind(ExceptionHandler::class, config('foundation.error_handler'));

        $this->registerMacros();

        // To be used while not implemented on nwidart/laravel-modules package
        $this->commands([
            FactoryMakeCommand::class,
            SetupCommand::class,
            MakeCrudCommand::class,
            GenericInstallCommand::class
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(LaravelEfficientUuidServiceProvider::class);
        $this->app->register(TNTSearchScoutServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/modules.php' => config_path('modules.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../Config/scout.php' => config_path('scout.php'),
        ], 'setup');

        $this->publishes([
            __DIR__ . '/../Config/app.php' => config_path('app.php'),
        ], 'setup');

        $this->publishes([
            __DIR__ . '/../Config/auth.php' => config_path('auth.php'),
        ], 'setup');

        $this->publishes([
            __DIR__ . '/../Config/cors.php' => config_path('cors.php'),
        ], 'setup');

        $this->publishes([
            __DIR__ . '/../Config/passport.php' => config_path('passport.php'),
        ], 'setup');

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', $this->moduleNameLower
        );
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
     * This macros provides json:api specs compliance.
     * Also normalizes paginate method for Scout and Eloquent
     * builders.
     */
    protected function registerMacros()
    {
        EloquentBuilder::macro(config('foundation.pagination.method_name'), function (int $maxResults = null, int $defaultSize = null) {
            $paginationConfig = config('foundation.pagination');

            $maxResults = $maxResults ?? $paginationConfig['max_results'];
            $defaultSize = $defaultSize ?? $paginationConfig['default_size'];
            $numberParameter = $paginationConfig['number_parameter'];
            $sizeParameter = $paginationConfig['size_parameter'];
            $paginationParameter = $paginationConfig['pagination_parameter'];

            $size = (int) request()->input($paginationParameter.'.'.$sizeParameter, $defaultSize);

            $size = $size > $maxResults ? $maxResults : $size;

            $paginator = $this
                ->paginate($size, ['*'], $paginationParameter.'.'.$numberParameter)
                ->setPageName($paginationParameter.'['.$numberParameter.']')
                ->appends(Arr::except(request()->input(), $paginationParameter.'.'.$numberParameter));

            if (!is_null(config('foundation.pagination.base_url'))) {
                $paginator->setPath(config('foundation.pagination.base_url'));
            }

            return $paginator;
        });

        ScoutBuilder::macro(config('foundation.pagination.method_name'), function (int $maxResults = null, int $defaultSize = null) {
            $paginationConfig = config('foundation.pagination');

            $maxResults = $maxResults ?? $paginationConfig['max_results'];
            $defaultSize = $defaultSize ?? $paginationConfig['default_size'];
            $numberParameter = $paginationConfig['number_parameter'];
            $sizeParameter = $paginationConfig['size_parameter'];
            $paginationParameter = $paginationConfig['pagination_parameter'];

            $size = (int) request()->input($paginationParameter.'.'.$sizeParameter, $defaultSize);

            $size = $size > $maxResults ? $maxResults : $size;

            $paginator = $this
                ->paginate($size, $paginationParameter.'.'.$numberParameter)
                ->setPageName($paginationParameter.'['.$numberParameter.']')
                ->appends(Arr::except(request()->input(), $paginationParameter.'.'.$numberParameter))->setPath(null);

            if (!is_null(config('foundation.pagination.base_url'))) {
                $paginator->setPath(config('foundation.pagination.base_url'));
            }

            return $paginator;
        });
    }
}
