<?php

namespace NwApi;

/**
 * @property \Doctrine\ORM\EntityManager $em Doctrine entity manager
 * @property string $configPath directory containing configuration files
 * @property string $entitiesPath directory containing entities files
 * @property string $rootPath directory containing the whole project
 * @property string $srcPath directory containing project's sources
 * @property int $env define current environment using ENV_DEVELOPMENT|ENV_PRODUCTION
 * @property \Slim\Slim $slim framework object
 */
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
