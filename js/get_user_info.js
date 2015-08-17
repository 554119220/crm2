//弹出用户详细信息
function getDetail(user_id)
{
      Ajax.call('users.php?act=get_detail&user_id=' + user_id, '', showDetail, 'GET', 'TEXT');
}

function showDetail(detail)
{
      document.getElementById('envon').innerHTML = detail;
      EV_modeAlert('envon');
}
//弹出添加服务信息
function addService(user_id, username)
{
      Ajax.call('users.php?act=add_service&user_id=' + user_id, '&username='+username, showForm, 'GET', 'TEXT');
}
//显示弹出层
function showForm(form)
{
      document.getElementById('envon').innerHTML = form;
}
//弹出添加订单信息
function addOrder(user_id, username)
{
      Ajax.call('order.php?act=add_order&user_id=' + user_id, '', showForm, 'GET', 'TEXT');
}

/**
* 按商品编号或商品名称或商品货号搜索商品
*/
function searchGoods()
{
      var eles = document.forms['goodsForm'].elements;

      /* 填充列表 */
      var keyword = Utils.trim(eles['keyword'].value);
      if (keyword != '')
      {
            Ajax.call('order.php?act=search_goods&keyword=' + keyword, '', searchGoodsResponse, 'GET', 'JSON');
      }
}

function searchGoodsResponse(result)
{
      if (result.message.length > 0)
      {
            alert(result.message);
      }
      if (result.error == 0)
      {
            var eles = document.forms['goodsForm'].elements;
            /* 清除列表 */
            var selLen = eles['goodslist'].options.length;
            for (var i = selLen - 1; i >= 0; i--)
            {
                  eles['goodslist'].options[i] = null;
            }
            var arr = result.goodslist;
            var goodsCnt = arr.length;
            if (goodsCnt > 0)
            {
                  for (var i = 0; i < goodsCnt; i++)
                  {
                        var opt = document.createElement('OPTION');
                        opt.value = arr[i].goods_id;
                        opt.text = arr[i].name;
                        eles['goodslist'].options.add(opt);
                  }
                  getGoodsInfo(arr[0].goods_id);
            }
            else
            {
                  getGoodsInfo(0);
            }
      }
}

/**
* 取得某商品信息
* @param int goodsId 商品id
*/
function getGoodsInfo(goodsId)
{
      if (goodsId > 0)
      {
            Ajax.call('order.php?act=json&func=get_goods_info', 'goods_id=' + goodsId, getGoodsInfoResponse, 'get', 'json');
      }
      else
      {
            document.getElementById('goods_name').innerHTML = '';
            document.getElementById('goods_sn').innerHTML = '';
            document.getElementById('goods_cat').innerHTML = '';
            document.getElementById('goods_brand').innerHTML = '';
            document.getElementById('add_price').innerHTML = '';
            document.getElementById('goods_attr').innerHTML = '';
      }
}

function getGoodsInfoResponse(result)
{
      var eles = document.forms['goodsForm'].elements;
      // 显示商品名称、货号、分类、品牌
      document.getElementById('goods_name').innerHTML = result.goods_name;
      document.getElementById('goods_sn').innerHTML = result.goods_sn;
      //document.getElementById('goods_cat').innerHTML = result.cat_name;
      document.getElementById('goods_brand').innerHTML = result.brand_name;
      document.getElementById('add_price').innerHTML = result.goods_price;
      // 显示价格：包括市场价、本店价（促销价）、会员价
      var priceHtml = '<input type="radio" name="add_price" value="' + result.market_price + '" />市场价 [' + result.market_price + ']<br />' +
            '<input type="radio" name="add_price" value="' + result.goods_price + '" checked />本店价 [' + result.goods_price + ']<br />';
      for (var i = 0; i < result.user_price.length; i++)
      {
            priceHtml += '<input type="radio" name="add_price" value="' + result.user_price[i].user_price + '" />' + result.user_price[i].rank_name + ' [' + result.user_price[i].user_price + ']<br />';
      }
      priceHtml += '<input type="radio" name="add_price" value="user_input" />' + input_price + '<input type="text" name="input_price" value="" /><br />';
      document.getElementById('add_price').innerHTML = priceHtml;
      // 显示属性
      var specCnt = 0; // 规格的数量
      var attrHtml = '';
      var attrType = '';
      var attrTypeArray = '';
      var attrCnt = result.attr_list.length;
      for (i = 0; i < attrCnt; i++)
      {
            var valueCnt = result.attr_list[i].length;
            // 规格
            if (valueCnt > 1)
            {
                  attrHtml += result.attr_list[i][0].attr_name + ': ';
                  for (var j = 0; j < valueCnt; j++)
                  {
                        switch (result.attr_list[i][j].attr_type)
                        {
                              case '0' :
                              case '1' :
                                    attrType = 'radio';
                                    attrTypeArray = '';
                                    break;
                              case '2' :
                                    attrType = 'checkbox';
                                    attrTypeArray = '[]';
                                    break;
                        }
                        attrHtml += '<input type="' + attrType + '" name="spec_' + specCnt + attrTypeArray + '" value="' + result.attr_list[i][j].goods_attr_id + '"';
                        if (j == 0)
                        {
                              attrHtml += ' checked';
                        }
                        attrHtml += ' />' + result.attr_list[i][j].attr_value;
                        if (result.attr_list[i][j].attr_price > 0)
                        {
                              attrHtml += ' [+' + result.attr_list[i][j].attr_price + ']';
                        }
                        else if (result.attr_list[i][j].attr_price < 0)
                        {
                              attrHtml += ' [-' + Math.abs(result.attr_list[i][j].attr_price) + ']';
                        }
                  }
                  attrHtml += '<br />';
                  specCnt++;
            }
            // 属性
            else
            {
                  attrHtml += result.attr_list[i][0].attr_name + ': ' + result.attr_list[i][0].attr_value + '<br />';
            }
      }
      eles['spec_count'].value = specCnt;
      document.getElementById('goods_attr').innerHTML = attrHtml;
}

/**
* 把商品加入订单
*/
function addToOrder()
{
      var eles = document.forms['goodsForm'].elements;
      var goodsId = eles['goodslist'].value;
      // 检查是否选择了商品
      if (eles['goodslist'].options.length <= 0)
      {
            alert(pls_search_goods);
            return false;
      }
      Ajax.call('order.php?act=addToCart', 'goods_id=' + goodsId, showList, 'GET', 'TEXT');
      document.getElementById('goods_name').innerHTML = '';
      document.getElementById('goods_sn').innerHTML = '';
      document.getElementById('goods_brand').innerHTML = '';
      document.getElementById('add_price').innerHTML = '';
}

function showList(res)
{
      var tr = document.getElementById('goods_list').insertRow(1);
      tr.innerHTML = res;
      changePayment();
      calcIntegral();
}

function calcTotal()
{
      var cellObj = document.getElementById('goods_list');
      var cellNum = cellObj.rows.length - 1;
      var total = 0;
      for (var i = 1; i < cellNum; i++)
      {
            total += parseFloat(cellObj.rows[i].cells[4].innerHTML);
      }
      document.getElementById('total').innerHTML = total.toFixed(2);
}

function calcPrice(val)
{
      var pay_id = document.getElementById('payment').value;
      if(pay_id != 0)
      {
            changePayment(pay_id);
      }

      var trIndex = val.parentNode.parentNode.rowIndex;
      var cellObj = document.getElementById('goods_list');
      var price = cellObj.rows[trIndex].cells[2].getElementsByTagName('input');
      var num = cellObj.rows[trIndex].cells[3].getElementsByTagName('input');
      var subTotal = price[0].value * num[0].value;
      cellObj.rows[trIndex].cells[4].innerHTML = subTotal.toFixed(2);

      calcIntegral();
}

function delRow(val)
{
      var i = val.parentNode.parentNode.rowIndex;
      document.getElementById('goods_list').deleteRow(i);
      document.getElementById('discounted').innerHTML = '';
      document.getElementById('payment').value = 0;
      document.getElementById('integral').value = 0;
      reCalcPrice();
}

function submitOrder()
{
      var goodsInfo = document.getElementById('goodsForm').elements;
      if (document.getElementById('admin_name').value.length < 2)
      {
            if (confirm('该订单的客服没有选择，是否继续提交？'))
            {
            }
            else 
            {
                  return;
            }
      }
      var info = '';
      for (var x in goodsInfo)
      {
            if(goodsInfo[x].name == 'over')
            {
                  break;
            }
            else if(goodsInfo[x].name == 'secrecy' && goodsInfo[x].checked == true)
            {
                  info += goodsInfo[x].name + '::' + goodsInfo[x].value + '|';
            }
            else if(goodsInfo[x].name != 'goodslist' && goodsInfo[x].name != 'keyword' && goodsInfo[x].name != 'search' && goodsInfo[x].name != 'addToCart' && goodsInfo[x].name != 'secrecy')
            {
                  info += goodsInfo[x].name + '::' + goodsInfo[x].value + '|';
            }
      }
            Ajax.call('order.php?act=new_order', '&data=' + info, showRes, 'GET', 'TEXT');
}



function changePayment()
{
      var val = document.getElementById('payment').value;
      Ajax.call('order.php?act=pay_discount', '&pay_id=' + val, changeDiscount, 'GET', 'TEXT');
}

function changeDiscount(res)
{
      var integral = parseFloat(document.getElementById('integral').value).toFixed(2) /100;
      document.getElementById('discounted').innerHTML =  res;
      reCalcPrice();
}

function calcIntegral()
{
      changePayment();
      var integral = parseFloat(document.getElementById('integral').value).toFixed(2) /100;
      var total = document.getElementById('total').innerHTML;
      var shipping_fee = document.getElementById('shipping_fee').value;
      shipping_fee = shipping_fee ? parseFloat(shipping_fee) : 0;
      document.getElementById('total').innerHTML = parseFloat(total - integral + shipping_fee).toFixed(2);
}

//重新计算价格
function reCalcPrice()
{
      calcTotal();
      var total = parseFloat(document.getElementById('total').innerHTML).toFixed(2);
      var shipping = document.getElementById('shipping_fee').value;
      shipping = shipping ? parseFloat(shipping) : 0;
      var integral = document.getElementById('integral').value;
      integral = integral ? (parseFloat(integral) /100).toFixed(2) : 0;
      var goods_amount = document.getElementById('goods_amount').value;
      goods_amount = goods_amount ? parseFloat(goods_amount) : 0;
      var discounted = document.getElementById('discounted').innerHTML;
      discounted = discounted ? parseFloat(discounted) : 1;
      if (goods_amount) 
      {
            document.getElementById('total').innerHTML = (goods_amount * discounted + shipping -integral).toFixed(2);
      }
      else
      {
            document.getElementById('total').innerHTML = (total * discounted + shipping -integral).toFixed(2);
      }
}

// 快速添加服务记录
function fastAdd()
{
  var theForm = document.getElementById('theForm');
  var radio = theForm['service_status'];

  for(var j = 0; j < radio.length; ++j)
  {
    if(radio[j].checked)
      var temp = radio[j].value;
  }

  var data = '&service_manner='  + theForm['service_manner'].value;
  data    += '&service_class='   + theForm['service_class'].value;
  data    += '&purchase='        + theForm['purchase'].value;
  data    += '&special_feedback='+ theForm['special_feedback'].value;
  data    += '&service_time='    + theForm['service_time'].value;
  data    += '&service_status='  + temp;
  data    += '&logbook='         + theForm['logbook'].value;
  data    += '&user_id='         + theForm['user_id'].value;
  data    += '&user_name='       + theForm['user_name'].value;

  if(theForm['handler'].length>0)
  {
    data += '&handler=' + theForm['handler'].value;
  }

  var checkbox = theForm['characters[]'];
  if(checkbox)
  {
    data    += '&characters=';
    for(var i = 0; i < checkbox.length; ++i)
    {
      if(checkbox[i].checked)
      {
        data += ',' + checkbox[i].value;
      }
    }
  }
  Ajax.call('users.php?act=fast_add', data, showRes, 'GET', 'TEXT');
}

//显示添加订单和添加服务的弹出层
function showRes(res)
{
  document.getElementById('envon').innerHTML = res;
  setTimeout("EV_closeAlert()", 1600);
}


function fold () {
  var ch = document.getElementById('ch');
  if (ch.style.display == 'none') {
    ch.style.display = 'block';
  }
  else {
    ch.style.display = 'none';
  }
}

function setEffectType (effId, userId)
{
  Ajax.call('users.php', 'act=setEffect&userId='+userId+'&effId='+effId, feedback, 'POST', 'JSON');
}

function feedback (res)
{
  if (res.code == 0)
  {
    alert(res.msg);
  }

  if (res.code == 1)
  {
    document.getElementById('eff_'+res.ele).innerHTML = res.msg;
  }
}

function reqEdit(obj, userId)
{
  Ajax.call('users.php', 'act=setEffect&req=showEdit&userId='+userId, feedback, 'POST', 'TEXT');
}

function reqEdit(userId)
{

  Ajax.call('users.php', 'act=setEffect&req=showEdit&userId='+userId, feedback, 'POST', 'JSON');
}

