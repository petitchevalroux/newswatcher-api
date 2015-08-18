<?php

namespace NwApi\libraries;

/**
 * Implement singleton design pattern.
 */
abstract class Singleton
{
    protected static $instances = [];

    /**
     * Return singleton instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }

    /**
     * Allow to override an instance.
     *
     * Can be usefull when mocking for unit test
     *
     * @param string    $instance_id
     * @param Singleton $instance
     */
    public function setInstance(Singleton $instance)
    {
        self::$instances[get_called_class()] = $instance;
    }
}
