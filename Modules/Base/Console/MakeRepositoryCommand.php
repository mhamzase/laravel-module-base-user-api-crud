<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Base\Helpers\Stub;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Modules\Base\Helpers\GenerateConfigReader;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeRepositoryCommand extends GeneratorCommand
{
    use ModuleCommandTrait;
    /**
     * The console command name.
     *
     * @var string
     */

    protected $name = 'module:make-repository';

    protected $argumentName = 'repository_name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Repositories';




    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['repository_name', InputArgument::REQUIRED, 'Repository name is required'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    public function getDefaultNamespace($proxy = false) : string
    {

        $module = $this->laravel['modules'];

        $namespace = $module->config('paths.generator.repositories.namespace') ?: $module->config('paths.generator.repositories.path', 'Repositories');

        if($proxy)
        {
            $namespace = 'Proxies/'.$namespace;
        }
        return $namespace;
    }

    /**
     * Execute the console command.
     */
    public function handle() : int
    {
        Parent::handle();
        if($this->option('e'))
        {
            $this->createProxyClass();
        }
        return 0;
    }


    private function createProxyClass()
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath(true));

        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        $contents = $this->getTemplateContents(true);

        try {
            $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;
            (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");
        }
    }

    /**
     * @return mixed
     */
    protected function getDestinationFilePath($proxy = false)
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $pathConfig = 'repository';
        if($proxy)
        {
            $pathConfig = $pathConfig .'-proxy';
        }

        $commandPath = GenerateConfigReader::read("base.paths.generator.".$pathConfig);
        return $path . $commandPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents($proxy = false)
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $data = [
            'NAMESPACE'    => ($proxy)? $this->getProxyClassNameSpace($module):$this->getClassNamespace($module),
            'CLASS'        => $this->getClass(),
        ];

        $stubPath = ($proxy)? '/repository-proxy.stub': '/repository.stub';

        return (new Stub($stubPath, $data, base_path() . '/Modules/Base/Console/stubs'))->render();
    }


    public function getProxyClassNameSpace($module)
    {
        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));


        $extra = str_replace('/', '\\', $extra);

        $namespace = $this->laravel['modules']->config('namespace');

        $namespace .= '\\' . $module->getStudlyName();

        $namespace .= '\\' . $this->getDefaultNamespace(true);

        $namespace .= '\\' . $extra;

        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['command', null, InputOption::VALUE_OPTIONAL, 'The terminal command that should be assigned.', null],
            ['e', null, InputOption::VALUE_NONE, 'Make repository expandable', null],
        ];
    }


    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('repository_name'));
    }
}
