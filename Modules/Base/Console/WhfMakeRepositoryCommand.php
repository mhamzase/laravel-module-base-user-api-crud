<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Modules\Base\Console\MakeRepositoryCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WhfMakeRepositoryCommand extends MakeRepositoryCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'whf:make-repository';


    public function handle() : int
    {
        $this->call('module:make-repository', [
            'repository_name' => $this->argument('repository_name'),
            'module' => $this->getModuleName(),
            '--e' => true,
        ]);

        return 0;
    }

}
