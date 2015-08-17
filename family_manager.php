<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');

if($_REQUEST['act'] == 'get_family_healthy')
{
    $user_id = intval($_REQUEST['user_id']);
    $sql_select = 'SELECT user_name FROM '.$GLOBALS['ecs']->table('users')." WHERE user_id=$user_id";
    $user_name = $GLOBALS['db']->getOne($sql_select);

    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0) {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    } else {
        $filter['page_size'] = 13; 
    }

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('weight_condition')." WHERE user_id=$user_id";
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
        'act'           => 'get_family_healthy',
    );

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('weight_condition')." WHERE user_id=$user_id ORDER BY update_time DESC"
        .' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";
    $healthy_info = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('healthy_info',$healthy_info);
    $smarty->assign('user_name',$user_name);
    $smarty->assign('filter',$filter);
    $res['info'] = $smarty->fetch('family_user_healthy.htm');

    die($json->encode($res));
}

//移出该家庭
elseif($_REQUEST['act'] == 'del_family_user')
{
    $res     = array('req_msg'=>true,'timeout'=>2000,'message'=>'','code'=>0);
    $user_id = intval($_REQUEST['user_id']);

    $res = array(
        'code'    => false,
        'message' => '',
        'timeout' => 2000
    );

    
    $sql_del = 'UPDATE '.$GLOBALS['ecs']->table('user_family_member')." SET status=1 WHERE user_id='$user_id'";

    $res['code'] = $GLOBALS['db']->query($sql_del);

    if($res['code'])
        $res['message'] = '移出成功';
    else
        $res['message'] = '暂时将他不能移出该家庭';

    die($json->encode($res));

}

//加入家庭
elseif ($_REQUEST['act'] == 'add_to_family')
{
    $family_id      = intval($_REQUEST['family_id']);
    $user_id        = intval($_REQUEST['user_id']);
    $family_name    = mysql_real_escape_string($_REQUEST['family_name']);
    $grade          = intval($_REQUEST['grade']);
    $input_time     = $_SERVER['REQUEST_TIME'];
    $res['code']    = 0;
    $res['user_id'] = $user_id;

    //已经有家庭成员
    if($family_id != 0) {
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_family').
            " WHERE family_id=$family_id";
        $result = $GLOBALS['db']->getOne($sql_select);

        if($result) {
            $sql_select = 'SELECT user_id,grade_id FROM '.$GLOBALS['ecs']->table('user_family_member')." WHERE user_id=$user_id";
            $friends_list = $GLOBALS['db']->getRow($sql_select);

            /*没有加入家庭*/
            if(!$friends_list) {
                $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_family_member')
                    ." WHERE grade_id=$grade AND grade_id<>101";

                if(!$GLOBALS['db']->getOne($sql_select)) {
                    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_family_member')
                        .'(user_id,family_id,grade_id,input_time,add_admin)VALUES('.
                        "$user_id,$family_id,$grade,$input_time,{$_SESSION['admin_id']})";;
                    $result = $GLOBALS['db']->query($sql_insert);
                    if($result) {
                        $res['code'] = 1;
                    }
                } else {
                    $res['code'] = 3;   //已经存在该辈分的成员
                }
            } else {
                //从朋友圈中转入家庭
                if($friends_list['grade_id'] == 101){
                    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('user_family_member').
                        " SET grade_id=$grade,family_id=$family_id WHERE user_id=$user_id";
                    $res['code'] = $GLOBALS['db']->query($sql_update);
                }else{
                    $res['code'] = 2;       //已经加入该家庭
                }
            }
        }else {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_family').
                '(family_id,family_name,input_time,add_admin)VALUES('.
                "$family_id,'$family_name',$input_time,{$_SESSION['admin_id']})";

            $result = $GLOBALS['db']->query($sql_insert);

            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')
                ." SET family_id=$family_id WHERE user_id=$family_id";

            $GLOBALS['db']->query($sql_update);

            if($result) {
                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_family_member')
                    .'(user_id,family_id,grade_id,input_time,add_admin)VALUES('
                    ."$family_id,$family_id,99,$input_time,{$_SESSION['admin_id']}),"
                    ."($user_id,$family_id,$grade,$input_time,{$_SESSION['admin_id']})";

                $result = $GLOBALS['db']->query($sql_insert);
                if($result) {
                    $res['code'] = 1;
                }
            }
        }

        if($res['code'] == 1) {
            $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')
                ." SET family_id=$family_id WHERE user_id=$user_id";

            $GLOBALS['db']->query($sql_update);
        }
    }

    die($json->encode($res));
}

//添加新体检模板
elseif ($_REQUEST['act'] == 'add_new_test')
{
    $user_id = intval($_REQUEST['user_id']);

    $sql_select = 'SELECT * FROM ' .$GLOBALS['ecs']->table('examination');
    $examination = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('user_id',$user_id);
    $smarty->assign('examination',$examination);

    $res['main'] = $smarty->fetch('add_new_test.htm');

    die($json->encode($res));
}

//家长操作
elseif ($_REQUEST['act'] == 'operation_parent')
{
    $user_id    = intval($_REQUEST['user_id']);
    $grade_name = mysql_real_escape_string($_REQUEST['grade_name']);
    $operater   = mysql_real_escape_string($_REQUEST['operater']);

    $res = array(
        'req_msg'    => true,
        'message'    => '',
        'code'       => 0,
        'timeout'    => 2000,
        'user_id'    => $user_id,
        'grade_name' => $grade_name,
        'operater'   => $operater
    );

    $sql_query = 'UPDATE '.$GLOBALS['ecs']->table('user_family_member');

    if($operater == 'del') {
        $sql_query .= " SET real_parent=0 WHERE user_id=$user_id";
    } else if($operater == 'upd') {
        $sql_select = 'SELECT family_id FROM '.$GLOBALS['ecs']->table('user_family_member')
            ." WHERE user_id=$user_id";

        $family_id  = $GLOBALS['db']->getOne($sql_select);
        $sql_select = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('user_family_member')
            ." WHERE family_id=$family_id AND real_parent=1";

        if($GLOBALS['db']->getOne($sql_select) >= 2) {
            $res['message'] = '家长不能超过两个人';
            die($json->encode($res));
        } else
            $sql_query .= " SET real_parent=1 WHERE user_id=$user_id";
    }

    $result = $GLOBALS['db']->query($sql_query);

    if($result) {
        $res['code']    = 1;
        $res['message'] = '修改成功';
    } else
        $res['message'] = '修改失败';

    die($json->encode($res));
}

//添加新的体检项目
else if($_REQUEST['act'] == 'add_examination')
{
    $res['examination_name'] = $examination_name = trim(mysql_real_escape_string($_REQUEST['examination_name']));
    $res['units'] = $units = trim(mysql_real_escape_string($_REQUEST['units']));
    $res['descript'] = $descript = trim(mysql_real_escape_string($_REQUEST['descript']));

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('examination')
        ." WHERE examination_name='$examination_name'";
    $count = $GLOBALS['db']->getOne($sql_select);
    if($count)
        $res['code'] = 0;
    else
    {
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('examination')
            .'(examination_name,units,descript,rule_id,input_time)VALUES('
            ."'$examination_name','$units','$descript',0)";

        $res['code'] = $GLOBALS['db']->query($sql_insert);
        $res['examination_id'] = $GLOBALS['db']->insert_id();
    }

    die($json->encode($res));
}

//修改主要体检信息
elseif ($_REQUEST['act'] == 'modify_weight')
{
    $healthy_info_file = stripslashes($_REQUEST['JSON']);
    $healthy_info_file = json_decode($healthy_info_file,true);
    $healthy_info_file = addslashes_deep($healthy_info_file);

    $admin_remark = mysql_real_escape_string($healthy_info_file['admin_remark']);
    array_pop($healthy_info_file);
    $user_id = intval($healthy_info_file['user_id']);
    array_pop($healthy_info_file);

    $res['update_time'] = $update_time = date('Y-m-d');
    $res['req_msg']     = true;
    $res['time_out']    = '2000';
    $res['message']     = '';

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('other_examination_test').
        " WHERE FROM_UNIXTIME(input_time,'%Y-%m-%d')='$update_time' AND user_id=$user_id";

    if($GLOBALS['db']->getOne($sql_select)) {
        $res['code'] = false;
        $res['message'] = '今天已经测试，并保存了记录，无需重复添加';
    } else {
        //其它的体检项目
        $input_time = time();
        $admin_id = $_SESSION['admin_id'];
        foreach($healthy_info_file AS $key=>$val)
        {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('other_examination_test')
                .'(user_id,examination_name,examination_value,input_time,admin_id)VALUES('
                ."$user_id,'$key','$val',$input_time,$admin_id".')';

            $GLOBALS['db']->query($sql_insert);
        }
        $res['code'] = true;

        $sql_select = 'SELECT e.examination_name FROM '.$GLOBALS['ecs']->table('examination').
            ' AS e LEFT JOIN '.$GLOBALS['ecs']->table('other_examination_test').
            ' AS et ON et.examination_name=e.descript '.
            " WHERE FROM_UNIXTIME(input_time,'%Y-%m-%d')='$update_time' AND et.user_id=$user_id";

        $examination['keys'] = $GLOBALS['db']->getCol($sql_select);
        $examination['values'] = array_values($healthy_info_file);

        $res['examination'] = $examination;
    }

    if($res['code']) {
        $res['message'] = '添加成功';
    } elseif($res['message'] == '')
        $res['message'] = '添加失败';

    die($json->encode($res));
}

//获取以往记录
elseif ($_REQUEST['act'] == 'get_history_healthy')
{
    $user_id = intval($_REQUEST['user_id']);
    $examination_id = intval($_REQUEST['examination']);

    $sql_select = 'SELECT examination_name,descript FROM '.$GLOBALS['ecs']->table('examination')
        ." WHERE examination_id=$examination_id";
    $examination_info = $GLOBALS['db']->getRow($sql_select);

    $sql_select = "SELECT FROM_UNIXTIME(e.input_time,'%Y-%m-%d') AS input_time,e.examination_value,a.user_name AS admin_name FROM "
        .$GLOBALS['ecs']->table('other_examination_test')
        .' AS e LEFT JOIN '.$GLOBALS['ecs']->table('admin_user')
        .' AS a ON e.admin_id=a.user_id '
        ." WHERE e.user_id=$user_id AND e.examination_name='{$examination_info['descript']}'"
        .' ORDER BY e.input_time DESC';

    $history_examination = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('history_examination',$history_examination);
    $smarty->assign('examination_info',$examination_info);
    $smarty->assign('user_name',$_REQUEST['user_name']);

    $res['info'] = $smarty->fetch('history_examination.htm');

    die($json->encode($res));
}
