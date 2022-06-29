<?php

namespace Modules\User\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Modules\User\Entities\Permission;
use Modules\User\Entities\PermissionAlias;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GeneratePermissionsCommand extends Command
{
    protected $guardName = 'sanctum';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permissions:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
    public function handle()
    {
        DB::transaction(function ()
        {
            $modules = Module::all();

            /*loop through all permissions files inside every module/config directory */
            foreach ($modules as $moduleName => $module)
            {
                $configPath =  $module->getPath() . '/Config/permissions.php';
                if(file_exists($configPath))
                {
                    $permissions = include $configPath;

                    $this->handlePermissions($permissions, $moduleName);
                }
            }

            if(Config::has('permissions'))
            {
                $this->handlePermissions(config('permissions'), 'Root');
            }

        });
    }


    public function handlePermissions($permissions, $moduleName)
    {
        /* generate resource permissions */
        if(isset($permissions['resource']) && sizeof($permissions['resource']))
        {
            $this->generateResourcePermissions($permissions['resource'], $moduleName);
        }

        /* generate single permissions with aliases */
        if(isset($permissions['single']))
        {
            $this->generateSinglePermissions($permissions['single'], $moduleName);
        }
    }

    /**
     * generate single permissions with aliases
     * @param $permissions
     * @param $moduleName
     */
    private function generateSinglePermissions($permissions, $moduleName)
    {
        foreach ($permissions as $permission)
        {
            if(!empty($permission['name']))
            {
                Permission::updateOrCreate(['name' => $permission['name']], ['module_name' => $moduleName, 'guard_name' => $this->guardName]);

                /* check if need to create/update aliases */
                if(isset($permission['alias']))
                {
                    $permissionObj = Permission::where('name', $permission['name'])->first();

                    foreach($permission['alias'] as $alias)
                    {
                        PermissionAlias::updateOrCreate(['name' => $alias], ['module_name' => $moduleName , 'permission_id' => $permissionObj->id]);
                    }
                }
            }
        }
    }

    /**
     * generate resource permissions
     * @param $permissions
     */
    private function generateResourcePermissions($permissions, $moduleName)
    {
        $resourceAliases = $this->getResourceAliases();

        foreach($permissions as $permission)
        {
            foreach ($resourceAliases as $alias)
            {
                $name = $permission['name'] . '.' . $alias['name'];
                Permission::updateOrCreate(['name' => $name], ['module_name' => $moduleName, 'guard_name' => $this->guardName ]);

                if(isset($alias['items']))
                {
                    $permissionObj = Permission::where('name', $name)->first();

                    foreach ($alias['items'] as $aliasItem)
                    {
                        $alias = $permission['name'] . '.' . $aliasItem;
                        PermissionAlias::updateOrCreate(['name' => $alias], ['module_name' => $moduleName , 'permission_id' => $permissionObj->id]);
                    }
                }
            }
        }
    }

    private function getResourceAliases()
    {
        return [
            ['name' => 'viewAny', 'items' => ['index'] ],
            ['name' => 'view'],
            ['name' => 'create', 'items' => ['store'] ],
            ['name' => 'update', 'items' => ['edit'] ],
            ['name' => 'delete', 'items' => ['destroy'] ]
        ];
    }


    /*$resource_alias = [
            'index'   => 'viewAny',
            'show'    => 'view',
            'create'  => 'create',
            'store'   => 'create',
            'edit'    => 'update',
            'update'  => 'update',
            'destroy' => 'delete'
        ];*/
}
