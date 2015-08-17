/**
 * 发送获取订单请求
 */
function giveMeOrder (obj) {
  Ajax.call(obj.value, '', sendToServerResponse, 'POST', 'JSON');
}


/**
 * 订单商品信息过滤
 */
function sendGoodsFilter () {
  try {
    var filterURL = document.getElementById('page_link').href;
    var goods_kind = document.getElementById('goods_kind').value;
    if (filterURL == undefined) {
      filterURL = 'order.php?act=current_order'; 
    }

    Ajax.call(filterURL+'&goods_kind='+goods_kind, '', sendToServerResponse, 'GET', 'JSON');
  } catch (ex) {
    alert(ex);
  }
}

/**
 * 查询订单是否已经存在
 */
function checkOrderSn (obj) {
  // 拍拍
  if (/^C\d{2}-\d{7}-\d{7}$|^\d{14}$|^\d{15,16}$|^\d{13}$|^\d{10,11}$|^\d{8}-\d{8}-\d{10}$|^\d{6}[0-9a-z]{6}$/i.test(obj.value)) {
    Ajax.call('order.php?act=check_order&order_sn='+obj.value, '', checkOrderSnResp, 'GET', 'JSON');
  } else if (obj.value.length > 0){
    showMsg({req_msg:true,message:'请填写正确的订单编号！'});
    obj.focus();
  }
}

function checkOrderSnResp(res) {
  if (res.req_msg) {
    showMsg(res);

    document.getElementById('detail').parentNode.removeChild(document.getElementById('detail'));
  }

  return false;
}

/**
 * 添加订单编号到刷单列表
 */
function addOrderSnForBrush(inputObj, oid) {
  var orderSn       = inputObj.value;

  if (!inputObj.value) {
    inputObj.parentNode.innerHTML = '';
    return false;
  }

  var brushPlatform = inputObj.parentNode.getAttribute('platform');

  var msg = {req_msg:true,timeout:2000};
  if (!inputObj.value) {
    msg.message = '请填写正确的订单编号！';
    showMsg(msg);
    return false;
  }

  switch (brushPlatform) {
    case '6':
      if (!/^\d{15}$/.test(orderSn)) {
        msg.message = '提交的订单编号不是来自天猫！';
      }
      break;
    case 10:
    case 16:
      if (!/^\d{10}$/.test(orderSn)) {
        msg.message = '提交的订单编号不是来自京东或当当！';
      }
      break;
    case 14:
      if (!/^\d{12}$/.test(orderSn)) {
        msg.message = '提交的订单编号不是来自一号店！';
      }
      break;
  }

  if (msg.message) {
    showMsg(msg);
    inputObj.focus();
    return false;
  }

  Ajax.call('order.php?act=save_brush_order_sn', 'order_sn='+orderSn+'&brush_platform='+brushPlatform+'&order_id='+oid, addOrderSnForBrushResp, 'POST', 'JSON');
}

function addOrderSnForBrushResp(res) {
  var inputObj = document.getElementById('html_'+res.id);
  inputObj.parentNode.innerText = inputObj.value;
  showMsg(res);
}

/**
 * 显示顾客联系信息：手机、电话、地址
 */
function showThisInfo(oid, table) {
  Ajax.call('order.php?act=show_single_info&order_id='+oid+'&table='+table, '', showThisInfoResp, 'GET', 'JSON');
}

function showThisInfoResp(res) {
  showMsg(res);
}
