<?php

/**
 * 子账号基本信息
 * @author auto create
 */
class SubAccountInfo
{
	
	/** 
	 * 子账号是否参与分流 true:参与分流 false:未参与分流
	 **/
	public $subDispatchStatus;
	
	/** 
	 * 子账号Id
	 **/
	public $subId;
	
	/** 
	 * 子账号用户名
	 **/
	public $subNick;
	
	/** 
	 * 子账号是否已欠费 true:已欠费 false:未欠费
	 **/
	public $subOwedStatus;
	
	/** 
	 * 子账号当前状态：1正常，2卖家停用，3处罚冻结
	 **/
	public $subStatus;
	
	/** 
	 * 主账号Id
	 **/
	public $userId;
	
	/** 
	 * 主账号用户名
	 **/
	public $userNick;	
}
?>