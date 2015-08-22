<?php

namespace NwApi;

class Di extends Libraries\Singleton
{
    protected function __construct()
    {
    }

    private function getDependenciesDirectory()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'Dependencies'.DIRECTORY_SEPARATOR;
    }

    private function getDependencyPath($name)
    {
        return $this->getDependenciesDirectory().str_replace(['.', DIRECTORY_SEPARATOR], '', $name).'.php';
    }

    public function __get($name)
    {
        if (!isset($this->{$name})) {
            $this->{$name} = require $this->getDependencyPath($name);
        }

        return $this->{$name};
    }
}
