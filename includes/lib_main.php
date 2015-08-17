<?php

/**
 * ECSHOP 管理中心公用函数库
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: lib_main.php 17217 2011-01-19 06:29:08Z liubo $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 导航菜单
 */
function list_nav ()
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('admin_action');

    if ($_SESSION['action_list'] != 'all')
    {
        $sql .= ' WHERE action_code IN (\''.str_replace(',',"','",$_SESSION['action_list']).'\')';
    }

    $sql .= ' ORDER BY `order` ASC ';

    $res = $GLOBALS['db']->getAll($sql);

    $list = array ();
    foreach ($res as $val)
    {
        @$list[$val['action_id']] = $val;
    }

    $nav_1st = array ();
    foreach ($list as $val)
    {
        if ($val['action_level'] == 0)
        {
            @$nav_1st[$val['action_id']]['label']  = $val['label'];
            @$nav_1st[$val['action_id']]['action'] = $val['action_code'];
        }
    }

    $nav_2nd = array ();
    foreach ($list as $key=>$val)
    {
        if ($val['action_level'] == 1)
        {
            @$nav_2nd[$list[$val['parent_id']]['action_code']][$key]['label']  = $val['label'];
            @$nav_2nd[$list[$val['parent_id']]['action_code']][$key]['action'] = $val['action_code'];
        }
    }

    $nav_3rd = array ();
    foreach ($list as $key=>$val)
    {
        if ($val['action_level'] == 2)
        {
            @$nav_3rd[$list[$val['parent_id']]['action_code']][$key]['label']  = $val['label'];
            @$nav_3rd[$list[$val['parent_id']]['action_code']][$key]['action'] = $val['action_code'];
        }
    }

    $nav = array ($nav_1st, $nav_2nd, $nav_3rd);
    return $nav;
}

/**
 * 获得所有模块的名称以及链接地址
 *
 * @access      public
 * @param       string      $directory      插件存放的目录
 * @return      array
 */
function read_modules($directory = '.')
{
    global $_LANG;

    $dir         = @opendir($directory);
    $set_modules = true;
    $modules     = array();

    while (false !== ($file = @readdir($dir)))
    {
        if (preg_match("/^.*?\.php$/", $file))
        {
            include_once($directory. '/' .$file);
        }
    }
    @closedir($dir);
    unset($set_modules);

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    return $modules;
}

/**
 * 系统提示信息
 *
 * @access      public
 * @param       string      msg_detail      消息内容
 * @param       int         msg_type        消息类型， 0消息，1错误，2询问
 * @param       array       links           可选的链接
 * @param       boolen      $auto_redirect  是否需要自动跳转
 * @return      void
 */
function sys_msg($msg_detail, $msg_type = 0, $links = array(), $auto_redirect = true)
{
    if (count($links) == 0)
    {
        $links[0]['text'] = $GLOBALS['_LANG']['go_back'];
        $links[0]['href'] = 'javascript:history.go(-1)';
    }

    assign_query_info();

    $GLOBALS['smarty']->assign('ur_here',     $GLOBALS['_LANG']['system_message']);
    $GLOBALS['smarty']->assign('msg_detail',  $msg_detail);
    $GLOBALS['smarty']->assign('msg_type',    $msg_type);
    $GLOBALS['smarty']->assign('links',       $links);
    $GLOBALS['smarty']->assign('default_url', $links[0]['href']);
    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);

    $GLOBALS['smarty']->display('message.htm');

    exit;
}

//CRM提示信息
function crm_msg($message='失败',$code=false,$timeout='2000'){
    return array(
        'req_msg' => true,
        'message' => $message,
        'code'    => $code,
        'timeout'=> $timeout,
    );
}

/**
 * 记录管理员的操作内容
 *
 * @access  public
 * @param   string      $sn         数据的唯一值
 * @param   string      $action     操作的类型
 * @param   string      $content    操作的内容
 * @param   init        $role       平台
 * @param   init        $effect     类别
 * @return  void
 */
function admin_log($sn = '', $action, $content,$module='',$sn='',$table='') {
    $log_info = $GLOBALS['_LANG']['log_action'][$action] . $GLOBALS['_LANG']['log_action'][$content] .': ';
    $ip = real_ip();
    if('users' == $module){
        //记录对顾客的操作
        $table = 'users';
        if(strpos($sn,',')){
            $where = " WHERE user_id IN($sn)";
        }else{
            $where = " WHERE user_id=$sn";
        }
        //顾客信息
        $sql_select = 'SELECT eff_id,role_id,admin_id,user_rank FROM '.$GLOBALS['ecs']->table($table).$where;
        $user_info = $GLOBALS['db']->getAll($sql_select);
        if ($user_info) {
            $sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('admin_log') . ' (log_time,do_admin,log_info,ip_address,module,code,sn,role_id,eff_id,admin_id,user_rank,table_name)VALUES';
            foreach ($user_info as $val) {
                $values[] = " ('{$_SERVER['REQUEST_TIME']}',{$_SESSION['admin_id']},'". stripslashes($log_info) . "', '$ip','$module','$action','$sn',{$val['role_id']},{$val['eff_id']},{$val['admin_id']},{$val['user_rank']},'$table')";
            }
            $values = join(',',$values);
            $GLOBALS['db']->query($sql.$values);
        }
    }elseif('order' == $module){
        $table = 'order_info';
        $sql_select = "SELECT platform,admin_id FROM %s WHERE order_id=%s";
        $order_info = $GLOBALS['db']->getRow(sprintf($sql_select,$GLOBALS['ecs']->table($table),$sn));
        if (!$order_info) {
            $order_info = $GLOBALS['db']->getRow(sprintf($sql_select,$GLOBALS['ecs']->table('ordersyn_info'),$sn));
            $table = 'ordersyn_info';
        }
        $sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('admin_log').'(log_time,do_admin,log_info,ip_address,module,code,sn,role_id,admin_id,table_name)'.
            "VALUES('{$_SERVER['REQUEST_TIME']}',{$_SESSION['admin_id']},'" . stripslashes($log_info)."','$ip','$module','$action','$sn',{$order_info['platform']},{$order_info['admin_id']},'$table')";
        $GLOBALS['db']->query($sql);
    }
    //实时统计操作并提醒
    $sql_select = 'SELECT log_id,do_times FROM '.$GLOBALS['ecs']->table('admin_log').
        " WHERE do_admin={$_SESSION['admin_id']} AND module='$module' AND sn='$sn' AND code='$action' ORDER BY do_times DESC";
    $history = $GLOBALS['db']->getRow($sql_select);

    if ($history) {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_log').
            " SET do_times=do_times+1 WHERE log_id={$history['log_id']}";
        $GLOBALS['db']->query($sql_update);
        security_noticing($history['log_id']);
    }
}

/**
 * 将通过表单提交过来的年月日变量合成为"2004-05-10"的格式。
 *
 * 此函数适用于通过smarty函数html_select_date生成的下拉日期。
 *
 * @param  string $prefix      年月日变量的共同的前缀。
 * @return date                日期变量。
 */
function sys_joindate($prefix)
{
    /* 返回年-月-日的日期格式 */
    $year  = empty($_POST[$prefix . 'Year']) ? '0' :  $_POST[$prefix . 'Year'];
    $month = empty($_POST[$prefix . 'Month']) ? '0' : $_POST[$prefix . 'Month'];
    $day   = empty($_POST[$prefix . 'Day']) ? '0' : $_POST[$prefix . 'Day'];

    return $year . '-' . $month . '-' . $day;
}

/**
 * 设置管理员的session内容
 *
 * @access  public
 * @param   integer $user_id        管理员编号
 * @param   string  $username       管理员姓名
 * @param   string  $action_list    权限列表
 * @param   string  $last_time      最后登录时间
 * @return  void
 */
function set_admin_session($user_id, $username, $role_id, $group_id, $ext, $action_list, $last_time)
{
    $_SESSION['admin_id']    = $user_id;
    $_SESSION['admin_name']  = $username;
    $_SESSION['role_id']     = $role_id;
    $_SESSION['group_id']    = $group_id;
    $_SESSION['ext']         = $ext;
    $_SESSION['action_list'] = $action_list;
    $_SESSION['last_check']  = $last_time; // 用于保存最后一次检查订单的时间
}

/**
 * 插入一个配置信息
 *
 * @access  public
 * @param   string      $parent     分组的code
 * @param   string      $code       该配置信息的唯一标识
 * @param   string      $value      该配置信息值
 * @return  void
 */
function insert_config($parent, $code, $value)
{
    global $ecs, $db, $_LANG;

    $sql = 'SELECT id FROM ' . $ecs->table('shop_config') . " WHERE code = '$parent' AND type = 1";
    $parent_id = $db->getOne($sql);

    $sql = 'INSERT INTO ' . $ecs->table('shop_config') . ' (parent_id, code, value) ' .
        "VALUES('$parent_id', '$code', '$value')";
    $db->query($sql);
}

/**
 * 判断管理员对某一个操作是否有权限。
 *
 * 根据当前对应的action_code，然后再和用户session里面的action_list做匹配，以此来决定是否可以继续执行。
 * @param     string    $priv_str    操作对应的priv_str
 * @param     string    $msg_type       返回的类型
 * @return true/false
 */
function admin_priv($priv_str, $msg_type = '' , $msg_output = true)
{
    global $_LANG;

    if ($_SESSION['action_list'] == 'all')
    {
        return true;
    }

    if (strpos(',' . $_SESSION['action_list'] . ',', ',' . $priv_str . ',') === false)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
        if ( $msg_output)
        {
            sys_msg($_LANG['priv_error'], 0, $link);
        }
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * admin_priv加强版
 * @param $action  操作对应的action
 * @param $message 权限不足时的提示信息
 * @param $timeout 提示信息框显示的时间
 * @param $req_msg 是否以弹出层方式显示提示信息
 */
function crm_admin_priv($action, $message = '', $timeout = 3000, $req_msg = true)
{
    if (!admin_priv($action, '', false)) {
        die($json->encode(array('req_msg'=>$req_msg, 'message'=>$message, 'timeout'=>$timeout)));
    }
}

/**
 * 检查管理员权限
 *
 * @access  public
 * @param   string  $authz
 * @return  boolean
 */
function check_authz($authz)
{
    return (preg_match('/,*'.$authz.',*/', $_SESSION['action_list']) || $_SESSION['action_list'] == 'all');
}

/**
 * 检查管理员权限，返回JSON格式数剧
 *
 * @access  public
 * @param   string  $authz
 * @return  void
 */
function check_authz_json($authz)
{
    if (!check_authz($authz))
    {
        make_json_error($GLOBALS['_LANG']['priv_error']);
    }
}

/**
 * 取得红包类型数组（用于生成下拉列表）
 *
 * @return  array       分类数组 bonus_typeid => bonus_type_name
 */
function get_bonus_type()
{
    $bonus = array();
    $sql = 'SELECT type_id, type_name, type_money FROM ' . $GLOBALS['ecs']->table('bonus_type') .
        ' WHERE send_type = 3';
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $bonus[$row['type_id']] = $row['type_name'].' [' .sprintf($GLOBALS['_CFG']['currency_format'], $row['type_money']).']';
    }

    return $bonus;
}

/**
 * 取得用户等级数组,按用户级别排序
 * @param   bool      $is_special      是否只显示特殊会员组
 * @return  array     rank_id=>rank_name
 */
function get_rank_list($is_special = false)
{
    $rank_list = array();
    $sql = 'SELECT rank_id, rank_name, min_points FROM ' . $GLOBALS['ecs']->table('user_rank');
    if ($is_special)
    {
        $sql .= ' WHERE special_rank = 1';
    }
    $sql .= ' ORDER BY min_points';

    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $rank_list[$row['rank_id']] = $row['rank_name'];
    }

    return $rank_list;
}

/**
 * 按等级取得用户列表（用于生成下拉列表）
 *
 * @return  array       分类数组 user_id => user_name
 */
function get_user_rank($rankid, $where)
{
    $user_list = array();
    $sql = 'SELECT user_id, user_name FROM ' . $GLOBALS['ecs']->table('users') . $where.
        ' ORDER BY user_id DESC';
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $user_list[$row['user_id']] = $row['user_name'];
    }

    return $user_list;
}

/**
 * 取得广告位置数组（用于生成下拉列表）
 *
 * @return  array       分类数组 position_id => position_name
 */
function get_position_list()
{
    $position_list = array();
    $sql = 'SELECT position_id, position_name, ad_width, ad_height '.
        'FROM ' . $GLOBALS['ecs']->table('ad_position');
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $position_list[$row['position_id']] = addslashes($row['position_name']). ' [' .$row['ad_width']. 'x' .$row['ad_height']. ']';
    }

    return $position_list;
}

/**
 * 生成编辑器
 * @param   string  input_name  输入框名称
 * @param   string  input_value 输入框值
 */
function create_html_editor($input_name, $input_value = '')
{
    global $smarty;

    $editor = new FCKeditor($input_name);
    $editor->BasePath   = '../includes/fckeditor/';
    $editor->ToolbarSet = 'Normal';
    $editor->Width      = '100%';
    $editor->Height     = '320';
    $editor->Value      = $input_value;
    $FCKeditor = $editor->CreateHtml();
    $smarty->assign('FCKeditor', $FCKeditor);
}

/**
 * 取得商品列表：用于把商品添加到组合、关联类、赠品类
 * @param   object  $filters    过滤条件
 */
function get_goods_list($filter)
{
    $filter->keyword = json_str_iconv($filter->keyword);
    $where = get_where_sql($filter); // 取得过滤条件

    /* 取得数据 */
    $sql = 'SELECT goods_id, goods_name, shop_price '.
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' . $where .
        'LIMIT 50';
    $row = $GLOBALS['db']->getAll($sql);

    return $row;
}

/**
 * 取得文章列表：用于商品关联文章
 * @param   object  $filters    过滤条件
 */
function get_article_list($filter)
{
    /* 创建数据容器对象 */
    $ol = new OptionList();

    /* 取得过滤条件 */
    $where = ' WHERE a.cat_id = c.cat_id AND c.cat_type = 1 ';
    $where .= isset($filter->title) ? " AND a.title LIKE '%" . mysql_like_quote($filter->title) . "%'" : '';

    /* 取得数据 */
    $sql = 'SELECT a.article_id, a.title '.
        'FROM ' .$GLOBALS['ecs']->table('article'). ' AS a, ' .$GLOBALS['ecs']->table('article_cat'). ' AS c ' . $where;
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $ol->add_option($row['article_id'], $row['title']);
    }

    /* 生成列表 */
    $ol->build_select();
}

/**
 * 返回是否
 * @param   int     $var    变量 1, 0
 */
function get_yes_no($var)
{
    return empty($var) ? '<img src="images/no.gif" border="0" />' : '<img src="images/yes.gif" border="0" />';
}

/**
 * 生成过滤条件：用于 get_goodslist 和 get_goods_list
 * @param   object  $filter
 * @return  string
 */
function get_where_sql($filter)
{
    $time = date('Y-m-d');

    $where  = isset($filter->is_delete) && $filter->is_delete == '1' ?
        ' WHERE is_delete = 1 ' : ' WHERE is_delete = 0 ';
    $where .= (isset($filter->real_goods) && ($filter->real_goods > -1)) ? ' AND is_real = ' . intval($filter->real_goods) : '';
    $where .= isset($filter->cat_id) && $filter->cat_id > 0 ? ' AND ' . get_children($filter->cat_id) : '';
    $where .= isset($filter->brand_id) && $filter->brand_id > 0 ? " AND brand_id = '" . $filter->brand_id . "'" : '';
    $where .= isset($filter->intro_type) && $filter->intro_type != '0' ? ' AND ' . $filter->intro_type . " = '1'" : '';
    $where .= isset($filter->intro_type) && $filter->intro_type == 'is_promote' ?
        " AND promote_start_date <= '$time' AND promote_end_date >= '$time' " : '';
    $where .= isset($filter->keyword) && trim($filter->keyword) != '' ?
        " AND (goods_name LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_sn LIKE '%" . mysql_like_quote($filter->keyword) . "%' OR goods_id LIKE '%" . mysql_like_quote($filter->keyword) . "%') " : '';
    $where .= isset($filter->suppliers_id) && trim($filter->suppliers_id) != '' ?
        " AND (suppliers_id = '" . $filter->suppliers_id . "') " : '';

    $where .= isset($filter->in_ids) ? ' AND goods_id ' . db_create_in($filter->in_ids) : '';
    $where .= isset($filter->exclude) ? ' AND goods_id NOT ' . db_create_in($filter->exclude) : '';
    $where .= isset($filter->stock_warning) ? ' AND goods_number <= warn_number' : '';

    return $where;
}

/**
 * 获取地区列表的函数。
 *
 * @access  public
 * @param   int     $region_id  上级地区id
 * @return  void
 */
function area_list($region_id)
{
    $area_arr = array();

    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('region').
        " WHERE parent_id = '$region_id' ORDER BY region_id";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['type']  = ($row['region_type'] == 0) ? $GLOBALS['_LANG']['country']  : '';
        $row['type'] .= ($row['region_type'] == 1) ? $GLOBALS['_LANG']['province'] : '';
        $row['type'] .= ($row['region_type'] == 2) ? $GLOBALS['_LANG']['city']     : '';
        $row['type'] .= ($row['region_type'] == 3) ? $GLOBALS['_LANG']['cantonal'] : '';

        $area_arr[] = $row;
    }

    return $area_arr;
}

/**
 * 取得图表颜色
 *
 * @access  public
 * @param   integer $n  颜色顺序
 * @return  void
 */
function chart_color($n)
{
    /* 随机显示颜色代码 */
    $arr = array('33FF66', 'FF6600', '3399FF', '009966', 'CC3399', 'FFCC33', '6699CC', 'CC3366', '33FF66', 'FF6600', '3399FF');

    if ($n > 8)
    {
        $n = $n % 8;
    }

    return $arr[$n];
}

/**
 * 获得商品类型的列表
 *
 * @access  public
 * @param   integer     $selected   选定的类型编号
 * @return  string
 */
function goods_type_list($selected)
{
    $sql = 'SELECT cat_id, cat_name FROM ' . $GLOBALS['ecs']->table('goods_type') . ' WHERE enabled = 1';
    $res = $GLOBALS['db']->query($sql);

    $lst = '';
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $lst .= "<option value='$row[cat_id]'";
        $lst .= ($selected == $row['cat_id']) ? ' selected="true"' : '';
        $lst .= '>' . htmlspecialchars($row['cat_name']). '</option>';
    }

    return $lst;
}

/**
 * 取得货到付款和非货到付款的支付方式
 * @return  array('is_cod' => '', 'is_not_cod' => '')
 */
function get_pay_ids()
{
    $ids = array('is_cod' => '0', 'is_not_cod' => '0');
    $sql = 'SELECT pay_id, is_cod FROM ' .$GLOBALS['ecs']->table('payment'). ' WHERE enabled = 1';
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['is_cod'])
        {
            $ids['is_cod'] .= ',' . $row['pay_id'];
        }
        else
        {
            $ids['is_not_cod'] .= ',' . $row['pay_id'];
        }
    }

    return $ids;
}

/**
 * 清空表数据
 * @param   string  $table_name 表名称
 */
function truncate_table($table_name)
{
    $sql = 'TRUNCATE TABLE ' .$GLOBALS['ecs']->table($table_name);

    return $GLOBALS['db']->query($sql);
}

/**
 *  返回字符集列表数组
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_charset_list()
{
    return array(
        'UTF8'   => 'UTF-8',
        'GB2312' => 'GB2312/GBK',
        'BIG5'   => 'BIG5',
    );
}


/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content='', $error="0", $message='', $append=array())
{
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

    $res = array('error' => $error, 'message' => $message, 'content' => $content);

    if (!empty($append))
    {
        foreach ($append AS $key => $val)
        {
            $res[$key] = $val;
        }
    }

    $val = $json->encode($res);

    exit($val);
}

/**
 *
 *
 * @access  public
 * @param
 * @return  void
 */
function make_json_result($content, $message='', $append=array())
{
    make_json_response($content, 0, $message, $append);
}

/**
 * 创建一个JSON格式的错误信息
 *
 * @access  public
 * @param   string  $msg
 * @return  void
 */
function make_json_error($msg)
{
    make_json_response('', 1, $msg);
}

/**
 * 根据过滤条件获得排序的标记
 *
 * @access  public
 * @param   array   $filter
 * @return  array
 */
function sort_flag($filter)
{
    $flag['tag']    = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
    $flag['img']    = '<img src="images/' . ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' : 'sort_asc.gif') . '"/>';

    return $flag;
}

/**
 * 分页的信息加入条件的数组
 *
 * @access  public
 * @return  array
 */
function page_and_size($filter)
{
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
    {
        $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
    }
    else
    {
        $filter['page_size'] = 15;
    }

    /* 每页显示 */
    $filter['page'] = (empty($_REQUEST['page']) || intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    /* page 总数 */
    $filter['page_count'] = (!empty($filter['record_count']) && $filter['record_count'] > 0) ? ceil($filter['record_count'] / $filter['page_size']) : 1;

    /* 边界处理 */
    if ($filter['page'] > $filter['page_count'])
    {
        $filter['page'] = $filter['page_count'];
    }

    $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];

    return $filter;
}

/**
 *  将含有单位的数字转成字节
 *
 * @access  public
 * @param   string      $val        带单位的数字
 *
 * @return  int         $val
 */
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last)
    {
    case 'g':
        $val *= 1024;
    case 'm':
        $val *= 1024;
    case 'k':
        $val *= 1024;
    }

    return $val;
}

/**
 * 获得指定的商品类型下所有的属性分组
 *
 * @param   integer     $cat_id     商品类型ID
 *
 * @return  array
 */
function get_attr_groups($cat_id)
{
    $sql = "SELECT attr_group FROM " . $GLOBALS['ecs']->table('goods_type') . " WHERE cat_id='$cat_id'";
    $grp = str_replace("\r", '', $GLOBALS['db']->getOne($sql));

    if ($grp)
    {
        return explode("\n", $grp);
    }
    else
    {
        return array();
    }
}

/**
 * 生成链接后缀
 */
function list_link_postfix()
{
    return 'uselastfilter=1';
}

/**
 * 保存过滤条件
 * @param   array   $filter     过滤条件
 * @param   string  $sql        查询语句
 * @param   string  $param_str  参数字符串，由list函数的参数组成
 */
function set_filter($filter, $sql, $param_str = '')
{
    $filterfile = basename(PHP_SELF, '.php');
    if ($param_str)
    {
        $filterfile .= $param_str;
    }
    setcookie('ECSCP[lastfilterfile]', sprintf('%X', crc32($filterfile)), time() + 600);
    setcookie('ECSCP[lastfilter]',     urlencode(serialize($filter)), time() + 600);
    setcookie('ECSCP[lastfiltersql]',  base64_encode($sql), time() + 600);
}

/**
 * 取得上次的过滤条件
 * @param   string  $param_str  参数字符串，由list函数的参数组成
 * @return  如果有，返回array('filter' => $filter, 'sql' => $sql)；否则返回false
 */
function get_filter($param_str = '')
{
    $filterfile = basename(PHP_SELF, '.php');
    if ($param_str)
    {
        $filterfile .= $param_str;
    }

    if (isset($_GET['uselastfilter']) && isset($_COOKIE['ECSCP']['lastfilterfile'])
        && $_COOKIE['ECSCP']['lastfilterfile'] == sprintf('%X', crc32($filterfile)))
    {
        return array(
            'filter' => unserialize(urldecode($_COOKIE['ECSCP']['lastfilter'])),
            'sql'    => base64_decode($_COOKIE['ECSCP']['lastfiltersql'])
        );
    }
    else
    {
        return false;
    }
}

/**
 * URL过滤
 * @param   string  $url  参数字符串，一个urld地址,对url地址进行校正
 * @return  返回校正过的url;
 */
function sanitize_url($url , $check = 'http://')
{
    if (strpos( $url, $check ) === false)
    {
        $url = $check . $url;
    }
    return $url;
}

/**
 * 检查分类是否已经存在
 *
 * @param   string      $cat_name       分类名称
 * @param   integer     $parent_cat     上级分类
 * @param   integer     $exclude        排除的分类ID
 *
 * @return  boolean
 */
function cat_exists($cat_name, $parent_cat, $exclude = 0)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('category').
        " WHERE parent_id = '$parent_cat' AND cat_name = '$cat_name' AND cat_id<>'$exclude'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

function brand_exists($brand_name)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('brand').
        " WHERE brand_name = '" . $brand_name . "'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

/**
 * 获取当前管理员信息
 *
 * @access  public
 * @param
 *
 * @return  Array
 */
function admin_info()
{
    $sql = "SELECT * FROM ". $GLOBALS['ecs']->table('admin_user')."
        WHERE user_id = '$_SESSION[admin_id]'
        LIMIT 0, 1";
    $admin_info = $GLOBALS['db']->getRow($sql);

    if (empty($admin_info))
    {
        return $admin_info = array();
    }

    return $admin_info;
}

/**
 * 供货商列表信息
 *
 * @param       string      $conditions
 * @return      array
 */
function suppliers_list_info($conditions = '')
{
    $where = '';
    if (!empty($conditions))
    {
        $where .= 'WHERE ';
        $where .= $conditions;
    }

    /* 查询 */
    $sql = "SELECT suppliers_id, suppliers_name, suppliers_desc
        FROM " . $GLOBALS['ecs']->table("suppliers") . "
        $where";

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 供货商名
 *
 * @return  array
 */
function suppliers_list_name()
{
    /* 查询 */
    $suppliers_list = suppliers_list_info(' is_check = 1 ');

    /* 供货商名字 */
    $suppliers_name = array();
    if (count($suppliers_list) > 0)
    {
        foreach ($suppliers_list as $suppliers)
        {
            $suppliers_name[$suppliers['suppliers_id']] = $suppliers['suppliers_name'];
        }
    }

    return $suppliers_name;
}

/**
 * 获取经济来源
 *
 */
function get_income()
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('income').' WHERE available=1 ORDER BY sort ASC';
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取顾客来源
 */
function get_from_where()
{
    $time = time();
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('from_where')." WHERE available=1 AND enddate=0 OR enddate>$time ORDER BY sort ASC";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取顾客类型
 */
function get_customer_type($str = '') {
    if ($str) {
        $sql = 'SELECT type_name,type_id FROM '.$GLOBALS['ecs']->table('customer_type').
            " WHERE available>0 AND type_id IN ($str) ORDER BY sort ASC";
    } else {
        $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('customer_type').' WHERE available>0 ORDER BY sort ASC';
    }
    return $GLOBALS['db']->getAll($sql);
}


/**
 * 获取疾病列表
 */
function get_disease()
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('disease')." WHERE available=1 ORDER BY common_rate ASC";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取服务方式
 */
function get_service_manner()
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_manner').' WHERE available=1';
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取服务类别
 */
function get_service_class()
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('service_class').' WHERE available=1';
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取顾客购买力分级
 **/
function get_type_list($where_addr = '')
{
    $sql = 'SELECT type_id, type_name FROM '.$GLOBALS['ecs']->table('customer_type').' ORDER BY sort ASC';
    $type_list = $GLOBALS['db']->getAll($sql);
    foreach ($type_list as $key=>$val)
    {
        if ($where_addr)
        {
            $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users').' u,'.$GLOBALS['ecs']->table('user_address')." a WHERE u.customer_type=$val[type_id] $where_addr AND admin_id=$_SESSION[admin_id]";
        }
        else
        {
            $sql = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('users')." WHERE customer_type=$val[type_id] AND admin_id=$_SESSION[admin_id]";
        }
        $type_list[$key]['num'] = $GLOBALS['db']->getOne($sql);
    }

    return $type_list;
}

function resort($total)
{
    foreach ($total as $key=>$val)
    {
        is_array($val) && ksort($total[$key]);
        $val['4_month']['amount'] && $key_tmp[$val['4_month']['amount']] = $key;
    }

    @krsort($key_tmp);

    foreach ($key_tmp as $val)
    {
        $total_tmp[$val] = $total[$val];
    }
    return $total_tmp;
}

/**
 * 获取客服列表
 **/
function get_admin ($role_id = 0) {
    $role = "";
    if (is_int($role_id)) {
        $role = " role_id=$role_id AND stats=1 ";
    } elseif ($role_id == 'session') {
        if (!admin_priv('all', '', false)) {
            $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('role')." WHERE manager={$_SESSION['admin_id']}";
            $manager = $GLOBALS['db']->getAll($sql_select);
            foreach ($manager as $val) {
                $temp[] = $val['role_id'];
            }

            $role = " manager={$_SESSION['admin_id']} ";
            if (count($temp) > 1) {
                $manager = implode(',', $temp);
            } else {
                $manager = $temp[0];
            }

            $manager && $role = " (manager={$_SESSION['admin_id']} OR role_id IN ($manager)) AND status=1 ";
        }
    }

    $sql = 'SELECT user_name, user_id FROM '.$GLOBALS['ecs']->table('admin_user');
    if ($role) {
        $sql .= " WHERE $role ORDER BY user_id ASC";
    } else {
        $sql .= ' ORDER BY user_id ASC';
    }

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取团队列表
 */
function get_role($where='role_id NOT IN (3, 4, 5, 8)')
{
    $sql = 'SELECT role_name, role_id FROM '.$GLOBALS['ecs']->table('role')." WHERE 1 AND $where";
    return $GLOBALS['db']->getAll($sql);
}

/* 获取管理员列表 */
function get_admin_userlist ($manager = 0)
{
    $list = array();
    $sql = 'SELECT u.user_id, u.user_name, u.max_customer, u.add_time, u.last_login, r.role_name, u.transfer, u.manager, u.status, u.stats'
        .' FROM '.$GLOBALS['ecs']->table('role').' r, '
        .$GLOBALS['ecs']->table('admin_user').' u ';

    if ($manager && $_SESSION['action_list'] != 'all') {
        $sql .= " WHERE u.status>0 AND u.role_id=r.role_id AND (u.manager=$_SESSION[admin_id] OR u.role_id=$_SESSION[role_id]) ORDER BY u.role_id ASC";
    } else {
        $sql .= ' WHERE u.status>0 AND r.role_id=u.role_id AND agency_id=0 ORDER BY u.role_id ASC';
    }

    $list = $GLOBALS['db']->getAll($sql);

    foreach ($list AS $key=>$val) {
        $list[$key]['add_time']   = local_date($GLOBALS['_CFG']['date_format'], $val['add_time']);
        $list[$key]['last_login'] = local_date($GLOBALS['_CFG']['time_format'], $val['last_login']);
    }

    return $list;
}

/**
 * 只获取客服的ID
 */
function get_admin_id ($role_id)
{
    $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE status>0 AND role_id=$role_id";
    return $GLOBALS['db']->getCol($sql_select);
}


/**
 * 获取退货原因选项
 * return array
 */
function get_back_reason ()
{
    $sql = 'SELECT reason_id, reason FROM '.$GLOBALS['ecs']->table('back_reason')." WHERE availabled=1 ORDER BY sort ASC";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取套餐详细信息
 * @param   int   $packing_id
 * @return  array
 */
function get_packing_info ($packing_id)
{
    $sql = 'SELECT packing_name, packing_id, packing_type, packing_price, packing_cycle, packing_img, packing_img_a, packing_desc, keywords, packing_promote, is_on_sale, is_shipping, promote_start_time, promote_end_time FROM '.$GLOBALS['ecs']->table('packing')." WHERE packing_id=$packing_id";
    $res = $GLOBALS['db']->getRow($sql);

    $res['promote_start_time'] = date('Y-m-d H:i:s', $res['promote_start_time']);
    $res['promote_end_time']   = date('Y-m-d H:i:s', $res['promote_end_time']);
    $res['add_time']           = date('Y-m-d H:i:s', $res['add_time']);
    $res['update_time']        = date('Y-m-d H:i:s', $res['update_time']);

    return $res;
}

/**
 * 获取套餐内商品列表
 * @param   int/array   $packing_id
 * @return  array
 **/
function get_packing_goods_list ($packing_id)
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('packing_goods').' pg, '.$GLOBALS['ecs']->table('goods').
        " g WHERE pg.packing_id=$packing_id AND g.goods_id=pg.goods_id ORDER BY extension ASC";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取品牌信息
 * @param   int   $packing_id
 * @return  array
 **/
function get_packing_brand_info($packing_id)
{
    $sql = 'SELECT * FROM '.$GLOBALS['ecs']->table('packing_goods').' pg, '.$GLOBALS['ecs']->table('brand')." b WHERE pg.brand_id=b.brand_id AND pg.packing_id=$packing_id";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 根据商品获取相关组合或套餐
 * @param   int   $goods_id
 * return   array
 **/
function get_goods_packing ($goods_id)
{
    $sql = 'SELECT p.* FROM '.$GLOBALS['ecs']->table('packing_goods').' pg, '.$GLOBALS['ecs']->table('packing')." p WHERE pg.packing_id=p.packing_id AND pg.goods_id=$goods_id AND pg.extension=2";
    $packing_list = $GLOBALS['db']->getAll($sql);

    $big5 = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
    foreach ($packing_list  as $key=>$val)
    {
        $sql = 'SELECT g.goods_id, g.goods_name, g.goods_thumb FROM '.$GLOBALS['ecs']->table('packing_goods').' pg, '.$GLOBALS['ecs']->table('goods').' g WHERE pg.goods_id=g.goods_id AND pg.packing_id='.$val['packing_id'];

        $goods_list = $GLOBALS['db']->getAll($sql);
        for ($i = count($goods_list); $i <= 5; ++$i)
        {
            array_push($goods_list, '0');
        }
        $packing_list[$key]['goods_list'] = $goods_list;
    }

    foreach ($packing_list as $key=>$val)
    {
        $packing[$big5[$key]] = $val;
    }

    return $packing;
}

/**
 * 获取商品组合的商品列表
 * @param   int $packing_id
 * @param   return
 **/
function get_packing_goods($packing_id)
{
    $sql = 'SELECT goods_id, goods_name, num, extension FROM '.
        $GLOBALS['ecs']->table('packing_goods')." WHERE packing_id=$packing_id";
    return $GLOBALS['db']->getAll($sql);
}
/**
 * 获取商品组合的组合描述
 * @param   int $packing_id
 * @param   return
 **/
function get_packing_article ($packing_id)
{
    $sql = 'SELECT article FROM '.$GLOBALS['ecs']->table('packing_article')." WHERE packing_id=$packing_id";
    return $GLOBALS['db']->getOne($sql);
}

/**
 * 获取地址信息（淘宝表中）
 */
function get_area_taobao ($type = 1, $parent = '')
{
    $sql = 'SELECT region_name, region_id FROM '.$GLOBALS['ecs']->table('region')." WHERE region_type=$type";
    if ($parent)
    {
        $sql .= " AND parent_id=$parent";
    }
    $sql .= ' ORDER BY priority DESC, region_id ASC';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 获取角色列表
 */
function get_role_list($type = '')
{
    $list = array();
    if (!empty($type) && is_int($type))
    {
        $type = " WHERE role_type>=$type";
    }
    $sql  = 'SELECT role_id, role_name, action_list, compete, manager FROM '
        .$GLOBALS['ecs']->table('role').$type.' ORDER BY convert(role_name using gbk) ASC';
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

/**
 * 记录操作内容
 */
function record_operate ($sql, $table)
{
    $now_time = time() + 8*3600;
    $table_name = explode('_', $table);
    $sql = mysql_real_escape_string($sql);

    $sql = 'INSERT INTO '.$GLOBALS['ecs']->table($table_name[0].'_record').
        '(admin_id, sql_content, table_name, operate_time)VALUES('.
        "{$_SESSION['admin_id']}, '$sql', '$table', $now_time)";
    $GLOBALS['db']->query($sql);
}

/**
 * 子菜单列表
 * @param   $file   string  文件名
 */
function sub_menu_list ($file)
{
    global $smarty;
    if (isset($_REQUEST['ext']))
    {
        $nav = list_nav();
        $smarty->assign('nav_2nd', $nav[1][$file]);
        $smarty->assign('nav_3rd', $nav[2]);
        $smarty->assign('file_name', $file);

        return $smarty->fetch('left.htm');
    }
    else
    {
        return false;
    }
}

/**
 * 获取服务
 */
function get_service($user_id)
{
    $sql_select = 'SELECT FROM_UNIXTIME(s.service_time) service_time,s.admin_name,c.class,m.manner,s.logbook FROM '.
        $GLOBALS['ecs']->table('service').' AS s LEFT JOIN '.$GLOBALS['ecs']->table('service_class').
        ' AS c ON s.service_class=c.class_id LEFT JOIN '.$GLOBALS['ecs']->table('service_manner').
        ' AS m ON s.service_manner=m.manner_id '." WHERE s.user_id=$user_id ORDER BY service_time";
    $result = $GLOBALS['db']->getAll($sql_select);

    return $result;
}

/**
 * 改变双元素二维数组为单元素一维关联数组
 * @param   $arr     array
 * @param   $key    0/1 0 取第一个元素作为新数组的key   1 第二个元素作为新数组的key
 */
function assoc_value ($arr, $key = 0)
{
    $res = array ();
    $value = $key ? 0 : 1;
    foreach ($arr as $val)
    {
        $res[$val[$key]] = $val[$value];
    }

    return $res;
}

/**
 * 获取销售平台列表
 */
function platform_list ($platform = array ())
{
    $sql_select = 'SELECT role_name,role_id,role_describe FROM '.$GLOBALS['ecs']->table('role')." WHERE role_type>0 ";
    if (!admin_priv('all', '', false) && empty($platform)) {
        $action = explode(',', $_SESSION['action_list']);
        $action = implode("','", array_filter($action));
        $sql_select .= " AND action IN ('$action')";
    }

    if (!empty($platform)) {
        $platform = implode(',', $platform);
        $sql_select .= " AND role_id IN ($platform)";
    }

    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * 识别日期/时间戳 并自动转为日期
 */
function stamp2date ($str, $format = 'Y-m-d')
{
    if (strpos($str,'-'))
    {
        return $str;
    }
    else
    {
        return date($format, $str);
    }
}

/**
 * 获取有效客服列表
 */
function get_admin_tmp_list ($role = 0)
{
    $sql = 'SELECT user_name,user_id,group_id,role_id FROM '.
        $GLOBALS['ecs']->table('admin_user').' WHERE status>0 AND stats>0';
    if (!admin_priv('all', '', false) && $role && $_SESSION['role_id']) {
        $sql .= " AND role_id={$_SESSION['role_id']}";
    } else {
        $sql .= ' AND role_id IN ('.SALE.') ';
    }
    $admin_list = $GLOBALS['db']->getAll($sql.' ORDER BY convert(user_name using gbk) ASC');
    return $admin_list;
}

//ALL权限查询部门的客服
function get_admin_by_all(){
    $where = ' WHERE status>0 AND stats>0';
    if ($_SESSION['admin_id'] == 493) {
        $where .= " AND role_id IN(34,35,36)";
    }elseif(!empty($_SESSION['role_id'])) {
        $where .= " AND role_id={$_SESSION['role_id']}";
    }
    $sql = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user').$where.' ORDER BY convert(user_name using gbk) ASC'; 
    return $GLOBALS['db']->getAll($sql);
}



/**
 * 根据role_id获取客服列表
 */
function admin_list_by_role($role_list)
{
    $sql_select = 'SELECT user_name,user_id,group_id,role_id FROM '.
        $GLOBALS['ecs']->table('admin_user').' WHERE status>0 AND stats>0';
    if (is_array($role_list) && !empty($role_list)) {
        $sql_select .= ' AND role_id IN ('.implode(',', $role_list).')';
    } else {
        return false;
    }

    return $GLOBALS['db']->getAll($sql_select);
}


/**
 * 线下销售客服列表
 */
function offline_admin_list ($role = 0)
{
    $sql = 'SELECT user_name,user_id,group_id,role_id FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE status>0 AND stats>0';
    if (!admin_priv('all', '', false) && $role && $_SESSION['role_id']) {
        $sql .= " AND role_id={$_SESSION['role_id']}";
    } else {
        $sql .= ' AND role_id IN ('.OFFLINE_SALE.') ';
    }

    return $GLOBALS['db']->getAll($sql);
}
/**
 * 获取顾客类型
 */
function get_user_active()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_active')." WHERE available=1";
    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * 报表统计期限
 */
function report_statistics_limit ($profile_id)
{

    $sql_select = 'SELECT config_name,config_value FROM '.$GLOBALS['ecs']->table('profile_config')." WHERE profile_id=$profile_id";
    $configs = $GLOBALS['db']->getAll($sql_select);

    $config = array ();
    foreach ($configs as $val) {
        $config[$val['config_name']] = $val['config_value'];
    }

    return $config;
}

/**
 * 获取小组列表
 */
function get_group_list ($role_id)
{
    $sql_select = 'SELECT group_id,group_name FROM '.$GLOBALS['ecs']->table('group');
    if ($role_id) {
        $sql_select .= " WHERE role_id IN ($role_id)";
    }

    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * get_admin_list_by_group
 * @return Array
 * @author Nixus
 **/
function get_admin_list_by_group($group_id)
{
    $group_id = intval($group_id);
    if (! $group_id) {
        return false;
    }

    $sql_select = 'SELECT user_name,user_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE group_id=$group_id";
    $admin_list = $GLOBALS['db']->getAll($sql_select);

    return $admin_list;
}

/**
 * 分页函数
 */
function break_pages($record_count, $page_size, $current_page)
{
    $page['page_count'] = $record_count>0 ? ceil($record_count/$page_size) : 1;

    // 设置分页
    $page['page_set'] = array (1,2,3,4,5,6,7);
    if ($current_page > 4) {
        foreach ($page['page_set'] as &$val) {
            $val += $current_page -4;
        }
    }

    if (end($page['page_set']) > $page['page_count']) {
        $page['page_set'] = array ();

        for ($i = 7; $i >= 0; $i--) {
            if ($page['page_count'] - $i > 0) {
                $page['page_set'][] = $page['page_count'] - $i;
            }
        }
    }

    $page['start'] = ($current_page - 1)*$page_size +1;
    $page['end']   = $current_page*$page_size;

    return $page;
}

/**
 * 订单来源
 */
function order_source_list () {
    $sql_select = 'SELECT source_id,source_name FROM '.$GLOBALS['ecs']->table('order_source');
    $order_source_list = $GLOBALS['db']->getAll($sql_select);

    return $order_source_list;
}

/**
 * 获取电话录音
 */
function scan_phone_records($user_id)
{
    $dir_name = 'records/record_'.($user_id%7)."/{$user_id}";
    if (!file_exists("/WebSite/$dir_name")) {
        return false;
    }

    $records_list = scandir("/WebSite/$dir_name");

    array_shift($records_list);
    array_shift($records_list);

    $final_list = array();
    foreach ($records_list as $val){
        preg_match('/\d+-\d+-(\d{8})-/', $val, $key);
        $final_list[$key[1]][] = "../../$dir_name/$val";
    }

    return $final_list;
}

/**
 * trans_group_list
 * @return arr
 * @author nxs
 **/
function trans_group_list()
{
    $sql_select = 'SELECT role_describe FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id={$_SESSION['role_id']}";
    $role_code = $GLOBALS['db']->getOne($sql_select);

    $sql_select = 'SELECT group_id,group_name FROM '.$GLOBALS['ecs']->table('role').' r, '.$GLOBALS['ecs']->table('group').
        " g WHERE g.role_id=r.role_id AND r.role_describe='$role_code'";
    return $GLOBALS['db']->getAll($sql_select);
}

/**
 * trans_part_list
 * @return arr
 * @author nxs
 **/
function trans_part_list()
{
    $sql_select = 'SELECT role_describe FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id={$_SESSION['role_id']}";
    $role_code = $GLOBALS['db']->getOne($sql_select);

    $sql_select = 'SELECT role_id FROM '.$GLOBALS['ecs']->table('role')." WHERE role_describe='$role_code'";
    return $GLOBALS['db']->getCol($sql_select);
}

/**
 * ECP_list
 * @return array
 * @author nixus
 **/
function ECP_list()
{
    $sql_select = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').' WHERE role_type=3';
    return $GLOBALS['db']->getAll($sql_select);
}

//隐藏联系方式
function hideContact($val){
    $sql = 'SELECT status FROM '.$GLOBALS['ecs']->table('contact_status')." WHERE where_use='anywhere'";
    $status = $GLOBALS['db']->getOne($sql);
    if ($status && !in_array($_SESSION['role_id'],array(13,20))) {
        if ($val) {
        $val = substr_replace($val,'****',3,4);
        }
    }
    return $val;
}

/**
* curl拨号
*/
function curlPhone($phone, $exten) {
    $ch = curl_init();
    $url =  'http://192.168.1.240/call.php';
    //?phone=%s&exten=%s
    $poster = array('phone' => $phone, 'exten' => $exten);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $poster);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
/**
* 验证号码及是否为市话
*/
function isLocalPhone($phone) {
    // 判断号码是固话还是手机
    if (preg_match('/^01[34578]{1}\d{9}$/', $phone)) {
        $phone = substr($phone, 1);
    } elseif (preg_match('/^1[34578]{1}\d{9}$/', $phone)) {
    } else {
        return $phone;
    }
    $localPhone = file('localPhone.txt');
    if (array_search(intval(substr($phone, 0, 7)), $localPhone) !== false) {
        return $phone;
    } else {
        return '0'.$phone;
    }
}

//后台分页
function admin_page_size($sql_count,$act,$condition)
{
    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    $filter['recorder_size'] = empty($_REQUEST['recorder_size']) ? 15 : intval($_REQUEST['recorder_size']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_count);
    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

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
        'page_count'    => $filter['page_count'],
        'recorder_size' => $filter['recorder_size'],
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'page_start'    => ($filter['page'] - 1)*$filter['page_size'] +1,
        'page_end'      => $filter['page']*$filter['page_size'],
        'act'           => $act,
        'disct'         => $dist
    );
    return $filter;
}

function get_role_by_all(){
    $where = ' WHERE role_id>31';
    if ($_SESSION['admin_id'] == 493) {
        $where .= " AND role_id IN(34,35,36)";
    }elseif (!empty($_SESSION['role_id'])) {
        $where .= " AND role_id={$_SESSION['role_id']}";
    }
    $sql = 'SELECT role_id,role_name FROM '.$GLOBALS['ecs']->table('role').$where.' ORDER BY convert(role_name using gbk) ASC';
    return $GLOBALS['db']->getAll($sql);
}

//获取未接来电
function miss_call(){
    $url = 'http://192.168.1.240/mon/missCall.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('date'=>$_SERVER['REQUEST_TIME'],'admin_id'=>$_SESSION['admin_id'],'ext'=>$_SESSION['ext']));
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result,true);
}

function data_from_jinlun($act,$parameter){
    $url = 'http://192.168.1.240/mon/'.$act;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$parameter);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result,true);
}
