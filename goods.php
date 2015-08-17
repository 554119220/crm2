<?php
/**
 * ECSHOP 产品管理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: order.php 17157 2010-05-13 06:02:31Z yehuaixiao $
 */
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('goods'), $db, 'goods_id', 'goods_name');

/*-- 仓库子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
     $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
     $nav = list_nav();
     $smarty->assign('nav_2nd', $nav[1][$file]);
     $smarty->assign('nav_3rd', $nav[2]);
     $smarty->assign('file_name', $file);

     die($smarty->fetch('left.htm'));
}

/*--- 商品列表 ---*/
elseif($_REQUEST['act'] == 'goods_list')
{
     $res = array ();
     if (isset($_REQUEST['ext']))
     {
          $file = basename($_SERVER['PHP_SELF'], '.php');
          $nav = list_nav();
          $smarty->assign('nav_2nd', $nav[1][$file]);
          $smarty->assign('nav_3rd', $nav[2]);
          $smarty->assign('file_name', $file);
          $res['left'] = $smarty->fetch('left.htm');  
     }

    //给模板赋值；
    @$account_list = get_accountlist($goods_id, $type);

    $smarty->assign('goods_list',   $account_list['account']);
    $smarty->assign('filter',       $account_list['filter']);
    $smarty->assign('record_count', $account_list['record_count']);
    $smarty->assign('page_count',   $account_list['page_count']);
    $smarty->assign('pageprev',     $account_list['filter']['page']-1);
    $smarty->assign('pagenext',     $account_list['filter']['page']+1);
	
    $res['main'] = $smarty->fetch('goods_list.htm');        
    die($json->encode($res));
   
}

/*--- 检查库存批次 ---*/
elseif($_REQUEST['act'] == 'check_stock_batch')
{
    $goods_sn = $_GET['goods_sn'];
    $res['info']['id'] = $goods_sn;
    $res['response_action'] = 'check_stock_batch';
    $sql_select = 'SELECT quantity,FROM_UNIXTIME(production_day, "%Y-%m-%d") production_day
        FROM '.$GLOBALS['ecs']->table('stock_goods')." WHERE  goods_sn ='".$goods_sn."'";	
	$check_list = $GLOBALS['db']->getAll($sql_select);
	$smarty->assign('check_list',$check_list);
    $res['info']['info'] = $smarty->fetch('batch.htm');

        die($json->encode($res));	
}

/*--- 添加商品 ---*/
else if($_REQUEST['act'] == 'add_goods')
{
    $res = array ();
     if (isset($_REQUEST['ext']))
     {
          $file = basename($_SERVER['PHP_SELF'], '.php');
          $nav = list_nav();
          $smarty->assign('nav_2nd', $nav[1][$file]);
          $smarty->assign('nav_3rd', $nav[2]);
          $smarty->assign('file_name', $file);
          $res['left'] = $smarty->fetch('left.htm');
         
     }

     include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件

      $is_add = $_REQUEST['act'] == 'add'; // 添加还是编辑的标识
      $is_copy = $_REQUEST['act'] == 'copy'; //是否复制
      $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
      $code=$code=='virual_card' ? 'virual_card': '';

      if ($code == 'virual_card')
      {
            admin_priv('virualcard'); // 检查权限
      }
      else
      {
            admin_priv('goods_add'); // 检查权限
      }

      /* 供货商名 */
      $suppliers_list_name = suppliers_list_name();
      $suppliers_exists = 1;
      if (empty($suppliers_list_name))
      {
            $suppliers_exists = 0;
      }

      $smarty->assign('suppliers_exists', $suppliers_exists);
      $smarty->assign('suppliers_list_name', $suppliers_list_name);
      unset($suppliers_list_name, $suppliers_exists);

      /* 如果是安全模式，检查目录是否存在 */
      if (ini_get('safe_mode') == 1 && (!file_exists('../' . IMAGE_DIR . '/'.date('Ym')) || !is_dir('../' . IMAGE_DIR . '/'.date('Ym'))))
      {
            if (@!mkdir('../' . IMAGE_DIR . '/'.date('Ym'), 0777))
            {
                  $warning = sprintf($_LANG['safe_mode_warning'], '../' . IMAGE_DIR . '/'.date('Ym'));
                  $smarty->assign('warning', $warning);
            }
      }

      /* 如果目录存在但不可写，提示用户 */
      elseif (file_exists('../' . IMAGE_DIR . '/'.date('Ym')) && file_mode_info('../' . IMAGE_DIR . '/'.date('Ym')) < 2)
      {
            $warning = sprintf($_LANG['not_writable_warning'], '../' . IMAGE_DIR . '/'.date('Ym'));
            $smarty->assign('warning', $warning);
      }

      /* 取得商品信息 */
      if ($is_add)
      {
            /* 默认值 */
            $last_choose = array(0, 0);
            if (!empty($_COOKIE['ECSCP']['last_choose']))
            {
                  $last_choose = explode('|', $_COOKIE['ECSCP']['last_choose']);
            }

            $goods = array(
                  'goods_id'           => 0,
                  'goods_desc'         => '',
                  'cat_id'             => $last_choose[0],
                  'brand_id'           => $last_choose[1],
                  'is_on_sale'         => '1',
                  'is_alone_sale'      => '1',
                  'is_shipping'        => '0',
                  //'other_cat'          => array(), // 扩展分类
                  'goods_type'         => 0,       // 商品类型
                  'shop_price'         => 0,
                  //'promote_price'      => 0,
                  //'market_price'       => 0,
                  //'integral'           => 0,
                  'goods_number'       => $_CFG['default_storage'],
                  'warn_number'        => 1,
                  //'promote_start_date' => local_date('Y-m-d'),
                  //'promote_end_date'   => local_date('Y-m-d', local_strtotime('+1 month')),
                  'goods_weight'       => 0,
                  //'give_integral'      => -1,
                  //'rank_integral'      => -1,

                  'shelflife'          => 24,
                  'per_number'         => 100,
                  'every_num'          => 3,
                  'everyday_use'       => 3,
                  'every_num_units'    => '粒',
                  'per_number_units'   => '粒'
            );

            if ($code != '')
            {
                  $goods['goods_number'] = 0;
            }

            /* 关联商品 */
            $link_goods_list = array();
            $sql = "DELETE FROM " . $ecs->table('link_goods') .
                  " WHERE (goods_id = 0 OR link_goods_id = 0)" .
                  " AND admin_id = '$_SESSION[admin_id]'";
            $db->query($sql);

            /* 组合商品 */
            $group_goods_list = array();
            $sql = "DELETE FROM " . $ecs->table('group_goods') .
                  " WHERE parent_id = 0 AND admin_id = '$_SESSION[admin_id]'";
            $db->query($sql);

            /* 关联文章 */
            $goods_article_list = array();
            $sql = "DELETE FROM " . $ecs->table('goods_article') .
                  " WHERE goods_id = 0 AND admin_id = '$_SESSION[admin_id]'";
            $db->query($sql);

            /* 属性 */
            $sql = "DELETE FROM " . $ecs->table('goods_attr') . " WHERE goods_id = 0";
            $db->query($sql);

            /* 图片列表 */
            $img_list = array();
      }
      else
      {
            /* 商品信息 */
            $sql = 'SELECT * FROM ' . $ecs->table('goods') . " WHERE goods_id = '$_REQUEST[goods_id]'";
            $goods = $db->getRow($sql);

            /* 虚拟卡商品复制时, 将其库存置为0*/
            if ($is_copy && $code != '')
            {
                  $goods['goods_number'] = 0;
            }

            if (empty($goods) === true)
            {
                  /* 默认值 */
                  $goods = array(
                        'goods_id'           => 0,
                        'goods_desc'         => '',
                        'cat_id'             => 0,
                        'is_on_sale'         => '1',
                        'is_alone_sale'      => '1',
                        'is_shipping'        => '0',
                        'other_cat'          => array(), // 扩展分类
                        'goods_type'         => 0,       // 商品类型
                        'shop_price'         => 0,
                        'promote_price'      => 0,
                        'market_price'       => 0,
                        'integral'           => 0,
                        'goods_number'       => 1,
                        'warn_number'        => 1,
                        'promote_start_date' => local_date('Y-m-d'),
                        'goods_weight'       => 0,
                        'give_integral'      => -1,
                        'rank_integral'      => -1,
                        'shelflife'          => 24,
                        'per_number'         => 100,
                        'every_num'          => 3,
                        'everyday_use'       => 3,
                        'per_number_units'   => '粒',
                        'every_num_units'    => '粒'
                  );
            }

            /* 获取商品类型存在规格的类型 */
            $specifications = get_goods_type_specifications();
            $goods['specifications_id'] = $specifications[$goods['goods_type']];
            $_attribute = get_goods_specifications_list($goods['goods_id']);
            $goods['_attribute'] = empty($_attribute) ? '' : 1;

            /* 根据商品重量的单位重新计算 */
            if ($goods['goods_weight'] > 0)
            {
                  $goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
            }

            if (!empty($goods['goods_brief']))
            {
                  $goods['goods_brief'] = trim_right($goods['goods_brief']);
                  $goods['goods_brief'] = $goods['goods_brief'];
            }
            if (!empty($goods['keywords']))
            {
                  $goods['keywords']    = trim_right($goods['keywords']);
                  $goods['keywords']    = $goods['keywords'];
            }

            /* 如果不是促销，处理促销日期 */
            if (isset($goods['is_promote']) && $goods['is_promote'] == '0')
            {
                  unset($goods['promote_start_date']);
                  unset($goods['promote_end_date']);
            }
            else
            {
                  $goods['promote_start_date'] = local_date('Y-m-d', $goods['promote_start_date']);
                  $goods['promote_end_date'] = local_date('Y-m-d', $goods['promote_end_date']);
            }

            /* 如果是复制商品，处理 */
            if ($_REQUEST['act'] == 'copy')
            {
                  // 商品信息
                  $goods['goods_id']     = 0;
                  $goods['goods_sn']     = '';
                  $goods['goods_name']   = '';
                  $goods['goods_img']    = '';
                  $goods['goods_thumb']  = '';
                  $goods['original_img'] = '';

                  // 扩展分类不变

                  // 关联商品
                  $sql = "DELETE FROM " . $ecs->table('link_goods') .
                        " WHERE (goods_id = 0 OR link_goods_id = 0)" .
                        " AND admin_id = '$_SESSION[admin_id]'";
                  $db->query($sql);

                  $sql = "SELECT '0' AS goods_id, link_goods_id, is_double, '$_SESSION[admin_id]' AS admin_id" .
                        " FROM " . $ecs->table('link_goods') .
                        " WHERE goods_id = '$_REQUEST[goods_id]' ";
                  $res = $db->query($sql);
                  while ($row = $db->fetchRow($res))
                  {
                        $db->autoExecute($ecs->table('link_goods'), $row, 'INSERT');
                  }

                  $sql = "SELECT goods_id, '0' AS link_goods_id, is_double, '$_SESSION[admin_id]' AS admin_id" .
                        " FROM " . $ecs->table('link_goods') .
                        " WHERE link_goods_id = '$_REQUEST[goods_id]' ";
                  $res = $db->query($sql);
                  while ($row = $db->fetchRow($res))
                  {
                        $db->autoExecute($ecs->table('link_goods'), $row, 'INSERT');
                  }

                  // 配件
                  $sql = "DELETE FROM " . $ecs->table('group_goods') .
                        " WHERE parent_id = 0 AND admin_id = '$_SESSION[admin_id]'";
                  $db->query($sql);

                  $sql = "SELECT 0 AS parent_id, goods_id, goods_price, '$_SESSION[admin_id]' AS admin_id " .
                        "FROM " . $ecs->table('group_goods') .
                        " WHERE parent_id = '$_REQUEST[goods_id]' ";
                  $res = $db->query($sql);
                  while ($row = $db->fetchRow($res))
                  {
                        $db->autoExecute($ecs->table('group_goods'), $row, 'INSERT');
                  }

                  // 关联文章
                  $sql = "DELETE FROM " . $ecs->table('goods_article') .
                        " WHERE goods_id = 0 AND admin_id = '$_SESSION[admin_id]'";
                  $db->query($sql);

                  $sql = "SELECT 0 AS goods_id, article_id, '$_SESSION[admin_id]' AS admin_id " .
                        "FROM " . $ecs->table('goods_article') .
                        " WHERE goods_id = '$_REQUEST[goods_id]' ";
                  $res = $db->query($sql);
                  while ($row = $db->fetchRow($res))
                  {
                        $db->autoExecute($ecs->table('goods_article'), $row, 'INSERT');
                  }

                  // 图片不变

                  // 商品属性
                  $sql = "DELETE FROM " . $ecs->table('goods_attr') . " WHERE goods_id = 0";
                  $db->query($sql);

                  $sql = "SELECT 0 AS goods_id, attr_id, attr_value, attr_price " .
                        "FROM " . $ecs->table('goods_attr') .
                        " WHERE goods_id = '$_REQUEST[goods_id]' ";
                  $res = $db->query($sql);
                  while ($row = $db->fetchRow($res))
                  {
                        $db->autoExecute($ecs->table('goods_attr'), addslashes_deep($row), 'INSERT');
                  }
            }

            // 扩展分类
            $other_cat_list = array();
            $sql = "SELECT cat_id FROM " . $ecs->table('goods_cat') . " WHERE goods_id = '$_REQUEST[goods_id]'";
            $goods['other_cat'] = $db->getCol($sql);
            foreach ($goods['other_cat'] AS $cat_id)
            {
                  $other_cat_list[$cat_id] = cat_list(0, $cat_id);
            }
            $smarty->assign('other_cat_list', $other_cat_list);

            $link_goods_list    = get_linked_goods($goods['goods_id']); // 关联商品
            $group_goods_list   = get_group_goods($goods['goods_id']); // 配件
            $goods_article_list = get_goods_articles($goods['goods_id']);   // 关联文章

            /* 商品图片路径 */
            if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 10) && !empty($goods['original_img']))
            {
                  $goods['goods_img'] = get_image_path($_REQUEST['goods_id'], $goods['goods_img']);
                  $goods['goods_thumb'] = get_image_path($_REQUEST['goods_id'], $goods['goods_thumb'], true);
            }

            /* 图片列表 */
            $sql = "SELECT * FROM " . $ecs->table('goods_gallery') . " WHERE goods_id = '$goods[goods_id]'";
            $img_list = $db->getAll($sql);

            /* 格式化相册图片路径 */
            if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0))
            {
                  foreach ($img_list as $key => $gallery_img)
                  {
                        $gallery_img[$key]['img_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], false, 'gallery');
                        $gallery_img[$key]['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true, 'gallery');
                  }
            }
            else
            {
                  foreach ($img_list as $key => $gallery_img)
                  {
                        $gallery_img[$key]['thumb_url'] = '../' . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
                  }
            }
      }

      /* 拆分商品名称样式 */
      $goods_name_style = explode('+', empty($goods['goods_name_style']) ? '+' : $goods['goods_name_style']);

      /* 创建 html editor */
      create_html_editor('goods_desc', $goods['goods_desc']);

      /* 模板赋值 */
      $smarty->assign('code',    $code);
      $smarty->assign('ur_here', $is_add ? (empty($code) ? $_LANG['02_goods_add'] : $_LANG['51_virtual_card_add']) : ($_REQUEST['act'] == 'edit' ? $_LANG['edit_goods'] : $_LANG['copy_goods']));
      //$smarty->assign('action_link', list_link($is_add, $code));
      $smarty->assign('goods', $goods);
      $smarty->assign('goods_name_color', $goods_name_style[0]);
      $smarty->assign('goods_name_style', $goods_name_style[1]);
      $smarty->assign('cat_list', cat_list(0, $goods['cat_id']));
      $smarty->assign('brand_list', get_brand_list());
      $smarty->assign('unit_list', get_unit_list());
      $smarty->assign('user_rank_list', get_user_rank_list());
      $smarty->assign('weight_unit', $is_add ? ($goods['goods_weight'] >= 1 ? '1' : '0.001') : '1');
      $smarty->assign('cfg', $_CFG);
      $smarty->assign('form_act', $is_add ? 'insert' : ($_REQUEST['act'] == 'edit' ? 'update' : 'insert'));
      
      if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
      {
            $smarty->assign('is_add', true);
      }

      if(!$is_add)
      {
            $smarty->assign('member_price_list', get_member_price_list($_REQUEST['goods_id']));
      }

      $smarty->assign('link_goods_list', $link_goods_list);
      $smarty->assign('group_goods_list', $group_goods_list);
      $smarty->assign('goods_article_list', $goods_article_list);
      $smarty->assign('img_list', $img_list);
      $smarty->assign('goods_type_list', goods_type_list($goods['goods_type']));
      $smarty->assign('gd', gd_version());
      $smarty->assign('thumb_width', $_CFG['thumb_width']);
      $smarty->assign('thumb_height', $_CFG['thumb_height']);
      $smarty->assign('goods_attr_html', build_attr_html($goods['goods_type'], $goods['goods_id']));
      $volume_price_list = '';
      if(isset($_REQUEST['goods_id']))
      {
            $volume_price_list = get_volume_price_list($_REQUEST['goods_id']);
      }

      if (empty($volume_price_list))
      {
            $volume_price_list = array('0'=>array('number'=>'','price'=>''));
      }

      $smarty->assign('volume_price_list', $volume_price_list);
      /* 显示商品信息页面 */
      assign_query_info();
      $res['main'] = $smarty->fetch('add_goods.htm');   

    die($json->encode($res));
}

/*--- 检查商品编号是否重复 ---*/
elseif ($_REQUEST['act'] == 'check_goods_sn')
{
    //print_r($_REQUEST);die;
      $goods_sn = trim($_REQUEST['goods_sn']);
 
      /* 检查是否重复 */
      $sql_select = 'SELECT goods_id FROM '.$GLOBALS['ecs']->table('goods').
          " WHERE goods_sn='$_REQUEST[old_sn]'";
      $old_id = $GLOBALS['db']->getOne($sql_select);

      $sql_select = 'SELECT goods_id FROM '.$GLOBALS['ecs']->table('goods').
          " WHERE goods_sn='$_REQUEST[goods_sn]'";
      $new_id = $GLOBALS['db']->getOne($sql_select);

      if( $new_id && $old_id != $new_id)
      {
          $res['req_msg'] = true;
          $res['message'] = '商品编号已存在！！';
          $res['timeout'] = 2000;

          die($json->encode($res));
      }
     $res['second'] = 'second';
     $res['name'] = 'goods_sn';
     $res['value'] = $goods_sn;
     $res['tagName'] = 'input';


     die($json->encode($res));    
}

/*--- 添加商品分类 ----*/
elseif($_REQUEST['act'] == 'add_category')
{
    $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
    $category = empty($_REQUEST['cat']) ? '' : trim($_REQUEST['cat']);

    if(cat_exists($category, $parent_id))
    {
        $res['code'] = 1;
        $res['message'] = '分类存在';

        die($json->encode($res));
    }
    else
    {
        $sql_insert = 'INSERT INTO ' . $ecs->table('category') .
            '(cat_name, parent_id, is_show)' ." VALUES ( '$category', '$parent_id', 1)";
        $GLOBALS['db']->query($sql_insert);
        $category_id = $GLOBALS['db']->insert_id();

        $res['id'] = $category_id;
        $res['parent_id'] = $parent_id;
        $res['cat'] = $category;

        die($json->encode($res));
    }
}

/*--- 添加品牌 ---*/
elseif($_REQUEST['act'] == 'add_brand')
{
    $brand = empty($_REQUEST['brand']) ? '' : trim($_REQUEST['brand']);

    if(brand_exists($brand))
    {
        $res['code'] = 1;
        $res['message'] = '品牌已存在';

        die($json->encode($res));
    }
    else
    {
        $sql_insert = 'INSERT INTO ' . $GLOBALS['ecs']->table('brand') . '(brand_name)' .
               "VALUES ( '$brand')";
        $GLOBALS['db']->query($sql_insert);
        $brand_id = $GLOBALS['db']->insert_id();

        $res['id'] = $brand_id;
        $res['brand'] = $brand;

        die($json->encode($res));
    }
}

/*--- 提交添加商品 ---*/
elseif($_REQUEST['act'] == 'add_goods_submit')
{
    //接收数据
    $goods_name = trim($_REQUEST['goods_name']);
    $goods_sn = trim($_REQUEST['goods_sn']);
    $goods_brief = mysql_real_escape_string($_REQUEST['goods_brief']);
    $cat_id = intval($_REQUEST['cat_id']);
    $brand_id = intval($_REQUEST['brand_id']);
    $shop_price = floatval($_REQUEST['shop_price']);
    $goods_number = intval($_REQUEST['goods_number']);
    $goods_number_units = $_REQUEST['goods_number_units'];
    $warn_number = intval($_REQUEST['warn_number']);
    $packing = $_REQUEST['packing'];
    $shelflife = intval($_REQUEST['shelflife']);
    $per_unit_number = intval($_REQUEST['per_unit_number']);
    $per_number_units = $_REQUEST['per_number_units'];
    $goods_weight = floatval($_REQUEST['goods_weight']);
    $every_num = intval($_REQUEST['every_num']);
    $everyday_use = $_REQUEST['everyday_use'];
    $add_time = time();

    //防止提交空数据
    if($goods_name == '' || $goods_sn == '' || $shop_price == '')
    {
        $res['req_msg'] = true;
        $res['message'] = '提交内容不能为空';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('goods').
        '(goods_name,goods_sn,goods_brief,cat_id,brand_id,shop_price,goods_number,
        goods_number_units,warn_number,packing,shelflife,per_unit_number,
        per_number_units,goods_weight,every_num,everyday_use,add_time) VALUES ('.
        "'$goods_name','$goods_sn','$goods_brief','$cat_id','$brand_id','$shop_price',
        '$goods_number','$goods_number_units','$warn_number','$packing','$shelflife',
        '$per_unit_number','$per_number_units','$goods_weight','$every_num','$everyday_use',
    '$add_time')";
    $result = $GLOBALS['db']->query($sql_insert);
    if($result)
    {
        $res['req_msg'] = true;
        $res['message'] = '添加成功';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '添加失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
}

/*--- 编辑商品页面 ---*/
elseif($_REQUEST['act'] == 'edit_goods')
{
    $goods_id = $_GET['goods_id'];

    $sql_select = 'SELECT g.*,c.cat_name,b.brand_name FROM '
        .$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('category').' c,'
        .$GLOBALS['ecs']->table('brand')." b WHERE goods_id=$goods_id ".
        ' AND g.cat_id=c.cat_id AND g.brand_id=b.brand_id';
    $goods = $GLOBALS['db']->getRow($sql_select);

    //给商品分类赋值
    $smarty->assign('cat_list', cat_list(0, $goods['cat_id']));
    //给品牌赋值
    $smarty->assign('brand_list', get_brand_list());
    $smarty->assign('goods',$goods);
    $res['main'] = $smarty->fetch('edit_goods.htm');   
    
    die($json->encode($res));
}

/*--- 更新商品 ---
elseif($_REQUEST['act'] == 'update_goods')
{
    //接收数据
    $goods_name = trim($_REQUEST['goods_name']);
    $goods_sn = trim($_REQUEST['goods_sn']);
    $goods_brief = mysql_real_escape_string($_REQUEST['goods_brief']);
    $cat_id = intval($_REQUEST['cat_id']);
    $brand_id = intval($_REQUEST['brand_id']);
    $shop_price = floatval($_REQUEST['shop_price']);
    $goods_number = intval($_REQUEST['goods_number']);
    $goods_number_units = $_REQUEST['goods_number_units'];
    $warn_number = intval($_REQUEST['warn_number']);
    $packing = $_REQUEST['packing'];
    $shelflife = intval($_REQUEST['shelflife']);
    $per_unit_number = intval($_REQUEST['per_unit_number']);
    $per_number_units = $_REQUEST['per_number_units'];
    $goods_weight = floatval($_REQUEST['goods_weight']);
    $every_num = intval($_REQUEST['every_num']);
    $everyday_use = $_REQUEST['everyday_use'];
    $goods_id = intval($_REQUEST['goods_id']);

    //更新数据
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').
        " SET goods_name='$goods_name',goods_sn='$goods_sn',goods_brief='$goods_brief',
        cat_id='$cat_id',brand_id='$brand_id',shop_price='$shop_price',
        goods_number='$goods_number',goods_number_units='$goods_number_units',
        warn_number='$warn_number',packing='$packing',shelflife='$shelflife',
        per_unit_number='$per_unit_number',per_number_units='$per_number_units',
        goods_weight='$goods_weight',every_num='$every_num',everyday_use='$everyday_use'
         WHERE goods_id='$goods_id'";
    $result = $GLOBALS['db']->query($sql_update);
        
    if($result)
    {    
        $res['req_msg'] = true;
        $res['message'] = '编辑成功';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
    else
    {
        $res['req_msg'] = true;
        $res['message'] = '编辑失败';
        $res['timeout'] = 2000;

        die($json->encode($res));
    }
}

/*--- ajax编辑商品 ---*/
elseif($_REQUEST['act'] == 'ajax_edit')
{
    $gid = $_REQUEST['gid'];

    if($_REQUEST['info'] != '')
    {
        $info = $_REQUEST['info'];
       
        //根据传过来的id查找对应记录的字段默认值。
        $sql_select = "SELECT $info FROM ".$GLOBALS['ecs']->table('goods').
            " WHERE goods_id=$gid ";
        $value = $GLOBALS['db']->getOne($sql_select);
    }

    // 处理两个选项
    if($_REQUEST['info1'] != '' && $_REQUEST['info2'] != '' )
    {
        $info1 = $_REQUEST['info1'];
        $info2 = $_REQUEST['info2'];

        $sql_select = "SELECT $info1 FROM ".$GLOBALS['ecs']->table('goods').
            " WHERE goods_id=$gid ";
        $value1 = $GLOBALS['db']->getOne($sql_select);

        $sql_select = "SELECT $info2 FROM ".$GLOBALS['ecs']->table('goods').
            " WHERE goods_id=$gid ";
        $value2 = $GLOBALS['db']->getOne($sql_select);
    }

    //拼装好数据返回
    if(strtolower($_REQUEST['type']) == 'input' && strtolower($_REQUEST['info']) == 'goods_sn')
    {
        $res['info'] = strtolower($_REQUEST['info']);
        $res['gid'] = $_REQUEST['gid'];
        $res['main'] = "<input type='text' name='$info' value='$value'      
            onblur="."checkGoodsSn(this.value,'".$value."')".' />';
    }
    elseif(strtolower($_REQUEST['type']) == 'input')
    {
        $res['info'] = $_REQUEST['info'];
        $res['gid'] = $_REQUEST['gid'];
        $res['main'] = "<input type='text' name='$info' value='$value'
           onblur='goodsSaveInputInfo(this)' />";
    }
    elseif(strtolower($_REQUEST['type']) == 'select')
    {
        $res['info'] = $_REQUEST['info'];
        $res['gid'] = $_REQUEST['gid'];
        $smarty->assign('name',$info);
        $smarty->assign('packing',$value);
        $res['main'] = $smarty->fetch('ajax_goods_temp.htm');
    }
    elseif(strtolower($_REQUEST['type']) == 'textarea')
    {
        $res['info'] = $_REQUEST['info'];
        $res['gid'] = $_REQUEST['gid'];
        $res['main'] = "<textarea name='$_REQUEST[info]' cols='40' rows='4' onblur='goodsSaveInputInfo(this)' >$value</textarea>";
    }
    elseif(strtolower($_REQUEST['type']) == 'num_sel' )
    {

        $res['name'] = $_REQUEST['name'];

        $smarty->assign('num',$_REQUEST['num']);
        $smarty->assign('name1',$_REQUEST['info1']);
        $smarty->assign('name2',$_REQUEST['info2']);
        $smarty->assign('goods_number',$value1);
        $smarty->assign('goods_number_units',$value2);

        $res['main'] = $smarty->fetch('ajax_goods_temp.htm');
    }

    die($json->encode($res));
}

/*--- ajax更新商品 ---*/
elseif($_REQUEST['act'] == 'ajax_update')
{
    $gid = $_REQUEST['gid'];

    if($_REQUEST['type'] == 'num_sel')
    {
        $info1 = $_REQUEST['info1'];
        $info2 = $_REQUEST['info2'];
        $value1 = $_REQUEST['value1'];
        $value2 = $_REQUEST['value2'];

        //更新数据
         $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').
         " SET $info1='$value1',$info2='$value2' WHERE goods_id='$gid'";
         $result = $GLOBALS['db']->query($sql_update);
        
         if($result)
         {
             $res['info'] = $info1;
             $res['info1'] = $info1;
             $res['info2'] = $info2;
             $res['num'] = $_REQUEST['num'];
             $res['main'] = $value1.'&nbsp;'.$value2;
             $res['type'] = 'num_sel';
             $res['gid'] = $gid;
         }
         else
         {
             $res['req_msg'] = true;
             $res['timeout'] = 2000;
             $res['message'] = '编辑失败';

             die($json->encode($res));
         }
    }
   
    else
    {
         $info = $_REQUEST['info'];
         $val = $_REQUEST['val'];
      
         //更新数据
         $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('goods').
             " SET $info='$val' WHERE goods_id='$gid'";
         $result = $GLOBALS['db']->query($sql_update);
         if($result)
         {
            $res['gid'] = $_REQUEST['gid'];
            $res['info'] = $_REQUEST['info'];
            $res['main'] = $_REQUEST['val'];
            $res['type'] = $_REQUEST['type'];
         }
    }
   
    die($json->encode($res)); 

}
elseif($_REQUEST['act'] == 'syn_goods'){
   $sql = 'SELECT zyyl,peiliao,cfplb,fyff,syff,scs,sccj,id FROM crm_ecms_goods'; 
   $emcs_goods = $GLOBALS['db']->getAll($sql);
   $sql = 'SELECT goods_sn,goods_name FROM '.$GLOBALS['ecs']->table('goods');
   $crm_goods = $GLOBALS['db']->getAll($sql);

   foreach ($emcs_goods as $v) {
       $field_values = array(); 
       if (empty($v['zyyl'])){
           $field_values['zyyl'] = !empty($v['peiliao'])?$v['peiliao']:$v['cfplb'];
       }
       if (empty($v['syff'])){
           $field_values['syff'] = $v['fyff'];
       }
       if (empty($v['sccj'])){
           $field_values['sccj'] = addslashes_deep($v['scs']);
       }
       $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('ecms_goods'),$field_values,'UPDATE',"id={$v['id']}");
   }
   //foreach ($emcs_goods as $v) {
   //    foreach ($crm_goods as $c) {
   //        if (!strcmp($v['title'],$c['goods_name']) || strpos($c['goods_name'],$v['title']) !== false){
   //            $sql = 'UPDATE '.$GLOBALS['ecs']->table('ecms_goods')." SET good_sn={$c['goods_sn']} WHERE id={$v['id']}";
   //            $GLOBALS['db']->query($sql);
   //        }
   //    }
   //}
}


function get_accountlist($user_id, $account_type = '')
{
    /* 初始化分页参数 */
    $filter = array(
        'user_id'       => $user_id,
        'account_type'  => $account_type
    );

    /* 查询记录总数，计算分页数 */
    $sql = "SELECT COUNT(1) FROM " . $GLOBALS['ecs']->table('goods') ;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $filter = page_and_size($filter);

    /* 查询记录 */
    $sql = 'SELECT g.goods_id,g.goods_sn,g.goods_name,g.shop_price,g.integral,g.goods_weight,
        g.shelflife,g.goods_type,SUM(sg.quantity) as stock,COUNT(sg.goods_sn) as sn FROM '
        .$GLOBALS['ecs']->table('goods').' g,'.$GLOBALS['ecs']->table('stock_goods').
        ' sg WHERE g.goods_sn=sg.goods_sn GROUP BY g.goods_sn';
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['change_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['change_time']);
        $arr[] = $row;
    }

    return array('account' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}
