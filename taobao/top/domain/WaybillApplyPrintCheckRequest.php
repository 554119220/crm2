<?php

/**
 * 打印请求
 * @author auto create
 */
class WaybillApplyPrintCheckRequest
{
	
	/** 
	 * TOP平台请求的ISV APPKEY
	 **/
	public $appKey;
	
	/** 
	 * 物流服务商Code
	 **/
	public $cpCode;
	
	/** 
	 * 打印面单详细信息
	 **/
	public $printCheckInfoCols;
	
	/** 
	 * 申请者编码
	 **/
	public $sellerId;	
}
?>