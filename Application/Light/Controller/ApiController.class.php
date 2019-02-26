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

    // 销售收款
    public function SalesReceiptsApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        if(!$mod) list($system,$mod,$action) = array($_GET['system'],$_GET['modname'],$_GET['action']);
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 销售退款
    public function SalesRefundApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        if(!$mod) list($system,$mod,$action) = array($_GET['system'],$_GET['modname'],$_GET['action']);
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    
    // 发货修改
    public function fh_edit_Apply_hbApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    // 发货修改
    public function fh_edit_ApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 矿粉物料配置
    public function KfMaterielApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    // 复合粉物料配置 
    public function FhfMaterielApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    // 水泥物料配置
    public function SnMaterielApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 其他收入
    public function AddMoneyQtApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 投资其他收入
    public function AddMoneyQtTzApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    // 费用用款
    public function CostMoneyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    // 费用付款
    public function CostMoneyPayApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }
    // 退货修改
    public function fh_refund_ApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 生控记录
    public function kfScjlApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 矿粉物料配置补录
    public function KfMaterielAmendApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 新增备案客户
    public function NewGuestApplyApi(){
        $system = I('get.system');
        $mod    = I('get.modname');
        $action = I('get.action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 新增总客户
    public function Contract_guest_ApplyApi(){
        $system = I('get.system');
        $mod    = I('get.modname');
        $action = I('get.action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 新增子客户
    public function Contract_guest_Apply2Api(){
        $system = I('get.system');
        $mod    = I('get.modname');
        $action = I('get.action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 新增子客户
    public function ContractApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 发货删除
    public function fh_del_ApplyApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }

    // 汪测试
    public function CeShiApi(){
        $system = I('system');
        $mod    = I('modname');
        $action = I('action');
        $logic = D(ucfirst($system).$mod ,'Logic');
        $res = $logic->$action();
        $this->ajaxReturn($res);
    }



}