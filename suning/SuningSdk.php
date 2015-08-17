<?php

class SuningSdk
{

    protected static $autoPath = array();

    /**
     * 注册自动加载类机制，指定sdk路径等
     */
    public static function register ()
    {
        //
        //echo __CLASS__;
        // 避免其他自动加载函数加载异常，优先注册当前机制
        $func = spl_autoload_functions();
        if ($func) {
            foreach ($func as $f)
                spl_autoload_unregister($f);
        }

        spl_autoload_register(array(__CLASS__,'autoload'));

        //没太明白
        if ($func) {
            foreach ($func as $f)
                spl_autoload_register($f);
        }
        // 自动包含地址
        $dir = dirname(__FILE__);
        self::$autoPath = array(
            $dir . DIRECTORY_SEPARATOR . 'client',
            $dir . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'request',
        );
    }

    /**
     *
     * @param unknown $className            
     */
    public static function autoload ($className)
    {
        foreach (self::$autoPath as $path) {
            $f = $path . DIRECTORY_SEPARATOR . $className . '.class.php';
            if (file_exists($f)) {
                include $f;
                return;
            }
        }
    }
}
SuningSdk::register();
