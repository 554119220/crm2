<?php
define('IN_ECS', true);
require(dirname(__FILE__).'/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

/*定时修改积分规则可用性*/ 

/*-- 服务子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

//办公电脑管理
elseif ($_REQUEST['act'] == 'pc_manager')
{
    /*
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
     */

    //$res = array('req_msg'=>true,'message'=>'','timeout'=>2000);

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
                " WHERE s.room='{$room_info[$i]['room']}' ORDER BY seat ASC";

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
        //$res['message'] = '';
    }

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_type');
    $account_type = $GLOBALS['db']->getAll($sql_select);
    $account_info = array();
    $total = count($account_type);

    $account_type_list = array('qq','ppcrm','qqcrm','wangwang');
    for($i=0; $i<count($account_type_list); $i++)
    {
        $sql_select = 'SELECT account_name FROM '.$GLOBALS['ecs']->table('account').
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

?>
