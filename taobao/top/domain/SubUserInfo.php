<?php

/**
 * 子账号基本信息
 * @author auto create
 */
class SubUserInfo
{
	
	/** 
	 * 子账号姓名
	 **/
	public $fullName;
	
	/** 
	 * 是否参与分流 1不参与 2参与
	 **/
	public $isOnline;
	
	/** 
	 * 子账号用户名
	 **/
	public $nick;
	
	/** 
	 * 子账号所属的主账号的唯一标识
	 **/
	public $sellerId;
	
	/** 
	 * 主账号昵称
	 **/
	public $sellerNick;
	
	/** 
	 * 子账号当前状态 1正常 -1删除  2冻结
	 **/
	public $status;
	
	/** 
	 * 子账号Id
	 **/
	public $subId;	
}
?>