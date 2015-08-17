<?php
//给模板分页设置
function setHtmlPage($filter){
    $smarty->assign('page_link',    $filter['condition']);
    $smarty->assign('filter',       $filter['filter']);
    $smarty->assign('record_count', $filter['record_count']);
    $smarty->assign('page_count',   $filter['page_count']);
    $smarty->assign('page_size',    $filter['page_size']);
    $smarty->assign('page_start',   $filter['start']);
    $smarty->assign('page_end',     $filter['end']);
    $smarty->assign('full_page',    1);
    $smarty->assign('page_set',     $filter['page_set']);
    $smarty->assign('page',         $filter['page']);
    $smarty->assign('act',          $_REQUEST['act']);
}
?>
