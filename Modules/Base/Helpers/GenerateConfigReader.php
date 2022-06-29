<?php


namespace Modules\Base\Helpers;

use Nwidart\Modules\Support\Config\GeneratorPath;

class GenerateConfigReader
{
    public static function read(string $value) : GeneratorPath
    {
        return new GeneratorPath(config($value));
    }
}
