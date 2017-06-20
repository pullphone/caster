<?php

namespace Caster;

use Caster\DataFormat;

trait Caster
{
    public static function get($schema)
    {
        $dataFormat = DataFormat\Accessor::getInstance($schema);
        return new Executor($dataFormat);
    }
}
