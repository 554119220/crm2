<?php

/**
 * ECSHOP 超值礼包管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: packing.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php');

/*------------------------------------------------------ */
//-- 添加活动
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('packing_manage');

    /* 初始化信息 */
    $start_time = local_date('Y-m-d H:i');
    $end_time   = local_date('Y-m-d H:i', strtotime('+1 month'));
    $packing    = array(
          'packing_price' => '',
          'start_time'    => $start_time,
          'end_time'      => $end_time
    );
    
    create_html_editor('article', $packing['article']);

    $smarty->assign('packing',      $packing);
    $smarty->assign('ur_here',      $_LANG['packing_add']);
    $smarty->assign('action_link',  array('text' => $_LANG['00_packing_list'], 'href'=>'packing.php?act=list'));
    $smarty->assign('cat_list',     cat_list());
    $smarty->assign('brand_list',   get_brand_list());
    $smarty->assign('form_action',  'insert');

    assign_query_info();
    $smarty->display('packing_info.htm');
}

elseif ($_REQUEST['act'] == 'insert')
{
    if ($_SESSION['admin_id'] == 1) {
    echo '<pre>';
    print_r($_REQUEST);
    echo '</pre>';
    }
    /* 权限判断 */
      admin_priv('packing_manage');

      $sql = 'SELECT COUNT(*) FROM '.$ecs->table('packing').' WHERE packing_name="'
            .trim($_POST['packing_name']).'"';
      if ($db->getOne($sql))
      {
            //组合存在时的跳转函数
            sys_msg(sprintf($_LANG['packing_exist'],  $_POST['packing_name']) , 1);
      }

      $record = array (
            'packing_name'       => trim($_POST['packing_name']),
            'packing_type'       => intval($_POST['packing_type']),
            'promote_start_time' => local_strtotime($_POST['start_time']),
            'promote_end_time'   => local_strtotime($_POST['end_time']),
            'add_time'           => gmtime(),
            'add_admin_id'       => $_SESSION['admin_id'],
            'update_time'        => gmtime(),
            'updater'            => $_SESSION['admin_id'],
            'packing_price'      => floatval($_POST['packing_price']),
            'packing_cycle'      => intval($_POST['packing_cycle']),
            'packing_desc'       => trim($_POST['packing_desc']),
            'keywords'           => trim($_POST['keywords']),
            'is_on_sale'         => intval($_POST['is_on_sale']),
            'is_shipping'        => intval($_POST['is_shipping']),
            'packing_promote'    => intval($_POST['packing_promote']),
      );


      if (is_uploaded_file($_FILES['packing_img']['tmp_name']) || is_uploaded_file($_FILES['packing_img_a']['tmp_name']))
      {
            $img_name = date('YmdHis').mt_rand(1000,9999).'.'.pathinfo($_FILES['packing_img']['name'], PATHINFO_EXTENSION);
            $img_name_a = date('YmdHis').mt_rand(1000,9999).'.'.pathinfo($_FILES['packing_img_a']['name'], PATHINFO_EXTENSION);
            $upload_dir = 'images/upload/packing_img';
            if (is_dir(ROOT_PATH.$upload_dir) || mkdir(ROOT_PATH.$upload_dir))
            {
                  $img_name   = $upload_dir.'/'.$img_name;
                  $img_name_a = $upload_dir.'/'.$img_name_a;
            }

            $img_name   && move_uploaded_file(iconv('UTF-8', 'GBK', $_FILES['packing_img']['tmp_name']), ROOT_PATH.$img_name);
            $img_name_a && move_uploaded_file(iconv('UTF-8', 'GBK', $_FILES['packing_img_a']['tmp_name']), ROOT_PATH.$img_name_a);
            $record['packing_img']   = $img_name;
            $record['packing_img_a'] = $img_name_a;
      }

      /* 处理提交数据 */
      if (empty($_POST['packing_price']))
      {
            $_POST['packing_price'] = 0;
      }

      $info = array('packing_price'=>$_POST['packing_price']);

      /* 插入数据 */
      $db->AutoExecute($ecs->table('packing'), $record, 'INSERT');

      /* 组合编号 */
      if ($packing_id = $db->insert_id())
      {
            if (!empty($_POST['article']))
            {
                  $sql = 'INSERT INTO '.$ecs->table('packing_article')."(packing_id, article)VALUES($packing_id, '".htmlspecialchars($_POST[article])."')";
                  $db->query($sql);
            }

            $k = 1;
            foreach ($_POST['target_select'] as $key=>$val) 
            {
                  if (($k && strpos($val, '~')) || strpos($val, '!'))
                  {
                        $packing_goods[$key] = array (
                              'num'       => substr($val, strpos($val, '×')+2, -1),
                              'extension' => strpos($val, '~') ? 2 : 0
                        );
                        strpos($val, '~') && $k--;
                  }
                  else
                  {
                        $packing_goods[$key] = array (
                              'num'       => substr($val, strpos($val, '×')+2),
                              'extension' => 1
                        );
                  }

                  $packing_goods[$key]['packing_id'] = $packing_id;
                  $packing_goods[$key]['goods_id']   = substr($val, 0, strpos($val, '×'));
            }
            
            //将组合中的商品添加至组合商品表中
            if (!handle_packing_goods($packing_id, $packing_goods))
            {
                  sys_msg(sprintf($_LANG['list_failed'],  $_POST['packing_name']) , 1);
            }
      }


      admin_log($_POST['packing_name'],'add','packing');
      $link[] = array('text' => $_LANG['back_list'], 'href'=>'packing.php?act=list');
      $link[] = array('text' => $_LANG['continue_add'], 'href'=>'packing.php?act=add');
      sys_msg($_LANG['add_succeed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑活动
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
      /* 权限判断 */
      admin_priv('packing_manage');

      $packing            = get_packing_info($_REQUEST['id']);       // 商品组合信息
      $packing_goods_list = get_packing_goods($_REQUEST['id']);      // 组合商品列表
      $packing['article'] = htmlspecialchars_decode(get_packing_article($_REQUEST['id']));    // 商品组合描述

      create_html_editor('article',        $packing['article']);
      $smarty->assign('packing',           $packing);
      $smarty->assign('ur_here',           $_LANG['packing_edit']);
      $smarty->assign('action_link',       array('text' => $_LANG['00_packing_list'], 'href'=>'packing.php?act=list&' . list_link_postfix()));
      $smarty->assign('cat_list',     cat_list());
      $smarty->assign('brand_list',   get_brand_list());
      $smarty->assign('form_action',       'update');
      $smarty->assign('packing_goods_list', $packing_goods_list);

      assign_query_info();
      $smarty->display('packing_info.htm');

}
elseif ($_REQUEST['act'] == 'update')
{
      /* 权限判断 */
      admin_priv('packing_manage');

      $packing_id = intval($_POST['id']);

      /* 处理提交数据 */
      if (empty($_POST['packing_price']))
      {
            $_POST['packing_price'] = 0;
      }

      /* 检查活动重名 */
      $sql = 'SELECT COUNT(*) FROM '.$ecs->table('packing').' WHERE packing_name="'
            .trim($_POST['packing_name']).'" AND packing_id<>'.$packing_id;
      if ($db->getOne($sql))
      {
            sys_msg(sprintf($_LANG['packing_exist'],  $_POST['packing_name']) , 1);
      }

      $record = array (
            'packing_name'       => trim($_POST['packing_name']),
            'packing_type'       => intval($_POST['packing_type']),
            'promote_start_time' => local_strtotime($_POST['start_time']),
            'promote_end_time'   => local_strtotime($_POST['end_time']),
            'update_time'        => gmtime(),
            'updater'            => $_SESSION['admin_id'],
            'packing_price'      => floatval($_POST['packing_price']),
            'packing_cycle'      => intval($_POST['packing_cycle']),
            'packing_desc'       => trim($_POST['packing_desc']),
            'keywords'           => trim($_POST['keywords']),
            'is_on_sale'         => intval($_POST['is_on_sale']),
            'is_shipping'        => intval($_POST['is_shipping']),
            'packing_promote'    => intval($_POST['packing_promote']),
      );
      
      if (is_uploaded_file($_FILES['packing_img']['tmp_name']) || is_uploaded_file($_FILES['packing_img_a']['tmp_name']))
      {
            $sql = 'SELECT packing_img, packing_img_a FROM '.$ecs->table('packing')." WHERE packing_id=$packing_id";
            $existed_img = $db->getRow($sql);
            
            $img_name = $existed_img['packing_img'] ? pathinfo($existed_img['packing_img'], PATHINFO_FILENAME).'.'.pathinfo($existed_img['packing_img'], PATHINFO_EXTENSION) : date('YmdHis').mt_rand(1000,9999).'.'.pathinfo($_FILES['packing_img']['name'], PATHINFO_EXTENSION);

            $img_name_a = $existed_img['packing_img_a'] ?  pathinfo($existed_img['packing_img_a'], PATHINFO_FILENAME).'.'.pathinfo($existed_img['packing_img_a'], PATHINFO_EXTENSION) : date('YmdHis').mt_rand(1000,9999).'.'.pathinfo($_FILES['packing_img_a']['name'], PATHINFO_EXTENSION);
            
            $upload_dir = 'images/upload/packing_img';
            if (is_dir(ROOT_PATH.$upload_dir) || mkdir(ROOT_PATH.$upload_dir))
            {
                  $img_name   = $upload_dir.'/'.$img_name;
                  $img_name_a = $upload_dir.'/'.$img_name_a;
            }

            $img_name   && move_uploaded_file(iconv('UTF-8', 'GBK', $_FILES['packing_img']['tmp_name']), ROOT_PATH.$img_name);
            $img_name_a && move_uploaded_file(iconv('UTF-8', 'GBK', $_FILES['packing_img_a']['tmp_name']), ROOT_PATH.$img_name_a);
            $record['packing_img']   = $img_name;
            $record['packing_img_a'] = $img_name_a;
      }
      /* 更新数据 */
      $db->autoExecute($ecs->table('packing'), $record, 'UPDATE', "packing_id=$packing_id");

      /* 更新商品描述 */
      if (!empty($_POST['article']))
      {
            /* 确认该组合是否有 详细描述 */
            $sql = 'SELECT COUNT(*) FROM '.$ecs->table('packing_article')." WHERE packing_id=$packing_id";
            $existed_article = $db->getOne($sql);

            /* 存在 详细描述 则更新，不存在则插入一条 详细描述 */
            if ($existed_article)
            {
                  $sql = 'UPDATE '.$ecs->table('packing_article')." SET article='".htmlspecialchars($_POST[article])."' WHERE packing_id=$packing_id";
            }
            else
            {
                  $sql = 'INSERT INTO '.$ecs->table('packing_article')."(packing_id, article)VALUES($packing_id, '".htmlspecialchars($_POST[article])."')";
            }

            $db->query($sql);
      }

      /* 更新商品组合 */
      /* 删除原商品组合 */
      $sql = 'DELETE FROM '.$ecs->table('packing_goods')." WHERE packing_id=$packing_id";
      $db->query($sql);

      /* 添加新的商品组合 */
      $k = 1;
      foreach ($_POST['target_select'] as $key=>$val) 
      {
            if (($k && strpos($val, '~')) || strpos($val, '!'))
            {
                  $packing_goods[$key] = array (
                        'num'       => substr($val, strpos($val, '×')+2, -1),
                        'extension' => strpos($val, '~') ? 2 : 0
                  );
                  strpos($val, '~') && $k--;
            }
            else
            {
                  $packing_goods[$key] = array (
                        'num'       => substr($val, strpos($val, '×')+2),
                        'extension' => 1
                  );
            }

            $packing_goods[$key]['packing_id'] = $packing_id;
            $packing_goods[$key]['goods_id'] = substr($val, 0, strpos($val, '×'));
      }

      //将组合中的商品添加至组合商品表中
      if (!handle_packing_goods($packing_id, $packing_goods))
      {
            sys_msg(sprintf($_LANG['list_failed'],  $_POST['packing_name']) , 1);
      }

      admin_log($_POST['packing_name'], 'edit', 'packing');
      $link[] = array('text' => $_LANG['back_list'], 'href'=>'packing.php?act=list&' . list_link_postfix());
      sys_msg($_LANG['edit_succeed'],0,$link);
}

/*------------------------------------------------------ */
//-- 删除指定的活动
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'remove')
{
      check_authz_json('packing_manage');

      $id = intval($_GET['id']);

      $sql = "DELETE FROM " .$ecs->table('packing') .
            " WHERE packing_id='$id'";
      if ($db->query($sql))
      {
            $sql = "DELETE FROM " .$ecs->table('packing_goods')." WHERE packing_id='$id'";
            $db->query($sql);
      }

      $url = 'packing.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

      ecs_header("Location: $url\n");
      exit;
}

/*------------------------------------------------------ */
//-- 活动列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'list')
{
      $smarty->assign('ur_here',      $_LANG['00_packing_list']);
      $smarty->assign('action_link',  array('text' => $_LANG['packing_add'], 'href'=>'packing.php?act=add'));

      $packings = get_packinglist();

      $smarty->assign('packing_list', $packings['packings']);
      $smarty->assign('filter',       $packings['filter']);
      $smarty->assign('record_count', $packings['record_count']);
      $smarty->assign('page_count',   $packings['page_count']);

      $sort_flag  = sort_flag($packings['filter']);
      $smarty->assign($sort_flag['tag'], $sort_flag['img']);

      $smarty->assign('full_page',    1);
      assign_query_info();
      $smarty->display('packing_list.htm');
}

/*------------------------------------------------------ */
//-- 查询、翻页、排序
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'query')
{
      $packings = get_packinglist();

      $smarty->assign('packing_list', $packings['packings']);
      $smarty->assign('filter',       $packings['filter']);
      $smarty->assign('record_count', $packings['record_count']);
      $smarty->assign('page_count',   $packings['page_count']);

      $sort_flag  = sort_flag($packings['filter']);
      $smarty->assign($sort_flag['tag'], $sort_flag['img']);

      make_json_result($smarty->fetch('packing_list.htm'), '',
            array('filter' => $packings['filter'], 'page_count' => $packings['page_count']));
}

/*------------------------------------------------------ */
//-- 编辑活动名称
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_packing_name')
{
      check_authz_json('packing_manage');

      $id = intval($_POST['id']);
      $val = json_str_iconv(trim($_POST['val']));

      /* 检查活动重名 */
      $sql = "SELECT COUNT(*) ".
            " FROM " . $ecs->table('packing').
            " WHERE packing_name='$val' AND packing_id <> '$id'" ;
      if ($db->getOne($sql))
      {
            make_json_error(sprintf($_LANG['packing_exist'],  $val));
      }

      $sql = 'UPDATE '.$ecs->table('packing')." SET packing_name='$val' WHERE packing_id=$id";
      if ($db->query($sql))
      {
            make_json_result(stripslashes($val));
      }
}

/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'search_goods')
{
      include_once(ROOT_PATH . 'includes/cls_json.php');
      $json = new JSON;

      $filters = $json->decode($_GET['JSON']);

      $arr = get_goods_list($filters);

      $opt = array();
      foreach ($arr AS $key => $val)
      {
            $opt[$key] = array('value' => $val['goods_id'],
                  'text' => $val['goods_name'],
                  'data' => $val['shop_price']);

            $opt[$key]['products'] = get_good_products($val['goods_id']);
      }

      make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 搜索商品，仅返回名称及ID
/*------------------------------------------------------ */

//elseif ($_REQUEST['act'] == 'get_goods_list')
//{
//    include_once(ROOT_PATH . 'includes/cls_json.php');
//    $json = new JSON;
//
//    $filters = $json->decode($_GET['JSON']);
//
//    $arr = get_goods_list($filters);
//
//    $opt = array();
//    foreach ($arr AS $key => $val)
//    {
//        $opt[$key] = array('value' => $val['goods_id'],
//                        'text' => $val['goods_name'],
//                        'data' => $val['shop_price']);
//
//        $opt[$key]['products'] = get_good_products($val['goods_id']);
//    }
//
//    make_json_result($opt);
//}

/*------------------------------------------------------ */
//-- 修改商品组合：上架、免运费、优惠
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'change')
{
      $id    = substr($_POST['id'], strrpos($_POST['id'], '~')+1);
      $field = substr($str, 0, strpos($str, '~'));

      $sql = 'UPDATE '.$GLOBALS['ecs']->table('packing')." SET $field=IF($field, 0, 1) WHERE packing_id=$id";

      if ($GLOBALS['db']->query($sql))
      {
            $sql = "SELECT $field FROM ".$ecs->table('packing')." WHERE packing_id=$id";

            $res['img'] = $db->getOne($sql);
            $res['id']  = $_POST['id'];

            include_once('../includes/cls_json.php');
            $json = new JSON();
            die($json->encode($res));
      }
}

/*------------------------------------------------------ */
//-- 删除一个商品
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'drop_packing_goods')
{
      include_once(ROOT_PATH . 'includes/cls_json.php');
      $json = new JSON;

      check_authz_json('packing_manage');

      $fittings   = $json->decode($_GET['drop_ids']);
      $arguments  = $json->decode($_GET['JSON']);
      $packing_id = $arguments[0];

      $goods  = array();
      $g_p    = array();
      foreach ($fittings AS $val)
      {
            $val_array = explode('_', $val);
            if (isset($val_array[1]) && $val_array[1] > 0)
            {
                  $g_p['product_id'][] = $val_array[1];
                  $g_p['goods_id'][] = $val_array[0];
            }
            else
            {
                  $goods[] = $val_array[0];
            }
      }

      if (!empty($goods))
      {
            $sql = "DELETE FROM " .$ecs->table('packing') .
                  " WHERE packing_id='$packing_id' AND " .db_create_in($goods, 'goods_id');
            if ($packing_id == 0)
            {
                  $sql .= " AND admin_id = '$_SESSION[admin_id]'";
            }
            $db->query($sql);
      }

      if (!empty($g_p))
      {
            $sql = "DELETE FROM " .$ecs->table('packing') .
                  " WHERE packing_id='$packing_id' AND " .db_create_in($g_p['goods_id'], 'goods_id') . " AND " . db_create_in($g_p['product_id'], 'product_id');
            if ($packing_id == 0)
            {
                  $sql .= " AND admin_id = '$_SESSION[admin_id]'";
            }
            $db->query($sql);
      }

      $arr = get_packing_goods($packing_id);
      $opt = array();

      foreach ($arr AS $val)
      {
            $opt[] = array('value'      => $val['goods_id'],
                  'text'      => $val['goods_name'],
                  'data'      => '');
      }

      clear_cache_files();
      make_json_result($opt);
}


/**
 * 获取活动列表
 *
 * @access  public
 *
 * @return void
 */
function get_packinglist()
{
      $result = get_filter();
      if ($result === false)
      {
            /* 查询条件 */
            $filter['keywords']   = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
            if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
            {
                  $filter['keywords'] = json_str_iconv($filter['keywords']);
            }
            $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'packing_id' : trim($_REQUEST['sort_by']);
            $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

            $where = (!empty($filter['keywords'])) ? " AND packing_name like '%". mysql_like_quote($filter['keywords']) ."%'" : '';

            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('packing');
            $filter['record_count'] = $GLOBALS['db']->getOne($sql);

            $filter = page_and_size($filter);

            /* 获活动数据 */
            $sql = "SELECT packing_id, packing_name AS packing_name, promote_start_time, promote_end_time, packing_price, is_on_sale, is_shipping, packing_promote ".
                  " FROM " . $GLOBALS['ecs']->table('packing') .
                  " ORDER by $filter[sort_by] $filter[sort_order] LIMIT ". $filter['start'] .", " . $filter['page_size'];

            $filter['keywords'] = stripslashes($filter['keywords']);
            set_filter($filter, $sql);
      }
      else
      {
            $sql    = $result['sql'];
            $filter = $result['filter'];
      }

      $row = $GLOBALS['db']->getAll($sql);

      foreach ($row AS $key => $val)
      {
            $row[$key]['promote_start_time'] = local_date($GLOBALS['_CFG']['date_format'], $val['promote_start_time']);
            $row[$key]['promote_end_time']   = local_date($GLOBALS['_CFG']['date_format'], $val['promote_end_time']);
      }

      $arr = array('packings' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

      return $arr;
}

/**
 * 保存组合的商品
 * @param   int     $packing_id
 * @param   array   $packing_goods
 * @return  void
 */
function handle_packing_goods($packing_id, $packing_goods)
{
      $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('packing_goods').'(num, extension, packing_id, goods_id)VALUES';
      foreach ($packing_goods as $val) 
      {
            $values[] = '('.implode(',', array_values($val)).')';
      }

      $sql .= implode(',', $values);
      if ($GLOBALS['db']->query($sql))
      {
            $sql = 'UPDATE '.$GLOBALS['ecs']->table('packing_goods').' p, '
                  .$GLOBALS['ecs']->table('goods').' g '
                  ." SET p.goods_name=g.goods_name, p.goods_price=g.shop_price, p.brand_id=g.brand_id WHERE p.goods_id=g.goods_id AND p.packing_id=$packing_id";
            if ($GLOBALS['db']->query($sql))
            {
                  $goods_price = $GLOBALS['db']->getOne("SELECT SUM(num*goods_price) FROM ".$GLOBALS['ecs']->table('packing_goods')." WHERE packing_id=$packing_id");
                  $GLOBALS['db']->query('UPDATE '.$GLOBALS['ecs']->table('packing')." SET goods_price=$goods_price-packing_price WHERE packing_id=$packing_id");
                  return true;
            }
            else
            {
                  return false;
            }
      }
}
?>
