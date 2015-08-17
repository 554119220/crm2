<?php

/**
 * 地址库返回数据信息
 * @author auto create
 */
class AddressResult
{
	
	/** 
	 * 详细街道地址，不需要重复填写省/市/区
	 **/
	public $addr;
	
	/** 
	 * 区域ID
	 **/
	public $areaId;
	
	/** 
	 * 是否默认退货地址
	 **/
	public $cancelDef;
	
	/** 
	 * 市
	 **/
	public $city;
	
	/** 
	 * 地址库ID
	 **/
	public $contactId;
	
	/** 
	 * 联系人姓名
	 **/
	public $contactName;
	
	/** 
	 * 区、县
	 **/
	public $country;
	
	/** 
	 * 是否默认取货地址
	 **/
	public $getDef;
	
	/** 
	 * 备注
	 **/
	public $memo;
	
	/** 
	 * 手机号码，手机与电话必需有一个 
手机号码不能超过20位
	 **/
	public $mobilePhone;
	
	/** 
	 * 修改日期时间
	 **/
	public $modifyDate;
	
	/** 
	 * 电话号码,手机与电话必需有一个
	 **/
	public $phone;
	
	/** 
	 * 省
	 **/
	public $province;
	
	/** 
	 * 公司名称,
	 **/
	public $sellerCompany;
	
	/** 
	 * 是否默认发货地址
	 **/
	public $sendDef;
	
	/** 
	 * 地区邮政编码
	 **/
	public $zipCode;	
}
?>