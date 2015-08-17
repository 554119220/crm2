<?php

/**
 * 分销产品
 * @author auto create
 */
class FenxiaoProduct
{
	
	/** 
	 * 警戒库存
	 **/
	public $alarmNumber;
	
	/** 
	 * 类目id
	 **/
	public $categoryId;
	
	/** 
	 * 所在地：市
	 **/
	public $city;
	
	/** 
	 * 采购价格，单位：元。
	 **/
	public $costPrice;
	
	/** 
	 * 创建时间
	 **/
	public $created;
	
	/** 
	 * 经销采购价
	 **/
	public $dealerCostPrice;
	
	/** 
	 * 产品描述路径，通过http请求获取
	 **/
	public $descPath;
	
	/** 
	 * 产品描述的内容
	 **/
	public $description;
	
	/** 
	 * 折扣ID（新增参数）
	 **/
	public $discountId;
	
	/** 
	 * 是否有保修，可选值：false（否）、true（是）
	 **/
	public $haveGuarantee;
	
	/** 
	 * 是否有发票，可选值：false（否）、true（是）
	 **/
	public $haveInvoice;
	
	/** 
	 * 产品图片
	 **/
	public $images;
	
	/** 
	 * 自定义属性，格式为pid:value;pid:value
	 **/
	public $inputProperties;
	
	/** 
	 * 查询产品列表时，查询入参增加is_authz:yes|no 
yes:需要授权 
no:不需要授权
	 **/
	public $isAuthz;
	
	/** 
	 * 导入的商品ID
	 **/
	public $itemId;
	
	/** 
	 * 下载人数
	 **/
	public $itemsCount;
	
	/** 
	 * 更新时间
	 **/
	public $modified;
	
	/** 
	 * 产品名称
	 **/
	public $name;
	
	/** 
	 * 累计采购次数
	 **/
	public $ordersCount;
	
	/** 
	 * 商家编码
	 **/
	public $outerId;
	
	/** 
	 * 产品分销商信息
	 **/
	public $pdus;
	
	/** 
	 * 产品图片路径
	 **/
	public $pictures;
	
	/** 
	 * 产品ID
	 **/
	public $pid;
	
	/** 
	 * ems费用，单位：元
	 **/
	public $postageEms;
	
	/** 
	 * 快递费用，单位：元
	 **/
	public $postageFast;
	
	/** 
	 * 运费模板ID
	 **/
	public $postageId;
	
	/** 
	 * 平邮费用，单位：元
	 **/
	public $postageOrdinary;
	
	/** 
	 * 运费类型，可选值：seller（供应商承担运费）、buyer（分销商承担运费）
	 **/
	public $postageType;
	
	/** 
	 * 产品线ID
	 **/
	public $productcatId;
	
	/** 
	 * 产品属性，格式为pid:vid;pid:vid
	 **/
	public $properties;
	
	/** 
	 * 属性别名，格式为pid:vid:alias;pid:vid:alias
	 **/
	public $propertyAlias;
	
	/** 
	 * 所在地：省
	 **/
	public $prov;
	
	/** 
	 * 产品库存
	 **/
	public $quantity;
	
	/** 
	 * 根据商品ID查询时，返回这个产品对应的商品ID，只对分销商查询接口有效
	 **/
	public $queryItemId;
	
	/** 
	 * 配额可用库存
	 **/
	public $quotaQuantity;
	
	/** 
	 * 预扣库存
	 **/
	public $reservedQuantity;
	
	/** 
	 * 最高零售价，单位：分。
	 **/
	public $retailPriceHigh;
	
	/** 
	 * 最低零售价，单位：分。
	 **/
	public $retailPriceLow;
	
	/** 
	 * 关联的后端商品id
	 **/
	public $scitemId;
	
	/** 
	 * sku列表
	 **/
	public $skus;
	
	/** 
	 * 产品spu id
	 **/
	public $spuId;
	
	/** 
	 * 采购基准价，单位：元。
	 **/
	public $standardPrice;
	
	/** 
	 * 零售基准价，单位：元
	 **/
	public $standardRetailPrice;
	
	/** 
	 * 发布状态，可选值：up（上架）、down（下架）
	 **/
	public $status;
	
	/** 
	 * 分销方式：AGENT（只做代销，默认值）、DEALER（只做经销）、ALL（代销和经销都做）
	 **/
	public $tradeType;
	
	/** 
	 * 铺货时间
	 **/
	public $upshelfTime;	
}
?>