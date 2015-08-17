<?php

/**
 * 发票信息
 * @author auto create
 */
class Invoicewlbwmsconsignordernotify
{
	
	/** 
	 * 发票金额
	 **/
	public $billAccount;
	
	/** 
	 * 发票内容
	 **/
	public $billContent;
	
	/** 
	 * Erp发票ID
	 **/
	public $billId;
	
	/** 
	 * 发票抬头
	 **/
	public $billTitle;
	
	/** 
	 * 发票类型：VINVOICE - 增值税普通发票， EVINVOICE - 电子增票
	 **/
	public $billType;	
}
?>