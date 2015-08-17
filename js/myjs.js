function key (val)
{
      if(val.length>0)
      {
            addWord();
      }
      else
      {
            alert('请输入疾病名称！');
      }
}
function addWord()
{
      var disease = document.getElementById('disease').value;
      if(disease.length > 0)
      {
            Ajax.call('disease.php?act=add', 'disease='+disease, showRes, 'GET', 'TEXT');
      }
      else
      {
            alert('请输入疾病名称！');
      }
}

function showRes(res)
{
      var ob = parseObjectToJSON(res);
      var show = document.getElementById('showdisease');
      show.innerHTML += '<span id='+ob.disease_id+'>'+ob.disease+'<a href=# onclick=delWord("'+ob.disease_id+'"); >[删除]</a></span>';

}

function delWord (id)
{
      Ajax.call('disease.php?act=del', 'id='+id, showDelRes, 'GET', 'TEXT');
}

function showDelRes (res)
{
      var del = document.getElementById(res);
      del.innerHTML = '';
}
