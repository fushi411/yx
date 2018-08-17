<?php
namespace Light\Controller;
use Think\Controller;

/**
 * 各种模块接口 分发器
 * 
 */
class ApiController extends BaseController {
    // 独立？分发？ api + method_name

    // 原料采购付款
    public function CgfkApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    
    // 物流采购付款
    public function WlCgfkApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 配件采购付款
    public function PjCgfkApplyApi(){
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

    // 矿粉配比
    public function KfRatioApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 矿粉配比
    public function SnRatioApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

     // 复合粉配比
     public function FhfRatioApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 量库库存
    public function LkStockApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 量库库存
    public function SalesReceiptsApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        if(!$mod) list($system,$mod,$action) = array($_GET['system'],$_GET['modname'],$_GET['action']);
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
}