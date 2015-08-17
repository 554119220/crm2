function submitAccount()
{ 
  var obj = document.forms['for_account'];
  var password = obj.elements['password'].value;            //帐号密码
  var type = obj.elements['type'].value;                    //帐号类型
  var subject = obj.elements['subject'].value;
  var purpose = obj.elements['account_purpose'].value;
  if(type == 1)                                             //新帐号类型
  {
    var account_name = obj.elements['txtqquser_name'].value;
    var is_vip = obj.elements['is_vip'].value;
  }
  else
  {
    var account_name = obj.elements['user_name'].value; 
  }

  var email = obj.elements['email'].value;
  var department = obj.elements['department'].value;         //部门
  var user_id = obj.elements['user'].value;                    //使用者id
  var url = obj.elements['url'].value;                      //登陆地址
  var passwordProtectID = obj.elements['passwordProtectID'].value;  //密码保护
  var phone = obj.elements['phone'].value;
  var remark = obj.elements['remark'].value;

  var belong = document.getElementById('belong_field'); //授权可查看者

  var _spans = belong.getElementsByTagName('span');
  var belong_list = '';

  for(var i=0;i<_spans.length;i++)
  {
    belong_list += _spans[i].id+"','";
  }

  var msg = new Array();
  msg['timeout'] = 1000;

  if(!chePassword(password))
  {
    msg['message'] = '密码不能空';
    showMsg(msg);
  }
  else if(!cheEmail(email))
  {
    msg['message'] = 'email格式不正确';
    showMsg(msg);
  }
  else if(!chePhone(phone))
  {
    document.getElementById('alarm_phone').innerHTML = '手机格式有误';
    return ;
  }
  else
  {
    Ajax.call('performance.php?act=insert_account','account_name='+account_name+'&is_vip='+is_vip+'&purpose='+purpose+'&password='+password+'&type_id='+type+'&subject='+subject+'&email='+email+'&department='+department+'&user_id='+user_id+'&url='+url+'&passwordProtect_id='+passwordProtectID+'&belong_list='+belong_list+'&phone='+phone+'&remark='+remark,addAccount,'POST','JSON');
  }
}

// 添加账号回调
function addAccount(res)
{
  if(res.code == 1)
  {
    document.getElementById('div_pop_ups').style.display='none';
    document.getElementById('fade').style.display='none';
    showMsg(res);
  }
}

//验证手机
function chePhone(phone)
{
  if(phone != '')
  {
    if(phone.length != 11)
    { 
      return false;
    }
    else
      return true;  
  }
  else
    return true;

}

//验证密码
function chePassword(password)
{
  if(password == "")
  {
    document.getElementById('alarm_pwd').innerHTML = '密码不能为空';
    return false;
  }
  else if(password.length<6)
  {
    document.getElementById('alarm_pwd').innerHTML = '密码不能少于六位';
    return false;  
  }
  else
  {
    document.getElementById('alarm_pwd').innerHTML = '';
    return true;
  }
}

//验证用户名
function cheUsername(user_name)
{
  if(user_name == "")
  {
    document.getElementById('alarm_username').innerHTML = '用户名不能为空';
   ; return false;
  }
  else
  {
    document.getElementById('alarm_username').innerHTML = '';
    return true;
  }
}

//判断类型
function judge(val,insert_val)
{
  var obj_table = document.getElementById('table_info');
  var obj_select = document.getElementById('type_id');
  var input_qq = document.getElementById('txtqquser_name');
  var user_name = document.getElementById('txt_user_name');
  var new_type = document.getElementById('new_type');

  var tr_select = (obj_select.parentNode).parentNode;
  if(insert_val != '')
  {
    var insert_index = tr_select.rowIndex+1;
  }
  else
  {
    var insert_index = new_type == null ? tr_select.rowIndex+1 : tr_select.rowIndex+2;
  }

  if(val == 1 && input_qq == null)
  {
    obj_table.rows[insert_index].innerHTML = '<th width="130">QQ ：</th><td><input type="text" class="input_text" value="" name="txtqquser_name" id="txtqquser_name" onblur="cheQQ(this.value)" /> <label style="display:inline;color:red"><input type="checkbox" name="is_vip" id="is_vip_id" /><b>&nbsp;会员</b></label> <span id="alarm_qq" style="color:red"></span></td>'
  }
  else if(input_qq != null && val == 1 && insert_val != '')
  {
  }
  else
  {
    if(user_name == null)
    {
      obj_table.rows[insert_index].innerHTML = '<th width="130">用户名 ：</th><td><input name="user_name" id="txt_user_name" value="'+insert_val+'" type="text" onclick="cheUsername(this.value)"/> <span id="alarm_username" style="color:red"></span></td>'
    }

    switch(val)
    {
      case '2' :  //邮箱
        document.getElementById('urlid').value='';
        break;
      case '3' :  //营销QQ
        document.getElementById('urlid').value='';
        break;
      case '4' :  //旺旺
        document.getElementById('urlid').value="http://wangwang.taobao.com/";
        break;
      case '5' :  //腾讯微博
        document.getElementById('urlid').value="http://share.v.t.qq.com/index.php?c=share&a=index";
        break;
      case '6' :  //新浪微博
        document.getElementById('urlid').value="http://weibo.com/";
        break;
      case '8' :  //百度
        document.getElementById('urlid').value="http://baidu.com/";
        break;
      default :
        document.getElementById('urlid').value=''; 
        break;
    }
  }
}

//只能输入数字
function isNum(e) 
{
  var k = window.event ? e.keyCode : e.which;
  if (((k >= 48) && (k <= 57)) || k == 8 || k == 0)
  {

  } 
  else if (e.keyCode == 13)
  {
    var obj = document.forms['for_account'];
    var qq = obj.elements['txtqq'].value;

    Ajax.call('account_manager.php?act=getAdminqq&','user_name='+qq+'&id=1',getqqResponse,'GET','JSON');
  } 
  else
  {
    if (window.event) 
    {
      window.event.returnValue = false;
    }           
    else 
    {
      e.preventDefault();
    }

  } 
}

//获取QQ列表回调
function getqqResponse(res)
{
  if(res.code == 1)
  {
    document.getElementById('account_qqlist').style.display = 'block';
    document.getElementById('account_qqlist').innerHTML = res.main;
  }
}

//验证邮箱
function cheEmail(email)
{
  if(email != '')
  {
    var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/;
    if(!(reg.test(email)))
    {
      document.getElementById('alarm_email').innerHTML = 'email格式不正确';
      return false;
    }
    else
    {
      document.getElementById('alarm_email').innerHTML = '';
      return true;
    } 
  }
  else
  {
    return true;
  }
}

//验证登陆地址
function cheUrl(url) 
{
  var reg = /^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/;
  if(!(reg.test(url)))
  {
    document.getElementById('url').innerHTML = "非法url";
    return false;
  }
  else
  {
    return true;
  }
}

//添加新类型
function addType()
{
  var type_name = prompt('请输入新的类型名称 格式：多客服-PPCRM','');

  var msg = new Array();
  msg['timeout'] = 2000;
  if(type_name != null && type_name != '')
  {
    var arr = type_name.split('-');
    if(arr.length != 2 )
    {
      msg['message'] = '新类型输入格式不正确';
      showMsg(msg);
    }
    else if(arr[0] == '' || arr[1] == '')
    {
      msg['message'] = '新类型输入信息不完全';
      showMsg(msg);
    }
    else
    {
      var obj_select = document.getElementById('type_id');
      for(var i = 0;i < obj_select.options.length; i++)
      {
        if(obj_select.options[i].text == arr[0])
          break;
      }

      if(i >= obj_select.options.length)
      {
        Ajax.call('account_manager.php?act=add_type','type_name='+arr[0]+'&label='+arr[1],addTypeRes,'GET','JSON');
      }
    }
  }
}

function addTypeRes(res)
{
  var obj_select = document.getElementById('type_id');
  var opt = document.createElement('option');
  opt.value = res.type_id;
  opt.text = res.type_name;

  obj_select.appendChild(opt);

  var opt_index = obj_select.options.length - 1;
  obj_select.options[opt_index].selected = true;
}

//添加关系
function addGrade()
{
  var grade_name = prompt('请输入新的关系名称','');

  var msg = new Array();
  msg['timeout'] = 2000;
  if(grade_name != null && grade_name != '') 
  {
    var obj_select = document.getElementById('type_id');
    for(var i = 0;i < obj_select.options.length; i++)
    {
      if(obj_select.options[i].text == grade_name)
        break;
    }

    if(i >= obj_select.options.length)
    {
      Ajax.call('account_manager.php?act=add_grade','grade_name='+grade_name,addTypeRes,'GET','JSON');
    }
  }
}

//搜索家庭成员
/*
function schAddFamilyUser(phone)
{
  if(phone != '')
  {
    Ajax.call('account_managerr.php?act=sch_add_family_user','phone='+phone,schAddFamilyUserRes,'GET','JSON');
  }
}

function schAddFamilyUserRes(res)
{
  document.getElementById('pop_ups').innerHTML = res.info;
  document.getElementById('fade').style.display = 'block';
  document.getElementById('div_pop_ups').style.display = 'block';
  document.getElementById('close_pop').style.display = 'none';
}
*/

//验证类型
function cheType()
{
  var obj = document.forms['for_account'];
  var type_name = obj.elements['new_type'].value;

  Ajax.call('account_manager.php?act=add_newtype','type_name='+type_name,che_typeResponse,'GET','TEXT');
}
//验证类型回调
function che_typeResponse(res)
{
  if(res == 1)
    document.getElementById('new_type_alarm').innerHTML = '类型已经存在';
  else
    document.getElementById('new_type_alarm').innerHTML = '可以添加'; 
}

//单独的QQ验证
function cheQQ(qq)
{
  if(/^[0-9]+$/.test(qq) && qq.length > 6)
  {
    document.getElementById('alarm_qq').innerHTML = '';
    document.getElementById('txt_email').value = qq + '@qq.com';
    document.getElementById('urlid').value = "http:\/\/user.qzone.qq.com\/" + qq;
  }
  else if(cheEmail(qq))
  {
    document.getElementById('alarm_qq').innerHTML = '';
    document.getElementById('txt_email').value = qq + '@qq.com';
    document.getElementById('urlid').value = "http:\/\/user.qzone.qq.com\/" + qq;
  }
  else
  {
    document.getElementById('alarm_qq').innerHTML = 'qq格式不正确';
  }
}

//验证主题
function cheSubject(subject)
{
  if(subject == "")
  {
    document.getElementById('subject').innerHTML = ' * 主题不能为空' ; 
  }
  else
  {
    document.getElementById('subject').innerHTML = "";
  }
}

/*
 * 修改账号
 */
function modifyAccountControl()
{
  var obj = document.forms['for_account'];
  var account_id = obj.elements['account_id'].value;        //帐号id
  var password = obj.elements['password'].value;            //密码
  var type = obj.elements['type'].value;                    //帐号类型  
  var email = obj.elements['email'].value;                  //邮箱
  var department = obj.elements['department'].value;        //部门
  var user = obj.elements['user'].value;                    //使用者
  var url = obj.elements['url'].value;                      //登陆地址
  var handin = obj.elements['admin_id'].value;              //提交者
  var subject = obj.elements['subject'].value;              //主题
  var passwordProtectID = obj.elements['passwordProtectID'].value;  //密码保护
  var usual_update = '';     //修改时间
  var is_vip = obj.elements['is_vip'];
  var remark = obj.elements['remark'].value;
  var purpose = obj.elements['account_purpose'].value;


  var belong = document.getElementById('belong_field'); //授权可查看者

  var _spans = belong.getElementsByTagName('span');
  var belong_list = '';

  for(var i=0;i<_spans.length;i++)
  {
    belong_list += _spans[i].id+"','";
  }

  is_vip = is_vip.checked == true ? 1 : 0;

  if(type == 1)                                             //新帐号类型
  {
    var account_name = obj.elements['txtqquser_name'].value;
  }
  else
  {
    var account_name = obj.elements['user_name'].value; 
  }

  var msg = new Array();
  msg['timeout'] = 1000;

  if(!chePassword(password))
  {
    alert('password');
    msg['message'] = '密码不能为空'
      showMsg(msg);
  }
  else if(!cheEmail(email))
  {
    alert('email');
    msg['message'] = '邮箱不能为空'
      showMsg(msg);
  }
  else
  {
    Ajax.call('account_manager.php?act=update_account','account_name='+account_name+'&password='+password+'&type_id='+type+'&is_vip='+is_vip+'&purpose='+purpose+'&email='+email+'&department='+department+'&user='+user+'&url='+url+'&admin_id='+handin+'&subject='+subject+'&passwordProtect_id='+passwordProtectID+'&account_id='+account_id+'&usual_update='+usual_update+'&belong_list='+belong_list,updateAccount,'POST','JSON'); 
  }
}

//修改帐号回调
function updateAccount(res)
{
  if(res.code)
  {
    document.getElementById('div_pop_ups').style.display = 'none';
    document.getElementById('fade').style.display = 'none';
    showMsg(res);
  }
  else
  {
    return ;
  }
}

/*
 * 查找Q群
 * 验证搜索条件
 */
function cheVal()
{
  var obj = document.forms['search_qqgroup'];
  var admin = obj.elements['admin'].value;            //Q群管理员
  var qqgroup = obj.elements['qqgroup'].value;        //Q群帐号
  var subject = obj.elements['subject'].value;        //主题
  var qq = obj.elements['qq'].value;                  //所属QQ
  var url = obj.elements['search_url'];               //登陆地址
  var paramer = "";

  if(admin != "")
  {
    paramer +=  '&admin='+admin;
  }  

  if(qqgroup != "")
  {
    paramer += '&qqgroup='+qqgroup;
  }

  if(subject != "")
  {
    paramer += "&subject="+subject;
  }

  if(qq != "")
  {
    paramer += "&qq="+qq;
  }

  var url = 'account_manager.php?act=search_qqgroup' + paramer;
  document.getElementById('search_url').href= url; 
}

/*
 * 帐号搜索公用函数
 */
function searchAccount()
{
  var obj = document.forms['for_search_account'];
  var type_id = obj.elements['type'].value;           //类型
  var admin_id = obj.elements['admin_id'].value;
  var account_name = obj.elements['account_name'].value;
  var objstatus = obj.elements['arrstatus'];          //帐号可用性
  var statusValue = "";

  for(var i=0; i<objstatus.length; i++)
  {
    if(objstatus[i].checked == true)
    {
      statusValue += objstatus[i].value + ",";
    }
  }

  statusValue = statusValue.substr(0,statusValue.length-1);

  Ajax.call('account_manager.php?act=account_search','type_id='+type_id+'&admin_id='+admin_id+'&account_name='+account_name+'&status='+statusValue,searchAccountResponse,'GET','JSON');
}

//帐号搜索回调函数
function searchAccountResponse(res)
{
  var box = document.getElementById('box');
  var divs = box.getElementsByTagName('div');

  for(var i=0; i<divs.length; i++)
  {
    if(divs[i].id != 'resource')
    {
      divs[i].style.display = 'none';
    }
    else
    {
      divs[i].style.display = '';
    }
  }

  if(res.code == 1)
  {
    var pre_bottom_div = document.getElementById('bottom_tip');
    if(pre_bottom_div != null)
    {
      pre_bottom_div.parentNode.removeChild(pre_bottom_div);
    }
    document.getElementById('resource').innerHTML = res.main;
    init();
  }
}

//获取Q群管理
function getAdmin(obj)
{
  var adminId = obj.value;

  // 遍历select下拉选项  找出被选中的option的TEXT
  for (var i = 0; i < obj.length; i++)
  {
    if (obj[i].value == adminId)
    {
      var adminName = obj[i].text;
    }
  }

  var groupList = document.getElementById('grouplist');          // 获取存放管理员的容器对象
  var checkBox = groupList.getElementsByTagName('input').length; // 获取容器中已经存放管理员的数量

  // 如果容器中存放了所有可选的管理员 则不允许继续添加  弹出信息提示用户
  if (checkBox == obj.length)
  {
    /*
       var msg        = new Array ();
       msg['message'] = '请勿重复添加！';
       msg['timeout'] = 2000;

       showMsg(msg); // 消息提示函数
       */
    return false; // 中断提交操作
  }

  // 获取容器中的数据
  var adminList = groupList.innerHTML;

  // 添加新的数据
  adminList += '<label style="margin:6px 10px 6px 10px"><input type="checkbox" value='+adminId+' name="adminid_list" checked />'+adminName+'</label>';
  document.getElementById('grouplist').innerHTML = adminList; // 重新将数据放回容器中
}


/*
 * 显示帐号更新时间选项
 */
function getUpdateTime(divnum)
{
  var divId = 'updateTimeTd'+divnum;
  /*document.getElementById(divId).innerHTML = "<form name='for_updateTime' method='POST'>"+
    "<label><input type='checkbox' name='updateTime' value='7'>星期天<\/label>"+
    "<label><input type='checkbox' name='updateTime' value='1'>星期一<\/label>"+
    "<label><input type='checkbox' name='updateTime' value='2'>星期二<\/label>"+
    "<label><input type='checkbox' name='updateTime' value='3'>星期三<\/label>"+
    "<label><input type='checkbox' name='updateTime' value='4'>星期四<\/label>"+
    "<label><input type='checkbox' name='updateTime' value='5'>星期五<\/label>"+
    "<label><input type='checkbox' name='updateTime' value='6'>星期六<\/label>"+
    "<input type='button' id='btnUpdateTime' class='b_submit' value='提交' onclick='postUpdateTime({$val.account_id})'>"+
    "<\/form>";
    */
  document.getElementById(divId).style.display = document.getElementById(divId).style.display=='block'?'none':'block';
}

/*
 * 添加Q群
 */
function addQqgroup()
{
  var qq = document.getElementById('qq').value;                       //获取QQ
  var qqgroup = document.getElementById('qqgroup').value;             //Q群帐号
  var subject = document.getElementById('subject').value;             //Q群主题
  var departmentID = document.getElementById('DepartmentID').value;   //部门ID
  var adminList = document.getElementById('grouplist');               //获取管理员列表
  var inputs = adminList.getElementsByTagName('input');               //管理员列表容器
  var strAdmin = ''; 

  if(qq == "" && qq.length < 6)
  {
    qq = document.getElementById('qqlist').value;
  }

  if(inputs.length > 0)
  {
    for (i = 1; i <= inputs.length; i++)  //循环获得管理员的id
    {
      strAdmin += inputs[i-1].value + ',';
    }
  }
  else 
  {
    strAdmin = document.getElementById('admin_id').value;
  }


  Ajax.call('account_manager.php?act=insert_qqgroup&','qq='+qq+'&qqgroup='+qqgroup+'&subject='+subject+'&strAdmin='+strAdmin+'&departmentID='+departmentID,addQqgroupResponse,'GET','JSON');
}

//添加Q群回调
function addQqgroupResponse(res)
{
  if(res.code == 1)
  {
    document.getElementById('addedGrouplist').innerHTML += res.main;
    document.getElementById('grouplist').innerHTML = "已经添加管理员:";
  }
  else
  {
    var msg = new Array();
    msg['message'] = '添加失败！';
    msg['timeout'] = 2000;

    showMsg(msg); // 消息提示函数
    return false; // 中断提交操作
  }
}

//选择下拉列表中的QQ自动填充ID为qq的文本框
function getQq(obj)
{
  document.getElementById('qq').value = obj.value;
}

//验证Q群是否已经存在
function isExistQg()
{
  var qq = document.getElementById('qq').value;
  var qqgroup = document.getElementById('qqgroup').value;
  if(qq == "")
  {
    qq = document.getElementById('qqlist').value;
  }

  Ajax.call('account_manager.php?act=cheQqgroup&','qq='+qq+'&qqgroup='+qqgroup,isExistQgResponse,'GET','TEXT');
}
//验证Q群回调
function isExistQgResponse(res)
{
  if(res['code'] == 1 )
  {
    var msg = new Array();
    msg['message'] = '已经存在此Q群！';
    msg['timeout'] = 2000;

    showMsg(msg);
    return false;
  }
  else
  {
    document.getElementById('btnadd').disabled = false; 
  }
}

//获取匹配QQ
function getAdminqq(e)
{
  if(e.keyCode == 13)
  {
    var obj = document.forms['for_account'];
    var user_name = obj.elements['txtAdminId'].value;

    Ajax.call('account_manager.php?act=getAdminqq&','user_name='+user_name+'&id=2',getAdminResponse,'GET','JSON'); 
  }
  else
  {
    return false;
  }
}

//获取匹配QQ回调
function getAdminResponse(res)
{
  if(res.code == 1)
  {
    document.getElementById('adminqqList').style.display = 'block';
    document.getElementById('adminqqList').innerHTML = res.main;
  }
}

//设置帐号更新时间
function postUpdateTime(account_id)
{
  var obj = document.forms['for_updateTime'];      //更新时间表单
  var objUpdateTime = obj.elements['updateTime'];  //存时间的容器
  var updateTimelist = "";

  for(var i = 0; i < objUpdateTime.length; i++)    //遍历获取更新时间
  {
    if(objUpdateTime[i].checked == true)
    {
      updateTimelist += objUpdateTime[i].value + ','; 
    }
  }

  updateTimelist = updateTimelist.substr(0,updateTimelist.length-1);    //存放更新时间的字符串 

  Ajax.call('account_manager.php?act=updatetime_config&','account_id='+account_id+'&updateTimelist='+updateTimelist,postUpdateTimeResponse,'GET','JSON');

}
//帐号更新设置回调
function postUpdateTimeResponse(res)
{
  if(res.code == 1)
  {
    var msg = new Array();

    msg['message'] = "更新时间设置成功";
    msg['timeout'] = 2000;

    showMsg('msg');
    return true;
  }
  else
  {
    var msg = new Array();

    msg['message'] = "更新时间设置失败";
    msg['timeout'] = 2000;

    showMsg(msg);
    return false;
  }
}

/*
 * 帐号密码修改
 */
function modifyPwd(account_id,password,user_name)
{
  Ajax.call('account_manager.php?act=modifyPassword&','behave='+1+'&account_id='+account_id+'&password='+password+'&user_name='+user_name,modifyPwdResponse,'GET','JSON');
}

//帐号密码修改回调
function modifyPwdResponse(res)
{
  document.getElementById('even').innerHTML = res.main;
  EV_modeAlert('even');
}

//提交密码修改
function subPassword()
{
  var obj = document.forms['for_modifyPwd'];
  var account_id = obj.elements['account_id'].value; 
  var newPassword = obj.elements['second_input'].value;
  if(!cheRepeatPwd(newPassword))
  {
    // var msg = new Array();
    // msg['message] = '两次输入密码不一致,请重新输入';
    // msg['timeout'] = 2000;
    //
    // showMsg(msg);
    // return flase;
  }
  else
  {
    Ajax.call('account_manager.php?act=modifyPassword&','behave='+0+'&account_id='+account_id+'&newPassword='+newPassword,subPasswordResponse,'GET','JSON');
  }
} 
//提交密码修改回调
function subPasswordResponse(res)
{
  showMsg(res.message);
}

//取消密码修改
function modifyPwdClose()
{
  EV_closeAlert();
  setTimeout("EV_closeAlert",800);
}

//验证两次输入密码
function cheRepeatPwd(password)
{
  var obj = document.forms['for_modifyPwd'];
  var firstPassword = obj.elements['first_input']; 

  if(password == "")
  {
    document.getElementById('password2').innerHTML = '* 请输入确认密码';
  }
  else
  {
    if(password == firstPassword)
    {
      document.getElementById('password2').innerHTML = '';
      return true;
    }
    else
    {
      document.getElementById('password2').innerHTML = '* 两次输入密码不一致'
        return  false;
    }  
  }  

}

//检查帐号是否可添加
function addable()
{
  var obj = document.forms['for_account'];      //添加帐号表单
  var type_id = obj.elements['type'].value;        //帐号类型
  var user_name = '';                           //帐号名称
  if(type_id == 1)
  {
    user_name = obj.elements['txtqquser_name'].value;
  }
  else
  {
    user_name = obj.elements['user_name'].value;
  }

  Ajax.call('account_manager.php?act=add_able&','type_id='+type_id+'&user_name='+user_name,addableResponse,'GET','JSON');
}

function addableResponse(res)
{
  if(res == 1)
  {
    document.getElementById('qquser_name').innerHTML = '该帐号已经存在,不可重复添加';
    document.getElementById('user_name').innerHTML = '该帐号已经存在,不可重复添加';
  }
  else
  {
    document.getElementById('qquser_name').innerHTML = '可以添加';
    document.getElementById('user_name').innerHTML = '可以添加';
  }
}

//删除Q群
function delgroup(group_id,qqgroup)
{
  Ajax.call('account_manager.php?act=delgroup&','group_id='+group_id+'&qqgroup='+qqgroup,delgroupResponse,'GET','TEXT');
}

function delgroupResponse(res)
{
  if(res == 1)
  {
    alert('删除成功');
  }
  else if(res == 0)
  {
    alert('删除失败');
  }
}

//修改Q群
function updgroup(group_id,qqgroup)
{
  Ajax.call('account_manager.php?act=updgroup&','group_id='+group_id+'&qqgroup='+qqgroup,updgroupResponse,'GET','JSON'); 
}

//修改Q群回调
function updgroupResponse(res)
{
  document.getElementById('even').innerHTML = res.main;
  EV_modeAlert('even');
}


//formObj 将要移动对象 toOjb目标对象
function select_move(formObj,toObj)
{
  if(formObj.selectedIndex != -1)
  {
    toObj.add(new Option(formObj.options[formObj.selectedIndex].text,formObj.options[formObj.selectedIndex].value));
    formObj.remove(formObj.selectedIndex);
  } 
}

function advbatchRes(res)
{
  if(res.code == 1)
    showMsg(res);
  else
  {
    document.getElementById('resource').innerHTML = res.main;
    init();
  }
}

//调节顾客帐号
function modifyAccountInfo()
{
  var obj = document.forms['modify_account_form'];
  //var add_sub_user_money = obj.elements['add_sub_user_money'].value;
  var add_sub_user_money = '';
  //var user_money = obj.elements['user_money'].value;
  var user_money = '';
  var user_rank = obj.elements['user_rank'].value;
  //var add_sub_rank_points = obj.elements['add_sub_rank_points'].value;
  var add_sub_rank_points = '';
  //var rank_points = obj.elements['rank_points'].value;
  var rank_points = '';
  //var add_sub_pay_points = obj.elements['add_sub_pay_points'].value;
  var add_sub_pay_points = '';
  //var pay_points = obj.elements['pay_points'].value;
  var pay_points = '';
  var change_desc = obj.elements['change_desc'].value;
  var user_id = obj.elements['user_id'].value;

  //alert(add_sub_user_money+' '+user_money+' '+add_sub_rank_points+' '+rank_points+' '+add_sub_pay_points+' '+pay_points+' '+user_rank);

  Ajax.call('account_log.php?act=insert&','add_sub_user_money='+add_sub_user_money+'&user_money='+user_money+'&user_rank='+user_rank+'&add_sub_rank_points='+add_sub_rank_points+'&rank_points='+rank_points+'&add_sub_pay_points='+add_sub_pay_points+'&pay_points='+pay_points+'&change_desc='+change_desc+'&user_id='+user_id,modAccountInfo,'GET','JSON');

}

function modAccountInfo(res)
{
  var msg = new Array();
  msg['timeout'] = 2000;
  if(res.code == 1)
  {
    msg['message'] = '确认成功';
  }
  else
  {
    msg['message'] = '确认失败';
  }

  showMsg(msg);
}

//搜索将要批量修改等级的会员
function schBatch(obj)
{
  var min_order_times = Number(obj.elements['min_order_times'].value);
  var max_order_times = Number(obj.elements['max_order_times'].value);
  var min_order_amount = Number(obj.elements['min_order_amount'].value);
  var max_order_amount = Number(obj.elements['max_order_amount'].value);
  var once_order = Number(obj.elements['once_order'].value);
  var role = obj.elements['role'].value;
  var z = k = 0;

  var msg = new Array();
  msg['timeout'] = 2000;

  if(min_order_times=='0'&&max_order_times=='0'&&min_order_amount=='0'&& max_order_amount=='0'&&once_order=='0'&& role =='请选择部门')
  {
    msg['message'] = '搜索值不能为空';
    showMsg(msg);
    return;
  }

  if(min_order_times || max_order_times)
  {
    if(!max_order_times)
    {
      msg['message'] = '购买次数输入有误';
      z = 1;
    }
    else if(min_order_times > max_order_times)
    {
      mes['message'] = '购买次数输入有误';
      z = 1;
    }
  }

  if(min_order_amount || max_order_amount)
  {
    if(!max_order_amount)
    {
      msg['message'] = '成功消费总金额输入有误';
      k = 1;
    }
    else if(min_order_amount > max_order_amount)
    {
      msg['message'] = '成功消费总金额输入有误';
      k = 1;
    }
  }

  if(z == 1 || k == 1)
  {
    showMsg(msg);
  }
  else
  {
    Ajax.call('service.php?act=sch_batch','min_order_times='+min_order_times+'&max_order_times='+max_order_times+'&min_order_amount='+min_order_amount+'&max_order_amount='+max_order_amount+'&once_order='+once_order+'&role_id='+role,schBatchRes,'GET','JSON');
  }
}

function schBatchRes(res)
{
  document.getElementById('btn_submit').value = res.main;
}

function addaccount()
{
  Ajax.call('performance.php','act='+'addaccount',addaccountRes,'GET','JSON');
}

function addaccountRes(res)
{
  document.getElementById('pop_ups').innerHTML = res.info;
  document.getElementById('fade').style.display = 'block';
  document.getElementById('div_pop_ups').style.display = 'block';
  document.getElementById('close_pop').style.display = 'none';
  if(res.account_name != null)
  {
    judge(res.type_id,res.account_name);
    res.account_name = '';
  }
}

//添加密码保护
function addPwdProtect()
{
  var pwdobj = document.getElementById('pwdPro');
  var opt = document.createElement('option');
  opt.value = pwdobj.options.length+1;
  opt.text = '密保'+opt.value;

  Ajax.call('account_manager.php?act=add_pwd_pro','pwd_pro_name='+opt.text,addPwdProRes,'GET','JSON');
}

function addPwdProRes(res)
{
  if(res.code)
  {
    var pwdobj = document.getElementById('pwdPro');
    var opt = document.createElement('option');
    opt.value = res.pwd_pro_id;
    opt.text = res.pwd_pro_name;
    var lgh = pwdobj.options.length - 1;

    pwdobj.appendChild(opt);
    pwdobj.options[opt.value - 1].selected = true;  
  }
}

//设置管理员位置和拥有账号信息
function set_info(obj)
{
}

//授权用户
function belong()
{

}

//排除已经选择的使用者
function delAdmin(user)
{
  var datalist = document.getElementById('admin_list');
  var options = datalist.options;

  for(var i = 0;i < options.length; i++)
  {
    if(options[i].value == user)
    {
      datalist.remove(i);
      break;
    }
  }

  var select = document.getElementById('store_admin');
  var opt = document.createElement('option');
  opt.value = options[i].text;
  opt.text = options[i].value;

  select.appendChild(opt);
}

//获得可选择员工
function setAdmin(user_id,seat)
{
  if(user_id == 0)
  {
    document.getElementById(seat+'_control_field').innerHTML = '<img src="images/contact_add.png" title="添加员工" onclick="setInput('+seat+',0,0)" />';
    document.getElementById(seat+'_admin_field').innerHTML = '';
  }
  else
  {
    Ajax.call('system.php?act=arranged_admin','user_id='+user_id+'&seat='+seat,arrangedAdminRes,'GET','JSON');
  }
}

//所安排的员工是否已有座位回调
function arrangedAdminRes(res)
{
  var user_id = res.admin_id;
  var seat = res.seat;
  var confirm_arrange = 0;
  if(res.code == 1)
  {
    var r = confirm('确认安排'+res.admin_name+'就座'+res.seat);
    if(r)
      confirm_arrange = 1;
  }
  else
    confirm_arrange = 1;

  if(confirm_arrange)
  {
    Ajax.call('system.php?act=set_admin','user_id='+user_id+'&seat='+seat+'&re_seat='+res.re_seat,setAdminRes,'GET','JSON');
  }
}

function setAdminRes(res)
{
  if(res.code)
  {
    document.getElementById(res.seat+'_admin_field').innerHTML = '';
    document.getElementById(res.seat+'_control_field').innerHTML = res.info;
  }
}

//设置表单填写
function setInput(seat,set_value,from_where)
{
  if(from_where == 0)
  {
    Ajax.call('system.php?act=get_temp_admin','seat='+seat+'&user_id='+set_value,getTempAdminList,'GET','JSON');
  }
  else if(from_where == 1)
  {
    document.getElementById(seat+'_pc_control').innerHTML = '<input type="text" name="pc_number" id="'+seat+'_pc_nu" value="'+set_value+'" onblur="setPcNumber(this.value,\''+seat+'\')"/>';
    document.getElementById(seat+'_pc_control').display = '';
    //document.getElementById(seat+'_pc_control').style.display = 'none';
  }
}

//表单填写回调
function getTempAdminList(res)
{
  var obj = document.getElementById(res.seat+'_admin_field');
  obj.innerHTML = res.info;
  obj.focus();
  document.getElementById(res.seat+'_control_field').innerHTML = '';
}

//安排电脑
function setPcNumber(pc_number,seat)
{
  Ajax.call('system.php?act=arrange_pc','pc_number='+pc_number+'&seat='+seat,setPcNumRes,'GET','JSON');
}

function setPcNumRes(res)
{
  document.getElementById(res.seat+'_pc_number_field').style.display= 'none';
  if(res.pc_number != '')
  {
    document.getElementById(res.seat+'_pc_control').innerHTML = '<label class="btn_new" onclick="getPcInfo(this,\''+res.seat+'\')">'+res.pc_number+'</label><img src="images/edit.gif" onclick="setInput(\''+res.seat+'\',\''+res.pc_number+'\',1)" title="修改电脑编号"><img src="images/0.gif" onclick="delPcNum(\''+res.seat+',\''+res.pc_number+'\')" title="删除电脑">';
    document.getElementById(res.seat+'_pc_control').style.display = '';
  }
  else
  {
    document.getElementById(res.seat+'_pc_control').innerHTML = '<img src="images/add_square.png" title="添加电脑" onclick="setInput(\''+res.seat+'\',\'\',1)"/>';
  }
}

//员工拥有帐号信息
function getAdminAccount(obj,user_id)
{
  document.getElementById('will_edit_admin').innerHTML = obj.innerText;
  document.getElementById('account_qq').focus();
  Ajax.call('system.php?act=get_admin_account','user_id='+user_id,getAdminAccountRes,'GET','JSON');
}

function getAdminAccountRes(res)
{
  document.getElementById('will_edit_admin_id').value = res.user_id;
  if(res.account != 0)
  {
    document.getElementById('account_qq').value = res.account_info.qq;
    document.getElementById('account_ppcrm').value = res.account_info.ppcrm ;
    document.getElementById('account_qqcrm').value = res.account_info.qqcrm;
    document.getElementById('account_wangwang').value = res.account_info.wangwang;
    document.getElementById('account_tel').value = res.account_info.tel;
  }
  else
  {
    document.getElementById('account_qq').value = '';
    document.getElementById('account_ppcrm').value = '';
    document.getElementById('account_qqcrm').value = '';
    document.getElementById('account_wangwang').value = '';
    document.getElementById('account_tel').value = '';
  }
}

//删除某座位员工
function delAdmin(user_id,seat)
{
  Ajax.call('system.php?act=del_admin_from_seat','user_id='+user_id+'&seat='+seat,delAdminRes,'GET','JSON');
}

function delAdminRes(res)
{
  document.getElementById(res.seat+'_control_field').innerHTML = res.info;
}

//获取电脑信息
function getPcInfo(obj,pc_number)
{
  document.getElementById('will_edit_pc_number').innerHTML = obj.innerText;
  document.getElementById('pc_ip').focus();
  Ajax.call('system.php?act=get_pc_info','pc_number='+pc_number,getPcInfoRes,'GET','JSON');
}

function getPcInfoRes(res)
{
  if(res.code == 1)
  {
    document.getElementById('pc_ip').value = res.pc_ip;
    document.getElementById('pc_mac').value = res.pc_mac;

    if(res.pc_case_status == 0)
    {
      document.getElementById('pc_case_normal').checked = true;
    }
    else
    {
      document.getElementById('pc_case_abnormal').checked = true;
    }

    if(res.pc_monitor_status == 0)
    {
      document.getElementById('pc_monitor_normal').checked = true;
    }
    else
    {
      document.getElementById('pc_monitor_abnormal').checked = true;
    }
  }
}

//编辑员工拥有的平台账号
function updateAdminAccount(obj)
{
  var admin_id = obj.elements['will_edit_admin_id'].value;
  var qq = obj.elements['account_qq'].value;
  var ppcrm = obj.elements['account_ppcrm'].value;
  var qqcrm = obj.elements['account_qqcrm'].value;
  var wangwang = obj.elements['account_wangwang'].value;
  var tel = obj.elements['account_tel'].value;

  Ajax.call('system.php?act=update_admin_account','admin_id='+admin_id+'&qq='+qq+'&ppcrm='+ppcrm+'&qqcrm='+qqcrm+'&wangwang='+wangwang+'&tel='+tel,updAdminAccountRes,'GET','JSON');
}

function updAdminAccountRes(res)
{
  showMsg(res);
}

//修改电脑信息
function modifyPCInfo(obj)
{
  var pc_number = document.getElementById('will_edit_pc_number').innerText;
  if(pc_number != '')
  {

    var pc_ip = obj.elements['pc_ip'].value;
    var pc_mac = obj.elements['pc_mac'].value;
    var pc_case = obj.elements['pc_case'].value;
    var pc_monitor = obj.elements['pc_monitor'].value;

    Ajax.call('system.php?act=modify_pc_info','pc_number='+pc_number+'&pc_ip='+pc_ip+'&pc_mac='+pc_mac+'&pc_monitor='+pc_monitor+'&pc_case='+pc_case,modPCInfoRes,'GET','JSON');
  }
  else
  {
    document.getElementById('will_edit_pc_number').innerHTML = '<font color="red">请选择要修改的电脑</font>';
  }
}

function modPCInfoRes(res)
{
  showMsg(res);
}

//修改账号模块
function modifyAccount(account_id)
{
  Ajax.call('performance.php?act=modify_account','account_id='+account_id,addaccountRes,'GET','JSON');
}

//获取授权查看员工
function getBelong(obj)
{
  var index = obj.selectedIndex; // 选中索引
  var name = obj.options[index].text; // 选中文本
  var box_field = document.getElementById('belong_field');
  var box_innerHtml = box_field.innerHTML;
  var span_admins = box_field.getElementsByTagName('span');
  var i = 0
    for(;i<span_admins.length;i++)
    {
      if(span_admins[i].getElementsByTagName('span').id == obj.value)
      {
        break;
      }
    }

  if(i >= span_admins.length && obj.value != 0)
  {
    box_field.innerHTML = box_innerHtml+'<span name="'+obj.value+'" id="'+obj.value+'"><img src="images/0.gif" onclick="delBelong(this)">'+name+'</span>';
  }
}

//移除授权员工
function delBelong(obj)
{
  var _div = obj.parentNode.parentNode; //div
  var _span = obj.parentNode;

  _div.removeChild(_span);
}

//添加新的账号用途
function addNewPurpose()
{
  var new_purpose = prompt('请输入新的用途名','');
  if(new_purpose !='' && new_purpose != null)
  {
    var obj_purpose = document.getElementById('account_purpose');
    for(var i = 0; i < obj_purpose.options.length; i++)
    {
      if(new_purpose == obj_purpose.options[i].value)
      {
        var msg = new Array();
        msg['timeount'] = '2000';
        msg['message'] = '已经存在相同用途，请重新输入';
        showMsg(msg);
        return ;
      }
    }

    if(i >= obj_purpose.options.length)
    {
      Ajax.call('account_manager.php?act=add_purpose','purpose_name='+new_purpose,addPurposeRes,'GET','JSON');
    }
  }
}

function addPurposeRes(res)
{
  if(res.code == 1)
  {
    var obj_purpose = document.getElementById('account_purpose');
    var opt = document.createElement('option');

    opt.value = res.purpose_id;
    opt.text = res.purpose_name;
    obj_purpose.appendChild(opt);

    var sel_purpose_id = obj_purpose.options.length - 1;
    obj_purpose.options[sel_purpose_id].selected = true;  
  }
}

//删除账号
function delAccount(obj,account_id)
{
  var row_id = obj.parentNode.parentNode.rowIndex;
  alert(row_id);
  obj.parentNode.parentNode.parentNode.id = 'table'+row_id;

  Ajax.call('account_manager.php?act=del_account','row_id='+row_id+'&account_id='+account_id,delAccountRes,'GET','JSON');

}

function delAccountRes(res)
{
  showMsg(res);

  if(res.code)
  {
    var obj_table = document.getElementById('table'+res.row_id);
    obj_table.deleteRow(res.row_id);
  }
}

//确认提现、充值申请
function confirmCheck(obj,obj_tr)
{
  var id = obj.elements['id'].value;
  var is_paid = obj.elements['is_paid'].value;
  var admin_note = obj.elements['admin_note'].value;
  var tr_id = obj_tr.rowIndex;
  
  if(admin_note != '')
    Ajax.call('user_account.php?act=action','id='+id+'&is_paid='+is_paid+'&admin_note='+admin_note+'&tr_id='+tr_id,confirmCheckRes,'POST','JSON');
  else
    return ;
}

function confirmCheckRes(res)
{
  close_pop();
  showMsg(res);
  if(res.code)
  {
    var obj_table = document.getElementById('pay_point_list');
    obj_table.deleteRow(res.rr_id);
  }
}
