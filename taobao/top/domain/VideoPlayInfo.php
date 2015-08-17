<?php

/**
 * 视频播放信息
 * @author auto create
 */
class VideoPlayInfo
{
	
	/** 
	 * android  pad兼播放的m3u8列表文件（包含多码率）。适用大于等于3.0版本Android。
	 **/
	public $androidpadUrl;
	
	/** 
	 * android pad播放的mp4文件列表。适用2.3版本的Android
	 **/
	public $androidpadV23Url;
	
	/** 
	 * android  phone播放的m3u8列表文件（包含多码率，）。适用大于等于3.0版本Android。
	 **/
	public $androidphoneUrl;
	
	/** 
	 * android  phone播放的mp4文件列表。适用2.3版本的Android。
	 **/
	public $androidphoneV23Url;
	
	/** 
	 * Flash播放器地址，可直接通过PC浏览器播放
	 **/
	public $flashUrl;
	
	/** 
	 * ipad播放的m3u8列表文件（包含多码率）
	 **/
	public $ipadUrl;
	
	/** 
	 * iphone播放的m3u8列表文件（包含多码率）
	 **/
	public $iphoneUrl;
	
	/** 
	 * Web嵌入html代码，可直接嵌入页面中，支持html5的video标签，支持HLS播放协议最终返回m3u8资源，否则返回mp4资源
	 **/
	public $webUrl;	
}
?>