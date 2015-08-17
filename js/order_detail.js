/**
 * 搜索商品
 */
function inven_searchGoods(obj)
{
     var keyword = obj.value;
     if (/[\u4E00-\u9FA5]{2}/.test(keyword))
     {
          Ajax.call('order.php?act=search_goods&keyword='+keyword, '', inven_searchGoodsResponse, 'GET', 'JSON')
     }
}

function inven_searchGoodsResponse (res)
{
     if (res.info == 'searchGoods')
     {
       inven_showGoodsOption(res);
     }
}

/**
 * 在select中显示商品选项
 */
function inven_showGoodsOption (goodsOpt)
{
  var obj = document.getElementById('search_goods');
  obj.length = 0;
  var goods = goodsOpt.main;
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
 * 删除单元行
 */
function removeRow (id)
{
    var rowObj = document.getElementById(id);
    if (typeof(rowObj) == 'object' && rowObj)
    {
        rowObj.parentNode.deleteRow(rowObj.rowIndex);
    }
}

/**
 * 添加新商品
 */
function inven_addNewGoods()
{
  // 表单对象
  var theForm     = document.forms['inventory_form'];
  var msg = new Array();
  msg['timeout'] = 2000;

  // 商品信息
  var number   = theForm.elements['number'].value;
  var goodsObj = theForm.elements['search_goods'];
  var id       = goodsObj.value;
  var isGift   = theForm.elements['is_gift'].checked ? 1 : 0;
  var price    = isGift ? 0 : theForm.elements['price'].value;
  var inven_money = parseFloat(document.getElementById('inven_money').innerHTML); //存货余额
  var user_money = parseFloat(document.getElementById('user_money').innerHTML);  //会员充值额
  var record_user_money = parseFloat(document.getElementById('record_user_money').value);

  var amount = price*number; //商品总价
  var remain_money = inven_money - amount;

  for (var i = 0; i < goodsObj.length; i++)
  {
    if (goodsObj.options[i].selected)
    {
      var name = goodsObj.options[i].text;
    }
  }

  if(remain_money < 0 && (user_money += remain_money)>0)
  {
    if(!record_user_money)
    {
      if(confirm('确认使用充值余额'+Math.abs(remain_money)+'元'))
      {
        document.getElementById('user_money').innerHTML = user_money;
        //记录使用的充值余额
        document.getElementById('record_user_money').value = Math.abs(remain_money);
        //记录使用的存货余额
        document.getElementById('record_inven_money').value = inven_money;
        remain_money = 0;
      }
    }
  }

  if(isGift || remain_money >= 0 )
  {

    var goodsList = document.getElementById('inven_goods_list'); 
    var rowObj = goodsList.insertRow(goodsList.rows.length);
    document.getElementById('inven_money').innerHTML = remain_money;

    // 商品ID
    var goodsId = rowObj.insertCell(0);
    goodsId.innerHTML = '<input type="checkbox" name="list_id[]" value="'+id+'" id="goods_id_'+id+'"/><strong onclick="inven_removeGoods(this)">删除</strong>';

    // 商品名称
    var goodsName = rowObj.insertCell(1);
    goodsName.setAttribute('colspan',5);
    goodsName.innerHTML = name+'<input type="hidden" name="list_name[]" value="'+name+'"/>';


    // 商品价格
    var goodsPrice = rowObj.insertCell(2);
    goodsPrice.innerHTML = price+'<input type="hidden" name="list_price[]" value="'+price+'"/>';

    // 商品数量
    var goodsNumber = rowObj.insertCell(3);
    goodsNumber.innerHTML = number+'<input type="hidden" name="list_number[]" value="'+number+'"/>';

    // 赠品
    var gift = rowObj.insertCell(4);
    gift.innerHTML = '<img src="images/'+isGift+'.gif" alt="赠品" onclick="alert('+isGift+')"/><input type="hidden" name="list_gift[]" value="'+isGift+'"/>';


    // 单个商品总价
    var singleAmount = rowObj.insertCell(5);
    singleAmount.innerHTML = !isGift ? amount : '赠品';

       // 计算订单总金额
       inven_calcOrderAmount();
     }
     else
     {
       msg['message'] = '没有足够存货或可用余额';
       showMsg(msg);
       return;
     }
}

/**
 * 删除一个商品
 */
function inven_removeGoods(obj)
{
  var rowObj = obj.parentNode.parentNode;       // 行DOM
  var tblObj = rowObj.parentNode;
  var record_user_money =  parseFloat(document.getElementById('record_user_money').value); //使用的充值余额
  var record_inven_money = parseFloat(document.getElementById('record_inven_money').value); //使用的存货余额

  if(rowObj.rowIndex == tblObj.rows.length-1)
  {
    if(record_user_money != 0)
    {
      document.getElementById('user_money').innerHTML = parseFloat(document.getElementById('user_money').innerText) + record_user_money;
      document.getElementById('record_user_money').value = 0;
    }
    else if( record_inven_money != 0)
    {
      document.getElementById('inven_money').innerHTML = parseFloat(document.getElementById('inven_money').innerText) + record_inven_money;
    }
    else
    {
      document.getElementById('inven_money').innerText = parseFloat(document.getElementById('inven_money').innerText )
        + parseFloat(rowObj.cells[5].innerText);
    }
  }

  tblObj.deleteRow(rowObj.rowIndex); // 删除行
  inven_calcOrderAmount();  // 重新计算金额
}

/**
 * 计算订单费用
 */
function inven_calcOrderAmount ()
{
  var goodsAmount = 0; // 商品总金额
  var shippingFee = parseFloat(document.getElementById('inven_shipping_fee').value); // 运费
  var isPackage = 0;

  var tableObj = document.getElementById('inven_goods_list');
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

  document.getElementById('inven_goods_amount').value = goodsAmount;
  document.getElementById('inven_order_amount').innerHTML = parseFloat(goodsAmount)+parseFloat(shippingFee) + '元';
}

//添加订单
function inven_addNewOrder()
{
  var goodsList = document.forms['inventory_goods_list'];
  var tableObj = document.getElementById('inven_goods_list');
  var id = document.getElementById('ID').value;

  var goods = {};
  var data = {
    "user_id":id,
  }

  for (var i = 2; i < tableObj.rows.length; i++)
  {
    if (tableObj.rows.length > 3)
    {
      goods[i-2] = {
        "goods_sn"     : goodsList.elements['list_id[]'][i-2].value,
        "goods_price"  : goodsList.elements['list_price[]'][i-2].value,
        "goods_number" : goodsList.elements['list_number[]'][i-2].value,
        "is_gift"      : goodsList.elements['list_gift[]'][i-2].value,
      };
    }
    else
    {
      goods[i-2] = {
        "goods_sn"     : goodsList.elements['list_id[]'].value,
        "goods_price"  : goodsList.elements['list_price[]'].value,
        "goods_number" : goodsList.elements['list_number[]'].value,
        "is_gift"      : goodsList.elements['list_gift[]'].value,
      };
    }
  }

  data['shipping_fee'] = goodsList.elements['inven_shipping_fee'].value;
  data['goods_amount'] = goodsList.elements['inven_goods_amount'].value; 
  data['order_amount'] = parseFloat(document.getElementById('inven_order_amount').innerHTML); 
  data['goods_list'] = goods;

  Ajax.call('order.php?act=add_inven_order', data, addNewOrderResponse, 'POST', 'JSON');
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

//获取选择要提货商品的数量和单价
function getInvenAmount(goods_id)
{
  var user_id = document.getElementById('ID').value;
  alert(user_id);

  Ajax.call('order.php?act=get_inven_amount','user_id='+user_id+'&goods_id='+goods_id,getInvenRes,'GET','JSON'); 
}

function getInvenRes(res)
{
  if(res.code == 1)
  {
    var obj = document.forms['inventory_form'];
    obj.elements['number'].value = res.number;
    obj.elements['price'].value = res.goods_price;
  }
}
