<?php

namespace Modules\Base\Console;

use Illuminate\Support\Str;
use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Modules\Base\Helpers\GenerateConfigReader;
use Nwidart\Modules\Support\Stub as ParentStub;
use Modules\Base\Helpers\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeModelCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'model';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for the specified module.';

    public function handle() : int
    {
        parent::handle();

        $this->handleOptionalMigrationOption();
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
     * Create a proper migration name:
     * ProductDetail: product_details
     * Product: products
     * @return string
     */
    private function createMigrationName()
    {
        $pieces = preg_split('/(?=[A-Z])/', $this->argument('model'), -1, PREG_SPLIT_NO_EMPTY);

        $string = '';
        foreach ($pieces as $i => $piece) {
            if ($i+1 < count($pieces)) {
                $string .= strtolower($piece) . '_';
            } else {
                $string .= Str::plural(strtolower($piece));
            }
        }

        return $string;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of model will be created.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
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
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes.', null],
            ['migration', 'm', InputOption::VALUE_NONE, 'Flag to create associated migrations', null],
            ['e', null, InputOption::VALUE_NONE, 'Make model expandable', null],
            ['r', null, InputOption::VALUE_NONE, 'Make model repository', null],
        ];
    }

    /**
     * Create the migration file with the given model if migration flag was used
     */
    private function handleOptionalMigrationOption()
    {
        if ($this->option('migration') === true) {
            $migrationName = 'create_' . $this->createMigrationName() . '_table';
            $this->call('module:make-migration', ['name' => $migrationName, 'module' => $this->argument('module')]);
        }
    }

    /**
     * @return mixed
     */
    protected function getTemplateContents($proxy = false)
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        $data = [
            'NAME'              => $this->getModelName(),
            'FILLABLE'          => $this->getFillable(),
            'NAMESPACE'         => ($proxy)? $this->getProxyClassNameSpace($module):$this->getClassNamespace($module),
            'CLASS'             => $this->getClass(),
            'LOWER_NAME'        => $module->getLowerName(),
            'MODULE'            => $this->getModuleName(),
            'STUDLY_NAME'       => $module->getStudlyName(),
            'MODULE_NAMESPACE'  => $this->laravel['modules']->config('namespace'),
        ];
        $basePath = base_path() . '/Modules/Base/Console/stubs';
        if($proxy)
        {
            $modelStub = '/model-proxy.stub';
        }
        else
        {
            $modelStub = '/model.stub';
        }

        return  (new Stub($modelStub,$data,$basePath))->render();

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

        $pathConfig = 'model';
        if($proxy)
        {
            $pathConfig = $pathConfig .'-proxy';
        }

        $modelPath = GenerateConfigReader::read("base.paths.generator.".$pathConfig);

        return $path . $modelPath->getPath() . '/' . $this->getModelName() . '.php';
    }

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('model'));
    }

    /**
     * @return string
     */
    private function getFillable()
    {
        $fillable = $this->option('fillable');

        if (!is_null($fillable)) {
            $arrays = explode(',', $fillable);

            return json_encode($arrays);
        }

        return '[]';
    }



    public function getDefaultNamespace($proxy = false) : string
    {
        $module = $this->laravel['modules'];

        $namespace = $module->config('paths.generator.model.namespace') ?: $module->config('paths.generator.model.path', 'Entities');

        if($proxy)
        {
            $namespace = 'Proxies/'.$namespace;
        }

        return $namespace;
    }
}
