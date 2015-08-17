<?php

//公用和常用函数
function get_role_group_list(){
    $sql_select = 'SELECT g.group_id,g.group_name,r.role_id,r.role_name FROM '.$GLOBALS['ecs']->table('group').
        ' g LEFT JOIN '.$GLOBALS['ecs']->table('role').' r ON g.role_id=r.role_id ';
    $role_group = $GLOBALS['db']->getAll($sql_select);

    $role_group_list = array();
    foreach($role_group as $val){
        $role_group_list[$val['role_id']]['role_id'] = $val['role_id']; 
        $role_group_list[$val['role_id']]['role_name'] = $val['role_name']; 

        $group_list = array(
            'group_id'   => $val['group_id'],
            'group_name' => $val['group_name']
        );

        $role_group_list[$val['role_id']]['group_list'][] = $group_list; 
    }

    return $role_group_list;
}

function get_only_group_list($where=''){
    $sql_select = 'SELECT group_id,group_name FROM '.$GLOBALS['ecs']->table('group').$where;
    $group_list = $GLOBALS['db']->getAll($sql_select);

    return $group_list;
}

/* 分页大小 */
function filter_page($sql_one,$sql_select,$act){
    $filter['page']             = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    $filter['recorder_size']    = empty($_REQUEST['recorder_size']) ? 15 : intval($_REQUEST['recorder_size']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_one);
    $filter['page_count']   = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4) {
        foreach ($page_set as &$val) {
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count']) {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--) {
            if ($filter['page_count'] - $i > 0) {
                $page_set[] = $filter['page_count'] - $i;
            }
        }
    }

    $filter = array (
        'filter'        => $filter,
        'page_count'    => $filter['page_count'],
        'recorder_size' => $filter['recorder_size'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'page_list'     => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
        'page_start'    => ($filter['page'] - 1)*$filter['page_size'] +1,
        'page_end'      => $filter['page']*$filter['page_size'],
        'act'           => $act,
    );

    $sql_select .= ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    try{
        $resource   = $GLOBALS['db']->getAll($sql_select);
    }catch(Exception $e){
        $resource   = array();
    }

    $result = array('filter'=>$filter,'result'=>$resource);
    return $result;
}

/*获取品牌名*/
function get_brand_id_name()
{
    $sql_select = 'SELECT brand_name FROM '.$GLOBALS['ecs']->table('brand')." WHERE is_show=1";
    $brand = $GLOBALS['db']->getAll($sql_select);
    return $brand;
}

//获取商品
function get_sel_goods($goods_id = 0,$brand_id=0)
{
    $where = " WHERE is_delete=0 AND goods_sn NOT LIKE '%\_%' "; 
    if($goods_id != 0){
        $where .= " AND goods_id=$goods_id ";
    }

    if($brand_id != 0){
        $where .= " AND brand_id=$brand_id ";
    }

    $sql_select = 'SELECT goods_id,goods_name,goods_sn FROM '.$GLOBALS['ecs']->table('goods') .$where;

    return $GLOBALS['db']->getAll($sql_select);
}

//获取品牌列表
function get_brand_id()
{
    $sql_select = 'SELECT brand_id,brand_name FROM '.$GLOBALS['ecs']->table('brand')." WHERE is_show=1";
    $brand = $GLOBALS['db']->getAll($sql_select);
    return $brand;
}

/*整理任务列表结果*/
function format_tasks_list($tasks_list,$period_list,$group_list){
    foreach($tasks_list as &$task){
        foreach($period_list as $period){
            if($task['period_id'] == $period['period_id']){
                $task['deadline'] = $period['period_name'];
            }
        }

        if($task['deadline'] != 0){
            $task['deadline'] = date('Y-m-d',$task['deadline']);
        }

        if(!$task['platform']){
            $task['platform'] = '-';
        }

        foreach($group_list as $group){
            if($group['group_id'] == $task['group_id']){
                $task['group_name'] = $group['group_name'];
            }elseif($task['group_id'] == 0){
                $task['group_name'] = '-';
            }
        }
    }   

    return $tasks_list;
}

/**
 * effects list
 */
function list_effects_common ()
{
    $sql_select = 'SELECT eff_id id,eff_name name FROM '.$GLOBALS['ecs']->table('effects').
        ' WHERE available=1 ORDER BY sort ASC';
    $effects = $GLOBALS['db']->getAll($sql_select);

    return $effects;
}

/**
 * platform list
 */
function list_role_common ()
{
    $sql_select = 'SELECT role_id id,role_name name FROM '.$GLOBALS['ecs']->table('role').
        ' WHERE role_type>0';
    $platform = $GLOBALS['db']->getAll($sql_select);

    return $platform;
}

/**
 * Get characters
 */
function get_characters ()
{
    $sql_select = 'SELECT character_id,characters FROM '.$GLOBALS['ecs']->table('character').
        ' WHERE available=1 ORDER BY sort ASC';
    $characters = $GLOBALS['db']->getAll($sql_select);

    return $characters;
}

//获取部门
function get_role_customer($where){
    $sql  = 'SELECT role_id, role_name FROM '
        .$GLOBALS['ecs']->table('role')." WHERE 1 $where ORDER BY role_id ASC";
    return $GLOBALS['db']->getAll($sql);
}

//对二维数组进行重新排序
function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array      = array();
    $sortable_array = array();
    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }
        switch ($order) {
        case SORT_ASC:
            asort($sortable_array);
            break;
        case SORT_DESC:
            arsort($sortable_array);
            break;
        }
        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }
    return $new_array;
}

