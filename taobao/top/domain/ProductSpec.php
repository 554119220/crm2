<?php

/**
 * ProductSpec(产品规格)结构。
 * @author auto create
 */
class ProductSpec
{
	
	/** 
	 * 产品规格条形码，支持EAN-13格式。
	 **/
	public $barcode;
	
	/** 
	 * 产品品牌id
	 **/
	public $brandId;
	
	/** 
	 * 认证图片列表
	 **/
	public $certifiedPics;
	
	/** 
	 * 认证文本列表
	 **/
	public $certifiedTxts;
	
	/** 
	 * 基础色数据
	 **/
	public $changeProp;
	
	/** 
	 * 用户输入的属性值存放位置，例如可输入的销售属性，当用户获取pid vid后，应该先从spec_props_alias中获取，然后通过类目属性获取，获取不到，可以通过这个字段获取。
	 **/
	public $customePropsName;
	
	/** 
	 * 产品规格吊牌价，以分为单位的整数，非负,无默认值，上限999999999
	 **/
	public $labelPrice;
	
	/** 
	 * 上市时间
	 **/
	public $marketTime;
	
	/** 
	 * 规格的数量，仅当通过组合产品获取时有值
	 **/
	public $number;
	
	/** 
	 * 产品的主图片地址。绝对地址，格式：http://host/image_path。
	 **/
	public $picUrl;
	
	/** 
	 * 产品货号
	 **/
	public $productCode;
	
	/** 
	 * 产品ID。
	 **/
	public $productId;
	
	/** 
	 * 产品规格ID。
	 **/
	public $specId;
	
	/** 
	 * 产品规格的销售属性组合。格式为：pid:vid;pid:vid。其中：pid是销售属性id，vid是销售属性值id。如果该类目品牌下面没有销售属性，可以不用填写。销售属性通过tmall.brandcat.salespro.get接口获取。
	 **/
	public $specProps;
	
	/** 
	 * 销售属性值别名。格式为：pid1:vid1:别名1;pid2:vid2:别名2。其中：pid是销售属性id，vid是销售属性值id。别名长度不可以超过30个字符。目前只有颜色销售属性支持别名。
	 **/
	public $specPropsAlias;
	
	/** 
	 * 1:表示可以使用的数据，也就是审核通过的。
3：表示等待小二审核的产品规格，这个数据暂时还不能使用，要等待审核通过后，才能使用。
	 **/
	public $status;	
}
?>