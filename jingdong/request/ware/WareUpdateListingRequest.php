<?php
/**
 * User: zhangfeng
 * Date: 12-6-13
 * Time: 下午2:08
 */

class WareUpdateListingRequest
{
    /**
     * @var 商品id
     */
    private $wareId;

    /**
     * @var 流水号
     */
    private $tradeNo;

    /**
     * 首先需要对业务参数进行安装首字母排序，然后将业务参数转换json字符串
     * @return string
     */
    public function getAppJsonParams()
    {
        $apiParams["ware_id"]=$this->wareId;
        $apiParams["trade_no"]=$this->tradeNo;
        ksort($apiParams);
        return json_encode($apiParams);
    }

    /**
     *
     * 获取方法名称
     * @return string
     */
    public function getApiMethod()
    {
        return "360buy.ware.update.listing";
    }



    public function setWareIds($wareId)
    {
        $this->wareId = $wareId;
    }

    public function getWareId()
    {
        return $this->wareId;
    }

    /**
     * @param  $tradeNo
     */
    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    }

    /**
     * @return
     */
    public function getTradeNo()
    {
        return $this->tradeNo;
    }
}