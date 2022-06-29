<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class TestAPI extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'whf:test-api {--workers=} {--url=} {--timeout=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for testing the API endpoints';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        $process = new Process( [ 'rm', '-rf', storage_path( 'app/public/api-test-results.xml' ) ] );
        $process->run();
        $this->info( "Existing Test Results XML removed" );
//
        $process = new Process( [ 'rm', '-rf', storage_path( 'app/public/api-test-results.html' ) ] );
        $process->run();
        $this->info( "Existing Test Results HTML removed" );

        //Compile code
        $process = new Process( [ 'prance', '-compile', module_path( 'base', 'api-docs/simple.yaml' ), module_path( 'base', 'api-docs/compiled.yaml' ) ] );
        $process->run();
        $this->info( "Compiled yaml file for better testing" );

        $process = new Process( [
            'schemathesis',
            'run',
            '-w ' . $this->option( 'workers' ),
            '--hypothesis-deadline=' . $this->option( 'timeout' ) * 1000,
            '--junit-xml=' . storage_path( 'app/public/api-test-results.xml' ),
            '--base-url=' . $this->option( 'url' ),
            module_path( 'base', 'api-docs/openapi.json' )
        ] );
        $process->setTimeout( 3600 );
        $process->run();
        $this->info( "Test Results Produced" );

//        dd( 'junit-viewer --results=' . storage_path('app/public/api-test-results.xml') .  ' > ' .  storage_path('app/public/api-test-results.html'));
//        $process = new Process( [ 'junit-viewer --results=' . storage_path('app/public/api-test-results.xml') .  ' > ' .  storage_path('app/public/api-test-results.html') ] );
//        $process->run();
        exec( 'junit-viewer --results=' . storage_path( 'app/public/api-test-results.xml' ) . ' > ' . storage_path( 'app/public/api-test-results.html' ) );
        $this->info( "HTML Generated for test results" );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            [ 'workers', null, InputOption::VALUE_OPTIONAL, 'Default workers are', 20 ],
            [ 'url', null, InputOption::VALUE_OPTIONAL, 'Current laravel url', URL::to( '/' ) ],
            [ 'timeout', null, InputOption::VALUE_OPTIONAL, 'Time for single endpoint test in seconds', 15 ],
        ];
    }
}
