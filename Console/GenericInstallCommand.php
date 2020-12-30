<?php

namespace SavageGlobalMarketing\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenericInstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'foundation:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run installation steps';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('key:generate');
        $this->call('migrate');
        $this->call('module:seed');
        $this->call('permission:migrate');

        exec('rm public/storage');
        $this->call('storage:link');

        exec('chmod -R 777 storage/');
        exec('chmod -R 777 bootstrap/');

        Artisan::call('passport:install');
        $output = Artisan::output();
        $output = explode('Client ID: ', $output);
        $output = explode('Client secret: ', $output[1]);

        $id = trim($output[0]);
        $secret = explode("\n", $output[1])[0];

        $env = file_get_contents('.env');
        $env = str_replace('PASSPORT_PERSONAL_ACCESS_CLIENT_ID=', 'PASSPORT_PERSONAL_ACCESS_CLIENT_ID='.$id, $env);
        $env = str_replace('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=', 'PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET='.$secret, $env);

        file_put_contents('.env', $env);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            //['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            //['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
