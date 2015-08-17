<?php

/**
 * 分页信息
 * @author auto create
 */
class TOPPaginator
{
	
	/** 
	 * 当前页码。取值范围:大于零的整数。默认值为1,即默认返回第一页数据。
	 **/
	public $currentPage;
	
	/** 
	 * 是否最后一页
	 **/
	public $isLastPage;
	
	/** 
	 * 每页条数。默认值：12
	 **/
	public $pageSize;
	
	/** 
	 * 搜索到符合条件的结果总数
	 **/
	public $totalResults;	
}
?>