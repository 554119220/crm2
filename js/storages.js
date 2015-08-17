/**
 * 热销商品修改
 */
function changeHotStats(gid) {
  Ajax.call('storage.php', 'act=change_hot_stats&goods_id=' + gid, changeHotStatsResp, 'POST', 'JSON');
}

/**
 * 热销商品修改 回调函数
 */
function changeHotStatsResp(res) {
  document.getElementById('goods_' + res.goods_id).src = 'images/' + res.is_hot + '.gif';
}

/**
 * 按品牌列出商品
 */
function searchGoodsByBrand(obj) {
  var keyword = document.getElementById('keyword').value;
  var brand_id = document.getElementById('brand_id').value;

  keyword = keyword == '商品名称/编号' ? '': keyword;

  var data = new Array();
  if (keyword) {
    data.push('&keyword=' + keyword);
  }

  if (brand_id != 0) {
    data.push('&brand_id=' + brand_id);
  }

  Ajax.call(obj.value, data.join('&'), searchGoodsByBrandResp, 'GET', 'JSON');
}

/**
 * 按品牌列出商品 回调函数
 */
function searchGoodsByBrandResp(res) {
  document.getElementById('main').innerHTML = res.main;
  init();
}

/**
 * 按照库存及产品有效期查询商品
 */
function filterGoods(obj) {
  var label = obj.parentNode.parentNode.getElementsByTagName('label');
  for (var i = 0; i < label.length; i++) {
    label[i].className = '';
  }

  obj.parentNode.className = 'input_checked';

  var target = document.getElementById('target').value;
  Ajax.call(target, 'filter=' + obj.value, filterGoodsResp, 'GET', 'JSON');
}

/**
 * 按照库存及产品有效期查询商品 回调函数
 */
function filterGoodsResp(res) {
  document.getElementById('main').innerHTML = res.main;
  init();
}

/*库存排序*/
function goodsSort(sort_by) {
  var sortOrder = document.getElementById('sort_order').value;
  var target = document.getElementById('target').value;
  var elements = document.getElementsByTagName('input');
  var filter = '';

  for (var i = 0; i < elements.length; i++) {
    if (elements[i].name = 'filter' && elements[i].type == 'radio' && elements[i].checked) {
      filter = elements[i].value;
    }
  }

  Ajax.call(target, 'sort_order=' + sortOrder + '&sort_by=' + sort_by + '&filter=' + filter, filterGoodsResp, 'GET', 'JSON');
}

/**
 * 添加新的产品
 */
function addNewProduct() {
  var theForm = document['newProduct'];
  var data = 'act=add_goods_submit';

  var msg = new Array();
  for (var i = 0; i < theForm.length; i++) {
    switch (theForm[i].type) {
      case 'text':
        formsValue = theForm[i].value;
        break;
      case 'number':
        formsValue = parseInt(theForm[i].value);
        break;
      case 'select-one':
        formsValue = parseInt(theForm[i].value);
        break;
      case 'radio':
        if (theForm[i].checked) {
          if (theForm[i].name == 'goods_type' && theForm[i].value == 0) if (!confirm('确定将该商品设置为赠品？')) return false;
          data += '&' + theForm[i].name + '=' + theForm[i].value;
        };
        continue;
    }

    if (formsValue || theForm[i].name == 'per_number_units') {
      data += '&' + theForm[i].name + '=' + theForm[i].value;
    } else {
      var field = theForm[i].parentNode.previousSibling.nodeType == 1 ? theForm[i].parentNode.previousSibling.innerHTML: theForm[i].parentNode.previousSibling.previousSibling.innerHTML;
      msg.message = field + '不能为空！';
      msg.timeout = 3000;
      showMsg(msg);
      return false;
    }
  }

  var pattern = /(run.*?goods_type)/i;
  if (!pattern.test(data)) {
    msg.message = '经营类型或产品类型：请选择经营类型或产品类型！';
    msg.timeout = 3000;
    showMsg(msg);
    return false;
  };

  Ajax.call('storage.php', data, addNewProductResponse, 'POST', 'JSON');
}

/**
 * 添加新产品 (回调函数)
 */
function addNewProductResponse(res) {
  showMsg(res);
  var theForm = document['newProduct'];
  for (var i = 0; i < theForm.length; i++) {
    if (theForm[i].type == 'input') theForm[i].value = '';
  }
}

/**
 * 显示商品详细信息
 */
function showGoodsDetail(gid) {
  var trObj = document.getElementById('temp');
  if (trObj) {
    trObj.parentNode.deleteRow(trObj.rowIndex);
    return false;
  };
  Ajax.call('storage.php', 'act=get_detail&goods_id=' + gid, showGoodsDetailResp, 'POST', 'JSON');
}

/**
 * 商品详细信息  回调函数
 */
function showGoodsDetailResp(res) {
  var trObj = document.getElementById('tr_2_' + res.id);
  var rowObj = trObj.parentNode.insertRow(trObj.rowIndex + 1);
  rowObj.style.background = '#fff';
  rowObj.id = 'temp';

  var cellObj = rowObj.insertCell(0);
  cellObj.colSpan = trObj.cells.length;

  cellObj.innerHTML = res.info;
}

/**
 *  产品名称自动填充
 **/
function autoNum(obj, key) {
  if (obj != '') {
    var str = 'num=' + obj + '&key=' + key;
    Ajax.call('storage.php?act=autonum', str, autoNumResponse, 'POST', 'JSON');
  }

}

/**
 *  产品名称自动填充回调函数
 **/
function autoNumResponse(res) {
  document.getElementById('goods_name' + res.key + '').value = res.goodsname;
}

/**
 *  添加一行进货单记录
 **/
function addStock(obj) {
  var table = document.getElementById('add_gn'); // 获取表格对象
  var rows = table.rows.length; //获取表格的行数
  var rowObj = obj.parentNode.parentNode; // 获取当前文本所在的行对象
  var rowInnerHtml = rowObj.innerHTML; //获取当前文本所在行的内容
  var i = parseInt(Math.random() * 30); //获取一个随机数 
  var goodsname = 'goods_name' + rows + i; //要替换的goodsname
  var autoNum = ',' + rows + i; //要替换的autoNum的值
  // 将添加行元素的函数 addStock 替换为删除行元素的函数 removeStock
  rowInnerHtml = rowInnerHtml.replace(/addStock/, 'removeStock');
  rowInnerHtml = rowInnerHtml.replace(/\+/, '-'); // 替换 添加 + 为 删除 -
  rowInnerHtml = rowInnerHtml.replace(/goods_name0/, goodsname); // 替换添加goodname的id
  rowInnerHtml = rowInnerHtml.replace(/,0/, autoNum); // 替换添加autoNum的值
  var newRow = table.insertRow(rows); // 在表格尾部添加一行
  newRow.innerHTML = rowInnerHtml; // 在新添加的行元素中填充内容
  var input = newRow.getElementsByTagName('input');
  for (var i in input) {
    input[i].value = '';
  }

}

/**
 *  删除一行进货单记录
 **/
function removeStock(obj) {
  var table = document.getElementById('add_gn'); // 获取表格对象
  table.deleteRow(obj.parentNode.parentNode.rowIndex); // 删除相应的行
}

/**
 *  提交进货单
 **/
function addReceipts(obj) {
  var table = document.getElementById('add_gn');
  var form  = document.forms['receipt'];
  var data  = [];

  for (var i = 0; i < form.elements.length; i++) {
    if (typeof(form.elements[i]) == 'object' && form.elements[i].name != '') {
      data.push(form.elements[i].name + '=' + form.elements[i].value);
    }else continue;
  }

  Ajax.call('storage.php?act=add_new_receipt', data.join('&'), addReceiptsResponse, 'POST', 'JSON');
}

/**
 *  提交进货单回调函数
 **/
function addReceiptsResponse(res) {
  if (res.req_msg) {
    showMsg(res);
  }
}

/**
 *  编辑进货单
 **/
function editStock(obj) {
  var form = document.forms['edit_receipt'];
  var str = '';

  for (var i in form.elements) {
    if (typeof(form.elements[i]) == 'object' && form.elements[i].name != '') {
      str = str + '&' + form.elements[i].name + '=' + form.elements[i].value;
    }
  }

  Ajax.call('storage.php?act=update', str, editStockResponse, 'POST', 'JSON');
}

/**
 *  编辑进货单回调函数
 **/
function editStockResponse(res) {
  if (res.req_msg) {
    showMsg(res);
  }
}

/**
 * 在表格中插入库存批次详情行
 * @param   data  obj 数据集
 */
function insertStockBatchRow(data) {
  // 判断是否要折叠详细信息临时行
  var delTr = document.getElementById('temp_2'); // 获取临时行对象
  if (delTr != null) {
    // 删除详细信息临时行
    var trId = delTr.previousSibling.previousSibling.id;
    delTr.parentNode.deleteRow(delTr.rowIndex);
  }

  if (trId != 'tr_2_' + data.id) {
    // 右侧表格
    var rowObj = document.getElementById('tr_2_' + data.id); // 获取当前行的对象
    var tbObj = rowObj.parentNode; // 获取表格对象
    var insRows = tbObj.insertRow(rowObj.rowIndex + 1); // 在当前行之后插入一行
    insRows.style.background = '#fff'; // 设置插入行的背景色
    insRows.id = 'temp_2'; // 给插入的行添加临时id
    var insCell = insRows.insertCell(0); // 给新插入行添加单元格
    //alert(insCell.style.vAlign);
    insCell.vAlign = 'top'; // 设置单元格内容顶部显示
    insCell.colSpan = 10; // 设置新添加的单元格所占列数
    insCell.innerHTML = data.info; // 向新单元格中添加数据
  }
}

/**
 * 在表格中插入进货单详情行
 * @param   data  obj 数据集
 */
function insertStockRow(data) {
  // 判断是否要折叠详细信息临时行
  var delTr = document.getElementById('temp_2'); // 获取临时行对象
  if (delTr != null) {
    // 删除详细信息临时行
    var trId = delTr.previousSibling.previousSibling.id;
    delTr.parentNode.deleteRow(delTr.rowIndex);
  }

  if (trId != 'tr_2_' + data.id) {
    // 右侧表格
    var rowObj = document.getElementById('tr_2_' + data.id); // 获取当前行的对象
    var tbObj = rowObj.parentNode; // 获取表格对象
    var insRows = tbObj.insertRow(rowObj.rowIndex + 1); // 在当前行之后插入一行
    insRows.style.background = '#fff'; // 设置插入行的背景色
    insRows.id = 'temp_2'; // 给插入的行添加临时id
    var insCell = insRows.insertCell(0); // 给新插入行添加单元格
    //alert(insCell.style.vAlign);
    insCell.vAlign = 'top'; // 设置单元格内容顶部显示
    insCell.colSpan = 5; // 设置新添加的单元格所占列数
    insCell.innerHTML = data.info; // 向新单元格中添加数据
  }
}

//查询滞销产品
function filterDeadStock(obj) {
  var brand = obj.elements['brand'].value;
  var goods_name = obj.elements['goods_name'].value;
  var production_start = obj.elements['production_start'].value;
  var production_end = obj.elements['production_end'].value;
  var arrival_start = obj.elements['arrival_start'].value;
  var arrival_end = obj.elements['arrival_end'].value;
  var from_sch = true;

  var data = {
    'brand': brand,
    'goods_name': goods_name,
    'production_start': production_start,
    'production_end': production_end,
    'arrival_start': arrival_start,
    'arrival_end': arrival_end,
    'from_sch': from_sch,
  };

  Ajax.call('storage.php?act=dead_stock', data, filterDeadStockRes, 'POST', 'JSON');
}

function filterDeadStockRes(res) {
  document.getElementById('resource').innerHTML = res.main;
  init();
  timelyStockAlarm();
}

//检索每日订单
function filterEverydayCheck(obj) {
  var data = [];
  for (var i = 0; i < obj.elements.length - 1; i++) {
    if (obj.elements[i].name == 'month') continue;
    if (obj.elements[i].value != '') data.push(obj.elements[i].name + '=' + obj.elements[i].value);
  }

  var current_day = new Date();
  data.push('day=' + obj.month.value + '-' + current_day.getDate());

  Ajax.call('finance.php?act=everyday_order_check', data.join('&'), sendToServerResponse, 'POST', 'JSON');
}

//获取当前品牌库存修改开关状态
function stockSwitch(brand_id) {
  var behave = 'show_switch_status';
  Ajax.call('storage.php?act=stock_switch', 'brand_id=' + brand_id + '&behave=' + behave, stockSwitchRes, 'GET', 'JSON');
}

function stockSwitchRes(res) {
  if (res.mod_stock_status > 0) {
    document.getElementById('current_stauts_desc').innerHTML = '<font color="red">当前状态：开启</font>';
  }
  else if (res.mod_stock_status == 0) {
    document.getElementById('current_stauts_desc').innerHTML = '<font color="red">当前状态：未开启</font>';
  }
  timelyStockAlarm();
}

//开启或关闭修改库存
function modSwitch() {
  var brand_id = document.getElementById('stock_brand_id').value;
  var behave = 'mod_stock_status_time';

  Ajax.call('storage.php?act=stock_switch', 'brand_id=' + brand_id + '&behave=' + behave, stockSwitchRes, 'GET', 'JSON');
}

//修改库存模板
function modStockQuantity(rec_id, quantity, behave, myself) {
  if (behave == 'get_input') {
    myself.parentNode.innerHTML = '<input type="number" onblur="modStockQuantity(' + rec_id + ',' + 'this.value' + ',' + "'modify_quantity'" + ',this' + ')" value="' + quantity + '"/>';
  }
  else if (behave == 'modify_quantity') {
    Ajax.call('storage.php?act=modify_stock', 'rec_id=' + rec_id + '&quantity=' + myself.value, modStockQuantityRes, 'GET', 'JSON');
  }
}

function modStockQuantityRes(res) {
  var p_tag = document.getElementById('msg').getElementsByTagName('p');
  if (res.code) {
    document.getElementById('td_' + res.rec_id).innerHTML = '<label ondblclick="modStockQuantity(' + res.rec_id + ',' + res.quantity + ",'get_input',this)" + '">' + res.quantity + '</label>';
    p_tag[0].innerHTML += '<span id="pop_alarm"><font color="red">修改成功</font><span>';
  }
  else {
    p_tag[0].innerHTML += '<span id="pop_alarm"><font color="red">修改失败</font></span>';
  }
  setTimeout('document.getElementById("pop_alarm").innerHTML=""', 2000);
}

//实时库存
function timelyStockAlarm() {
  //当月第一天清理本地数据
  var objDate = new Date();
  if (objDate.getDate() == 1 || localStorage.alarmTime != objDate.getDate()) {
    localStorage.clear();
  }

  var stoAlarmTimes = 0;
  if (localStorage.stoAlarmTimes) {
    stoAlarmTimes = localStorage.stoAlarmTimes;
  }
  Ajax.call('storage.php', 'act=timely_stock_alarm' + '&sto_alarm_times=' + stoAlarmTimes, timelyStockAlarmRes, 'GET', 'JSON');
}

function timelyStockAlarmRes(res) {
  if (res.donot_alarm) {
    return;
  } else {
    document.getElementById('fade').style.display = 'block';
    document.getElementById('msgBtn').className = 'hide';
    showMsg(res);
    var obj = document.getElementById('msg');
    var titleObj = obj.getElementsByTagName('h3');
    titleObj[0].innerHTML = '库存警报';
    obj.style.zIndex = 1002;
  }
}

//确认库存提醒
function confirmStockAlarm() {
  if (document.getElementById('goods_sn')) {
    var goodsSn = document.getElementById('goods_sn').value;
  } else {
    var goodsSn = '';
  }

  if (goodsSn) {
    if (document.getElementById('arrival_time')) {
      var arrivalTime = document.getElementById('arrival_time').value;
      var addStoOrderTime = document.getElementById('add_sto_order_time').value;
    } else {
      var arrivalTime = 0;
    }
    if (!document.getElementById('confirm_pwd').value) {
      return;
    } else {
      var confirm_pwd = document.getElementById('confirm_pwd').value;
    }

    if (arrivalTime && addStoOrderTime) {
      Ajax.call('storage.php?act=confirm_stock_alarm', 'goods_sn=' + goodsSn + '&arrival_time=' + arrivalTime + '&add_sto_order_time=' + addStoOrderTime + '&confirm_pwd=' + confirm_pwd, confirmStockAlarmResp, 'GET', 'JSON');
    } else {
      document.getElementById('arrival_time').autofocus = true;
    }
  } else {
    if (localStorage.stoAlarmTimes == null) {
      localStorage.stoAlarmTimes = 1;
    } else {
      localStorage.stoAlarmTimes++;
    }

    //每隔一天清理本地缓存
    var aTime = new Date();
    if (localStorage.alarmTime != aTime.getDate()) {
      localStorage.clear();
    }

    localStorage.alarmTime = aTime.getDate();
    document.getElementById('fade').style.display = 'none';
    document.getElementById('msg').className = 'hide';
    var body = document.body;
    if (document.getElementById('clip')) {
      //objDiv = document.getElementById('clip');
      //body.removeChild(objDiv);
    }
  }
}

function confirmStockAlarmResp(res) {
  if (res.pwd_error) {
    document.getElementById('confirm_pwd').value = '';
    document.getElementById('confirm_pwd').focus();
    return;
  }

  if (res.code) {
    timelyStockAlarm();
  } else {
    showMsg(res);
    document.getElementById('fade').style.display = 'none';
  }
}

//保存库存提醒的localStorage
function setStockAlarmCookie(res) {
  localStorage['goodsSn' + res.goods_sn] += 1;
  localStorage['stoConfirmAlarm' + res.goods_sn] = res.alarm_sto_time;
}

//全选要确认的紧急库存
function selectAllStockAlarm(objForm, objInp, checkName) {
  var selectField = objForm.getElementsByTagName('input');
  for (var i = 0; i < selectField.length; i++) {
    if (selectField[i].type == 'checkbox' && selectField[i].name == checkName) {
      selectField[i].checked = objInp.checked;
    }
  }
}

//修改安全库存
function editInpValue(obj, warn_number, goods_id) {
  if (obj.type != 'number') {
    obj.parentNode.innerHTML = '<input id="warn_number_field" type="number" style="width:48px" onblur="editInpValue(this,this.value,' + goods_id + ')" value="' + obj.innerHTML + '"/>';
    if (document.getElementById('warn_number_field')) {
      document.getElementById('warn_number_field').focus();
    }
  } else {
    if (warn_number != '') {
      Ajax.call('storage.php?act=edit_warn_number', 'warn_number=' + warn_number + '&goods_id=' + goods_id, editInpValueResp, 'GET', 'JSON');
    } else return;
  }
}

function editInpValueResp(res) {
  if (res.code) {
    showMsg(res);
    document.getElementById('td_' + res.goods_id).innerHTML = res.warn_number;
  } else {
    return;
  }
}

//商品上、下架
function isOnsale(val, goods_id, obj) {
  if (goods_id != '') {
    var rowIndex = obj.parentNode.parentNode.rowIndex;
    Ajax.call('storage.php?act=edit_on_sale', 'goods_id=' + goods_id + '&is_on_sale=' + val + '&row_index=' + rowIndex, isOnsaleResp, 'GET', 'JSON');
  }
}

function isOnsaleResp(res) {
  showMsg(res);
  if (res.code) {
    var obj = document.getElementById('goods_table');
    obj.deleteRow(res.row_index);
  }
}

//下架0库存产品
function soldOutZero() {
  if (confirm('您确认要将所有0库存商品下架')) {
    Ajax.call('storage.php?act=sold_out_zero', '', soldOutZeroResp, 'GET', 'JSON');
  }
}

function soldOutZeroResp(res) {
  showMsg(res);
  Ajax.call('storage.php?act=goods_list', '', sendToServerResponse, 'GET', 'JSON');
}

//开始盘点库存
function startInventory(obj) {
  var behave = 1;
  if (obj.value == String('取消盘点')) {
    behave = 0;
  } else if (obj.value == String('开始盘点')) {
    behave = 1;
  } else if (obj.value == String('结束盘点')) {
    behave = 2;
  }

  if (confirm('您确定要' + obj.value)) {
    Ajax.call('storage.php?act=stocktake_add', 'behave=' + behave, startInventoryResp, 'GET', 'JSON');
  }
}

function startInventoryResp(res) {
  showMsg(res);
  document.getElementById('sta_inv_btn').value = res.behave == 1 ? '结束盘点': '开始盘点';
  document.getElementById('can_inv_btn').type = res.behave == 1 ? 'button': 'hidden';
  document.getElementById('time_title').innerHTML = res.behave == 1 ? res.start_time: '';

  if (res.behave != 0) {
    var startTimeSel = document.getElementById('start_time');
    var endTimeSel = document.getElementById('end_time');
  }

  if (res.behave == 1) {
    startTimeSel.options[0].selected = endTimeSel.options[0].selected = true;
  } else if (res.behave == 2) {

    var opt = document.createElement('option');
    opt.value = res.unix_start_time;
    opt.text = res.start_time;
    startTimeSel.add(opt, startTimeSel[1]);
    var opt = document.createElement('option');
    opt.value = res.unix_end_time;
    opt.text = res.end_time;
    endTimeSel.add(opt, endTimeSel[1]);

    startTimeSel.options[1].selected = endTimeSel.options[1].selected = true;
  }
  document.getElementById('resource').innerHTML = res.main;
  init();
}

function getGoodsBybrand(brand_id) {
  Ajax.call('storage.php?act=get_goods_by_brand', 'brand_id=' + brand_id, getGoodsBybrandResp, 'GET', 'JSON');
}

function getGoodsBybrandResp(goods) {
  var obj = document.getElementById('goods_id');
  var opt = document.createElement('option');

  obj.length = 0;
  opt.value = 0;
  opt.text = '请选择商品';

  obj.appendChild(opt);

  for (var i in goods) {
    if (typeof(goods[i]) == 'function') continue;

    var opt = document.createElement('option');
    opt.value = goods[i].goods_sn;
    opt.text = goods[i].goods_name;
    obj.appendChild(opt);
  }
}

//搜索库存盘点记录
function schInventorySto(obj) {
  var objElementsLength = obj.elements.length;
  var arrDate = new Array();
  var strDate = '';

  for (var i = 0; i < objElementsLength - 1; i++) {
    if (obj.elements[i].name == 'goods_sn') {
      obj.elements[i].value = "'" + obj.elements[i].value + "'";
    }
    arrDate.push(obj.elements[i].name + '=' + obj.elements[i].value);
  }

  strDate = arrDate.join('&');
  Ajax.call('storage.php?act=sch_inventory_list', strDate, schInventoryStoRes, 'GET', 'JSON');
}

function schInventoryStoRes(res) {
  document.getElementById('resource').innerHTML = res.main;
  if (res.p_url != '') {
    document.getElementById('a_printer').href = res.p_url;
  }
  init();
}

//选择盘点时间
function getInventoryTime(obj) {
  if (obj.value == 0) {
    document.getElementById('start_time').options[0].selected = true;
    document.getElementById('end_time').options[0].selected = true;
    return;
  }
  var timeType = obj.id == 'start_time' ? 0: 1;
  var time_val = obj.value;
  Ajax.call('storage.php?act=get_inventory_time', 'time_type=' + timeType + '&time_val=' + time_val, getInventoryTimeResp, 'GET', 'JSON');
}

function getInventoryTimeResp(res) {
  if (res.time_type) {
    var selObj = document.getElementById('start_time');
  } else {
    var selObj = document.getElementById('end_time');
  }

  optList = selObj.options;
  for (var i = 0; i < optList.length; i++) {
    if (optList[i].value == res.time_val) {
      optList[i].selected = true;
      break;
    }
  }
}

//修改实际库存
function modActualQuantity(obj, quantity, storage_id) {
  if (obj.type != 'number') {
    obj.parentNode.innerHTML = '<input id="actual_quantity_field" type="number" style="width:48px" onblur="modActualQuantity(this,this.value,' + storage_id + ')" value="' + obj.innerHTML + '"/>';
    if (document.getElementById('actual_quantity_field')) {
      document.getElementById('actual_quantity_field').focus();
    }
  } else {
    if (quantity != '') {
      Ajax.call('storage.php?act=mod_actual_quantity', 'quantity=' + quantity + '&storage_id=' + storage_id, modActualQuantityResp, 'GET', 'JSON');
    } else return;
  }
}

function modActualQuantityResp(res) {
  if (res.code) {
    showMsg(res);
    document.getElementById('td_' + res.storage_id).innerHTML = res.quantity;
  } else {
    return;
  }
}

// 列出名单
function listAdmin(obj) {
  if (obj.value == 0) {
    Ajax.call('storage.php?act=stock_alarm_site', '', limitResponse, 'GET', 'JSON');
  } else {
    Ajax.call('storage.php?act=list_admin', 'role_id=' + obj.value, listAdminResp, 'GET', 'JSON');
  }
}

function listAdminResp(res) {
  if (res) {
    var trObj = tdObj = {};
    var objTable = document.getElementById('admin_list_tbl');
    objTable.innerHTML = '';
    var k = 0;
    var trLength = Math.ceil(res.length / 7);
    for (var i = 0; i < trLength; i++) {
      trObj = objTable.insertRow(i);
      for (var j = 0; j < 7; j++) {
        k = parseInt(i * 7 + j);
        tdObj = trObj.insertCell(j);
        if (k < res.length) {
          tdObj.innerHTML = '<label><input type="checkbox" name="checkbox" value="' + res[k]['user_id'] + '"/>  ' + res[k]['user_name'] + '</label>';
        }
      }
    }
  }
  return;
}

//设置可弹库存警报员工
function setAlarmAdmin(obj) {
  var obj = obj.form;
  var elementsList = obj.getElementsByTagName('input');
  var adminList = [];

  for (var i = 0; i < elementsList.length; i++) {
    if (elementsList[i].type == 'checkbox' && elementsList[i].name == 'checkbox' && elementsList[i].checked) {
      adminList.push(elementsList[i].value);
    }
  }

  if (adminList) {
    Ajax.call('storage.php?act=set_alarm_admin', 'admin_list=' + adminList.join(","), onlyShowMsg, 'GET', 'JSON');
  } else {
    return;
  }
}

/*checkbox 操作*/
function checkedAll(obj) {
  var objForm = obj.form;
  var elementsList = objForm.elements;

  for (var i = 0; i < elementsList.length; i++) {
    if (elementsList[i].name == 'checkbox' && elementsList[i].type == 'checkbox') {
      elementsList[i].checked = obj.checked;
    }
  }
}

/*添加订货表单*/
function addOrderSheet(goods_sn, goods_name) {
  if (goods_sn != '' && goods_name != '') {
    Ajax.call('storage.php?act=add_order_sheet_form', 'goods_sn=' + goods_sn + '&goods_name=' + goods_name, popupForm, 'POST', 'JSON');
  } else {
    return;
  }
}

/*添加订货操作*/
function addOrderSheetDone(obj) {
  var data = [];
  var elementsList = obj.elements;
  var error = false;

  for (var i = 0; i < elementsList.length - 1; i++) {
    if (elementsList[i].name == 'production_day' && elementsList[i].value == '') {
      document.getElementById('td_alarm').innerHTML = "<font color=red>生产日期不能为空</font>";
      error = true;
      break;
    }

    if (elementsList[i].name == 'quantity' && elementsList[i].value <= 0) {
      document.getElementById('td_alarm').innerHTML = "<font color=red>订货数量不能为0</font>";
      error = true;
      break;
    }

    if (elementsList[i].name == 'manufacturer') {
      var optList = elementsList[i].options;
      optList[elementsList[i].selectedIndex].value = optList[elementsList[i].selectedIndex].text;
    }

    data.push(elementsList[i].name + '=' + elementsList[i].value);
  }

  if (!error) {
    document.getElementById('msg').className = 'hide';
    Ajax.call('storage.php?act=add_order_sheet_done', data.join('&'), addOrderSheetDoneResp, 'GET', 'JSON');
  } else {
    return;
  }
}

function addOrderSheetDoneResp(res) {
  showMsgRes(res);
  getIndexAlarm(1, 7);
}

//搜索订货记录
function schOrderSheet(obj) {
  var data = [];
  var elements = obj.elements;

  for (var i = 0; i < elements.length; i++) {
    data.push(elements[i].name + '=' + elements[i].value);
  }

  Ajax.call('storage.php?act=sch_order_sheet', data.join('&'), fullSearchResponse, 'GET', 'JSON');
}

/*订货列表操作*/
function controlOrderSheet(behave, order_sheet_id, obj) {
  if (order_sheet_id != 0) {
    var url = 'storage.php?act=control_order_sheet';
    var data = [];
    data.push('behave' + '=' + behave);
    data.push('order_sheet_id' + '=' + order_sheet_id);

    switch (behave) {
      case 'del':
        var trIndex = obj.parentNode.parentNode.rowIndex;
        data.push('tr_index' + '=' + trIndex);
        Ajax.call(url, data.join('&'), controlOrderSheetResp, 'GET', 'JSON');
        break;
      case 'mod_form':
        break;
      case 'mod_done':
        break;
      case 'arrival':
        break;
    }
  } else {
    return;
  }
}

function controlOrderSheetResp(res) {
  switch (res.behave) {
    case 'del':
      showMsg(res);
      if (res.code) {
        var tblObj = document.getElementById('order_sheet_table');
        tblObj.deleteRow(res.tr_index);
        var formObj = document.forms['schOrderSheetForm'];
        schOrderSheet(formObj);
      }
      break;
    case 'mod_form':
      break;
    case 'mod_done':
      break;
    case 'arrival':
      break;
  }

  return;
}

/**
 * 显示/隐藏品牌
 */
function showOrHideBrand(bid) {
  Ajax.call('storage.php', 'act=show_or_hide&brand_id=' + bid, showOrHideBrandResp, 'POST', 'JSON');
}

function showOrHideBrandResp(res) {
  showMsg(res);
  if (res.success) {
    var brandObj = null;
    document.getElementById('brand_' + res.brand_id).src = 'images/' + res.is_show + '.gif';
  }
}


/*创建仓库调拨记录*/
function createAllot() {
  if (document.getElementById('add_allot_div')) {
    document.getElementById('resource').innerHTML = '';
  } else {
    Ajax.call('storage.php?act=create_allot', 'behave=show', fullSearchResponse, 'GET', 'JSON');
  }
}

function schAllot(obj) {

}

function getPdcDay(goods_sn) {
  if (goods_sn) {
    Ajax.call('storage.php?act=get_pdc_day', 'goods_sn=' + goods_sn, inSelect, 'GET', 'JSON');
  }
}

function inSelect(res) {
  if (res.length > 0) {
    var sltObj = document.getElementById(res.id);
    sltObj.options.length = 0;

    var optObj = document.createElement('option');
    optObj.value = 0;
    optObj.text = res.text;
    sltObj.appendChild(optObj);

    for (var i = 0; i < res.options.length; i++) {
      var optObj = document.createElement('option');
      optObj.value = res.options[i].value;
      optObj.text = res.options[i].text;
      sltObj.appendChild(optObj);
    }
  }
}

/*修改商品状态*/
function modGoodsStatus(obj) {
  if (obj.getAttribute('value')) {
    obj.parentNode.id = 'field_' + obj.getAttribute('value');
    Ajax.call('storage.php?act=mod_goods_status', 'goods_sn=' + obj.getAttribute('value') + '&td_id=' + obj.parentNode.id + '&status=' + obj.getAttribute('sta'), inTd, 'GET', 'JSON');
  }
}

/*将结果填入单元格中*/
function inTd(res) {
  if (res.req_msg) {
    showMsgRes(res);
  }

  if (document.getElementById(res.td_id)) {
    document.getElementById(res.td_id).innerHTML = res.content;
  }
}

/*鼠标经过显示控制图标*/
function mouseoverShowCtr(id, sta) {
  if (document.getElementById(id)) {
    if (sta) {
      document.getElementById(id).style.display = 'inline';
    } else {
      document.getElementById(id).style.display = 'none';
    }
  }
}

