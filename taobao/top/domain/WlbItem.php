<?php

/**
 * 物流宝商品
 * @author auto create
 */
class WlbItem
{
	
	/** 
	 * 品牌ID
	 **/
	public $brandId;
	
	/** 
	 * 颜色
	 **/
	public $color;
	
	/** 
	 * 创建人
	 **/
	public $creator;
	
	/** 
	 * 标记，用逗号隔开的字符串。
BIT_HAS_AUTHORIZE 第1位 是否有授权规则;
BATCH  第2位 是否有批次规则；
SYNCHRONIZATION 第3位 是否有同步规则。
	 **/
	public $flag;
	
	/** 
	 * 创建日期
	 **/
	public $gmtCreate;
	
	/** 
	 * 修改日期
	 **/
	public $gmtModified;
	
	/** 
	 * 货类
	 **/
	public $goodsCat;
	
	/** 
	 * 高
	 **/
	public $height;
	
	/** 
	 * 商品id
	 **/
	public $id;
	
	/** 
	 * 是否危险品
	 **/
	public $isDangerous;
	
	/** 
	 * 是否易碎
	 **/
	public $isFriable;
	
	/** 
	 * 是不是sku商品
值为true或false
	 **/
	public $isSku;
	
	/** 
	 * 商家编码
	 **/
	public $itemCode;
	
	/** 
	 * 最后修改人
	 **/
	public $lastModifier;
	
	/** 
	 * mm
	 **/
	public $length;
	
	/** 
	 * 商品的名称
	 **/
	public $name;
	
	/** 
	 * 包装材料
	 **/
	public $packageMaterial;
	
	/** 
	 * 父item的id，当item为物流宝子商品时，parent_id必填,否则不必填
可通过父ID来得知商品的关系。
	 **/
	public $parentId;
	
	/** 
	 * 价格
	 **/
	public $price;
	
	/** 
	 * 计价货类
	 **/
	public $pricingCat;
	
	/** 
	 * 属性key:value; key:value
	 **/
	public $properties;
	
	/** 
	 * 发布版本号，用来同步商
	 **/
	public $publishVersion;
	
	/** 
	 * 商品备注
	 **/
	public $remark;
	
	/** 
	 * 状态，item_status_valid -- 1 表示 有效 item_status_lock -- 2 表示锁住
	 **/
	public $status;
	
	/** 
	 * 前台商品名称
	 **/
	public $title;
	
	/** 
	 * 商品类型：
NORMAL--普通类型;
COMBINE--组合商品;
DISTRIBUTION--分销商品;
默认为NORMAL
	 **/
	public $type;
	
	/** 
	 * 商品所有人淘宝ID
	 **/
	public $userId;
	
	/** 
	 * 商品所有人淘宝nick
	 **/
	public $userNick;
	
	/** 
	 * 立方mm
	 **/
	public $volume;
	
	/** 
	 * 重量
	 **/
	public $weight;
	
	/** 
	 * 宽
	 **/
	public $width;	
}
?>