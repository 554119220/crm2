<?php
/**
 * Class SuningCustomOrderGet
 * @author nixus
 */
class SuningCustomOrderGet
{
    private $orderCode;

    /**
     * Setter for orderCode
     */
    public function setOrderCode($orderCode)
    {
        $this->orderCode = $orderCode;
    }
    
    /**
     * makeReqObject
     * @return object
     * @author nixus
     **/
    public function makeReqObject()
    {
        $req_param = array (
            "sn_request" => array (
                "sn_body" => array (
                    "orderGet" => array (
                        "orderCode" => "$this->orderCode"
                    )
                )
            )
        );

        return json_encode($req_param);
    }
}
