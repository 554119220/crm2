<?php

/**
 * 推荐的关联商品
 * @author auto create
 */
class FavoriteItem
{
	
	/** 
	 * 商品ID
	 **/
	public $itemId;
	
	/** 
	 * 商品名称
	 **/
	public $itemName;
	
	/** 
	 * 商品图片地址
	 **/
	public $itemPictrue;
	
	/** 
	 * 商品价格
	 **/
	public $itemPrice;
	
	/** 
	 * 商品的详情页面地址
	 **/
	public $itemUrl;
	
	/** 
	 * 促销价格
	 **/
	public $promotionPrice;
	
	/** 
	 * 商品销售次数
	 **/
	public $sellCount;
	
	/** 
	 * 商品id（具有跟踪效果）代替原来的item_id  <a href="http://dev.open.taobao.com/bbs/read.php?tid=24323">详细说明</a>
	 **/
	public $trackIid;	
}
?>