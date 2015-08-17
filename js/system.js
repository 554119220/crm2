function admindisable(obj, uid) {
  obj.id = 'field_' + uid;
  Ajax.call('system.php?act=disable&uid=' + uid + '&field=' + obj.getAttribute('note'), '', admindisablereponse, 'GET', 'JSON');
}

function admindisablereponse(res) {
  var inputObj = document.getElementById('field_' + res.uid);

  inputObj.value = parseInt(res.content) ? '是': '否';
  inputObj.style.background = parseInt(res.content) ? '': '#E0E0E0';

  var msg = '修改成功，';
  switch (res.note) {
    case 'status':
      msg += parseInt(res.content) ? '该员工状态已改为在职！': '该员工状态已改为离职！';
      break;
    case 'stats':
      msg += parseInt(res.content) ? '系统将开始统计该员工的相关数据！': '系统将不再统计该员工的相关数据！';
      break;
    case 'assign':
      msg += parseInt(res.content) ? '系统将自动为该员工分配新顾客！': '系统将不再为该员工分配新顾客！';
      break;
    case 'freeze':
      msg += parseInt(res.content) ? '系统将向该员工屏蔽Ta的顾客主要信息': '该员工可查看自己顾客的所有资料';
      break;
  }

  inputObj.removeAttribute('id');
  showMsg({
    req_msg: true,
    timeout: 2000,
    message: msg
  });
}

//这个函数是对表单提交	
function sumit() {
  var obj = document.forms['form'];

  var username = obj.elements['username'].value;
  var password = obj.elements['password'].value;
  var mobile = obj.elements['mobile'].value;
  var pass = obj.elements['pass'].value;
  var name = obj.elements['name'].value;
  var roles = obj.elements['roles'].value;
  var group_id = obj.elements['group_id'].value;
  var ext = obj.elements['ext'].value;

  if (!checkInputValue(username, password, pass, mobile)) {
    return;
  }

  var data = 'password=' + password + '&mobile=' + mobile + '&pass=' + pass + '&name=' + name + '&username=' + username + '&roles=' + roles + '&group_id=' + group_id + '&ext='+ext;
  Ajax.call('system.php?act=adds', data, addadmin, 'POST', 'JSON');
}

function addadmin(res) {
  showMsg(res);
  if (res.code == 1) {
    document.getElementById('btn_submit_add').disabled = true;
  }
}

//这个函数式对用户名的提示；
function user() {
  var obj = document.forms['form'];
  var username = obj.elements['username'].value;
  if (username == '') document.getElementById('users').innerHTML = "用户名不能为空";
  else Ajax.call('system.php?act=user', 'user=' + username, user_admin, 'POST', 'JOSN');
}

function user_admin(res) {
  document.getElementById('users').innerHTML = res;
}

//对权限的判断	
function checkq(list, obj) {
  var frm = obj.form;
  for (i = 0; i < frm.elements.length; i++) {
    if (frm.elements[i].name == "action_code[]") {
      var regx = new RegExp(frm.elements[i].value + "(?!_)", "i");

      if (list.search(regx) > - 1) {
        frm.elements[i].checked = obj.checked;
      }
    }
  }
}

function checkAll(frm, checkbox) {
  for (i = 0; i < frm.elements.length; i++) {
    if (frm.elements[i].name == 'action_code[]' || frm.elements[i].name == 'chkGroup') {
      frm.elements[i].checked = checkbox.checked;
    }
  }
}

function keeps() {
  var str = '';
  var frm = document.forms['from2'];
  var role_name = frm.elements['role_name'].value;
  var role_vie = frm.elements['role_vie'].value;
  var role_manage = frm.elements['role_manage'].value;
  var role_type = frm.elements['role_type'].value;
  var role_desc = frm.elements['role_desc'].value;
  for (i = 0; i < frm.elements.length; i++) {
    if (frm.elements[i].name == 'action_code[]' && frm.elements[i].checked == true) {
      str += frm.elements[i].value + ','
    }
  }

  Ajax.call('system.php?act=role_keeps', 'str=' + str + '&role_name=' + role_name + '&role_vie=' + role_vie + '&role_manage=' + role_manage + '&role_type=' + role_type + '&role_desc=' + role_desc, roleRomve, 'POST', 'TEXT')
}

function roleRomve(res) {
  var msg = new Array();
  msg['timeout'] = 2000;
  if (res) msg['message'] = '添加成功';
  else msg['message'] = '添加失败';
  showMsg(msg);
}

// 对客户资料的添加；
// 对手机的判断.验证顾客是否重复；
// 验证顾客是否重复
// 是不是黑名单
function isRepeat() {
  var area_code = document.getElementById('area_code').value;
  var home_phone = document.getElementById('home_phone').value;
  var mobile_phone = document.getElementById('mobile_phone').value;
  var phone = '';

  if ((area_code.length && ! /^0\d{2,3}$/.test(area_code)) || (home_phone.length && ! /^[1-9]{1}\d{5,7}$/.test(home_phone))) {
    showMsg({
      req_msg: true,
      message: '请正确填写固话号码！！'
    });

    document.getElementById('home_phone').value = '';
    return false;
  } else if (home_phone.length) {
    phone = '&area_code=' + area_code + '&hphone=' + home_phone;
  }

  if (mobile_phone.length && /^1[34578]{1}\d{9}/.test(mobile_phone)) {
    phone += '&mphone=' + mobile_phone;
  } else if (mobile_phone.length) {
    showMsg({
      req_msg: true,
      message: '请填写正确的手机号码！！'
    });

    document.getElementById('mobile_phone').value = '';
    return false;
  }

  if (phone.length) {
    Ajax.call('users.php?act=add_custom', phone, infoRes, "POST", "TEXT");
  }
}

function infoRes(res) {
  if (res == 1) {
    var home_phone = document.getElementById('home_phone').value;
    var mobile_phone = document.getElementById('mobile_phone').value;

    // 显示用户已经存在的信息
    // 是否被列入黑名单
    if (inBlacklist(home_phone, mobile_phone)) {} else {
      document.getElementById('infos').innerHTML = '';
    }
  } else {
    showMsg({
      req_msg: true,
      message: '<b><font color="red">' + res + '</font></b>'
    });

    document.getElementById('area_code').value = '';
    document.getElementById('home_phone').value = '';
    document.getElementById('mobile_phone').value = '';
  }

  return false;
}

//选项卡的判断；
function change(obj) {
  var input = document.getElementsByTagName('input');

  for (var i = 0; i < input.length; i++) {
    if (input[i].type == 'radio' && input[i].id) {
      var div = document.getElementById(input[i].value);
      if (obj.id != input[i].id) {
        input[i].checked = false;
        if (input[i].previousSibling.previousSibling) input[i].previousSibling.previousSibling.className = 'tab-back';
        if (div) div.className = 'hide';
      }
      else if (input[i].checked) {
        input[i].previousSibling.previousSibling.className = 'tab-front';
        div.className = 'show';
      }
    }
  }
}

/* 添加一行记录 */
function addLines(obj) {
  var newobj = obj.parentNode.parentNode;
  var table = newobj.parentNode;
  var tr = table.insertRow(newobj.rowIndex + 1);
  tr.innerHTML = newobj.innerHTML;
  var input = tr.getElementsByTagName('input');
  for (el in input) {
    if (!isNaN(el)) {
      var index = parseInt(input[el].name.match(/\d/g)) + 1;
      input[el].name = input[el].name.replace(/\[\d*\]/, '[' + index + ']');
    }
  }
  var select = tr.getElementsByTagName('select');
  for (el in select) {
    if (!isNaN(el)) {
      var index = parseInt(select[el].name.match(/\d/g)) + 1;
      select[el].name = select[el].name.replace(/\[\d*\]/, '[' + index + ']');
    }
  }
  obj.style.display = 'none';
}

/* 阳历阴历转换 */
function calendarChange(obj) {
  var calendar = document.getElementById('calendar');
  var birthday = document.getElementById('birthday');

  var birthdayDate = birthday.value;
  var calendarType = calendar.value;
  if (birthdayDate) {
    Ajax.call('users.php?act=calendar', 'birthday=' + birthdayDate + '&type=' + calendarType, showCalendar, 'POST', 'JSON');
  }
}
/* 阴阳历转换回调函数 */
function showCalendar(res) {
  document.getElementById('showBirthday').innerHTML = '<strong>' + res.type + '</strong>' + res.date;
}
/**
 * 检查表单输入的数据
 */
function validates(obj) {
  // validator = new Validator("theForm");
  // validator.isEmail("email", invalid_email, true);
  // var selProvinces = document.getElementById('selProvinces').value;
  // var selCities    = document.getElementById('selCities').value;
  //var selDistricts = document.getElementById('selDistricts').value;
  var custom = document.forms['theForms'];
  var user_name = custom.elements['username'].value; // 顾客姓名
  var eff_id = custom.elements['eff_id'].value; // 顾客分类；
  var sex = "";
  var radio = document.getElementsByName("sex");

  for (var i = 0; i < radio.length; i++) {
    if (radio[i].checked == true) {
      sex = radio[i].value;
      break;
    }
  }

  // 获取性别；
  var birthday = custom.elements['birthday'].value; // 生日；
  // var age_group   = custom.elements['age'].value; // 年龄段
  var from_where = custom.elements['from_where'].value; // 顾客来源
  var customer_type = custom.elements['customer_type'].value; // 顾客类型；
  var mobile_phone = custom.elements['mobile_phone'].value; // 手机号码；
  var id_card = custom.elements['id_card'].value; // 身份证号；
  var member_cid = custom.elements['member_cid'].value; // 会员卡号；
  var area_code = document.getElementById('area_code').value; // 区号
  var home_phone = document.getElementById('home_phone').value;
  var qq = custom.elements['qq'].value; // QQ；
  var aliww = custom.elements['aliww'].value; // 阿里旺旺；
  var habby = custom.elements['habby'].value; // 爱好
  var email = custom.elements['email'].value; // 邮箱；
  var occupat = custom.elements['occupat'].value; // 顾客职业；
  var income = custom.elements['income'].value; // 经济来源；
  var disease_2 = custom.elements['disease_2'].value; // 其他疾病；
  //var remarks     = custom.elements['remarks'].value;   // 备注；
  var parent_id = custom.elements['parent_id'].value; // 推荐人；
  var country = custom.elements['country'].value; // 国家；
  // var uname       = custom.elements['uname'].value;       // 国家；
  // var admin_id    = custom.elements['admin_id'].value;    // 顾客归属；
  // var first_admin = custom.elements['first_admin'].value; // 添加顾客客服；
  var snail = custom.elements['snail'].value; // 平邮地址；
  // var team       = custom.elements['team'].value;//所属团队；
  var lang = custom.elements['lang'].value; // ch常用语言；
  var role_id = custom.elements['role_id'].value; // 所属平台；
  var province = custom.elements['province'].value; // 省份；
  var city = custom.elements['city'].value; // 城市；
  var district = custom.elements['district'].value; // 区县
  var address = custom.elements['address'].value; // 详细地址；
  var zipcode = custom.elements['zipcode'].value; // 邮编；
  var characters = '';
  var disease = '';
  var uname = '';
  var mobile = '';
  var relation = '';
  var habitancy = '';
  var age = '';
  var relasex = '';
  var profession = '';
  var financial = '';
  var selfcare = '';

  // 验证手机号码 
  if ((!home_phone || !! mobile_phone) && ! /^1[34578]\d{9}$/.test(mobile_phone)) {
    showMsg({
      req_msg: true,
      timeout: 2000,
      message: '请输入正确的手机号码！'
    });
    return false;
  }

  // 验证用户名
  if (user_name.length <= 0) {
    showMsg({
      req_msg: true,
      timeout: 2000,
      message: '请输入（正确的）用户名！'
    });
    return false;
  }

  //性格
  for (i = 0; i < custom.elements.length; i++) {
    if (custom.elements[i].name == 'characters[]' && custom.elements[i].checked == true) {
      characters += custom.elements[i].value + ':';
    }
  }

  //疾病
  for (k = 0; k < custom.elements.length; k++) {
    if (custom.elements[k].name == 'disease[]' && custom.elements[k].checked == true) {
      disease += custom.elements[k].value + ':';
    }
  }

  for (i = 0; i < document.getElementById("social-table").getElementsByTagName("tr").length - 1; i++) {
    relation += 'relation[' + i + ']=' + custom.elements['relation[' + i + ']'].value + '&'; // 与顾客关系
    mobile += 'mobile[' + i + ']=' + custom.elements['mobile[' + i + ']'].value + '&'; // 电话联系
    habitancy += 'habitancy[' + i + ']=' + custom.elements['habitancy[' + i + ']'].value + '&'; // 关系与居住关系
    age += 'age[' + i + ']=' + custom.elements['age[' + i + ']'].value + '&'; // 年龄关系
    relasex += 'relasex[' + i + ']=' + custom.elements['relasex[' + i + ']'].value + '&'; // 性别
    profession += 'profession[' + i + ']=' + custom.elements['profession[' + i + ']'].value + '&'; // 职业
    financial += 'financial[' + i + ']=' + custom.elements['financial[' + i + ']'].value + '&'; // 经济
    selfcare += 'selfcare[' + i + ']=' + custom.elements['selfcare[' + i + ']'].value + '&'; // 保健意识
    uname += 'uname[' + i + ']=' + custom.elements['uname[' + i + ']'].value + '&'; // 名称
  }

  if (province == 0 || city == 0 || address == 0) {
    showMsg({
      req_msg: true,
      timeout: 2000,
      message: '请填写详细的省、市、区信息！'
    });
    return false;
  }

  Ajax.call('users.php?act=insert', 'username=' + user_name + '&eff_id=' + eff_id + '&sex=' + sex + '&birthday=' + birthday + '&from_where=' + from_where + '&customer_type=' + customer_type + '&mobile_phone=' + mobile_phone + '&id_card=' + id_card + '&member_cid=' + member_cid + '&qq=' + qq + '&aliww=' + aliww + '&habby=' + habby + '&email=' + email + '&occupat=' + occupat + '&income=' + income + '&disease_2=' + disease_2 + '&parent_id=' + parent_id + '&province=' + province + '&city=' + city + '&address=' + address + '&zipcode=' + zipcode + '&disease=' + disease + '&characters=' + characters + '&' + uname + '&' + relasex + '&' + mobile + '&' + relation + '&' + habitancy + '&' + age + '&' + profession + '&' + financial + '&' + selfcare + '&snail=' + snail + '&lang=' + lang + '&country=' + country + '&role_id=' + role_id + '&district=' + district + '&area_code=' + area_code + '&home_phone=' + home_phone, insertUser, 'POST', 'JSON');
}

function insertUser(res) {
  showMsg(res);
  Ajax.call('users.php?act=users_list', '', sendToServerResponse, 'GET', 'JSON');
}

/* 删除一行记录 */
function removeLine(obj) {
  var newobj = obj.parentNode.parentNode;
  var table = newobj.parentNode;

  if (table.rows.length == 2) {
    return;
  }
  table.deleteRow(newobj.rowIndex);
  var index = table.rows.length - 1;
  table.getElementsByTagName('tr').item(index).getElementsByTagName('img').item(0).style.display = 'inline';
}

// 从数据库中删除一条社会关系记录
function removeRela(obj, rela_id, user_id) {
  var newobj = obj.parentNode.parentNode;
  var table = newobj.parentNode;

  if (rela_id == 0 && user_id == 0) {
    if (table.rows.length == $row_number) {
      return;
    }
    removeLine(obj);
  }
  else {
    if (confirm('是否永久删除该行记录？')) {
      Ajax.call('users.php', 'act=del_rela&rela_id=' + rela_id + '&user_id=' + user_id, delFeedback, 'POST', 'TEXT');
      removeLine(obj);
    }
  }
}

function delFeedback(res) {
  if (res) {
    alert('删除成功！');
  }
  else {
    alert('删除失败，请稍后再试！');
  }
}
/*删除客户*/
function del_users(uid) {
  if (!window.confirm("是否要删除")) {
    return false;
  }
  Ajax.call('users.php?act=del_custom&uid=' + uid, '', delate_users, 'POST', 'TEXT');
}
function delate_users(res) {
  if (res == '1') {
    alert(' 删除成功');
  } else {
    alert(' 删除失败');
  }
}

function edit_custom(uid) {
  // validator = new Validator("theForm");
  // validator.isEmail("email", invalid_email, true);
  // var selProvinces = document.getElementById('selProvinces').value;
  // var selCities    = document.getElementById('selCities').value;
  //var selDistricts = document.getElementById('selDistricts').value;
  var custom = document.forms['theForms'];
  var user_name = custom.elements['username'].value; //顾客姓名
  var eff_id = custom.elements['eff_id'].value; //顾客分类；
  var sex = "";
  var radio = document.getElementsByName("sex");
  for (var i = 0; i < radio.length; i++) {
    if (radio[i].checked == true) {
      sex = radio[i].value;
      break;
    }
  } //获取性别；
  var birthday = custom.elements['birthday'].value; //生日；
  //var age_group=custom.elements['age'].value;//年龄段
  var from_where = custom.elements['from_where'].value; //顾客来源
  var customer_type = custom.elements['customer_type'].value; //顾客类型；
  var mobile_phone = custom.elements['mobile_phone'].value; //手机号码；
  var id_card = custom.elements['id_card'].value; //身份证号；
  var member_cid = custom.elements['member_cid'].value; //会员卡号；
  var qq = custom.elements['qq'].value; //QQ；
  var aliww = custom.elements['aliww'].value; //阿里旺旺；
  var habby = custom.elements['habby'].value; //爱好
  var email = custom.elements['email'].value; //邮箱；
  var occupat = custom.elements['occupat'].value; //顾客职业；
  var income = custom.elements['income'].value; //经济来源；
  var disease_2 = custom.elements['disease_2'].value; //其他疾病；
  //var remarks=custom.elements['remarks'].value;//备注；
  var parent_id = custom.elements['parent_id'].value; //推荐人；
  var country = custom.elements['country'].value; //国家；
  //var uname=custom.elements['uname'].value;//国家；
  //var admin_id=custom.elements['admin_id'].value;//顾客归属；
  //var first_admin=custom.elements['first_admin'].value;//添加顾客客服；
  var snail = custom.elements['snail'].value; //平邮地址；
  // var team=custom.elements['team'].value;//所属团队；
  var lang = custom.elements['lang'].value; //ch常用语言；
  var role_id = custom.elements['role_id'].value; //所属平台；
  var province = custom.elements['province'].value; //省份；
  var city = custom.elements['city'].value; //城市；
  var district = custom.elements['district'].value; //区县
  var address = custom.elements['address'].value; //详细地址；
  var zipcode = custom.elements['zipcode'].value; //邮编；
  var characters = '';
  var disease = '';
  var uname = '';
  var mobile = '';
  var relation = '';
  var habitancy = '';
  var age = '';
  var relasex = '';
  var profession = '';
  var financial = '';
  var selfcare = '';
  for (i = 0; i < custom.elements.length; i++) {
    if (custom.elements[i].name == 'characters[]' && custom.elements[i].checked == true) {
      characters += custom.elements[i].value + ':';
    }
  } //性格
  for (k = 0; k < custom.elements.length; k++) {
    if (custom.elements[k].name == 'disease[]' && custom.elements[k].checked == true) {
      disease += custom.elements[k].value + ':';
    }
  } //疾病
  for (i = 0; i < custom.elements.length; i++) {
    if (custom.elements[i].name == 'uname[]') {
      uname += custom.elements[i].value + ',';
    }
  } //关系姓名
  for (i = 0; i < document.getElementById("social-table").getElementsByTagName("tr").length - 1; i++) {
    relation += custom.elements['relation[' + i + ']'].value + ','; //与顾客关系
    mobile += custom.elements['mobile[' + i + ']'].value + ','; //电话联系
    habitancy += custom.elements['habitancy[' + i + ']'].value + ','; //关系与居住关系
    age += custom.elements['age[' + i + ']'].value + ','; //年龄关系
    relasex += custom.elements['relasex[' + i + ']'].value + ','; //性别
    profession += custom.elements['profession[' + i + ']'].value + ','; //职业；
    financial += custom.elements['profession[' + i + ']'].value + ','; //经济
    selfcare += custom.elements['profession[' + i + ']'].value + ','; //保健意识
    uname += custom.elements['profession[' + i + ']'].value + ','; //名称
  }

  Ajax.call('users.php?act=edit&uid=' + uid, 'username=' + user_name + '&eff_id=' + eff_id + '&sex=' + sex + '&birthday=' + birthday + '&from_where=' + from_where + '&customer_type=' + customer_type + '&mobile_phone=' + mobile_phone + '&id_card=' + id_card + '&member_cid=' + member_cid + '&qq=' + qq + '&aliww=' + aliww + '&habby=' + habby + '&email=' + email + '&occupat=' + occupat + '&income=' + income + '&disease_2=' + disease_2 + '&parent_id=' + parent_id + '&province=' + province + '&city=' + city + '&address=' + address + '&zipcode=' + zipcode + '&disease=' + disease + '&characters=' + characters + '&uname=' + uname + '&rela_sex=' + relasex + '&mobile=' + mobile + '&relation=' + relation + '&habitancy=' + habitancy + '&age=' + age + '&profession=' + profession + '&financial=' + financial + '&selfcare=' + selfcare + '&snail=' + snail + '&lang=' + lang + '&country=' + country + '&role_id=' + role_id + '&district=' + district, edit_user, 'POST', 'TEXT');
}

function edit_user(res) {
  if (res == '1') {
    alert('编辑成功');

  } else if (res == '0') {
    alert('编辑失败');
  }
}

//编辑管理员信息
function editAdminInfo(user_id) {
  Ajax.call('system.php?act=edit_admin&', 'user_id=' + user_id, editAdminInfoRes, 'GET', 'JSON');
}

function editAdminInfoRes(res) {
  main.innerHTML = res.main;
}

//添加管理员
function addAdminTemp() {
  Ajax.call('system.php?act=addadmin', '', addAdminRes, 'POST', 'JSON');
}

function addAdminRes(res) {
  main.innerHTML = res.main;
}

//编辑管理员
function updateAdmin(obj) {
  var user_id      = document.getElementById('id').value;
  var username     = obj.elements['username'].value;
  var number       = obj.elements['number'].value;
  var pre_password = obj.elements['pre_password'].value;
  var password     = obj.elements['password'].value;
  var pass         = obj.elements['pass'].value;
  var mobile       = obj.elements['mobile'].value;
  var name         = obj.elements['name'].value;
  var roles        = obj.elements['roles'].value;
  var ext          = obj.elements['ext'].value;

  if (password != '' && pre_password != '') {
    if (!checkInputValue(username, password, pass, mobile)) return;
  }
  else if (!checkInputValue(username, '123456', '123456', mobile)) return;
  Ajax.call('system.php?act=edit_admin_done&', 'user_id=' + user_id + '&number=' + number + '&pre_password=' + pre_password + '&password=' + password + '&pass=' + pass + '&mobile=' + mobile + '&name=' + name + '&role_id=' + roles+'&ext='+ext, updAdmRes, 'GET', 'JSON');
}

function updAdmRes(res) {
  showMsg(res);
  if (res.code) Document.getElementById('btn_submit_add').disabled = true;
}

//验证表单输入的数据格式
function checkInputValue(username, password, pass, mobile) {
  var msg = new Array();
  msg['timeout'] = 2000;

  if (username == '') {
    msg['message'] = '用户名不能为空';
    showMsg(msg);
    return false;
  }

  if (password.length < 6) {
    msg['message'] = '密码不能少于6位';
    showMsg(msg);
    return false;
  }

  if (password !== pass) {
    msg['message'] = '两次密码输入不相同';
    showMsg(msg);
    return false;
  }

  if (! (/^1[34578]\d{9}$/.test(mobile))) {
    msg['message'] = '手机不能为空，或格式不正确';
    showMsg(msg);
    return false;
  }

  return true;
}

//分派权限
function assignAuthority(obj) {
  var obj = obj.form;
  var user_id = obj.elements['user_id'].value;
  var role_id = obj.elements['role_id'].value;
  var action_list = '';

  for (var i = 0; i < obj.elements.length; i++) {
    if (obj.elements[i].checked == true) {
      action_list += obj.elements[i].value + ',';
    }
  }

  Ajax.call('system.php?act=assign_authority_done&', 'user_id=' + user_id + '&role_id=' + role_id + '&action_list=' + action_list, assignAuthorityRes, 'GET', 'JSON')
}

function assignAuthorityRes(res) {
  showMsg(res);
}

//选择上级权限
function selectPre(obj) {
  var tr = ((obj.parentNode).parentNode).parentNode;
  var lbl_obj = tr.getElementsByTagName('input');
  var count = 0;
  if (obj.checked) {
    lbl_obj[0].checked = true;
  }
  else {
    for (var i = 1; i < lbl_obj.length; i++) {
      if (lbl_obj[i].checked == true) count++;
    }
    lbl_obj[0].checked = count > 0 ? true: false;
  }
}

//根据部门获取管理员列表
function getRoleAdmin(role_id) {
  Ajax.call('filter.php?act=get_role_admin&', 'role_id=' + role_id, roleAdminRes, 'GET', 'JSON');
}

function roleAdminRes(res) {
  var obj = document.getElementById(res.obj);
  obj.length = 0;
  var admin = res.main;

  if (admin != '') {
    var opt = document.createElement('option');
    opt.value = 0;

    if (res.obj == 'admin_list') {
      opt.text = '选择管理员';
      obj.appendChild(opt);
      for (var i in admin) {
        if (typeof(admin[i]) == 'function') continue;
        opt = document.createElement('option');
        opt.value = admin[i].user_id;
        opt.text = admin[i].user_name;
        obj.appendChild(opt);
      }
    }
    else {
      opt.text = "选择操作";
      obj.appendChild(opt);
      for (var i in admin) {
        if (typeof(admin[i]) == 'function') continue;
        opt = document.createElement('option');
        opt.value = admin[i].operater_id;
        opt.text = admin[i].operater_name;
        obj.appendChild(opt);
      }
    }
  }
  else {
    var opt = document.createElement('option');
    opt.value = 0;
    opt.text = '没有任何记录';
    obj.appendChild(opt);
  }
}

//显示操作或智选器
function showdiv(target, hide) {
  document.getElementById(target).style.display = '';
  document.getElementById(hide).style.display = 'hide';
}

//获取操作的属性
function getOptAtr(opterator_id) {

}

//通过管理员获得操作
function getAdmOpt(admin_id) {
  var role_id = document.getElementById('role_list').value;
  Ajax.call('filter.php?act=get_admin_opt&', 'role_id=' + role_id + '&admin_id=' + admin_id, roleAdminRes, 'GET', 'JSON');
}

//检查操作可否添加
function checkOpt(obj) {
  var role_id = obj.elements['role_list'].value;
  var admin_id = obj.elements['admin_list'].value;
  var operater_name = obj.elements['operater_name'].value;

  if (operater_name != '') {
    Ajax.call('filter.php?act=check_opt&', 'role_id=' + role_id + '&admin_id=' + admin_id + '&operater_name=' + operater_name, checkres, 'GET', 'JSON');
  }
}

function checkres(res) {
  showMsg(res);
  if (res.code) {
    document.getElementById('operater_name').value = '';
  }
}

//添加操作
function addOpt(obj) {
  var role_id = obj.elements['role_list'].value;
  var admin_id = obj.elements['admin_list'].value;
  var operater_name = obj.elements['operater_name'].value;

  Ajax.call('filter.php?act=add&', 'role_id=' + role_id + '&admin_id=' + admin_id + '&operater_name=' + operater_name, addres, 'GET', 'JSON');
}

function addres(res) {
  showMsg(res);
}

/* 报表设置函数 */
function sendConfigInfo(obj) {
  var data = ['act=save_statistics_date_limit'];

  for (var i = 0; i < obj.length; i++) {
    switch (obj[i].type) {
      case 'radio':
        obj[i].checked && data.push(obj[i].name + '=' + obj[i].value);
        break;
      case 'text':
        if (!isNaN(obj[i].value)) {
          data.push(obj[i].name + '=' + obj[i].value);
        }
    }
  }

  Ajax.call('report_forms.php', data.join('&'), showMsg, 'POST', 'JSON');
  return false;
}

/**
 * 获取部门下小组列表
 */
function getGroupList(obj) {
  var sltObj = document.getElementById('group_id');
  sltObj.length = 0;

  var optObj = document.createElement('option');
  optObj.value = 0;
  optObj.text = '请选择';
  sltObj.appendChild(optObj);

  Ajax.call('system.php?act=group_list&role_id=' + obj.value, '', getGroupListResp, 'GET', 'JSON');
}

function getGroupListResp(res) {
  if (res.length > 0) {
    var sltObj = document.getElementById('group_id');

    for (var i = 0; i < res.length; i++) {
      var optObj = document.createElement('option');
      optObj.value = res[i].group_id;
      optObj.text = res[i].group_name;

      sltObj.appendChild(optObj);
    }

    if (document.getElementById('group_row')) {
      document.getElementById('group_row').className = '';
    }
  }
}

/*获得小组成员*/
function getAdminList(obj) {
  var sltObj = document.getElementById('admin_id');
  sltObj.length = 0;
  var role_id = document.getElementById('platform').value;

  var optObj = document.createElement('option');
  optObj.value = 0;
  optObj.text = '请选择';
  sltObj.appendChild(optObj);

  Ajax.call('system.php?act=admin_list', 'group_id=' + obj.value + '&role_id=' + role_id, getAdminListResp, 'GET', 'JSON');
}

function getAdminListResp(res) {
  if (res.length > 0) {
    var sltObj = document.getElementById('admin_id');
    fillSelect(res,sltObj);
  }
}

//部门下的员工
function roleAdminList(obj){
  var sltObj    = document.getElementById('admin_id');
  sltObj.length = 0;
  var role_id   = obj.value;
  var optObj    = document.createElement('option');
  optObj.value  = 0;
  optObj.text   = '请选择';
  sltObj.appendChild(optObj);

  Ajax.call('system.php?act=admin_list', 'role_id=' + role_id, getAdminListResp, 'GET', 'JSON');
}

function fillSelect(res,obj){
  for (var i = 0; i < res.length; i++) {
    var optObj   = document.createElement('option');
    optObj.value = res[i].user_id;
    optObj.text  = res[i].user_name;
    obj.appendChild(optObj);
  }
}

/**
 * 加载客服列表
 */
function filterStaff(obj) {
  var role_id = obj.elements['role_id'].value;
  var group_id = obj.elements['group_id'].value;

  Ajax.call('system.php', 'act=admin_by_role&role_id=' + role_id + '&group_id=' + group_id, filterStaffResp, 'POST', 'JSON');
  return false;
}

function filterStaffResp(res) {
  document.getElementById('resource').innerHTML = res.main;

  init();
}

/**
 * 修改顾客上限
 */
function saveMaxCustomer(obj, id) {
  Ajax.call('system.php', 'max_customer=' + obj.value + '&act=save&id=' + id, saveMaxCustomerResp, 'POST', 'JSON');
}

function saveMaxCustomerResp(res) {
  document.getElementById('html_' + res.id).parentNode.innerText = res.maxCustomer;
  showMsg(res);
}

function listFeedback(obj) {
  Ajax.call('system.php?act=feedback_collect', 'readed=' + obj.value, inMain, 'GET', 'JSON');
}

/*更改BUG反馈状态修改*/
function dealFeedback(msg_id, obj) {
  var trIndex = obj.parentNode.parentNode.rowIndex;

  Ajax.call('feedback.php?act=deal_feedback', 'msg_id=' + msg_id + '&item=' + obj.value + '&tr_index=' + trIndex + '&table_name=' + 'feedback_table', delTr, 'GET', 'JSON');
}

/*提交更新日志*/
function addChangeLog(obj) {
  var title = obj.elements['title'].value;
  var logText = obj.elements['log_text'].value;

  if (logText.length <= 0) {
    var msg = [];
    msg['timeout'] = 2000;
    msg['message'] = '更新内容不能为空';
    showMsg(msg);
  } else {
    Ajax.call('system.php?act=change_log', 'behave=' + 'add_change_log' + '&title=' + title + '&log_text=' + logText, showAndInmain, 'GET', 'JSON');
  }
}

/*提示并加载内容*/
function showAndInmain(res) {
  showMsg(res);
  inMain(res);
}

/**
 * 获取顾客来源ID
 */
function obtainFromWhereID(obj) {
  if (obj.value.length) {
    Ajax.call('query.php', 'act=obtain_fromwhere&fromWhere='+obj.value, obtainFromWhereIDResp, 'POST', 'JSON');
  }
}
function obtainFromWhereIDResp(res) {
  if (res.from_id) {
    document.getElementById('fromWhere').value = res.from_id;
  } else {
    showMsg(res);
  }
}
//隐藏联系方式
function setContactStatus(obj){
  Ajax.call('system.php?act=set_contact_status','status='+obj.elements['status'].value,showMsg,'GET','JSON');
}
