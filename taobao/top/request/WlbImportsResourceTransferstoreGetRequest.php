<?php
/**
 * TOP API: taobao.wlb.imports.resource.transferstore.get request
 * 
 * @author auto create
 * @since 1.0, 2015.05.06
 */
class WlbImportsResourceTransferstoreGetRequest
{
	/** 
	 * 商品前台叶子类目ID
	 **/
	private $cids;
	
	/** 
	 * 卖家发货地址的区域ID，如果不填则为默认发货地址ID
	 **/
	private $fromId;
	
	/** 
	 * 通过taobao.wlb.imports.resource.get接口查询出来的资源ID
	 **/
	private $resourceId;
	
	/** 
	 * 买家收货地址区域ID
	 **/
	private $toId;
	
	private $apiParas = array();
	
	public function setCids($cids)
	{
		$this->cids = $cids;
		$this->apiParas["cids"] = $cids;
	}

	public function getCids()
	{
		return $this->cids;
	}

	public function setFromId($fromId)
	{
		$this->fromId = $fromId;
		$this->apiParas["from_id"] = $fromId;
	}

	public function getFromId()
	{
		return $this->fromId;
	}

	public function setResourceId($resourceId)
	{
		$this->resourceId = $resourceId;
		$this->apiParas["resource_id"] = $resourceId;
	}

	public function getResourceId()
	{
		return $this->resourceId;
	}

	public function setToId($toId)
	{
		$this->toId = $toId;
		$this->apiParas["to_id"] = $toId;
	}

	public function getToId()
	{
		return $this->toId;
	}

	public function getApiMethodName()
	{
		return "taobao.wlb.imports.resource.transferstore.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->cids,"cids");
		RequestCheckUtil::checkMaxListSize($this->cids,20,"cids");
		RequestCheckUtil::checkNotNull($this->resourceId,"resourceId");
		RequestCheckUtil::checkNotNull($this->toId,"toId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
