<?php

/**
 * Class SuningCustomOrderDeliveryAdd
 * @author nixus
 */
class SuningCustomOrderDeliveryAdd
{
    private $orderCode;
    private $expressNo;
    private $expressCompanyCode;

    private $deliveryTime;//    = date('Y-m-d H:i:s', time());

    private $productCode     = null;
    private $orderLineNumber = null;

    /**
     * Setter for orderCode
     */
    public function setOrderCode($orderCode)
    {
        $this->orderCode = $orderCode;
    }

    /**
     * Setter for expressNo
     */
    public function setExpressNo($expressNo)
    {
        $this->expressNo = $expressNo;
    }

    /**
     * Setter for expressCompanyCode
     */
    public function setExpressCompanyCode($expressCompanyCode)
    {
        $this->expressCompanyCode = $expressCompanyCode;
    }

    /**
     * Setter for deliveryTime
     */
    public function setDeliveryTime($deliveryTime)
    {
        $this->deliveryTime = $deliveryTime;
    }

    /**
     * Setter for productCode
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
    }

    /**
     * Setter for orderLineNumber
     */
    public function setOrderLineNumber($orderLineNumber)
    {
        $this->orderLineNumber = $orderLineNumber;
    }

    /**
     * makeReqObject 
     * @return object
     * @author nixus
     **/
    public function makeReqObject ()
    {
        $err = array('error_code' => 'param_is_null');
                
        if (empty($this->orderCode)) {
            $err['error_msg'] = '订单编号错误';
            return $err;
        }

        if (empty($this->expressNo)) {
            $err['error_msg'] = '快递单号错误';
            return $err;
        }

        if (empty($this->expressCompanyCode)) {
            $err['error_msg'] = '快递公司代号错误';
            return $err;
        }

        $req_param = array (
            "sn_request" => array (
                "sn_body" => array (
                    "orderDelivery" => array (
                        "orderCode"          => "$this->orderCode",
                        "expressNo"          => "$this->expressNo",
                        "expressCompanyCode" => "$this->expressCompanyCode",
                        "deliveryTime"       => "$this->deliveryTime",
                    )
                )
            )
        );

        if ($this->productCode) {
            $sendDetail = array (
                "productCode" => $this->productCode
            );

            $req_param['sn_request']['sn_body']['orderDelivery']['sendDetail'] = $sendDetail;
        }

        if ($this->orderLineNumber) {
            $orderLineNumbers = array (
                "orderLineNumber" => $this->orderLineNumber
            );

            $req_param['sn_request']['sn_body']['orderDelivery']['orderLineNumbers'] = $orderLineNumbers;
        } else {
            $orderLineNumbers = array (
                "orderLineNumber" => ""
            );

            $req_param['sn_request']['sn_body']['orderDelivery']['orderLineNumbers'] = $orderLineNumbers;
        }

        return json_encode($req_param);
    }
}
