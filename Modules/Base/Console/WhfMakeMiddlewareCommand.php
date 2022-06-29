<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Modules\Base\Console\MakeMiddlewareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WhfMakeMiddlewareCommand extends MakeMiddlewareCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'whf:make-middleware';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() : int
    {
        $this->call('module:make-middleware', [
            'name' => $this->argument('name'),
            'module' => $this->getModuleName(),
            '--e' => true,
        ]);

        return 0;
    }

}
