<?php

namespace Modules\Base\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Modules\Base\Helpers\GenerateConfigReader;
use Nwidart\Modules\Support\Stub as ParentStub;
use Modules\Base\Helpers\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class MakeMiddlewareCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new middleware class for the specified module.';

    public function getDefaultNamespace($proxy = false) : string
    {
        $module = $this->laravel['modules'];
        $namespace =  $module->config('paths.generator.filter.namespace') ?: $module->config('paths.generator.filter.path', 'Http/Middleware');
        if($proxy)
        {
            $namespace = 'Proxies/'.$namespace;
        }
        return $namespace;
    }



    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the command.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
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
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['e', null, InputOption::VALUE_NONE, 'Make controller expandable', null],
        ];
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents($proxy = false)
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $data = [
            'NAMESPACE' => ($proxy)? $this->getProxyClassNameSpace($module):$this->getClassNamespace($module),
            'CLASS'     => $this->getClass(),
        ];

        $basePath = base_path() . '/Modules/Base/Console/stubs';
        if($proxy)
        {
            return  (new Stub('/middleware-proxy.stub',$data, $basePath))->render();
        }
        else
        {
            return  (new ParentStub('/middleware.stub',$data,$basePath))->render();
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
     * @return mixed
     */
    protected function getDestinationFilePath($proxy = false)
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $pathConfig = 'filter';
        if($proxy)
        {
            $pathConfig = $pathConfig .'-proxy';
        }

        $middlewarePath = GenerateConfigReader::read("base.paths.generator.".$pathConfig);

        return $path . $middlewarePath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    private function getFileName()
    {
        return Str::studly($this->argument('name'));
    }
}
