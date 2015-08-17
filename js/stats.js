/**
* 查询条件
*/
function sendStatsCondition (obj)
{
    var adminList = document.getElementById('adminList').getElementsByTagName('input');
    var shapeList = document.getElementById('displayShape').getElementsByTagName('input');

    var shape;
    var roleId;
    var adminArr = new Array();

    for (var i = 0; i < adminList.length; i++)
    {
        if (i == 0)
        {
            roleId = adminList[i].value;
        }

        adminList[i].parentNode.parentNode.className = '';
        if (obj.type == adminList[i].type && adminList[i].checked)
        {
            if (obj.type != 'radio') adminArr.push(adminList[i].value);
            adminList[i].parentNode.parentNode.className = 'checked';
        }
        else
        {
            adminList[i].checked = false;
            adminList[i].parentNode.parentNode.className = '';
        }
    }

    for (var i = 0; i < shapeList.length; i++)
    {
        if (shapeList[i].checked)
        {
            shape = shapeList[i].value;
        }
    }

    adminArr = adminArr.join(',');
    Ajax.call('report_forms.php', 'act=user_stats&get=admin&admin_list='+adminArr+'&shape='+shape+'&role_id='+roleId, sendStatsConditionResp, 'POST', 'JSON');
}

function sendStatsConditionResp (res)
{
    switch (res.shape) {
        case 'table' : 
            document.getElementById('dataDisplay').innerHTML = res.main;
            break;
        case 'pieChart' :
            drawMapCharts(res); 
            drawRingPieCharts(res); break;
        case 'mapChart' :
            drawMapCharts(res); break;
    }
}

/**
* 显示方式
*/
function switchDisplayShape (obj)
{
    var adminList = document.getElementById('adminList').getElementsByTagName('input');
    var shapeList = document.getElementById('displayShape').getElementsByTagName('input');

    var roleId;
    var shape = obj.value;
    var adminArr = new Array();

    for (var i = 0; i < adminList.length; i++)
    {
        if (i == 0)
        {
            roleId = adminList[i].value;
        }

        adminList[i].parentNode.parentNode.className = '';
        if (adminList[i].checked)
        {
            if (adminList[i].type != 'radio') adminArr.push(adminList[i].value);
            adminList[i].parentNode.parentNode.className = 'checked';
        }
    }

    for (var i = 0; i < shapeList.length; i++)
    {
        shapeList[i].parentNode.parentNode.className = '';
    }

    obj.parentNode.parentNode.className = 'checked';

    adminArr = adminArr.join(',');
    Ajax.call('report_forms.php', 'act=user_stats&get=admin&admin_list='+adminArr+'&shape='+shape+'&role_id='+roleId, sendStatsConditionResp, 'POST', 'JSON');
}

function switchDisplayShapeResp (res)
{
}

/**
* 查询订单数据
*/
function queryOrderData ()
{
    var query_time = document.getElementById('query_time').value;
    var platform   = document.getElementById('platform').value;
    var admin_id   = document.getElementById('admin_id').value;

    Ajax.call('report_forms.php','act=order_data_amount&query_time='+query_time+'&platform='+platform+'&admin_id='+admin_id,queryOrderDataResp,'POST','JSON');
}

/**
* 查询订单数据回调函数
*/
function queryOrderDataResp (res)
{
    document.getElementById('dataArea').innerHTML = res.main;
    drawPieCharts(res);
    init();
}

/**
* ECharts Pie Charts
*/
function drawPieCharts (resObj)
{
    require.config({paths:{echarts:'js/echarts'}});
    require(
        ['echarts'],
        function (ec) {
            var myChart = ec.init(document.getElementById('charts'));
            var option = {
                title : {
                    text: resObj.title,
                    subtext: resObj.subtext,
                    x:'center'
                },
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient : 'vertical',
                    x : 'left',
                    data:resObj.name
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : true,
                        dataView : {readOnly: false},
                        restore : true,
                        saveAsImage : true
                    }
                },
                calculable : true,
                series : [
                    {
                        name:'支付方式使用率',
                        type:'pie',
                        radius : [0, 110],
                        center: [,225],
                        data:resObj.data
                    }
                ]
            };
            myChart.setOption(option);
        }
    );
}

/**
* ECharts Ring Pie Charts
*/
function drawRingPieCharts (resObj)
{
    require.config({paths:{echarts:'js/echarts'}});
    require(
        ['echarts'],
        function (ec) {
            var myChart = ec.init(document.getElementById('dataDisplay'));
            var option = {
                title : {
                    text: resObj.title,
                    subtext: resObj.subtext,
                    x:'center'
                },
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient : 'vertical',
                    x : 'left',
                    data:resObj.data
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : true,
                        dataView : {readOnly: false},
                        restore : true,
                        saveAsImage : true
                    }
                },
                calculable : false,
                series : [
                    {
                        name:'团队顾客数量',
                        type:'pie',
                        selectedMode: 'single',
                        radius : [0, 100],
                        itemStyle : {
                            normal : {
                                label : {
                                    position : 'inner'
                                },
                                labelLine : {
                                    show : false
                                }
                            }
                        },
                        data:resObj.role
                    },
                    {
                        name:'个人顾客数量',
                        type:'pie',
                        radius : [120, 160],
                        data:resObj.admin
                    }
                ]
            };

            myChart.setOption(option);
        }
    );
}

/**
* ECharts Map Charts
*/
function drawMapCharts (resObj)
{
    require.config({paths:{echarts:'js/echarts-map'}});
    require(
        ['echarts'],
        function (ec) {
            var myChart = ec.init(document.getElementById('dataDisplay'));
            var ecConfig = require('echarts/config');
            var zrEvent = require('zrender/tool/event');
            var curIndx = 0;
            var mapType = [
                'china',
                // 23个省
                '广东', '青海', '四川', '海南', '陕西', 
                '甘肃', '云南', '湖南', '湖北', '黑龙江',
                '贵州', '山东', '江西', '河南', '河北',
                '山西', '安徽', '福建', '浙江', '江苏', 
                '吉林', '辽宁', '台湾',
                // 5个自治区
                '新疆', '广西', '宁夏', '内蒙古', '西藏', 
                // 4个直辖市
                '北京', '天津', '上海', '重庆',
                // 2个特别行政区
                '香港', '澳门'
            ];
            myChart.on(ecConfig.EVENT.MOUSEWHEEL, function(param){
                curIndx += zrEvent.getDelta(param.event) > 0 ? (-1) : 1;
                if (curIndx < 0) {
                    curIndx = mapType.length - 1;
                }
                var mt = mapType[curIndx % mapType.length];
                option.tooltip.trigger = 'item';
                option.series[0].mapType = mt;
                option.title.subtext = mt + '（滚轮或点击切换）';
                myChart.setOption(option, true);

                zrEvent.stop(param.event);
            });
            myChart.on(ecConfig.EVENT.MAP_SELECTED, function(param){
                var len = mapType.length;
                var mt = mapType[curIndx % len];
                if (mt == 'china') {
                    // 全国选择时指定到选中的省份
                    var selected = param.selected;
                    for (var i in selected) {
                        if (selected[i]) {
                            mt = i;
                            while (len--) {
                                if (mapType[len] == mt) {
                                    curIndx = len;
                                }
                            }
                            break;
                        }
                    }
                }
                else {
                    curIndx = 0;
                    mt = 'china';
                }
                option.tooltip.trigger = 'item';
                option.series[0].mapType = mt;
                option.title.subtext = mt + ' （滚轮或点击切换）';
                myChart.setOption(option, true);
            });
            var option = {
                title: {
                    text : resObj.title,
                    subtext : 'china （滚轮或点击切换）'
                },
                tooltip : {
                    trigger: 'item',
                },
                dataRange: {
                    min: 0,
                    max: resObj.max_num,
                    color:['blue','skyblue'],
                    text:['高','低'],           // 文本，默认为数值文本
                    calculable : true
                },
                series : [
                    {
                        name: '顾客数量',
                        type: 'map',
                        mapType: 'china',
                        selectedMode : 'single',
                        itemStyle:{
                            normal:{label:{show:true}},
                            emphasis:{label:{show:true}}
                        },
                        data:resObj.data
                    }
                ]
            };
            myChart.setOption(option);
        }
    );
}

/**
* 查询新增顾客数量
*/
function statsUserMonthly (obj) {
    var start = document.getElementById('start_time').value;
    var end   = document.getElementById('end_time').value;

    Ajax.call(obj.value, 'is_ajax=1&start_time='+start+'&end_time='+end, statsResp, 'GET', 'JSON');
}

/**
* 显示查询结果 回调函数
*/
function statsResp (res) {
    document.getElementById('user_stream_analysis').innerHTML = res.main;
}

/**
* 查询顾客来源
*/
function statsUserSource (obj) {
    var iFromId  = document.getElementById('from_id').value;
    var iRoleId  = document.getElementById('role_id').value;
    var iAdminId = document.getElementById('admin_id').value;

    Ajax.call(obj.value, 'is_ajax=1&from_id='+iFromId+'&role_id='+iRoleId+'&admin_id='+iAdminId, statsResp, 'GET', 'JSON');
}

/**
* 检索
*/
function filterByThis () {
    var role  = document.getElementById('role_id') && document.getElementById('role_id').value;
    var group = document.getElementById('group_id') && document.getElementById('group_id').value;

    var rowsList = document.getElementById('person_style').tBodies[0].rows;

    var attribute = null;
    if (/\d+/.test(role)) {
        attribute = 'role';
        role = parseInt(role);
    } else {
        attribute = 'role_code';
    }

    for (var i = rowsList.length - 2; i >= 0; i--) {
        rowsList[i].className = 'hide';
        if (role && parseInt(group)) {
            if (rowsList[i].getAttribute('group') == group && rowsList[i].getAttribute(attribute) == role) {
                rowsList[i].className = '';
            }
        } else if (role) {
            if (rowsList[i].getAttribute(attribute) == role) {
                rowsList[i].className = '';
            }
        } else if (parseInt(group)) {
            if (rowsList[i].getAttribute('group') == group) {
                rowsList[i].className = '';
            }
        } else {
            rowsList[i].className = '';
        }
    }

    calcTotal();
}

/**
* 保存目标销量
*/
function saveSalesTarget (obj) {
    var salesTarget = [];
    for (var i = obj.elements.length - 1; i >= 0; i--) {
        if (obj.elements[i].name && obj.elements[i].value) {
            salesTarget.push(obj.elements[i].name+':'+obj.elements[i].value);
        }
    }

    Ajax.call('debris_operation.php', 'act=save_sales_target&sales_target='+salesTarget, saveSalesTargetResp, 'POST', 'JSON');

    return false;
}

function saveSalesTargetResp (res) {
    showMsg(res);
}

/**
* 平台销量细分
*/
function getSalesDetail(platform,period) {
    Ajax.call('report_forms.php', 'act=sales_detail&platform='+platform+'&period='+period, getSalesDetailResp, 'POST', 'JSON');
}

function getSalesDetailResp(res) {
    showMsg(res);
}

/**
* 获取月份下的日销量
*/
function showThisMonth(months) {
    Ajax.call('report_forms.php?act=product_sales&months='+months, '', sendToServerResponse, 'GET', 'JSON');
}

function tableLock (obj) {
    var leftTable = document.getElementById('leftTable');
    var topTable  = document.getElementById('topTable');

    topTable.getElementsByTagName('table')[0].style.left = -obj.scrollLeft + 'px';
    leftTable.getElementsByTagName('table')[0].style.top = -obj.scrollTop + 'px';
}

/**
* 隐藏/显示
*/
function showOrHide(obj) {
    if (document.getElementById('col_1').checked || document.getElementById('col_11').checked) {
        var tableObj = document.getElementById('person_style');
        var rowObj   = document.getElementById('sortByThis').cells;

        for (var i = rowObj.length - 1; i >= 0; i--) {
            if (rowObj[i].innerText == obj.value) {
                rowObj[i].style.display = obj.checked ? '' : 'none';
                tableObj.tHead.rows[0].cells[Math.ceil((i+1)/2)].colSpan = obj.checked ? 2 : 1;

                for (var j = tableObj.tBodies[0].rows.length - 1; j >= 0; j--) {
                    tableObj.tBodies[0].rows[j].cells[i].style.display = obj.checked ? '' : 'none';
                }
            }
        }
    } else {
        obj.checked = true;
    }
}
