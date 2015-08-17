<?php
/**
 * TOP API: taobao.wlb.imports.resource.get request
 * 
 * @author auto create
 * @since 1.0, 2015.05.06
 */
class WlbImportsResourceGetRequest
{
	/** 
	 * 卖家发货地址，以^^^隔开，建议填写
	 **/
	private $fromAddress;
	
	/** 
	 * 买家收货地址，以^^^隔开，建议填写
	 **/
	private $toAddress;
	
	private $apiParas = array();
	
	public function setFromAddress($fromAddress)
	{
		$this->fromAddress = $fromAddress;
		$this->apiParas["from_address"] = $fromAddress;
	}

	public function getFromAddress()
	{
		return $this->fromAddress;
	}

	public function setToAddress($toAddress)
	{
		$this->toAddress = $toAddress;
		$this->apiParas["to_address"] = $toAddress;
	}

	public function getToAddress()
	{
		return $this->toAddress;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.imports.resource.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
