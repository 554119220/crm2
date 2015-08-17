<?php

/**
 * 商品详情模块
 * @author auto create
 */
class ItemDescModule
{
	
	/** 
	 * 描述具体内容
	 **/
	public $content;
	
	/** 
	 * 一个List<String>的Json串，里面是模块引导提示，不超过3条（isv提交时可忽略不传）
	 **/
	public $intros;
	
	/** 
	 * 模块id ,(注意:用户自定义模块不用填此项。)
	 **/
	public $moduleId;
	
	/** 
	 * 模块名称
	 **/
	public $moduleName;
	
	/** 
	 * 是否必填 （isv提交时可忽略不传）
	 **/
	public $required;
	
	/** 
	 * 淘宝图片空间的地址链接，示例模板，最多不超过三个（isv提交时可忽略不传）
	 **/
	public $tplUrls;
	
	/** 
	 * cat_mod:表示是运营设置的类目维度模块，usr_mod表示的是商家自定义模块。
	 **/
	public $type;	
}
?>