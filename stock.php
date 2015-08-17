<?php

/**
 * ECSHOP 商品管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: goods.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('goods'), $db, 'goods_id', 'goods_name');


if($_REQUEST['act'] == 'stock_list')
{
      admin_priv('goods_list');

      // 获取进货单列表
      $final = stock_list();
      $smarty->assign('stock_list', $final['stock_list']);

      $smarty->assign('filter', $final['filter']);
      $smarty->assign('page_count', $final['page_count']);
      $smarty->assign('record_count', $final['record_count']);

      $smarty->display('stock_list.htm');
}

//查看进货单详情
elseif ($_REQUEST['act'] == 'edit')
{
      admin_priv('goods_add'); // 检查权限
      
      $sql = 'SELECT * FROM '.$ecs->table('stock')
            .' WHERE stock_id='.$_GET['stock_id'];
      $stock = $db->getRow($sql);
      $stock['arrival_day'] = date('Y-m-d', $stock['arrival_day']);
      $smarty->assign('stock', $stock);

      $sql = 'SELECT *, FROM_UNIXTIME(production_day,"%Y-%m-%d") production_day 
            FROM '.$ecs->table('stock_goods').' WHERE stock_id='.$_GET['stock_id'];
      $stock_goods = $db->getAll($sql);
      $smarty->assign('stock_goods', $stock_goods);

      $sql = 'SELECT goods_name,goods_sn FROM '.$ecs->table('goods');
      $goods_list = $db->getAll($sql);
      $smarty->assign('goods_list', $goods_list);

      $smarty->assign('act', 'update');

      $smarty->display('stock_add.htm');
}

// 更新进货数据
elseif ($_REQUEST['act'] == 'update')
{
      admin_priv('goods_add'); // 检查权限
      
      extract($_REQUEST);

      $sql = 'UPDATE '.$ecs->table('stock')." SET stock_sn='$stock_sn', arrival_day=UNIX_TIMESTAMP('$arrival_day'), contacter='$contacter', phone='$phone', confirmer='$confirmer' WHERE stock_id=$stock_id";
      $db->query($sql);

      //$sql = 'INSERT INTO '.$ecs->table('stock_goods').'(goods_sn, goods_name, quantity, production_day, manufacturer, nochange)VALUES';
      foreach ($e_goods_sn as $key=>$val)
      {
            $sql = 'UPDATE '.$ecs->table('stock_goods')
                ." SET goods_sn=$val, goods_name=$e_goods_name[$key], 
                quantity=$e_quantity[$key],
                production_day=UNIX_TIMESTAMP('$e_production_day[$key]'), 
                  manufacturer=$e_manufacturer[$key] WHERE sg_id=$key" ;
            $db->query($sql);
      }

      $tmp_goods_name = implode($goods_name);
      if(!empty($tmp_goods_name))
      {
          $sql = 'INSERT INTO '.$ecs->table('stock_goods').'(stock_id, goods_sn, goods_name, quantity, production_day, manufacturer)VALUES';
            foreach ($goods_sn as $key=>$val)
            {
                  $values[] = "('$stock_id', '$val', '$goods_name[$key]', '$quantity[$key]', UNIX_TIMESTAMP('$production_day[$key]'), '$manufacturer[$key]')";
            }

            $sql .= implode(',', $values);
            $db->query($sql);
      }

      // 将订单中的产品数量汇总至库存总量
      /*$sql = 'UPDATE '.$ecs->table('goods').' g, '.$ecs->table('stock_goods')
            .' s'.' SET g.goods_number=g.goods_number+s.quantity, 
            s.collect=1 WHERE g.goods_sn=s.goods_sn AND s.collect=0';
      $db->query($sql);
       */

      sys_msg('进货单修改成功', 1, $link);
}

// 添加进货单
elseif ($_REQUEST['act'] == 'stock_add')
{
      admin_priv('goods_add'); // 检查权限

      // 获取产品列表
      $sql = 'SELECT goods_name,goods_sn FROM '.$ecs->table('goods');
      $res = $db->getAll($sql);
      $smarty->assign('goods_list', $res);

      // 获取品牌列表
      $sql = 'SELECT brand_id, brand_name FROM '.$ecs->table('brand');
      $res = $db->getAll($sql);
      $smarty->assign('brand_list', $res);

      $smarty->assign('act', 'insert');
      $smarty->display('stock_add.htm');
}

elseif ($_REQUEST['act'] == 'insert')
{
      admin_priv('goods_add'); // 检查权限
      $stock = array(
            'stock_sn'    => implode('', explode('-', $_POST['arrival_day'])),
            'class_num'   => count($_POST['goods_sn']),
            'arrival_day' => $_POST['arrival_day'] ? strtotime($_POST['arrival_day']) : time(),
            'contacter'   => trim($_POST['contacter']),
            'phone'       => trim($_POST['phone']),
            'confirmer'   => trim($_POST['confirmer']),
            'adder'       => $_SESSION['admin_id']
      );

      $field = array_keys($stock);
      $value = array_values($stock);
      $sql = 'INSERT INTO '.$ecs->table('stock').'(stock_sn, class_num, arrival_day, contacter, phone, confirmer, adder, add_time)VALUE(\''.implode('\',\'', $value)."',UNIX_TIMESTAMP(NOW()))";
      $db->query($sql);
      $stock_id = $db->insert_id();

      $sql = 'INSERT INTO '.$ecs->table('stock_goods').'(stock_id, goods_sn, goods_name, quantity, prickle, production_day, manufacturer, nochange)VALUES';
      foreach ($_POST['goods_sn'] as $key=>$val)
      {
            $values[] = "('$stock_id', '$val', '{$_POST[goods_name][$key]}', '{$_POST[quantity][$key]}', '{$_POST[prickle][$key]}', UNIX_TIMESTAMP('{$_POST[production_day][$key]}'), '{$_POST[manufacturer][$key]}', '{$_POST[quantity][$key]}')";
      }

      $sql .= implode(',', $values);
      $db->query($sql);

      /* 将订单中的产品数量汇总至库存总量
      $sql = 'UPDATE '.$ecs->table('goods').' g, '.$ecs->table('stock_goods').' s'
            .' SET g.goods_number=g.goods_number+s.quantity, s.collect=1 WHERE g.goods_sn=s.goods_sn AND s.collect=0';
      $db->query($sql);
       */
      sys_msg('进货单添加成功', 1, $link);
      sys_msg($is_insert ? $_LANG['add_goods_ok'] : $_LANG['edit_goods_ok'], 0, $link);
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
      $stock_list = stock_list();

      $handler_list = array();
      $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=card', 'title'=>$_LANG['card'], 'img'=>'icon_send_bonus.gif');
      $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=replenish', 'title'=>$_LANG['replenish'], 'img'=>'icon_add.gif');
      $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=batch_card_add', 'title'=>$_LANG['batch_card_add'], 'img'=>'icon_output.gif');

      if (isset($handler_list[$code]))
      {
            $smarty->assign('add_handler',      $handler_list[$code]);
      }

      $smarty->assign('code',         $code);
      $smarty->assign('stock_list',   $stock_list['stock_list']);
      $smarty->assign('filter',       $stock_list['filter']);
      $smarty->assign('record_count', $stock_list['record_count']);
      $smarty->assign('page_count',   $stock_list['page_count']);
      $smarty->assign('list_type',    'stock');
      $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);

      /* 排序标记 */
      $sort_flag  = sort_flag($goods_list['filter']);
      $smarty->assign($sort_flag['tag'], $sort_flag['img']);

      /* 获取商品类型存在规格的类型 */
      $specifications = get_goods_type_specifications();
      $smarty->assign('specifications', $specifications);
      
      make_json_result($smarty->fetch('stock_list.htm'), '',
            array('filter' => $stock_list['filter'], 'page_count' => $stock_list['page_count']));
}

elseif ($_REQUEST['act'] == 'get_detail')
{
      $sql = 'SELECT stock_sn, FROM_UNIXTIME(arrival_day, "%Y-%m-%d") arrival_day, 
            class_num, confirmer, contacter, phone, adder, 
            FROM_UNIXTIME(add_time, "%Y-%m-%d") add_time FROM '
            .$GLOBALS['ecs']->table('stock').' WHERE stock_id='.$_GET['stock_id'];
      $stock = $GLOBALS['db']->getRow($sql);
      extract($stock);

      $sql = 'SELECT goods_sn, goods_name, quantity,
            FROM_UNIXTIME(production_day, "%Y-%m-%d") production_day,
            manufacturer, brand FROM '.$GLOBALS['ecs']->table('stock_goods')
            .' WHERE stock_id='.$_GET['stock_id'];
      $stock_goods = $GLOBALS['db']->getAll($sql);

      $goods_list = '';
      foreach ($stock_goods as $val)
      {
            $goods_list .= "<tr><td>$val[goods_sn]</td><td>$val[goods_name]</td><td>$val[quantity]</td><td>$val[production_day]</td><td>$val[manufacturer]</td><td>$val[brand]</td></tr>";
      }

      echo <<<EOF
      <div class="pop_head aui_title">
            <span class="f14">进货详细信息</span><span class="right pr10 f30">
<a href="javascript:EV_closeAlert()">×</a></span>
      </div>
<style>
#envon table, td{ border:1px solid #508ecf;border-left:none;border-top:none;}
#envon table {border:1px solid #508ecf;border-right:none;border-bottom:none;}
</style>
      <table class="crm_cut" cellspacing="0" cellpadding="0"  border="0" width="96%">
            <tr>
                  <td width="10%" class="crm_bg_td">进货单号</td>
                  <td width="12%" class="crm_bg_td">进货日期</td>
                  <td width="10%" class="crm_bg_td">产品种数</td>
                  <td width="10%" class="crm_bg_td">确认人员</td>
                  <td width="15%" class="crm_bg_td">厂家联系人</td>
                  <td width="15%" class="crm_bg_td">联系电话</td>
                  <td width="10%" class="crm_bg_td">添加人</td>
                  <td width="15%" class="crm_bg_td">添加时间</td>
            </tr>
            <tr>
                  <td>$stock_sn</td>
                  <td>$arrival_day</td>
                  <td>$class_num</td>
                  <td>$confirmer</td>
                  <td>$contacter</td>
                  <td>$phone</td>
                  <td>$adder</td>
                  <td>$add_time</td>
            </tr>
      </table>
      <table  class="crm_cut">
            <tr>
                  <td class="crm_bg_td" width="10%">商品代号</td>
                  <td class="crm_bg_td" width="20%">商品名称</td>
                  <td class="crm_bg_td" width="10%">进货数量</td>
                  <td class="crm_bg_td" width="20%">生产日期</td>
                  <td class="crm_bg_td" width="20%">生产厂家</td>
                  <td class="crm_bg_td" width="20%">品牌</td>
            </tr>
            $goods_list
      </table>
    <a href="javascript:EV_closeAlert()" class="right sub_bg"></a>
EOF;
}

/* 查询商品的进货批次及库存情况 */
elseif ($_REQUEST['act'] == 'stock_batch')
{
      $sql = 'SELECT FROM_UNIXTIME(sg.production_day, "%Y-%m-%d") production_day, quantity, prickle FROM '
            .$GLOBALS['ecs']->table('stock').' s, '
            .$GLOBALS['ecs']->table('stock_goods').' sg '
            ." WHERE s.stock_id=sg.stock_id AND sg.goods_sn='$_POST[sn]' "
            .' ORDER BY production_day ASC ';
      $batch_res = $GLOBALS['db']->getAll($sql);
      
      if (count($batch_res))
      {
            $smarty->assign('batch_res', $batch_res);
            echo $smarty->fetch('stock_batch.htm');
            exit;
      }
      else 
      {
            echo '该商品尚无进货记录';
            exit;
      }
}

/**
 * 修改商品库存
 * @param   string  $goods_id   商品编号，可以为多个，用 ',' 隔开
 * @param   string  $value      字段值
 * @return  bool
 */
function update_goods_stock($goods_id, $value)
{
      if ($goods_id)
      {
            /* $res = $goods_number - $old_product_number + $product_number; */
            $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . "
                  SET goods_number = goods_number + $value,
                  last_update = '". gmtime() ."'
                  WHERE goods_id = '$stock_id'";
            $result = $GLOBALS['db']->query($sql);

            /* 清除缓存 */
            clear_cache_files();

            return $result;
      }
      else
      {
            return false;
      }
}

function stock_list()
{
      $result = get_filter();
      
      if ($result === false)
      {
            /* 过滤条件 */
            $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
            if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
            {
                  $filter['keywords'] = json_str_iconv($filter['keywords']);
            }

            $filter['sort_by'] = empty($_REQUEST['sort_by'])    ? 'stock_id' : trim($_REQUEST['sort_by']);
            $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC'     : trim($_REQUEST['sort_order']);

            $ex_where = ' WHERE 1 ';

            // 搜索关键字
            if ($filter['keywords'])
            {
                  //$ex_where .= " AND u.user_name LIKE '%" . mysql_like_quote($filter['keywords']) ."%' OR mobile_phone LIKE '%".mysql_like_quote($filter['keywords'])."%'";
            }

            if ($filter['rank'])
            {
                  $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('stock')." WHERE ";
                  $row = $GLOBALS['db']->getRow($sql);

                  if ($row['special_rank'] > 0)
                  {
                        /* 特殊等级 */
                        //$ex_where .= " AND u.user_rank = '$filter[rank]' ";
                  }
                  else
                  {
                        //$ex_where .= " AND u.rank_points >= " . intval($row['min_points']) . " AND u.rank_points < " . intval($row['max_points']);
                  }
            }

            // START: 这里控制访问权限
            if ($_SESSION['action_list'] != 'all')
            {
                  //$ex_where .= ' AND u.admin_id='.$_SESSION['admin_id']; 
            }
            // END: 这里控制访问权限

            $filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('stock') .' s '. $ex_where);
            
            /* 分页大小 */
            $filter = page_and_size($filter);
            $sql = "SELECT stock_id, stock_sn, FROM_UNIXTIME(arrival_day, '%Y-%m-%d') arrival_day, confirmer, contacter, phone FROM " . $GLOBALS['ecs']->table('stock') .' s '
                  .$ex_where . " ORDER BY " . $filter['sort_by'] . ' ' 
                  . $filter['sort_order'] . " LIMIT " .
                  $filter['start'] . ', '. $filter['page_size'];

            $filter['keywords'] = stripslashes($filter['keywords']);
            set_filter($filter, $sql);
      }
      else
      {
            $sql    = $result['sql'];
            $filter = $result['filter'];
      }

      $stock_list = $GLOBALS['db']->getAll($sql);

      $count = count($stock_list);

      $arr = array('stock_list' => $stock_list, 'filter' => $filter,
            'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

      return $arr;
}

?>
