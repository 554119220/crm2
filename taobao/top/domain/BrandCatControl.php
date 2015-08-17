<?php

/**
 * 管控的品牌类目信息
 * @author auto create
 */
class BrandCatControl
{
	
	/** 
	 * 被管控的品牌的ID号码
	 **/
	public $brandId;
	
	/** 
	 * 被管控的品牌名称
	 **/
	public $brandName;
	
	/** 
	 * 被管控的类目的ID号
	 **/
	public $catId;
	
	/** 
	 * 被管控的类目的ID号
	 **/
	public $catName;
	
	/** 
	 * 以;隔开多个认证资料。:隔开资料ID与内容。如？1:产品包装图片;2:完整产品资质
	 **/
	public $certifiedData;	
}
?>