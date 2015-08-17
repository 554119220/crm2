/** 切换选项卡
 * obj obj    选项卡的input控件
 */
function change (obj)
{
      var input = document.getElementsByTagName('input');

      for (var i = 0; i < input.length; i++) {
            if (input[i].type == 'radio' && input[i].id && input[i].name == 'table') {
                  var div = document.getElementById(input[i].value);
                  if (obj.id != input[i].id) {
                        input[i].checked = false;
                        if (input[i].previousSibling.previousSibling) {
                             input[i].previousSibling.previousSibling.className = 'tab-back';
                        }

                        if (div) {
                             div.className = 'hide';
                        }
                  } else if (input[i].checked) {
                        input[i].previousSibling.previousSibling.className = 'tab-front';
                        div.className = 'show';
                  }
            }
      }
}
