<?php

/**
 * ECSHOP 管理中心团购商品管理
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: gift_buy.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
require_once(ROOT_PATH . 'includes/lib_order.php');

/* 检查权限 */
admin_priv('gift_buy');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 团购活动列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'list')
{
    /* 模板赋值 */
    $smarty->assign('full_page',    1);
    $smarty->assign('ur_here',      $_LANG['gift_buy_list']);
    $smarty->assign('action_link',  array('href' => 'gift_buy.php?act=add', 'text' => $_LANG['add_gift_buy']));

    $list = gift_buy_list();

    $smarty->assign('gift_buy_list',    $list['item']);
    $smarty->assign('filter',           $list['filter']);
    $smarty->assign('record_count',     $list['record_count']);
    $smarty->assign('page_count',       $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示商品列表页面 */
    assign_query_info();
    $smarty->display('gift_buy_list.htm');
}

elseif ($_REQUEST['act'] == 'query')
{
    $list = gift_buy_list();

    $smarty->assign('gift_buy_list', $list['item']);
    $smarty->assign('filter',         $list['filter']);
    $smarty->assign('record_count',   $list['record_count']);
    $smarty->assign('page_count',     $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('gift_buy_list.htm'), '',
          array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加/编辑团购活动
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    /* 初始化/取得团购活动信息 */
    if ($_REQUEST['act'] == 'add')
    {
        $gift_buy = array(
            'gift_id'  => 0,
            //'price_ladder'  => array(array('amount' => 0, 'price' => 0)),
            'start_time'    => date('Y-m-d', time() + 86400),
            'end_time'      => date('Y-m-d', time() + 4 * 86400)
        );
    }
    else
    {
        $gift_buy_id = intval($_REQUEST['id']);
        if ($gift_buy_id <= 0)
        {
            die('invalid param');
        }

        $gift_buy = gift_buy_info($gift_buy_id);
    }

    $smarty->assign('gift_buy', $gift_buy);

    /* 模板赋值 */
    $smarty->assign('ur_here', $_LANG['add_gift_buy']);
    $smarty->assign('action_link', list_link($_REQUEST['act'] == 'add'));
    $smarty->assign('cat_list', cat_list());
    $smarty->assign('brand_list', get_brand_list());

    /* 显示模板 */
    assign_query_info();
    $smarty->display('gift_buy_info.htm');
}

/*------------------------------------------------------ */
//-- 添加/编辑团购活动的提交
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'insert_update')
{
      if (!$_REQUEST['gift_id'])
      {
            // 处理赠品ID 赠品数量
            if (is_array($_POST['target_gift']))
            {
                  foreach ($_POST['target_gift'] as $key=>$val)
                  {
                        list($gift_info[$key]['goods_id'], $gift_info[$key]['goods_num']) = explode('x', $val);
                  }
            }
            else
            {
                  sys_msg($_LANG['error_gifts_null'], 1);
            }

            $gift = array (
                  'promotion'    => trim($_POST['promotion']),       // 促销活动名称
                  'type'         => intval($_POST['type']),          // 促销类型（活动赠品、消费赠品、备注赠品）
                  'goods_id'     => trim($_POST['goods_id']),      // 商品ID
                  'goods_sn'     => trim($_POST['goods_id']),      // 商品ID
                  'packing_id'   => intval($_POST['packing_id']),    // 套餐ID
                  'order_amount' => intval($_POST['order_amount']),  // 订单金额
                  'goods_num'    => intval($_POST['goods_num']),     // 商品数量
                  'gift_info'    => json_encode($gift_info),         // 赠品信息（包含赠品名称，赠品编码和赠品数量）
                  'start_time'   => strtotime($_POST['start_time']), // 开始时间
                  'end_time'     => strtotime($_POST['end_time']),   // 结束时间
                  'platform'     => intval($_POST['platform'])       // 适用平台，1：所有平台（所有平台、天猫、拍拍、京东……）
            );

            $fields = array_keys($gift);
            $values = array_values($gift);
            $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('goods_gift').
                  '('.implode(',', $fields).")VALUES('".implode("','", $values).'\')';
            $GLOBALS['db']->query($sql);
            $gift_id = $GLOBALS['db']->insert_id();

            $links[] = array('text' => $_LANG['back_list'], 'href'=>'gift_buy.php?act=list');
            $msg = $_LANG['add_success'];
      }
      else 
      {
            if (is_array($_POST['target_gift']))
            {
                  foreach ($_POST['target_gift'] as $key=>$val)
                  {
                        list($gift_info[$key]['goods_id'], $gift_info[$key]['goods_num']) = explode('x', $val);
                  }
            }
            else
            {
                  sys_msg($_LANG['error_gifts_null'], 1);
            }

            $del_key = array('giftword', 'gift_name', 'gift_number', 'act', 'submit', 'gift_id');
            foreach ($_REQUEST as $key=>$val)
            {
                  if (in_array($key, $del_key))
                  {
                        continue;
                  }

                  if ($key == 'target_gift')
                  {
                        foreach ($val as $k=>$v)
                        {
                              list($gift_info[$k]['goods_id'], $gift_info[$k]['goods_num']) = explode('x', $v);
                        }

                        $gift_buy[] = 'gift_info=\''.json_encode($gift_info).'\'';
                  }

                  if ($key == 'start_time' || $key == 'end_time')
                  {
                        $gift_buy[] = $key.'='.strtotime($val);
                  }
                  elseif (is_numeric($val))
                  {
                        $gift_buy[] = $key.'='.intval($val);
                  }
                  elseif (is_string($val))
                  {
                        $gift_buy[] = $key.'="'.mysql_real_escape_string(trim($val)).'"';
                  }
            }

            $gift_id = $_POST['gift_id'];
            $sql = 'UPDATE '.$GLOBALS['ecs']->table('goods_gift').
                  ' SET '.implode(',', $gift_buy)." WHERE gift_id=$gift_id";
            $GLOBALS['db']->query($sql);

            $links[] = array('text' => $_LANG['back_list'], 'href'=>'gift_buy.php?act=list');
            $msg = $_LANG['edit_success'];
      }

      if (!empty($gift_id))
      {
          $sql = 'UPDATE '.$GLOBALS['ecs']->table('goods_gift').' gi, '.$GLOBALS['ecs']->table('goods').
              " g SET gi.goods_sn=g.goods_sn WHERE gi.gift_id=$gift_id AND g.goods_id=gi.goods_id";
          $GLOBALS['db']->query($sql);
      }

      sys_msg($msg, 0, $links);
}


/*------------------------------------------------------ */
//-- 批量删除团购活动
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_drop')
{
    if (isset($_POST['checkboxes']))
    {
        $del_count = 0; //初始化删除数量
        foreach ($_POST['checkboxes'] AS $key => $id)
        {
            /* 取得团购活动信息 */
            $gift_buy = gift_buy_info($id);

            /* 如果团购活动已经有订单，不能删除 */
            if ($gift_buy['valid_order'] <= 0)
            {
                /* 删除团购活动 */
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('goods_activity') .
                        " WHERE act_id = '$id' LIMIT 1";
                $GLOBALS['db']->query($sql, 'SILENT');

                admin_log(addslashes($gift_buy['goods_name']) . '[' . $id . ']', 'remove', 'gift_buy');
                $del_count++;
            }
        }

        /* 如果删除了团购活动，清除缓存 */
        if ($del_count > 0)
        {
            clear_cache_files();
        }

        $links[] = array('text' => $_LANG['back_list'], 'href'=>'gift_buy.php?act=list');
        sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
    }
    else
    {
        $links[] = array('text' => $_LANG['back_list'], 'href'=>'gift_buy.php?act=list');
        sys_msg($_LANG['no_select_gift_buy'], 0, $links);
    }
}

/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'search_goods')
{
    //check_authz_json('gift_by');

    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json   = new JSON;
    $filter = $json->decode($_GET['JSON']);
    $arr    = get_goods_list($filter);

    make_json_result($arr);
}

/*------------------------------------------------------ */
//-- 编辑保证金
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_deposit')
{
    check_authz_json('gift_by');

    $id = intval($_POST['id']);
    $val = floatval($_POST['val']);

    $sql = "SELECT ext_info FROM " . $ecs->table('goods_activity') .
            " WHERE act_id = '$id' AND act_type = '" . GAT_GROUP_BUY . "'";
    $ext_info = unserialize($db->getOne($sql));
    $ext_info['deposit'] = $val;

    $sql = "UPDATE " . $ecs->table('goods_activity') .
            " SET ext_info = '" . serialize($ext_info) . "'" .
            " WHERE act_id = '$id'";
    $db->query($sql);

    clear_cache_files();

    make_json_result(number_format($val, 2));
}

/*------------------------------------------------------ */
//-- 编辑保证金
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_restrict_amount')
{
    check_authz_json('gift_by');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    $sql = "SELECT ext_info FROM " . $ecs->table('goods_activity') .
            " WHERE act_id = '$id' AND act_type = '" . GAT_GROUP_BUY . "'";
    $ext_info = unserialize($db->getOne($sql));
    $ext_info['restrict_amount'] = $val;

    $sql = "UPDATE " . $ecs->table('goods_activity') .
            " SET ext_info = '" . serialize($ext_info) . "'" .
            " WHERE act_id = '$id'";
    $db->query($sql);

    clear_cache_files();

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 删除团购活动
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('gift_by');

    $id = intval($_GET['id']);

    /* 取得团购活动信息 */
    $gift_buy = gift_buy_info($id);

    /* 如果团购活动已经有订单，不能删除 */
    if ($gift_buy['valid_order'] > 0)
    {
        make_json_error($_LANG['error_exist_order']);
    }

    /* 删除团购活动 */
    $sql = "DELETE FROM " . $ecs->table('goods_activity') . " WHERE act_id = '$id' LIMIT 1";
    $db->query($sql);

    admin_log(addslashes($gift_buy['goods_name']) . '[' . $id . ']', 'remove', 'gift_buy');

    clear_cache_files();

    $url = 'gift_buy.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/* 搜索赠品 */
elseif ($_REQUEST['act'] == 'get_package_list')
{
     $keyword = mysql_real_escape_string(trim($_REQUEST['pgkeyword']));

     $sql = 'SELECT packing_desc goods_id, packing_name goods_name FROM '.$GLOBALS['ecs']->table('packing').
          " WHERE packing_name LIKE '%$keyword%'";
     $res['content'] = $GLOBALS['db']->getAll($sql);

     include_once(ROOT_PATH . 'includes/cls_json.php');

     $json = new JSON;
     die($json->encode($res));
}

/* 修改活动赠品记录 */
elseif ($_REQUEST['act'] == 'change_status')
{
     // 验证权限
     check_authz_json('change_gift_status');

     $id = str_replace('status_', '', $_POST['data']);
     $sql = 'UPDATE '.$GLOBALS['ecs']->table('goods_gift').
          " SET status=IF(status, 0, 1) WHERE gift_id='$id'";
     $GLOBALS['db']->query($sql);

     $sql = 'SELECT status, start_time, end_time FROM '.$GLOBALS['ecs']->table('goods_gift').
          " WHERE gift_id=$id";
     $status = $GLOBALS['db']->getRow($sql);

     // 判断活动赠品状态
     $now_time = time() +28800;
     $res['id'] = $id;
     if ($status['start_time'] < $now_time && $status['end_time'] > $now_time && $status['status'] == 1)
     {
          $res['status'] = '进行中';
     }
     elseif ($status['end_time'] < $now_time && $status['status'] == 1)
     {
          $res['status'] = '已过期';
     }
     elseif ($status['status'] == 1)
     {
          $res['status'] = '已启用';
     }
     else 
     {
          $res['status'] = '未启用';
     }

     include '..\includes\cls_json.php';
     $json = new JSON;
     die($json->encode($res));
}

/*
 * 取得团购活动列表
 * @return   array
 */
function gift_buy_list()
{
      $result = get_filter();
      if ($result === false)
      {
            /* 过滤条件 */
            $filter['keyword']      = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
            if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
            {
                  $filter['keyword'] = json_str_iconv($filter['keyword']);
            }
            $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'gift_id' : trim($_REQUEST['sort_by']);
            $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

            $where = (!empty($filter['keyword'])) ? " AND promotion LIKE '%" . mysql_like_quote($filter['keyword']) . "%'" : '';

            if ($_SESSION['action_list'] != 'all')
            {
                $where .= "AND platform='{$_SESSION['role_id']}'"; 
            }

            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods_gift').
                  " WHERE 1 $where";
            $filter['record_count'] = $GLOBALS['db']->getOne($sql);

            /* 分页大小 */
            $filter = page_and_size($filter);

            $now_time = time() +28800;
            /* 查询 */
            $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('goods_gift').
                  "  WHERE 1 $where "." ORDER BY $filter[sort_by] $filter[sort_order] ".
                  " LIMIT ". $filter['start'] .", $filter[page_size]";

            $filter['keyword'] = stripslashes($filter['keyword']);
            set_filter($filter, $sql);
      }
      else
      {
            $sql    = $result['sql'];
            $filter = $result['filter'];
      }

      $res = $GLOBALS['db']->getAll($sql);
      foreach ($res as $key=>$val)
      {
            $res[$key]['gift_info']  = json_decode($val['gift_info'], true);
            $res[$key]['start_time'] = local_date($GLOBALS['_CFG']['date_format'],$val['start_time']);
            $res[$key]['end_time']   = local_date($GLOBALS['_CFG']['date_format'],$val['end_time']);

            // 判断活动赠品状态
            if ($val['start_time'] < $now_time && $val['status'] == 1)
            {
                $res[$key]['status'] = '2';
            }

            // 判断活动赠品状态
            if ($val['end_time'] < $now_time && $val['status'] == 1)
            {
                $res[$key]['status'] = 3;
            }
      }

      $arr = array('item' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

      return $arr;
}

/**
 * 取得某商品的团购活动
 * @param   int     $goods_id   商品id
 * @return  array
 */
function goods_gift_buy($goods_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('goods_activity') .
            " WHERE goods_id = '$goods_id' " .
            " AND act_type = '" . GAT_GROUP_BUY . "'" .
            " AND start_time <= " . gmtime() .
            " AND end_time >= " . gmtime();

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 列表链接
 * @param   bool    $is_add         是否添加（插入）
 * @return  array('href' => $href, 'text' => $text)
 */
function list_link($is_add = true)
{
    $href = 'gift_buy.php?act=list';
    if (!$is_add)
    {
        $href .= '&' . list_link_postfix();
    }

    return array('href' => $href, 'text' => $GLOBALS['_LANG']['gift_buy_list']);
}

?>
