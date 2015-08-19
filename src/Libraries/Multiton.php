<?php

namespace NwApi\Libraries;

/**
 * Implemente Multiton design pattern.
 */
abstract class Multiton
{
    /**
     * Contains all instances.
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Instance id.
     *
     * @var string
     */
    protected $instanceId = '';

    /**
     * protected constructor to disallow call without multiton.
     */
    protected function __construct($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * Return instance corresponding to $instanceId.
     *
     * @param string   $instanceId
     * @param callable $constructCallback callback call just after construct
     *
     * @return \static
     */
    public static function getInstance($instanceId, callable $constructCallback = null)
    {
        $class = get_called_class();
        $instance = &self::$instances[$class][$instanceId];
        if (!isset($instance)) {
            $instance = new static($instanceId);
            if (!is_null($constructCallback)) {
                $constructCallback($instance);
            }
        }

        return $instance;
    }

    /**
     * Allow to force an instance.
     *
     * Usefull for mocking
     *
     * @param string $instance_id
     * @param object $instance
     */
    public function setInstance($instanceId, Multiton $instance)
    {
        self::$instances[get_called_class()][$instanceId] = $instance;
    }
}
