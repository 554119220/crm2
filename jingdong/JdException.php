<?php
/**
 * User: zhangfeng
 * Date: 12-6-11
 * Time: 下午9:12
 *
 */

class JdException
{
    /**
     * @var 错误代码
     */
    private $errCode;

    /**
     * @var 错误信息
     */
    private $errMsg;

    function JdException($errCode,$errMsg)
    {
        $this->errCode = $errCode;
        $this->errMsg = $errMsg;
    }

}
