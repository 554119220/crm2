var nav;           // 导航
var link;          // a链接
var keepShow;      // 显示隐藏菜单
var delay;         // 保存setTimeout的变量
var main;          // 主数据区
var keepShowTemp;  // 保存显示的菜单对象
var action = null; // 保存向服务器请求的act
var timer  = null;

var offsetDisX = document.getElementById('left').offsetWidth;
window.onload  = init();

/**
 * 消息提示框
 */
function showMsg (msg) {
  var objBtn  = document.getElementById('msgBtn');
  var objMsg  = document.getElementById('msg');
  var objBody = document.body;
  var objDiv  = null;
  objBtn.value = '确定';
  if (!document.getElementById('clip')) {
    objDiv = document.createElement('div');
    // 添加遮罩层
    objDiv.style.background = 'rgba(194,194,194,0.2)';
    objDiv.style.position   = 'absolute';
    objDiv.style.top        = 0;
    objDiv.style.left       = 0;
    objDiv.style.width      = '100%';
    objDiv.style.height     = '1000%';
    objDiv.style.zIndex     = 999;
    objDiv.style.opacity    = '0.6';
    objDiv.id = 'clip';

    objBody.appendChild(objDiv);

    // 隐藏溢出内容/滚动条
    objBody.style.overflow = 'hidden';

    // 显示提示信息
    objMsg.className = 'show';
  } else {
    objDiv = document.getElementById('clip');
  }

  objMsg.getElementsByTagName('h3')[0].innerHTML = msg.title ? msg.title : '消息提示';

  var objShut = document.createElement('strong');
  objShut.innerHTML = '×';
  objShut.style.float = 'right';
  objShut.style.marginRight = '8px';
  objShut.style.cursor = 'pointer';
  objMsg.getElementsByTagName('h3')[0].appendChild(objShut);

  if (!msg.manual_hide) {
    objDiv.onclick = objShut.onclick = function () {
      objMsg.className = 'hide';
      objBody.removeChild(objDiv);
      objBody.style.overflow = 'auto';
      clearTimeout(timer);
    };
  }

  objMsg.getElementsByTagName('p')[0].innerHTML  = msg.message;

  if (parseInt(msg.timeout)) {
    timer = setTimeout(function () {
      objMsg.className = 'hide';
      objBtn.className = 'hide';
      objBody.removeChild(objDiv);
      objBody.style.overflow = 'auto';
    }, msg.timeout);
  } else if (msg.swt) {
    if (msg.btncontent != false) {
      objBtn.value = msg.btncontent ? msg.btncontent : '确定';
      objBtn.type = msg.type;
      objBtn.className = 'show';
      objBtn.setAttribute('form', msg.form_id);
      objBtn.onclick = function () {
        objMsg.className = 'hide';
        objBody.removeChild(objDiv);
        objBody.style.overflow = 'auto';
        objBtn.className = 'hide';
      };
    }
  } else {
    if (msg.btncontent != false) {
      objBtn.value = msg.btncontent ? msg.btncontent : '确定';
      objBtn.className = 'show';
      objBtn.onclick = function () {
        objMsg.className = 'hide';
        objBody.removeChild(objDiv);
        objBody.style.overflow = 'auto';
        objBtn.className = 'hide';
      };
    }
  }

  if (msg.hide_btn) {
    objBtn.className = 'hide';
  }

  objMsg.style.left = parseInt((objMsg.parentNode.offsetWidth - parseInt(getComputedStyle(objMsg, false)['width']))/2) + 'px';
  objMsg.style.top  = parseInt((objMsg.parentNode.offsetHeight - parseInt(getComputedStyle(objMsg, false)['height']))/4) + objBody.scrollTop+'px';

  if (msg.redirectURL && msg.redirectURL.length) {
    Ajax.call(msg.redirectURL, '', sendToServerResponse, 'GET', 'JSON');
  }
  msg = null;
}

/**
 * 初始化页面脚本
 */
function init () {
  cellObjSelect();  // 表格列高亮
  hightLightRows(); // 不同表格同行高亮
  addSortEvent();   // 表格绑定排序
  main = document.getElementById('main');
  link = document.getElementsByTagName('a');
  for (var k = 0; k < link.length; k++)
  {
    if (link[k].name == 'tBodies-detail') {
      link[k].onclick = function () {
        return getDetail(this);
      };
    } else if (link[k].getAttribute('name') == 'rightShowArea') {
      link[k].onclick = function () {
        return showUserList(this);
      };
    } else if (link[k].getAttribute('name') == 'rightShowAreaTag') {
    } else if (link[k].name != 'msg' && link[k].target != '_blank') {
      link[k].onclick = sendToServer;
    }
  }

  pageInit();

  var temp = document.getElementById('cont-nav');
  nav = temp.getElementsByTagName('a');
  keepShow = temp.getElementsByTagName('span');

  for (var l in keepShow) {
    if (typeof(keepShow[l].parentNode) == 'object') {
      keepShow[l].onmouseover = keepShowSpan;
    }
  }

  for (var i in nav) {
    if (typeof(nav[i].parentNode) == 'object' && nav[i].parentNode.tagName == 'DIV') {
      nav[i].onclick     = switchTab;
      nav[i].onmouseover = showSubLink;
      nav[i].onmouseout  = hideSubLink;
    }
  }
}

/**
 * 切换标签页  选项卡
 */
function switchTab () {
  main.innerHTML = '';
  for (var i in nav) {
    if (typeof(nav[i].parentNode) == 'object' && nav[i].parentNode.tagName == 'DIV') {
      nav[i].className = 'nav';
    }
  }

  this.className += ' current';

  Ajax.call(this.href, '', switchTabResponse, 'GET', this.href.match('Erp') ? 'JSON' : 'TEXT');
  return false;
}

/**
 * 标签页数据 回调函数
 */
function switchTabResponse (res) {
  if (res.main) {
    document.getElementById('left').innerHTML = res.left;
    document.getElementById('main').innerHTML = res.main;
  } else {
    document.getElementById('left').innerHTML = res;
  }
  init();
}

/**
 * 显示导航子菜单
 */
function showSubLink () {
  for (var i in nav) {
    if (typeof(nav[i].parentNode) == 'object' && nav[i].parentNode.tagName == 'DIV')
      if (nav[i].nextSibling != undefined) {
        nav[i].nextSibling.nextSibling.className = 'hide';
      }
  }

  this.nextSibling.nextSibling.style.left = this.offsetLeft +'px';
  this.nextSibling.nextSibling.style.top  = this.offsetTop +26 +'px';
  this.nextSibling.nextSibling.className = 'show downMenu';
}

/**
 * 隐藏导航子菜单
 */
function hideSubLink ()
{
  keepShowTemp = this.nextSibling.nextSibling;
  delay = setTimeout(hideSpan, 3000);
}

/**
 * 发送Ajax请求 （全局可用）
 */
function sendToServer () {
  var url = this.href;
  // 确认订单
  if (/act=ordersyn_verify/.test(url)) {
    url = ordersynVerify(url);
    var msg = {message:'订单正在提交，请稍候。。。', req_msg:true, manual_hide:true, hide_btn:true};
    showMsg(msg);

    if (url == false) {
      return false;
    }
  } else if (/act=ordersyn_del/.test(url)) {      // 关闭订单 
    if (! confirm('该订单删除后，将不再同步，确认要删除？')) {
      return false;
    }

    // 订单关闭原因
    url += '&close_reason='+document.getElementById('close_reason').value;
  } else if (/act=do_returns/.test(url)) { // 订单退货
    if (! confirm('确认该订单要退回？')) {
      return false;
    } else {
      var theForm       = document.forms['do_returns'];
      var expressNumber = theForm.elements['express_number'].value;
      var returnReason  = theForm.elements['return_reason'].value;
      url += '&express_number='+expressNumber+'&return_reason='+returnReason;
    }
  } else if (/act=order_del/.test(url)) {
    url += '&close_reason='+document.getElementById('close_reason').value;
    removeRow('temp_1');
    removeRow('temp_2');
  } else if (/act=delete_goods/.test(url)) {
  } else {
    // 删除已经显示的详情DOM
    var detail = document.getElementById('detail')||document.getElementById('temp_2');
    if (detail) {
      var temp = detail.previousSibling.previousSibling.id;
      var tmp = temp.replace(/tr_2_/, '') || temp.replace(/tr_/, '');
      var txt = /\d+/.exec(temp);
      removeRow(detail.id);
      if (!isNaN(tmp)) {
        removeRow('temp_1');
        Ajax.call('order.php?act=unlock&order_id='+tmp, '', unlockResp, 'GET', 'TEXT');
        var pattern = new RegExp('id='+tmp);
        if (pattern.test(this)) {
          return false;
        }
      } else {
        tmp = tmp.replace(/tr_/, 'id=') || tmp.replace(/tr_/, 'id/');
        var pattern = new RegExp(tmp);
        if (pattern.test(this)) {
          return false;
        }
      }
    }
  }

  // 日期条件
  var pattern = new RegExp('act='+action);
  if (action && pattern.test(this.href)) {
    if (document.getElementById('start_time'))
      var start_time = document.getElementById('start_time').value;
    if (document.getElementById('end_time')) 
      var end_time   = document.getElementById('end_time').value;
    if (start_time && end_time) url += '&start_time='+start_time+'&end_time='+end_time;
    pattern = new RegExp(/act=(\w+)&?/);
    action  = pattern.exec(this.href);
    action  = action[1];
  }

  Ajax.call(url, '', sendToServerResponse, 'GET', 'JSON');
  return false;
}


/**
 * Ajax回调函数
 * @param   res   JSON Object  服务器端返回的结果集
 * res.response_action  返回Ajax发送的数据中的act
 * res.code             返回PHP脚本执行的结果 1：成功 0：失败
 * res.obj              返回将被操作的对象的id的前缀
 * res.id               将被操作的对象的数字id
 * res.main             返回操作后的数据信息 
 * res.page             返回操作后的数据信息 
 * res.req_msg          是否需要弹窗显示结果信息 是：true  否：false
 * res.left             页面左侧导航
 * res.info             暂时保留
 * 包含将调用的函数（res.response_action）
 * 和查询到的数据（res.info）
 */
function sendToServerResponse (res) {
  if (res.hasOwnProperty("a") && res.a == 'page_link') {
    document.getElementById('listDiv').innerHTML = res.main;
    document.getElementById('pageDiv').innerHTML = res.page;
    init();
    return false;
  }

  if (res.resp == 'logout') {
    self.location = 'privilege.php?act=login';
  }

  // 提交订单 从临时订单页面删除该订单
  var arr = ['ordersyn_verify', 'ordersyn_del', 'order_del'];
  if (in_array(arr, res.response_action) && res.code == 1) {
    removeRow('tr_1_'+res.id);
    removeRow('tr_2_'+res.id);
    removeRow('temp_1');
    removeRow('temp_2');
    setTimeout(function () {
      var objBody = document.body;
      var objMsg  = document.getElementById('msg');
      var objDiv = document.getElementById('clip');
      if (objDiv) {
        objBody.removeChild(objDiv);
      }
      objMsg.className = 'hide';
      objBody.style.overflow = 'auto';
      clearTimeout(timer);
    }, 2000);
  }

  // 订单锁定
  if (res.info == 'order_lock') {
    return order_lock(res);
  }

  // 显示订单详情
  if (res.response_action == 'order_detail') {
    if (res.code == 0) {
      showMsg(res);
      removeRow('tr_1_'+res.order_id);
      removeRow('tr_2_'+res.order_id);
      return false;
    }

    // showRow(res.info);  // 此处为两种方案 1、预先在模板中加入隐藏的行元素，填充内容后显示
    insertRow(res.info);   // 方案2、创建相应的行元素，再从服务器端拉去数据添加进表格中

    var isExistDetail = document.getElementById('temp_2');
    if (isExistDetail == undefined)
    {
      return false;
    }
  }
  // 显示顾客详细信
  if (res.response_action == 'detail') {
    if (res.tp) {
      document.getElementById('user_detail').innerHTML = res.info;
    }else{
      insertRowInSingleTable(res);
    }
  }

  // 显示进货单详细信息
  if (res.response_action == 'detail_stock') {
    // showRow(res.info);  // 此处为两种方案 1、在模板中加入隐藏的行元素，填充内容后显示
    insertStockRow(res.info);   // 方案2、创建相应的行元素，添加进表格中
  }

  if (res.response_action == 'check_stock_batch') {
    // showRow(res.info);  // 此处为两种方案 1、在模板中加入隐藏的行元素，填充内容后显示
    insertStockBatchRow(res.info);   // 方案2、创建相应的行元素，添加进表格中
  }

  if (res.response_action == 'list_service') {
    document.getElementById('listDiv').innerHTML = res.main;
  }

  if (res.response_action == 'search_service') {
    document.getElementById('resource').innerHTML = res.main;
  }

  if (res.page) {
    var dataArea = document.getElementById('listDiv')  || document.getElementById('dataList');
    var pageArea = document.getElementById('pageList') || document.getElementById('page');

    dataArea.innerHTML = res.main;
    pageArea.innerHTML = res.page;

    if (res.tag) {
      var currTag = document.getElementById('tag_'+res.tag);
      var tagList = currTag.parentNode.children;
      for (var i=0; i < tagList.length; i++) {
        tagList[i].className = 'last';
      };
      currTag.className = 'current-tab';
    };
  } else if (res.response_action == null && typeof(res.main) != 'undefined') {
    main.innerHTML = res.main;
  } else if (res.response_action == 'user_stats') {
    main.innerHTML = res.main;
  }

  if (typeof(res.left) != 'undefined') {
    var left = document.getElementById('left');
    left.innerHTML = res.left;
  }

  if (res.switch_tag) {
    var pattern = new RegExp(res.href);
    var tag = document.getElementById('sub_tag');
    for (var i in tag.children) {
      if (typeof(tag.children[i]) == 'object') {
        if(tag.children[i].id == 'tag_'+res.id) {
          tag.children[i].className = 'current-tab';
        } else {
          tag.children[i].className = 'last';
        }
      }
    }
  }

  if (res.req_msg) {
    showMsg(res);
  }

  if (res.code == 4) {
    var trObj = document.getElementById('tr_'+res.id);
    trObj.parentNode.deleteRow(trObj.rowIndex);
  }

  if (res.response_action == 'delete_goods') {
    removeRow('rec_'+res.id);
  }

  init();
}

/**
 * 解锁回调函数：无实际意义
 */
function unlockResp (res) { }

/**
 * 提交订单
 */
function ordersynVerify (url)
{
  var obj = document.getElementById('temp_1');
  var select = obj.getElementsByTagName('select');
  var secrecy = document.getElementById('secrecy');

  var data = new Array ();
  var msg = new Array();
  for (var i in select) {
    if (select[i].value == undefined || select[i].disabled) {
      continue;
    }

    if (/\d+/.test(select[i].value)) {
      data.push(select[i].name+'='+select[i].value);
    } else {
      switch (select[i].name) {
        case 'order_admin_id' : msg.message = '请选择订单归属！'; msg.timeout = 2000;
                                break;
        case 'user_admin_id' : msg.message = '请选择顾客归属！'; msg.timeout = 2000;
                               break;
        case 'eff_id' : msg.message = '请选择顾客分类！'; msg.timeout = 2000;
                        break;
        case 'order_type' : msg.message = '请选择订单类型！'; msg.timeout = 2000;
                            break;
      }

      showMsg(msg);
      return false;
    }
  }

  url += '&'+data.join('&');

  if (secrecy.checked) url += '&secrecy='+secrecy.value;

  return url;
}

/**
 * 将订单的详细信息填充至隐藏单元格中，并显示单元格
 */
function showRow (data)
{
  var infoRow = document.getElementById('tr_2_'+data.id);
  infoRow.children[0].cellSpan = 13;
  infoRow.children[0].innerHTML = data.info;
  infoRow.className = 'show';
}

/**
 * 在表格中插入行
 * @param   data  obj 数据集
 */
function insertRow (data)
{
  // 判断是否要折叠详细信息临时行
  var delTr = document.getElementById('temp_2'); // 获取临时行对象
  if (delTr != null)
  {
    // 删除详细信息临时行
    var trId = delTr.previousSibling.previousSibling.id;
    delTr.parentNode.deleteRow(delTr.rowIndex);

    var delTr = document.getElementById('temp_1');
    delTr.parentNode.deleteRow(delTr.rowIndex);

  }

  if (trId != 'tr_2_'+data.id)
  {
    // 右侧表格
    var rowObj = document.getElementById('tr_2_'+data.id); // 获取当前行的对象
    var tbObj = rowObj.parentNode;                         // 获取表格对象
    var insRows = tbObj.insertRow(rowObj.rowIndex+1);      // 在当前行之后插入一行

    insRows.style.background = '#fff'; // 设置插入行的背景色
    insRows.id = 'temp_2';             // 给插入的行添加临时id

    var insCell = insRows.insertCell(0);  // 给新插入行添加单元格
    insCell.vAlign = 'top'; // 设置单元格内容顶部显示
    insCell.colSpan = 13;                 // 设置新添加的单元格所占列数
    insCell.innerHTML = data.info;        // 向新单元格中添加数据

    // 左侧表格
    var userRow = document.getElementById('tr_1_'+data.id); // 获取当前行的对象
    var userTb  = userRow.parentNode;                       // 获取当前行所在的表格对象
    var insUserRow = userTb.insertRow(userRow.rowIndex+1);  // 插入新的行

    insUserRow.style.background = '#fff'; // 设置插入行的背景色
    insUserRow.id = 'temp_1';             // 给插入行添加临时id

    var insUserCell = insUserRow.insertCell(0);     // 在新插入行中添加单元格

    // 保持单元格高度一致
    if (insUserCell.offsetHeight > insCell.offsetHeight)
    {
      insCell.style.height = insUserCell.offsetHeight+'px';
    }
    else 
    {
      // 设置新插入单元格的行高与右侧表格中相应单元格的行号一致
      insUserCell.style.height = insCell.offsetHeight+'px';
    }

    insUserCell.className = 'td_menu';
    insUserCell.colSpan = 3;                        // 设置单元格所占列数
    insUserCell.innerHTML = data.menu;//'&nbsp;';   // 在左侧填充操作菜单项
  }
}

/**
 * 显示下拉菜单
 */
function keepShowSpan ()
{
  clearTimeout(delay);
  keepShowTemp.className = 'show downMenu';
  keepShowTemp.onmouseout = hideSpan;
}

/**
 * 隐藏下拉菜单
 */
function hideSpan ()
{
  keepShowTemp.className = 'hide';
  keepShowTemp.style = '';
}

/**
 * 固定表格首行
 */
function syncscroll(obj)
{
  scroll1.children[0].style.position = "relative";
  scroll2.children[0].style.position = "relative";
  scroll1.children[0].style.left = "-"+obj.scrollLeft +'px';
  scroll2.children[0].style.top =  "-"+obj.scrollTop +'px';
}

/**
 * 切换选项卡
 */
function switchSubTab (obj)
{
  var ul = obj.parentNode;
  for (var i in ul.children)
  {
    if (i != 'length')
    {
      ul.children[i].className = '';
      if (ul.children[i].type != undefined)
      {
        if (document.getElementById(ul.children[i].type))
          document.getElementById(ul.children[i].type).className = 'hide';
      }
    }
  }

  obj.className = 'o_select';
  document.getElementById(obj.type).className = 'show';

  var temp_1 = document.getElementById('temp_1');
  var temp_2 = document.getElementById('temp_2');

  // 保持单元格高度一致
  if (temp_1)
  {
    if (temp_1.children[0].offsetHeight > temp_2.offsetHeight)
    {
      temp_2.style.height = temp_1.children[0].offsetHeight+'px';
    }
    else 
    {
      temp_1.children[0].style.height = temp_2.offsetHeight+'px';
    }
  }

  // 被查看详情的行自动移动到列表首行
  //var scrollObj = temp_2.parentNode.parentNode;
  //scrollObj.scrollTop += rowObj.offsetTop - scrollObj.scrollTop;
}

/**
 * 发送修改信息
 */
function sendEditInfo(obj) {
  var url = obj.value || obj.getAttribute('value');
  var pattern = new RegExp(/info=([a-z_]+)/i);
  var keyword = pattern.exec(url);
  obj.parentNode.id = keyword[1];

  Ajax.call(url, '', showOption, 'GET', 'JSON');
}

/**
 * 显示可选项
 */
function showOption (res) {
  if(res.code == 2) {
    showMsg(res);
    return false;
  }

  var obj = document.getElementById(res.info);
  if (res.type == 'select') {
    var tmp = obj.innerHTML
      tmp = tmp.replace(/<button.*<\/button>/, '');
    obj.innerHTML = tmp+'<br />'+res.main;
  } else {
    obj.innerHTML = res.main;
  }
}

/**
 * 编辑订单信息 
 */
function saveOrderInfo (obj)
{
  if (obj.name == 'district' && obj.value == 0)
  {
    return false;
  }

  var id = document.getElementById('order_id').value;
  var request;

  switch (obj.name)
  {
    case 'shipping' : request = obj.name+'&shipping_id='+obj.value;
                      break;

    case 'district' : 
                      var province = document.getElementById('selProvinces').value;
                      var city     = document.getElementById('selCities').value;
                      var district = obj.value;

                      request = obj.name+'&province='+province+'&city='+city+'&district='+district;
                      break;

    default :
                      request = obj.name+'&val='+obj.value;
                      break;
  }

  Ajax.call('order.php?act=save&id='+id+'&info='+request+'&type='+obj.tagName, '', showOrderInfo, 'GET', 'JSON');
}

/**
 * 显示编辑后的结果信息
 */
function showOrderInfo (res)
{
  var obj = document.getElementById(res.info);
  obj.innerHTML = res.main+'<button value="order.php?act=edit&info='+res.info+'&id='+res.id+'&type='+res.type+'" onclick="sendEditInfo(this)"><img src="images/edit.gif" alt="修改" title="修改"></button>';

  if (res.info == 'consignee')
  {
    var consigneeObj = document.getElementById('tr_2_'+res.order_id);
    consigneeObj.children[1].innerHTML = res.main;
  }

  if (res.req_msg)
  {
    showMsg(res);
  }
}

/**
 * 订单锁定与解锁
 */
function order_lock (res)
{
  document.getElementById('lock_'+res.id).innerText = res.main;
  return false;
}

/**
 * 搜索商品
 */
function searchGoods(obj)
{
  var keyword = obj.value;
  var order_id = '';
  if (document.getElementById('order_id')) {
    order_id = '&order_id='+document.getElementById('order_id').value;
  }

  if (/[\u4E00-\u9FA5]{2}/.test(keyword))
  {
    Ajax.call('order.php?act=search_goods&keyword='+keyword+order_id, '', searchGoodsResponse, 'GET', 'JSON')
  }
}

/**
 * 搜索商品回调函数
 * @param   res.info   告知回调函数应该调用的其它函数
 * @param   res.main   服务器返回的结果数据
 * @param   res.obj    对某个对象进行操作
 * @param   res.error  错误及提示信息 只有出现错误时才出现
 */
function searchGoodsResponse (res)
{
  if (res.info == 'searchGoods')
  {
    showGoodsOption(res);
  }
}

/**
 * 在select中显示商品选项
 */
function showGoodsOption (goodsOpt)
{
  var obj = document.getElementById(goodsOpt.obj);
  obj.length = 0;
  var goods = goodsOpt.main;
  var opt = document.createElement('option');
  opt.value = 0;
  opt.text = '请选择商品或套餐';
  obj.appendChild(opt);
  for (var i in goods)
  {
    if (typeof(goods[i]) == 'function') continue;
    var opt = document.createElement('option');
    opt.value = goods[i].goods_id;
    opt.text = goods[i].goods_name;
    obj.appendChild(opt);
  }
}

/**
 * 添加商品
 */
function addGoods ()
{
  var theForm  = document.forms['theForm'];

  var goods_id = theForm.elements['goods_id'].value;
  var number   = theForm.elements['number'].value;
  var price    = theForm.elements['price'].value;
  var id       = document.getElementById('order_id').value;

  var isGift = '';
  for (var i = theForm.elements['is_gift'].length-1; i >= 0; i--) {
    if (theForm.elements['is_gift'][i].checked) {
      isGift = theForm.elements['is_gift'][i].value;
      theForm.elements['is_gift'][i].checked = false;
      break;
    }
  }

  var data = 'goods_id='+goods_id+'&number='+number+'&price='+price+'&order_id='+id+'&is_gift='+isGift;
  Ajax.call('order.php?act=add_goods&'+data, '', addGoodsResponse, 'GET', 'JSON');

  return false;
}

/**
 * 在订单详情页显示结果
 */
function addGoodsResponse (res)
{
  // 更新订单 商品列表
  var obj = document.getElementById('goods_list');
  obj.innerHTML = res.main;

  // 更新商品总额
  document.getElementById('goods_amount').innerHTML = res.goods_amount;

  // 更新配送费用
  document.getElementById('shipping_fee').innerHTML = res.shipping_fee;

  // 更新订单总额
  document.getElementById('final_amount').innerHTML = res.final_amount;

  // 更新订单总额
  document.getElementById('final_amount_1').innerHTML = res.final_amount;

  init(); // 重新初始化页面脚本
}


/**
 * 删除单元行
 */
function removeRow (id)
{
  var rowObj = document.getElementById(id);
  if (typeof(rowObj) == 'object') {
    rowObj.parentNode.deleteRow(rowObj.rowIndex);
  }
}

/**
 * 删除单元格
 */
function removeCell ()
{
}

/**
 * 显示运单号输入框
 */
function replaceToInput (obj, order_id)
{
  var trackingSn = !/[\u4e00-\u9fa5]/.test(obj.innerText) ? obj.innerText : '';
  obj.innerHTML = '';
  obj.innerHTML = '<input type="text" id="trackingSn" name="trackingSn" value="'+trackingSn+'" size="10" onblur="sendTrackingSn(this,' + order_id + ')">';

  obj.style.padding = 0;
  obj.children[0].style.width = '115px';
  obj.children[0].style.marginLeft = 0;

  document.getElementById('trackingSn').focus();
}

/**
 * 提交运单号到服务器
 */
function sendTrackingSn(obj, order_id)
{
  if (obj.value.match(/\d+/)) {
    var trackingSn = obj.value.indexOf('-') > 0 ? obj.value.slice(0,obj.value.indexOf('-')) : obj.value;
    Ajax.call('order.php?act=edit_tracking_sn&order_id='+order_id+'&tracking_sn='+trackingSn,'',displayTrackingSn,'GET','JSON');
  } else {
    var res = new Array ();
    res.errMsg = 3;
    res.req_msg = true;
    res.message = '请输入正确的运单号！';
    displayTrackingSn(res);
  }
}

/**
 * 返回运单号提交结果 回调函数
 */
function displayTrackingSn(res)
{
  showMsg(res);
  var obj = document.getElementById("trackingSn").parentNode;
  if (res.errMsg == 1) {
    obj.innerHTML = res.shipping_name;
  } else if (res.errMsg == 2) {
    var tagA       = document.createElement('a');
    tagA.href      = 'http://www.kuaidi100.com/chaxun?com='+res.shipping_code+'&nu='+res.tracking_sn;
    tagA.title     = res.shipping_name;
    tagA.target    = '_blank';
    tagA.innerHTML = res.tracking_sn;

    obj.innerHTML = '';
    obj.appendChild(tagA);
  } else if (res.errMsg == 3) {
    return false;
  }

  obj.removeAttribute('id');
}

/**
 * 在表格中插入进货单详情行
 * @param   data  obj 数据集
 */
function insertStockRow (data)
{
  // 判断是否要折叠详细信息临时行
  var delTr = document.getElementById('temp_2'); // 获取临时行对象
  if (delTr != null) {
    // 删除详细信息临时行
    var trId = delTr.previousSibling.previousSibling.id;
    delTr.parentNode.deleteRow(delTr.rowIndex);

    //var delTr = document.getElementById('temp_1');
    //delTr.parentNode.deleteRow(delTr.rowIndex);
  }

  if (trId != 'tr_2_'+data.id)
  {
    // 右侧表格
    var rowObj = document.getElementById('tr_2_'+data.id); // 获取当前行的对象
    var tbObj = rowObj.parentNode;                         // 获取表格对象
    var insRows = tbObj.insertRow(rowObj.rowIndex+1);      // 在当前行之后插入一行

    insRows.style.background = '#fff'; // 设置插入行的背景色
    insRows.id = 'temp_2';             // 给插入的行添加临时id

    var insCell = insRows.insertCell(0);  // 给新插入行添加单元格
    insCell.vAlign = 'top'; // 设置单元格内容顶部显示
    insCell.colSpan = 5;                 // 设置新添加的单元格所占列数
    insCell.innerHTML = data.info;        // 向新单元格中添加数据
    /*
    // 左侧表格
    var userRow = document.getElementById('tr_1_'+data.id); // 获取当前行的对象
    var userTb  = userRow.parentNode;                       // 获取当前行所在的表格对象
    var insUserRow = userTb.insertRow(userRow.rowIndex+1);  // 插入新的行

    insUserRow.style.background = '#fff'; // 设置插入行的背景色
    insUserRow.id = 'temp_1';             // 给插入行添加临时id

    var insUserCell = insUserRow.insertCell(0);     // 在新插入行中添加单元格

    // 保持单元格高度一致
    if (insUserCell.offsetHeight > insCell.offsetHeight)
    {
    insCell.style.height = insUserCell.offsetHeight+'px';
    }
    else 
    {
    // 设置新插入单元格的行高与右侧表格中相应单元格的行号一致
    insUserCell.style.height = insCell.offsetHeight+'px';
    }

    insUserCell.className = 'td_menu';
    insUserCell.colSpan = 3;                        // 设置单元格所占列数
    insUserCell.innerHTML = '进货单详情';//'&nbsp;';   // 在左侧填充操作菜单项
    */
  }
}

/**
 * 变更输入区域
 */
function changeKeywordsArae (obj)
{
  switch (obj.value) {
    case 'region' :
      Ajax.call('order.php?act=get_regions', '', changeKeywordsAraeResponse, 'GET', 'TEXT');
      break;
    case 'from_where' :
      Ajax.call('query.php?act=from_where', '', changeKeywordsAraeResponse, 'GET', 'TEXT');
      break;
    case 'admin_name' :
      Ajax.call('query.php?act=admin_name', '', changeKeywordsAraeResponse, 'GET', 'TEXT');
      break;
    case 'sex' :
      document.getElementById('keywordsArea').innerHTML = '<select id="keywords"><option value=0>未知</option><option value=1>男</option><option value=2>女</option></select>';
      break;
    case 'shipping_feed' :
      Ajax.call('query.php?act=shipping_feed', '', changeKeywordsAraeResponse, 'GET', 'TEXT');
      break;
    case 'platform' :
      Ajax.call('query.php?act=platform', '', changeKeywordsAraeResponse, 'GET', 'TEXT');
      break;
    default : 
      document.getElementById('keywordsArea').innerHTML = '<input type="text" id="keywords">';
  }
}

/**
 * 变更关键词区域 回调函数
 */
function changeKeywordsAraeResponse (res) {
  document.getElementById('keywordsArea').innerHTML = res;
}

/**
 * 查询订单
 */
function searchOrder (obj)
{
  var keyfields  = document.getElementById('keyfields').value;  // 要查询的字段名称
  var start_time = document.getElementById('start_time').value; // 下单时间段开始时间
  var end_time   = document.getElementById('end_time').value;   // 下单时间段结束时间
  var time_select = document.getElementById('time_select')?document.getElementById('time_select').value:0;
  var data = '&a=search&keyfields='+keyfields;

  if(document.getElementById('platform')){
    var platform   = document.getElementById('platform').value;   // 购买平台
    data += '&platform='+platform;
  }

  if (start_time && end_time) {
    data += '&start_time='+start_time+'&end_time='+end_time+'&time_select='+time_select;
  }


  switch (keyfields) {
    case 'region':
      var province = document.getElementById('selProvinces').value;
      var city     = document.getElementById('selCities').value;
      var district = document.getElementById('selDistricts').value;
      data += '&province='+province+'&city='+city+'&district='+district;
      break;
    case 'add_time':
      var start_time = document.getElementById('start_time').value;
      var end_time   = document.getElementById('end_time').value;
      data += '&start_time='+start_time+'&end_time='+end_time;
      break;
    case 'from_where':
      data += '&from_where='+document.getElementById('from_where').value;
      break;
    case 'admin_name':
      data += '&admin_id='+document.getElementById('admin_id').value;
      data += '&user_name='+document.getElementById('keywords').value;
      break;
    case 'shipping_feed':
      data += '&shipping_feed_id='+document.getElementById('shipping_feed_id').value;
      break;
    default :
      var keywords = document.getElementById('keywords').value;  // 要查询的关键词
      data += '&keyfields='+keyfields+'&keywords='+keywords;
      break;
  }

  var pattern = new RegExp(/act=(\w+)&?/);
  action  = pattern.exec(obj.value);
  action  = action[1];
  Ajax.call(obj.value+data, '', searchOrderResponse, 'GET', 'JSON');
}

/**
 * 查询订单 回调函数
 */
function searchOrderResponse (res)
{
  if (res.a) {
    document.getElementById('listDiv').innerHTML = res.main;
    document.getElementById('pageDiv').innerHTML = res.page;
  } else {
    main.innerHTML = res.main;
    var tag = document.getElementById('sub_tag');
    if (res.switch_tag && tag)
    {
      var pattern = new RegExp(res.href);
      for (var i in tag.children)
      {
        if (typeof(tag.children[i]) == 'object')
        {
          if(tag.children[i].id == 'tag_'+res.id)
            tag.children[i].className = 'current-tab';
          else
            tag.children[i].className = 'last';
        }
      }
    }
  }

  init();
}

/**
 * 在表格中插入新行
 */
function insertRowInSingleTable (res)
{
  var parentObj = document.getElementById('tr_'+res.id);                // 获取当前行的DOM对象
  var rowObj    = parentObj.parentNode.insertRow(parentObj.rowIndex+1); // 在表格中插入行

  rowObj.id = 'detail'; // 设置新插入行的ID

  var cellObj     = rowObj.insertCell(0);      // 插入单元格
  cellObj.colSpan = parentObj.children.length; // 设置单元格的colSpan属性

  cellObj.innerHTML = res.info;  // 在单元格中填充内容

  // 被查看详情的行自动移动到列表首行
  //var scrollObj = parentObj.parentNode.parentNode.parentNode;
  //alert(scrollObj.offsetTop);
  //scrollObj.scrollTop += scrollObj.offsetTop - scrollObj.scrollTop;
}

/**
 * 发送要更新的数据
 */
function saveInfo(obj) {
  var url = document.getElementById('URI').value;  // 获取发送请求的服务器脚本名称
  var id = document.getElementById('ID').value;

  var data = 'act=save'+'&id='+id+'&info=';
  data += obj.name == 'lunar' ? 'birthday' : obj.name;

  if (obj.type == 'text') {
    data += '&type='+obj.type+'&value='+obj.value;
  }

  if (obj.name == 'lunar') {
    var lunar = obj.value;
    if (Boolean(parseInt(lunar))) {
      var birthday = document.getElementById('birthdate').value;
      data += '&type=text&value='+birthday+'&lunar='+lunar;
    } else {
      var msg = new Array ();
      msg.timeout = 2000;
      msg.req_msg = true;
      msg.message = '请选择出生日期的类型！';
      showMsg(msg);
      return false;
    }
  }

  if (obj.type == 'radio') {
    data += '&type='+obj.type+'&value='+obj.value;
  }

  if (obj.tagName == 'SELECT' && obj.id == 'selDistricts') {
    //alert(document.getElementById('selDistricts').value);
    if (document.getElementById('selDistricts').value == 0) {
      return false;
    }

    data += '&type='+obj.tagName+'&province='+document.getElementById('selProvinces').value;
    data += '&city='+document.getElementById('selCities').value;
    data += '&district='+document.getElementById('selDistricts').value;
  }

  if (obj.tagName == 'SELECT' && obj.id != 'selDistricts' && obj.id != 'lunar') {
    data += '&type='+obj.tagName+'&value='+obj.value;
  }

  if (obj.type == 'button') {
    var objList = obj.parentNode.getElementsByTagName('input');
    var marketing = new Array();
    for (var i = objList.length - 1; i >= 0; i--){
      if (objList[i].type == 'checkbox' && objList[i].checked) {
        marketing.push(objList[i].value);
      }
    };
    data += '&value='+JSON.stringify(marketing);
  }

  Ajax.call(url, data, showInfo, 'POST', 'JSON');
}

/**
 * 显示编辑后的结果信息
 */
function showInfo(res) {
  var obj = document.getElementById(res.info);
  var url = document.getElementById('URI').value;
  if (obj) {
    var act = res.act ? res.act : 'edit';
    obj.innerHTML = res.main+'<button value="'+url+'?act='+act+'&info='+res.info+'&id='+res.id+'&type='+res.type+'" onclick="sendEditInfo(this)"><img src="images/edit.gif" alt="修改" title="修改"></button>';
  }

  if (res.info == 'consignee') {
    var consigneeObj = document.getElementById('tr_2_'+res.user_id);
    consigneeObj.children[1].innerHTML = res.main;
  }

  /*
     if (res.info == 'goods_amount' || res.info == 'shipping_fee')
     {
  // 更新订单总额
  document.getElementById('final_amount').innerHTML = res.main;

  // 更新订单总额
  document.getElementById('final_amount_1').innerHTML = res.main;
  }
  */

  if (res.req_msg) {
    showMsg(res);
  }
  obj.removeAttribute('id');
}

/**
 * 添加新商品
 */
var goodsName = null;
function addNewGoods ()
{
  // 表单对象
  var theForm = document.forms['theForm'];
  var pattern = /(【库存：)(\d+)(】)$/;

  // 商品信息
  var number   = theForm.elements['number'].value;
  var goodsObj = theForm.elements['goods_id'];
  var id       = goodsObj.value;
  var price    = isGift ? 0 : theForm.elements['price'].value;

  var isGift     = null;
  var promotions = null;

  for (var i = theForm.elements['is_gift'].length-1; i >= 0; i--) {
    if (theForm.elements['is_gift'][i].checked) {
      isGift = theForm.elements['is_gift'][i].value;
      promotions = theForm.elements['is_gift'][i].parentNode.innerText;

      theForm.elements['is_gift'][i].checked = false;
      break;
    }
  }

  if (0 == id) {
    showMsg({req_msg:true,timeout:2000,message:'请选择购买的商品！'});
    return false;
  }

  for (var i = 0; i < goodsObj.length; i++) {
    if (goodsObj.options[i].selected) {
      var name = goodsObj.options[i].text;
      if (pattern.test(name)) {
        var storage = parseInt(name.match(pattern)[2]) - number;
        if (storage < 0) {
          showMsg({req_msg:true,timeout:2000,message:'库存不足，无法下单。请考虑其它同类商品！'});
          return false;
        } else {
          goodsObj.options[i].text = name.replace(pattern, '$1'+storage+'$3');
        }
        name = name.replace(pattern, '');
      }
    }
  }

  var goodsList = document.getElementById('goods_list'); 
  var rowObj = goodsList.insertRow(goodsList.rows.length);

  // 商品ID
  var goodsId = rowObj.insertCell(0);
  goodsId.innerHTML = '<input type="checkbox" name="list_id[]" value="'+id+'" id="goods_id_'+id+'"/><strong onclick="removeGoods(this)">删除</strong>';

  // 商品名称
  goodsName = rowObj.insertCell(1);
  rowObj.style.height = '20px';
  if (isNaN(id)) {
    Ajax.call('order.php?act=packing_goods&id='+id, '', packingGoodsListResp, 'GET', 'TEXT');
  } else {
    goodsName.innerHTML = name+'<input type="hidden" name="list_name[]" value="'+name+'"/>';
  }

  // 商品价格
  var goodsPrice = rowObj.insertCell(2);
  goodsPrice.innerHTML = price+'<input type="hidden" name="list_price[]" value="'+price+'"/>';

  // 商品数量
  var goodsNumber = rowObj.insertCell(3);
  goodsNumber.innerHTML = number+'<input type="hidden" name="list_number[]" value="'+number+'"/>';

  // 赠品
  var gift = rowObj.insertCell(4);
  /*
     var promotions = '';
     if (1 == isGift) {
     promotions = '赠品';
     } else if (2 == isGift) {
     promotions = '活动';
     } else if (3 == isGift) {
     promotions = '补发';
     }
     */

  gift.innerHTML = promotions+'<input type="hidden" name="list_gift[]" value="'+isGift+'"/>';

  var tmp    = price.split('.').join('')*number;
  var power  = 0;
  if (price.split('.')[1]) {
    power  = price.split('.')[1].length;
  }
  var amount = tmp/Math.pow(10,power);

  // 单个商品总价
  var singleAmount = rowObj.insertCell(5);
  singleAmount.innerHTML = isGift != 1 && isGift != 3 ? amount : promotions;

  // 计算订单总金额
  calcOrderAmount();
}

function packingGoodsListResp (res) {
  goodsName.innerHTML = res;
  goodsName = null;
}

/**
 * 保存新订单
 */
function addNewOrder()
{
  // 顾客ID
  var id = document.getElementById('ID').value;

  var orderInfo = document.forms['order_info'];
  var goodsList = document.forms['goodsList'];
  var tableObj  = document.getElementById('goods_list');

  var consignee   = orderInfo.elements['consignee'].value;
  var tel         = orderInfo.elements['tel'].value;
  var mobile      = orderInfo.elements['mobile'].value;
  var province    = orderInfo.elements['province'].value;
  var city        = orderInfo.elements['city'].value;
  var district    = orderInfo.elements['district'].value;
  var address     = orderInfo.elements['address'].value;
  var pay_id      = orderInfo.elements['pay_id'].value;
  var shipping_id = orderInfo.elements['shipping_id'].value;
  var remarks     = orderInfo.elements['remarks'].value;
  var admin_id    = orderInfo.elements['admin_id'].value;
  //var platform  = orderInfo.elements['platform'].value;
  var team        = orderInfo.elements['team'].value;
  var order_type  = orderInfo.elements['order_type'].value;

  var platform_order_sn = orderInfo.elements['platform_order_sn'].value; // 订单编号

  var res = new Array ();
  res['timeout'] = 2000;

  if (!consignee) {
    res['message'] = '请正确填写收货人姓名！';
    showMsg(res);
    return false;
  }

  if (!mobile && !tel) {
    res['message'] = '请输入收货人联系方式（非常重要）！';
    showMsg(res);
    return false;
  }

  if (province == 0 || city == 0 || !address) {
    res['message'] = '请提供详细的收货地址，以便订单可以准确配送！';
    showMsg(res);
    return false;
  }

  if (pay_id == 0) {
    res['message'] = '请选择订单所使用的支付方式！';
    showMsg(res);
    return false;
  }

  if (shipping_id == 0) {
    res['message'] = '请选择最优的配送方式！';
    showMsg(res);
    return false;
  }

  /*
     if (platform == 0)
     {
     res['message'] = '请选择产生该订单的销售平台！';
     showMsg(res);
     return false;
     }
     */

  if (team == 0) {
    res['message'] = '请选择产生该订单的购买平台！';
    showMsg(res);
    return false;
  }

  if (team < 33 && team != 1 && team != 29 && team != 31 && team != 9 && team != 27 && team != 28 && team != 2 && !platform_order_sn) {
    res['message'] = '非中老年、会员部订单须提供购买平台的订单编号';
    showMsg(res);
    return false;
  }

  if (!order_type) {
    if (confirm('您没有选择订单类型，系统将按静默订单进行处理？')) {
      order_type = 3;
    }
  }

  if (tableObj.rows.length <= 2) {
    res['message'] = '请先添加商品，再提交订单！';
    showMsg(res);
    return false;
  }

  if (admin_id == 0) {
    if (!confirm('该订单未选择归属客服，将作为静默订单处理！是否继续？')) {
      return false;
    }
  }

  if (goods_amount == 0) {
    if (!confirm('确认该订单的商品总额为0元？')) {
      return false;
    }
  }

  var data = {
    "user_id":id,
    "platform_order_sn":platform_order_sn,
    "tel":tel,
    "mobile":mobile,
    "province":province,
    "city":city,
    "district":district,
    "pay_id":pay_id,
    "shipping_id":shipping_id,
    "shipping_fee":shipping_fee,
    "consignee":consignee,
    "address":address,
    "admin_id":admin_id,
    //"platform":platform,
    "team":team,
    "order_type":order_type,
    "goods_amount":goods_amount,
    "remarks":remarks
  };

  for (var i = orderInfo.elements['order_source'].length -1; i > 0; i--) {
    if (orderInfo.elements['order_source'][i].checked) {
      data['platform_type'] = orderInfo.elements['order_source'][i].value;
    }
  }

  var goods = {};
  for (var i = 2; i < tableObj.rows.length; i++) {
    if (tableObj.rows.length > 3) {
      goods[i-2] = {
        "goods_sn"     : goodsList.elements['list_id[]'][i-2].value,
        "goods_price"  : goodsList.elements['list_price[]'][i-2].value,
        "goods_number" : goodsList.elements['list_number[]'][i-2].value,
        "is_gift"      : goodsList.elements['list_gift[]'][i-2].value,
      };
    } else {
      goods[i-2] = {
        "goods_sn"     : goodsList.elements['list_id[]'].value,
        "goods_price"  : goodsList.elements['list_price[]'].value,
        "goods_number" : goodsList.elements['list_number[]'].value,
        "is_gift"      : goodsList.elements['list_gift[]'].value,
      };
    }
  }

  data['shipping_fee'] = goodsList.elements['shipping_fee'].value;
  data['goods_amount'] = goodsList.elements['goods_amount'].value;
  data['goods_list']   = goods;

  Ajax.call('order.php?act=add_new_order', data, addNewOrderResponse, 'POST', 'JSON');
}

/**
 * 添加新订单回调函数
 */
function addNewOrderResponse (res)
{
  // 提交订单后，删除顾客详情行
  if(res.response_action == 'add_new_order' && res.code == 1)
  {
    removeRow('detail');
  }

  showMsg(res);
}

/**
 * 删除一个商品
 */
function removeGoods (obj)
{
  var rowObj = obj.parentNode.parentNode;       // 行DOM

  var domGoodsList = document.getElementById('goods_id');
  for (var i = domGoodsList.length -1; i >= 0; i--) {
    if (/【库存：\d+】/.test(domGoodsList.options[i].text) && obj.previousSibling.value === domGoodsList.options[i].value && rowObj.cells[3]) {
      var storage = parseInt(domGoodsList.options[i].text.match(/【库存：(\d+)】/)[1])+parseInt(rowObj.cells[3].innerText);
      domGoodsList.options[i].text = domGoodsList.options[i].text.replace(/【库存：\d+】/, '【库存：'+storage+'】');
    }
  }

  rowObj.parentNode.deleteRow(rowObj.rowIndex); // 删除行

  calcOrderAmount();  // 重新计算金额
}

/**
 * 计算订单费用
 */
function calcOrderAmount ()
{
  var goodsAmount = 0; // 商品总金额
  var shippingFee = parseFloat(document.getElementById('shipping_fee').value); // 运费
  var isPackage = 0;

  var tableObj = document.getElementById('goods_list');
  for (var i = 2; i < tableObj.rows.length; i++)
  {
    if (tableObj.rows[i].cells[5].innerText != '赠品')
    {
      goodsAmount += parseFloat(tableObj.rows[i].cells[5].innerText);
      if (parseFloat(tableObj.rows[i].cells[5].innerText) == 0.1)
      {
        isPackage = 1;
      }
    }
    else 
    {
      goodsAmount += 0;
    }
  }

  var newGoodsAmount = document.getElementById('goods_amount').value;
  if (newGoodsAmount != 0 && newGoodsAmount != goodsAmount)
  {
    if (confirm('确定该订单的商品金额为【'+newGoodsAmount+'】？')){
      document.getElementById('goods_amount').value = newGoodsAmount;
      goodsAmount = newGoodsAmount;
    }
    else{
      document.getElementById('goods_amount').value = goodsAmount;
    }
  }
  else{
    document.getElementById('goods_amount').value = goodsAmount;
  }

  document.getElementById('order_amount').innerHTML = parseFloat(goodsAmount)+parseFloat(shippingFee) + '元';
}

/**
 * 修改疾病与性格
 */
function addOne (obj)
{
  var data = '&info='+obj.name+'&value='+obj.value;
  data += '&id='+document.getElementById('ID').value;

  Ajax.call('users.php?act=save', data, addOneResponse, 'POST',  'JSON');
}

/**
 * 修改疾病与性格  回调函数
 */
function addOneResponse (res) {}

/**
 * 审核订单
 */
function review (id)
{
  Ajax.call('order.php?act=review&id='+id, '', reviewResponse, 'GET', 'JSON');
}

/**
 * 审核订单 回调函数
 */
function reviewResponse (res)
{
  if (res.code)
  {
    var obj = document.getElementById('review_'+res.id)
      if (res.main == 1)
        obj.className = 'fav_start-y';
      else if (res.main == 0)
        obj.className = 'fav_start-x';
  }

  if (res.req_msg) showMsg(res);
}

/**
 * 设置分页大小及页数
 */
function sendPageValue(obj)
{
  var pageValue = parseInt(obj.value);
  if (event.keyCode == 13 && pageValue)
  {
    var pageLink = document.getElementById('page_link').href;

    if (obj.name == 'page_size')
    {
      pageLink += '&page_size='+pageValue;
    }
    else if (obj.name == 'page')
    {
      pageLink += '&page='+pageValue;
    }

    Ajax.call(pageLink, '', sendToServerResponse, 'GET', 'JSON');
  }
}

/**
 * 公共查询
 function search ()
 {
 var theForm = document.forms['searchForm'];
 var keyword = theForm.elements['search'].value;
 if (!isNaN(keyword) && keyword.length < 6)
 {
 alert('您输入的关键词为数字，请不要少于6位！');
 return false;
 }
 else if (keyword.length <2)
 {
 alert('您输入的关键词为数字，请不要少于2个中文字符！');
 return false;
 }

 Ajax.call('search.php?act=search&keyword='+keyword, '', showSearchResponse, 'GET', 'JSON');
 }
 */

/**
 * 公共查询输出结果函数
 */
function showSearchResponse (res)
{
  document.getElementById('showRes').innerHTML = res.main;
}

/**
 * 收货日期
 */
function receiptGoods(id)
{
  Ajax.call('order.php', 'act=receive_goods&id='+id, receiptGoodsResponse, 'POST', 'JSON');
}

/**
 * 收货日期 (回调函数)
 */
function receiptGoodsResponse (res)
{
  showMsg(res);
  if (res.code == 1)
  {
    document.getElementById('shipping_'+res.order_id).src = "images/1.gif";
  }
}

/**
 * 退货
 */
function return_goods_sign (id)
{
  Ajax.call('order.php', 'act=return_goods_sign&id='+id, receiptGoodsResponse, 'POST', 'JSON');
}

/**
 * 确认收货
 */
function submitReceiveDate ()
{
  var theForm = document['date'];

  var id           = theForm.elements['id'].value;
  var receive_time = theForm.elements['receive_time'].value;

  var receive_status = null;
  for (var i = theForm.elements['receive_status'].length - 1; i >= 0; i--) {
    if (theForm.elements['receive_status'][i].checked) {
      receive_status = theForm.elements['receive_status'][i].value;
    }
  }

  if (!receive_time && !receive_status) {
    var tmp = document.getElementById('msg').getElementsByTagName('p')[0].innerHTML;

    var res = {};
    res.message = '请选择 收货时间 或 其它选项！';
    res.req_msg = true;
    res.timeout = 2000;
    showMsg(res);

    var msgTmp = {};
    msgTmp.message = tmp;
    msgTmp.req_msg = true;
    msgTmp.title   = '请选择顾客的收货时间：';
    msgTmp.btncontent = false;

    msgTmp = JSON.stringify(msgTmp);
    setTimeout("showMsg("+msgTmp+")", 2000);

    return false;
  }

  var data = 'act=shipping_done&id='+id+'&receive_status='+receive_status+'&receive_time='+receive_time;
  data += '&others='+theForm.elements['others'].value;

  document.getElementById('msg').className = 'hide';
  document.body.removeChild(document.getElementById('clip'));
  Ajax.call('order.php', data, submitReceiveDateResponse, 'POST', 'JSON');
}

/**
 * 确认收货 (回调函数)
 */
function submitReceiveDateResponse (res)
{
  showMsg(res);

  var objPage = document.getElementById('page_link');
  var Url = objPage.href;
  var objPageList = objPage.parentNode.parentNode.getElementsByTagName('span');
  var page = null
    for (var i = objPageList.length - 1; i >= 0; i--){
      if (objPageList[i].className == 'cur') {
        page = parseInt(objPageList[i].innerText);
      }
    }

  Url = Url.replace(/page=\d+/, 'page='+page);
  Ajax.call(Url, '', sendToServerResponse, 'GET', 'JSON');

  return false;
}

/**
 * 销售统计
 */
function salesStats (obj)
{
  var start_time = document.getElementById('start_time').value;
  var end_time   = document.getElementById('end_time').value;

  if (!start_time || !end_time)
  {
    var msg = new Array ();
    msg.timeout = 2000;
    msg.req_msg = true;
    msg.message = '必须输入完整的查询参数！';

    showMsg(msg);
    return false;
  }

  var pattern = new RegExp(/act=(\w+)&?/);
  action  = pattern.exec(obj.value);
  action  = action[1];
  Ajax.call(obj.value, 'start_time='+start_time+'&end_time='+end_time, salesStatsResponse, 'GET', 'JSON');
}

/**
 * 销售统计 (回调函数)
 */
function salesStatsResponse (res)
{
  var tableObj = document.getElementById(res.act);
  //tableObj.innerHTML = res.main;
  main = document.getElementById('main');
  main.innerHTML = res.main;

  init();
}

/**
 * 重复购买
 */
function statsRebuy (obj)
{
  var start_time = document.getElementById('start_time').value;
  var end_time   = document.getElementById('end_time').value;
  var admin_id   = document.getElementById('admin_id').value;

  var data = 'start_time='+start_time+'&end_time='+end_time+'&admin_id='+admin_id;
  if (document.getElementById('platform')) {
    var platform   = document.getElementById('platform').value;
    data += '&platform='+platform;
  }
  Ajax.call(obj.value, data, statsRebuyResponse, 'POST', 'JSON');
}

/**
 * 重复购买 (回调函数)
 */
function statsRebuyResponse (res)
{
  if (res.code == 2)
  {
    showMsg(res);
    return false;
  }

  document.getElementById('stats_rebuy').innerHTML = res.main;
  document.getElementById('order_num').innerHTML = res.order_num;
  document.getElementById('amount').innerHTML = res.amount;
}

/**
 * 修改平台
 */
function changePlatform (obj)
{
  if (obj.value > 0)
  {
    Ajax.call('report_forms.php', 'act=get_platform_admin&platform='+obj.value, changePlatformResponse, 'POST', 'JSON');
  }
}

/**
 * 修改平台（回调函数）
 */
function changePlatformResponse (res)
{
  var selObj = document.getElementById('admin_id');
  selObj.length = 0;

  var opt   = document.createElement('option');
  opt.text  = '选择客服';
  opt.value = 0;
  selObj.add(opt, null);

  for (var i = 0; i < res.length; i++)
  {
    var opt   = document.createElement('option');
    opt.text  = res[i].user_name;
    opt.value = res[i].user_id;

    selObj.add(opt, null);
  }
}

/**
 * 计算回购率
 */
function statsRate (obj)
{
  var startTimeObj = document.getElementById('start_time');
  var endTimeObj   = document.getElementById('end_time');
  var platformObj  = document.getElementById('platform');
  var rowIdObj     = document.getElementById('row');
  var adminIdObj   = document.getElementById('admin_id');

  var data = ['ajax=1'];

  // 时间
  if (startTimeObj && endTimeObj && startTimeObj.value && endTimeObj.value) {
    data.push('start_time='+startTimeObj.value, 'end_time='+endTimeObj.value);
  }

  // 平台
  if (platformObj && parseInt(platformObj.value)) {
    data.push('platform='+platformObj.value);
  }

  // 小组
  if (rowIdObj && parseInt(rowIdObj.value)) {
    data.push('row_id='+rowIdObj.value);
  }

  // 客服
  if (adminIdObj && parseInt(adminIdObj.value)) {
    data.push('admin_id='+adminIdObj.value);
  }

  if (data.length > 0) {
    Ajax.call(obj.value, data.join('&'), statsRateResponse, 'POST', 'JSON');
  }
}

/**
 * 计算回购率(回调函数)
 */
function statsRateResponse (res)
{
  document.getElementById('rate_stats').innerHTML = res.main;
}

/**
 * 发送退回商品信息
 */
function sendBackGoods ()
{
  var theForm = document['BackGoods'];
  var data = new Array();
  for(var i=0; i<theForm.length; i++){
    if (theForm.elements[i].name)
    {
      data.push(theForm.elements[i].name+'='+theForm.elements[i].value);
    }
  }

  data = data.join('&');
  Ajax.call('order.php?act=handle_storage_back', data, receiptGoodsResponse, 'POST', 'JSON');
  return false;
}

/**
 * 逐日核对订单数据
 */
function sendCheckData (obj)
{
  Ajax.call('finance.php?act=everyday_order_check', 'month='+obj.value, sendCheckDataResponse, 'POST', 'JSON');
}

/**
 * 逐日核对订单数据 （回调函数）
 */
function sendCheckDataResponse (res)
{
  document.getElementById('main').innerHTML = res.main;
  init();
}

/**
 * 会员部销售统计日期查询
 */
function statsMember (obj)
{
  var start = document.getElementById('start_time').value;
  var end   = document.getElementById('end_time').value;

  Ajax.call(obj.value, 'start_time='+start+'&end_time='+end, statsMemberResp, 'GET', 'JSON');
}

function statsMemberResp (res)
{
  document.getElementById('stats_member').innerHTML = res.main;
}

/**
 * 查询个人销量
 */
function querySales (obj) {
  var start = obj.elements['start_time'].value;
  var end   = obj.elements['end_time'].value;

  Ajax.call('report_forms.php', 'act=stats_saler_month&start='+start+'&end='+end, querySalesResp, 'POST', 'JSON');
  return false;
}

/**
 * 查询个人销量  回调函数
 */
function querySalesResp (res) {
  document.getElementById('person_style').innerHTML = res;
}

/**
 * 判断是否数组包含某个元素
 */
function in_array (arr, str) {
  for (var i=0; i < arr.length; i++) {
    if (arr[i] == str) return true;
  };

  return false;
}

function hightLight (domTableId, rowNum) {
  var trNodesLeft  = document.getElementById(domTableId+'-left').getElementsByTagName('tr');
  var trNodesRight = document.getElementById(domTableId+'-right').getElementsByTagName('tr');

  for (var i=0; i < trNodesLeft.length; i++)
  {
    trNodesLeft[i].className  = '';
    trNodesRight[i].className = '';
  };

  trNodesLeft[rowNum].className  = 'trhover';
  trNodesRight[rowNum].className = 'trhover';

  trNodesRight[rowNum].onmouseout = trNodesLeft[rowNum].onmouseout = function () {
    trNodesLeft[rowNum].className  = '';
    trNodesRight[rowNum].className = '';
  };
}

/**
 * 显示商品的详细信息
 */
function displayGoodsDetail (goodsSn) {
  if (goodsSn != 0) {
    Ajax.call('order.php', 'act=get_goods_detail&sn='+goodsSn, showGoodsDetailResp, 'POST', 'JSON');
  };
}

function displayGoodsDetailResp (res) {
  //alert(res);
}

/**
 * 表格行同时高亮
 */
function hightLightRows () {

  var objLeftTable  = document.getElementById('left_table');
  var objRightTable = document.getElementById('right_table');


  if (objLeftTable && objRightTable) {
    for (var i = 0; i < objLeftTable.rows.length; i++)
    {
      objRightTable.tBodies[0].rows[i].className = 'tr_list';
      objLeftTable.tBodies[0].rows[i].className  = 'tr_list';
      if (objLeftTable.tBodies[0].rows[i].id == 'temp_1' || objRightTable.tBodies[0].rows[i].id == 'temp_2') {
        continue;
      }
      objRightTable.tBodies[0].rows[i].onmouseover = objLeftTable.tBodies[0].rows[i].onmouseover = function () {
        objRightTable.tBodies[0].rows[this.rowIndex].className = 'tr_list tr_bg_list';
        objLeftTable.tBodies[0].rows[this.rowIndex].className  = 'tr_list tr_bg_list';
      };

      objRightTable.tBodies[0].rows[i].onmouseout = objLeftTable.tBodies[0].rows[i].onmouseout = function () {
        objRightTable.tBodies[0].rows[this.rowIndex].className = 'tr_list';
        objLeftTable.tBodies[0].rows[this.rowIndex].className  = 'tr_list';
      };
    }
  }
}

// 分页初始化
function pageInit () {
  var pageList = document.getElementById('pageList');
  if (pageList == undefined) {
    return false;
  }
  var link = pageList.getElementsByTagName('a');
  for (var i = link.length - 1; i >= 0; i--){
    link[i].onclick = pageNext;
  };

  return false;
}

/**
 * 翻页函数
 */
function pageNext () {
  Ajax.call(this.href+'&method=Ajax', '', pageNextResp, 'POST', 'JSON');
  return false;
}

/**
 * 数据展示回调函数
 */
function pageNextResp (res) {
  if (res.page) {
    var dataMain = document.getElementById('dataList');
    var pageList = document.getElementById('pageList');
    if (dataMain == undefined || pageList == undefined) {
      return false;
    }
    dataMain = dataMain.tBodies ? dataMain.tBodies[0] : dataMain;
    dataMain.innerHTML = res.main;
    pageList.innerHTML = res.page;
  } else {
    document.getElementById('main').innerHTML = res.main;
  }

  init();
  return false;
}

/**
 * 统计发出的商品数量
 */
function statsShippingGoods () {
  var theForm = document.forms['pageSearch'];
  if (theForm == undefined) {
    return false;
  }

  var data = {};
  for (var i = 0; i < theForm.elements.length; i++) {
    if (theForm.elements[i].value && theForm.elements[i].value != 0 && theForm.elements[i].name)
      data[theForm.elements[i].name] = theForm.elements[i].value;
  }

  var str = JSON.stringify(data);

  Ajax.call(theForm.action, 'data='+str, statsShippingGoodsResp, 'POST', 'JSON');
  return false;
}

/**
 * 统计发出的商品数量 回调函数
 */
function statsShippingGoodsResp (res) {
  document.getElementById('resource').innerHTML = res.main;
  init();
}

/**
 * 更换数据信息
 */
function statsPreSales () {
  var theForm = document.forms['pageSearch'];
  var data = '';
  if (theForm.elements['startTime'] && theForm.elements['endTime']) {
    data += 'start_time='+theForm.elements['startTime'].value+'&end_time='+theForm.elements['endTime'].value;
  }

  if (theForm.elements['admin_id'] != undefined && theForm.elements['admin_id'].value > 0) {
    data += '&admin_id='+theForm.elements['admin_id'].value;
  }

  Ajax.call(theForm.action, data, statsPreSalesResp, theForm.method, 'JSON');
  return false;
}

function statsSaleDetail () {
  var theForm = document.forms['pageSearch'];
  var data = '';
  if (theForm.elements['order_month']) {
    data += 'order_month='+theForm.elements['order_month'].value;
  }

  if (theForm.elements['admin_id'] != undefined && theForm.elements['admin_id'].value > 0) {
    data += '&admin_id='+theForm.elements['admin_id'].value;
  }

  Ajax.call(theForm.action, data, statsPreSalesResp, theForm.method, 'JSON');
  return false;
}

function statsPreSalesResp (res) {
  var showArea = document.getElementById('dataList');
  showArea.innerHTML = res.main;
  tableSort();
}

/**
 * 移动消息框
 */
window.onload = function () {
  var objMsg = document.getElementById('msg');
  var startX = null;
  var startY = null;
  objMsg.getElementsByTagName('h3')[0].onmousedown = function () {
    var maxWidth = objMsg.style.width = getComputedStyle(objMsg, false)['width'];
    objMsg.getElementsByTagName('h3')[0].style.cursor = 'move';
    startX = event.clientX - objMsg.offsetLeft;
    startY = event.clientY - objMsg.offsetTop;
    objMsg.getElementsByTagName('h3')[0].onmousemove = function () {
      var currentX = event.clientX - startX;
      var currentY = event.clientY - startY;

      //var maxLeft = parseInt(document.documentElement.offsetWidth) - parseInt(maxWidth);

      currentY = currentY > 0 ? currentY : 0;
      //currentX = currentX < maxLeft ? currentX : maxLeft;

      objMsg.style.left = parseInt(currentX) + 'px';
      objMsg.style.top  = parseInt(currentY) + 'px';
    };

    document.onmouseup = function () {
      objMsg.getElementsByTagName('h3')[0].onmousemove = null;
      document.onmouseup   = null;
      objMsg.getElementsByTagName('h3')[0].style.cursor = 'default';
    };

    return false;
  };
};

/**
 * 通用查询
 */
function commonSearch () {
  var theForm = document.forms['pageSearch'];

  var keyData = [];
  for (var i=0; i < theForm.length; i++) {
    if (theForm[i].name && theForm[i].value != 0) {
      keyData.push(theForm[i].name+'='+theForm[i].value);
    }
  };

  if (keyData.length > 0) {
    keyData.push('method=Ajax');
    keyData = keyData.join('&');
    var url = document.getElementById('page_link').href || theForm.action;
    Ajax.call(url, keyData, pageNextResp, 'POST', 'JSON');
  } else {
    var msg = {'message':'未选择搜索条件','timeout':2000,'req_msg':true};
    showMsg(msg);
  }

  return false;
}

/**
 * 替换HTML
 */
function replaceHTML (obj, id) {
  if (obj.innerHTML && !isNaN(obj.innerHTML)) {
    return false;
  }

  obj.innerHTML = '<input id="card_number" value="" placeholder="请输入卡号后四位" onfocus="listCardNumber(this,'+id+')" onkeyup="listCardNumber(this,'+id+')" onblur="saveCardNumber(this,'+id+')">';

  obj.style.padding = 0;
  obj.children[0].style.width = '115px';
  obj.children[0].style.marginLeft = 0;

  document.getElementById('card_number').focus();

  orderId = id;
}

/**
 * 动态查询会员卡号
 */
function listCardNumber (obj, id) {
  var ulObj = document.getElementById('card_list');
  if (obj.value.length > 1 && obj.value.length != 10) {
    Ajax.call('memship.php?act=card_list&card_number='+obj.value+'&order_id='+id, '', listCardNumberResp, 'GET', 'JSON');
  } else if(ulObj) {
    ulObj.parentNode.removeChild(ulObj);
  }
}

function listCardNumberResp (res) {
  // 发生异常
  if (res.code) {
    showMsg(res);

    // 顾客已绑定会员卡
    var cardNumberObj = document.getElementById('card_number');
    cardNumberObj.parentNode.innerHTML = res.card_number;

    return false;
  }

  var inputObj = document.getElementById('card_number');
  var tdObj = inputObj.parentNode;

  var ulObj = document.getElementById('card_list');
  if (ulObj) {
    ulObj.parentNode.removeChild(ulObj);
  }

  ulObj = document.createElement('ul');
  ulObj.id = 'card_list';
  ulObj.className = 'CardNumber';

  ulObj.onmouseout = function () {
    timer = setTimeout(function () {ulObj.parentNode.removeChild(ulObj);}, 500);
  };

  ulObj.onmouseover = function () {
    clearTimeout(timer);
  };

  for (var i = res.length - 1; i >= 0; i--){
    var liObj = document.createElement('li');
    liObj.innerText = res[i];

    liObj.onclick = function () {
      selectMemCard(this);
    };

    ulObj.appendChild(liObj);
  };

  tdObj.appendChild(ulObj);
}

/**
 * 选择备选会员卡号 并发送至服务器绑定会员
 */
function selectMemCard (obj) {
  var cardNumberObj = document.getElementById('card_number');
  cardNumberObj.value = obj.innerText;
  cardNumberObj.focus();

  var ulObj = document.getElementById('card_list');
  if (ulObj) {
    ulObj.parentNode.removeChild(ulObj);
  }
}

/**
 * 发送卡号到服务器并与顾客id绑定
 */
function saveCardNumber (obj,id) {
  if (obj.value.length == 10) {
    Ajax.call('memship.php?act=bind_card_number&card_number='+obj.value+'&order_id='+id, '', saveCardNumberResp, 'GET', 'JSON');
  }
}

function saveCardNumberResp (res) {
  showMsg(res); // 显示提示信息

  // 将input替换为html
  if (res.code == 1) {
    var cardNumberObj = document.getElementById('card_number');
    cardNumberObj.parentNode.innerHTML = res.card_number;
    return false;
  }
}

/**
 * 查询详细信息
 */
function getDetail (obj) {
  if (delRowFromtBodies(obj, 'detail')) {
    Ajax.call(obj.href, '', getDetailResp, 'POST', 'JSON');
  }
  return false;
}

/**
 * 处理查询结果
 */
function getDetailResp (res) {
  appendChildIntBodies(res);
}

/**
 * 显示信息到表格中
 */
function appendChildIntBodies (res) {
  var trObj = document.getElementById('tr_'+res.id);
  var newTrObj = document.createElement('tr');
  var newTdObj = document.createElement('td');

  newTdObj.innerHTML = res.info;
  newTdObj.colSpan = trObj.children.length;
  newTrObj.id = res.response_action;

  newTrObj.setAttribute('detail', res.id);

  newTrObj.appendChild(newTdObj);

  trObj.parentNode.insertBefore(newTrObj, trObj.nextSibling);
}

/**
 * 删除已有的信息行
 */
function delRowFromtBodies (obj, id) {
  var detailObj = document.getElementById(id);
  if (detailObj) {
    if ('tr_'+detailObj.getAttribute('detail') == obj.parentNode.parentNode.id) {
      document.getElementById('tr_'+detailObj.getAttribute('detail')).parentNode.removeChild(detailObj);
      return false;
    } else {
      document.getElementById('tr_'+detailObj.getAttribute('detail')).parentNode.removeChild(detailObj);
      return true;
    }
  } else {
    return true;
  }
}

/**
 * 隐藏/显示左侧菜单
 */
function showOrHideMenu (obj) {
  var menuObj = document.getElementById('left_parent');
  var reportFormObj = document.getElementById('person_style');

  if (reportFormObj) {
    if (menuObj.style.display == 'none') {
      reportFormObj.style.left = reportFormObj.offsetLeft + offsetDisX + 'px';
    } else if (menuObj.style.display == 'inline') {
      reportFormObj.style.left = reportFormObj.offsetLeft - offsetDisX + 'px';
    }
  }

  menuObj.style.display = menuObj.style.display == 'none' ? 'inline' : 'none';
}

/**
 * 选中 反选
 */
function checkboxSelected (id) {
  var boxObj = document.getElementById(id);
  checkboxList = boxObj.getElementsByTagName('input');

  for (var i = checkboxList.length - 1; i >= 0; i--){
    if (checkboxList[i].type == 'checkbox') {
      if (checkboxList[i].checked) {
        checkboxList[i].checked = false;
      } else {
        checkboxList[i].checked = true;
      }
    }
  }
}

/**
 * td中插入input
 */
function insertInputToTd (obj, name, id) {
  if (obj.innerText.length > 0) {
    if (confirm('确定要修改该条形码？')) {
    } else {
      return false;
    }
  }

  var inputObj = document.createElement('input');

  inputObj.type  = 'text';
  inputObj.name  = name;
  inputObj.value = obj.innerText;

  inputObj.onblur = function () {
    if (! this.value) {
      showMsg({req_msg:true,timeout:2000,message:'请输入正确的条形码！'});
    } else if (confirm('确定修改条码？')) {
      this.parentNode.id = 'goods_'+id;
      Ajax.call('single_interact.php', 'act='+name+'&value='+this.value+'&id='+id, insertInputToTdResp, 'POST', 'JSON');
    }
  };

  obj.innerText = '';
  obj.appendChild(inputObj);
}

function insertInputToTdResp (res) {
  if (res.code == 1) {
    var tdObj = document.getElementById('goods_'+res.id);
    tdObj.innerHTML = res.bar_code;
    tdObj.removeAttribute('id');
  }

  showMsg(res);
}

function replaceToAddr (obj) {
  if (obj.value == 'addr') {
    Ajax.call('order.php?act=get_regions', '', replaceToAddrResp, 'GET', 'TEXT');
  } else {
    obj.parentNode.nextSibling.nextSibling.innerHTML = '<input type="text" name="contact_value" value="">';
  }

  return false;
}

function replaceToAddrResp (res) {
  var theForm = document.forms['contactInfo'];
  theForm.elements['contact_value'].parentNode.innerHTML = res+theForm.elements['contact_value'].parentNode.innerHTML;
  document.getElementById('selDistricts').onchange = null;
}


/**
 *  表格列高亮
 */
function cellObjSelect () {
  if (document.getElementById('colHL')) {
    var cellObj = document.getElementsByTagName('td');
    for (var i = cellObj.length -1; i >= 0; i--) {
      cellObj[i].onmouseover = function () {
        colHL(this);
      }
    }
  }
}

function colHL(obj) {
  var focusCellIndex      = obj.cellIndex;
  var focusCellBackground = obj.style.background;
  var tableObj            = obj.parentNode.parentNode.parentNode;

  for (var i = tableObj.rows.length -1; i >= 0; i--) {
    if (tableObj.rows[i] && tableObj.rows[i].cells[focusCellIndex] && tableObj.rows[i].cells[focusCellIndex].style && tableObj.rows[i].cells[focusCellIndex].colSpan == 1 && focusCellIndex != 0) {
      tableObj.rows[i].cells[focusCellIndex].style.background = '#78B878';
      tableObj.rows[i].cells[focusCellIndex].onmouseout = function () {
        clearColHL(this, focusCellBackground);
      };
    }
  }
}

function clearColHL(obj, focusCellBackground) {
  var focusCellIndex = obj.cellIndex;
  var tableObj = obj.parentNode.parentNode.parentNode;

  for (var i = tableObj.rows.length -1; i >= 0; i--) {
    if (tableObj.rows[i] && tableObj.rows[i].cells[focusCellIndex] && tableObj.rows[i].cells[focusCellIndex].style && tableObj.rows[i].cells[focusCellIndex].colSpan == 1) {
      tableObj.rows[i].cells[focusCellIndex].style.background = focusCellBackground;
    }
  }
}

/**
 * 添加排序功能
 */
function addSortEvent () {
  var sortRowObj = document.getElementById('sortByThis');
  var offset     = 0;

  if (!sortRowObj) {
    var rowsObj = document.getElementsByTagName('tr');
    for (var i = rowsObj.length -1; i >= 0; i--) {
      if (rowsObj[i].className == 'sortByThis') {
        for (var j = rowsObj[i].cells.length -1; j > 0; j--) {
          rowsObj[i].cells[j].onclick = function () {
            sortByThisCol(this);
          };
        }
      }
    }
  }

  if (sortRowObj) {
    if (document.getElementById('rank')) {
      offset = 1;
    }

    for (var i = sortRowObj.cells.length -1; i >= offset; i--) {
      sortRowObj.cells[i].onclick = function () {
        sortByThisCol(this);
      };
    }
  }
}

/**
 * 显示/隐藏 子菜单
 */
function showSubMenu(obj) {
  var detailsList = document.getElementById('left').getElementsByTagName('details');
  for (var i = detailsList.length -1; i >= 0; i--) {
    detailsList[i].removeAttribute('open');
  }
}

/**
 * 客服检索
 */
function commonFilterByThis(id) {
  var listObj = document.getElementById(id);
  var role_id = document.getElementById('role_id').value;
  var group_id = document.getElementById('group_id').value;

  for (var i = listObj.children.length -1; i >= 0; i--) {
    var roleBoolean = parseInt(role_id) && parseInt(role_id) == parseInt(listObj.children[i].getAttribute('role_id'));
    var groupBoolean = parseInt(group_id) && parseInt(group_id) == parseInt(listObj.children[i].getAttribute('group_id'));

    if (roleBoolean && groupBoolean) {
      listObj.children[i].className = '';
    } else if (!parseInt(group_id) && roleBoolean) {
      listObj.children[i].className = '';
    } else if (!parseInt(group_id) && !parseInt(role_id)) {
      listObj.children[i].className = '';
    } else {
      listObj.children[i].className = 'hide';
    }
  }
}

/**
 * 把文本替换成可编辑表单
 */
function replaceToInputHTML(obj, id, formType, func) {

  var formHtml   = document.createElement('input');
  formHtml.type  = formType || 'text';
  formHtml.value = obj.innerText;
  formHtml.id    = 'html_'+id;
  formHtml.size  = 10;

  formHtml.onblur = function () {
    func(formHtml, id);
  };

  obj.innerHTML = '';
  obj.appendChild(formHtml);
}

/**
 * 提交更新的数据
 */
function postUpdateData(formObj, postUrl) {
  if (formObj.value.length > 0 && formObj.value > 0) {
    Ajax.call(postUrl, 'field='+formObj.value, postUpdateDataResp, 'POST', 'JSON');
  } else {
    showMsg({req_msg:true,timeout:2000,message:'请检查输入是否正确？！'});
  }
}

function postUpdateDataResp(res) {
  var formObj = document.getElementById('html_'+res.form_id);
  formObj.parentNode.innerHTML = res.value;

  showMsg(res);
  return false;
}

/**
 * 级联下拉菜单
 */
function linkSelectMenu() {
  var searchUrl = document.getElementById('searchUrl').value;
  var brandId   = document.getElementById('brand').value;

  Ajax.call(searchUrl, 'brand_id='+brandId, linkSelectMenuResp, 'POST', 'JSON');
}

function linkSelectMenuResp(res) {
  var subSelectObj = document.getElementById('goods');
  subSelectObj.length = 0;

  for (var i=0; i < res.length; i++) {
    subSelectObj.add(new Option(res[i].goods_name, res[i].goods_id));
  }

  return false;
}

/**
 * 查询产品相关的顾客
 */
function showGoodsUsers() {
  var goodsId = document.getElementById('goods').value;
  var obj = document.forms['search_form'];
  var data = [];
  for (var i = 0; i < obj.elements.length; i++) {
    if ('goods_id' == obj.elements[i].name && obj.elements[i].value==0) {
        var msg = [];
        msg['timeout'] = 2000;
        msg['message']= '请选择商品';
        showMsg(msg);
        return;
    }else{
      data.push(obj.elements[i].name+'='+obj.elements[i].value);
    }
  }
  Ajax.call('users.php?act=show_goods_users',data.join('&'), showGoodsUsersResp, 'GET', 'JSON');
}

function showGoodsUsersResp(res) {
  document.getElementById('listDiv').innerHTML = res.main;
  document.getElementById('pageDiv').innerHTML = res.page;
  init();
}
