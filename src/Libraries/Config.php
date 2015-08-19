<?php

namespace NwApi\Libraries;

class Config extends Multiton
{
    protected function __construct($instanceId)
    {
        parent::__construct($instanceId);
        $di = \NwApi\Di::getInstance();
        require $di->configPath.$instanceId.'.php';
        $vars = get_defined_vars();
        unset($vars['instanceId']);
        unset($vars['di']);
        foreach ($vars as $name => $value) {
            $this->{$name} = $value;
        }
    }
}
