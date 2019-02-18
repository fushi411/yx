/*
* check_vacancy      检测填写的内容是否有空缺的标志位
* checkItem          检测填写的内容是否有空缺的函数
* telCheck           电话号码正则
* getDate            获取当前日期的函数
**/

var check_vacancy = false;
$(function(){
    getDate();
    $('#submit2').on('click',function(){
        checkItem();
    })
});


function checkItem() {
    check_vacancy = false;
    var name = $('#name').val();
    var product = $('#product').val();
    var contacts = $('#contacts').val();
    var telephone = $('#telephone').val();
    var info = $('#info').val();
    if(name==''){
        tooltip('输入备案名称');
        //layer.msg('输入备案名称!',{icon: 5});
        return false;
    }
    if(product==''){
        tooltip('请选择备案产品');
        //layer.msg('请选择备案产品!',{icon: 5});
        return false;
    }
    if(contacts==''){
        tooltip('请选择备案产品');
        //layer.msg('请输入联系人!',{icon: 5});
        return false;
    }
    if(telCheck(telephone)){

    }else{
        tooltip('电话号码的格式有误，请修改!');
        //layer.msg('电话号码的格式有误，请修改!',{icon: 5});
        return false;
    }
    if (info.length<5||info.length>15) {
        tooltip('备案信息须在5-15个字!');
        //layer.msg('备案信息须在5-15个字!',{icon: 5});
        return false;
    }
    check_vacancy = true;
    layer.msg('成功了哦',{icon: 1});
    // $.ajax({
    //     type:'post',
    //     url:
    // })
}

function telCheck(telephone) {
    var reg = /^(0|86|17951)?(13[0-9]|15[012356789]|166|17[3678]|18[0-9]|14[57])[0-9]{8}$/;
    var res = reg.test(telephone)
    return res;
}

function getDate() {
    // var myDate = new Date();
    // var date = myDate.toLocaleDateString();
    var date = new Date();
    var seperator1 = "-";
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = year + seperator1 + month + seperator1 + strDate;
    $("#date").attr('value',currentdate);
}