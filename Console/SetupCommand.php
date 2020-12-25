<?php

namespace SavageGlobalMarketing\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'foundation:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assisted initial setup';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--provider' => 'SavageGlobalMarketing\Foundation\Providers\FoundationServiceProvider']);

        $setupOptions = [];

        $setupOptions['namespace'] = $this->ask('What should be the namespace for module classes?');
        $setupOptions['path'] = $this->ask('Witch path should be placed the modules source?', Str::studly($setupOptions['namespace']));
        $setupOptions['assetsPath'] = $this->ask('Witch path should be placed the modules assets?', Str::snake($setupOptions['path']));
        $setupOptions['vendor'] = $this->ask('What should be the vendor name?', Str::lower($setupOptions['namespace']));

        $this->updateSetupFile($setupOptions);
        $this->updateComposerFile($setupOptions);

        exec('rm ' . base_path('database/migrations/*'));

        $this->call('vendor:publish', [
            '--provider' => 'SavageGlobalMarketing\Foundation\Providers\FoundationServiceProvider',
            '--tag' => 'setup',
            '--force' => true
        ]);

        $this->call('pest:install');
        $this->call('telescope:install');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    private function updateSetupFile($options)
    {
        $file = base_path() . '/config/modules.php';

        $setup = file_get_contents($file);

        $setNamespace = "'namespace' => '{$options['namespace']}'";
        $path = "'modules' => base_path('{$options['path']}')";
        $assetsPath = "'assets' => public_path('{$options['assetsPath']}')";
        $vendor = "'vendor' => '{$options['vendor']}'";

        $setup = str_replace("'namespace' => 'SavageGlobalMarketing'", $setNamespace, $setup);
        $setup = str_replace("'modules' => base_path('SavageGlobalMarketing')", $path, $setup);
        $setup = str_replace("'assets' => public_path('SavageGlobalMarketing')", $assetsPath, $setup);
        $setup = str_replace("'vendor' => 'savageglobalmarketing'", $vendor, $setup);

        file_put_contents(base_path() . '/config/modules.php', $setup);
    }

    private function updateComposerFile($options)
    {
        $file = base_path().'/composer.json';

        $composer = file_get_contents($file);

        $original = '"App\\\": "app/"';
        $mod = $original.','.PHP_EOL;
        $mod .= "\t\t\t".'"';
        $mod .= Str::studly($options['namespace']).'\\\": "';
        $mod .= $options['path'].'/"';

        $composer = str_replace($original, $mod, $composer);

        file_put_contents($file, $composer);
    }
}
