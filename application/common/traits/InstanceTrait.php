<?php
namespace app\common\traits;

trait InstanceTrait
{

    /**
     * 获取实例
     *
     * @return self
     */
    public static function getInstance()
    {
        return new static();
    }

    /**
     * 获取单实例
     *
     * @return self
     */
    public static function getSingleton()
    {
        static $instance = null;
        
        if (! isset($instance)) {
            $instance = self::getInstance();
        }
        
        return $instance;
    }
}