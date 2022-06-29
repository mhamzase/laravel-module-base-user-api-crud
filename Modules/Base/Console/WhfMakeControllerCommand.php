<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Modules\Base\Console\MakeControllerCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WhfMakeControllerCommand extends MakeControllerCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'whf:make-controller';

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
    public function handle() : int
    {
        $this->call('module:make-controller', [
            'controller' => $this->argument('controller'),
            'module' => $this->getModuleName(),
            '--e' => true,
        ]);

        return 0;
    }

}
