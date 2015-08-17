<?php

/**
 * 退款超时
 * @author auto create
 */
class RefundRemindTimeout
{
	
	/** 
	 * 是否存在超时。可选值:true(是),false(否)
	 **/
	public $existTimeout;
	
	/** 
	 * 提醒的类型（退款详情中提示信息的类型）
	 **/
	public $remindType;
	
	/** 
	 * 超时时间。格式:yyyy-MM-dd HH:mm:ss
	 **/
	public $timeout;	
}
?>