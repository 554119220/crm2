<?php

/**
 * 服务平台评价流水对象
 * @author auto create
 */
class ScoreResult
{
	
	/** 
	 * 服务态度评分
	 **/
	public $attitudeScore;
	
	/** 
	 * 平均分
	 **/
	public $avgScore;
	
	/** 
	 * 易用性评分
	 **/
	public $easyuseScore;
	
	/** 
	 * 评价时间
	 **/
	public $gmtCreate;
	
	/** 
	 * 评价id
	 **/
	public $id;
	
	/** 
	 * 是否实际付费 1-实际付费 2-实际未付费
	 **/
	public $isPay;
	
	/** 
	 * 是否为有效评分 1-有效评分 2-无效评分
	 **/
	public $isValid;
	
	/** 
	 * 服务规格code
	 **/
	public $itemCode;
	
	/** 
	 * 服务规格名称
	 **/
	public $itemName;
	
	/** 
	 * 描述相符
	 **/
	public $matchedScore;
	
	/** 
	 * 专业性评分
	 **/
	public $profScore;
	
	/** 
	 * 交片速度
	 **/
	public $rapidScore;
	
	/** 
	 * 服务code
	 **/
	public $serviceCode;
	
	/** 
	 * 稳定性评分
	 **/
	public $stabilityScore;
	
	/** 
	 * 评论内容
	 **/
	public $suggestion;
	
	/** 
	 * 评价人用户昵称
	 **/
	public $userNick;	
}
?>