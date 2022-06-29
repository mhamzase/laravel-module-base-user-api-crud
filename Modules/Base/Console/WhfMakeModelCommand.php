<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Modules\Base\Console\MakeModelCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WhfMakeModelCommand extends MakeModelCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'whf:make-model';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() : int
    {
        $this->call('module:make-model', [
            'model' => $this->argument('model'),
            'module' => $this->getModuleName(),
            '--e' => true,
        ]);

        return 0;
    }

}
