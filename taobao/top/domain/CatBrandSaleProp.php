<?php

/**
 * 被管控的品牌和类目的所对应的销售属性
 * @author auto create
 */
class CatBrandSaleProp
{
	
	/** 
	 * 被管控的品牌的Id
	 **/
	public $brandId;
	
	/** 
	 * 被管控的类目ID
	 **/
	public $catId;
	
	/** 
	 * 如果该属性为营销属性，则获取默认值
	 **/
	public $defMarketPropValue;
	
	/** 
	 * true表示：不是产品的规格属性
false表示：是产品的规格属性。
	 **/
	public $isNotSpec;
	
	/** 
	 * 被管控的销售属性ID
	 **/
	public $propertyId;	
}
?>