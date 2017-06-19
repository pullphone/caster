<?php

namespace Caster;

use Caster\DataFormat;

trait Caster
{
    public static function getAccessor($schema)
    {
        Config::initialize();
        return DataFormat\Accessor::getInstance($schema);
    }
}
