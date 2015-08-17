<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

if ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);
    die($smarty->fetch('left.htm'));
}

/* 员工列表 */
elseif ($_REQUEST['act'] == 'operator_manage')
{
    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    if (admin_priv('all', '', false)) {
    } elseif (admin_priv('admin_trans-part_view', '', false)) {
        $role_list = trans_part_list();
        $role_list = implode(',', $role_list);
        $where = " WHERE ad.role_id IN ($role_list) ";
        $smarty->assign('assign_user', 1);
    } else {
        $where = " WHERE ad.role_id={$_SESSION['role_id']} ";
        $smarty->assign('assign_user', 1);
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_user')." ad $where";

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
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
        'record_count'  => $filter['record_count'],
        'page_size'     => $filter['page_size'],
        'page'          => $filter['page'],
        'page_set'      => $page_set,
        'condition'     => $condition,
        'start'         => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'           => $filter['page']*$filter['page_size'],
        'act'           => 'operator_manage',
    );

    $sql_select = "SELECT ad.user_id,ad.user_name,ad.mobile,ad.phone,ad.ext,ad.status,ad.stats,ad.freeze,r.role_name FROM " . $GLOBALS['ecs']->table('admin_user').
        ' AS ad LEFT JOIN '.$GLOBALS['ecs']->table('role').' AS r ON ad.role_id=r.role_id'.$where.
        ' ORDER BY status DESC,convert(r.role_name using gbk) DESC,ad.add_time DESC LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";
    $account_list = $GLOBALS['db']->getAll($sql_select);

    //foreach($account_list as &$val) {
    //    $val['change_time'] = date('Y-m-d', $val['change_time']);
    //    $val['last_login']  = date('Y-m-d', $val['last_login']);
    //}

    $smarty->assign('filter',       $filter);
    $smarty->assign('account_list', $account_list);
    $smarty->assign('role_list',    get_role_lists());

    $smarty->assign('curr_title', '员工列表');

    $res['main'] = $smarty->fetch('op_list.htm');

    die($json->encode($res));
}

//管理员是否启用。
elseif($_REQUEST['act'] == 'disable')
{
    $uid   = intval($_REQUEST['uid']);
    $field = mysql_real_escape_string($_REQUEST['field']);

    //更新状态
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user')." SET $field=IF($field, 0, 1) WHERE user_id=$uid";
    $GLOBALS['db']->query($sql_update);

    $sql = "SELECT $field FROM ".$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$uid";
    $res = $GLOBALS['db']->getOne($sql);

    if ('assign' == $field) {
        $sql_select = 'SELECT counter FROM '.$GLOBALS['ecs']->table('admin_user').' ORDER BY counter DESC';
        $counter = $GLOBALS['db']->getOne($sql_select);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user')." SET counter=$counter WHERE user_id=$uid";
        $GLOBALS['db']->query($sql_update);
    }

    $user_json = array (
        'uid'     => $uid,
        'content' => $res,
        'note'    => $field
    );

    die($json->encode($user_json));
}

//添加管理员模板
elseif($_REQUEST['act'] == 'addadmin')
{
    $smarty->assign('curr_title', '添加新员工');
    $smarty->assign('select',get_role_lists());

    $res['main'] = $smarty->fetch('add_user.htm');
    die($json->encode($res));
}

//这里是对管理员的添加操作；
elseif($_REQUEST['act'] == 'adds') {
    $username = trim(mysql_real_escape_string($_REQUEST['username']));
    $mobile   = trim(mysql_real_escape_string($_REQUEST['mobile']));
    $name     = trim(mysql_real_escape_string($_REQUEST['name']));
    $password = mysql_real_escape_string($_REQUEST['password']);
    $pass     = $_REQUEST['pass'];
    $role_id  = intval($_REQUEST['roles']);
    $group_id = intval($_REQUEST['group_id']);
    $ext = intval($_REQUEST['ext']);
    $time     = $_SERVER['REQUEST_TIME'];

    if(!empty($ec_salt)) {
        $password = md5(md5($password.$ec_salt));
    } else {
        $password = md5($password);
    }

    $res            = array();
    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    $res['code']    = false;

    $sql = 'INSERT INTO '.$GLOBALS['ecs']->table('admin_user').
        '(user_name,password,ec_salt,add_time,status,mobile,role_id,group_id,ext)VALUES'.
        "('$username','$password','$ec_salt','$time',1,'$mobile',$role_id,$group_id,$ext)";
    $result = mysql_query($sql);
    if($result) {
        $user_id = $GLOBALS['db']->insert_id();
        $sql = 'SELECT action_list FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id=$role_id";
        $action_list = $GLOBALS['db']->getOne($sql);

        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').' SET action_list='."'$action_list' WHERE user_id=$user_id";
        $res['code'] = $GLOBALS['db']->query($sql_update);

        if($res['code']) {
            $res['message'] = '添加成功';
        } else {
            $res['message'] = '添加失败';
        }
    }

    die($json->encode($res));
}

//对用户名的判断；
elseif($_REQUEST['act'] == 'user')
{
    //判断用户名是否可用；
    $user = trim(mysql_real_escape_string($_REQUEST['user']));

    $sql = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user'). "WHERE user_name='$user'";
    $res = $db->getOne($sql);
    if($res)
        die("该用户已存在");
    else
        die("恭喜，该用户可添加");
}

//对角色管理的操作；
elseif($_REQUEST['act'] == 'role_manage')
{
    $role_list=get_role_lists();
    $smarty->assign('role_list', $role_list);
    $res['main'] = $smarty->fetch('role_list.htm');

    die($json->encode($res));

}

//对角色员的添加	
elseif($_REQUEST['act'] == 'addrole')
{
    $sql_query = "SELECT * FROM " .$ecs->table('admin_action').
        " WHERE parent_id = 0";
    $res = $db->query($sql_query);
    while ($rows = $db->FetchRow($res))
    {
        $priv_arr[$rows['action_id']] = $rows;
    }

    /* 按权限组查询底级的权限名称 */
    $sql = "SELECT * FROM " .$ecs->table('admin_action').
        " WHERE parent_id " .db_create_in(array_keys($priv_arr));
    $result = $db->query($sql);

    while ($priv = $db->FetchRow($result))
    {
        $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
    }

    // 将同一组的权限使用 "," 连接起来，供JS全选
    foreach ($priv_arr AS $action_id => $action_group)
    {
        $priv_arr[$action_id]['priv_list'] = join(',', @array_keys($action_group['priv']));

        foreach ($action_group['priv'] AS $key => $val)
        {
            $priv_arr[$action_id]['priv'][$key]['cando'] = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
        }
    }

    /* 模板赋值 */
    $smarty->assign('priv_arr',    $priv_arr);

    $ress['main'] = $smarty->fetch('add_role.htm');
    die($json->encode($ress));
}

/* 把添加新角色值插入数据库 */
elseif($_REQUEST['act'] == 'role_keeps')
{
    $action_list = mysql_real_escape_string($_POST['str']);
    $role_name = mysql_real_escape_string($_POST['role_name']);
    $compete = $_POST['role_vie'];
    $manager = $_POST['role_manage'];
    $role_type = $_POST['role_type'];
    $role_describe = $_POST['role_desc'];

    $sql = 'INSERT INTO crm_role (role_name,action_list,role_describe,compete,manager,role_type) VALUES '.
        "('$role_name','$action_list','$role_describe','$compete','$manager','$role_type')";

    $result = $GLOBALS['db']->query($sql);

    if($result)
        die(1);
    else
        die(2);
}

//编辑管理员信息模板
elseif ($_REQUEST['act'] == 'edit_admin')
{
    $user_id = intval($_REQUEST['user_id']);
    /* 不能编辑demo这个管理员 */
    if ($_SESSION['admin_name'] == 'demo') {
    }

    /* 查看是否有权限编辑其他管理员的信息 */
    if ($_SESSION['admin_id'] != $_REQUEST['id']) {
        admin_priv('admin_manage','',false);
    }

    /* 获取管理员信息 */
    $sql = 'SELECT ext,user_id,user_name,mobile,role_id FROM ' .$GLOBALS['ecs']->table('admin_user').
        " WHERE user_id=$user_id";
    $user_info = $db->getRow($sql);

    $smarty->assign('action_link', array('text' => '管理员信息编辑', 'href'=>'system.php?act=operator_manage'));
    $smarty->assign('user',        $user_info);

    /* 获得该管理员的权限 */
    $priv_str = $GLOBALS['db']->getOne('SELECT action_list FROM ' .$GLOBALS['ecs']->table('admin_user'). " WHERE user_id=$user_id");

    /* 如果被编辑的管理员拥有了all这个权限，将不能编辑 */
    if ($priv_str != 'all')
    {
        $smarty->assign('select',  get_role_list());
    }

    $smarty->assign('form_act',    'update');
    $smarty->assign('action',      'edit');

    $res['main'] = $smarty->fetch('update_user.htm');
    die($json->encode($res));
}

//编辑管理员信息操作
elseif ($_REQUEST['act'] == 'edit_admin_done')
{
    if(admin_priv('edit_admin_done','',false))
    {
        $user_id      = intval($_REQUEST['user_id']);
        $number       = mysql_real_escape_string($_REQUEST['user_id']);
        $pre_password = mysql_real_escape_string($_REQUEST['pre_password']);
        $password     = mysql_real_escape_string($_REQUEST['password']);
        $pass         = mysql_real_escape_string($_REQUEST['pass']);
        $mobile       = mysql_real_escape_string($_REQUEST['mobile']);
        $name         = mysql_real_escape_string($_REQUEST['name']);
        $role_id      = intval($_REQUEST['role_id']);
        $ext          = intval($_REQUEST['ext']);

        //判断是否修改密码
        if(!empty($password))
        {
            /* 查询旧密码并与输入的旧密码比较是否相同 */
            //$sql = 'SELECT password FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id = $user_id";
            //$old_password = $GLOBALS['db']->getOne($sql);

            //$sql ="SELECT ec_salt FROM ".$ecs->table('admin_user')." WHERE user_id = '$admin_id'";
            //$old_ec_salt= $db->getOne($sql);    //旧工号

            $old_ec_salt = true;
            if(empty($old_ec_salt))
            {
                $old_ec_password=md5($pre_password);
            }
            else
            {
                //$old_ec_password=md5(md5($pre_password.$old_ec_salt));
            }

            //if ($pre_password <> $old_ec_password)
            //{
            //    $res['message'] = '输入的旧密码不正确';
            //    die($res);
            //}

            /* 比较新密码和确认密码是否相同 */
            if ($password <> $pass)
            {
                $res['message'] = '两次输入的密码不正确';
                die($res);
            }
            else
            {
                $pwd_modified = true;
                $password = ',password=\''.md5($password).'\'';
            }
        }

        if ($ext) {
            $ext = ' ,ext='.$ext;
        }

        if (!empty($role_id)) {
            $sql = 'SELECT action_list FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id=$role_id";
            $row = $GLOBALS['db']->getRow($sql);
            $action_list = ', action_list = \''.$row['action_list'].'\'';
            $role_id = ' role_id = '.$role_id.' ';
        }

        //更新管理员信息
        if($pwd_modified)
        {
            $sql_update = 'UPDATE '.$ecs->table('admin_user'). ' SET '.
                $role_id.
                $action_list.
                $password.
                $ext.
                ',mobile='."'$mobile'".
                ',ec_salt='."'$number'".
                " WHERE user_id=$user_id";
        }
        else
        {
            $sql_update = 'UPDATE ' .$ecs->table('admin_user'). ' SET '.
                $role_id.
                $action_list.
                ',mobile='."'$mobile'".
                ',ec_salt='."'$number'".
                " WHERE user_id=$user_id";
        }
        $code = $GLOBALS['db']->query($sql_update);
        if($code)
            $res = crm_msg('修改成功',$code);
        elseif($code)
            $res = crm_msg('修改失败',$code);
        die($json->encode($res));
    }
}

//分派权限模板
elseif ($_REQUEST['act'] == 'assign_authority')
{
    if($_REQUEST['user_id'] != '')
    {
        $user_id = intval($_REQUEST['user_id']);
        $smarty->assign('user_id',$user_id);
        $sql_select  = 'SELECT * FROM '.$GLOBALS['ecs']->table('admin_user').
            " WHERE user_id=$user_id";    
    }
    elseif($_REQUEST['role_id'] != '')
    {
        $role_id = intval($_REQUEST['role_id']);
        $smarty->assign('role_id',$role_id);
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role')." WHERE role_id=$role_id";
    }

    $result = $GLOBALS['db']->getRow($sql_select);

    $action_list = explode(',',$result['action_list']);
    array_pop($action_list);

    $smarty->assign('user',$result);
    $smarty->assign('priv_arr',get_action_list());
    $smarty->assign('action_list',$action_list);

    $res['main'] = $smarty->fetch('upd_admin_action.htm');
    die($json->encode($res));
}

//分派权限操作
elseif ($_REQUEST['act'] == 'assign_authority_done')
{
    $action_list = trim($_REQUEST['action_list']);      //权限列表
    $user_id = intval($_REQUEST['user_id']);            //管理员ID
    $role_id = intval($_REQUEST['role_id']);            //角色ID

    $arr_action = explode(',',$action_list);
    array_pop($arr_action);

    for($i=0;$i<count($arr_action);$i++)
    {
        $arr_action[$i] = '\''.$arr_action[$i].'\'';
    }

    $str_action = implode(',',$arr_action);

    $where = " WHERE action_code IN($str_action) AND action_level=1";
    $sql_select = 'SELECT action_id FROM '.$GLOBALS['ecs']->table('admin_action').$where;

    $arr_action_list = $GLOBALS['db']->getCol($sql_select);
    $action_list_id = implode(',',$arr_action_list);

    $sql_select = 'SELECT action_code FROM '.$GLOBALS['ecs']->table('admin_action')." WHERE parent_id IN($action_list_id)";
    $result = $GLOBALS['db']->getCol($sql_select);
    $str_action_list = implode(',',$result);

    $action_list .= $str_action_list;

    if($user_id != 0)
    {
        //并加入第三级操作
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user').
            ' SET action_list='."'$action_list'"." WHERE user_id=$user_id" ;
    }
    elseif($role_id != 0)
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('role').
            ' SET action_list='."'$action_list'"." WHERE role_id=$role_id" ;
    }

    $res['req_msg'] = true;
    $res['timeout'] = 2000;
    if($GLOBALS['db']->query($sql_update))
    {
        $res['message'] = '设置成功';
    }
    else
    {
        $res['message'] = '设置失败';
    }

    die($json->encode($res));
}

//通过部门检索管理员
elseif ($_REQUEST['act'] == 'admin_by_role')
{
    $role_id  = intval($_REQUEST['role_id']);
    $group_id = intval($_REQUEST['group_id']);

    // 分页
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 20; 
    }

    $where = $condition = '';
    if($role_id) {
        $where     = " WHERE r.role_id=$role_id";
        $condition = "&role_id=$role_id";
    }

    if ($group_id) {
        $where     .= " AND group_id=$group_id";
        $condition .= "&group_id=$group_id";
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_user').' r '.$where;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
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
        'filter'       => $filter,
        'page_count'   => $filter['page_count'],
        'record_count' => $filter['record_count'],
        'page_size'    => $filter['page_size'],
        'page'         => $filter['page'],
        'page_set'     => $page_set,
        'condition'    => $condition,
        'start'        => ($filter['page'] - 1)*$filter['page_size'] +1,
        'end'          => $filter['page']*$filter['page_size'],
        'act'          => 'admin_by_role',
    );

    $sql_select = 'SELECT ad.*,r.role_name FROM '.$GLOBALS['ecs']->table('admin_user').
        ' AS ad LEFT JOIN '.$GLOBALS['ecs']->table('role').' AS r on ad.role_id=r.role_id '. $where.' ORDER BY ad.status DESC'.
        ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";
    $result = $GLOBALS['db']->getAll($sql_select);

    foreach($result as &$val) {
        $val['last_login'] = date('Y-m-d',$val['last_login']);
        $val['change_time'] = date('Y-m-d',$val['change_time']);
    }

    $smarty->assign('account_list',$result);
    $smarty->assign('filter',$filter);
    $smarty->assign('condition',$condition);

    $res['response_action'] = 'search_service';
    $res['main'] = $smarty->fetch('admin_by_role.htm');

    die($json->encode($res));
}

/*
    //办公电脑管理
elseif ($_REQUEST['act'] == 'pc_manager')
{
    $room = $_REQUEST['room'];
    $pre_seat = $_REQUEST['pre_seat'];

    for($i=1;$i<=25;$i++)
    {
        if($i<10) 
        {
            $seat = $room.$pre_seat.'0'.$i;
        }
        else
        {
            $seat = $room.$pre_seat.$i;
        }
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('office_seat').
            "(seat,room)VALUES('$seat','$room')";
        $GLOBALS['db']->query($sql_insert);
    }
    exit();

    $res = array('req_msg'=>true,'message'=>'','timeout'=>2000);

    if(admin_priv('all','',false))
    {
        $super = 0;
        $role = 1;
    }
    elseif(admin_priv('pc_manager','',false))
    {
        $super = 1;
        $role = 1;
    }

    if($role)
    {
        $sql_select = 'SELECT room FROM '.$GLOBALS['ecs']->table('office_seat').' GROUP BY room';
        $room_info = $GLOBALS['db']->getAll($sql_select);
        $room = $room_info;
        $room_nu = count($room_info); //房间数量

        for($i =0; $i<$room_nu; $i++)
        {
            $sql_select= 'SELECT s.*,p.*,a.user_name AS admin_name,r.role_name FROM '.$GLOBALS['ecs']->table('office_seat').
                ' AS s LEFT JOIN '.$GLOBALS['ecs']->table('pc_manager').
                ' AS p ON s.pc_id=p.pc_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
                ' AS a ON s.admin_id=a.user_id LEFT JOIN '.$GLOBALS['ecs']->table('role').
                ' AS r ON a.role_id=r.role_id '.
                " WHERE s.room='{$room_info[$i]['room']}'";

            $room_info[$i]['seat_info'] = $GLOBALS['db']->getAll($sql_select);
        }

        for($i = 0; $i<$room_nu;$i++)
        {
            if($i == 0)
            {
                $room_info[$i]['status'] = "style=\"display:''\"";
            }
            else
            {
                $room_info[$i]['status'] = "style=\"display:none\"";
            }
        }
    }
    else
    {
        $res['message'] = '';
    }

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_type');
    $account_type = $GLOBALS['db']->getAll($sql_select);
    $account_info = array();
    $total = count($account_type);

    $account_type_list = array('qq','ppcrm','qqcrm','wangwang');
    for($i=0; $i<count($account_type_list); $i++)
    {
        $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('account').
            ' WHERE type_id = (SELECT type_id FROM '.$GLOBALS['ecs']->table('account_type').
            " WHERE label='{$account_type_list[$i]}')";
        $account_info[$account_type_list[$i]] = $GLOBALS['db']->getCol($sql_select);    
    }

    $smarty->assign('super',$super);
    $smarty->assign('room_info',$room_info);
    $smarty->assign('room',$room);
    $smarty->assign('account_info',$account_info);
    $smarty->assign('admin_info',get_admin_userlist());

    $res['main'] = $smarty->fetch('pc_manager.htm');
    die($json->encode($res));
}
 */
//安排座位员工列表
elseif ($_REQUEST['act'] == 'get_temp_admin')
{
    $res = array();
    $res['seat'] = mysql_real_escape_string($_REQUEST['seat']);
    $user_id = intval($_REQUEST['user_id']);

    //已经安排座位的员工
    $sql_select = 'SELECT admin_id FROM '.$GLOBALS['ecs']->table('office_seat').' WHERE admin_id <> 0';
    $is_exist = $GLOBALS['db']->getAll($sql_select);

    //在职员工
    $sql_select = 'SELECT user_name,user_id FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE status=1';
    $tem_admin_list = $GLOBALS['db']->getAll($sql_select);

    $select_obj = '<select id="admin_name" onblur="setAdmin(this.value,'.$res['seat'].')">';
    $select_obj .= '<option value="0">请选择员工</option>';
    foreach($tem_admin_list AS $val)
    {
        $selected = '';
        if(in_array($val['user_id'],$is_exist))
            $color = 'style="color:red"';

        if($val['user_id'] == $user_id && $user_id != 0)
        {
            $selected = ' selected';
        }

        $select_obj .= '<option '.$color.$selected.' value="'.$val['user_id'].'">'.$val['user_name'].'</option>';
    }

    $select_obj .= '</select>';
    $res['info'] = $select_obj;

    die($json->encode($res));
}

//安排座位
elseif ($_REQUEST['act'] == 'set_admin')
{
    $user_id = intval($_REQUEST['user_id']);
    $seat = mysql_real_escape_string($_REQUEST['seat']);
    $re_seat = intval($_REQUEST['re_seat']);

    $res['admin_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$user_id");

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('office_seat')."SET admin_id=$user_id, admin_name='{$res['admin_name']}',status=1 WHERE seat='$seat'";

    $result = $GLOBALS['db']->query($sql_update);

    $res['seat'] = $seat;
    if($result)
    {
        $res['code'] = 1;
        $res['info'] = '<label class="btn_new" onclick="getAdminAccount(this,'.
            $user_id.','.$seat.')">'.$res['admin_name'].'</label>'.
            '<img src="images/edit.gif" onclick="setInput('.$seat.','.
            "'$user_id'".
            ',0)" title="修改使用者" >'.
            '<img src="images/0.gif" onclick="delAdmin('.$user_id.','.$seat.')" title="删除使用者">';
        if(isset($_REQUEST['re_seat']))
        {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('office_seat').
                " SET admin_id=0,admin_name='',status=0 WHERE seat=$re_seat";
            $GLOBALS['db']->query($sql_update);
        }
    }
    else
        $res['code'] = 0;

    die($json->encode($res));
}

//删除某座位员工
elseif ($_REQUEST['act'] == 'del_admin_from_seat')
{
    $res['seat'] = mysql_real_escape_string($_REQUEST['seat']);
    $user_id = intval($_REQUEST['user_id']);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('office_seat').
        "SET admin_id=0,admin_name='',status=0 WHERE admin_id=$user_id";
    if($GLOBALS['db']->query($sql_update))
    {
        $res['info'] = '<img src="images/contact_add.png" title="添加员工" onclick="setInput('
            .$res['seat'].',0,0)" />';
    }

    die($json->encode($res));
}

//获得员工拥有的平台帐号
elseif ($_REQUEST['act'] == 'get_admin_account')
{
    $res = array();
    $user_id = intval($_REQUEST['user_id']);

    $sql_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_work_account').
        " WHERE admin_id=$user_id";
    $is_account_exit = $GLOBALS['db']->getOne($sql_count);

    if($is_account_exit)
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('admin_work_account').
            " WHERE admin_id=$user_id";
        $res['account_info'] = $GLOBALS['db']->getAll($sql_select);
        $res['account'] = 1;
    }
    else
    {
        $res['account'] = 0;
    }

    $res['account_info'] = $res['account_info'][0];

    $res['user_id'] = $user_id;
    die($json->encode($res));

}

//修改员工平台账号
elseif ($_REQUEST['act'] == 'update_admin_account')
{
    $admin_id = intval($_REQUEST['admin_id']);
    $qq = mysql_real_escape_string($_REQUEST['qq']);
    $qqcrm = mysql_real_escape_string($_REQUEST['qqcrm']);
    $ppcrm = mysql_real_escape_string($_REQUEST['ppcrm']);
    $wangwang = mysql_real_escape_string($_REQUEST['wangwang']);
    $qq = mysql_real_escape_string($_REQUEST['qq']);

    $res = array('code'=>0,'timeout'=>2000,'message'=>'','req_msg'=>true);

    $sql_count = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_work_account').
        " WHERE admin_id=$admin_id"; 

    $update = $GLOBALS['db']->getOne($sql_count);
    if($update)
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_work_account').
            " SET qq='$qq',qqcrm='$qqcrm',ppcrm='$ppcrm',wangwang='$wangwang',tel='$tel'".
            " WHERE admin_id=$admin_id"; 
        if($GLOBALS['db']->query($sql_update))
        {
            $res['code'] = 1;
            $res['message'] = '修改成功';
        }
        else
        {
            $res['message'] = '修改失败';
        }
    }
    else
    {
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('admin_work_account').
            '(admin_id,qq,qqcrm,ppcrm,wangwang,tel)VALUES('.
            "$admin_id,'$qq','$qqcrm','$ppcrm','$wangwang','$tel'".')';

        if($GLOBALS['db']->query($sql_insert))
        {
            $res['code'] = 1;
            $res['message'] = '添加成功';
        }
        else
        {
            $res['message'] = '添加失败';
        }
    }

    die($json->encode($res));
}

//平台账号查询
elseif ($_REQUEST['act'] == 'platform_account')
{
    if(admin_priv('all','',false))
    {
        $smarty->assign('super',1); 
    }

    $res['main'] = $smarty->fetch('sch_platform_account.htm');

    die($json->encode($res));
}

//获取电脑信息
elseif ($_REQUEST['act'] == 'get_pc_info')
{
    $pc_number = mysql_real_escape_string($_REQUEST['pc_number']); 
    $sql_select = 'SELECT s.ip,p.mac,p.case_status,p.monitor_status FROM '
        .$GLOBALS['ecs']->table('office_seat').' AS s LEFT JOIN '
        .$GLOBALS['ecs']->table('pc_manager')
        ." AS p  ON p.pc_case=s.pc_number WHERE s.pc_number='$pc_number'";

    $pc_info = $GLOBALS['db']->getAll($sql_select);
    $pc_info = $pc_info[0];

    $res = array();
    if($pc_info)
    {
        $res['code'] = 1;
        //list($res['pc_ip'],$res['pc_mac'],$res['pc_case'],$res['pc_monitor']) = $pc_info;
        $res['pc_ip'] = $pc_info['ip'];
        $res['pc_mac'] = $pc_info['mac'];
        $res['pc_case_status'] = $pc_info['case_status'];
        $res['pc_monitor_status'] = $pc_info['monitor_status'];
    }
    else
    {
        $res['code'] == 0; 
    }

    die($json->encode($res));

}

// 修改电脑信息
elseif ($_REQUEST['act'] == 'modify_pc_info')
{
    $pc_number = mysql_real_escape_string($_REQUEST['pc_number']);
    $pc_ip = mysql_real_escape_string($_REQUEST['pc_ip']);
    $pc_mac = mysql_real_escape_string($_REQUEST['pc_mac']);
    $pc_case_status = intval($_REQUEST['pc_case']);
    $pc_monitor_status = intval($_REQUEST['pc_monitor']);

    $res = array('req_msg'=>true,'message'=>'');

    if($pc_number != '')
    {
        $sql_str[0] = 'UPDATE '.$GLOBALS['ecs']->table('pc_manager').
            " SET mac='$pc_mac',monitor_status=$pc_monitor_status,case_status=$pc_case_status WHERE pc_case='$pc_number' AND is_use=1";
        $sql_str[1] = 'UPDATE '.$GLOBALS['ecs']->table('office_seat').
            " SET ip='$pc_ip',mac='$pc_mac' WHERE pc_number='$pc_number'";

        for($i = 0; $i < 2; $i++)
        {
            $GLOBALS['db']->query($sql_str[$i]);
        }
        $res['message'] = '添加成功';
    }
    else
    {
        $res['message'] = '添加失败';
    }

    die($json->encode($res));
}

//安排电脑
elseif ($_REQUEST['act'] == 'arrange_pc')
{
    $pc_number = mysql_real_escape_string($_REQUEST['pc_number']);
    $seat = mysql_real_escape_string($_REQUEST['seat']);

    $sql_str[0] = 'UPDATE '.$GLOBALS['ecs']->table('office_seat').
        " SET pc_number='$pc_number' WHERE seat='$seat'";

    $sql_select = 'SELECT COUNT(*) FROM '
        .$GLOBALS['ecs']->table('pc_manager')
        ." WHERE pc_case='$pc_number'";

    //电脑已经存在
    if($GLOBALS['db']->getOne($sql_select))
    {
        $sql_str[1] = 'UPDATE '.$GLOBALS['ecs']->table('pc_manager')
            ." SET is_use=1 WHERE pc_case='$pc_number'";
    }
    else
    {
        $sql_str[1] = 'INSERT INTO'.$GLOBALS  ['ecs']->table('pc_manager').
            '(pc_case,case_status,monitor_status,is_use)VALUES('.
            "'$pc_number',0,0,1)";
    }

    for($i = 0; $i < 2; $i++)
    {
        $GLOBALS['db']->query($sql_str[$i]);
    }

    $res['seat'] = $seat;
    $res['pc_number'] = $pc_number; 

    die($json->encode($res));
}

//查找将要安排的员工是否已经有座位
elseif($_REQUEST['act'] == 'arranged_admin')
{
    $admin_id = intval($_REQUEST['user_id']);
    $seat = mysql_real_escape_string($_REQUEST['seat']);
    $res = array();
    $sql_select = 'SELECT admin_id,admin_name,seat FROM '.$GLOBALS['ecs']->table('office_seat').
        " WHERE admin_id=$admin_id";

    $admin_info = $GLOBALS['db']->getAll($sql_select);

    if($admin_info)
    {
        $res['code'] = 1;
        $res['admin_name'] = $admin_info[0]['admin_name'];
        $res['re_seat'] = $admin_info[0]['seat'];
    }
    else
    {
        $res['code'] = 0;
    }

    $res['seat'] = $seat;
    $res['admin_id'] = $admin_id;

    die($json->encode($res));
}

/* 查询部门下小组 */
elseif ($_REQUEST['act'] == 'group_list') {
    $role_id = intval($_REQUEST['role_id']);
    $sql_select = 'SELECT group_id,group_name FROM '.$GLOBALS['ecs']->table('group')." WHERE role_id=$role_id";
    $group_list = $GLOBALS['db']->getAll($sql_select);

    die($json->encode($group_list));
}

/*查询员工*/
elseif($_REQUEST['act'] == 'admin_list'){
    $group_id  = isset($_REQUEST['group_id']) ? intval($_REQUEST['group_id']) : 0;
    $role_id   = isset($_REQUEST['role_id']) ? intval($_REQUEST['role_id']) : 0;
    $user_name = isset($_REQUEST['user_name']) ? trim(mysql_real_escape_string($_REQUEST['user_name'])) : '';

    $where = ' WHERE status=1 AND freeze=0 ';
    if ($role_id) {
        $where .= " AND role_id=$role_id ";
    }

    if ($user_name) {
        $where .= " AND user_name LIKE '%$user_name%' ";
    }

    $sql_select = 'SELECT user_id,user_name FROM '.$GLOBALS['ecs']->table('admin_user').$where;
    $admin_list = $GLOBALS['db']->getAll($sql_select);

    die($json->encode($admin_list));
}

//通过部门获取员工
elseif ($_REQUEST['act'] == 'get_role_admin')
{
    $res['target'] = explode(',',$_REQUEST['target']);
    $role_id = intval($_REQUEST['role_id']);
    $sql = 'SELECT user_name, user_id ,role_id FROM '.$GLOBALS['ecs']->table('admin_user')
        ." WHERE status>0 AND stats>0 AND role_id=$role_id ORDER BY convert(user_name using gbk) ASC";
    $res['admin'] = $GLOBALS['db']->getAll($sql);
    die($json->encode($res));
}

//发布公告
//notice_type 1 重要通知，2 紧急通知，3 好消息 4 大单贺讯 
elseif($_REQUEST['act'] == 'issue_notice'){
    $notice_type = intval($_REQUEST['notice_type']);
    $title = mysql_real_escape_string($_REQUEST['title']);
    $notice = mysql_real_escape_string($_REQUEST['notice']);
    $weight = intval($_REQUEST['weight']);
    $remind_time = intval($_REQUEST['remind_time']);
    $remind_time = $remind_time == 0 ? time() : time()+24*3600;
    $issue_time = time();

    $role_id = isset($_REQUEST['platform']) ? intval($_REQUEST['platform']) : 0 ;
    $group_id = isset($_REQUEST['group_id']) ? intval($_REQUEST['group_id']): 0 ;
    $admin_id = isset($_REQUEST['admin_id']) ? intval($_REQUEST['admin_id']): 0 ;

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('public_notice')
        .' (weight,notice_type,title,content,writed_id,issue_time,remind_time,role_id,group_id,admin_id)'
        ." VALUES($weight,'$notice_type','$title','$notice',{$_SESSION['admin_id']},$issue_time,$remind_time";

    if(admin_priv('all_mgr','',false) || admin_priv('all')){
        if($admin_id){
            $sql_values = ",$role_id,$group_id,$admin_id)";
        }elseif($group_id){
            $sql_values = ",$role_id,$group_id,0)";
        }elseif($role_id){
            $sql_values = ",$role_id,0,0)";
        }
    }elseif(admin_priv('role_view','',false)){
        if($admin_id){
            $sql_values = ",{$_SESSION['role_id']},$group_id,$admin_id)";
        }elseif($group_id){
            $sql_values = ",{$_SESSION['role_id']},$group_id,0)";
        }
    }elseif(admin_priv('group_view','',false)){
        if($admin_id){
            $sql_values = ",0,0,$admin_id)";
        }
    }

    if($sql_values == ''){
        $sql_values = ',0,0,0)';
    }

    $res = array('req_msg'=>true,'timeout'=>2000,'code'=>false,'message'=>'');
    $res['code'] = $GLOBALS['db']->query($sql_insert.$sql_values);

    if($res['code']){
        $res['message'] = '发布成功';
    }else{
        $res['message'] = '发布失败';
    }

    die($json->encode($res));

}

//获得公告的内容
elseif ($_REQUEST['act'] == 'get_notice'){
    $notice_id = intval($_REQUEST['notice_id']);
    $res = array(
        'req_msg'   =>  true,
        'title'     =>  '',
        'message'   =>  '',
    );

    $sql_select = 'SELECT title,content FROM '.$GLOBALS['ecs']->table('public_notice')." WHERE notice_id=$notice_id";
    $result = $GLOBALS['db']->getRow($sql_select);

    $res['title'] = $result['title'];
    $res['message'] = $result['content'];

    die($json->encode($res));
}

/* 保存员工数据 */
elseif ($_REQUEST['act'] == 'save') {

    $user_id  = intval($_REQUEST['id']);
    $max_cust = intval($_REQUEST['max_customer']);
    $res = array (
        'id'      => $user_id,
        'req_msg' => true,
    );

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_user')." SET max_customer=$max_cust WHERE user_id=$user_id";
    if ($GLOBALS['db']->query($sql_update)) {
        $res['maxCustomer'] = $max_cust;
        $res['message']     = '顾客上限修改成功！';
    } else {
        $sql_select = 'SELECT max_customer FROM '.$GLOBALS['ecs']->table('admin_user')." WHERE user_id=$user_id";
        $max_cust = $GLOBALS['db']->getOne($sql_select);

        $res['maxCustomer'] = $max_cust;
        $res['message']     = '顾客上限修改失败，请重新再试！';
    }

    die($json->encode($res));
}

//收集反馈和意见
elseif('feedback_collect' == $_REQUEST['act']){
    $readed = isset($_REQUEST['readed']) ? intval($_REQUEST['readed']) : 0;

    $sql_select = 'SELECT m.message_id,m.title,m.sent_time,read_time,m.readed,m.message_class,a.user_name FROM '.$GLOBALS['ecs']->table('admin_feedback').
        ' m LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' a ON a.user_id=m.sender_id'.
        " WHERE readed=$readed AND message_class IN(0,1) ORDER BY sent_time DESC";

    $feedback_list = $GLOBALS['db']->getAll($sql_select);

    if($feedback_list){
        foreach($feedback_list as &$val){
            $val['sent_time'] = date('Y-m-d H时',$val['sent_time']);
            $val['read_time'] = empty($val['read_time']) ? '-' : date('Y-m-d H时',$val['read_time']);
            $val['message_class'] = $val['message_class'] ? '体验问题' : 'BUG问题';
        }
    }

    $smarty->assign('feedback_list',$feedback_list);
    $smarty->assign('readed',$readed);
    $res['main'] = $smarty->fetch('feedback_list.htm');
    die($json->encode($res));
}

//更新日志
elseif('change_log' == $_REQUEST['act']){
    $behave = isset($_REQUEST['behave']) ? $_REQUEST['behave'] : 'change_log';

    if('add_change_log' == $behave){
        $title    = isset($_REQUEST['title']) ? mysql_real_escape_string($_REQUEST['title']) : '';
        $log_text = isset($_REQUEST['log_text']) ? mysql_real_escape_string(nl2br($_REQUEST['log_text'])) : '';

        $res = array(
            'req_msg' => true,
            'message' => '',
            'timeout' => 2000,
            'code'    => false,
        );

        if (!empty($title)) {
            $year = date('Y');
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('changelog').
                '(log_text,title,upgrade_time,developer,year)'.
                "VALUES('$log_text','$title',{$_SERVER['REQUEST_TIME']},{$_SESSION['admin_id']},$year)";

            $res['code'] = $GLOBALS['db']->query($sql_insert);
            $res['message'] = $res['code'] ? '提交成功' : '提交失败';
        }
    }

    $developer_arr = array('吴远航','苏鑫');

    if(in_array($_SESSION['admin_name'],$developer_arr)){
        $smarty->assign('developer','developer');
    }

    $sql_select = 'SELECT year FROM '.$GLOBALS['ecs']->table('changelog').
        ' GROUP BY year ORDER BY year DESC';
    $years = $GLOBALS['db']->getCol($sql_select);

    if($years){
        $sql_select = 'SELECT c.title,c.log_text,c.upgrade_time,c.year,a.user_name FROM '.
            $GLOBALS['ecs']->table('changelog').
            ' c LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
            ' a ON c.developer=a.user_id ORDER BY upgrade_time DESC';

        $log_list = $GLOBALS['db']->getAll($sql_select);    

        foreach($years as &$val){
            foreach($log_list as &$log){
                if($val == $log['year']){
                    $log['upgrade_time'] = date('m-d H:i',$log['upgrade_time']);
                    $change_log[$val][] = $log;
                }
            }
        }
    }

    $smarty->assign('developer','developer');
    $smarty->assign('change_log',$change_log);
    $res['main'] = $smarty->fetch('changelog.htm');

    die($json->encode($res));
}

/*查看管理员*/
elseif($_REQUEST['act'] == 'view_admin'){
    if(admin_priv('allot_authority','',true)){

        $role_list  = get_role();
        $admin_list = get_admin('all');
        $mem = new Memcache;
        $mem->connect('127.0.0.1', 11211);
        $admin_view = $mem->get("admin_view");
        if(empty($admin_view)){

            foreach ($role_list as $role) {
                $admin_view[]['role_id'] = $role['role_id'];
                foreach($admin_list as $admin){
                    if($role['role_id'] == $admin['role_id']){
                        $role_admin[$role['role_id']][] = array(
                            'admin_id'  => $admin['user_id'],
                            'user_name' => $admin['user_name']
                        );
                    }
                }
            }

            foreach($admin_view as &$val){
                $val['admin'] = $role_admin[$val['role_id']];
            }
        }

        $mem->add("admin_view", $admin_view, false, 20*3600);
        $mem->close();

        $smarty->assign('role_list',$role_list);
        $smarty->assign('admin_view',$admin_view);
        $res['main'] = $smarty->fetch('admin_view.htm');
        die($json->encode($res));
    }
}

//获取通知消息
//订单弹窗本部门订单弹窗
elseif ($_REQUEST['act']  == 'get_msg_notice'){
    $role = $_SESSION['role_id'];
    $where = ' WHERE status=1';
    if (!admin_priv('all','',false)) {
        $where .= " AND role_id={$_SESSION['role_id']}";
    }else{
        $where .= ' AND notice_type=5';
    }

    if ($_SESSION['role_id']>31 && !admin_priv('all','',false)) {
        $where .= ' AND notice_type IN (4,5)';
    }
    $start_time = strtotime(date('Y-m-d 00:00:00'));
    $end_time = strtotime(date('Y-m-d 23:59:59'));
    $where .= " AND remind_time BETWEEN $start_time AND $end_time";
    $sql = "SELECT content,title,notice_id FROM "
        .$GLOBALS['ecs']->table('public_notice')." $where ORDER BY remind_time DESC";
    $data = $GLOBALS['db']->getAll($sql);
    if($_SESSION['admin_id'] == 139){
        $data = array();
    } 
    $res['length'] = count($data);
    $res['msg'] = $data;
    $res['remind_title'] = '特大好消息';
    die($json->encode($res));
}
elseif($_REQUEST['act'] == 'contact_status'){
    $sql = 'SELECT status FROM '.$GLOBALS['ecs']->table('contact_status')." LIMIT 1";
    $smarty->assign('status',$GLOBALS['db']->getOne($sql));
    $res['main'] = $smarty->fetch('contact_status.htm');
    die($json->encode($res)); 
}
elseif($_REQUEST['act'] == 'set_contact_status'){
    $res = array(
        'req_msg' => true,
        'message' => '修改失败',
        'timeout' => 2000,
        'code'    => false,
    ); 
    $sql = 'UPDATE '.$GLOBALS['ecs']->table('contact_status').' SET status=IF(status=1,0,1) WHERE id=1'; 
    $res['code'] = $GLOBALS['db']->query($sql);
    if ($res['code']) {
        $res['message'] = '修改成功';
    }
    die($json->encode($res));
}

/*
 *  函数部分 
 */

/* 获取角色列表 */
function get_role_lists()
{
    $list = array();
    if (admin_priv('all', '', false)) {
    } elseif (admin_priv('admin_trans-part_view', '', false)) {
        $role_list = trans_part_list();
        $role_list = implode(',', $role_list);
        $where = " WHERE role_id IN ($role_list) ";
    } else {
        $where = " WHERE role_id={$_SESSION['role_id']} ";
    }

    $sql  = 'SELECT role_id,role_name,action_list,role_describe,role_describe,manager,role_type,compete FROM '.
        $GLOBALS['ecs']->table('role')." $where ORDER BY role_id DESC";
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

function get_action_list()
{
    $sql_query = "SELECT * FROM " .$GLOBALS['ecs']->table('admin_action').
        " WHERE parent_id = 0";
    $res = $GLOBALS['db']->query($sql_query);

    while ($rows = $GLOBALS['db']->FetchRow($res))
    {
        $priv_arr[$rows['action_id']] = $rows;
    }

    /* 按权限组查询底级的权限名称 */
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('admin_action').
        " WHERE parent_id " .db_create_in(array_keys($priv_arr));
    $result = $GLOBALS['db']->query($sql);

    while ($priv = $GLOBALS['db']->FetchRow($result))
    {
        $priv_arr[$priv["parent_id"]]["priv"][$priv["action_code"]] = $priv;
    }

    // 将同一组的权限使用 "," 连接起来，供JS全选
    foreach ($priv_arr AS $action_id => $action_group)
    {
        $priv_arr[$action_id]['priv_list'] = join(',', @array_keys($action_group['priv']));

        foreach ($action_group['priv'] AS $key => $val)
        {
            $priv_arr[$action_id]['priv'][$key]['cando'] = (strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all') ? 1 : 0;
        }
    }
    return $priv_arr;
}

//获取本地电脑信息 ip 
function get_client_ip()
{
    // IP
    if(getenv('HTTP_CLIENT_IP'))
    {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } 
    elseif(getenv('HTTP_X_FORWARDED_FOR'))
    {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif(getenv('REMOTE_ADDR'))
    {
        $onlineip = getenv('REMOTE_ADDR');
    }
    else
    {
        $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
    }

    $macaddress = exec ("arp -a | find /i".' "'.$onlineip.'"'); //mac
    $computername = exec ('nbtstat -A'.' '.$onlineip.' | find /i "20"'); //pc name
    $macname = (preg_split("/\s+/", trim($macaddress)));
    $username=(preg_split("/\s+/", trim($computername)));

    $pc_net_info = array('ip'=>$onlineip,'mac'=>$macaddress,'computername'=>$computername,'user_name'=>$username);

    return $pc_net_info;
}
