<?php
/**
 * ECSHOP 管理员信息以及权限管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: privilege.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
     $_REQUEST['act'] = 'login';
}
else
{
     $_REQUEST['act'] = trim($_REQUEST['act']);
}

/* 初始化 $exc 对象 */
$exc = new exchange($ecs->table("admin_user"), $db, 'user_id', 'user_name');

/*------------------------------------------------------ */
//-- 退出登录
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'logout')
{
    /* 清除cookie */
    setcookie('ECSCP[admin_id]',   '', 1);
    setcookie('ECSCP[admin_pass]', '', 1);

    $sess->destroy_session();

    die($json->encode(array('resp'=>'logout')));
}

if ($_REQUEST['act'] == 'register') {

    $role_list = get_role_list();

    $sql_select = 'SELECT dorm_id,dormitory FROM '.$GLOBALS['ecs']->table('dormitory').' WHERE available=1';
    $dorm_list = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('staff', 1);

    $smarty->assign('staff_id', generate_staff_id());

    $smarty->assign('province_list', get_regions(1,1)); // 省份

    $smarty->assign('role_list', $role_list); // 部门列表
    $smarty->assign('dorm_list', $dorm_list); // 宿舍列表

    $smarty->assign('joined_date', date('Y-m-d')); // 当前日期

    $smarty->display('register.htm');
}

if (isset($_REQUEST['submit_records'])) {
    $request = addslashes_deep($_REQUEST);

    unset($request['submit_records'], $request['act'], $request['pic']); // free无用数据

    // 按表进行数据分类
    foreach ($request as $key=>$val){
        if (!is_array($val)) {
            $staff_info[$key] = $val;
        } else {
            $$key = $val;
        }
    }

    // 处理入职时间
    $staff_info['joined_date'] = strtotime($staff_info['joined_date']); // 入职时间

    $staff_info['date_mark'] = date('ymd', $staff_info['joined_date']); // 员工编号：日期标识

    // 设置员工所属地区
    $staff_info['greater'] = 'GZ';

    // 绑定客服ID
    // 添加工作账号
    if (!$staff_info['user_id'] = insert_admin_info($staff_info)) {
        $res['timeout'] = 2000;
        $res['req_msg'] = true;
        $res['message'] = '对不起，系统出错，请联系技术人员或稍后再试！';
    }

    // 将员工档案提交至 crm_staff_records
    $staff_id = insert_staff_info($staff_info);

    // 处理员工的学习经历
    $edu = array();
    foreach ($edu_start as $key=>$val) {
        $edu[$key]['edu_start'] = $val;
        $edu[$key]['edu_end']   = $edu_end[$key];
        $edu[$key]['school']    = $school[$key];
        $edu[$key]['major']     = $major[$key];
        $edu[$key]['graduater'] = $graduater[$key];
    }

    // 提交学习经历到数据库
    deal_satellite_info($staff_id, $edu, 'edu_exp');

    // 处理员工的工作经历
    $work = array();
    foreach ($work_start as $key=>$val) {
        $work[$key]['work_start']   = $val;
        $work[$key]['work_end']     = $work_end[$key];
        $work[$key]['company']      = $company[$key];
        $work[$key]['position']     = $position[$key];
        $work[$key]['left_reasons'] = $left_reasons[$key];
    }

    // 提交工作经历到数据库
    deal_satellite_info($staff_id, $work, 'work_exp');

    // 处理员工的紧急联系人
    $contact = array ();
    foreach ($contact_name as $key=>$val){
        $contact[$key]['contacter'] = $val;
        $contact[$key]['relation']     = $relation[$key];
        $contact[$key]['phone']        = $contact_phone[$key];
    }

    // 提交紧急联系人到数据库
    deal_satellite_info($staff_id, $contact, 'emergency_contact');

    // 处理员工照片及各类证书
    $avator = $_FILES['avator'];
    $pic = $_FILES['pic'];

    $staff_info['date_mark'] = date('ymd', $staff_info['joined_date']); // 员工编号：日期标识
    $staff_info['greater'] = 'GZ';

    $fill = '';
    for ($i = strlen($staff_id); $i < 4; $i++) {
        $fill .= '0';
    }

    $dir = "uploads/staff/{$staff_info['greater']}{$staff_info['date_mark']}$fill$staff_id";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    if (!empty($avator)) {
        $avator_url = "$dir/avator".substr($avator['name'], strpos($avator['name'], '.'));
        if (move_uploaded_file($avator['tmp_name'], $avator_url)) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('staff_records')." SET avator_url='$avator_url' WHERE staff_id=$staff_id";
            $GLOBALS['db']->query($sql_update);
        }
       /*
        else {
            $res['req_msg'] = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
            $res['timeout'] = 2000;
            $res['message'] = '欢迎加入康健人生大家庭！您的信息已经保存成功！';
            die("<script>showMsg($res);</script>");
        }
        */
    }
   /* else {
            $res['req_msg'] = 'true';  // true 要求显示反馈信息 false 不要求显示反馈信息
            $res['timeout'] = 2000;
            $res['message'] = '欢迎加入康健人生大家庭！您的信息已经保存成功！';
            die("<script>showMsg($res);</script>");
    }
    */


    $staff_pic = array();
    foreach ($pic['name'] as $key=>$val){
        if ($val) {
            $tmp_url = "$dir/".(microtime(true)*10000).$key.substr($val, strpos($val, '.'));
            if (move_uploaded_file($pic['tmp_name'][$key], $tmp_url)) {
                $staff_pic[] = $staff_id.',"'.$tmp_url.'"';
            }
        }
    }

    if (!empty($staff_pic)) {
        $staff_pic_url = implode('),(', $staff_pic).')';
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('staff_pic').'(staff_id, pic_url)VALUES('.$staff_pic_url;
        $GLOBALS['db']->query($sql_insert);
    }

    ecs_header("Location: ./index.php");
}

/*------------------------------------------------------ */
//-- 登陆界面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'login')
{
     header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
     header("Cache-Control: no-cache, must-revalidate");
     header("Pragma: no-cache");

     include(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_index_info.php');
     if ($_REQUEST['role_id']) {
         $res = get_sale_rank();
         die($json->encode($res));
     }

     if ((intval($_CFG['captcha']) & CAPTCHA_ADMIN) && gd_version() > 0)
     {
         $smarty->assign('gd_version', gd_version());
         $smarty->assign('random',     mt_rand());
     }

     // 首先判断登录当天是否是一号
     /*
     $login_time = time();
     if (date('j', $login_time) == 1)
     {
	     // 从数据库中读取所有用户的user_id 和 最后一次的登录时间 last_login
	     $sql = 'SELECT user_id, FROM_UNIXTIME(last_login, "%Y%m%d") last_login FROM '.$GLOBALS['ecs']->table('admin_user').
		     ' WHERE password<>1';
	     $last_login = $GLOBALS['db']->getAll($sql);

	     $sql = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').
		     " SET password=1, last_login=$login_time WHERE user_id<>1 AND user_id IN ";

	     foreach ($last_login as $v)
	     {
		     // 判断用户登录的最后时间是否在今日之前
		     if ($v['last_login'] < intval(date('Ymd')))
		     {
			     $uid[] = $v['user_id'];
		     }
	     }

	     //  将符合之上所有条件的用户的密码设置为1（不加MD5） 并将最后一次登录时间设置为当前时间
	     if (!empty($uid))
	     {
		     $sql .= '('.implode(',', $uid).')';
		     //file_put_contents('login.txt', $sql);
		     $GLOBALS['db']->query($sql);
	     }
     }
      */
     $smarty->display('login.htm');
}


/*------------------------------------------------------ */
//-- 验证登陆信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'signin')
{
	if (!empty($_SESSION['captcha_word']) && (intval($_CFG['captcha']) & CAPTCHA_ADMIN))
	{
		include_once(ROOT_PATH . 'includes/cls_captcha.php');

		/* 检查验证码是否正确 */
		$validator = new captcha();
		if (!empty($_POST['captcha']) && !$validator->check_word($_POST['captcha']))
		{
			sys_msg($_LANG['captcha_error'], 1);
		}
	}

	$_POST['username'] = isset($_POST['username']) ? trim($_POST['username']) : '';
	$_POST['password'] = isset($_POST['password']) ? trim($_POST['password']) : '';

	$sql="SELECT `ec_salt` FROM ". $ecs->table('admin_user') ."WHERE user_name = '" . $_POST['username']."'";
	$ec_salt =$db->getOne($sql);
    if(!empty($ec_salt)) {
        /* 检查密码是否正确 */
        $sql = "SELECT user_id,user_name,role_id,group_id,ext,password,last_login,action_list,last_login,suppliers_id,ec_salt"
            ." FROM ".$ecs->table('admin_user')." WHERE user_name='".$_POST['username'].
            "' AND password='".md5(md5($_POST['password']).$ec_salt)."' AND status>0";
    } else {
        /* 检查密码是否正确 */
        $sql = "SELECT user_id,user_name,role_id,group_id,ext,password,last_login,action_list,last_login,suppliers_id,ec_salt"
            ." FROM ".$ecs->table('admin_user')." WHERE user_name='".$_POST['username'].
            "' AND password='".md5($_POST['password'])."' AND status>0";
    }

    $row = $db->getRow($sql);
    if ($row) {
		// 检查是否为供货商的管理员 所属供货商是否有效
		if (!empty($row['suppliers_id'])) {
            $supplier_is_check = suppliers_list_info(' is_check=1 AND suppliers_id='.$row['suppliers_id']);
            if (empty($supplier_is_check)) {
                sys_msg($_LANG['login_disable'], 1);
            }
        }

        // 登录成功
        set_admin_session($row['user_id'], $row['user_name'], $row['role_id'], $row['group_id'], $row['ext'], $row['action_list'], $row['last_login']);
        setcookie('admin_id', $_SESSION['admin_id']);

        /* 登录成功后备份商品库存 */
        if ($row['role_id'] == 8)
        {
            $sql = 'UPDATE '.$ecs->table('goods').' SET yesterday=UNIX_TIMESTAMP(NOW()), yesterday_num=goods_number WHERE yesterday<UNIX_TIMESTAMP(CURRENT_DATE())';
            $db->query($sql);
        }

        $_SESSION['suppliers_id'] = $row['suppliers_id'];
        if(empty($row['ec_salt']))
        {
            $ec_salt=rand(1,9999);
            $new_possword=md5(md5($_POST['password']).$ec_salt);
            $db->query("UPDATE " .$ecs->table('admin_user').
                " SET ec_salt='" . $ec_salt . "', password='".$new_possword."'".
                " WHERE user_id='$_SESSION[admin_id]'");
        }

        if($row['action_list'] == 'all' && empty($row['last_login']))
        {
            $_SESSION['shop_guide'] = true;
        }

        // 更新最后登录时间和IP
        $db->query("UPDATE ".$ecs->table('admin_user')." SET last_login='".gmtime()."',last_ip='".real_ip()."',login_times=login_times+1".
            " WHERE user_id='$_SESSION[admin_id]'");

        //if (isset($_POST['remember']))
        //{
        $time = time() + 3600 * 2;
        setcookie('ECSCP[admin_id]', $row['user_id'], $time);
        setcookie('ECSCP[admin_pass]',md5($row['password'].$_CFG['hash_code']),$time);
        //}

        // 清除购物车中过期的数据
        clear_cart();

        ecs_header("Location: ./index.php\n");

        exit;
    }
    else
    {
        /* 获取登录密码 */
        $username = mysql_real_escape_string(trim($_REQUEST['username']));
        $sql = 'SELECT user_id, password, ec_salt, mobile FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_name='$username' AND status=1";
        $userInfo = $GLOBALS['db']->getRow($sql);

        if (!count($userInfo))
        {
            sys_msg('用户不存在，请检查用户名！', 1);
        }

        if ($userInfo['password'] == 1)
        {
            $passwdStr = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $passwd = '';
            for ($len = 0; $len < 5;)
            {
                $passwd .= $passwdStr[mt_rand(0, strlen($passwdStr))];
                $len = strlen($passwd);
            }

            if (empty($userInfo['ec_salt']))
            {
                $password = md5($passwd);
            }
            else
            {
                $password = md5(md5($passwd).$userInfo['ec_salt']);
            }

            if (empty($userInfo['mobile']))
            {
                sys_msg('用户未预留手机，请联系管理员！', 1);
            }

            $sql = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').
                " SET password='$password' WHERE user_id={$userInfo['user_id']} AND password=1 AND status=1";
            if ($GLOBALS['db']->query($sql))
            {
                include_once('../includes/cls_sms.php');
                $sms = new sms;
                if ($sms->send($userInfo['mobile'], '本周登录的密码是'.$passwd))
                {
                    sys_msg('密码已通过短信发送到您手机，请注意查收！', 1);
                }
            }
        }
        else
        {
            sys_msg($_LANG['login_faild'], 1);
        }
    }
}

/*------------------------------------------------------ */
//-- 管理员列表页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'list')
{
    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['02_admin_list'].'：'.$_SESSION['admin_name']);
    $smarty->assign('action_link', array('href'=>'privilege.php?act=add', 'text' => $_LANG['admin_add']));
    $smarty->assign('full_page',   1);

    $admin_list = get_admin_userlist(count(get_admin('session')));

    foreach ($admin_list as $val)
    {
        $admin_list_tmp[$val['user_id']] = $val['user_name'];
    }

    foreach ($admin_list as $key=>$val)
    {
        $admin_list[$key]['manager'] = $admin_list_tmp[$val['manager']];
    }

    $smarty->assign('admin_list', $admin_list);

    /* 显示页面 */
    assign_query_info();
    $smarty->display('privilege_list.htm');
}

/*------------------------------------------------------ */
//-- 查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $smarty->assign('admin_list',  get_admin_userlist());
    make_json_result($smarty->fetch('privilege_list.htm'));
}

/*------------------------------------------------------ */
//-- 添加管理员页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 检查权限 */
    admin_priv('admin_add');

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['admin_add']);
    $smarty->assign('action_link', array('href'=>'privilege.php?act=list', 'text' => $_LANG['02_admin_list']));
    $smarty->assign('form_act',    'insert');
    $smarty->assign('action',      'add');
    $smarty->assign('select_role',  get_role_list());

    /* 显示页面 */
    assign_query_info();
    $smarty->display('privilege_info.htm');
}

/*------------------------------------------------------ */
//-- 添加管理员的处理
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert')
{
    admin_priv('admin_add');

    /* 判断管理员是否已经存在 */
    if (!empty($_POST['user_name']))
    {
        $is_only = $exc->is_only('user_name', stripslashes($_POST['user_name']));

        if (!$is_only)
        {
            sys_msg(sprintf($_LANG['user_name_exist'], stripslashes($_POST['user_name'])), 1);
        }
    }

    /* Email地址是否有重复 */
    if (!empty($_POST['email']))
    {
        $is_only = $exc->is_only('email', stripslashes($_POST['email']));

        if (!$is_only)
        {
            sys_msg(sprintf($_LANG['email_exist'], stripslashes($_POST['email'])), 1);
        }
    }

    /* 获取添加日期及密码 */
    $add_time = gmtime();

    $password  = md5($_POST['password']);
    $role_id = '';
    $action_list = '';
    if (!empty($_POST['select_role']))
    {
        $sql = "SELECT action_list FROM " . $ecs->table('role') . " WHERE role_id = '".$_POST['select_role']."'";
        $row = $db->getRow($sql);
        $action_list = $row['action_list'];
        $role_id = $_POST['select_role'];
    }

    $sql = "SELECT nav_list FROM " . $ecs->table('admin_user') . " WHERE action_list = 'all'";
    $row = $db->getRow($sql);

    $sql = "INSERT INTO ".$ecs->table('admin_user')." (user_name, email, password, add_time, nav_list, action_list, role_id, transfer, mobile) ".
        "VALUES ('".trim($_POST['user_name'])."', '".trim($_POST['email'])."', '$password', '$add_time', '$row[nav_list]', '$action_list', '$role_id', 1, '".trim($_POST['mobile'])."')";

    $db->query($sql);
    /* 转入权限分配列表 */
    $new_id = $db->Insert_ID();

    /*添加链接*/
    $link[0]['text'] = $_LANG['go_allot_priv'];
    $link[0]['href'] = 'privilege.php?act=allot&id='.$new_id.'&user='.$_POST['user_name'].'';

    $link[1]['text'] = $_LANG['continue_add'];
    $link[1]['href'] = 'privilege.php?act=add';

    sys_msg($_LANG['add'] . "&nbsp;" .$_POST['user_name'] . "&nbsp;" . $_LANG['action_succeed'],0, $link);

    /* 记录管理员操作 */
    admin_log($_POST['user_name'], 'add', 'privilege');
}

/*------------------------------------------------------ */
//-- 编辑管理员信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    /* 不能编辑demo这个管理员 */
    if ($_SESSION['admin_name'] == 'demo')
    {
        $link[] = array('text' => $_LANG['back_list'], 'href'=>'privilege.php?act=list');
        sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
    }

    $_REQUEST['id'] = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    /* 查看是否有权限编辑其他管理员的信息 */
    if ($_SESSION['admin_id'] != $_REQUEST['id'])
    {
        admin_priv('admin_manage');
    }

    /* 获取管理员信息 */
    $sql = "SELECT user_id, user_name, email, password, agency_id, role_id FROM " .$ecs->table('admin_user').
        " WHERE user_id = '".$_REQUEST['id']."'";
    $user_info = $db->getRow($sql);


    /* 取得该管理员负责的办事处名称 */
    if ($user_info['agency_id'] > 0)
    {
        $sql = "SELECT agency_name FROM " . $ecs->table('agency') . " WHERE agency_id = '$user_info[agency_id]'";
        $user_info['agency_name'] = $db->getOne($sql);
    }

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['admin_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['admin_list'], 'href'=>'privilege.php?act=list'));
    $smarty->assign('user',        $user_info);

    /* 获得该管理员的权限 */
    $priv_str = $db->getOne("SELECT action_list FROM " .$ecs->table('admin_user'). " WHERE user_id = '$_GET[id]'");

    /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
    if ($priv_str != 'all')
    {
        $smarty->assign('select_role',  get_role_list());
    }
    $smarty->assign('form_act',    'update');
    $smarty->assign('action',      'edit');

    assign_query_info();
    $smarty->display('privilege_info.htm');
}

/*------------------------------------------------------ */
//-- 更新管理员信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update' || $_REQUEST['act'] == 'update_self')
{

    /* 变量初始化 */
    $admin_id     = !empty($_REQUEST['id'])        ? intval($_REQUEST['id'])      : 0;
    $admin_name   = !empty($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
    $admin_email  = !empty($_REQUEST['email'])     ? trim($_REQUEST['email'])     : '';
    $admin_mobile = !empty($_REQUEST['mobile'])    ? trim($_REQUEST['mobile'])    : '';
    $ec_salt      = rand(1,9999);

    $password = !empty($_POST['new_password']) ? ", password = '".md5(md5($_POST['new_password']).$ec_salt)."'"    : '';
    if ($_REQUEST['act'] == 'update')
    {
        /* 查看是否有权限编辑其他管理员的信息 */
        if ($_SESSION['admin_id'] != $_REQUEST['id'])
        {
            admin_priv('admin_manage');
        }
        $g_link = 'privilege.php?act=list';
        $nav_list = '';
    }
    else
    {
        $nav_list = !empty($_POST['nav_list'])     ? ", nav_list = '".@join(",", $_POST['nav_list'])."'" : '';
        $admin_id = $_SESSION['admin_id'];
        $g_link = 'privilege.php?act=modif';
    }
    /* 判断管理员是否已经存在 */
    if (!empty($admin_name))
    {
        $is_only = $exc->num('user_name', $admin_name, $admin_id);
        if ($is_only == 1)
        {
            sys_msg(sprintf($_LANG['user_name_exist'], stripslashes($admin_name)), 1);
        }
    }

    /* Email地址是否有重复 */
    if (!empty($admin_email))
    {
        $is_only = $exc->num('email', $admin_email, $admin_id);

        if ($is_only == 1)
        {
            sys_msg(sprintf($_LANG['email_exist'], stripslashes($admin_email)), 1);
        }
    }

    //如果要修改密码
    $pwd_modified = false;

    if (!empty($_POST['new_password']))
    {
        /* 查询旧密码并与输入的旧密码比较是否相同 */
        $sql = "SELECT password FROM ".$ecs->table('admin_user')." WHERE user_id = '$admin_id'";
        $old_password = $db->getOne($sql);
        $sql ="SELECT ec_salt FROM ".$ecs->table('admin_user')." WHERE user_id = '$admin_id'";
        $old_ec_salt= $db->getOne($sql);
        if(empty($old_ec_salt))
        {
            $old_ec_password=md5($_POST['old_password']);
        }
        else
        {
            $old_ec_password=md5(md5($_POST['old_password']).$old_ec_salt);
        }
        if ($old_password <> $old_ec_password)
        {
            $link[] = array('text' => $_LANG['go_back'], 'href'=>'javascript:history.back(-1)');
            sys_msg($_LANG['pwd_error'], 0, $link);
        }

        /* 比较新密码和确认密码是否相同 */
        if ($_POST['new_password'] <> $_POST['pwd_confirm'])
        {
            $link[] = array('text' => $_LANG['go_back'], 'href'=>'javascript:history.back(-1)');
            sys_msg($_LANG['js_languages']['password_error'], 0, $link);
        }
        else
        {
            $pwd_modified = true;
        }
    }

    $role_id = '';
    $action_list = '';
    if (!empty($_POST['select_role']))
    {
        $sql = "SELECT action_list FROM " . $ecs->table('role') . " WHERE role_id = '".$_POST['select_role']."'";
        $row = $db->getRow($sql);
        $action_list = ', action_list = \''.$row['action_list'].'\'';
        $role_id = ', role_id = '.$_POST['select_role'].' ';
    }
    //更新管理员信息
    if($pwd_modified)
    {
        $sql = "UPDATE " .$ecs->table('admin_user'). " SET ".
            "user_name = '$admin_name', ".
            "email = '$admin_email' ".
            //"mobile = '$admin_mobile', ".
            "ec_salt = '$ec_salt' ".
            $action_list.
            $role_id.
            $password.
            $nav_list.
            "WHERE user_id = '$admin_id'";
    }
    else
    {
        $sql = "UPDATE " .$ecs->table('admin_user'). " SET ".
            "user_name = '$admin_name', ".
            "email = '$admin_email' ".
            //"mobile = '$admin_mobile' ".
            $action_list.
            $role_id.
            $nav_list.
            "WHERE user_id = '$admin_id'";
    }

    $db->query($sql);
    /* 记录管理员操作 */
    admin_log($_POST['user_name'], 'edit', 'privilege');

    /* 如果修改了密码，则需要将session中该管理员的数据清空 */
    if ($pwd_modified && $_REQUEST['act'] == 'update_self')
    {
        $sess->delete_spec_admin_session($_SESSION['admin_id']);
        $msg = $_LANG['edit_password_succeed'];
    }
    else
    {
        $msg = $_LANG['edit_profile_succeed'];
    }

    /* 提示信息 */
    $link[] = array('text' => strpos($g_link, 'list') ? $_LANG['back_admin_list'] : $_LANG['modif_info'], 'href'=>$g_link);
    sys_msg("$msg<script>parent.document.getElementById('header-frame').contentWindow.document.location.reload();</script>", 0, $link);

}

elseif ($_REQUEST['act'] == 'editpasswd')
{

    /* 查看是否有权限编辑其他管理员的信息 */
    if ($_SESSION['admin_id'] != $_REQUEST['id'])
    {
        admin_priv('passwd');
    }

    /* 获取管理员信息 */
    $sql = "SELECT user_id, user_name, email, password, agency_id, role_id FROM " .$ecs->table('admin_user').
        " WHERE user_id = '".$_SESSION['admin_id']."'";
    $user_info = $db->getRow($sql);

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['admin_edit']);
    $smarty->assign('action_link', array('text' => '管理员列表', 'href'=>'privilege.php?act=list'));
    $smarty->assign('user',       $user_info);
    $smarty->assign('noedit',     'readonly');

    /* 获得该管理员的权限 */
    $priv_str = $db->getOne("SELECT action_list FROM " .$ecs->table('admin_user'). " WHERE user_id = '$_GET[id]'");

    /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
    if ($priv_str != 'all')
    {
        $smarty->assign('select_role',  get_role_list());
    }

    $smarty->assign('form_act',    'update');
    $smarty->assign('action',      'edit');

    assign_query_info();
    $smarty->display('privilege_info.htm');
}

/*------------------------------------------------------ */
//-- 编辑个人资料
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'modif')
{
    /* 不能编辑demo这个管理员 */
    if ($_SESSION['admin_name'] == 'demo')
    {
        $link[] = array('text'=>$_LANG['back_admin_list'],'href'=>'privilege.php?act=list');
        sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
    }

    include_once('includes/inc_menu.php');
    include_once('includes/inc_priv.php');

    /* 包含插件菜单语言项 */
    $sql = "SELECT code FROM ".$ecs->table('plugins');
    $rs = $db->query($sql);
    while ($row = $db->FetchRow($rs))
    {
        /* 取得语言项 */
        if (file_exists(ROOT_PATH.'plugins/'.$row['code'].'/languages/common_'.$_CFG['lang'].'.php'))
        {
            include_once(ROOT_PATH.'plugins/'.$row['code'].'/languages/common_'.$_CFG['lang'].'.php');
        }

        /* 插件的菜单项 */
        if (file_exists(ROOT_PATH.'plugins/'.$row['code'].'/languages/inc_menu.php'))
        {
            include_once(ROOT_PATH.'plugins/'.$row['code'].'/languages/inc_menu.php');
        }
    }

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }

    ksort($modules);

    foreach ($modules AS $key => $val)
    {
        if (is_array($val))
        {
            foreach ($val AS $k => $v)
            {
                if (is_array($purview[$k]))
                {
                    $boole = false;
                    foreach ($purview[$k] as $action)
                    {
                        $boole = $boole || admin_priv($action, '', false);
                    }
                    if (!$boole)
                    {
                        unset($modules[$key][$k]);
                    }
                }
                elseif (! admin_priv($purview[$k], '', false))
                {
                    unset($modules[$key][$k]);
                }
            }
        }
    }

    /* 获得当前管理员数据信息 */
    $sql = "SELECT user_id, user_name, email, nav_list ".
        "FROM " .$ecs->table('admin_user'). " WHERE user_id = '".$_SESSION['admin_id']."'";
    $user_info = $db->getRow($sql);

    /* 获取导航条 */
    $nav_arr = (trim($user_info['nav_list']) == '') ? array() : explode(",", $user_info['nav_list']);
    $nav_lst = array();
    foreach ($nav_arr AS $val)
    {
        $arr              = explode('|', $val);
        $nav_lst[$arr[1]] = $arr[0];
    }

    /* 模板赋值 */
    $smarty->assign('lang',        $_LANG);
    $smarty->assign('ur_here',     $_LANG['modif_info']);
    $smarty->assign('action_link', array('text' => $_LANG['admin_list'], 'href'=>'privilege.php?act=list'));
    $smarty->assign('user',        $user_info);
    $smarty->assign('menus',       $modules);
    $smarty->assign('nav_arr',     $nav_lst);

    $smarty->assign('form_act',    'update_self');
    $smarty->assign('action',      'modif');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('privilege_info.htm');
}

/*------------------------------------------------------ */
//-- 为管理员分配权限
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'allot')
{
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/priv_action.php');

    admin_priv('allot_priv');
    if ($_SESSION['admin_id'] == $_GET['id'])
    {
        admin_priv('all');
    }

    /* 获得该管理员的权限 */
    $priv_str = $db->getOne("SELECT action_list FROM " .$ecs->table('admin_user'). " WHERE user_id = '$_GET[id]'");

    /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
    if ($priv_str == 'all')
    {
        $link[] = array('text' => $_LANG['back_admin_list'], 'href'=>'privilege.php?act=list');
        sys_msg($_LANG['edit_admininfo_cannot'], 0, $link);
    }

    /* 获取权限的分组数据 */
    $sql_query = "SELECT action_id, parent_id, action_code,relevance FROM " .$ecs->table('admin_action').
        " WHERE parent_id = 0";
    $res = $db->query($sql_query);
    while ($rows = $db->FetchRow($res))
    {
        $priv_arr[$rows['action_id']] = $rows;
    }

    /* 按权限组查询底级的权限名称 */
    $sql = "SELECT action_id, parent_id, action_code FROM " .$ecs->table('admin_action').
        " WHERE parent_id " .db_create_in(array_keys($priv_arr));
    $result = $db->query($sql);
    while ($priv = $db->FetchRow($result))
    {
        $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
    }

    // 将同一组的权限使用 "," 连接起来，供JS全选
    foreach ($priv_arr AS $action_id => $action_group)
    {
        if (!is_array($action_group['priv']))
        {
            continue;
        }

        $priv_arr[$action_id]['priv_list'] = implode(',', @array_keys($action_group['priv']));

        foreach ($action_group['priv'] AS $key => $val)
        {
            $priv_arr[$action_id]['priv'][$key]['cando'] = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
        }
    }

    /* 赋值 */
    $smarty->assign('lang',        $_LANG);
    $smarty->assign('ur_here',     $_LANG['allot_priv'].'['.$_GET['user'].']');
    $smarty->assign('action_link', array('href'=>'privilege.php?act=list', 'text'=>$_LANG['02_admin_list']));
    $smarty->assign('priv_arr',    $priv_arr);
    $smarty->assign('form_act',    'update_allot');
    $smarty->assign('user_id',     $_GET['id']);

    /* 显示页面 */
    assign_query_info();
    $smarty->display('privilege_allot.htm');
}

/*------------------------------------------------------ */
//-- 更新管理员的权限
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update_allot')
{
    admin_priv('admin_manage');

    /* 取得当前管理员用户名 */
    $admin_name = $db->getOne("SELECT user_name FROM " .$ecs->table('admin_user'). " WHERE user_id = '$_POST[id]'");

    /* 更新管理员的权限 */
    $act_list = @join(",", $_POST['action_code']);
    $sql = "UPDATE " .$ecs->table('admin_user'). " SET action_list = '$act_list' ".
        "WHERE user_id = '$_POST[id]'";

    $db->query($sql);
    /* 动态更新管理员的SESSION */
    if ($_SESSION["admin_id"] == $_POST['id'])
    {
        $_SESSION["action_list"] = $act_list;
    }

    /* 记录管理员操作 */
    admin_log(addslashes($admin_name), 'edit', 'privilege');

    /* 提示信息 */
    $link[] = array('text' => $_LANG['back_admin_list'], 'href'=>'privilege.php?act=list');
    sys_msg($_LANG['edit'] . "&nbsp;" . $admin_name . "&nbsp;" . $_LANG['action_succeed'], 0, $link);

}

/*------------------------------------------------------ */
//-- 删除一个管理员
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('admin_drop');

    $id = intval($_GET['id']);

    /* 获得管理员用户名 */
    $admin_name = $db->getOne('SELECT user_name FROM '.$ecs->table('admin_user')." WHERE user_id='$id'");

    /* demo这个管理员不允许删除 */
    if ($admin_name == 'demo')
    {
        make_json_error($_LANG['edit_remove_cannot']);
    }

    /* ID为1的不允许删除 */
    if ($id == 1)
    {
        make_json_error($_LANG['remove_cannot']);
    }

    /* 管理员不能删除自己 */
    if ($id == $_SESSION['admin_id'])
    {
        make_json_error($_LANG['remove_self_cannot']);
    }

    if ($exc->drop($id))
    {
        $sess->delete_spec_admin_session($id); // 删除session中该管理员的记录

        admin_log(addslashes($admin_name), 'remove', 'privilege');
        clear_cache_files();
    }

    $url = 'privilege.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

elseif($_REQUEST['act'] == 'set_manager')
{
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('admin_user')." SET manager=$_POST[manager] WHERE user_id=$_POST[id]";
    $GLOBALS['db']->query($sql);

    $sql = 'SELECT user_id, manager FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$_POST[id]";
    $res = $GLOBALS['db']->getRow($sql);
    $res['manager'] = $GLOBALS['db']->getOne('SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$res[manager]");

    include_once(ROOT_PATH.'includes/cls_json.php');
    $json = new JSON;
    echo $res = $json->encode($res);
    exit;
}

elseif ($_REQUEST['act'] == 'change_status')
{
    $sql = 'UPDATE '.$ecs->table('admin_user')." SET status=IF(status, 0, 1) WHERE user_id=$_POST[id]";
    if ($db->query($sql))
    {
        $sql = 'SELECT status, user_id FROM '.$ecs->table('admin_user')." WHERE user_id=$_POST[id]";
        $res = $db->getRow($sql);
        include_once(ROOT_PATH.'includes/cls_json.php');
        $json = new JSON();
        echo $json->encode($res);
        exit;
    }
}

elseif ($_REQUEST['act'] == 'change_stats')
{
    $sql = 'UPDATE '.$ecs->table('admin_user')." SET stats=IF(stats, 0, 1) WHERE user_id=$_POST[id]";
    if ($db->query($sql))
    {
        $sql = 'SELECT stats, user_id FROM '.$ecs->table('admin_user')." WHERE user_id=$_POST[id]";
        $res = $db->getRow($sql);
        include_once(ROOT_PATH.'includes/cls_json.php');
        $json = new JSON();
        die($json->encode($res));
    }
}

elseif ($_REQUEST['act'] == 'max')
{
    $adminId = intval($_REQUEST['admin_id']);
    $maxNum = intval($_REQUEST['maxNum']);
    if ($adminId && $maxNum)
    {
        $sql = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').
            " SET max_customer=$maxNum WHERE user_id=$adminId";
        $GLOBALS['db']->query($sql);
        die($maxNum);
    }
}


/* 清除购物车中过期的数据 */
function clear_cart()
{
    /* 取得有效的session */
    $sql = "SELECT DISTINCT session_id " .
        " FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " .
        $GLOBALS['ecs']->table('sessions') . " AS s " .
        " WHERE c.session_id = s.sesskey ";
    $valid_sess = $GLOBALS['db']->getCol($sql);

    // 删除cart中无效的数据
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
        " WHERE session_id NOT " . db_create_in($valid_sess);
    $GLOBALS['db']->query($sql);
}

/**
 * 员工信息建档
 */
function insert_staff_info ($staff_info)
{
    $fields = array_keys($staff_info);
    $values = array_values($staff_info);

    $fields = implode(',', $fields);
    $values = implode('","', $values);

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('staff_records')."($fields)VALUES(\"$values\")";
    $GLOBALS['db']->query($sql_insert);

    return $GLOBALS['db']->insert_id();
}

/**
 * 处理员工的工作经历、学习经历、紧急联系人
 */
function deal_satellite_info($staff_id, $info, $table)
{
    $fields = array ();
    $values = array();
    foreach ($info as $val){
        if ((isset($val['school']) && empty($val['school'])) || (isset($val['company']) && empty($val['company']))) {
            continue;
        }

        if (!empty($val)) {
            if (empty($fields)) {
                $tmp_field = array_keys($val);
                array_unshift($tmp_field, 'staff_id');
                $fields = implode(',', $tmp_field);
            }

            $tmp_value = array_values($val);
            array_unshift($tmp_value, $staff_id);
            $value[] = implode('","', $tmp_value);
        }
    }

    if (empty($value) || empty($fields)) {
        return false;
    }

    $values = implode('"),("', $value);
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table($table)."($fields)VALUES(\"$values\")\r\n";

    return $GLOBALS['db']->query($sql_insert);
}

/**
 * 生成员工编号
 */
function generate_staff_id ()
{
    $sql_select = 'SELECT staff_id FROM '.$GLOBALS['ecs']->table('staff_records').' ORDER BY staff_id DESC';
    $staff_id = $GLOBALS['db']->getOne($sql_select);

    $staff_id = $staff_id ? ++$staff_id : 1;

    $len = strlen($staff_id);
    for (;$len < 4; $len++) {
        $staff_id = '0'.$staff_id;
    }

    $staff_id = 'GZ'.date('ymd').$staff_id;

    return $staff_id;
}

/**
 * 添加新的工作账号
 */
function insert_admin_info($info)
{
    // 查询该客服是否已经存在
    $sql_select = 'SELECT user_id FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE status>0 AND mobile={$info['staff_phone']}";
    $user_id = $GLOBALS['db']->getOne($sql_select);
    if ($user_id) {
        return $user_id;
    }

    $passwdStr = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $passwd = '';
    $ec_salt = '';
    for ($len = 0; $len < 5;) {
        $passwd .= $passwdStr[mt_rand(0, strlen($passwdStr))];
        $len = strlen($passwd);
        $ec_salt .= $passwdStr[mt_rand(0, strlen($passwdStr))];
    }

    $password = md5(md5($passwd).$ec_salt);

    $sql_select = 'SELECT action_list FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id={$info['branch_id']}";
    $action_list = $GLOBALS['db']->getOne($sql_select);

    $time = time();
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('admin_user').
        '(user_name, password, ec_salt, mobile, stats, status, add_time, action_list, role_id, assign)VALUES('.
        "'{$info['staff_name']}', '$password', '$ec_salt', {$info['staff_phone']}, 1, 1, $time, '$action_list', {$info['branch_id']}, 0)";
    if ($GLOBALS['db']->query($sql_insert))
    {
        include_once('../includes/cls_sms.php');
        $sms = new sms;
        $sms->send($info['staff_phone'], "欢迎加入康健人生大家庭，登录账号是{$info['staff_name']}，密码是$passwd");
    }

    return $GLOBALS['db']->insert_id();
}
//龙虎榜
function get_sale_rank(){
    $role_id = intval($_REQUEST['role_id']);
    $admin_id = intval($_REQUEST['admin_id']);
    if ($role_id>32 && $role_id != 38) {
        $sql_sub = " AND platform=$role_id AND admin_id=$admin_id AND order_type NOT IN(0,1,3) GROUP BY admin_id";
        //10：00之前调用昨天的数据
        if(date('H')<=10){
            $start = strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
            $end = strtotime(date('Y-m-d 23:59:59',strtotime('-1 day')));
        }else{
            $start = strtotime(date('Y-m-d 00:00:00'));
            $end = strtotime(date('Y-m-d 23:59:59'));
        }
        $rank_list = get_ranklist($sql_sub,'personal_ranklist',$start,$end);
        //获取相片
        if ($rank_list) {
            foreach ($rank_list as &$v) {
                $sql = 'SELECT avator_url FROM '.$GLOBALS['ecs']->table('oa_staff_records')." WHERE user_id={$v['admin_id']}";
                $img = $GLOBALS['db']->getOne($sql);
                $v['img'] = substr($img,2);
            }
        }
        return $rank_list;
    }
}
?>
