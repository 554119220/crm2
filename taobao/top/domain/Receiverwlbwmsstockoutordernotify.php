<?php

/**
 * 收件人信息
 * @author auto create
 */
class Receiverwlbwmsstockoutordernotify
{
	
	/** 
	 * 收件方地址
	 **/
	public $receiverAddress;
	
	/** 
	 * 收件方区县
	 **/
	public $receiverArea;
	
	/** 
	 * 收件方城市
	 **/
	public $receiverCity;
	
	/** 
	 * 退供场景ECP填充供应商编码，调拨出库单ECP填充调拨入仓库编码, B2B出库单填写分销商ID(无分销ID的null)
	 **/
	public $receiverCode;
	
	/** 
	 * 收件方手机
	 **/
	public $receiverMobile;
	
	/** 
	 * 收件方名称
	 **/
	public $receiverName;
	
	/** 
	 * 收件方电话
	 **/
	public $receiverPhone;
	
	/** 
	 * 收件方省份
	 **/
	public $receiverProvince;
	
	/** 
	 * 收件方邮编
	 **/
	public $receiverZipCode;	
}
?>