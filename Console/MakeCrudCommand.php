<?php

namespace SavageGlobalMarketing\Foundation\Console;

use SavageGlobalMarketing\Foundation\Fillable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeCrudCommand extends Command
{
    use ModuleCommandTrait;

    protected $name = 'module:make-crud';

    protected $description = 'Creates a complete CRUD with Repository Pattern';

    private array $fileProperties = [
        'with-model'          => ['file' => 'model', 'suffix' => 'Model'],
        'with-migration'      => ['file' => '', 'suffix' => ''],
        'with-request'        => ['file' => 'request', 'suffix' => 'Request'],
        'with-interface'      => ['file' => 'contracts', 'suffix' => 'Contract'],
        'with-repository'     => ['file' => 'repository', 'suffix' => 'Repository'],
        'with-transformation' => ['file' => 'resource', 'suffix' => 'Resource'],
        'with-service'        => ['file' => 'services', 'suffix' => 'Service'],
        'with-controller'     => ['file' => 'controller', 'suffix' => 'Controller'],
        'with-policy'         => ['file' => 'policies', 'suffix' => 'Policy'],
        'with-factory'        => ['file' => 'factory', 'suffix' => 'Factory'],
    ];

    private Module $module;

    private Fillable $fillable;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = app('modules')->findOrFail($this->argument('module'));

        $this->fillable = new Fillable($this->option('fillable') ?? '');

        array_walk($this->fileProperties, function ($prop, $option) {
            if ($this->option($option) || $this->option('full')) {
                if ($option === 'with-migration') {
                    $this->handleOptionalMigrationOption();
                } elseif ($option === 'with-service') {
                    $actions = ['query', 'create', 'get', 'update', 'destroy'];

                    foreach ($actions as $action) {
                        $this->createFiles('service-' . $action, $prop['suffix'], Str::studly($action));
                    }
                } else {
                    $this->createFiles($prop['file'], $prop['suffix']);
                }
            }
        });

        $this->addRoute();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'Model name'],
            ['module', InputArgument::REQUIRED, 'Module name'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes of model', null],
            ['with-model', 'm', InputOption::VALUE_NONE, 'Flag to create associated model', null],
            ['with-migration', 'd', InputOption::VALUE_NONE, 'Flag to create associated model migrations', null],
            ['with-request', 'e', InputOption::VALUE_NONE, 'Flag to create associated request', null],
            ['with-interface', 'i', InputOption::VALUE_NONE, 'Flag to create associated contract', null],
            ['with-repository', 'r', InputOption::VALUE_NONE, 'Flag to create associated repository', null],
            ['with-transformation', 't', InputOption::VALUE_NONE, 'Flag to create associated transformation', null],
            ['with-service', 's', InputOption::VALUE_NONE, 'Flag to create associated service', null],
            ['with-controller', 'c', InputOption::VALUE_NONE, 'Flag to create associated controller', null],
            ['with-policy', 'p', InputOption::VALUE_NONE, 'Flag to create associated policy', null],
            ['with-factory', 'f', InputOption::VALUE_NONE, 'Flag to create associated factories', null],
            ['full', null, InputOption::VALUE_NONE, 'Flag to create all associated components (Except migration)', null],
            ['force', null, InputOption::VALUE_NONE, 'Override existing files', null],
        ];
    }

    /**
     * Generates template content.
     *
     * @param $suffix
     * @param $filePath
     * @param $stub
     *
     * @return string
     */
    protected function getTemplateContents($suffix, $filePath, $stub, $prefix = ''): string
    {
        return (new Stub('/'.strtolower($stub).'.stub', [
            'CLASS'             => $this->argument('model').$suffix,
            'NAME'              => $this->argument('model'),
            'CAMEL_NAME'        => Str::camel($this->argument('model')),
            'SNAKE_NAME'        => Str::snake($this->argument('model')),
            'LOWER_NAME'        => Str::camel($this->argument('model')),
            'FILLABLE'          => $this->fillable->getFillablePlain() ?? '',
            'EMPTY_FILLABLE'    => $this->fillable->getFillableEmpty() ?? '',
            'RESOURCE_FILLABLE' => $this->fillable->getFillableForResource() ?? '',
            'RESOURCE_CLASS'    => config('foundation.user_resource_class'),
            'MODULE'            => $this->module->getStudlyName(),
            'MODULE_NAMESPACE'  => config('modules.namespace').'\\'.$this->module->getStudlyName(),
            'USER_CLASS'        => config('auth.providers.users.model'),
            'COMMENT_POLICY'    => config('foundation.bypass_policy') ? 'true; //' : '',
            'NAMESPACE'         => $this->getClassNamespace($filePath),
            'MODEL_CLASS'        => $this->getModelClass(),
            'MODEL_NAME'        => $this->argument('model'),
            'ACTION'            => Str::studly($prefix),
        ]))->render();
    }

    /**
     * Generates files in appropriate path.
     *
     * @param string $file
     * @param string $suffix
     * @param string $prefix
     */
    private function createFiles($file, $suffix, $prefix = ''): void
    {
        $path = GenerateConfigReader::read($file)->getPath();
        $stub = strtolower($suffix);
        $suffix = str_replace('Model', '', $suffix);
        $contents = $this->getTemplateContents($suffix, $path, $stub, $prefix);

        $modelName = $this->argument('model');

        if ($suffix === 'Service') {
            $path .= '/'.$modelName;
        }

        $path = $this->module->getPath().'/'.$path.'/'.$prefix.$modelName.$suffix.'.php';

        $dir = dirname($path);

        if (!$this->laravel['files']->isDirectory($dir)) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        try {
            (new FileGenerator($path, $contents))->withFileOverwrite($this->option('force'))->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $exception) {
            $this->warn("File : {$path} already exists.");
        }
    }

    /**
     * Get class namespace.
     *
     * @param string $path
     *
     * @return string
     */
    private function getClassNamespace($path): string
    {
        $path = str_replace('/', '\\', $path);

        $namespace = array_keys($this->module->getComposerAttr('autoload')['psr-4'])[0];

        return $namespace.Str::studly($path);
    }

    private function addRoute(): void
    {
        $file = $this->module->getPath().'/'.config('modules.stubs.files.routes/api');

        $routesContent = file_get_contents($file);

        $routeName = strtolower(config('modules.namespace'));
        $routeURI = strtolower(Str::plural($this->argument('model')));

        $newRoute = "Route::apiResource('{$routeURI}', '{$this->argument('model')}Controller', ['as' => '{$routeName}']);";
        $routesContent = str_replace($newRoute, '', $routesContent);

        if (!Str::contains($routesContent, $newRoute)) {
            $routesContent .= PHP_EOL.$newRoute.PHP_EOL;
            file_put_contents($file, $routesContent);
        }
    }

    /**
     * Create the migration file with the given
     * model if migration flag was used.
     */
    private function handleOptionalMigrationOption(): void
    {
        if (! $this->option('with-migration'))
            return;

        $pieces = preg_split('/(?=[A-Z])/', $this->argument('model'), -1, PREG_SPLIT_NO_EMPTY);

        $last = array_pop($pieces);
        $pieces[] = Str::plural($last);
        $migrationName = 'create_'.strtolower(implode('_', $pieces)).'_table';

        $fillable = $this->fillable->getReverseFillablePlain();
        $this->call('module:make-migration', ['--fields' => $fillable, 'name' => $migrationName, 'module' => $this->argument('module')]);
    }


    /**
     * Get model class name.
     *
     * @return string
     */
    public function getModelClass()
    {
        $model = null;

        $module = $this->laravel['modules']->findOrFail($this->getModuleName());
        $modules = $this->laravel['modules'];

        $namespace = $this->laravel['modules']->config('namespace');

        $namespace .= '\\' . $module->getStudlyName();

        $namespace .= '\\' .  $modules->config('paths.generator.model.path', 'Entities');

        $namespace .= '\\' .  str_replace('Factory', '', $this->argument('model'));

        return $namespace ?: 'Illuminate\\Database\\Eloquent\\Model';
    }


    /**
     * Get model class name.
     *
     * @return string
     */
    public function getModelName()
    {
        return class_basename($this->getModelClass());
    }
}
