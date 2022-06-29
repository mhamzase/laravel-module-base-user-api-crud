<?php


namespace Modules\Base\Helpers;


class General
{
    /**
     * Convert camel to snake case string
     * @param $input
     * @return string
     */
    public static function camelToSnake ( $input ) :string
    {
        if ( preg_match ( '/[A-Z]/', $input ) === 0 ) { return $input; }
        $pattern = '/([a-z])([A-Z])/';
        $r = strtolower ( preg_replace_callback ( $pattern, function ($a) {
            return $a[1] . "-" . strtolower ( $a[2] );
        }, $input ) );
        return $r;
    }

    /**
     * Is multi array
     * @param $arr
     * @return bool
     */
    public static function isMultiArray($arr) :bool
    {
        $rv = array_filter($arr,'is_array');
        if(count($rv)>0) return true;
        return false;
    }

    /**
     * Get module info
     * @return array
     */
    public static function getModulesInfo()
    {
        $modules = \Nwidart\Modules\Facades\Module::all();

        $modulesInfo = [];
        foreach($modules as $key=>$module)
        {

            $moduleInfo['name'] = $module->getName() == 'Classifieds'? 'Classified': $module->getName();
            $moduleInfo['status'] = $module->isEnabled();
            $moduleInfo['parent'] = ($moduleInfo['name'] == 'Page') ? 'CMS' : '';
            $modulesInfo[] = $moduleInfo;
        }

        return $modulesInfo;
    }

    /**
     * Get the module name.
     *
     * @return string
     */
    public static function getModuleName()
    {
        $module = app('modules')->getUsedNow();

        $module = app('modules')->findOrFail($module);

        return $module->getStudlyName();
    }
}
