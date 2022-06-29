<?php

namespace Modules\Base\Console;

use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Illuminate\Support\Str;
use Modules\Base\Helpers\GenerateConfigReader;
use Modules\Base\Helpers\Stub;
use Nwidart\Modules\Support\Stub as ParentStub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Nwidart\Modules\Commands\GeneratorCommand;

class MakeControllerCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'controller';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful controller for the specified module.';

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath($proxy = false)
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $pathConfig = 'controller';
        if($proxy)
        {
            $pathConfig = $pathConfig .'-proxy';
        }

        $controllerPath = GenerateConfigReader::read("base.paths.generator.".$pathConfig);

        return $path . $controllerPath->getPath() . '/' . $this->getControllerName() . '.php';
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
     * @return string
     */
    protected function getTemplateContents($proxy = false)
    {

        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $data =  [
            'MODULENAME'        => $module->getStudlyName(),
            'CONTROLLERNAME'    => $this->getControllerName(),
            'NAMESPACE'         => $module->getStudlyName(),
            'CLASS_NAMESPACE'   => ($proxy)? $this->getProxyClassNameSpace($module):$this->getClassNamespace($module),
            'CLASS'             => $this->getControllerNameWithoutNamespace(),
            'LOWER_NAME'        => $module->getLowerName(),
            'MODULE'            => $this->getModuleName(),
            'NAME'              => $this->getModuleName(),
            'STUDLY_NAME'       => $module->getStudlyName(),
            'MODULE_NAMESPACE'  => $this->laravel['modules']->config('namespace'),
        ];

        $basePath = base_path() . '/Modules/Base/Console/stubs';
        if($proxy)
        {
            return  (new Stub('/controller-proxy.stub',$data, $basePath))->render();
        }
        else
        {
            return  (new ParentStub($this->getStubName(),$data,$basePath))->render();
        }
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['controller', InputArgument::REQUIRED, 'The name of the controller class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate a plain controller', null],
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
            ['e', null, InputOption::VALUE_NONE, 'Make controller expandable', null],
        ];
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->argument('controller'));

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return array|string
     */
    private function getControllerNameWithoutNamespace()
    {
        return class_basename($this->getControllerName());
    }

    public function getDefaultNamespace($proxy = false) : string
    {
        $module = $this->laravel['modules'];

        $namespace = $module->config('paths.generator.controller.namespace') ?: $module->config('paths.generator.controller.path', 'Http/Controllers');

        if($proxy)
        {
            $namespace = 'Proxies/'.$namespace;
        }

        return $namespace;
    }



    /**
     * Get the stub file name based on the options
     * @return string
     */
    private function getStubName()
    {
        if ($this->option('plain') === true) {
            $stub = '/controller-plain.stub';
        } elseif ($this->option('api') === true) {
            $stub = '/controller-api.stub';
        }
        else {
            $stub = '/controller.stub';
        }

        return $stub;
    }
}

