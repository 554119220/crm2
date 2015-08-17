
/* <!-- �˿���ϸ��Ϣ ��ǩ�л� -->*/
function switchTab (obj)
{
     var ul = document.getElementById('tab');
     var tab = ul.getElementsByTagName('input');

     for (var i in tab)
     {
          if (typeof(tab[i]) == 'object')
          {
               var temp = document.getElementById(tab[i].value)
               temp.className = 'hide-div';
               tab[i].parentNode.parentNode.className = 'f14';
          }
     }

     document.getElementById(obj.value).className = 'show-div';
     obj.parentNode.parentNode.className += ' oncheck';
}

/* ��� */
var list = '';
function listScroll (obj)
{
     obj.src = obj.id == 'gotop' ? 'images/gotop2.gif' : 'images/gobottom2.gif';
     list = setInterval(function (){scroll(obj)}, 1);
}

// �ƶ�
function scroll (obj)
{
     var showArea = document.getElementById('showArea');
     if (obj.id == 'gobottom')
          showArea.scrollTop += 1;
     else if (obj.id == 'gotop')
          showArea.scrollTop -= 1;

}

// ֹͣ�ƶ�
function stopScroll ()
{
     clearInterval(list);
}

// ����������ʾѡ���ͼƬ
function showImg (imgPath)
{
     imgPath = imgPath.replace(/2\.(\w+)$/, '1.$1');
     document.getElementById('main-img').src = imgPath;
}

/* �˿�����DIV��ǩ�л� */
document.onkeypress = function ()
{ 
     var newDiv = document.getElementById('envon');
     if (newDiv.style.display == 'block')
     {
          var tab = newDiv.getElementsByTagName('input');
          switch (event.keyCode)
          {
               case 96 :
                    for (var i in tab)
                    {
                         if (tab[i].name == 'tab' && tab[i].checked)
                         {
                              var temp = parseInt(tab[i].id.match(/\d+/));
                              if (temp == 52) temp = 49;
                              else temp += 1;
                         }
                    }
                    switchDetail(temp);
                    break;
               case 49 :
                    switchDetail(49);
                    break;
               case 50 :
                    switchDetail(50);
                    break;
               case 51 :
                    switchDetail(51);
                    break;
               case 52 :
                    switchDetail(52);
                    break;
          }
     }
};

/* ִ���л��˿�����DIV��ǩ */
function switchDetail (tabId)
{                   
     var tab = document.getElementById('envon').getElementsByTagName('input');
     for (var i in tab)
     {
          if (tab[i].name == 'tab')
          {
               tab[i].checked = false;
               tab[i].parentNode.parentNode.className = 'f14';
               document.getElementById(tab[i].value).className = 'hide-div';
          }
     }

     var showInfo = document.getElementById('tab_'+tabId);
     showInfo.checked = true;
     showInfo.parentNode.parentNode.className += ' oncheck';
     document.getElementById(showInfo.value).className = 'show-div';
}
