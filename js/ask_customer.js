//淘顾客的相关操作
function askControl(user_id,action)
{
  Ajax.call('users.php?act=control_ask&','action='+action+'&user_id='+user_id,askResponse,'GET','JSON');
}

function askResponse(res)
{
  showMsg(res.msg);
  //删除已经选择的行
  objTr = document.getElementById(res.user_id);
  objTable = objTr.parentNode;
  objTable.deleteRow(objTr.rowIndex);
}

//获取顾客的基本信息
function getUserInfo()
{

}

//基本设置
function askConfig()
{
  var ser_time = document.getElementById('serviceTime').value;
  var pur_time = document.getElementById('purchaseTime').value;

  Ajax.call('users.php?act=ask_config&','ser_time='+ser_time+'&pur_time='+pur_time,configResponse,'GET','JSON');
}

function configResponse(res)
{
  showMsg(res);
}

// 通过部门筛选
function roleCustomer(role_id) {
  var title  = document.getElementById('title').value;

  Ajax.call('users.php?act=role_customer&','role_id='+role_id+'&title='+title,Response,'GET','JSON'); 
}

function Response(res)
{
  document.getElementById('ask_content').innerHTML = res; 
}

//选择列表
function changePanel(index)
{
  var url = 'users.php?';
  var act = "";
  switch(index)
  {
    case '1' :
      act='ask_customer_list';          //可陶顾客
      break;
    case '2' :
      act='asked_customer_list';        //已陶顾客
      break;
    case '3' :
      act='asked_history';              //陶客历史
      break;
    case '4' : 
      act='exchange_his';               //顾客流向
      break;
    case '5' :
      act='ban_ask_list';               //禁止顾客
      break;
  }
  Ajax.call(url,'act='+act,chResponse,'GET','JSON');
}


//AJAX 的选项卡切换
function sqlTab(obj,index)
{
  var ul = obj.parentNode;
  for (var i in ul.children)
  {
    if (i != 'length')
    {
      ul.children[i].className = '';
    }
  }

  obj.className = 'o_select';

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

  var url = 'users.php?';
  var act = "";
  switch(index)
  {
    case 1 :
      act='ask_customer_list';        //可淘顾客
      break;
    case 2 :
      act='asked_customer_list';      //已陶顾客
      break;
    case 3 :
      act='asked_history';            //陶顾客历史
      break;
    case 4 : 
      act='exchange_his';             //顾客流向
      break;
    case 5 :
      act='ban_ask_list';             //禁止顾客
      break;
  }

  Ajax.call(url,'act='+act+'&times='+1,chResponse,'GET','JSON');
}

function chResponse(res)
{
  document.getElementById('ask_content').innerHTML = res;
  init();
}

//标签切换
function tabSub(obj)
{
  var ul = (obj.parentNode).parentNode;
  var buttons = ul.getElementsByTagName('button');
  var box = document.getElementById('box');
  var divs = box.getElementsByTagName('div');

  for(var i=0; i<buttons.length;i++)
  {
    buttons[i].className = 'btn_a';
  }
  obj.className = 'btn_s';

  for(var i=0; i<divs.length;i++)
  {
    if(divs[i].id == obj.name)
    {
      divs[i].style.display='block';
    }
    else
    {
      divs[i].style.display='none';
    }
  }
}
