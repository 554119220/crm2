<?php

/**
 * 收\发货地址
 * @author auto create
 */
class WaybillAddress
{
	
	/** 
	 * 详细地址
	 **/
	public $addressDetail;
	
	/** 
	 * --
	 **/
	public $addressFormat;
	
	/** 
	 * 地址信息系统标准格式
	 **/
	public $addressNonCodeFormat;
	
	/** 
	 * 区名称（三级地址）
	 **/
	public $area;
	
	/** 
	 * 三级地址代码
	 **/
	public $areaCode;
	
	/** 
	 * 市名称（二级地址）
	 **/
	public $city;
	
	/** 
	 * 二级地址代码
	 **/
	public $cityCode;
	
	/** 
	 * 末级地址
	 **/
	public $divisionId;
	
	/** 
	 * 省名称（一级地址）
	 **/
	public $province;
	
	/** 
	 * 一级地址代码
	 **/
	public $provinceCode;
	
	/** 
	 * 街道\镇名称（四级地址）
	 **/
	public $town;
	
	/** 
	 * 四级地址代码
	 **/
	public $townCode;
	
	/** 
	 * waybill 地址记录ID(非地址库ID)
	 **/
	public $waybillAddressId;	
}
?>