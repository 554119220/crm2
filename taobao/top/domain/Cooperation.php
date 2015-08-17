<?php

/**
 * 合作分销关系
 * @author auto create
 */
class Cooperation
{
	
	/** 
	 * 供应商授权的支付方式：ALIPAY(支付宝)、OFFPREPAY(预付款)、OFFTRANSFER(转帐)、OFFSETTLEMENT(后期结算)
	 **/
	public $authPayway;
	
	/** 
	 * 合作关系ID
	 **/
	public $cooperateId;
	
	/** 
	 * 分销商ID
	 **/
	public $distributorId;
	
	/** 
	 * 分销商nick
	 **/
	public $distributorNick;
	
	/** 
	 * 合作终止时间
	 **/
	public $endDate;
	
	/** 
	 * 等级ID
	 **/
	public $gradeId;
	
	/** 
	 * 授权产品线
	 **/
	public $productLine;
	
	/** 
	 * 授权产品线名称，和product_line中的值按序对应
	 **/
	public $productLineName;
	
	/** 
	 * 合作起始时间
	 **/
	public $startDate;
	
	/** 
	 * 合作状态： NORMAL(合作中)、 ENDING(终止中) 、END (终止)
	 **/
	public $status;
	
	/** 
	 * 供应商ID
	 **/
	public $supplierId;
	
	/** 
	 * 供应商NICK
	 **/
	public $supplierNick;
	
	/** 
	 * 分销方式： AGENT(代销) 、DEALER(经销)
	 **/
	public $tradeType;	
}
?>