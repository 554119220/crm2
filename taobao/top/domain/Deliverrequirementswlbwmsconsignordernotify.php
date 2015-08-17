<?php

/**
 * 配送要求
 * @author auto create
 */
class Deliverrequirementswlbwmsconsignordernotify
{
	
	/** 
	 * 送达日期
	 **/
	public $scheduleDay;
	
	/** 
	 * 送达结束时间
	 **/
	public $scheduleEnd;
	
	/** 
	 * 送达开始时间
	 **/
	public $scheduleStart;
	
	/** 
	 * 投递时延要求:  1-工作日 2-节假日 101,当日达102次晨达103次日达 111 活动标  104 预约达
	 **/
	public $scheduleType;	
}
?>