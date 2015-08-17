<?php
define('IN_ECS', true);
if ($_GET['old'] == 'old') {
    $f = fopen('data.txt','r');
    $i = 0;
    while(!feof($f)){
        $buffer[$i] = fgets($f);
        $info = explode(' ',$buffer[$i]);
        //连接邮编
        $user_id = array_shift($info);
        $code = end($info);
        if (preg_match("/^\d{6}/",$code) || (!preg_match("/^3\d{4}/",$code) && $i>0)){
            $buffer[$i-1] .= " {$buffer[$i]}";
            $buffer[$i] ='';
        }else if (!preg_match("/^\d{6}/",$code) && (!preg_match("/^3\d{4}/",$code) && $i>0)){ 
            $buffer[$i-1] .= " {$buffer[$i]}";
            $buffer[$i] = '';
        }
        $i++;
    }
    foreach ($buffer as $k=>$v) {
        if (empty($v)) {
            unset($buffer[$k]);
        }
    }
    $fw = fopen('newdata.txt','w');
    foreach ($buffer as &$v) {
        $info = explode(' ',$v);
        fwrite($fw,$v);
        echo $v,'<br>';
    }
}else{
    $f = fopen('newdata.txt','r');    
    while(!feof($f)){
       $text = fgets($f); 
       $a = explode(' ',$text);
       unset($a['1']);
       $text = implode(' ',$a);
       echo $text.'<br>';
    }
    //把联系方式前面的0去掉
    while(!feof($f)){
       $text = fgets($f);
       $a = explode(' ',$text);
       foreach ($a as &$v) {
          if (preg_match("/^01\d{10}/",$v)) {
              $v  = substr($v,1);
          } 
       }
       $text = implode(' ',$a);
       echo $text.'<br>';
    }
    $wf = fopen('r.txt','w');
    $i = 0;
    while(!feof($f)){
        $text = fgets($f);
        $text = trim($text);
        $a = explode(' ',$text);
        $contact = array();
        foreach ($a as $k=>&$v) {
            if (preg_match("/^\d+\d$/",$v)) {
                $contact[]  = $v;
                unset($v);
            }
            if (empty($v)) {
                unset($a[$k]);
            }
        }
        $a = array_values($a);
        if (count($a) > 2) {
            $r = "{$a[0]}','".implode(" ",$contact)."','{$a[1]}".end($a);
            fwrite($wf,$r);
            echo $r,'<br>';
        }
    }
    fclose($wf);
    fclose($f);
}
exit;
?>
