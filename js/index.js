/*首页的操作*/
var tpUrl = '../../thinkphp/index.php?g=home';
function getIndexInfo() {
	if (!document.getElementById('field_info')) {
		//显示加载中
		main.innerHTML = '<div style="padding-top:12px;;text-align:center;color:green">加载中，请稍等......</div>';
		Ajax.call(tpUrl, 'c=index&a=get_index_info', inMain, 'GET', 'JSON');
	} else {
		return false;
	}
}

function getIndexInfoRes(res) {
	main.innerHTML = res.main;
	init();
}

//获得库存警告
function getStockAlarm(obj) {
	changeNavStyle(obj);
	Ajax.call('storage.php', 'act=index_stock_alarm', indexNavResp, 'GET', 'JSON');
}

//查看销售统计
function getSalesStatistics(obj) {
	changeNavStyle(obj);
	Ajax.call('report_forms.php', 'act=index_nature_stat', indexNavResp, 'GET', 'JSON');
}

function indexNavResp(res) {
	document.getElementById('resource').innerHTML = res.main;
	init();
}

//改变导航样式
function changeNavStyle(obj) {
	var objHeader = obj.parentNode;
	var btnList = objHeader.getElementsByTagName('button');

	for (var i = 0; i < btnList.length; i++) {
		btnList[i].className = btnList[i].name == obj.name ? 'btn_s': 'btn_a';
	}
}

//统计的详细排行
function getMoreRanklist(showStatus, spanName) {
	var obj = document.getElementById(spanName);
	obj.style.display = showStatus;
	obj.style.top = event.clientY - 160 + 'px';
	if (event.clientX - 100 > 0) {
		obj.style.left = event.clientX - 180 + 'px';
	}
}

//按时期查询统计情况
function getStatistics(date_status) {
	Ajax.call('index.php', 'act=' + '' + '&date_status=' + date_status + '&from_sel=' + true, getSalesStatisticsResp, 'GET', 'JSON');
}

function getSalesStatisticsResp(res) {
	document.getElementById('nature_stats_index').innerHTML = res;
}

//获取首页警报库存
function getIndexAlarm(page, recorder_size) {
	if (page <= 0) {
		page = 1;
	}
	Ajax.call('index.php?act=index_stock_alarm', 'page=' + page + '&recorder=' + recorder_size, getIndexAlarmResp, 'GET', 'JSON');
}

function getIndexAlarmResp(res) {
	document.getElementById('index_stock_alarm').innerHTML = res;
}

//发布公告
function issueNotice(obj) {
	if ((event.keyCode == 13) && event.ctrlKey) {
		if (obj.value != '' && title != '') {
			var title = document.getElementById('notice_title').value;
			var selectedId = document.getElementById('notice_type').selectedIndex;
			//var notice_type = document.getElementById('notice_type').options[selectedId].text;
			var notice_type = document.getElementById('notice_type').options[selectedId].value;
			var weight = document.getElementById('notice_type').options[selectedId].value;
			var remind_time = '&remind_time=' + obj.form.elements['remind_time'].value;
			var data = [];
			var i = 0;
			if (document.getElementById('platform')) {
				data[i++] = 'platform=' + document.getElementById('platform').value;
			}
			if (document.getElementById('group_id')) {
				data[i++] = 'group_id=' + document.getElementById('group_id').value;
			}
			if (document.getElementById('admin_id')) {
				data[i++] = 'admin_id=' + document.getElementById('admin_id').value;
			}

			data.push('&notice_type=' + notice_type, '&title=' + title, '&notice=' + obj.value, '&weight=' + weight, remind_time);

			Ajax.call('system.php?act=issue_notice', data.join('&'), showMsg, 'POST', 'JSON');
		} else return;
	} else {
		return;
	}
}

//获取公告内容
function getNotice(notice_id) {
	Ajax.call('system.php?act=get_notice', 'notice_id=' + notice_id, showMsg, 'GET', 'JSON');
}

//获得排行榜更多信息【左边框】
function getMoreList(obj, ranklist_name_en, ranklist_name_zh) {

	if (document.getElementById('more_ranklist')) {
		var moreRanklistDiv = document.getElementById('more_ranklist');
		obj.innerHTML = 'more';
		if (document.getElementById('hide_left_input').value == ranklist_name_en) {
			document.body.removeChild(moreRanklistDiv);
		} else {
			document.getElementById('lab_' + document.getElementById('hide_left_input').value).innerText = 'more';
			document.body.removeChild(moreRanklistDiv);
			createLeftDiv(obj, ranklist_name_en, ranklist_name_zh);
		}
	} else {
		createLeftDiv(obj, ranklist_name_en, ranklist_name_zh);
	}
}

function createLeftDiv(obj, ranklist_name_en, ranklist_name_zh) {
	obj.innerHTML = 'less';
	var moreRanklistDiv = document.createElement('div'); //装载排行榜的div;
	moreRanklistDiv.id = 'more_ranklist';
	moreRanklistDiv.className = 'hide_site_div';

	var moreRanklistTitle = document.createElement('div');
	moreRanklistTitle.className = 'ranklist_div_title';
	moreRanklistTitle.innerHTML = '更多' + ranklist_name_zh + '<label onclick="javascript:document.body.removeChild(this.parentNode.parentNode)">X</label>';
	moreRanklistTitle.style.textAlign = 'center';
	moreRanklistTitle.style.height = '28px';

	moreRanklistDiv.appendChild(moreRanklistTitle);
	var hide_input = document.createElement('input');
	hide_input.id = 'hide_left_input';
	hide_input.type = 'hidden';
	hide_input.value = ranklist_name_en;
	moreRanklistDiv.appendChild(hide_input);

	moreRanklistDiv.innerHTML += '<table cellpadding=0 cellspacing=0 width=100% id=left_hide_table></table>';
	document.body.appendChild(moreRanklistDiv);

	var tableObj = document.getElementById(ranklist_name_en);
	var leftHideTable = document.getElementById('left_hide_table');
	leftHideTable.innerHTML = tableObj.innerHTML;
	for (var i = 0; i < leftHideTable.rows.length; i++) {
		if (leftHideTable.rows[i].className == 'hide') {
			leftHideTable.rows[i].className = '';
			leftHideTable.rows[i].style.display = '';
		}
	}

	moreRanklistDiv.action = setInterval(function() {
		if (moreRanklistDiv.offsetWidth == 190 || moreRanklistDiv.offsetWidth > 190) {
			clearInterval(moreRanklistDiv.action);
		} else {
			moreRanklistDiv.style.width = moreRanklistDiv.offsetWidth + 10 + 'px';
		}
	},
	10);
}

//近一个月顾客记念日
function getCommemoration(page, recorder_size) {
	page = page <= 0 ? 1: page;
	Ajax.call('index.php?act=get_commemoration', 'page=' + page + '&recorder_size=' + recorder_size, getCommemorationResp, 'GET', 'JSON');
}

function getCommemorationResp(res) {
	document.getElementById('index_commemoration').innerHTML = res.main;
	init();
}

//function popMoreRankList(obj,ranklist_name_en,ranklist_name_zh){
//  var tableObj = document.getElementById(ranklist_name_en);
//  if(tableObj.rows.length){
//    var recoderCount = tableObj.rows.length;
//    var colsLength = Math.ceil(recoderCount/2);
//    var rowsLength = colsLength > 1 ? 2 : tableObj.rows.length;
//    var ranklistDiv = document.createElement('div');
//    var msgDiv = document.getElementById('msg');
//    ranklistDiv.innerHTML = '<table cellpadding=0 cellspacing=0 width=100% id=pop_hide_table></table>';
//    msgDiv.appendChild(ranklistDiv);
//    var msg = [];
//    msg.btncontent = false; 
//    msg.title = ranklist_name_zh;
//    msg.message = ranklistDiv.innerHTML;
//    var popHideTable = document.getElementById('pop_hide_table');
//    [>创建行和列<]
//    var temp_col,temp_row;
//    for(var i = 0; i < rowsLength; i++){
//      temp_row = popHideTable.insertRow(i);
//      for(var j = 0; j < colsLength; j++){
//        temp_col = temp_row.insertCell(j);
//        temp_col.id = 'td_'+ (j*10+i);
//        temp_col.style.textAlign = 'left';
//        temp_col.innerHTML = (j*10+1) +temp_col.id;
//      }
//    }
//    showMsg(msg);
//    var preRanklistRow = tableObj.rows;
//    for(var i=0; i < preRanklistRow.length; i++){
//      document.getElementById('td_'+i).innerHTML = '<em>' + preRanklistRow[i].cells[0] + '</em>' +'<span>' + preRanklistRow[i].cells[1] + '</span>' + '<b>' + preRanklistRow[i].cells[2] + '</b>';
//    }
//  }
//}
//获得排行榜更多信息【弹窗】
function popMoreRanklist(ranklist_name_en, ranklist_name_zh) {
	var dateStatus = document.getElementById('date_status').value;
	var objTable = document.getElementById(ranklist_name_en);

	if (objTable.rows.length > 1) {
		Ajax.call('index.php?act=get_more_ranklist', 'ranklist_name_en=' + ranklist_name_en + '&ranklist_name_zh=' + ranklist_name_zh + '&date_status=' + dateStatus, popMoreRankListRes, 'GET', 'JSON');
	}
}

function popMoreRankListRes(res) {
	document.getElementById('msg').style.width = res.off_width;
	showMsg(res);
}

//本月推广活动
function getActivityList(page, recoder_size) {
	page = page <= 0 ? 1: page;
	Ajax.call('index.php?act=get_spread_activity', 'page=' + page + '&recorder_size=' + recoder_size, getSpreadActivityResp, 'GET', 'JSON');
}

function getSpreadActivityResp(res) {
	document.getElementById('spread_activity').innerHTML = res.main;
	init();
}

//销量警报
function getSaleAlarm(page) {
	if (page <= 0) {
		page = 1;
	}

	Ajax.call('index.php?act=get_sale_alarm', 'page=' + page, getSaleAlarmResp, 'GET', 'JSON');
}

function getSaleAlarmResp(res) {
	document.getElementById(res.div_id).innerHTML = res.main;
}

/*公共函数*/

/*底部提醒*/
function showBottom(res) {
	if (res.blacklist_ctr) {
		showMsg(res);
	} else if (res.service.length > 0) {
		var bottomDiv = document.getElementById('bottom_remind');
		var remindContent = document.getElementById('remind_content');
		bottomDiv.style.bottom = '0px';
		for (var i = 0; i < res.service.length; i++) {
			remindContent.innerHTML += res.service[i];
		}
	}
	return;
}

/*关闭底部提醒*/
function closeRemind() {
	var bottomDiv = document.getElementById('bottom_remind');
	document.getElementById('remind_content').innerHTML = '';
	bottomDiv.style.bottom = '-152px';
}

/*下一行表单*/
function trForm(tableObj, res) {
	var trObj = tableObj.insertRow(res.tr_index);
	trObj.innerHTML = res.main;
}

/*删除下一行显示*/
function delTrform(obj) {
	var trObj = obj.form.parentNode.parentNode.parentNode;
	trObj.parentNode.deleteRow(trObj.rowIndex);
}

/*弹窗表单 （共用消息弹出框）*/
function popupForm(res) {
	var msgDiv = document.getElementById('msg');
	var title = msgDiv.getElementsByTagName('h3');
	title.innerHTML = res.title;
	showMsg(res);
}

/*刷新首页任务*/
function refreshTask() {
	var obj = document.forms['index_task_form'];
	var dataObj = obj.elements['tasks_date'];

	for (var i = 0; i < dataObj.length; i++) {
		if (dataObj[i].checked == true) {
			var dataStatus = dataObj[i].value;
		}
	}

	Ajax.call('performance.php?act=refresh_task', 'data_status=' + dataStatus, refreshTaskResp, 'GET', 'JSON');
}

function refreshTaskResp(res) {
	var taskProgress = document.getElementById('task_progress_view');
	taskProgress.innerHTML = res.main;
}

function getMsgNotice(){
	Ajax.call('system.php?act=get_msg_notice','',getMsgNoticeRes,'GET','JSON');
}

//30秒查询一次
function getMsgNoticeRes(res){
  if (res) {
    var ulObj = document.createElement('ul');
    var liHtml = '';
    for (var i = 0; i<res.length; i++) {
      if (localStorage.getItem('notice'+res['msg'][i].notice_id) || i == 'toJSONString') {
        continue;
      }else{
        var liObj       = document.createElement('li');
        liObj.innerHTML = res['msg'][i].content;
        liHtml += '<li>'+res['msg'][i].content+'</li>';
        ulObj.appendChild(liObj);  
        localStorage.setItem('notice'+res['msg'][i].notice_id,res['msg'][i].notice_id); //只提示一次
	break;
      }
    }
    if (localStorage.getItem('notice_type') == 0 || localStorage.getItem('notice_type') == null) {
      if (ulObj.getElementsByTagName('li').length > 0) {
        var fdisplay = document.getElementById('fade').style.display;
        var mdisplay = document.getElementById('msg').className;
        if ('block' != fdisplay && 'block' != mdisplay) {
          var msg = [];
          msg['message'] = '<img class="alarm" src="images/laba.gif"/><ul>'+liHtml+'</ul>';
          msg['title'] = res.remind_title;
          msg['btncontent'] = '我要加油!'; 
          msg['no_cancel'] = false;
          showMsg(msg);
        }else{ document.getElementById('msgBtn').style.display='none';}
      }
    }else{
      var contentDiv = $("#remind_content");
      contentDiv.html('');
      if (ulObj.getElementsByTagName('li').length > 0) {
        $("#bottom_remind").css('display','block');
        $("#bottom_remind").css('bottom','0px');
        $("#remind_title").html(res.remind_title);
        contentDiv.append(ulObj);
      }else return;
    }
  }
  //30分钟更新一次
  setTimeout(getMsgNotice,1800000); 
}

//显示未接电话
function showMissCall(){
  var obj = document.getElementById('miss_call_list');
  if (obj.innerHTML == '') {
    return ;
  }else{
    obj.style.display = obj.style.display == 'none' ? '' : 'none';
  }
}

function getMissCall(){
  Ajax.call('service.php?act=miss_call','',getMissCallResp,'GET','JSON');
}
//有未接电话
function getMissCallResp(res){
  if (res) {
    document.getElementById('miss_call').innerHTML =  res.length;
    document.getElementById('miss_call').style.color = 'red';
    document.getElementById('miss_call_list').innerHTML =  '';
    var html = '<ul>';
    for(var i in res){
      html += '<li> '+res[i]+'</li>';
    }
    html += '</ul>'
      document.getElementById('miss_call_list').innerHTML =  html;
  }
  setTimeout(getMissCall,3800000); 
}
