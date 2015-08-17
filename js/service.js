var windowObj = null;

//添加服务
function submitService(subobj) {
  subobj.disabled = true;
  var id = document.getElementById('ID').value; //顾客id
  var obj = document.forms['for_service'];
  var service_class = obj.elements['service_class'].value; //服务类别
  var service_manner = obj.elements['service_manner'].value; //服务方式
  var service_time = obj.elements['service_time'].value; //服务时间
  var logbook = obj.elements['logbook'].value; //服务过程
  var show_it = obj.elements['show_sev'].value;

  var alarm_time = '';
  if (document.getElementById('alarm_time') && document.getElementById('msg').className != 'hide') {
    alarm_time = document.getElementById('alarm_time').value;
    if (alarm_time == '') {
      document.getElementById('alarm_info').innerHTML = '预约时间不正确';
      return;
    }
  }

  var msg = [];

  if (!cheLogbook(logbook)) {
    msg['message'] = '服务过程不能为空';
    msg['timeout'] = 2000;

    showMsg(msg);
    return false;
  } else {
    if (logbook.length < 10) {
      var r = confirm('服务记录字数太短，确认提交');
      if (r == true) {
        showFade();
        Ajax.call('service.php?act=add_service', 'user_id=' + id + '&service_class=' + service_class + '&service_manner=' + service_manner + '&service_time=' + service_time + '&logbook=' + logbook + '&show_it=' + show_it + '&alarm_time=' + alarm_time, insert_service, 'POST', 'JSON');
      }
    } else {
      showFade();
      Ajax.call('service.php?act=add_service', 'user_id=' + id + '&service_class=' + service_class + '&service_manner=' + service_manner + '&service_time=' + service_time + '&logbook=' + logbook + '&show_it=' + show_it + '&alarm_time=' + alarm_time, insert_service, 'POST', 'JSON');
    }
  }
}

//添加服务回调
function insert_service(res) {
  showMsg(res);
  if (res.code) {
    var objTr = document.getElementById('detail');
    objTr.parentNode.deleteRow(objTr.rowIndex);
  }
  setTimeout(hideFade, 3000);
}

/*
 * 服务搜索
 */
function searchService() {
  var user_id = document.getElementById('ID').value;
  var obj = document.forms['for_serverSeach'];
  var startTime = obj.elements['startTime'].value;
  var endTime = obj.elements['endTime'].value;

  Ajax.call('service.php?act=service_search', '&startTime=' + startTime + '&endTime=' + endTime + '&user_id=' + user_id, searchServiceResponse, 'POST', 'JSON');
}

function searchServiceResponse(res) {
  if (res.code == 1) {
    document.getElementById('service').innerHTML = res.main;
  }
  else {
    var msg = new Array();

    msg['mesage'] = '未能搜索到指定服务';
    msg['timeout'] = 2000;

    showMsg(msg);
  }
}

//将选择的客服姓名填充到input里
function getServiceAdmin() {
  var obj = document.forms['for_service'];
  var adminId = obj.elements['admin_list'].value;
  var adminNamebox = obj.elements['admin_list'];
  var adminName = "";
  var i = 0;
  while (1) {
    if (adminNamebox[i].value == adminId) {
      adminName = adminNamebox[i].text;
      break;
    }
    i++;
  }

  obj.elements['admin_name'].value = adminName;
}

//服务高级搜索
function fullSearch(obj) {
  var startTime = obj.elements['startTime'].value;
  var endTime = obj.elements['endTime'].value;
  var userName = obj.elements['user_name'].value;
  var adminId = obj.elements['admin_id'].value;
  var roleId = obj.elements['role_id'].value;

  Ajax.call('service.php?act=service_fuse', 'start_time=' + startTime + '&end_time=' + endTime + '&user_name=' + userName + '&admin_id=' + adminId + '&role_id=' +roleId, fullSearchResponse, 'POST', 'JSON');
}

//服务高级搜索回调
function fullSearchResponse(res) {
  document.getElementById('resource').innerHTML = res.main;
  init();
}

//提交提醒
function subRemind() {
  var obj = document.forms['for_remind_conf'];
  var remind_name = obj.elements['remind_name'].value;
  var remind_content = obj.elements['remind_content'].value;
  var remind_type = obj.elements['remind_type'].value;
  var remind_time = obj.elements['remind_time'].value;
  var show_index = 0;
  if (obj.elements['show_index'].checked) {
    show_index = obj.elements['show_index'].value;
  }

  if (remind_name != "" && remind_content != "" && remind_type != "" && remind_time != "") {
    Ajax.call('service.php?act=add_remind&', 'remind_name=' + remind_name + '&remind_content=' + remind_content + '&remind_type=' + remind_type + '&remind_time=' + remind_time + '&show_index=' + show_index, subRemindResponse, 'GET', 'JSON');
  }
  else {
    var msg = new Array();
    msg['timeout'] = 2000;
    msg['message'] = '信息未填写完整';

    showMsg(msg);
  }
}

//提交提醒回调
function subRemindResponse(res) {
  promptMsg(res);
}

//事件提醒提交
function subEvent() {
  var obj = document.forms['for_event_remind'];
  var event_type = obj.elements['remind_type'].value;
  var date = obj.elements['date'].value;
  var time = obj.elements['time'].value;
  var event_time = date + ' ' + time;
  var event_content = obj.elements['event_content'].value;

  Ajax.call('service.php?act=add_event&', 'event_type=' + event_type + '&event_time=' + event_time + '&event_content=' + event_content, eventResponse, 'GET', 'TEXT');
}

//回调
function eventResponse(res) {
  promptMsg(res);
}

//删除服务
function del_service(service_id) {}

/*
 * 验证函数
 */

//验证顾客姓名
function cheUser(username) {
  if (username == "") {
    document.getElementById('user_name').innerHTML = "顾客姓名不能为空";
    return false;
  }
  else {
    document.getElementById('user_name').innerHTML = "";
    return true;
  }
}

//验证客服姓名
function cheAdmin(admin) {
  if (admin == "") {
    document.getElementById('admin_name').innerHTML = "客服不能为空";
    return false;
  }
  else {
    document.getElementById('admin_name').innerHTML = "";
    return true;
  }
}

//验证服务过程
function cheLogbook(logbook) {
  if (logbook == "") {
    return false;
  }
  else {
    document.getElementById('logbook').innerHTML = "";
    return true;
  }
}

//鼠标划过按钮
function change_gb(obj) {
  obj.style.bordercolor = 'red';
}

//提示信息
function promptMsg(val) {
  var msg = new Array();
  msg['timeout'] = 2000;

  if (val == 1) {
    msg['message'] = '添加成功';
  }
  else if (val == 0) {
    msg['message'] = '添加失败';
  }

  showMsg(msg);
}

//公共查询
function search() {
  var keyword = document.forms['searchForm'].elements['search'].value;
  var condition = document.forms['searchForm'].elements['condition'].value;
  var filter = new Array(1, 2, 3, 5);

  for (var i = 0; i < filter.length; i++) {
    if (condition == filter[i]) {
      document.forms['searchForm'].elements['search'].value = '';
      break;
    }
  }

  if (keyword == '') {
    document.getElementById('alarm').innerHTML = '输入的查询字符长度太短，请重新输入！';
  }

  else if (/[\u4e00-\u9fa5]+/.test(keyword) && keyword.length < 2) {
    document.getElementById('alarm').innerHTML = '输入的查询条件太过简单，请重新输入！';
    return;
  }
  else if (/^\d+$/.test(keyword) && keyword.length < 5 && condition < 9) {
    document.getElementById('alarm').innerHTML = '输入的查询条件太过简单，请重新输入！';
    return;
  }
  else {
    document.getElementById('alarm').innerHTML = '';
    var url = 'search.php';

    if (document.getElementById('finance')) {
      url = 'finance_search.php';
    }

    var platform_code = '';
    if (document.getElementById('platform_id').style.display != 'none') {
      platform_code = document.getElementById('platform_id').value;
    }

    Ajax.call(url, 'act=search&keyword=' + keyword + '&condition=' + condition + '&platform+code=' + platform_code, show, 'POST', 'JSON');
  }
}

function show(res) {
  document.getElementById('search_info').innerHTML = res != 0 ? res: '未查询到满足要求的结果';
  document.getElementById('goods_search_res').style.display = 'none';
}

// 公共查更多信息
function getMoreInfo(user_id, condition, type) {
  if (user_id && condition) {
    Ajax.call('search.php?act=get_more_info', 'user_id=' + user_id + '&condition=' + condition + '&type=' + type, showGoods, 'GET', 'JSON');
  }
}

function showGoods(res) {
  var listInfo = document.getElementById('goods_search_res');
  var tdlist = listInfo.getElementsByTagName('td');
  tdlist[0].innerHTML = res;
  listInfo.style.display = 'block';
}

//查看服务
function showLogbook(logbook) {
  document.getElementById('goods').innerHTML = logbook;
}

//预约服务提醒
function getHandlerServe() {
  Ajax.call('service.php', 'act=alarmService', serveResponse, 'POST', 'JSON');
}

//服务提醒回调
function serveResponse(res) {
  //document.getElementById('handler_service').innerHTML = res;
  //document.getElementById('handler_service').style.display = 'block';
}

/*服务记录翻页
 *@page 跳转页数 @page_total 总页数
 * */
function pageTurn(page, page_size, eve, page_total) {
  if (eve.keyCode == 13) {
    if (page > page_total) {
      var msg = new Array();

      msg['timeout'] = 2000;
      msg['message'] = '输入超出范围';

      showMsg(msg);
    }
    else {
      Ajax.call('service.php?act=records&', 'page=' + page + '&page_size=' + page_size, limitResponse, 'GET', 'JSON');
    }
  }
}

/*每页显示记录数
 *@count 每页记录数
 */
function limit(count, eve) {
  if (eve.keyCode == 13) {
    if (count > 500) {
      var msg = new Array();

      msg['timeout'] = 2000;
      msg['message'] = '输入值过大';

      showMsg(msg);
    }
    else {
      Ajax.call('service.php?act=records&', 'page_size=' + count, limitResponse, 'GET', 'JSON');
    }
  }
}

function limitResponse(res) {
  main.innerHTML = res.main;
  init();
}

function cancel() {
  document.getElementById('message').className = 'hide';
}

//财务确认
function showPrompt(type, order_id) {
  Ajax.call('order.php?act=get_order_info&', 'order_id=' + order_id + '&type=' + type, showPromptRes, 'GET', 'JSON');
}

function showPromptRes(res) {
  var msgBox = document.getElementById('message');
  var msgIn = msgBox.getElementsByTagName('div')[0];
  msgBox.getElementsByTagName('p')[0].innerHTML = res.html;
  if (res.type == 'info') msgBox.getElementsByTagName('h5')[0].innerHTML = '发货白单确认';
  else msgBox.getElementsByTagName('h5')[0].innerHTML = '快递单确认';
  msgBox.className = 'show';
  msgIn.style.height = "480px";
  msgIn.style.width = "480px";
}

//白单确认
function confirmInfo(order_id) {
  var other = document.getElementById('other').value;
  var b_value = document.getElementById('b_value').value;

  Ajax.call('order.php?act=confirm_info&', 'order_id=' + order_id + '&other=' + other + '&type=' + 'info' + '&b_value=' + b_value, infoResponse, 'GET', 'TEXT');
}

function infoResponse(res) {
  document.getElementById('message').className = 'hide';
}

//快递单确认
function confirmExpress(order_id) {
  var other = document.getElementById('other').value;
  var shipping_fee = document.getElementById('shipping_fee').value;
  var shipping_name = document.getElementById('shipping_fee').value;
  var b_value = document.getElementById('b_value').value;

  Ajax.call('order.php?act=confirm_info&', 'order_id=' + order_id + '&other=' + other + '&shipping_fee=' + shipping_fee + '&type=' + 'express' + '&b_value=' + b_value, infoResponse, 'GET', 'TEXT');

}

function expressResponse(res) {
  document.getElementById('message').className = 'hide';
}

//全选
function selAll(obj, act) {
  var td = (obj.parentNode).parentNode;
  var td_inputs = td.getElementsByTagName('input');

  if (act) {
    for (var i = 0; i < td_inputs.length - 1; i++) {
      td_inputs[i].checked = true;
    }
  }
  else {
    for (var i = 0; i < td_inputs.length; i++) {
      td_inputs[i].checked = '';
    }
  }

}

//添加/修改会员积分等级
function addModRank(val, id, row_id) {
  var obj = document.forms[val];
  var rank_name = obj.elements['rank_name'].value; //等级名称
  var min_point = parseInt(obj.elements['min_point'].value); //积分下限
  var max_point = parseInt(obj.elements['max_point'].value); //积分上限
  var convert_scale = obj.elements['convert_scale'].value; //兑换比例
  var discount = obj.elements['discount'].value; //折扣比例
  var integral_discount = obj.elements['integral_discount'].value; //额外比例
  var platform = obj.elements['platform'].value; //适用平台
  if (id == 1) //修改会员等级
  {
    var id = document.getElementById('rank_id').value;
  }

  var validity = obj.elements['validity'].value; //有效期限
  var unit_data = obj.elements['unit_data'].value; //日期单位
  var msg = new Array();
  msg['timeout'] = 2000;
  //判断
  if (rank_name == '') {
    msg['message'] = '等级名不能为空';
    showMsg(msg);
    return;
  }

  if (min_point > max_point) {
    msg['message'] = '积分区间输入有误';
    showMsg(msg);
    return;
  }

  if (convert_scale == '') {
    msg['message'] = '兑换比例不能为空';
    showMsg(msg);
    return;
  }

  if (discount == '') {
    msg['message'] = '折扣比例不能为空';
    showMsg(msg);
    return;
  }

  if (validity == '') {
    msg['message'] = '有效期不能为空';
    showMsg(msg);
    return;
  }

  Ajax.call('service.php?act=add_mod_rank&', 'rank_name=' + rank_name + '&min_point=' + min_point + '&max_point=' + max_point + '&convert_scale=' + convert_scale + '&discount=' + discount + '&integral_discount=' + integral_discount + '&platform=' + platform + '&validity=' + validity + '&unit_data=' + unit_data + '&id=' + id + '&row_id=' + row_id, add_rank_res, 'GET', 'JSON');
}

function add_rank_res(res) {
  showMsg(res);
}

//添加或修改积分规则
function addModInte(val, id, row_id) {
  var obj = document.forms[val]; //表单对象
  var integral_title = obj.elements['integral_title'].value; //积分规则名称
  var integral_way = obj.elements['integral_way'].value; //积分赠送方式
  var scale = obj.elements['scale'].value; //赠送比例
  var platform = obj.elements['platform'].value; //适用平台
  var present_start = obj.elements['present_start'].value; //启动时间
  var present_end = obj.elements['present_end'].value; //结束时间                  
  var available_obj = obj.elements['available']; //是不启用
  var available = available_obj.checked == true ? 1: 0;
  var suit_brand = obj.elements['suit_brand'].value; //适用品牌
  var min_consume = obj.elements['min_consume'].value; //消费下限
  var max_consume = obj.elements['max_consume'].value; //消费上限
  var che_mod = 0;

  var msg = new Array();
  msg['timeout'] = 2000;

  if (integral_title == '') {
    msg['message'] = '规则名不能为空';
    showMsg(msg);
    return;
  }

  if (scale == '') {
    msg['message'] = '赠送比例不能为空';
    showMsg(msg);
    return;
  }

  if (present_start == '') {
    msg['message'] = '启用时间不能为空';
    showMsg(msg);
    return;
  }

  if (present_end == '') {
    msg['message'] = '结束时间不能为空';
    showMsg(msg);
    return;
  }

  if (min_consume == '') {
    msg['message'] = '消费下限不能为空';
    showMsg(msg);
    return;
  }

  if (max_consume == '') {
    msg['message'] = '消费上限不能为空';
    showMsg(msg);
    return;
  }

  if (id == 1) {
    id = document.getElementById('integral_id').value;
    che_mod = 1; //修改
  }

  Ajax.call('service.php?act=add_mod_inte', 'integral_title=' + integral_title + '&integral_way=' + integral_way + '&scale=' + scale + '&platform=' + platform + '&present_start=' + present_start + '&present_end=' + present_end + '&available=' + available + '&suit_brand=' + suit_brand + '&min_consume=' + min_consume + '&max_consume=' + max_consume + '&id=' + id + '&che_mod=' + che_mod, addRuleRes, 'POST', 'JSON');

}

function addRuleRes(res) {
  var msg = new Array();
  msg['timeout'] = 2000;
  if (res.code == 1) {
    msg['message'] = '添加/修改成功';
    /*
       var tr_obj = document.getElementById('temp_inte');
       var tb_obj = document.getElementById('cur_integral');
       tb_obj.deleteRow(tr_obj.rowIndex);
       */
  }
  else if (res.code == 2) {
    msg['message'] = '已经存在相同的规则';
  }
  else if (res.code = 3) {
    msg['message'] = '该平台已经启用相同类型规则';
  }
  showMsg(msg);
}

//搜索某部门适用的积分规则
function schIntegral() {
  var role_id = document.getElementById('role').value;
  Ajax.call('service.php?act=sch_integral&', 'role_id=' + role_id, resInte, 'GET', 'JSON');
}

function resInte(res) {
  document.getElementById('ins_div2').innerHTML = res.main;
}

//删除积分等级
function delRank(obj, val, rank_name) {
  var r = confirm("你确定要删除  " + rank_name);
  if (r == true) {
    var rank_id = val;
    var row_id = (obj.parentNode).parentNode.rowIndex;
    Ajax.call('service.php?act=del_ranks&', 'rank_id=' + rank_id + '&row_id=' + row_id, delRankRes, 'GET', 'JSON');
  }
  else {
    return false;
  }
}

function delRankRes(res) {
  showMsg(res);
  if (res.code) {
    var objtb = document.getElementById('cur_rank');
    objtb.deleteRow(res.row_id);
  }
}

//删除积分规则
function delIntegral(obj, val, title) {
  var integral_title = title;
  var r = confirm('确定要删除  ' + integral_title);
  if (r) {
    var integral_id = val;
    var row_id = (obj.parentNode).parentNode.rowIndex;
    Ajax.call('service.php?act=del_integrals&', 'integral_id=' + integral_id + '&row_id=' + row_id, delInteRes, 'GET', 'JSON');
  } else {
    return false;
  }
}

function delInteRes(res) {
  showMsg(res);
  if (res.code) {
    if (document.getElementById("cur_integral").style.display != 'none') {
      var tb_obj = document.getElementById('cur_integral');
    } else {
      var tb_obj = document.getElementById('dis_integral');
    }
    tb_obj.deleteRow(res.row_id);
  }
}

//确认积分
function confirmIntegral(val, points, user_id, obj) {
  var user_integral_id = val;
  var row_id = (obj.parentNode).parentNode.rowIndex;

  Ajax.call('service.php?act=confirm_integral&', 'user_integral_id=' + user_integral_id + '&points=' + points + '&user_id=' + user_id + '&row_id=' + row_id, conInteRes, 'GET', 'JSON');
}

function conInteRes(res) {
  showMsg(res);
  if (res.code) {
    var tb_obj = document.getElementById('temp_inte');
    tb_obj.deleteRow(res.row_id);
    if (document.getElementById(res.row_id + 'intr')) {
      tb_obj.deleteRow(res.row_id + 1);
    }
    schConInte();
  }
}

// Modify selected rank [get templates]
function modTem(obj, val, plan) {
  //修改等级设置
  if (plan == 1) {
    var tb_obj = document.getElementById('cur_rank');
    var rank_id = val; //modify the id of rank
    var row_id = (obj.parentNode).parentNode.rowIndex + 1;

    if (document.getElementById(val + 'rank_temp') == null) {
      Ajax.call('service.php?act=mod_rank_inte&', 'id=' + rank_id + '&row_id=' + row_id + '&plan=' + plan, modRes, 'GET', 'JSON');
    }
    else {
      tb_obj.deleteRow(row_id);
    }
  }
  //修改积分规则
  else {
    var integral_id = val; //modify the id of rank
    var row_id = (obj.parentNode).parentNode.rowIndex + 1;

    if (document.getElementById(val + 'integral_temp') == null) {
      Ajax.call('service.php?act=mod_rank_inte&', 'id=' + integral_id + '&row_id=' + row_id + '&plan=' + plan, modRes, 'GET', 'JSON');
    }
    else {
      tb_obj.deleteRow(row_id);
    }
  }

}

function modRes(res) {
  //修改等级回调
  if (res.plan == 1) {
    var tb_obj = document.getElementById('cur_rank');
    //20130819 start
    if (document.getElementById((res.row_id - 1) + 'rank_temp') == null)
      //var rank_detail = document.getElementById('rank_detail');
      //if(!rank_detail)
    {
      var tr_obj = tb_obj.insertRow(res.row_id);
      tr_obj.id = 'rank_detail';
      tr_obj.id = (res.row_id - 1) + 'rank_temp';
      //20130819 end
      var td_obj = tr_obj.insertCell(0);
      td_obj.setAttribute('colspan', 12);
      td_obj.innerHTML = res.main;
    }
    else {
      tb_obj.deleteRow(res.row_id);
    }
  }
  //修改积分规则回调
  else {
    if (res.plan == 2) {
      var tb_obj = document.getElementById('cur_integral');
      if (document.getElementById((res.row_id - 1) + 'inte_temp') == null) {
        var tr_obj = tb_obj.insertRow(res.row_id);
        tr_obj.id = (res.row_id - 1) + 'inte_temp';
        var td_obj = tr_obj.insertCell(0);
        td_obj.setAttribute('colspan', 12);
        td_obj.innerHTML = res.main;
      }
      else {
        tb_obj.deleteRow(res.row_id);
      }
    }
  }
}

//启用切换
function tabAvailable(val) {
  if (val == 1) {
    document.getElementById('disable').style.background = "#E0E0E0";
    document.getElementById('past_due').style.background = "#E0E0E0";
    document.getElementById('enable').style.background = "-webkit-gradient(linear, 0% 0%, 0% 100%, from(#2673C4), to(#75A6DA))";
    document.getElementById('cur_integral').style.display = '';
    document.getElementById('dis_integral').style.display = 'none';
    document.getElementById('past_due_integral').style.display = 'none';
  }
  else if (val == 0) {
    document.getElementById('enable').style.background = "#E0E0E0";
    document.getElementById('past_due').style.background = "#E0E0E0";
    document.getElementById('disable').style.background = "-webkit-gradient(linear, 0% 0%, 0% 100%, from(#2673C4), to(#75A6DA))";
    document.getElementById('cur_integral').style.display = 'none';
    document.getElementById('past_due_integral').style.display = 'none';
    document.getElementById('dis_integral').style.display = '';
  }
  else if (val == 2) {
    document.getElementById('enable').style.background = "#E0E0E0";
    document.getElementById('disable').style.background = "#E0E0E0";
    document.getElementById('past_due').style.background = "-webkit-gradient(linear, 0% 0%, 0% 100%, from(#2673C4), to(#75A6DA))";
    document.getElementById('cur_integral').style.display = 'none';
    document.getElementById('dis_integral').style.display = 'none';
    document.getElementById('past_due_integral').style.display = '';
  }
}

//启用规则
function enableInte(obj, integral_id) {
  var row_id = (obj.parentNode).parentNode.rowIndex;
  Ajax.call('service.php?act=enable_inte&', 'integral_id=' + integral_id + '&row_id=' + row_id, enableRes, 'GET', 'JSON');
}

function enableRes(res) {
  showMsg(res);
  if (res.code) {
    var tb_obj = document.getElementById('dis_integral');
    tb_obj.deleteRow(res.row_id);
  }
}

//判断是否有空值
function cheNull(obj) {
  var msg = new Array();
  if (this.type == 'text') //文本框
  {
    if (this.value == '') {
      msg['message'] = this.title + '不能为空';
    }
  }
  else if (this.type = 'checked') {

  }
}

//显示会员
function showNumbers(obj, rank_id) {
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

  Ajax.call('users.php?act=vip_list', 'rank_id=' + rank_id + '&from_sel=' + true, showNumRes, 'GET', 'JSON');
}

function showNumRes(res) {
  document.getElementById('resource').innerHTML = res.main;
  document.getElementById('record_count').innerHTML = '共' + res.record_count + '条';
  init();
}

//快捷营销
function fastSale(rank_id, obj) {
  var tb_obj = document.getElementById('vip_list');
  var row_id = (obj.parentNode).parentNode.rowIndex;
  var tr_obj = document.getElementById(row_id + 1 + 'vips');

  if (tr_obj) {
    tb_obj.deleteRow(row_id + 1);
  }
  else {
    Ajax.call('service.php?act=fast_sale&', 'rank_id=' + rank_id + '&row_id=' + row_id, fastRes, 'GET', 'JSON');
  }
}
function fastRes(res) {
  td_obj = comVipList(res);
  td_obj.innerHTML = res.main;
}

//会员分组列表公共
function comVipList(res) {
  var tb_obj = document.getElementById('vip_list');
  var tr_obj = tb_obj.insertRow(res.row_id);
  var td_obj = tr_obj.insertCell(0);
  tr_obj.setAttribute('id', res.row_id + 'vips');
  td_obj.setAttribute('id', 'resource');
  td_obj.setAttribute('colspan', 8);

  return td_obj;
}

//搜索部门的等级设置
function getRankPart(role_id) {
  Ajax.call('service.php?act=get_rank_part&', 'role_id=' + role_id, getRankRes, 'GET', 'JSON');
}

function getRankRes(res) {
  document.getElementById('ins_div').innerHTML = res.main;
}

//查看当前所属积分规则
function viewInte(obj) {
  var integral_id = obj.value;
  var tr_obj = (obj.parentNode).parentNode;
  var tb_obj = document.getElementById('temp_inte');
  var row_id = tr_obj.rowIndex;
  var tr_insert = document.getElementById(row_id + 'intr');
  if (tr_insert) {
    tb_obj.deleteRow(row_id + 1);
  }
  else {
    Ajax.call('service.php?act=view_inte&', 'row_id=' + row_id + '&integral_id=' + integral_id, viewInteRes, 'GET', 'JSON');
  }
}

function viewInteRes(res) {
  var tb_obj = document.getElementById('temp_inte');
  var tr_obj = tb_obj.insertRow(res.row_id + 1);
  tr_obj.setAttribute('id', res.row_id + 'intr');
  var td_obj = tr_obj.insertCell(0);
  td_obj.setAttribute('colspan', 12);

  td_obj.innerHTML = res.main;
}

//搜索未确认积分
function schConInte() {
  var obj = document.forms['sch_confirm_inte'];
  var integral_way = obj.elements['integral_way'].value;
  var integral_id = obj.elements['integral'].value;
  var platform = obj.elements['platform'].value;
  var user_name = obj.elements['user_name'].value;
  var admin = obj.elements['admin'].value;

  Ajax.call('service.php?act=sch_con_inte&', 'integral_way=' + integral_way + '&integral_id=' + integral_id + '&platform=' + platform + '&user_name=' + user_name + '&admin=' + admin, schConRes, 'GET', 'JSON');
}

function schConRes(res) {
  document.getElementById('resource').innerHTML = res.main;
  init();
}

//批量确认积分
function conCurInte() {
  var type = document.getElementById('confirm_type').value;
  type = parseInt(type);

  if (type == 1) //确认当前页
  {
    var tb_obj = document.getElementById('temp_inte');
    var input_obj = tb_obj.getElementsByTagName('input');
    var inte_id = "";

    if (input_obj.length == 1) {
      var msg = new Array();
      msg['timeout'] = 2000;
      msg['message'] = "没有可确认积分";

      showMsg(msg);
      return;
    }

    for (var i = 1; i < input_obj.length; i++) {
      if (input_obj[i].type == 'checkbox') {
        inte_id += input_obj[i].value + ',';
      }
    }

    inte_id = inte_id.substring(0, inte_id.length - 1);
    var r = confirm("要确认当前页用户积分!");
    if (r) {
      Ajax.call('service.php?act=confirm_integral&', 'inte_id=' + inte_id + '&type=' + type, conAllRes, 'GET', 'JSON');
    }
  }

  //当天
  else if (type == 2) {
    var r = confirm('确认当天用户积分');
    if (r) {
      Ajax.call('service.php?act=confirm_integral&', 'type=' + type + '&inte_id=' + 1, conAllRes, 'GET', 'JSON');
    }
  }
  else if (type == 3) {
    /*
       if(document.getElementById('custom').value == '')
       {
       var msg = new Array();
       msg['timeout'] = 2000;
       msg['message'] = '自定义时间不能为空';

       showMsg(msg);
       return;
       }
       var custom = '确认'+document.getElementById('custom').value + '天内会员积分';
       */
    var custom = '确定要确认全部积分？';
    var r = confirm(custom);
    if (r) {
      Ajax.call('service.php?act=confirm_integral&', 'type=' + type + '&inte_id=' + 2, conAllRes, 'GET', 'JSON');
    }
  }
}

function conAllRes(res) {
  if (res.code == 1) {
    setTimeout("schConInte()", 1000);
    var msg = new Array();
    showMsg(res);
  }
}

//会员详细信息
function getInfo(user_id) {
  var detail = document.getElementById('detail');
  if (detail != null) {
    detail.parentNode.removeChild(detail);
  }

  if (0 < user_id) {
    Ajax.call('users.php?act=user_detail&', 'id=' + user_id, userInfoRes, 'GET', 'JSON');
  }
}

function userInfoRes(res) {
  document.getElementById('pop_ups').innerHTML = res.info;
  var pop_ups = document.getElementById('pop_ups');
  var div_pop_ups = document.getElementById('div_pop_ups');

  var insert_div = '<div style="background:#2673C4;color:#FFF;text-align:center;padding:5px;border-radius:3px;height:24px;" id="title_pop">';

  insert_div += '顾客详情<label style="float:right;margin-right:6px;cursor:pointer" onclick="close_pop()" ><b>X</b></label></div>';

  div_pop_ups.innerHTML = insert_div;
  div_pop_ups.appendChild(pop_ups);
  document.getElementById('fade').style.display = 'block';
  div_pop_ups.style.display = 'block';
  div_pop_ups.style.style.left = '20%';
  div_pop_ups.style.style.top = '10%';
}

//搜索会员积分日志
function schInteLog() {
  var obj = document.forms['for_sch_inte_log'];
  var role_id = obj.elements['role'].value;
  var user_info = obj.elements['user_info'].value;
  var start_time = obj.elements['start_time'].value;
  var end_time = obj.elements['end_time'].value;
  var distinct = obj.elements['distinct'].value;
  if (distinct == '最小/最大') {
    distinct = '';
  }

  Ajax.call('service.php?act=sch_inte_log&', 'role_id=' + role_id + '&user_info=' + user_info + '&start_time=' + start_time + '&end_time=' + end_time + '&distinct=' + distinct, schLogRes, 'GET', 'JSON');
}

function schLogRes(res) {
  document.getElementById('resource').innerHTML = res.main;
  init();
}

//会员子分组
function createCld(rank_id, obj) {
  var tb_obj = document.getElementById('vip_list');
  var row_id = (obj.parentNode).parentNode.rowIndex;
  var tr_obj = document.getElementById(row_id + 1 + 'vips');

  if (tr_obj) {
    tb_obj.deleteRow(row_id + 1);
  }
  else {
    Ajax.call('service.php?act=create_cld&', 'rank_id=' + rank_id + '&row_id=' + row_id, createCldRes, 'GET', 'JSON');
  }
}

function createCldRes(res) {
  td_obj = comVipList(res);
  td_obj.innerHTML = res.info;
}

//撤销积分
function delUserInte(user_inte_id, obj) {
  var row_id = (obj.parentNode).parentNode.rowIndex;
  var r = confirm('确定撤销积分');
  if (r) {
    Ajax.call('service.php?act=del_user_inte&', 'user_inte_id=' + user_inte_id + '&row_id=' + row_id, delUInteRes, 'GET', 'JSON');
  }
  else return;
}

function delUInteRes(res) {
  showMsg(res);
  if (res.code) {
    var tb_obj = document.getElementById('temp_inte');
    tb_obj.deleteRow(res.row_id);
  }
}

//充值 提现
function showApply(act) {
  if (act == 'show') {
    document.getElementById('list_charges').style.display = 'none';
    document.getElementById('new_apply').style.display = '';
  }
  else {
    document.getElementById('list_charges').style.display = '';
    document.getElementById('new_apply').style.display = 'none';
  }
}

//修改公司规章制度
function modRuleTitle() {
  //var editor;
  //KindEditor.ready(function(K) {
  //  editor = K.create('textarea[name="content"]', {
  //    resizeType : 1,
  //         allowPreviewEmoticons : false,
  //         allowImageUpload : false,
  //         items : [
  //    'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
  //         'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
  //         'insertunorderedlist', '|', 'emoticons', 'image', 'link']
  //  });
  //});
}

//获取可行的升级的方式
function getUpRankMethod(user_id, upgrade_gap, obj) {
  var row_id = obj.parentNode.parentNode.rowIndex;
  Ajax.call('service.php?act=get_up_rank_method&', 'user_id=' + user_id + '&upgrade_gap=' + upgrade_gap + '&row_id=' + row_id, uprankMethodRes, 'GET', 'JSON');
}

function uprankMethodRes(res) {
  tb_obj = (obj.parentNode).parentNode;
}

//会员的余额申请
function submitSurplus() {
  var frm = document.forms['formSurplus'];
  var user_id = frm.elements['user_id'].value;
  var surplus_type = frm.elements['surplus_type'].value; //充值 0或提现 1
  var surplus_amount = frm.elements['amount'].value; //金额
  var user_note = frm.elements['user_note'].value; //顾客描述
  var payment_id = frm.elements['payment'].value; //支付方式
  var msg = new Array();

  msg['timeout'] = "2000";
  msg['message'] = '';

  if (surplus_amount.length == 0) {
    msg['message'] += '输入金额不能空' + "\n";
  }
  else {
    //var reg = new RegExp(/^[\.0-9]+/);
    var reg = /^[\.0-9]+/;
    if (!reg.test(surplus_amount)) {
      msg['message'] += '输入金额格式不正确，请重新输入' + '\n';
    }
  }

  if (user_note.length == 0) {
    msg['message'] += '会员描述不能空' + "\n";
  }

  if (msg['message'].length > 0) {
    showMsg(msg);
    return false;
  }

  Ajax.call('service.php?act=act_account&', 'user_id=' + user_id + '&surplus_type=' + surplus_type + '&surplus_amount=' + surplus_amount + '&user_note=' + user_note + '&payment_id=' + payment_id, subSurplusRes, 'GET', 'JSON');
}

function subSurplusRes(res) {
  showMsg(res);
  document.getElementById('btn_submitSurplus').disabled = true;
  document.getElementById('btn_submitSurplus').style.background = "-webkit-gradient(linear, 0% 0%, 0% 100%, from(#968B8B), to(#BDBEBE))";
}

function schUserAccount() {
  var obj = document.forms['schUserAccForm'];
  var keywords = obj.elements['keyword'].value;
  var payment = obj.elements['payment'].value;
  var is_paid = obj.elements['is_paid'].value;
  var process_type = obj.elements['process_type'].value;
  var start_date = obj.elements['start_date'].value;
  var end_date = obj.elements['end_date'].value;

  Ajax.call('service.php?act=list&', 'keywords=' + keywords + '&payment=' + payment + '&is_paid=' + is_paid + '&process_type=' + process_type + '&start_date=' + start_date + '&end_date=' + end_date, limitResponse, 'GET', 'JSON');
}

//确认资金申请操作
function conUserAccSurplus(id) {
  Ajax.call('user_account.php?act=check&', 'id=' + id, conUsrAcSurRes, 'GET', 'JSON');
}

function conUsrAcSurRes(res) {
  if (res.code == 1) {
    close_pop();
    showMsg(res);
  } else {
    document.getElementById('pop_ups').innerHTML = res.main;
    document.getElementById('fade').style.display = 'block';
    document.getElementById('div_pop_ups').style.display = 'block';
  }
}

//会员积分模板切换
function changeAccountTap(action) {
  var obj = document.getElementById("account_surplus");
  var divs = obj.getElementsByTagName('div');
  for (var i = 0; i < divs.length; i++) {
    if (divs[i].id == action) {
      divs[i].style.display = '';
    }
    else {
      divs[i].style.display = 'none';
    }
  }
}

//通过部门检索管理员
function adminByRole(role_id) {
  Ajax.call('system.php?act=admin_by_role&', 'role_id=' + role_id, fullSearchResponse, 'GET', 'JSON');
}

//顾客所有积分所兑换金额
function showCalculate(user_id, rank_points) {
  Ajax.call('search.php?act=showCal&', 'user_id=' + user_id + '&rank_points=' + rank_points, showCalRes, 'GET', 'JSON');
}

function showCalRes(res) {

  var calDiv = document.getElementById('goods');
  calDiv.style.display = '';
  calDiv.innerHTML = '【总积分：' + res.rank_points + '】' + '当前可使用积分:<input type="text" style="width:54px;border:1px solid " id="payPoints" onchange="calculatePoints(this.value,' + res.convert_scale + ')" value="' + res.rank_points + '"/> 可抵换<span id="money">' + (res.money).toFixed(2) + '</span>元<input type="button" class="b_submit" style="float:right" value="确认使用这积分" onclick="usePoints(' + res.user_id + ')" />';

}

//计算可抵换金额
function calculatePoints(rank_points, convert_scale) {
  var div_money = document.getElementById('money');
  div_money.innerHTML = (rank_points / convert_scale).toFixed(2);
}

//确认使用这些积分
function usePoints(user_id) {
  var pay_points = parseInt(document.getElementById('payPoints').value);

  if (confirm('确认顾客本次将使用' + pay_points + '积分')) {
    Ajax.call('service.php?act=pay_points', 'user_id=' + user_id + '&pay_points=' + pay_points, showMsgRes, 'GET', 'JSON');
  }
}

//搜索存货订单
function schInventory(obj) {
  var user_name = document.getElementById('user_name').value;
  var phone = document.getElementById('phone').value;
  var store_time = document.getElementById('store_time').value;
  var order_sn = document.getElementById('order_sn').value;

  Ajax.call('order.php?act=sch_inventory', 'user_name=' + user_name + '&phone=' + phone + '&store_time=' + '&order_sn=' + order_sn, fullSearchResponse, 'GET', 'JSON');
}

//获取产品服用说明
function getGoodsHlp(goods_num, goods_id, goods_name) {
  Ajax.call('service.php?act=get_goods_hlp', 'goods_num=' + goods_num + '&goods_id=' + goods_id + '&goods_name=' + goods_name, showMsgRes, 'GET', 'JSON');
}

function showMsgRes(res) {

  if (document.getElementById('clip')) {
    var objDiv = document.getElementById('clip');
    document.body.removeChild(objDiv);
  }

  showMsg(res);
}

//获取相关套餐
function getPackage(goods_id) {
  Ajax.call('service.php?act=get_package', 'goods_id=' + goods_id, showMsgRes, 'GET', 'JSON');
}

//search vips
function schRankVips(obj) {
  var data = [];
  for (var i = 0; i < obj.elements.length - 1; i++) {
    if (obj.elements[i].value == '') {
      continue;
    } else {
      data.push(obj.elements[i].name + '=' + obj.elements[i].value);
    }
  }

  data.push('rank_id=' + document.getElementById('select_rank_id').value);
  data.push('from_sel=' + true);

  if (data.length > 0) {
    Ajax.call('users.php?act=vip_list', data.join('&'), fullSearchResponse, 'GET', 'JSON');
  }
}

/*添加预约服务模板*/
function addAppointment() {
  var user_id = document.getElementById('ID').value;
  if (user_id != 0) {
    Ajax.call('service.php?act=add_appointment_view', 'user_id=' + user_id, justShow, 'GET', 'JSON');
  }
}

//推迟或删除预约服务
function modAppointment(appointments_id, behave, obj) {
  if (parseInt(appointments_id) != 0) {
    var tr_index = obj.parentNode.parentNode.rowIndex;
    var alarm_time = '';

    if (behave == 'postphone') {
      var tdObj = obj.parentNode.parentNode.cells[2];
      var inputObjs = tdObj.getElementsByTagName('input');

      if (inputObjs.length <= 0) {
        return;
      } else {
        alarm_time = inputObjs[0].value;
      }
    }
    Ajax.call('service.php?act=mod_appointment', 'appointments_id=' + appointments_id + '&behave=' + behave + '&tr_index=' + tr_index + '&alarm_time=' + alarm_time, modAppointmentResp, 'GET', 'JSON');
  }
}

/*搜索预约服务*/
function schAppointments(obj) {
  var cldElements = obj.elements;
  var data = [];
  for (var i = 0; i < cldElements.length - 1; i++) {
    data.push(cldElements[i].name + '=' + cldElements[i].value);
  }

  data.push('sch=' + true);

  if (data.length > 0) {
    Ajax.call('service.php?act=book_service_list', data.join('&'), fullSearchResponse, 'GET', 'JSON');
  } else {
    return;
  }
}

function modAppointmentResp(res) {
  var tblObj = document.getElementById('appointments_tbl');
  if (res.behave == 'del') {
    if (res.code) {
      tblObj.deleteRow(res.tr_index);
    }
  } else if (res.behave == 'postphone') {
    if (res.code) {
      var tdObj = tblObj.rows[res.tr_index].cells[2];
      tdObj.innerHTML = '<label onclick="setPostphoneService(this,' + "'" + res.alarm_time + "'" + ',' + res.appointments_id + ')">' + res.alarm_time + '</lable>';
    }
  } else {
    return;
  }

  showMsg(res);
}

/*通用弹出显示*/
function justShow(res) {
  showMsg(res);
  var obj = document.getElementById('msg');
  var titleObj = obj.getElementsByTagName('h3');
  titleObj[0].innerHTML = res.title;
  obj.style.zIndex = 1002;
}

/*关闭弹出层*/
function closeShow() {
  document.getElementById('msg').className = 'hide';
  document.getElementById('msg').style.width = '319px';
}

/*设置input控件失去焦点后表格自动填值*/
function setPostphoneService(obj, alarm_time, appointments_id) {
  obj.parentNode.innerHTML = '<input class="Wdate" name="alarm_time" onclick="WdatePicker()" type="text" value="' + alarm_time + '" />';
}

/*
 * 事件提醒
 * 预约服务提醒
 * */
function traversalAppointment() {
  Ajax.call('service.php?act=traversal_appointments', '', showBottom, 'GET', 'JSON');
  setTimeout(traversalAppointment, 1200000);
}

//遮盖层
function showFade() {
  document.getElementById('fade').style.display = 'block';
  document.getElementById('fade').style.background = 'transparent';
}

function hideFade() {
  document.getElementById('fade').style.display = 'none';
  document.getElementById('fade').style.backgroundColor = '#333';
}

function showKeyword(obj) {
  obj.style.color = '#000';
}

/*搜索产品知识*/
function schKnowlage(obj) {
  var keyword = obj.elements['keyword'].value;
  if (keyword != '' && keyword.length >= 2) {
    Ajax.call('service.php?act=knowlage_list', 'keyword=' + keyword + '&behave=' + 'search', inMain, 'GET', 'JSON');
  } else {
    return;
  }
}

/*知识库操作*/
function knowlageCtr(knowlage_id, behave) {
  if (knowlage_id != 0) {
    Ajax.call('service.php?act=knowlage_ctr', 'knowlage_id=' + knowlage_id + '&behave=' + behave, knowlageCtrResp, 'GET', 'JSON');
  } else {
    return;
  }
}

function knowlageCtrResp(res) {
  if (res.behave == 'mod') {
    document.getElementById('sch_knowlage_main_div').style.display = 'none';
    document.getElementById('mod_knowlage_div').style.display = 'block';
    document.getElementById('mod_knowlage_div').innerHTML = res.main;
  } else {
    showMsg(res);
    var objForm = document.forms['sch_knowlage_form'];
    schKnowlage(objForm);
  }
}

/*知识库顶部操作*/
function getMoreKnowlage(behave) {
  if (behave == 'return') {

    if (document.getElementById('sch_knowlage_main_div')) {
      document.getElementById('sch_knowlage_main_div').style.display = 'block';
    }

    if (document.getElementById('general_sch_div')) {
      document.getElementById('general_sch_div').style.display = 'none';
    }

    document.getElementById('mod_knowlage_div').style.display = 'none';

  } else if (behave == 'general_sch') {

    if (document.getElementById('sch_knowlage_main_div')) {
      document.getElementById('sch_knowlage_main_div').style.display = 'none';
    }

    document.getElementById('mod_knowlage_div').style.display = 'none';
    document.getElementById('general_sch_div').style.display = 'block';
  }
}

/**
 * 显示服务相关录音
 */
function showRecList(sid) {
  if (sid) {
    Ajax.call('service.php?act=rec_list&service_id=' + sid, '', showRecListResp, 'GET', 'JSON');
  }
}

function showRecListResp(res) {
  showMsg(res);
  return false;
}

function showClassTape(fid) {
  if (fid) {
    Ajax.call('service.php?act=class_tape&favor_id=' + fid, '', showRecListResp, 'GET', 'JSON');
  }
}

/**
 * 添加录音到播放器
 */
function addToPlayer(obj) {
  document.getElementById('player').src = obj.getAttribute('src');
}

/**
 * 收藏录音
 */
function collectTape() {
  var tape = document.getElementById('player').src;
  if (!/.*?mp3$/.test(tape)) {
    document.getElementById('audioNotice').innerText = '播放器中没有任何录音！';
    setTimeout("document.getElementById('audioNotice').innerText = ''", 2000);
    return false;
  }
  var service_id = document.getElementById('service_id').value;
  Ajax.call('service.php?act=collect_tape', 'file=' + tape + '&service_id' + service_id, collectTapeResp, 'POST', 'JSON');
}

function collectTapeResp(res) {
  showMsg(res);
  return false;
}

/*获取某产品知识库*/
function getKnowlage(obj, goods_id) {
  var itemName = obj.getAttribute('name');
  if (itemName == 'introduction' && localStorage.getItem(goods_id + '_introduction')) {
    obj.firstChild.title = localStorage.getItem(goods_id + '_introduction');
    obj.id = goods_id + '_intro';
  } else {
    Ajax.call('service.php?act=get_knowlage', 'item_name=' + itemName + '&goods_id=' + goods_id, newWiwdowsResp, 'POST', 'JSON');
  }
}

function newWiwdowsResp(res) {
  if (res.item_name == 'introduction') {
    if (!localStorage.getItem(res.goods_sn + '_introduction') && res.content != null) {
      localStorage.setItem(res.goods_sn + '_introduction', res.content);
      if (document.getElementById(res.goods_sn + '_intro')) {
        document.getElementById(res.goods_sn + '_intro').firstChild.title = localStorage[res.goods_sn + '_introduction'];
      }
    }
  } else {
    if (windowObj != null) {
      windowObj.close();
    }

    if (res.is_exist) {
      windowObj = window.open(res.html_path, res.item_name, 'height=400, width=500, top=100, left=300, toolbar=no, menubar=no, scrollbars=yes,resizable=no,location=no, status=no');

      windowObj.document.clear();
      windowObj.document.write(res.content);
      windowObj.document.title = res.knowlage_name;
    } else {
      var msg = new Array();
      msg['timeout'] = 2000;
      msg['message'] = '暂时没有加添加' + res.class_name;
      showMsg(msg);
    }
  }
}

function schGoodsForKnowLage(obj) {
  Ajax.call('service.php?act=knowlage_list', 'keyword=' + obj.elements['keyword'].value, inMain, 'GET', 'JSON');
}

/*删除收藏的通话录音*/
function deleteFavoriteTape(obj, favor_id) {
  if (favor_id != 0) {
    var trIndex = obj.parentNode.parentNode.rowIndex;

    Ajax.call('service.php?act=favor_del', 'table_name=' + 'favor_table' + '&tr_index=' + trIndex + '&favor_id=' + favor_id, delTr, 'GET', 'JSON');
  } else {
    return;
  }
}

/*删除表行*/
function delTr(res) {
  if (res.req_msg == true) {
    showMsg(res);
  }

  if (res.code && res.table_name != '') {
    var obj = document.getElementById(res.table_name);
    obj.deleteRow(res.tr_index);
  }
}

function showTapeCollect(obj) {
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
  document.getElementById('tape_collect_copyright').value = obj.type;
  var formObj = document.forms['tape_collect_form'];
  schTapeCollect(formObj);
}

/*搜索通话录音收藏*/
function schTapeCollect(obj) {
  var tapeCollectCopyright = document.getElementById('tape_collect_copyright').value;
  Ajax.call('service.php?act=tape_favorite', 'user_name=' + obj.elements['user_name'].value + '&tape_collect_copyright=' + tapeCollectCopyright + '&from_sch=' + 'from_sch', fullSearchResponse, 'GET', 'JSON');
}

/*修改录音收藏的权限*/
function chTapePublic(obj, copyright, favor_id) {
  if (favor_id != 0) {
    var comment = '';
    var explain = '';
    if ('praise' == copyright) {
      comment = prompt('你的鼓励，将成为他人的前进动力!');
      if (comment == '' || comment == null) {
        return;
      }
    } else if ('public' == copyright) {
      explain = prompt('对录音简单描述 ... ...');
      if (explain == '' || explain == null) {
        return;
      }
    }

    var trIndex = obj.parentNode.parentNode.rowIndex;
    Ajax.call('service.php?act=ch_tape_public', 'favor_id=' + favor_id + '&tr_index=' + trIndex + '&table_name=' + 'favor_table' + '&copyright=' + copyright + '&comment=' + comment + '&explain=' + explain, delTr, 'GET', 'JSON');
  } else {
    return;
  }
}

/*清空录音收藏*/
function clearTape(item) {
  Ajax.call('service.php?act=clear_tape', 'item=' + item, showMsgRes, 'GET', 'JSON');
}

/*选择多选员工*/
function selectAdmin() {
  Ajax.call('service.php?act=select_admin', '', selectAdminResp, 'GET', 'JSON');
}

function selectAdminResp(res) {
  showMsg(res);
}

/*给通话录音添加说明*/
function addExplance(fid, obj) {
  var comment = prompt('请填写简单说明');
  if (comment) {
    obj.parentNode.innerHTML = '<button class="btn_new" onclick="addExplance(' + fid + ',this' + ')">' + comment + '</button>';
    Ajax.call('service.php?act=add_tape_explain', 'simple_explain=' + comment + '&favor_id=' + fid, showMsg, 'GET', 'JSON');
  }
}

/*通过分类选择通话录音*/
function selectTape(obj) {
  Ajax.call('service.php?act=select_tape', 'class=' + obj.value + '&tape_collect_copyright=' + 'boutique' + '&from_sch=' + 'from_sch', fullSearchResponse, 'GET', 'JSON');
}

/*更改通话录音类别*/
function showSelect(obj, fid) {
  var classId = obj.value;
  var tdObj = obj.parentNode;
  var classList = ['减肥', '补肾', '三高'];

  tdObj.id = 'td_' + fid;
  tdObj.innerHTML = '';
  for (var i = 0; i < classList.length; i++) {
    tdObj.innerHTML += '<label><input type="radio" name="tape_class" value="' + parseInt(i + 1) + '" title="' + fid + '" onclick="showSelectDone(this)" act="service.php?act=modify_tape_class"/>' + classList[i] + '</label>';
  }

}

/*公共*/
/*表格中输入框*/
function showSelectDone(obj) {
  Ajax.call(obj.getAttribute('act'), 'id=' + obj.title + '&value=' + obj.value, selectDoneRes, 'GET', 'JSON');
}

function selectDoneRes(res) {
  if (document.getElementById('td_' + res.id)) {
    document.getElementById('td_' + res.id).innerHTML = res.main;
  }
}

function chCondition(obj) {
  var selectObj = document.getElementById('platform_id');
  selectObj.style.display = '';
  if (10 == obj.value) {
    var arr = {
      'jd': '京东',
      '1mail': '1号店',
      'dangdang': '当当',
      'suning': '苏宁'
    };

    selectObj.length = 0;
    for (var i in arr) {
      if (i != 'toJSONString') {
        var opt = document.createElement('option');
        opt.value = i;
        opt.text = arr[i];
        selectObj.appendChild(opt);
      }
    }
    return;
  } else {
    selectObj.style.display = 'none';
    return false;
  }
}

//点击拨号
function quickCall(user_id,ext,manner){
  var obj = document.getElementById('call_manner');
  if (obj) {
    var f = obj.value;
  }
  if (user_id && ext) {
    Ajax.call('service.php?act=quick_call','user_id='+user_id + '&ext='+ext+'&manner='+manner+'&f='+f,quickCallResp,'GET','TEXT');
  }
}

function quickCallResp(res){
  var msg = [];
  if (1 == res) {
    msg['timeout'] = 8000;
    msg['message'] = '正在拨号..... 请拿起座机等候 :) <img src="images/rang.gif" style="float:right;" />';
  }else if(2 == res){
    msg['timeout'] = 2000;
    msg['message'] = '拨号失败，请重试';
  }
  showMsg(msg);
}

//标记消息
function markStatus(obj){
  var noticeId = obj.value;
  if (noticeId) {
    document.getElementById('m_'+noticeId).innerHTML = '已读';
    obj.parentNode.innerHTML = '已读';
    Ajax.call('service.php?act=mark_msg_status','notice_id='+noticeId,showMsg,'GET','JSON');
  }
}

function searchMsg(obj){
  var roleId = obj.elements['role_id'].value;
  var msgStatus = obj.elements['status'].value;
  Ajax.call('service.php?act=sys_msg','role_id='+roleId+'&status='+msgStatus,sendToServerResponse,'GET','JSON');
}
