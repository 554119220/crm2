<?php

/**
 * 判定服务是否可达的返回结果
 * @author auto create
 */
class AddressReachableResult
{
	
	/** 
	 * 区域编码
	 **/
	public $divisionId;
	
	/** 
	 * 错误码
	 **/
	public $errorCode;
	
	/** 
	 * 错误信息
	 **/
	public $errorMsg;
	
	/** 
	 * 物流公司代号
	 **/
	public $partnerCode;
	
	/** 
	 * 物流公司编码ID
	 **/
	public $partnerId;
	
	/** 
	 * 物流公司名称
	 **/
	public $partnerName;
	
	/** 
	 * 服务是否可达，    0 - 不可达    1 - 可达    2 - 不确定    3 - 未配置
	 **/
	public $reachable;
	
	/** 
	 * 服务对应的数字编码，如揽派范围对应88
	 **/
	public $serviceType;
	
	/** 
	 * 是否成功
	 **/
	public $success;	
}
?>