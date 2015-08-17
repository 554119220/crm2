/**
 * 提交健康档案基本信息
 */

var existFile = 0;
var psyAnswer = new Array();

function subHealthy() {
	//验证客户是否已经拥有健康档案
	if (existFile > 0) {
		var msg = new Array();

		msg['message'] = '无需重复添加';
		msg['timeout'] = 2000;

		showMsg(msg);
	}
	else {
		var user_id          = document.getElementById('ID').value;
		var obj              = document.forms['for_healthy'];
		var user_name        = obj.elements['user_name'].value;
		var born_address     = obj.elements['born_address'].value;
		var work_address     = '';
		var marry            = obj.elements['marry'].value;
		var recheck          = obj.elements['recheck'].value;
		var cycle_check      = obj.elements['cycle_check'].value;
		var job_type         = obj.elements['job_type'].value;
		var worktime         = obj.elements['worktime'].value;
		var travel           = obj.elements['travel'];
		var travel_condition = "";

		for (var i = 0; i < travel.length; i++) {
			if (travel[i].checked == true) {
				travel_condition += travel[i].value + ' ';
			}
		}

		travel                   = travel_condition.substr(0, travel_condition.length - 1);
		var enviroment           = obj.elements['enviroment'];
		var enviroment_condition = "";

		for (var i = 0; i < enviroment.length; i++) {
			if (enviroment[i].checked == true) {
				enviroment_condition += enviroment[i].value + ' ';
			}
		}

		enviroment          = enviroment_condition.substr(0, enviroment_condition.length - 1);
		var healthy_element = obj.elements['healthy_element'].value;
		var blood_pressure  = obj.elements['blood_pressure'].value;
		var blood_fat       = obj.elements['blood_fat'].value;
		var blood_sugar     = obj.elements['blood_sugar'].value;
		var height          = obj.elements['height'].value;
		var weight          = obj.elements['weight'].value;
		var BMI             = obj.elements['BMI'].value;
		var waistline       = obj.elements['waistline'].value;
		var hipline         = obj.elements['hipline'].value;
		var WHR             = obj.elements['WHR'].value;
		var blood_type      = obj.elements['blood'].value;

		Ajax.call('service.php?act=upload_healthy&', 'user_id=' + user_id + '&user_name=' + user_name + '&born_address=' + born_address + '&work_address=' + work_address + '&marry=' + marry + '&recheck=' + recheck + '&cycle_check=' + cycle_check + '&job_type=' + job_type + '&worktime=' + worktime + '&travel=' + travel + '&enviroment=' + enviroment + '&healthy_element' + healthy_element + '&blood_pressure=' + blood_pressure + '&blood_fat=' + blood_fat + '&blood_sugar=' + blood_sugar + '&height=' + height + '&weight=' + weight + '&BMI=' + BMI + '&waistline=' + waistline + '&hipline=' + hipline + '&WHR=' + WHR + '&blood_type=' + blood_type, subHealthyResponse, 'GET', 'TEXT');
	}
}

//添加健康档案回调
function subHealthyResponse(res) {
	promptMsg(res);
	document.getElementById('added').value = 0;
	if (res == 1) returnUserinfo();
}

/*
 * 过敏史
 */
function subAllergy() {
	var user_id = document.getElementById('ID').value;
	var obj = document.forms['for_allergy'];
	var allergy = obj.elements['allergy'].value;
	var reason = obj.elements['reason'].value;
	var added = document.getElementById('added').value;

	if (added != 0) {
		promptMsg(2);
		return false;
	}
	else {
		Ajax.call('service.php?act=load_allergy&', 'user_id=' + user_id + '&allergy=' + allergy + '&reason=' + reason, subAllergyResponse, 'GET', 'TEXT');
	}
}
//过敏史回调
function subAllergyResponse(res) {
	promptMsg(res);
}

/**
 * 生活习惯 
 */
function subLifestyle() {
	var user_id = document.getElementById('ID').value;
	//饮食习惯
	var obj = document.forms['for_lifestyle'];
	var food_taste = obj.elements['food_taste'];
	var strfood_taste = "";
	for (var i = 0; i < food_taste.length; i++) {
		if (food_taste[i].checked == true) {
			strfood_taste += food_taste[i].value + ' ';
		}
	}
	food_taste = strfood_taste.substr(0, strfood_taste.length - 1);
	var fixed_dinner = obj.elements['fixed_dinner'].value;
	var mealtime = obj.elements['mealtime'].value;

	//睡眠习惯 
	var sleep_habit = obj.elements['sleep_habit'].value;
	var bedtime_start = obj.elements['bedtime_start'].value;
	var sleep_quality = obj.elements['sleep_quality'].value;
	var bedtime = obj.elements['bedtime'].value;

	//运动习惯
	var sport_times = obj.elements['sport_times'].value;
	var sport_time = obj.elements['sport_time'].value;
	var sport_period = obj.elements['sport_period'].value;
	var sport_type = obj.elements['sport_type'];
	var strsport_type = "";
	for (var i = 0; i < sport_type.length; i++) {
		if (sport_type[i].checked == true) {
			strsport_type += sport_type[i].value + ' ';
		}
	}
	sport_type = strsport_type.substr(0, strsport_type.length - 1);

	//吸烟习惯
	var smoke = obj.elements['smoke'].value;
	var smoke_age = obj.elements['smoke_age'].value;
	var passive_smoke = obj.elements['passive_smoke'].value;
	var smoke_number = obj.elements['smoke_numbers'].value;

	//喝酒习惯
	var drink = obj.elements['drink'].value;
	var drink_type = obj.elements['drink_type'];
	var strdrink_type = "";
	for (var i = 0; i < drink_type.length; i++) {
		if (drink_type[i].checked == true) {
			strdrink_type += drink_type[i].value + ' ';
		}
	}
	drink_type = strdrink_type.substr(0, strdrink_type.length - 1);

	var drink_capacity = obj.elements['drink_capacity'].value;
	var added = document.getElementById('added').value;

	if (added != 0) {
		promptMsg(2);
		return false;
	}
	else {
		Ajax.call('service.php?act=upload_lifestyle&', 'user_id=' + user_id + '&food_taste=' + food_taste + '&fixed_dinner=' + fixed_dinner + '&mealtime=' + mealtime + '&sleep_habit=' + sleep_habit + '&bedtime_start=' + bedtime_start + '&sleep_quality=' + sleep_quality + '&bedtime=' + bedtime + '&sport_times=' + sport_times + '&sport_time=' + sport_time + '&sport_period=' + sport_period + '&sport_type=' + sport_type + '&smoke=' + smoke + '&smoke_number=' + smoke_number + '&smoke_age=' + smoke_age + '&passive_smoke=' + passive_smoke + '&drink=' + drink + '&drink_type=' + drink_type + '&drink_capacity=' + drink_capacity, lifestyleResponse, 'GET', 'TEXT');
	}
}

//生活习惯
function lifestyleResponse(res) {
	promptMsg(res);
}

/*
 * 家族病例
 */
function subFamilyDisease() {
	var obj = document.forms['for_family_case'];
	var user_id = document.getElementById('ID').value;
	var family_disease = obj.elements['family_disease'];
	var strfamily_diease = "";

	for (var i = 0; i < family_disease.length; i++) {
		if (family_disease[i].checked == true) {
			strfamily_diease += family_disease[i].value + ' ';
		}
	}
	family_disease = strfamily_diease.substr(0, strfamily_diease.length - 1);
	var tumour = obj.elements['tumour'].value;

	var added = document.getElementById('added').value;

	if (added != 0) {
		promptMsg(2);
		return false;
	}
	else {
		Ajax.call('service.php?act=family_disease&', 'user_id=' + user_id + '&family_disease=' + family_disease + '&tumour=' + tumour, subFDResponse, 'GET', 'JSON');
	}
}

// 家族病例
function subFDResponse(res) {
	promptMsg(res);
}

/**
 * 既往病例
 */
function subBeforeCase() {
	var user_id = document.getElementById('ID').value;
	var obj = document.forms['for_before_acse'];
	var before_disease = obj.elements['before_disease'];
	var strdisease = "";
	for (var i = 0; i < before_disease.length; i++) {
		if (before_disease[i].checked) {
			strdisease += before_disease[i].value + ' ';
		}
	}
	before_disease = strdisease.substr(0, strdisease.length - 1);

	var added = document.getElementById('added').value;

	if (added != 0) {
		promptMsg(2);
		return false;
	}
	else {
		Ajax.call('service.php?act=before_case&', 'user_id=' + user_id + '&before_disease=' + before_disease, befResponse, 'GET', 'TEXT');
	}
}

function befResponse(res) {
	promptMsg(res);
}

/*
 * 心理
 */
function up_answer(index, question) {
	psyAnswer[index] = question;
}

function subPsychology() //提交心理信息
{
	var user_id = document.getElementById('ID').value;
	var box = document.getElementById('psychology');
	var objInput = box.getElementsByTagName("input");
	var psychology, strPsychology = "";
	var j = 0;

	for (var i = 0; i < objInput.length; i++) {
		if (objInput[i].type == 'radio' && objInput[i].checked == true) {
			strPsychology += objInput[i].value + ' ';
			j++;
		}
	}
	psychology = strPsychology.substr(0, strPsychology.length - 1);

	if (j < 6) {
		var msg = new Array();

		msg['timeout'] = 2000;
		msg['message'] = '问题未回答完全';
		showMsg(msg);
	}
	else {
		var added = document.getElementById('added').value;

		if (added != 0) {
			promptMsg(2);
			return false;
		}
		else {
			Ajax.call('service.php?act=psychology&', 'user_id=' + user_id + '&psychology=' + psychology, psychologyResponse, 'GET', 'TEXT');
		}
	}
}

//心理信息回调
function psychologyResponse(res) {
	promptMsg(res);
}

/**
 * 补充信息
 */
function subOther() {
	var user_id = document.getElementById('ID').value;
	var other = document.getElementById('txt_other').value;

	var added = document.getElementById('added').value;

	if (added != 0) {
		promptMsg(2);
		return false;
	}
	else {
		Ajax.call('service.php?act=subother&', 'user_id=' + user_id + '&other=' + other, subOtherResponse, 'GET', 'TEXT');
	}
}

//补充信息回调
function subOtherResponse(res) {
	promptMsg(res);
}

/**
 * 返回匹配顾客
 */
function getUsers(val) {
	switch (val) {
	case 0:
		var user_name = document.getElementById('txt_user_name').value;
		Ajax.call('service.php?act=get_match_user&', 'user_name=' + user_name, getUsersResponse, 'GET', 'JSON');
		break;
	case 1:
		var obj = document.getElementById('l_user_name');
		var user_id = obj.value;
		document.getElementById('ID').value = obj.value;
		document.getElementById('txt_user_name').value = obj.options[obj.selectedIndex].innerText;
		//Ajax.call('users.php?act=user_detail&','user_id='+user_id,healthyInfo,'GET','JSON');
	}
}

//回调
function getUsersResponse(res) {
	res = eval(res);
	var len = res.length;
	var objSelect = document.getElementById('l_user_name');
	var valItem;
	if (len > 0) {
		objSelect.innerHTML = '';
		for (var i = 0; i < len; i++) {
			valItem = new Option(res[i]['user_name'], res[i]['user_id']);
			objSelect.options.add(valItem);
		}
		document.getElementById('user_list').style.display = 'block';

		document.getElementById('txt_user_name').value = objSelect.options[0].text;
	}
}

//修改健康档案回调
function healthyInfo(res) {
	alert(res);
}

//返回用于确认客户的其他信息
function getUserOther(val) {
	var user_id = val;
	Ajax.call('service.php?act=isExistHFile&', 'user_id=' + user_id, Response, 'GET', 'JSON');
}

//验证是否已经存在档案回调
function Response(res) {
	existFile = res[0]['count'];
	if (existFile > 0) //已经存在
	{
		var objTable = document.getElementById('table_info');
		var len = objTable.rows.length;
		var msg = new Array();

		msg['message'] = '无需重复添加';
		msg['timeout'] = 2000;

		showMsg(msg);
		if (len > 5) {
			objTable.deleteRow(1);
		}

		document.getElementById('txt_user_name').value = "";
	}
	else {
		//显示顾客其他的基本信息
		var objTable = document.getElementById('table_info');
		var len = objTable.rows.length;
		if (len > 5) {
			objTable.deleteRow(1);
		}
		var newRow = objTable.insertRow(1);
		var sex = (res[0]['sex'] == 0) ? '男': '女';
		newRow.bgColor = "#F1F1F1";
		newRow.innerHTML = '<th width="130">性别 ：</th>' + '<td>' + sex + '</td>' + '<th>地址 ：</th>' + '<td>' + res[0]['address'] + '</td>';
		alert(res[0]['user_name']);
		document.getElementById('txt_user_name').value = res[0]['user_name'];
	}
}

//修改体重信息
function modifyWeight() {
	var user_id = document.getElementById('ID').value;
	var obj_form = document.forms['form_add_test'];
	var inputs = obj_form.getElementsByTagName('input');
	var admin_remark = document.getElementById('admin_remark').innerText;

	var key = '';
	var healthy_info = new Array();
	var healthy_info_file = {};
	var error = false;

	for (var i = 0; i < inputs.length; i++) {
		key = (inputs[i].id).substr(4, inputs[i].id.length);
		healthy_info_file[key] = inputs[i].value;
		if (inputs[i].value == '') error = true;
	}

	healthy_info_file['user_id'] = user_id;
	healthy_info_file['admin_remark'] = admin_remark;

	if (!error) {
		Ajax.call('family_manager.php?act=modify_weight', healthy_info_file, modify_weightResponse, 'POST', 'JSON');
	}
	else {
		document.getElementById('alarm_span').innerText = '体检信息没有填写完整';
		document.getElementById('alarm_span').style.display = 'inline';
	}
}

//修改体重回调
function modify_weightResponse(res) {
	showMsg(res);
	if (res.code) {
		var obj_table = document.getElementById('weight_his');
		obj_table.deleteRow(0);
		obj_table.deleteRow(0);
		var len = obj_table.rows.length;
		var new_rows = obj_table.insertRow(len);
		for (var i = 0; i < res.examination.keys.length; i++) {
			var th_obj = document.createElement('th');
			th_obj.innerHTML = res.examination.keys[i];
			new_rows.appendChild(th_obj);
		}

		new_rows = obj_table.insertRow(len + 1);
		for (i = 0; i < res.examination.values.length; i++) {
			var td_obj = new_rows.insertCell(i);
			td_obj.innerHTML = res.examination.values[i];
			new_rows.appendChild(td_obj);
		}
		close_pop();
	}
}

//跳转致添加健康档案
function healthyWin() {

}

//计算体重指数
function calculater(val, sex) {
	if (val == 'BMI') {
		var weight = document.getElementById('txt_weight').value;
		var height = document.getElementById('txt_height').value;
		var BMI = "";

		if (weight != "" && height != "") {
			height = height / 10;
			BMI = (weight / (height * height)).toFixed(2) * 100;
			if (sex == 1) //男性的BIM标准
			{
				if (BMI < 20) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '过轻';
				}
				else if (20 <= BMI && BMI < 25) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '正常';
				}
				else if (25 <= BMI && BMI < 30) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '过重';
				}
				else if (30 <= BMI && BMI < 35) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '肥胖';
				}
				else if (35 <= BMI) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '非常肥胖';
				}
			}
			else //女性的BMI标准
			{
				if (BMI < 19) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '过轻';
				}
				else if (19 <= BMI && BMI < 24) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '正常';
				}
				else if (24 <= BMI && BMI < 29) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '过重';
				}
				else if (29 <= BMI && BMI < 34) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '肥胖';
				}
				else if (34 <= BMI) {
					document.getElementById('txt_BMI').value = BMI + ' ' + '非常肥胖';
				}
			}
		}
	}
	else if (val == 'WHR') {
		var waistline = document.getElementById('txt_waistline').value;
		var hipline = document.getElementById('txt_hipline').value;
		var WHR = "";
		if (waistline != "" && hipline != "") {
			WHR = (waistline / hipline).toFixed(2);
			if (sex == 1) {
				if (WHR < 0.85) {
					document.getElementById('txt_WHR').value = WHR + ' ' + '下身肥胖';
				}
				else if (0.85 <= WHR && WHR > 0.95) {
					document.getElementById('txt_WHR').value = WHR + ' ' + '正常';
				}
				else if (0.95 <= WHR) {
					document.getElementById('txt_WHR').value = WHR + ' ' + '上身肥胖';
				}
			}
			else {
				if (WHR < 0.67) {
					document.getElementById('txt_WHR').value = WHR + ' ' + '下身肥胖';
				}
				else if (0.67 <= WHR && WHR < 0.80) {
					document.getElementById('txt_WHR').value = WHR + ' ' + '正常';
				}
				else if (0.80 <= WHR) {
					document.getElementById('txt_WHR').value = WHR + ' ' + '上身肥胖';
				}
			}
		}
		//document.getElementById('txt_WHR').value= (waistline/hipline).toFixed(2);
	}
}

//输出提示信息公共函数
function promptMsg(val) {
	var msg = new Array();
	msg['timeout'] = 2000;

	if (val == 1) {
		msg['message'] = '添加成功';
	}
	else if (val == 0) {
		msg['message'] = '添加失败';
	}
	else {
		msg['message'] = '请先添加基本信息';
	}
	showMsg(msg);
}

//只能输入数字
function isNum(e) {
	var k = window.event ? e.keyCode: e.which;
	if (((k >= 48) && (k <= 57)) || k == 8 || k == 0) {

	}
	else {
		if (window.event) {
			window.event.returnValue = false;
		}
		else {
			e.preventDefault();
		}
	}
}

//展开详细信息
function show_table(table_id) {
	document.getElementById(table_id).style.display = document.getElementById(table_id).style.display == 'none' ? 'block': 'none';
}

/*
 * 健康档案添加界面跳转
 */
function sendHealthy(user_id, user_name, sex) {
	Ajax.call('service.php?act=healthy_manager', 'user_id=' + user_id + '&user_name=' + user_name + '&sex=' + sex, displayResponse, 'GET', 'JSON');
}

//返回健康档案回调
function displayResponse(res) {
	var obj_div = document.createElement('div');
	obj_div.id = 'healthy_file_div';
	obj_div.innerHTML = res.main;
	document.body.appendChild(obj_div);
	document.getElementById('main').style.display = 'none';
	obj_div.style.display = 'block';
	document.getElementById('return_userinfo').style.display = '';
	var obj_tr = document.getElementById('detail');
	if (obj_tr) {
		var obj_table = obj_tr.parentNode;
		obj_table.deleteRow(obj_tr.rowIndex);
	}
}

//返回顾客详细信息
function returnUserinfo() {
	var obj_div = document.getElementById('healthy_file_div');
	if (obj_div) {
		document.body.removeChild(obj_div);
	}
	document.getElementById('main').style.display = '';
}

//填写心理情况
function updPsychology(i, val) {
	psyAnswer[i] = val;
	alert(psyAnswer[i]);
}

//搜索健康档案
function schHealthy(obj) {
	var user_name = obj.elements['user_name'].value;
	var phone = obj.elements['phone'].value;
	var disease = obj.elements['disease'].value;

	Ajax.call('service.php?act=sch_healthy', 'user_name=' + user_name + '&phone=' + phone + '&disease=' + disease, 'GET', 'JSON');
}

//家庭成员健康档案
function cheHealthy(user_id) {
	if (user_id != null && user_id != 0) {
		Ajax.call('family_manager.php?act=get_family_healthy', 'user_id=' + user_id, cheHealthyRes, 'GET', 'JSON');
	}
}

function cheHealthyRes(res) {
	showPop();
	init();
}

function delFamilyUser(user_id, user_name) {
	var r = confirm('确认要将' + user_name + '移出这个家庭');
	if (r) {
		Ajax.call('family_manager.php?act=del_family_user', 'user_id=' + user_id, delFamilyUserRes, 'GET', 'JSON');
	}
	else return;
}

function delFamilyUserRes(res) {
	showMsg(res);
	if (res.code) {
		var trObj = document.getElementById('detail');
		alert(123);
		var tblObj = tdObj.parentNode;
		tblObj.deleteRow(trObj.rowIndex);
	}
}

//添加新的测试模板
function addNewTest(user_id) {
	Ajax.call('family_manager.php?act=add_new_test', 'user_id=' + user_id, addNewTestRes, 'GET', 'JSON');
}

function addNewTestRes(res) {
	document.getElementById('pop_ups').innerHTML = res.main;
	document.getElementById('fade').style.display = 'block';
	document.getElementById('div_pop_ups').style.display = 'block';
	document.getElementById('close_pop').style.display = 'none';
}

//搜索为添加家庭顾客
function schForFamily(type) {
	if (type == 0){
    var phone = document.getElementById('family_phone').value;
  } else if (type == 1) {
    var phone = document.getElementById('friend_phone').value; 
  }
	var user_id = document.getElementById('ID').value;

	if (phone != '' && phone != null) {
		Ajax.call('account_manager.php?act=sch_for_family', 'phone=' + phone + '&user_id=' + user_id + '&type=' + type, schForFamilyRes, 'GET', 'JSON');
	}
	else return;
}

function schForFamilyRes(res) {
	showPop(res);
}

//关闭弹出
function close_pop() {
	if (document.getElementById('title_pop')) {
		var title_pop = document.getElementById('title_pop');
		document.getElementById('div_pop_ups').removeChild(title_pop);
	}
	document.getElementById('div_pop_ups').style.display = 'none';
	document.getElementById('fade').style.display = 'none';
}

//加入家庭
function addToFamily(family_id, user_id) {
  var family_name = '';
  if(document.getElementById('family_name')){
    family_name = document.getElementById('family_name').value;
  }

	var grade = document.getElementById('type_id_' + user_id).value;
	if (grade == 0 || grade == null) {
		document.getElementById('alarm_span').innerHTML = '请选择辈分';
		quick_close();
		return;
	}

  if(document.getElementById('family_name')){
    if (family_name == null || family_name == '') {
      document.getElementById('alarm_span').innerHTML = '没有填写家庭名称';
      quick_close();
      return;
    }
  }

  Ajax.call('family_manager.php?act=add_to_family', 'family_name=' + family_name + '&family_id=' + family_id + '&grade=' + grade + '&user_id=' + user_id, addToFamilyRes, 'GET', 'JSON');

}

function addToFamilyRes(res) {
	if (res.code == 1) {
		document.getElementById('alarm_span').innerHTML = '成功加入家庭';
		var obj = document.getElementById('type_id_' + res.user_id);
		var obj_table = document.getElementById('form_sch_family_user');
		var obj_tr = obj.parentNode.parentNode;
		obj_table.deleteRow(obj_tr.rowIndex);
	}
	else if (res.code == 2) {
		document.getElementById('alarm_span').innerHTML = 'Ta已经加入家庭';
	}
	else if (res.code == 3) {
		document.getElementById('alarm_span').innerHTML = '已经存在该辈分的成员';
	}
	else {
		document.getElementById('alarm_span').innerHTML = '无法加入该家庭';
	}
	quick_close();
}

function quick_close() {
	document.getElementById('alarm_span').style.display = '';
	setTimeout(function() {
		document.getElementById('alarm_span').style.display = 'none';
	},
	10000);
}

//添加新的测试项目
function AddNewExamination(sel_obj) {
	var index = sel_obj.selectedIndex;

	for (var i = 0; i <= sel_obj.options.length; i++) {
		if (document.getElementById('txt_' + sel_obj.options[index].id)) break;
	}

	if (i <= sel_obj.options.length) return;
	else {
		var obj_table = document.getElementById('table_new_test');
		var length = obj_table.rows.length;
		var new_tr = obj_table.insertRow(length);
		var new_th = document.createElement("th");
		new_tr.appendChild(new_th);
		var new_td = new_tr.insertCell(1);
		new_th.innerText = sel_obj.options[index].text + '：';
		new_td.innerHTML = '<label><input type="text" class="bottom_input" id="txt_' + sel_obj.options[index].id + '" >' + ' ' + sel_obj.options[index].title + '     <img src="images/no.gif" onclick="delTest(this)" /></label>';
	}
}

//真正家长操作
function setRealParent(user_id, grade_name, operater) {
	if (operater == 'del') var r = confirm('确定取消Ta的家长身份');
	else if (operater == 'upd') var r = true;
	if (r) {
		Ajax.call('family_manager.php?act=operation_parent', 'user_id=' + user_id + '&grade_name=' + grade_name + '&operater=' + operater, setRealParentRes, 'GET', 'JSON');
	}
}

function setRealParentRes(res) {
	showMsg(res);
	if (res.code) {
		if (res.operater == 'del') {
			document.getElementById('parent_' + res.user_id).innerHTML = res.grade_name;
			document.getElementById('label_parent_' + res.user_id).innerHTML = '<label style="color:#777;display:inline;cursor:pointer;" onclick="setRealParent(' + res.user_id + ',' + "'" + res.grade_name + "'" + ',' + "'upd'" + ')">设为家长</label>';
		}
		else if (res.operater == 'upd') {
			document.getElementById('parent_' + res.user_id).innerHTML = res.grade_name + '<font color="#3367AC">【家长】</font>';
			document.getElementById('label_parent_' + res.user_id).innerHTML = '<label class="btn_new" style="color:#3367AC;display:inline;cursor:pointer;" onclick="setRealParent(' + res.user_id + ',' + "'" + res.grade_name + "'" + ',' + "'del'" + ')">取消家长</label>';
		}
	}
}

//添加没有的体检项
function addExamination() {
	var examination_name = document.getElementById('examination_name').value;
	var units = document.getElementById('units').value;
	var descript = document.getElementById('descript').value;
	if (examination_name != null && units != null && descript != null && examination_name != '' && units != '' && descript != '') {
		Ajax.call('family_manager.php?act=add_examination', 'examination_name=' + examination_name + '&descript=' + descript + '&units=' + units, addExaminationRes, 'GET', 'JSON');
	}
}

function addExaminationRes(res) {
	if (res.code) {
		var obj_sel = document.getElementById('examination');
		var obj_table = document.getElementById('table_new_test');
		var opt = document.createElement('option');
		opt.value = res.examination_id;
		opt.text = res.examination_name;
		opt.id = res.descript;
		opt.name = res.units;
		obj_sel.appendChild(opt);

		var length = obj_table.rows.length;
		var new_tr = obj_table.insertRow(length);
		var new_th = document.createElement("th");
		new_tr.appendChild(new_th);
		var new_td = new_tr.insertCell(1);
		new_th.innerText = res.examination_name + '：';
		new_td.innerHTML = '<label><input type="text" class="bottom_input" id="txt_' + res.descript + '" >' + ' ' + res.units + '     <img src="images/no.gif" onclick="delTest(this)" /></label>';
	}
}

//移出朋友圈
function delFriends(user_id) {
	var msg = new Array();
	msg['timeout'] = 2000;
	msg['message'] = '暂时不能移除好友';

	showMsg(msg);
}

//删除体检项
function delTest(obj) {
	var obj_table = document.getElementById('table_new_test');
	obj_table.deleteRow(obj.parentNode.parentNode.parentNode.rowIndex);
}

//获取以往体检结果
function getHistoryHealthy(user_name) {
	var examination = document.getElementById('history_examination').value;
	var user_id = document.getElementById('ID').value;

	if (examination != 0) {
		Ajax.call('family_manager.php?act=get_history_healthy', 'user_id=' + user_id + '&examination=' + examination + '&user_name=' + user_name, showPop, 'GET', 'JSON');
	}
}

function showPop(res) {
	document.getElementById('pop_ups').innerHTML = res.info;
	document.getElementById('fade').style.display = 'block';
	document.getElementById('div_pop_ups').style.display = 'block';
  if(document.getElementById('close_pop')){
    document.getElementById('close_pop').style.display = 'none';
  }
}

