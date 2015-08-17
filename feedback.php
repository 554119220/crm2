<?php
/*=============================================================================
#     FileName: feedback.php
#         Desc: feedback for crm
#       Author: wuyuanhang
#        Email: 1828343841@qq.com
#     HomePage: http://www.kjrs365.com
#      Version: 0.0.1
#   LastChange: 2014-08-27 15:49:18
#      History:
=============================================================================*/
define('IN_ECS',true);
require(dirname(__FILE__).'/includes/init.php');
include_once(ROOT_PATH."includes/fckeditor/fckeditor.php");
date_default_timezone_set('Asia/Shanghai');

$action = empty($_REQUEST['act']) ? 'feedback_form' : mysql_real_escape_string($_REQUEST['act']);

if('feedback_form' == $action){
    $smarty->assign('behave','upload');
    $smarty->display('feedback.htm');
}

//提交反馈
elseif('feedback_done' == $action){

    $feedback_class = isset($_REQUEST['feedback_class']) ? intval($_REQUEST['feedback_class']) : 0;
    $message        = isset($_REQUEST['feedback']) ? $_REQUEST['feedback'] : '';
    $title          = isset($_REQUEST['title']) ? mysql_real_escape_string($_REQUEST['title']) : '';
    $res['code']    = false;

    if($_FILES['img1']['name']){
        $img1 = $_FILES['img1']['tmp_name'];
        $img1_name = rand(0,20).$_FILES['img1']['name'];
        move_uploaded_file($_FILES['img1']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/feedback/'.$img1_name);
    }

    if($_FILES['img2']['name']){
        $img2 = $_FILES['img2']['tmp_name'];
        $img2_name = rand(0,20).$_FILES['img2']['name'];
        move_uploaded_file($_FILES['img2']['tmp_name'],$_SERVER['DOCUMENT_ROOT'].'/feedback/'.$img2_name);
    }

    if(!empty($message)){
        $sql_select = 'SELECT COUNT(*) FROM '.$GLOBALS['ecs']->table('admin_feedback').
           " WHERE sender_id={$_SESSION['admin_id']} AND receiver_id=1 AND message='$message'  "; 
        if($GLOBALS['db']->getOne($sql_select)){
            $res['message'] = '请勿重复添加';
        }else{
            $sql_insert = 'INSERT INTO '.$GLOBALS['ecs']->table('admin_feedback').
                '(sender_id,receiver_id,sent_time,message_class,title,message,image1,image2)VALUES('.
                "{$_SESSION['admin_id']},1,{$_SERVER['REQUEST_TIME']},$feedback_class,'$title','$message','$img1_name','$img2_name')";
            $res['code']    = $GLOBALS['db']->query($sql_insert);
            $res['message'] = $res['code'] ? '感谢你的反馈和支持，我们会在最快的时间内解决你的问题' : '不好意思，反馈提交失败';
        }
    }else{
        $res['message'] = '不好意思，反馈提交失败';
    }

    $smarty->assign('res',$res);
    $smarty->assign('behave','upload');
    $smarty->display('feedback.htm');
}

elseif('feedback_more' == $action){
    $message_id = isset($_REQUEST['message_id']) ? intval($_REQUEST['message_id']) : 0;

    if($message_id){
        $sql_select = 'SELECT title,message,image1,image2 FROM '.$GLOBALS['ecs']->table('admin_feedback').
            " WHERE message_id=$message_id";
        $feedback = $GLOBALS['db']->getRow($sql_select); 
        if($feedback){
            if($feedback['image1']){
                $feedback['image1'] = "../../feedback/{$feedback['image1']}";
            }

            if($feedback['image2']){
                $feedback['image2'] = "../../feedback/{$feedback['image2']}";
            }
        }
    }

    $smarty->assign('feedback',$feedback);
    $smarty->assign('behave','see_more');
    $smarty->display('feedback.htm');
}

//改变BUG反馈状态
elseif('deal_feedback' == $action){

    $message_id = isset($_REQUEST['msg_id']) ? intval($_REQUEST['msg_id']) : 0;
    $item       = isset($_REQUEST['item']) ? intval($_REQUEST['item']) : 0;

    $res = array(
        'req_msg'    => true,
        'timeout'    => 2000,
        'message'    => '',
        'code'       => false,
        'tr_index'   => isset($_REQUEST['tr_index']) ? intval($_REQUEST['tr_index']) : 0,
        'table_name' => isset($_REQUEST['table_name']) ? $_REQUEST['table_name'] : '',
    );

    if($message_id){
        $sql_update = 'UPDATE '.$GLOBALS['ecs']->table('admin_feedback').
            " SET readed=IF($item,1,0),read_time=IF($item,{$_SERVER['REQUEST_TIME']},0) WHERE message_id=$message_id ";

        $res['code']    = $GLOBALS['db']->query($sql_update);
        $res['message'] = $res['code'] ? '操作成功' : '操作失败';
    }else{
        $res['message'] = '操作失败';
    }

    die($json->encode($res));

}

?>
