<?php


namespace Modules\Base\Proxies;


use App\Basic;
use App\BasicFinal;
use App\BasicTwo;
use Illuminate\Support\Facades\App;
use Nwidart\Modules\Facades\Module;

class LoadProxy {
    protected $extendables = [
        'Entities'  =>  'Entities',
        'Http/Controllers'  =>  'Http\Controllers',
        'Http/Middleware'  =>  'Http\Middleware',
        'Repositories'  =>  'Repositories',
        'Http/Requests'  =>  'Http\Requests',
        'Transformers'  =>  'Transformers',
    ];

    protected $baseModuleName = false;
    protected $classExtensions = [];
    protected $extensionMap = [];
    protected $extendedClasses = [];

    public static function init($baseModuleName) {
        $selfObj = new self;
        $selfObj->baseModuleName = $baseModuleName;
        $selfObj->process();
    }

    protected function process() {
        $modules = Module::allEnabled();
        foreach($modules as $name => $module) {
            $extendPath = $module->getPath() . '/Extend';
            //Check if path exists inside each module
            if(!file_exists($extendPath)) {
                continue;
            }

            //Process Each Proxy Type
            foreach ($this->extendables as $source => $target) {
                $this->scanModuleDirectory($module, $source, $target, $extendPath, 'Extend');
            }
        }

        //After Modules extend Base Laravel App
        $this->extendApp();

        //Add Final Proxy Classes to Class Extensions
        foreach ($this->extendables as $source => $target) {
            $module = Module::find($this->baseModuleName);
            $sourcePath = $module->getPath() . '/Proxies';
            $this->scanModuleDirectory($module, $source, $target, $sourcePath, 'Proxies');
        }

        //Extend Classes
        foreach ($this->classExtensions as $originalClass => $proxyClasses) {
            $this->extendClass($originalClass);
        }
    }

    /**
     * Extending Main Laravel's app
     */
    protected function extendApp() {
        $extendPath = app_path('Extend');
        if(file_exists($extendPath)) {
            foreach ($this->extendables as $source => $target) {
                $sourcePath = $extendPath . '/' . $source;
                //Verify Individual items
                if(file_exists($sourcePath)) {
                    $files = \File::files($sourcePath);
                    foreach($files as $file) {
                        //Check if we have a valid class for extending
                        $proxyClass = basename($file, '.php');
                        $appNamespace = app()->getNamespace();
                        $proxyClassPath = $appNamespace . "Extend\\" . $target .  "\\" . $proxyClass;
                        $originalClass = "Modules\\" . $this->baseModuleName . "\\" . $target .  "\\" . $proxyClass;
                        if(class_exists($originalClass)) {
                            $this->classExtensions[$originalClass][] = $proxyClassPath;
                        }
                    }
                }
            }
        }
    }

    protected function scanModuleDirectory($module, $source, $target, $extendPath, $type) {
        $sourcePath = $extendPath . '/' . $source;
        //Verify Individual items
        if(file_exists($sourcePath)) {
            $files = \File::files($sourcePath);
            foreach($files as $file) {
                //Check if we have a valid class for extending
                $proxyClass = basename($file, '.php');
                $this->classExtendable($module, $source, $target, $proxyClass, $type);
            }
        }
    }

    protected function classExtendable($module, $source, $target, $proxyClass, $type) {
        $proxyClassPath = "Modules\\" . $module->getName() . "\\$type\\" . $target .  "\\" . $proxyClass;
        $originalClass = "Modules\\" . $this->baseModuleName . "\\" . $target .  "\\" . $proxyClass;
//        if(class_exists($proxyClassPath) && class_exists($originalClass)) {
        if(class_exists($originalClass)) {
            $this->classExtensions[$originalClass][] = $proxyClassPath;
        }
    }


    protected function extendClass($class)
    {
        $class = ltrim($class, '\\');
        if (isset($this->extensionMap[$class]))
        {
            return $this->extensionMap[$class];
        }

        $extensions = $this->classExtensions[$class];

        $finalClass = $class;
        try
        {
            foreach ($extensions AS $extendClass)
            {
                if (preg_match('/[;,$\/#"\'\.()]/', $extendClass))
                {
                    continue;
                }

                // WFCP_ = White Falcon Class Proxy, in case you're wondering

                $nsSplit = strrpos($extendClass, '\\');
                if ($nsSplit !== false && $ns = substr($extendClass, 0, $nsSplit))
                {
                    $proxyClass = $ns . '\\Proxy' . substr($extendClass, $nsSplit + 1);
                }
                else
                {
                    $proxyClass = 'Proxy' . $extendClass;
                }


                // TODO: there may be a situation where this fails. If we've changed the extensions after classes have
                // been loaded, it's possible these classes will already be loaded with a different config. Figure out
                // how to handle that if possible. Remains to be seen if it comes up (mostly relating to add-on imports).

                class_alias($finalClass, $proxyClass);
                $finalClass = $extendClass;
                if (!class_exists($extendClass))
                {
                    throw new \ErrorException("Could not find class $extendClass when attempting to extend $class");
                }
            }
        }
        catch (\ErrorException $e)
        {
            $this->extensionMap[$class] = $class;
            throw $e;
        }

        $this->extensionMap[$class] = $finalClass;
        return $finalClass;
    }
}
