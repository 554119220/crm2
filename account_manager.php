<?php 
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
date_default_timezone_set('Asia/Shanghai');
$act = trim(mysql_real_escape_string($_REQUEST['act']));

/*-- 账号管理子菜单 --*/
if ($_REQUEST['act'] == 'menu')
{
    $file = strstr(basename($_SERVER['PHP_SELF']), '.', true);
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);

    die($smarty->fetch('left.htm'));
}

/*
 * 所有账号
 */
elseif ($_REQUEST['act'] == 'accounts')
{
    admin_priv('accounts');

    $res = array ();
    $file = basename($_SERVER['PHP_SELF'], '.php');
    $nav = list_nav();
    $smarty->assign('nav_2nd', $nav[1][$file]);
    $smarty->assign('nav_3rd', $nav[2]);
    $smarty->assign('file_name', $file);
    $res['left'] = $smarty->fetch('left.htm');

    $admin_id = $_SESSION['role_id'];
    //默认显示QQ空间
    $sql_condition = ' WHERE type_id=1 AND is_check=0';   //默认查询条件为类型为1(QQ),已经核检0(is_check)
    $department_id = $_SESSION['role_id'];

    //记录访问者的部门
    if($department_id == NULL || $department_id == 5)
    {
        $_SESSION['department'] = '超级管理员';
    }
    else
    {
        $department = department_get($department_id); 
        $_SESSION['department'] = $department[0]['role_name'];
    }

    if($department_id != NULL && $department_id != 5)     //访问用户所在部门 5为主管
    {
        $sql_condition .= " AND department_id=$department_id";
    }

    $result= accounts_get($sql_condition);             //所有账号信息 
    $account_type = type_get();                           //帐号类型
    $accounts = $result['account_list'];


    $smarty->assign('department',$_SESSION['department']);
    $smarty->assign('admin_name',$_SESSION['admin_name']);
    $smarty->assign('department_id',$department_id);
    $smarty->assign('admin_id',$admin_id);
    $smarty->assign('account_type',$account_type);
    $smarty->assign('accounts',$accounts);
    $smarty->assign('filter',$filter);

    $res['main'] = $smarty->fetch('accounts_list.htm');
    die($json->encode($res));
}

/*
 * 帐号按类型(type_id)分组 
 */
elseif ($_REQUEST['act'] == 'group_accounts')
{
    $type_id = intval($_GET['type_id']);
    $account_type = type_get();     //帐号类型 

    $sql_condition = " WHERE type_id=$type_id".
        ' AND is_check=0 ORDER BY content_updatetime Desc';     //按帐号内容(content_updatetime)排序

    $result = accounts_get($sql_condition);
    $accounts = $result['account_list'];

    if($_SESSION['role_id'] == NULL OR $_SESSION['role_id'] == 5)
    {
        $department = '超级管理员';
    }
    else
    {
        $department = departmentd_get($department_id);
        $department = $departmentd[0]['role_name']; 
    }

    $smarty->assign('admin_name',$_SESSION['admin_name']);
    $smarty->assign('admin_id',$_SESSION['role_id']);
    $smarty->assign('department',$department);
    $smarty->assign('accounts', $accounts);
    $smarty->assign('account_type',$account_type);

    $res['main'] = $smarty->fetch('accounts_list.htm');
    die($json->encode($res));
}

/*
 * 添加账号
 * 返回添加主界面
 */
elseif ($_REQUEST['act'] == 'addaccount')
{
    $type = type_get();                 //获取帐号类型
    $department = department_get();     //获取部门
    $passwordprotect = get_passwordprotect();       //密码保护
    $user =user_get();                  //帐号的使用者
    $admin = $_SESSION['admin_name'];     //提交者姓名,用户表最前用户 

    $smarty->assign('adminid',$user[0]['user_id']);
    $smarty->assign('admin',$admin);

    $smarty->assign('user',$user);
    $smarty->assign('department',$department);
    $smarty->assign('type',$type);
    $smarty->assign('passwordprotect',$passwordprotect);

    $res['main'] = $smarty->fetch('add_account.htm'); 
    die($json->encode($res)); 
}

// 将要添加帐号添入数据库
elseif ($_REQUEST['act'] == 'insert_account')
{
    $user_name = trim($_POST['user_name']); //帐号名称
    $password = trim($_POST['password']);         //密码
    $type_id = intval($_POST['type_id']);   //帐号类型
    $email = trim($_POST['email']);
    $department_id = intval($_POST['department']);  //所属部门
    $user = $_POST['user'];                 //使用者
    $url = trim($_POST['url']);             //帐号登陆地址
    $usual_update = intval($_POST['usual_update']); //是否经常更新(是 1,不是 0);
    $handin = $_SESSION['admin_id'];   //提交者id
    $inputTime = strtotime(date('Y-m-d H:i')) -8*3600;  //提交时间
    $subject = $_POST['subject'];           //主题
    $passwordProtectID = intval($_POST['passwordProtect_id']);   //密码保护</p>

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account')
        .'(user_name,password,type_id,email,department_id,user,url,usual_update,handin,inputTime,subject,passwordProtect_id)'
        ." VALUES('$user_name','$password',$type_id,'$email',$department_id,$user,'$url',$usual_update,$handin,'$inputTime','$subject',$passwordProtectID)";

    $result = $GLOBALS['db']->query($sql_insert);

    echo $result;
}

/*
 *账号删除
 */
elseif ($_REQUEST['act'] == 'del_account')
{
    $account_id = intval($_REQUEST['account_id']);
    $res['row_id'] = intval($_REQUEST['row_id']);
    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account')." WHERE account_id=$account_id"; 

    $res['req_msg'] = true;
    $res['timeout'] = '2000';
    $result= $GLOBALS['db']->query($sql_del);
    if($result)
    {
        $res['code'] = 1;       //执行成功
        $res['message'] = '删除成功';
    }
    else
    {
        $res['code'] = 0;   //执行失败
        $res['message'] = '删除失败';
    }

    die($json->encode($res));
}

/*
 *账号修改
 *返回修改界面
 */
elseif ($_REQUEST['act'] == 'updateAccount')
{
    $account_id = $_GET['account_id'];

    $sql_select = 'SELECT a.account_id,a.account_name AS account_name,a.email,u.user_name,a.password,p.passwordProtect,t.type_name,a.subject,a.url,d.role_name,a.content_updatetime,handin,is_check FROM '.
        $GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type').
        ' AS t ON a.type_id=t.type_id  LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' AS d ON a.department_id =d.role_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('account_passwordprotect').' AS p ON a.passwordProtect_id=p.passworProtect_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS u ON a.user=u.user_id'.
        " WHERE a.account_id=$account_id";

    $account_info = $GLOBALS['db']->getAll($sql_select);    //获取将要修改的帐号信息 
    $account_info = $account_info[0];

    $type = type_get();     //类型
    $user =user_get();      //使用者
    $department = department_get();     //部门 
    $passwordprotect = get_passwordprotect();       //密码保护

    $smarty->assign('account_id',$account_id);
    $smarty->assign('user',$user);
    $smarty->assign('account_info',$account_info);
    $smarty->assign('department',$department);
    $smarty->assign('type',$type);
    $smarty->assign('passwordprotect',$passwordprotect);

    $res['main'] = $smarty->fetch('account_modify.htm');
    die($json->encode($res)); 
}

/*
 * 修改帐号行为
 * 管理员权限
 */
elseif ($_REQUEST['act'] == 'update_account')
{
    $account_id = intval($_POST['account_id']);
    $account_name = trim($_POST['account_name']);                     //帐号使用者姓名
    $password = trim($_POST['password']);   //密码
    $type_id = intval($_POST['type_id']);                       //类型
    $email = trim($_POST['email']);
    $department_id = intval($_POST['department']);              //部门
    $user_id = intval($_POST['user']);                             //帐号使用者id
    $url = trim($_POST['url']);                                 //登陆地址
    $usual_update = intval($_POST['usual_update']);             //是否经常更新
    $handin = intval($_POST['admin_id']);                       //提交者id
    $subject = $_POST['subject'];                               //主题
    $passwordProtectID = intval($_POST['PassWordprotectID']);   //密保id
    $account_updatetime = strtotime(date('Y-m-d H:i')) -8*3600;             //帐号修改时间 
    $belong = trim($_REQUEST['belong_list']);              //授权查看者
    $is_vip = intval($_REQUEST['is_vip']);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
        " SET account_name='$account_name',password='$password',type_id=$type_id,email='$email',department_id=$department_id,user_id=$user_id,url='$url',usual_update=$usual_update,updater=$handin,subject='$subject',password_protect_id=$passwordProtectID,account_updatetime=$account_updatetime,belong='$belong',is_vip=$is_vip WHERE account_id=$account_id"; 

    $result = $GLOBALS['db']->query($sql_update); 
    $res = array('req_msg'=>true,'timeout'=>2000,'message'=>'','code'=>0);


    if($result)
    {
        $res['code'] = 1;
        $res['message'] = '修改成功';
    }
    else
    {
        $res['message'] = '修改失败';
    }

    die($json->encode($res));
}

/*
 *帐号搜索
 */
elseif ($_REQUEST['act'] == 'account_search')
{
    $type_id = intval($_REQUEST['type_id']);        //帐号类型
    $subject = intval($_REQUEST['subject']);        //帐号主题
    $account_name = trim(mysql_real_escape_string($_REQUEST['account_name']));    //使用者名称
    $status = intval($_REQUEST['status']);          //帐号的状态 (有效0 被禁1 被删2 密码错误3 被盗4)
    $user_id = intval($_REQUEST['admin_id']);
    $sql_condition = ' WHERE a.usable IN('.$status.')'; 
    $condition = '';

    /*获取查询条件*/
    if($type_id != "")
    {
        $sql_condition .= " AND a.type_id=$type_id";
        $condition .= "&type_id=$type_id";
    }

    if($account_name != "")
    {
        $sql_condition .= " AND a.account_name LIKE '%$account_name%'";
        $condition .= "&account_name=$account_name";
    }

    if($user_id != 0)
    {
        $sql_condition .= " AND a.user_id=$user_id";
        $condition .= "&admin_id=$user_id";
    }

    $accounts_res = accounts_get($sql_condition);

    $filter = $accounts_res['filter'];
    $accounts = $accounts_res['account_list'];
    $account_type = type_get();

    $accounts_list = array();
    foreach($accounts AS &$val)
    {
        if($val['belong'] != '')
        {
            $belong = explode("','",$val['belong']); 
            array_pop($belong);
            if(!in_array($_SESSION['admin_id'],$belong))
            {
                if(!admin_priv('all','',false))
                    $val['password'] = '-';
            }
            $sql_select = 'SELECT user_name FROM '
                .$GLOBALS['ecs']->table('admin_user')
                ." WHERE user_id IN('".$val['belong']."')";

            $belong = $GLOBALS['db']->getCol($sql_select);
            $val['belong'] = implode(' | ',$belong);    
        }

        if($val['user_id'] == $_SESSION['admin_id'])
        {
            $accounts_list[] = $val;
        }
        else
        {
            if($val['belong'] != '')
            {
                if(in_array($_SESSION['admin_id'],$belong)) 
                {
                    $accounts_list[] = $val;
                }
            }
        }
    } 

    if(!admin_priv('all','',false))
    {
        $accounts = $accounts_list;
        $smarty->assign('department_id',1);
    }

    $smarty->assign('account_type',$account_type);
    $smarty->assign('accounts',$accounts);
    $smarty->assign('filter',$filter);
    $smarty->assign('condition',$condition);

    $res['main'] = $smarty->fetch('account_searchList.htm');
    $res['code'] = 1;
    $res['response_action'] = 'search_service';
    die($json->encode($res));
}

/*
 *核检
 */
elseif ($_REQUEST['act'] == 'checked')
{
    $account_id = intval($_GET['account_id']);
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account')." SET is_check = 1 WHERE account_id=$account_id";

    $result = $GLOBALS['db']->query($sql_update);
}

/*
 * score 0未评分 很差1 差2 合格3 好4 优秀5 
 * 帐号评分
 */
elseif ($_REQUEST['act'] == 'give_score')
{
    admin_priv('give_score');

    $score = intval($_GET['score']);                //帐号分数
    $get_scored_id = intval($_GET['user']);         //被评分人id
    $account_id = intval($_GET['account_id']);      //被评分帐号
    $account_updatetime = strtotime(date('Y-m-d H:i')) -8*3600; //帐号修改时间
    $scored_id = $_SESSION['admin_id'];                                 //评分人默认为部门负责人(登录者) $_SESSION['admin_id']

    //插入评分记录
    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account_score').
        '(account_id,score,scored_id,get_scored_id,score_time)'.
        " VALUES($account_id,$score,$scored_id,$get_scored_id,$account_updatetime)";

    $insResult = $GLOBALS['db']->query($sql_insert);

    //记录当前得分为最近得分
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account')." SET prescore=$score".
        " WHERE account_id=$account_id";

    $result = $GLOBALS['db']->query($sql_update);

    //计算总得分
    $sql_select = 'SELECT SUM(score) AS total_score FROM '.$GLOBALS['ecs']->table('account_score').
        " WHERE account_id=$account_id";
    $total_score = $GLOBALS['db']->getAll($sql_select);

    //修改总得分
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account')."SET score={$total_score[0]['total_score']}".
        " WHERE account_id=$account_id";
    $result = $GLOBALS['db']->query($sql_update);

    if($insResult > 0)
    {
        //评分时间
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
            " SET account_updatetime=$account_updatetime WHERE account_id=$account_id";
        $updResult = $GLOBALS['db']->query($sql_update);
        if($updResult > 0)
        {
            $res['code'] = "评分成功";
            die($json->encode($res));
        }
    }
    else
    {
        $res['code'] = "评分失败";
        die($json->encode($res));
    }

}


/*
 * score为0值表示未评分
 * 检查评分
 */
elseif ($_REQUEST['act'] == 'account_score')
{
    //登录者所属部门
    $department_id = $_SESSION['role_id'];


    $employees = get_employees($department_id);     //部门员工列表
    $user_id = $employees[0]["user"];               //第一个员工id
    $department = department_get($department_id);

    //获取每个部门第一个员工ID 并默认显示
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account');
    if($department_id != "")
    {
        $sql_select .= " WHERE department_id=$department_id";
    }
    $result = $GLOBALS['db']->getAll($sql_select); 

    $smarty->assign('employees',$employees);
    $smarty->assign('department',$department);
    $smarty->assign('accounts',$department_account);
    if($department_id == "")
    {
        $admin_name = '超级管理员';
    }
    $smarty->assign('admin_name',$admin_name);

    $res['main'] = $smarty->fetch('account_score.htm');
    die($json->encode($res));
}

/*
 *  帐号评分查看
 *  查看指定员工的帐号评分信息
 */
elseif ($_REQUEST['act'] == 'get_score')
{   
    $user_id = $_GET['user_id'];    // 帐号使用者
    $department_id = $_SESSION['role_id'];
    if($department_id == "")
    {
        $admin_name = "超级管理员";
    }
    $employees = get_employees($department_id);  //  指部门登陆本系统者所属部门id

    $sql_select = 'SELECT a.user,a.account_id,a.account_name,t.type_name,a.subject, a.url,FROM_UNIXTIME(a.account_updatetime,"%Y年%m月%d日%H时") AS account_updatetime,a.prescore,a.score FROM '
        . $GLOBALS['ecs']->table('account')
        .' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type')
        .'AS t ON a.type_id=t.type_id'
        ." WHERE a.user=$user_id";
    $accounts = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('accounts',$accounts);
    $smarty->assign('admin_name',$admin_name);
    $smarty->assign('employees',$employees);    //部门员工

    $res['main'] = $smarty->fetch('account_score.htm');
    die($json->encode($res));
}

/*
 *  帐号评分明细
 */
elseif ($_REQUEST['act'] == 'score_info')
{
    $account_id = $_GET['account_id'];      //需要查看的帐号id    
    $sql_select = 'SELECT u.user_name AS admin_name,a.user_name FROM '.$GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.user=u.user_id '.
        " WHERE a.account_id=$account_id "; //帐号管理员与用户名
    $info = $GLOBALS['db']->getAll($sql_select);

    //查询评分情况详细信息
    $sql_select = 'SELECT a.subject,FROM_UNIXTIME(s.score_time,"%Y年%m月%d日%H时") AS score_time,u.user_name AS give_score,s.score,s.explain FROM '
        .$GLOBALS['ecs']->table('account_score')
        .' AS s LEFT JOIN '.$GLOBALS['ecs']->table('admin_user')
        .' AS u ON s.scored_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('account')
        .' AS a ON s.account_id=a.account_id'
        ." WHERE s.account_id=$account_id ORDER BY s.score_time DESC";
    $score_info = $GLOBALS['db']->getAll($sql_select);

    //统计差评与好评次数
    $sql_select = 'SELECT score,COUNT(score) AS score_total,account_id FROM '.$GLOBALS['ecs']->table('account_score').
        " WHERE account_id=$account_id GROUP BY score";
    /*
    $sql_select = 'SELECT [1] AS much_fail, [5] AS excellent FROM(SELECT score FROM '.
        $GLOBALS['ecs']->table('account_score').
        ')piv Pivot( COUNT(score) FOR score IN(1,5)) AS scoredetail '
        ." WHERE accont_id=$account_id";
     */

    $score = array('much_fail'=>0,'excellent'=>0);
    $score_total = $GLOBALS['db']->getAll($sql_select);
    foreach($score_total AS $key=>$val)
    {
        if($val['score'] < 3)
        {
            $score['much_fail'] +=  $val['score_total']; 
        }
        elseif($val['score'] > 3)
        {
            $score['excellent'] += $val['score_total'];
        }
    }

    $smarty->assign('score',$score);
    $smarty->assign('info',$info);
    $smarty->assign('score_info',$score_info); 

    $res['main'] = $smarty->fetch('score_info.htm');
    die($json->encode($res));
}

/*
 * 添加新的帐号类型
 */
elseif ($_REQUEST['act'] == 'add_newtype')
{
    $type_name = mysql_real_escape_string($_GET['type_name']);      //新类型的名称

    //如果需要添加,判断新类型是否存在
    if($type_name !='')
    {
        $sql_select = 'SELECT count(*) AS exist FROM '.$GLOBALS['ecs']->table('account_type')." WHERE type_name = '$type_name'";

        $result = $GLOBALS['db']->getAll($sql_select);

        if($result[0]['exist'] != 0)       //帐号已经存在
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }
    else
    {

    }
}

/*
 * Q群管理
 * 返回Q群管理界面
 */
elseif ($_REQUEST['act'] == 'currentGroup')
{

    $account_qq = qq_get();             //获取 QQ号
    $qqgroupList= qqGroup_get($account_qq[0]['user_name']);     //获取Q群列表

    $smarty->assign('qq_group',$qqgroupList);
    $smarty->assign('account_qq',$account_qq);


    $res['main'] = $smarty->fetch('account_qqgroup.htm');
    die($json->encode($res));
}

//获取Q群信息
elseif ($_REQUEST['act'] == 'getQqGroup')
{
    $account_qq = qq_get();
    $qq = intval($_GET['qq']);

    $qqgroup = qqGroup_get($qq);        //指定qq下的Q群
    $account_qq = qq_get();

    $smarty->assign('account_qq',$account_qq);
    $smarty->assign('qq_group',$qqgroup);

    $res['main'] = $smarty->fetch('account_qqgroup.htm');
    die($json->encode($res));
}

/*
 * 添加Q群
 * 返回添加界面
 */
elseif ($_REQUEST['act'] == 'add_qqgroup')
{
    $account_qq = qq_get();
    $department = department_get();     //获取部门
    $user_name = user_get();            //Q群使用者

    $smarty->assign('account_qq',$account_qq); 
    $smarty->assign('department',$department);

    $res['main'] = $smarty->fetch('add_qqqroup.htm');
    die($json->encode($res));
}

//执行Q群添加
elseif ($_REQUEST['act'] == 'insert_qqgroup')
{
    $qq = mysql_real_escape_string($_REQUEST['qq']);                //所属QQ
    $qqgroup = mysql_real_escape_string($_REQUEST['qqgroup']);      //Q群帐号
    $subject = mysql_real_escape_string($_REQUEST['subject']);      //主题
    $strAdmin = mysql_real_escape_string($_REQUEST['strAdmin']);    //Q管理员列表
    $department_id = intval($_REQUEST['departmentID']);             //部门
    $updateTime = strtotime(date('Y-m-d H:i')) -8*3600;             //更新时间

    $arrAdmin = explode(",",$strAdmin);     
    array_unique($arrAdmin);

    $qqgroup_values = "";           //存放Q群
    $groupadmin_values = "";        //存放Q群管理员
    $total = count($arrAdmin);

    $sql_insert_groupadmin = 'INSERT INTO '.$GLOBALS['ecs']->table('account_groupadmin').
        '(admin_id,department_id,type_id,qqgroup) VALUES';

    for($i = 0; $i < $total-1; $i++)        //遍历管理员并转成字符串格式 (待优化,可用strsub())
    {
        if($i == $total-2)
        {
            $groupadmin_values .= "($arrAdmin[$i],$department_id,1,$qqgroup)";
        }
        else
        {
            $groupadmin_values .= "($arrAdmin[$i],$department_id,1,$qqgroup),"; 
        }
    }
    $groupadmin_values = substr($groupadmin_values,0,strlen($groupadmin_values)-1);
    $sql_insert_groupadmin .= $groupadmin_values; 
    $result = $GLOBALS['db']->query($sql_insert_groupadmin);

    //添加Q群
    $qqgroup_values = 'INSERT INTO '.$GLOBALS['ecs']->table('account_qqgroup').
        '(qq,qqgroup,subject,department_id,updateTime) VALUES'.
        "('$qq','$qqgroup','$subject',$department_id,$updateTime)";

    $result = $GLOBALS['db']->query($qqgroup_values);
    $group_id = $GLOBALS['db']->insert_id();        //获得刚刚添加的Q群Id
    $res['code'] = 1;

    //查找刚刚添加的Q群
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_qqgroup').
        " WHERE group_id=$group_id";
    $qq_group = $GLOBALS['db']->getAll($sql_select);

    //查找新添Q群的管理员
    $sql_select = 'SELECT q.groupadmin_id,q.admin_id,a.user_name FROM '.$GLOBALS['ecs']->table('account_groupadmin').
        ' AS q LEFT JOIN '.$GLOBALS['ecs']->table('account').
        ' AS a ON q.admin_id=a.account_id'.
        " WHERE q.qqgroup=$qqgroup";
    $groupAdmin = $GLOBALS['db']->getAll($sql_select);

    //获取管理名称
    $sql_select = 'SELECT a.user_name FROM '.$GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_groupadmin').
        ' AS q ON a.account_id=q.admin_id'.
        " WHERE q.qqgroup=$qqgroup";
    $result = $GLOBALS['db']->getAll($sql_select);

    $adminTotal = count($result);
    $admin_idlist = "";
    for($i = 0;$i < $adminTotal-1;$i++)
    {
        $admin_idlist .= $result[$i]['user_name'].',';
    }

    //将Q群管理员id加入Q群表 
    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account_qqgroup')." SET admin_id='$admin_idlist'".
        " WHERE qqgroup=$qqgroup";
    $result = $GLOBALS['db']->query($sql_update);

    if($res['code'] == 1)
    {
        $smarty->assign('qq_group',$qq_group);
        $smarty->assign('groupAdmin',$groupAdmin);

        $res['main'] = $smarty->fetch('qq_grouplist.htm');
        die($json->encode($res));
    }
}

//验证将添加Q群是否存在
elseif ($_REQUEST['act'] == 'cheQqgroup')
{
    $qq = mysql_real_escape_string($_GET['qq']);
    $qqgroup = mysql_real_escape_string($_GET['qqgroup']);

    $sql_select = 'SELECT count(*) AS total FROM '.$GLOBALS['ecs']->table('account_qqgroup').
        " WHERE qq='$qq' AND qqgroup='$qqgroup'";

    $result = $GLOBALS['db']->getAll($sql_select);
    echo $result[0]['total'];

    if($result[0]['total'] == 1)
    {
        $res['code'] = 1; //不可以添加
    }
    else
    {
        $res['code'] = 0;
    }

}

/*
 * Q群管理员详细信息
 */
elseif ($_REQUEST['act'] == 'getAdminInfo')
{
    $qqgroup = mysql_real_escape_string($_GET['qqgroup']);      //Q群号
    $strAdmin = mysql_real_escape_string($_GET['strAdmin']);    //管理员

    $arAdmin = array();
    //将管理员id所组成的字符串分割
    $arAdmin = explode(',',$strAdmin);
    $length = count($arAdmin);
    $adminlist = "";

    for($i = 0; $i < $length; $i++)
    {
        if($i == $length-1)
        {
            $adminlist .= "'$arAdmin[$i]'"; 
        }
        else
        {
            $adminlist .= "'$arAdmin[$i]',";
        }
    }

    $sql_select = 'SELECT a.email,a.user_name AS qq,u.user_name,d.role_name FROM '.$GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.user=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' AS d ON a.department_id=d.role_id '.
        ' WHERE a.user_name IN('.$adminlist.')'.' AND a.type_id=1';

    $result = $GLOBALS['db']->getAll($sql_select);

    if($result)
    {
        $smarty->assign('qqgroup',$qqgroup);
        $smarty->assign('adminInfo',$result);

        $res['code'] = 1;
        $res['main'] = $smarty->fetch('groupAdminInfo.htm');
        die($json->encode($res));
    }
}

/*
 * 删除Q群
 * 删除Q群时同时删除Q群管理员表中对就值
 */
elseif ($_REQUEST['act'] == 'delgroup')
{
    $group_id = intval($_GET['group_id']);
    $qqgroup = mysql_real_escape_string($_GET['qqgroup']);

    $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account_qqgroup')." WHERE group_id=$group_id";

    $result = $GLOBALS['db']->query($sql_del);

    if($result == 1)
    {
        $sql_del = 'DELETE FROM '.$GLOBALS['ecs']->table('account_groupadmin')." WHERE qqgroup=$qqgroup";

        $result = $GLOBALS['db']->query($sql_del);

        if($result)
        {
            echo 1;     //删除成功
        }
        else
        {
            echo 0;     //删除失败
        }
    }
}

//修改Q群
elseif ($_REQUEST['act'] == 'updgroup')
{
    $group_id = $_GET['group_id'];
    $qqgroup = $_GET['qqgroup']; 

    //获取Q群信息
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_qqgroup').
        " WHERE group_id=$group_id";
    $qqgroup_info = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('qqgroup_info',$qqgroup_info);
    $smarty->assign('qqgroup',$qqgroup_info[0]['qqgroup']);
    $smarty->assign('admin_list',$qqgtoup_info[0]['admin_id']);
    $res['main'] = $smarty->fetch('modify_group.htm');

    die($json->encode($res));
}

//返回QQ于Q群管理员的下拉控件
elseif ($_REQUEST['act'] == 'getAdminqq')
{
    $user_name = mysql_real_escape_string($_GET['user_name']);
    $id = intval($_GET['id']);

    $sql_select = 'SELECT distinct user_name,account_id FROM '.$GLOBALS['ecs']->table('account').
        " WHERE type_id=1 AND user_name LIKE '%$user_name%'";
    $admin_qq = $GLOBALS['db']->getAll($sql_select);

    $res['code'] = 1;
    if($id == 2)        //Q群管理员列表
    {
        $smarty->assign('qqAdminlist',$admin_qq);
        $res['main'] = $smarty->fetch('qqAdminlist.htm');
    }
    else                //QQ列表
    { 
        $smarty->assign('account_qq',$admin_qq);
        $res['main'] = $smarty->fetch('qqlist.htm');
    }

    die($json->encode($res));
}

/*
 * 查找Q群
 */
elseif ($_REQUEST['act'] == 'search_qqgroup')
{
    $admin =mysql_real_escape_string($_GET['admin']);       //Q群管理员
    $qqgroup =mysql_real_escape_string($_GET['qqgroup']);
    $qq = $_GET['qq'];
    $subject = mysql_real_escape_string($_GET['subject']);

    $sql_condition = "";
    $account_qq = qq_get();
    $base = 0;

    $smarty->assign('account_qq',$account_qq);

    //获取查询条件 
    if($admin != "")
    {
        $sql_condition .= " WHERE q.admin_id LIKE '%$admin%'";
        $base = 1;
    }
    elseif($qqgroup != "")
    {
        if($base > 0)
        {
            $sql_condition .= " AND qqgroup LIKE '%$qqgroup%'";
        }
        else
        {
            $sql_condition .= " WHERE qqgroup LIKE '%$qqgroup%'";
            $base = 1;
        }
    }
    elseif($qq != "")
    {
        if($base > 0) {
            $sql_condition .= " AND qq LIKE '%$qq%'";
        }
        else
        {
            $sql_condition .= " WHERE qq LIKE '%$qq%'";
            $base = 1;
        }
    }
    elseif($subject != "")
    {
        if($base > 0 )
        {
            $sql_condition .= " AND subject LIKE '%$subject%'";
        }
        else
        {
            $sql_condition .= " WHERE subject LIKE '%$subject%'";
            $base = 1;
        }
    }
    if($base != 0)
    {
        $qq_grouplist = qq_group_get($sql_condition);
        $smarty->assign('qq_group',$qq_grouplist);

        $res['main'] = $smarty->fetch('account_qqgroup.htm');
        die($json->encode($res));
    }
    else
    {
        $res['message'] = '请输入查询条件';
    }
}

/*
 * 查看帐号更新
 * 部门管理员只能看到本部门的账号更新
 * 超级管理员可以看到所有
 */
elseif ($_REQUEST['act'] == 'update_view')
{
    admin_priv('update_view');

    $behave = $_GET['behave'];              //判断查看状态 未更新 已经更新 本周更新)

    $dateNow = strtotime(date('Y-m-d H:i')) -8*3600; 
    $weekarray=array('日','一','二','三','四','五','六'); 
    $nowData .=date('Y年m月d日').'  星期'.$weekarray[date('w')]; 
    $smarty->assign('nowDate',$nowData);
    $smarty->assign('admin_name',$admin_name);
    $weekday = date('w');

    //查看用户所在部门
    $department_id = $_SESSION['role_id'];

    //查询该部门未更新帐号,条件为(strtotime(data('Y-m-d')) - account_updatetime)>7
    if(!$department_id)
    {
        $sql_where = "";
    }
    else
    {
        $sql_where = " AND a.department_id=$department_id";
    }

    /* 如果没有选择则默认显示未更新 */
    switch($behave)
    {
    case 0:         //未更新
        $sql_condition = " WHERE ($dateNow-a.account_updatetime)/86400>=7 AND a.updateTime LIKE '%$weekday%'".$sql_where.
            ' AND a.usable=0';
        break;

    case 1:         //已经更新
        $sql_condition = " WHERE ($dateNow-a.account_updatetime)/86400<=7".$sql_where.
            ' AND a.usable=0';         
        break;

    case 2:         //本周更新
        break;

    default :       //未更新
        $sql_condition = " WHERE ($dateNow-a.account_updatetime)/86400>=7 AND a.updateTime LIKE '%$weekday%'".$sql_where.
            ' AND a.usable=0';
        break;
    }

    /*帐号更新情况 : 0 未更新, 1 已更新, 本周更新*/

    $result = get_accountToupdate($sql_condition);

    $smarty->assign('update_view',$result);
    $smarty->assign('status',$behave);
    $smarty->assign('admin_name',$_SESSION['admin_name']);

    $res['main'] = $smarty->fetch('account_update.htm');
    die($json->encode($res));
}    

/*
 * 更新时间设置:
 */
elseif ($_REQUEST['act'] == 'updatetime_config')
{
    $updateTime = mysql_real_escape_string($_GET['updateTimelist']);
    $account_id = intval($_GET['account_id']);

    $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
        " SET updateTime='$updateTime' WHERE account_id=$account_id";

    $result = $GLOBALS['db']->query($sql_update);

    if($result == 1)
    {
        $res['code'] = 1;
    }
    else
    {
        $res['code'] = 0;
    }

    die($json->encode($res));
}

//修改帐号密码
elseif ($_REQUEST['act'] == 'modifyPassword')
{
    $behave = intval($_GET['behave']);
    $account_id = intval($_GET['account_id']);
    $password = mysql_real_escape_string($_GET['password']);
    $user_name = mysql_real_escape_string($_GET['user_name']);

    $account['account_id'] = $account_id;
    $account['password'] = $password;
    $account['user_name'] = $user_name;

    if($behave == 1)     //返回密码修改操作界面 
    {
        $smarty->assign('account',$account);
        $res['main'] = $smarty->fetch('modifyPwd.htm');
    }
    else                //执行密码修改操作
    {
        $newpassword = mysql_real_escape_string($_GET['newPassword']);
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('account').
            " SET password=$newpassword";
        $result = $GLOBALS['db']->query($sql_update);
        if($result)
        {
            $res['code'] = 1;
            $res['message'] = '密码修改成功';
        }
        else
        {
            $res['code'] = 0;
            $res['message'] = '密码修改失败';
        }
    }
    //执行修改帐号密码操作
    die($json->encode($res));
}

//检查帐号是否可以添加
elseif ($_REQUEST['act'] == 'add_able')
{
    $type_id = $_GET['type_id'];
    $user_name = $_GET['user_name'];

    $sql_select = 'SELECT COUNT(*) AS exist FROM '.$GLOBALS['ecs']->table('account')
        ." WHERE type_id=$type_id AND user_name=$user_name";
    $result = $GLOBALS['db']->getAll($sql_select);

    if($result[0]['exist'] == 1)     
    {
        echo 1;   //不可以添加
    }
    else
    {
        echo 0;   //可以添加
    }

}

//添加新的账号用途
elseif ($_REQUEST['act'] == 'add_purpose')
{
   $purpose_name = trim(mysql_real_escape_string($_REQUEST['purpose_name'])); 

   $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account_purpose')
       .'(purpose_name,status,update_time)'
       ."VALUES('$purpose_name',1,UNIX_TIMESTAMP(NOW()))";

   $res['code'] = $GLOBALS['db']->query($sql_insert);
   $res['purpose_id'] = $GLOBALS['db']->insert_id();
   $res['purpose_name'] = $purpose_name;

   die($json->encode($res));
}

//添加新账号类型
elseif ($_REQUEST['act'] == 'add_type')
{
    $type_name = trim(mysql_real_escape_string($_REQUEST['type_name']));
    $label = trim(mysql_real_escape_string($_REQUEST['label']));

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account_type')
        .'(type_name,label)'."VALUES('$type_name','$label')";

    $res['code'] = $GLOBALS['db']->query($sql_insert);
    $res['type_name'] = $type_name;
    $res['type_id'] = $GLOBALS['db']->insert_id();

    die($json->encode($res));
}

else if($_REQUEST['act'] == 'add_pwd_pro')
{
    $res['pwd_pro_name'] = $pwd_pro_name = trim(mysql_real_escape_string($_REQUEST['pwd_pro_name'])); 

    $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account_passwordprotect').
        '(passwordProtect)'."VALUES('$pwd_pro_name')";
    $res['code'] = $GLOBALS['db']->query($sql_insert);

    $res['pwd_pro_id'] = $GLOBALS['db']->insert_id();

    die($json->encode($res));
}

//通过部门获取员工
elseif ($_REQUEST['act'] == 'get_role_admin')
{
    $role_id = intval($_REQUEST['role_id']);
    $sql = 'SELECT user_name, user_id ,role_id FROM '.$GLOBALS['ecs']->table('admin_user').' WHERE status>0 AND stats>0 AND role_id='.$role_id;
    $res['admin'] = $GLOBALS['db']->getAll($sql);

    die($json->encode($res));
}


//拉进黑名单操作模板
elseif ($_REQUEST['act'] == 'put_in_black_htm')
{
    $user_id = intval($_REQUEST['user_id']); 
    $user_name = mysql_real_escape_string($_REQUEST['user_name']);

    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('blacklist_type').
        " WHERE status=1";

    $blacklist_type = $GLOBALS['db']->getAll($sql_select);

    $smarty->assign('user_id',$user_id);
    $smarty->assign('user_name',$user_name);
    $smarty->assign('blacklist_type',$blacklist_type);

    $res['info'] = $smarty->fetch('put_in_black.htm');

    die($json->encode($res));
}

//添加未审核黑名单
elseif ($_REQUEST['act'] == 'put_in_black')
{
    $user_id        = intval($_REQUEST['user_id']);
    $blacklist_type = intval($_REQUEST['blacklist_type']);
    $reason         = trim(mysql_real_escape_string($_REQUEST['reason']));
    $account_type   = intval($_REQUEST['account_type']);
    $account_value  = mysql_real_escape_string($_REQUEST['account_value']);
    $res            = array();
    $res['req_msg'] = true;
    $res['code']    = 0;
    $res['timeout'] = '2000';

    if($user_id)
    {
        $sql_select = 'SELECT count(*) AS total,user_id,status FROM '.$GLOBALS['ecs']->table('user_blacklist')
            ." WHERE user_id=$user_id ";
        $result = $GLOBALS['db']->getRow($sql_select);

        if($result['total']>0 && $result['status'] == 0 || $result['status'] == 2)
        {
            $res['message'] = '该顾客已经被列入黑名单，无需重复操作';
        }
        else
        {
            if($result['total'] <= 0)
            {
                $sql_select = " SELECT user_id,user_name,admin_id,role_id,'"
                    .$_SESSION['admin_name']."',UNIX_TIMESTAMP(NOW()),$blacklist_type,'$reason' FROM "
                    .$GLOBALS['ecs']->table('users')
                    ." WHERE user_id=$user_id";

                $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_blacklist').
                    '(user_id,user_name,admin_id,role_id,operator_in,in_time,type_id,reason)'.$sql_select;    
            }
            elseif($result['status'] == 1)
            {
                $sql_insert = 'UPDATE '.$GLOBALS['ecs']->table('user_blacklist').
                    " SET status=0,reason='$reason',type_id=$blacklist_type,operator_in={$_SESSION['admin_name']} WHERE user_id=$user_id";
            }

            if($GLOBALS['db']->query($sql_insert)) {
                $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users').' SET is_black=1'.
                    " WHERE user_id=$user_id";
                $GLOBALS['db']->query($sql_update);

                $res['code'] = 1;
                $res['message'] = '已经加入黑名单';
            } else {
                $res['message'] = '未能加入黑名单';
            }
        }
    }
    elseif($account_type && $account_value)      //添加一条黑名单账号记录
    {
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('account_blacklist')
            ." WHERE account_type=$account_type AND account_value=$account_value";
        if($GLOBALS['db']->getOne($sql_select))
        {
            $res['message'] = '已经存在，不需要重复添加';
        }
        else
        {
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('account_blacklist')
                .'(account_type,blacklist_type,account_value,add_admin,in_time)VALUES('."$account_type,4,'$account_value',".$_SESSION['admin_id'].",UNIX_TIMESTAMP(NOW()))";
            if($GLOBALS['db']->query($sql_insert))
            {
                $res['code'] = 1;
                $res['message'] = '成功加入黑名单';
            } else
                $res['message'] = '未能加入黑名单';    
        }
    }
    else if (isset($_REQUEST['file_account'])) {
        //用文件导入方式导入黑名单 
    }

    die($json->encode($res));
}

//移出黑名单
else if ($_REQUEST['act'] == 'del_blacklist')
{
    $user_id = intval($_REQUEST['user_id']);
    $row_id = intval($_REQUEST['row_id']);

    $res = array('req_msg'=>true,'timeout'=>2000,'code'=>0,'message'=>'');

    $sql_del = 'UPDATE '.$GLOBALS['ecs']->table('user_blacklist')
        .' SET status=1,out_time=UNIX_TIMESTAMP(NOW())'
        ." WHERE user_id=$user_id";
    $res['code'] = $GLOBALS['db']->query($sql_del);

    if($res['code'])
    {
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('users')
            .' SET is_black=0'." WHERE user_id=$user_id";
        if($GLOBALS['db']->query($sql_update))
        {
            $res['message'] = '移除成功';
            $res['row_id'] = $row_id;
        }
    }
    else
    {
        $res['message'] = '移除失败';
    }

    die($json->encode($res));
}

//搜索黑名单
else if ($_REQUEST['act'] == 'sch_blacklist')
{
   $user_name = mysql_real_escape_string(trim($_REQUEST['user_name']));
   $phone = mysql_real_escape_string(trim($_REQUEST['phone']));
   $operator_in = intval($_REQUEST['operator_in']);
   $blacklist_status = intval($_REQUEST['blacklist_status']);

   $smarty->assign('blackstatus',$blacklist_status);

   $where = " WHERE b.status=$blacklist_status ";
   $condition = '';
   if($user_name != '')
   {
       $where .= " AND b.user_name LIKE '%$user_name%'";
       $condition .= "&user_name=$user_name"; 
   }

   if($phone != '')
   {
       $where .= " AND u.mobile_phone LIKE '%$phone%' ";
       $codition .= "&phone='$phone'";
   }

   if($operator_in != 0)
   {
       $where .= " AND b.operator_in=$operator_in ";
       $condtion .= "&operator_in=$operator_in";
   }

   /* 分页大小 */
   $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);
   if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
   {
       $filter['page_size'] = intval($_REQUEST['page_size']);
   }
   else
   {
       $filter['page_size'] = 20; 
   }

   $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_blacklist').
       ' AS b LEFT JOIN '.$GLOBALS['ecs']->table('role').
       ' AS r ON b.role_id=r.role_id LEFT JOIN '.$GLOBALS['ecs']->table('users').
       ' AS u ON u.user_id=b.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
       ' AS a ON a.user_id=b.admin_id '.$where;

   $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
   $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

   // 设置分页
   $page_set = array (1,2,3,4,5,6,7);
   if ($filter['page'] > 4)
   {
       foreach ($page_set as &$val)
       {
           $val += $filter['page'] -4;
       }
   }

   if (end($page_set) > $filter['page_count'])
   {
       $page_set = array ();
       for ($i = 7; $i >= 0; $i--)
       {
           if ($filter['page_count'] - $i > 0)
           {
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
       'act'           => 'sch_blacklist',
   );

   $sql_select = 'SELECT b.*,r.role_name,a.user_name AS admin_name FROM '.$GLOBALS['ecs']->table('user_blacklist').
       ' AS b LEFT JOIN '.$GLOBALS['ecs']->table('role').
       ' AS r ON b.role_id=r.role_id LEFT JOIN '.$GLOBALS['ecs']->table('users').
       ' AS u ON u.user_id=b.user_id LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
       ' AS a ON a.user_id=b.admin_id '.$where.
       ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

   $user_blacklist = $GLOBALS['db']->getAll($sql_select);

   foreach($user_blacklist AS &$val)
   {
       $val['in_time'] = date('Y-m-d',$val['in_time']);
   }

   $smarty->assign('user_blacklist',$user_blacklist);
   $smarty->assign('filter',$filter);

   $res['response_action'] = 'search_service';
   $res['main'] = $smarty->fetch('sch_blacklist.htm');

   die($json->encode($res));
}

//添加关系
elseif($_REQUEST['act'] == 'add_grade')
{
    $grade_name = mysql_real_escape_string($_REQUEST['grade_name']);

    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('user_family_grade')
        ." WHERE grade_name='$grade_name'";

    $res = array('time_out'=>'2000','req_msg'=>true,'message'=>'','code'=>0);
    $res['type_name'] = $grade_name;
    $result = $GLOBALS['db']->getOne($sql_select);

    if(!$result)
    {
        $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('user_family_grade').
            "(grade_name)VALUES('$grade_name')";

        $result = $GLOBALS['db']->query($sql_insert);
        $res['type_id'] = $GLOBALS['db']->insert_id();

        if($result)
        {
            $res['code'] = 1;
            $res['message'] = '添加成功';
        }
        else
        {
            $res['message'] = '添加失败';
        }
    }
    else
    {
        $res['message'] = '添加失败';
    }

    die($json->encode($res));
}

//搜索添加家庭成员
elseif ($_REQUEST['act'] == 'sch_for_family')
{
    $phone   = mysql_real_escape_string($_REQUEST['phone']);
    $user_id = intval($_REQUEST['user_id']);
    $type    = intval($_REQUEST['type']);

    $sql_select = 'SELECT family_id,family_name FROM '
        .$GLOBALS['ecs']->table('user_family')." WHERE family_id=$user_id";

    $family_info = $GLOBALS['db']->getRow($sql_select);
    $smarty->assign('family_info',$family_info);

    $power = admin_priv('all','',false) ? 1 : 0;
    $smarty->assign('power',$power);
    $smarty->assign('user_id',$user_id);

    if($phone != '') {
        $where = " WHERE u.mobile_phone LIKE '%$phone%' OR u.home_phone LIKE '%$phone%' ";

        $sql_select = 'SELECT u.user_id,u.user_name,u.mobile_phone,u.home_phone,a.address_name FROM '
            .$GLOBALS['ecs']->table('users')
            .' AS u LEFT JOIN '.$GLOBALS['ecs']->table('user_address')
            .' AS a ON u.address_id=a.address_id '.$where;

        $user_list = $GLOBALS['db']->getAll($sql_select);

        //获取成员关系
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('user_family_grade')
            ." WHERE type=$type";
        $grade_list = $GLOBALS['db']->getAll($sql_select);
        $smarty->assign('grade_list',$grade_list);
        $smarty->assign('user_list',$user_list);
        $smarty->assign('family_id',$user_id);
        $smarty->assign('type',$type);
        $res['info'] = $smarty->fetch('sch_for_family.htm');
        die($json->encode($res));
    }
}

//显示导入的黑名单账号
elseif($act == 'show_file_blacklist_account')
{
    
}

/*
 * 账号管理相关函数
 */

// 所有账号
function accounts_get($sql_condition)
{
    /* 分页大小 */
    $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page'])<=0) ? 1 : intval($_REQUEST['page']);

    if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
    {
        $filter['page_size'] = intval($_REQUEST['page_size']);
    }
    else
    {
        $filter['page_size'] = 20; 

    }

    //帐号总数
    $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('account').' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').' AS u ON a.updater=u.user_id'.$sql_condition;

    $filter['record_count'] = $GLOBALS['db']->getOne($sql_select);
    $filter['page_count'] = $filter['record_count']>0 ? ceil($filter['record_count']/$filter['page_size']) : 1;

    // 设置分页
    $page_set = array (1,2,3,4,5,6,7);
    if ($filter['page'] > 4)
    {
        foreach ($page_set as &$val)
        {
            $val += $filter['page'] -4;
        }
    }

    if (end($page_set) > $filter['page_count'])
    {
        $page_set = array ();
        for ($i = 7; $i >= 0; $i--)
        {
            if ($filter['page_count'] - $i > 0)
            {
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
        'act'           => 'account_search',
    );

    $sql_select = 'SELECT a.account_id,a.account_name,a.user_id,a.is_vip,a.purpose,a.password,a.belong,a.subject,a.url,FROM_UNIXTIME(account_updatetime,"%m月%d日") AS account_updatetime,u.user_name AS admin_name,a.is_check,r.role_name,a.usable FROM '
        .$GLOBALS['ecs']->table('account').' AS a LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.user_id=u.user_id LEFT JOIN '.$GLOBALS['ecs']->table('role').
        ' AS r ON a.department_id=r.role_id '.$sql_condition.
        ' LIMIT '.($filter['page']-1)*$filter['page_size'].",{$filter['page_size']}";

    $account_list = $GLOBALS['db']->getAll($sql_select);

    return array('account_list'=>$account_list,'filter'=>$filter);
}




//工作检查(部门帐号)
function get_department_account($department_id,$user_id)
{
    $sql_select = 'SELECT a.user, a.account_id, a.user_name, t.type_name, a.subject, a.url,FROM_UNIXTIME(a.account_updatetime,"%Y年%m月%d日") AS account_updatetime,a.prescore,a.score FROM '
        .$GLOBALS['ecs']->table('account')
        .' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type')
        .'AS t ON a.type_id=t.type_id'
        ." WHERE a.department_id=$department_id AND a.user=$user_id";

    $department_account = $GLOBALS['db']->getAll($sql_select);
    return $department_account;
}

//获取部门员工
function get_employees($department_id)
{
    $sql_select = 'SELECT distinct a.user,u.user_name FROM '
        .$GLOBALS['ecs']->table('account').' AS a LEFT JOIN '
        .$GLOBALS['ecs']->table('admin_user')
        ." AS u ON a.user=u.user_id";

    if($department_id != "")
    {
        $sql_select .= " WHERE a.department_id=$department_id GROUP BY a.user";   
    }

    $user_list=$GLOBALS['db']->getAll($sql_select);
    return $user_list;
}

//账号类型
function type_get()
{
    $sql_select = "SELECT * FROM ".$GLOBALS['ecs']->table('account_type');
    $type = $GLOBALS['db']->getAll($sql_select);
    return $type;
}

//部门
function department_get($department_id = "")
{
    if($department_id == "")
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role');
        $department =$GLOBALS['db']->getAll($sql_select);
        return $department;
    }
    else
    {
        $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('role')
            ." WHERE role_id = $department_id";
        $department =$GLOBALS['db']->getAll($sql_select);
        return $department;
    }
}

/*
 * 帐号的使用者
 */
function user_get()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('admin_user');
    $user = $GLOBALS['db']->getAll($sql_select);
    return $user;
}

//Q群(通过用户名)
function qqGroup_get($user_name)
{
    $sql_select = 'SELECT distinct q.group_id,q.qq,q.qqgroup,q.subject,a.user_name,q.admin_id,q.category FROM '.
        $GLOBALS['ecs']->table('account_qqgroup').
        ' AS q LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        " AS a ON q.admin_id=a.user_id WHERE qq=$user_name";

    $qqgroup = $GLOBALS['db']->getAll($sql_select);
    return $qqgroup;
}

//Q群(通过搜索条件)
function qq_group_get($sql_condition)
{
    $sql_select = 'SELECT q.qq,q.qqgroup,q.subject,a.user_name,q.admin_id,q.category,q.updateTime FROM '.
        $GLOBALS['ecs']->table('account_qqgroup').
        ' AS q LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS a ON q.admin_id=a.user_id'.$sql_condition;

    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}

//获取QQ
function qq_get()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account').' WHERE type_id=1';
    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}


//密码保护
function get_passwordprotect()
{
    $sql_select = 'SELECT * FROM '.$GLOBALS['ecs']->table('account_passwordprotect');
    $passwordprotect = $GLOBALS['db']->getAll($sql_select);
    return $passwordprotect;
}

//需要更新的账号
function get_accountToupdate($sql_condition)
{
    $sql_select = 'SELECT a.user_name,t.type_name,a.subject,a.url,FROM_UNIXTIME(a.account_updatetime,"%Y年%m月%d日") AS account_updatetime,u.user_name AS updater_name FROM '.
        $GLOBALS['ecs']->table('account').
        ' AS a LEFT JOIN '.$GLOBALS['ecs']->table('account_type').
        ' AS t ON a.type_id=t.type_id'.
        ' LEFT JOIN '.$GLOBALS['ecs']->table('admin_user').
        ' AS u ON a.updater=u.user_id'.$sql_condition;

    $result = $GLOBALS['db']->getAll($sql_select);
    return $result;
}
?>
