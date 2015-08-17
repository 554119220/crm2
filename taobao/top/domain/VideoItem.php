<?php

/**
 * 视频
 * @author auto create
 */
class VideoItem
{
	
	/** 
	 * 视频封面url
	 **/
	public $coverUrl;
	
	/** 
	 * 视频描述
	 **/
	public $description;
	
	/** 
	 * 视频时长
	 **/
	public $duration;
	
	/** 
	 * 是否允许他人观看
	 **/
	public $isOpenToOther;
	
	/** 
	 * 视频状态：等待转码（1），转码中（2），转码失败（3），等待审核（4），未通过审核（5），通过审核（6）
	 **/
	public $state;
	
	/** 
	 * 视频标签
	 **/
	public $tags;
	
	/** 
	 * 视频标题
	 **/
	public $title;
	
	/** 
	 * 视频上传时间
	 **/
	public $uploadTime;
	
	/** 
	 * 视频上传者数字id
	 **/
	public $uploaderId;
	
	/** 
	 * 视频id
	 **/
	public $videoId;
	
	/** 
	 * 视频播放地址
	 **/
	public $videoPlayInfo;	
}
?>