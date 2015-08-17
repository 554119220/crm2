<?php

/**
 * 商品描述模块化信息，包含类目级别的模块信息以及用户自定义模块约束等其他信息
 * @author auto create
 */
class ModularDescInfo
{
	
	/** 
	 * 旧描述与新描述共存时间（切换时间）。
	 **/
	public $deadLine;
	
	/** 
	 * 运营定义的该商品所属类目的类目级别模块信息列表，列表顺序即为模块顺序。
	 **/
	public $itmDscModules;
	
	/** 
	 * 默认值为false，如果此字段为true，则卖家上传的模块化的描述信息可以自定义排序。
	 **/
	public $userOrder;
	
	/** 
	 * 用户自定义模块数量最大值限制。类目级别模块+用户级别模块必须小于<20
	 **/
	public $usrDefMaxNum;	
}
?>