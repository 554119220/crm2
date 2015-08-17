<?php

/**
 * RoomImage（酒店图片）结构。各字段详细说明可参考接口定义，如：商品图片上传接口。
 * @author auto create
 */
class RoomImage
{
	
	/** 
	 * 商品所有图片的url，用”,”隔开。即，当前该商品的所有图片地址。
	 **/
	public $allImages;
	
	/** 
	 * 酒店商品id
	 **/
	public $gid;
	
	/** 
	 * 图片url。当前接口操作的图片url。调用上传图片接口时，代表上传图片后得到的图片url。调用删除图片接口时，代表被删除掉的图片url。
	 **/
	public $image;
	
	/** 
	 * 图片位置，可选值：1,2,3,4,5。代表图片的位置，如：2，代表第二张图片。
	 **/
	public $position;	
}
?>