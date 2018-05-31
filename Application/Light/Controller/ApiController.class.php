<?php
namespace Light\Controller;
use Think\Controller;

/**
 * 各种模块接口 分发器
 * 
 */
class ApiController extends BaseController {
    // 独立？分发？ api + method_name

    // 采购付款
    public function CgfkApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    
    // 信用额度
    public function CreditLineApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 临时额度
    public function TempCreditLineApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

}