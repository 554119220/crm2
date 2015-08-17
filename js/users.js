/**
 * 转赠顾客
 */
function userGivenTo ()
{
  var toId = document.getElementById('given_to').value;
  var uid = document.getElementById('ID').value;
  if (toId) {
    Ajax.call('users.php', 'act=give_up&to_id='+toId+'&uid='+uid, userGivenToResponse, 'POST', 'JSON');
  }
}

/**
 * 转赠顾客  回调函数
 */
function userGivenToResponse (res)
{
  if (res.code == 1) {
    removeRow('detail');
    removeRow('tr_'+res.id);
  }

  init();
  showMsg(res);
}

// 发送短信
var maxLen = 70;
var msg;
function increaseWords ()
{
  msg = document.getElementById('sms');
  var increase = document.getElementById('increase_words');
  if(increase.checked) {
    maxLen = 140; msg.maxLength = maxLen; notice();
  } else {
    maxLen = 70; msg.maxLength = maxLen; notice();
  }
}

function notice ()
{
  msg = document.getElementById('sms');
  document.getElementById('notice').innerHTML = '<strong>还剩下<font color=red>' + (maxLen - msg.value.length) + '</font>个字符</strong>';
}

function sendSms ()
{
  var data = 'act=send_sms&' + collectTheFormData().join('&');

  Ajax.call('service.php', data, sendSmsResp, 'POST', 'JSON');
  return false;
}

/**
 * 发送短信  回调函数
 */
function sendSmsResp (res)
{
  showMsg(res);
  if (res.code == 1) {
    var f = document['sms-send-form'];
    f.elements['sms'].value = '';
  }
}

/**
 * 统计短信条数
 */
function statsSMSNumber() {
  var smsObj = document.getElementById('sms');

  var data = 'act=send_sms_count&'+collectTheFormData().join('&');
  if (smsObj.value.length > 1) {
    Ajax.call('service.php', data, statsSMSNumberResp, 'POST', 'JSON');
  }
}

function statsSMSNumberResp(res) {
  showMsg(res);
}

/**
 * 获取短信表单数据
 */
function collectTheFormData () {
  var f      = document['sms-send-form'];
  var data   = [];

  if (f.elements.send_num) {
    var mobile = f.elements.send_num.value.split(',');

    if (mobile.length > 1) {
      for (var i = mobile.length -1; i >= 0; i--) {
        if (! /^1[34578]\d{9}$/.test(mobile[i])) {
          showMsg({req_msg:true,message:mobile[i]+'不是有效的手机号码！'});
          return false;
        }
      }
    }
  }

  for (var i = f.elements.length -3; i >= 0; i--) {
    if (f.elements[i].name) {
      if ((f.elements[i].type == 'checkbox' || f.elements[i].type == 'radio') && f.elements[i].checked) {
        data.push(f.elements[i].name+'='+f.elements[i].value);
      }

      if (f.elements[i].type != 'checkbox' && f.elements[i].type != 'radio' && f.elements[i].value) {
        data.push(f.elements[i].name+'='+f.elements[i].value);
      }
    }
  }

  return data;
}

/**
 * 添加新的顾客分类
 */
function addUserCat ()
{
  var theForm = document['user_cat'];
  var cat_name = theForm.elements['cat_name'].value;
  var cat_desc = theForm.elements['cat_desc'].value;

  if (!cat_name || !cat_desc){
    var msg = new Array;
    msg.timeout = 2000;
    msg.message = '请提供分类名称和分类说明！';
    showMsg(msg);
  }

  if (cat_name.length > 5){
    var msg = new Array;
    msg.timeout = 2000;
    msg.message = '自定义分类的名称不能超过5个字！';
    showMsg(msg);
  }

  Ajax.call('users.php', 'act=insert_user_cat&cat_name='+cat_name+'&cat_desc='+cat_desc, addUserCatResponse, 'POST', 'JSON');
}

/**
 * 添加自定义分类 (回调函数)
 */
function addUserCatResponse (res)
{
  showMsg(res);
  if (res.code)
  {
    var tableObj = document.getElementById('user_cat');
    var theForm = document['user_cat'];
    var trObj = tableObj.insertRow(tableObj.rows.length);

    //tdObj = trObj.insertCell(0);
    //tdObj.innerHTML = '<a href="'+res.cat_id+'" >删除</a>';

    tdObj = trObj.insertCell(0);
    tdObj.innerHTML = '<img src="images/1.gif" />';

    tdObj = trObj.insertCell(0);
    tdObj.innerHTML = res.cat_desc;

    tdObj = trObj.insertCell(0);
    tdObj.innerHTML = res.cat_name;

    tdObj = trObj.insertCell(0);
    tdObj.innerHTML = '↑';
  }
}

/**
 * 顾客分类
 */
function changeCat (uid)
{
  Ajax.call('users.php', '&act=change_cat&user_id='+uid, changeCatResponse, 'POST', 'JSON');
}

/**
 * 顾客分类 (回调函数)
 */
function changeCatResponse (res)
{
  showMsg(res);
}

/**
 * 提交新分类
 */
function submitNewCat ()
{
  var cat = document['change_cat'].getElementsByTagName('input');
  var uid = document.getElementById('user_id').value;
  for (var i=0; i<cat.length-1; i++) {
    if (cat[i].checked) {
      cat = cat[i].value; break;
    }
  }

  Ajax.call('users.php','act=update_cat&cat='+cat+'&user_id='+uid, submitNewCatResponse, 'POST', 'JSON');
}

/**
 * 提交新分类 (回调函数)
 */
function submitNewCatResponse (res)
{
  showMsg(res);
  removeRow('tr_'+res.tr_id);
  if (document.getElementById('detail')) {
    removeRow('detail');
  }
  init();
}

/**
 * 动态获取配送方式
 */
function getShipping (obj)
{
  if (obj.value == 0) return false;
  Ajax.call('order.php?act=get_shipping_list&pay_id='+obj.value, '', getShippingResponse, 'GET', 'JSON');
}

function getShippingResponse (res)
{
  var selObj = document.getElementById('shipping_id');
  selObj.length = 0;
  var opt = document.createElement('option');
  opt.text = '配送方式';
  opt.value = 0;
  selObj.add(opt);

  for (var i=0; i<res.length; i++){
    var opt = document.createElement('option');
    opt.text = res[i].shipping_name;
    opt.value = res[i].shipping_id;
    selObj.add(opt);
  }
}

/**
 * 介绍人详细信息
 */
function referralsDetail (uid) {
  Ajax.call('users.php?act=referrals_detail&id='+uid, '', referralsDetailResp, 'GET', 'JSON');
  return false;
}

/**
 * 介绍人详细信息  回调函数
 */
function referralsDetailResp (res) {
  showMsg(res);
}

/**
 * 查询介绍人
 */
function checkParentUser (obj) {
  if (obj.name != 'parent_id' || obj.value.length < 6) {
    return false;
  }

  if (obj.value) {
    Ajax.call('users.php?act=find_referrer&keywords='+obj.value, '', checkParentUserResp, 'GET', 'JSON');
  }
}

function checkParentUserResp (res) {
  console.log(res.length);
  if (res.length) {
  };
}

//添加网络黑名单单列表
function addNetworkBlacklist(obj)
{
  var msg        = new Array();
  msg['timeout'] = 2000;

  var user_name = obj.elements['user_name'].value;
  var item_type = obj.elements['item_type'].value;
  var number    = obj.elements['number'].value;
  var type_id = obj.elements['type_id'].value;

  if (0 == type_id) {
    msg['message'] = '请选择欺骗类型';
    showMsg(msg);
    return false;
  }

  if(user_name != '')
    Ajax.call('users.php?act=putin_network_blacklist','user_name='+user_name+'&item_type='+item_type+'&number='+number+'&type_id='+type_id,addNetworkBlacklistResp,'GET','JSON');
  else{
    msg['message'] = '顾客姓名不能为空';
    showMsg(msg);
    return false;
  }
}

function addNetworkBlacklistResp(res){
  showMsgRes(res);
  getNetworkBlacklist();
}

/*从网络黑名单单中移除*/
function modDelNetworkBlacklist(id){
  if(id > 0){
    var r = confirm('确定要删除黑名单？');
    if(r){
      Ajax.call('users.php?act=mod_del_network_blacklsit','id='+id,addNetworkBlacklistResp,'GET','JSON');
    }else
      return ;
  }else{
    return ;
  }
}

//将顾客加入黑名单模板
function putInBlacklist(user_id,user_name)
{
  if(user_id != null && user_id != 0)
  {
    Ajax.call('account_manager.php?act=put_in_black_htm','user_id='+user_id+'&user_name='+user_name,putInBlacklistHtmRes,'GET','JSON');
  }
}

function putInBlacklistHtmRes(res) {
  showPop(res);
}

//将顾客加入黑名单
function putInBlack(user_id)
{
  var blacklist_type = document.getElementById('blacklist_type').value;
  var reason         = document.getElementById('reason').value;

  if(user_id != null && user_id != 0)
  {
    Ajax.call('account_manager.php?act=put_in_black','user_id='+user_id+'&blacklist_type='+blacklist_type+'&reason='+reason,putInBlacklistRes,'GET','JSON');
  }
}

function putInBlacklistRes(res) {
  close_pop();
  showMsg(res);
}

//搜索黑名单;
function schBlacklist(obj)
{
  var user_name = document.getElementById('user_name').value;
  var phone = document.getElementById('phone').value;
  var operator_in = document.getElementById('admin_id').value;
  var blacklist_status = document.getElementById('blacklist_status').value;
  blacklist_status = blacklist_status ==  0 ? 2 : 0;

  Ajax.call('account_manager.php?act=sch_blacklist','user_name='+user_name+'&phone='+phone+'&operator_in='+operator_in+'&blacklist_status='+blacklist_status,schBlacklistRes,'GET','JSON');
}

function schBlacklistRes(res)
{
  document.getElementById('resource').innerHTML = res.main;
  init();
}

function checkBlack(user_id,user_name,obj,do_what){
  var row_id = obj.parentNode.parentNode.rowIndex;
  var r = null;
  if(do_what){
    r = confirm('确定撤消【'+user_name+'】进入黑名单的申请');
  }else{
    r = confirm('确认要将【'+user_name+'】拉入黑名单');
  }
  if(r){
    Ajax.call('users.php?act=check_blacklist','user_id='+user_id+'&row_id='+row_id+'&do_what='+do_what,moveOutBlackRes,'GET','JSON');
  }
}


//移出黑名单
function moveOutBlack(user_id,user_name,obj)
{
  var row_id = obj.parentNode.parentNode.rowIndex;
  var r = confirm('确定要将'+user_name+'从黑名单中移出');
  if(r) {
    Ajax.call('account_manager.php?act=del_blacklist','user_id='+user_id+'&row_id='+row_id,moveOutBlackRes,'GET','JSON');
  }
}

function moveOutBlackRes(res)
{
  showMsg(res);
  if(res.code) {
    var obj_table = document.getElementById('blacklist_table');
    obj_table.deleteRow(res.row_id);
  }
}

//根据状态查看黑名单顾客
function getBlacklistStatUser(blackStatus){
  Ajax.call('users.php?act=user_blacklist','status='+blackStatus+'&from_tab='+'from_tab',statusUserRes,'POST','JSON');
}

function statusUserRes(res){
  var obj = document.getElementById('blacklist_status');
  if(res.blackstatus == 0){
    obj.innerHTML = '已审核';
    obj.value = 2;
  } else {
    obj.innerHTML = '未审核';
    obj.value = 0;
  }

  document.getElementById('resource').innerHTML = res.main;
  init();
}

//判断是否在黑名单
function inBlacklist(home_phone,mobile_phone,qq,aliww)
{
  if(home_phone != '' || mobile_phone != '' || qq != '' || aliww != '') {
    Ajax.call('users.php?act=in_blacklist','home_phone='+home_phone+'&mobile_phone='+mobile_phone+'&qq='+qq+'&aliww='+aliww,inBlacklistRes,'GET','JSON');
  }
}

function inBlacklistRes(res)
{
  if(res.code) {
    switch(res.account_type) {
      case 'infos' :
        document.getElementById('infos').innerHTML ='<b><font color="red"> 该手机号码已经被列入黑名单，请谨慎添加</font></b>';
        break;
      case 'qq_alarm' :
        document.getElementById('qq_alarm').innerHTML = '<b><font color="red"> 该QQ已经被列入黑名单，请谨慎添加</font></b>';
        break;
      case 'aliww_alarm' :
        document.getElementById('qq_alarm').innerHTML = '<b><font color="red"> 该QQ已经被列入黑名单，请谨慎添加</font></b>';
        break;
    }
  } else {
    return false;
  }
}

//导入黑名单模板
function putAccountInBlack(behave) {
  if(behave == 'model'){
    Ajax.call('users.php?act=put_account_in_black','behave='+behave,putAccountInBlackRes,'GET','JSON');
  }
}

function putAccountInBlackRes(res) {
  showPop(res);
}



/**
 * 添加顾客到回收站
 */
function addToRecycle (obj) {
  var userListObj = document.getElementById('user_list');
  if (!obj.checked) {
    checkboxList = userListObj.getElementsByTagName('input');
    for (var i = checkboxList.length - 1; i >= 0; i--){
      if (checkboxList[i].value == obj.value) {
        checkboxList[i].onclick = function () {
          checkboxList[i].parentNode.parentNode.parentNode.removeChild(checkboxList[i].parentNode.parentNode);
        }();
      }
    }
  } else {
    var liObj    = document.createElement('li');
    var labelObj = document.createElement('label');
    var inputObj = document.createElement('input');
    var spanObj  = document.createElement('span');

    inputObj.value = obj.value;
    inputObj.name  = 'user_id[]';
    inputObj.type  = 'checkbox';

    spanObj.innerText = '×';
    spanObj.onclick = function () {
      this.parentNode.parentNode.removeChild(this.parentNode);
    };

    labelObj.appendChild(inputObj);
    labelObj.innerHTML += obj.parentNode.nextSibling.nextSibling.innerText;

    liObj.appendChild(labelObj);
    liObj.appendChild(spanObj);

    userListObj.appendChild(liObj);
  }
}

/**
 * 转顾客给
 */
function sendUsersTo () {
  var theForm = document.forms['usersStash'];
  if (theForm.elements['admin_id'].value <= 0) {
    var msg = {req_msg:true, timeout:2000, message:'请选择要转给的客服！'};
    showMsg(msg);
  }

  var boxObj = document.getElementById('user_list');
  var userListObj = boxObj.getElementsByTagName('input');

  var userList = [];
  for (var i = userListObj.length - 1; i >= 0; i--){
    if (userListObj[i].checked) {
      userList.push(userListObj[i].value);
    }
  }

  if (userList.length <= 0) {
    var msg = {req_msg:true, timeout:2000, message:'请选择要转走的顾客！'};
    showMsg(msg);
  }

  userList = JSON.stringify(userList);

  Ajax.call('users.php', 'act=send_users&user_list='+userList+'&send_to='+theForm.elements['admin_id'].value, sendUsersToResp, 'POST', 'JSON');
}

function sendUsersToResp (res) {
  if (res.user_list) {
    var userList = res.user_list.replace(/[^\d,]/g, '').split(',');
    var checkboxList = document.getElementById('user_list').getElementsByTagName('input');
    for (var j = userList.length - 1; j >= 0; j--){
      for (var i = checkboxList.length - 1; i >= 0; i--) {
        if (userList[j] == checkboxList[i].value) {
          checkboxList[i].onclick = function () {
            checkboxList[i].parentNode.parentNode.parentNode.removeChild(checkboxList[i].parentNode.parentNode);
          }();

          userList.pop();
          break;
        }
      }
    }
  }

  showMsg(res);
}

function showThis (obj, dstWidth) {
  clearInterval(obj.action);
  var speed = dstWidth > 0 ? 10 : -10;
  obj.action = setInterval(function () {
    if (obj.offsetWidth == dstWidth) {
      clearInterval(obj.action);
    } else {
      obj.style.width = obj.offsetWidth + speed + 'px';
    }
  }, 10);
}

function stopAction (obj) {
  if (obj.onmouseout == null || obj.onmouseover == null) {
    obj.onmouseover = function () {
      showThis(this, 170);
    };

    obj.onmouseout = function () {
      showThis(this, 0);
    };
  } else {
    obj.onmouseout  = null;
    obj.onmouseover = null;
  }
}

/**
 * 保存联系方式
 */
function saveContactInfo () {
  var theForm = document.forms['contactInfo'];
  var rowObj  = document.getElementById(theForm.elements['contact_name'].value+'Row');

  var selectedIndex = theForm.elements['contact_name'].selectedIndex;
  var region = '';

  if (! theForm.elements['contact_value'].value || /^\ +$/g.test(theForm.elements['contact_value'].value)) {
    showMsg({req_msg:true,timeout:2000,message:'所添加的内容不能为空！'});
    return false;
  }

  if (theForm.elements['contact_name'].value != 'addr') {
    var saveOk = null;
    for (var i=0; i < rowObj.cells.length; i++) {
      if (rowObj.cells[i].innerHTML.length < 2) {
        saveOk = null;
        break;
      } else if (rowObj.cells.length >= 5) {
        saveOk = true;
      }
    }
    if (saveOk) {
      showMsg({req_msg:true,timeout:2000,message:'该顾客的'+theForm.elements['contact_name'].options[selectedIndex].text+'数量达到上限!'});
      return false;
    }
    switch (theForm.elements['contact_name'].value) {
      case 'email' :
        if (!/^\w{5,}@\w+(\.\w{2,})+$/g.test(theForm.elements['contact_value'].value)) {
          showMsg({req_msg:true,timeout:2000,message:'请填写正确的邮件地址！'});
          return false;
        }
        break;
      case 'tel' :
        if (!/^(\d{3,4}-)?\d{6,8}$/g.test(theForm.elements['contact_value'].value)) {
          showMsg({req_msg:true,timeout:2000,message:'请填写正确的电话号码！'});
          return false;
        }
        break;
      case 'mobile' :
        if (!/^1\d{10}$/g.test(theForm.elements['contact_value'].value)) {
          showMsg({req_msg:true,timeout:2000,message:'请填写正确的手机号码！'});
          return false;
        }
        break;
        //case 'aliww' : break;
        //case 'wechat' : break;
      case 'qq' :
        if (!/^\d{6,11}$/g.test(theForm.elements['contact_value'].value)) {
          showMsg({req_msg:true,timeout:2000,message:'请填写正确的QQ号码！'});
          return false;
        }
    }
  } else {
    var prov = document.getElementById('selProvinces').value;
    var city = document.getElementById('selCities').value;
    var dist = document.getElementById('selDistricts').value;
    region = '&prov='+prov+'&city='+city+'&dist='+dist;
  }

  var userid  = document.getElementById('ID').value;
  var data    = theForm.elements['contact_name'].value + ':' + theForm.elements['contact_value'].value;

  Ajax.call('users.php', 'act=add_contact&user_id='+userid+'&data='+data+region, saveContactInfoResp, 'POST', 'JSON');
  return false;
}

function saveContactInfoResp (res) {
  if (res.code) {
    var rowObj = document.getElementById(res.field+'Row');
    if (res.field != 'addr') {
      for (var i=0; i < rowObj.cells.length; i++) {
        if (rowObj.cells[i].innerHTML.length < 2) {
          rowObj.cells[i].innerHTML = res.value;
          break;
        }
      }

      if (res.field == 'qq') {
        var rowObj = document.getElementById('tencentRow');
        for (var i=0; i < rowObj.cells.length; i++) {
          if (rowObj.cells[i].innerHTML.length < 2) {
            rowObj.cells[i].innerHTML = '<a href="tencent://message/?uin='+res.value+'" name="msg"><img src="http://wpa.qq.com/pa?p=1:'+res.value+':17"></a>';
            break;
          }
        }
      }
    } else {
      // 新增地址
      var addrRowObj = document.getElementById('addrRow');
      var theForm = document.forms['contactInfo'];
      var prov = theForm.elements['province'].options[theForm.elements['province'].selectedIndex].text;
      var city = theForm.elements['city'].options[theForm.elements['city'].selectedIndex].text;
      var dist = theForm.elements['district'].options[theForm.elements['district'].selectedIndex].text;
      var addr = theForm.elements['contact_value'].value;

      if (addrRowObj.cells[1].innerHTML.length > 2) {
        var tbodyObj   = rowObj.parentNode;
        var newRowObj  = document.createElement('tr');
        var newCellObj = document.createElement('td');

        newCellObj.colSpan      = rowObj.cells[1].colSpan;
        rowObj.cells[0].rowSpan = rowObj.cells[0].rowSpan ? rowObj.cells[0].rowSpan + 1 : 2;

        newCellObj.innerHTML = prov+city+dist+addr;

        newRowObj.appendChild(newCellObj);
        tbodyObj.appendChild(newRowObj);
      } else {
        addrRowObj.cells[1].innerHTML = prov+city+dist+addr;
      }

      // 在地址列表中添加新增的地址项
      var selectObj = document.getElementById('addrList');
      var newOpt    = document.createElement('option');
      newOpt.value  = res.insert_id;
      newOpt.text   = prov+city+dist+addr;

      selectObj.appendChild(newOpt);
    }
    rowObj.className = '';
  }

  showMsg(res);
}

/**
 * 更换收货地址
 */
function changeAddr (obj) {
  if (obj.value > 0) {
    Ajax.call('users.php?act=get_addr&addr_id='+obj.value, '', changeAddrResp, 'GET', 'JSON');
  }
}

function changeAddrResp (res) {
  var theForm = document.forms['order_info'];
  // 省
  for (var i = theForm.elements['province'].options.length - 1; i >= 0; i--){
    theForm.elements['province'].options[i].selected = false;
    if (res.prov == theForm.elements['province'].options[i].value) {
      theForm.elements['province'].options[i].selected = true;
      theForm.elements['province'].onchange = function () {
        region.loadRegions(res.prov, 2, 'selCities');
      }();
    }
  }

  // 市
  setTimeout(function () {
    for (var i = theForm.elements['city'].options.length - 1; i >= 0; i--){
      theForm.elements['city'].options[i].selected = false;
      if (res.city == theForm.elements['city'].options[i].value) {
        theForm.elements['city'].options[i].selected = true;
        theForm.elements['city'].onchange = function () {
          region.loadRegions(res.city, 3, 'selDistricts');
        }();
      }
    }
  }, 150);

  // 区
  setTimeout(function () {
    for (var i = theForm.elements['district'].options.length - 1; i >= 0; i--){
      theForm.elements['district'].options[i].selected = false;
      if (res.dist == theForm.elements['district'].options[i].value) {
        theForm.elements['district'].options[i].selected = true;
        theForm.elements['district'].onchange = function () {
          theForm.elements['address'].value = res.addr;
        }();
      }
    }
  },350);
}

function loadRegionsResp (res) {
  var selectObj = document.getElementById(res.target);
  for (var i = 0; i <= res.regions.length; i++) {
    var newOpt = document.createElement('option');
    try {
      if (res.regions[i].region_id) {
        newOpt.value = res.regions[i].region_id;
        newOpt.text  = res.regions[i].region_name;
      }
    } catch (ex) {
    }

    selectObj.appendChild(newOpt);
  }
}

function setDefault(obj) {
  Ajax.call('users.php', 'act=set_default&cid='+obj.getAttribute('cid'), setDefaultResp, 'POST', 'JSON');
}

function setDefaultResp(res) {
  showMsg(res);
  var rowObj = document.getElementById(res.vid+'Row');
  for (var i = rowObj.cells.length -1; i > 0; i--) {
    rowObj.cells[i].getElementsByTagName('span')[0].className = 'pointer';

    if (rowObj.cells[i].getElementsByTagName('span')[0].getAttribute('cid') == res.cid) {
      rowObj.cells[i].getElementsByTagName('span')[0].className = 'hide';
    }
  }
}

/**
 * 顾客资料处理
 */
function addUserIntoUserList() {
  var theForm = document.forms['data-users'];
  var data = [];
  var feed = null;
  for (var i = theForm.elements['feed'].length -1; i >= 0; i--) {
    if (theForm.elements['feed'][i].checked) {
      data.push('feed='+theForm.elements['feed'][i].value);
      feed = theForm.elements['feed'][i].value;
    }
  }

  if (feed == null) {
    showMsg({req_msg:true,timeout:2000,message:'请选择顾客的当前状态！'});
    return false;
  } else {
    data.push('rec_id='+theForm.elements['rec_id'].value);
  }

  if (feed == 4) {
    var sex  = null;
    for (var i = 0; i < theForm.elements.length -2; i++) {
      if (theForm.elements[i].name == 'feed' || theForm.elements[i].name == 'rec_id') {
        continue;
      }
      if (theForm.elements[i].type == 'radio' && theForm.elements[i].checked) {
        if (theForm.elements[i].name == 'sex') {
          data.push(theForm.elements[i].name+'='+theForm.elements[i].value);
          sex = 1;
        }
      } else if (theForm.elements[i].type != 'radio' && theForm.elements[i].name && theForm.elements[i].value) {
        data.push(theForm.elements[i].name+'='+theForm.elements[i].value);
      } else {
        switch (theForm.elements[i].name) {
          case 'user_name'  :
            showMsg({req_msg:true,timeout:2000,message:'请填写顾客姓名！'});
            return false;
          case 'home_phone'   :
          case 'mobile_phone' :
            if ((!theForm.elements['mobile_phone'].value) && (!theForm.elements['home_phone'].value)) {
              showMsg({req_msg:true,timeout:2000,message:'请填写手机或固话（二选一）！'});
              return false;
            }
            break;
          case 'province' :
          case 'city'     :
          case 'address'  :
            showMsg({req_msg:true,timeout:2000,message:'请选择并填写顾客的地址信息！'});
            return false;
          case 'sex'  : break;
          default :
                        if (theForm.elements[i].name && theForm.elements[i].name != 'district') {
                          showMsg({req_msg:true,timeout:2000,message:'请检查/仔细所填（选）内容！'});
                          return false;
                        }
        }
      }
    }

    if (sex == null) {
      showMsg({req_msg:true,timeout:2000,message:'请选择顾客的性别！'});
      return false;
    }
  }

  Ajax.call(theForm.action, data.join('&'), addUserIntoUserListResp, 'POST', 'JSON');
  Ajax.call('users.php?act=data_users&is_ajax=1', '', showNewUserDataResp, 'GET', 'JSON');
  return false;
}

function addUserIntoUserListResp(res) {
  showMsg(res);
}

function showNewUserDataResp (res) {
  document.getElementById('data-users').innerHTML = res.main;
}

function details (obj) {
  var open = false;
  if (obj.value == 4) {
    var open = true;
  }

  document.getElementById('save-details').open = open;
}

/**
 * 顾客回访
 */
function userTrace(obj) {
  Ajax.call('users.php', 'act=first_trace&trace_time='+obj.value, userTraceResp, 'POST', 'JSON');
}

function userTraceResp(res) {
}

/**
 * 列出客服自定义的分组
 */
function getAdminList(obj) {
  var role_id  = obj.elements['roles'].value;
  var group_id = obj.elements['group_id'].value;
}

/**
 * 显示顾客列表
 */
function showUserList (url) {
  Ajax.call(url, '',  showUserListResp, 'GET', 'JSON');
  return false;
}

function showUserListResp(res) {
  document.getElementById('rightShowArea').innerHTML = res.main;
  init();
}


/*搜索重复顾客*/
function schRepeatUser(obj){
  var elementsList = obj.elements;
  var data = new Array();
  for(var i = 0; i < elementsList.length - 3; i++){
    if (elementsList[i].type == 'checkbox' ) {
      if (elementsList[i].checked) {
        data.push(elementsList[i].name + '=' + elementsList[i].value);
      }
      continue;
    }else{
      data.push(elementsList[i].name + '=' + elementsList[i].value);
    }
  }
  data.push('behave=' + 'sch_repeat_user');
  Ajax.call('users.php?act=user_combine',data.join('&'),schRepeatUserResp,'POST','JSON');
}

function schRepeatUserResp(res){
  if(res.req_msg == true){
    showMsg(res);
  }else{
    document.getElementById('resource').innerHTML = res.main;
    var obj = document.getElementById('user_id_list');
    obj.innerHTML = '';
    if (res.user_id_list) {
      var opt = document.createElement('option');
      opt.value = 0;
      opt.text = '顾客ID';
      obj.appendChild(opt);
      for(var i in res.user_id_list){
        if (i !='toJSONString') {
          var opt = document.createElement('option');
          opt.value = res.user_id_list[i];
          opt.text = res.user_id_list[i];
          obj.appendChild(opt);
        }
      }
    }
  }
}


/*比较顾客*/
function compareUser(){
  var mainDiv   = document.getElementById('main');
  var inputList = document.getElementsByTagName('input');
  var userList  = new Array();

  for(var i = 0; i < inputList.length; i++){
    if(inputList[i].type == 'checkbox' && inputList[i].name == 'checkbox'){
      if(inputList[i].checked == true){
        userList.push(inputList[i].value);
      }
    }
  }

  if(userList.length < 2){
    var msg = new Array();
    msg['timeout'] = 2000;
    msg['message'] = '请选择两个顾客';
    showMsg(msg);
    return;
  }else{
    Ajax.call('users.php?act=compare_user','user_id_list='+userList.join(','),compareUserResp,'GET','JSON');
  }
}

function compareUserResp(res){
  if(res.req_msg){
    showMsg(res);
  }else{
    document.getElementById('compare_user_div').innerHTML = res.main;
  }
  return ;
}

/*合并顾客设置*/
function combineUser(){
  var mainDiv   = document.getElementById('main');
  var inputList = document.getElementsByTagName('input');
  var toUserId = document.getElementById('user_id_list').value;
  var userList  = new Array();
  var userName  = new Array();

  for(var i = 0; i < inputList.length; i++){
    if(inputList[i].type == 'checkbox' && inputList[i].name == 'checkbox'){
      if(inputList[i].checked == true){
        userList.push(inputList[i].value);
        userName.push(inputList[i].title);
      }
    }
  }

  var msg        = [];
  msg['tomeout'] = 2000;
  if(userList.length>0){
    //var r = confirm('确定要把【'+userName.join(',')+'】合并给'+toUserId);
    //if(r){
      Ajax.call('users.php?act=user_combine','original_user_id='+userList.join(',')+'&to_user_id='+toUserId+'&behave='+'combine_user',combineUserResp,'GET','JSON');
    //}else{
     //return ;
    //}
  }else{
    msg['message'] = '合并失败';
    showMsg(msg);
    return ;
  }
}

function combineUserResp(res){
  showMsg(res);
  document.getElementById('compare_user_div').innerHTML = '';
  if(document.getElementById('combine_user_config')){
    document.getElementById('combine_user_config').innerHTML = '';
  }

  var obj = document.forms['sch_repeat_user'];
  schRepeatUser(obj);
}

//合并设置
function setCombineConfig(){
  var mainDiv    = document.getElementById('main');
  var inputList  = document.getElementsByTagName('input');
  var userID     = new Array();

  for(var i = 0; i < inputList.length; i++){
    if(inputList[i].type == 'checkbox' && inputList[i].name == 'checkbox'){
      if(inputList[i].checked == true){
        userID.push(inputList[i].value);
      }

      if(userID.length == 2){
        break;
      }
    }
  }

  if(userID.length < 2){
    document.getElementById('compare_user_div').innerHTML = '';
  }
}

/*转顾客员工选择*/
function getBySendAdmin(obj){
  if(obj.elements['admin_name'] && obj.elements['admin_name'].value.length >= 2){
    var sltObj = document.getElementById('admin_id');
    sltObj.length = 0;
    var optObj   = document.createElement('option');
    optObj.value = 0;
    optObj.text  = '请选择';
    sltObj.appendChild(optObj);

    var optObj   = document.createElement('option');
    optObj.value = 74;
    optObj.text  = '临时账号';
    sltObj.appendChild(optObj);

    Ajax.call('users.php?act=get_by_send_admin','admin_name='+obj.elements['admin_name'].value,getAdminListResp,'GET','JSON');
  }else{
    return ;
  }
}

/*解除黑名单警报*/
function ignoreError(user_id,btn){
  var from_table = btn.getAttribute('note');
  if(from_table != '' && user_id !=''){
    Ajax.call('users.php?act=ignore_blacklist','user_id='+user_id+'&from_table='+from_table,ignoreErrorResp,'GET','JSON');
  }
}

function ignoreErrorResp(res){
  if(res.code){
    var btn =  document.getElementById('ignore_blacklist');
    btn.value = res.is_black == 3 ? '恢复警报' : '排除警报';
    btn.parentNode.parentNode.style.background = res.is_black == 3 ? '#F77474' : 'red';

    document.getElementById('error_msg').innerHTML = res.error_msg;

    if(document.getElementById('blacklist_ignore_div')){
      var obj = document.getElementById('blacklist_ignore_div');
      obj.style.display = res.is_black == 3 ? '' : 'none';
    }else{
      return ;
    }
  }else{
    return ;
  }
}

/*列出网络黑名单顾客*/
function getNetworkBlacklist(){
  Ajax.call('users.php?act=get_network_blacklist','',fullSearchResponse,'GET','JSON');
}

/*切换黑名单*/
function showBlacklist(obj) {
  var ul = obj.parentNode;
  for (var i in ul.children) {
    if (i != 'length') {
      ul.children[i].className = '';
      if (ul.children[i].type != undefined) {
        if (document.getElementById(ul.children[i].type)) document.getElementById(ul.children[i].type).className = 'hide';
      }
    }
  }

  obj.className = 'o_select';
  if(obj.type == 'network_blacklist'){
    document.getElementById('div_general_blacklist').style.display = 'none';
    document.getElementById('div_network_blacklist').style.display = '';
    getNetworkBlacklist();
  }else if(obj.type == 'general_blacklist'){
    document.getElementById('div_general_blacklist').style.display = '';
    document.getElementById('div_network_blacklist').style.display = 'none';
    schBlacklist();
  }else{
    return ;
  }
}

/*搜索网络诈骗顾客*/
function schNetBlacklist(obj){
  var elementsList = obj.elements;
  var data = new Array();

  for(var i = 0;i < elementsList.length - 1; i++){
    if(elementsList[i].value != ''){
      data.push(elementsList[i].name + '=' +elementsList[i].value);
    }
  }

  Ajax.call('users.php?act=get_network_blacklist',data.join('&'),fullSearchResponse,'GET','JSON');
}
/**
 * 显示录音播放器
 */
function showRecPlayer(obj) {
  var msg = {req_msg:true,message:'<audio src='+obj.children[0].src+' controls="controls"></audio>',title:obj.innerText,btncontent:'关闭',manual_hide:true}
  showMsg(msg);
}

/**
 * 显示拨号按钮
 */
function showDialBox() {
  if (window.getSelection().toString().length <= 0) {
    return false;
  }
  var dialBox = document.getElementById('dialBox');
  dialBox.className = 'show';
  dialBox.style.position = 'absolute';
  dialBox.style.top = event.pageY+15+'px';
  dialBox.style.left = event.pageX+'px';
  dialBox.style.zIndex = 100;
}
/**
 * 拨号
 */
function dial() {
  event.preventDefault();
  document.getElementById('dialBox').className = 'hide';
  var phone = window.getSelection().toString();
  if (window.getSelection) {
    var sel = window.getSelection();
    sel.removeAllRanges();
  }
  Ajax.call('users.php', 'act=dial&phone='+phone, dialResp, 'POST', 'JSON');
}

function dialResp(res) {
  showMsg(res);
}

//删除顾客
function deleteUser(obj,user_id){
  if (user_id != '' || user_id != null) {
    var trIndex = obj.parentNode.parentNode.rowIndex;
    var r = confirm('你确定删除顾客');
    if (r) {
      Ajax.call('users.php?act=del_user','user_id='+user_id+'&row_index='+trIndex,deleteUserResp,'GET','JSON');
    }else return;
  }
}

function deleteUserResp(res){
  if (res.code) {
    var table = document.getElementById('user_list_table');
    table.deleteRow(res.row_index);
  }
  showMsg(res);
}

//获得某客服的某类型的顾客的数量
function getAdminUserByCustomerCount(obj){
  var form = document.forms['transfer']; 
  var adminId = form.elements['admin_id'].value;
  if (adminId != 0) {
    $.get(
        'users.php?act=get_user_count&admin_id='+adminId+'&customer_type='+obj.value,
        function (res){
          document.getElementById('transfer_num').value = res.count;
        },'JSON');
  }
}
//根据需求计算顾客数量
function getAdminUserByEffect(obj){
  var form = document.forms['effect_transfer'];
  var adminId = form.elements['from_admin'].value;
  if (adminId) {
    $.get(
        'users.php?act=get_user_count&admin_id='+adminId+'&eff_id='+obj.value,
        function (res){
          form.elements['transfer_num'].value = res.count;
        },'JSON');
  }
}

function clearUserCount(obj){
  obj.elements['transfer_num'].value=0;
}

//批量转移顾客
function batch(obj,type)
{
  var from_admin   = obj.elements['from_admin'].value;
  var to_admin     = obj.elements['to_admin'].value;
  var transfer_num = obj.elements['transfer_num'].value;
  var msg          = [];
  msg['timeout']   = 2000;
  var data         = '';

  if ('type' == type) {
    var from_customer_type = obj.elements['from_customer_type'].value;
    var to_customer_type   = obj.elements['to_customer_type'].value;
    if (to_customer_type == 0 || from_customer_type == -1) {
      msg['message'] = '请选择顾客类别';
      showMsg(msg);
      return false;
    }else{
      data = '&to_customer_type='+to_customer_type+'&from_customer_type='+from_customer_type;
    }
  }else{
    var from_effect = obj.elements['from_effect'].value;
    if (from_effect == -1) {
      msg['message'] = '请选择顾客需求';
      showMsg(msg);
      return false;
    }else{
      data = '&from_effect='+from_effect;
    }
  }
  Ajax.call('users.php?act=from_to&','from_admin='+from_admin+'&to_admin='+to_admin+'&transfer_num='+transfer_num+data,showMsg,'GET','JSON');
}

//部分转移顾客
function transfer(obj)
{
  var from_phone = obj.elements['from_phone'].value;
  var to_admin = obj.elements['to_admin'].value;

  Ajax.call('users.php?act=transfer_submit&','from_phone='+from_phone+'&to_admin='+to_admin,showMsg,'GET','JSON');
}

//高级转移
function advanceBatch(obj)
{
  var elements = obj.elements;
  var from_admin    = obj.elements['adv_from_admin'].value; //源客服
  var to_admin      = obj.elements['adv_to_admin'].value;   //目标客服
  var ser_startTime = obj.elements['ser_startTime'].value;  //服务时间
  var ser_endTime   = obj.elements['ser_endTime'].value;
  var buy_startTime = obj.elements['buy_startTime'].value;  //购买时间
  var buy_endTime   = obj.elements['buy_endTime'].value;
  var add_startTime = obj.elements['add_startTime'].value;  //添加时间
  var add_endTime   = obj.elements['add_endTime'].value;
  var customer_type = obj.elements['customer_type'].value;

  if (!ser_startTime && !buy_startTime && !add_startTime) {
    var msg = [];
    msg['message'] = '至少选择一个条件';
    msg['timeout'] = 2000;
    showMsg(msg);
  }else if(from_admin>=0 && to_admin) {
    Ajax.call('users.php?act=advance_batch&','from_admin='+from_admin+'&to_admin='+to_admin+'&ser_startTime='+ser_startTime+'&ser_endTime='+ser_endTime+'&buy_startTime='+buy_startTime+'&buy_endTime='+buy_endTime+'&add_startTime='+add_startTime+'&add_endTime='+add_endTime+'&customer_type='+customer_type,showMsg,'POST','JSON');  
  }else return false;
}

//改变转顾客中的顾客类型选择
function changeCustomerType(obj){
  var opts = document.getElementById('from_customer_type_id').options;
  if (obj.value == 0) {
    for (var i = 0; i < opts.length; i++) {
      if (opts[i].value != 4) {
        opts[i].disabled = true;
      }else{
        opts[i].selected = true;
      }
    }
  }else{
    for (var i = 0; i < opts.length; i++) {
      opts[i].disabled = false;
    }
  }
}

//回收顾客
function recyleUser(obj){
  var roleId = obj.elements['role_id'].value;
  var recyleAdmin = obj.elements['recyle_admin'].value;
  var customer_type = obj.elements['customer_type'].value;
  if (roleId) {
    $.get(
        'users.php?act=recyle_user&role_id='+roleId+'&recyle_admin='+recyleAdmin+'&customer_type='+customer_type,
        function(data){
          showMsg(data);
        },'JSON');
  }
}

//电话授权
function phoneAuthorize(userId){
  var adminId = $("#authorize_admin").val();
  $.get(
      tpUrl+'&c=usersmanage&a=authorizeAdmin&admin_id='+adminId+'&user_id='+userId,
      function(res){
        showMsg(res);
      },'JSON');
}
function getPhoneAuthorizeUser(obj){
  $.get(
      tpUrl+'&c=usersmanage&a=phoneAuthorizeUser&to_me='+obj.value,
      function(res){
        $("#main").html(res.main);
        init();
      },'JSON');
}

function cancelPhoneAuthorize(obj){
  var r = confirm('你确认要取消该顾客的电话授权');
  if (r) {
    $.get(
        obj.value,
        function(res){
          showMsg(res);
        },'JSON');
  }else return;
}

//查找推荐人；
function searchReferrer(obj) {
  var defaultValue = '介绍人ID';
  var info = obj.value;
  if (info = info.replace(/^\s*|\s*$/, '')) { // && info != defaultValue)
    Ajax.call('users.php?act=find_referrer', 'keywords=' + info, searchReferrerResponse, 'POST', 'JSON');
  }
  else {
    obj.value = defaultValue;
    var slt = document.forms['theForms'].elements['parent_id'];
    slt.parentNode.removeChild(slt);
  }
}

function searchReferrerResponse(res) {
  var slt = document.forms['theForms'].elements['parent_id'];
  slt.length = 0;
  var temp = document.createElement('option');
  temp.text = '请选择推荐人';
  temp.value = 0;
  slt.add(temp);
  for (var i in res) {
    if (typeof(res[i]) != 'object') continue;
    var temp = document.createElement('option');
    temp.text = res[i].user_name;
    temp.value = res[i].user_id;
    slt.add(temp);
  }
  slt.className = '';
  slt.style.display = 'inline';
}
