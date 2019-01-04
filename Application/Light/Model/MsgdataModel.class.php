<?php
namespace Light\Model;
use Think\Model;

/**
 * 页面数据逻辑模型
 * @author 
 */

class MsgdataModel extends Model {
    // 虚拟模型
    protected $autoCheckFields = false;
    
    /**
     * 获取所需的页面跳转信息、审批信息
     * @param  string $mod_name 模块信息
     * @return array  $result   
     */
    public function GetMessage($mod_name,$viewtype=''){
        // 工厂模式 -> 模块名决定使用函数
        $obj = new MsgdataModel();
        $func = $mod_name;
        $result = method_exists($obj,$func) ? $this->$func():array();
        $this->activeOn($result,$viewtype);
        return $result;
    }

    /**
     * 对于页面显示当前页的处理
     * @param array 需要处理的输出
     */
    public function activeOn(&$array,$viewtype){
        if(empty($array)) return ;
        $system = I('system');
        $mod_name = I('modname');
        $str = $viewtype.$system.$mod_name;
        foreach($array['url'] as $k => $v){
            if(strcasecmp($v['modname'],$str) == 0) {
                $array['url'][$k]['on'] = 1;
                $array['title'] = $v['name'];
            }
        }
    }
    /**
     * 签收模式排除
     */
    public function QsArray(){
        $yxhb_mod  = M('yxhb_appflowtable')->field('pro_mod')->where(array('stage_name' => '签收'))->select();
        $kk_mod    = M('kk_appflowtable')->field('pro_mod')->where(array('stage_name' => '签收'))->select();
        return array_merge($yxhb_mod,$kk_mod);
    }
    
    // 采购付款
    
    public function DataCgfkApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保原材料采购付款','url' => U('Light/View/View',array('modname'=>'CgfkApply','system' => 'yxhb')),'modname' => 'yxhbCgfkApply'),
            array('name' => '建材原材料采购付款','url' => U('Light/View/View',array('modname'=>'CgfkApply','system' => 'kk')),'modname' => 'kkCgfkApply'), 
            array('name' => '环保物流运费付款','url' => U('Light/View/View',array('modname'=>'WlCgfkApply','system' => 'yxhb')),'modname' => 'yxhbWlCgfkApply'),
            array('name' => '建材物流运费付款','url' => U('Light/View/View',array('modname'=>'WlCgfkApply','system' => 'kk')),'modname' => 'kkWlCgfkApply'), 
            array('name' => '环保配件采购付款','url' => U('Light/View/View',array('modname'=>'PjCgfkApply','system' => 'yxhb')),'modname' => 'yxhbPjCgfkApply'),
            array('name' => '建材配件采购付款','url' => U('Light/View/View',array('modname'=>'PjCgfkApply','system' => 'kk')),'modname' => 'kkPjCgfkApply'), 
        );
        
        $result['kkCgfkApply']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CgfkApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CgfkApply','system'=>'kk')),
           
        );
        $result['yxhbCgfkApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CgfkApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CgfkApply','system'=>'yxhb')),
            
        );   
        $result['yxhbWlCgfkApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'WlCgfkApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'WlCgfkApply','system'=>'yxhb')),
           
        );   
        $result['kkWlCgfkApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'WlCgfkApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'WlCgfkApply','system'=>'kk')),
            
        );  
        $result['yxhbPjCgfkApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'PjCgfkApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'PjCgfkApply','system'=>'yxhb')),
           
        );   
        $result['kkPjCgfkApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'PjCgfkApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'PjCgfkApply','system'=>'kk')),
            
        );  
        return $result;      
    }

     // 采购付款
    public function CgfkApply(){
        return $this->DataCgfkApply();
    }
    // 物流采购付款
    public function WlCgfkApply(){
        return $this->DataCgfkApply();
    }

    // 采购付款
    public function PjCgfkApply(){
        return $this->DataCgfkApply();
    }

    // 信用额度
    public function CreditLineApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保信用额度','url' => U('Light/View/View',array('modname'=>'CreditLineApply','system' => 'yxhb')),'modname' => 'yxhbCreditLineApply'),
            array('name' => '建材信用额度','url' => U('Light/View/View',array('modname'=>'CreditLineApply','system' => 'kk')),'modname' => 'kkCreditLineApply'), 
        );

        $result['kkCreditLineApply']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CreditLineApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CreditLineApply','system'=>'kk'))
        );
        $result['yxhbCreditLineApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CreditLineApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CreditLineApply','system'=>'yxhb'))
        );   
        return $result;      
    }

    // 临时额度
    public function TempCreditLineApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保临时额度','url' => U('Light/View/View',array('modname'=>'TempCreditLineApply','system' => 'yxhb')),'modname' => 'yxhbTempCreditLineApply'),
            array('name' => '建材临时额度','url' => U('Light/View/View',array('modname'=>'TempCreditLineApply','system' => 'kk')),'modname' => 'kkTempCreditLineApply'), 
        );

        $result['kkTempCreditLineApply']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'TempCreditLineApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'TempCreditLineApply','system'=>'kk'))
        );
        $result['yxhbTempCreditLineApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'TempCreditLineApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'TempCreditLineApply','system'=>'yxhb'))
        );   
        return $result;      
    }

    // 配比通知
    public function RatioApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '矿粉配比通知'  ,'url' => U('Light/View/View',array('modname'=>'KfRatioApply','system' => 'yxhb')),'modname' => 'yxhbKfRatioApply'),
            array('name' => '水泥配比通知'  ,'url' => U('Light/View/View',array('modname'=>'SnRatioApply','system' => 'kk')),'modname' => 'kkSnRatioApply'),
            array('name' => '复合粉配比通知','url' => U('Light/View/View',array('modname'=>'FhfRatioApply','system' => 'kk')),'modname' => 'kkFhfRatioApply'), 
        );

        $result['kkSnRatioApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'SnRatioApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'SnRatioApply','system'=>'kk'))
        ); 
        $result['yxhbKfRatioApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'KfRatioApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'KfRatioApply','system'=>'yxhb'))
        );   
        $result['kkFhfRatioApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'FhfRatioApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'FhfRatioApply','system'=>'kk'))
        ); 
        return $result;  
    }
    // 矿粉配比通知
    public function KfRatioApply(){
        return $this->RatioApply();
    }
    // 水泥配比通知
    public function SnRatioApply(){
        return $this->RatioApply();
    }

    // 复合粉配比通知
    public function FhfRatioApply(){
        return $this->RatioApply();
    }

    // 复合粉配比通知
    public function LkStockApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保量库库存','url' => U('Light/View/View',array('modname'=>'LkStockApply','system' => 'yxhb')),'modname' => 'yxhbLkStockApply'),
        );
   
        $result['yxhbLkStockApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'LkStockApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'LkStockApply','system'=>'yxhb'))
        );   
        return $result;   
    }
    
        // 销售收款
        public function SalesReceiptsApply(){
            $result = array();
            $result['url'] = array(
                array('name' => '建材销售收款','url' => U('Light/View/View',array('modname'=>'SalesReceiptsApply','system' => 'kk')),'modname' => 'kkSalesReceiptsApply'),
                array('name' => '环保销售收款','url' => U('Light/View/View',array('modname'=>'SalesReceiptsApply','system' => 'yxhb')),'modname' => 'yxhbSalesReceiptsApply'),
            );
       
            $result['kkSalesReceiptsApply'] = array(
                'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesReceiptsApply','system' => 'kk')),
                'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply','system'=>'kk')),
                // 'fiexd_copy_id' => 'xly',
                // 'copydata' => array(
                //     array('name' =>'许丽颖','url' => 'http://shp.qpic.cn/bizmp/nFsVdIIiaLZsmAv2LEDiaFwmzW1KMfkUIA3IW1c9PibAfaPCICgibpibQNA/'),
                // )
            );  
            $result['yxhbSalesReceiptsApply'] = array(
                'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesReceiptsApply','system' => 'yxhb')),
                'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply','system'=>'yxhb')),
                // 'fiexd_copy_id' => 'xly',
                // 'copydata' => array(
                //     array('name' =>'许丽颖','url' => 'http://shp.qpic.cn/bizmp/nFsVdIIiaLZsmAv2LEDiaFwmzW1KMfkUIA3IW1c9PibAfaPCICgibpibQNA/'),
                // )
            );    
            return $result;   
        }
    // 发货修改
    public function fh_edit_Apply_hb(){
        return $this->fhApply(); 
    }

     // 发货修改
     public function fh_edit_Apply(){
        return $this->fhApply();
    }
    // 发货修改
    public function fh_refund_Apply(){
        return $this->fhApply();
    }

    public function fhApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保发货修改','url' => U('Light/View/View',array('modname'=>'fh_edit_Apply_hb','system' => 'kk')),'modname' => 'kkfh_edit_Apply_hb'),
            array('name' => '建材发货修改','url' => U('Light/View/View',array('modname'=>'fh_edit_Apply','system' => 'kk')),'modname' => 'kkfh_edit_Apply'),
            array('name' => '环保退货修改','url' => U('Light/View/View',array('modname'=>'fh_refund_Apply','system' => 'yxhb')),'modname' => 'yxhbfh_refund_Apply'),
            array('name' => '建材退货修改','url' => U('Light/View/View',array('modname'=>'fh_refund_Apply','system' => 'kk')),'modname' => 'kkfh_refund_Apply'),
        );
   
        $result['kkfh_edit_Apply_hb'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'fh_edit_Apply_hb','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'fh_edit_Apply_hb','system'=>'kk'))
        );   
        $result['kkfh_edit_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'fh_edit_Apply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'fh_edit_Apply','system'=>'kk'))
        );  
        $result['yxhbfh_refund_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'fh_refund_Apply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'fh_refund_Apply','system'=>'yxhb'))
        );
        $result['kkfh_refund_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'fh_refund_Apply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'fh_refund_Apply','system'=>'kk'))
        );
        return $result;  
    }


    // 矿粉物料配置
    public function KfMaterielApply(){
        return $this->MaterielApply();
    }
    // // 复合粉物料配置 
    // public function FhfMaterielApply(){
    //     return $this->MaterielApply();
    // }
    // // 水泥物料配置
    // public function SnMaterielApply(){
    //     return $this->MaterielApply();
    // }

      // 配比通知
      public function MaterielApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '矿粉物料配置'  ,'url' => U('Light/View/View',array('modname'=>'KfMaterielApply','system' => 'yxhb')),'modname' => 'yxhbKfMaterielApply'),
            // array('name' => '水泥物料配置'  ,'url' => U('Light/View/View',array('modname'=>'SnMaterielApply','system' => 'kk')),'modname' => 'kkSnMaterielApply'),
            // array('name' => '复合粉物料配置','url' => U('Light/View/View',array('modname'=>'FhfMaterielApply','system' => 'kk')),'modname' => 'kkFhfMaterielApply'), 
        );

        // $result['kkSnMaterielApply'] = array(
        //     'process' => U('Light/Process/ApplyProcess',array('modname'=>'SnMaterielApply','system' => 'kk')),
        //     'info'    => U('Light/Apply/applyInfo',array('modname'=>'SnMaterielApply','system'=>'kk'))
        // ); 
        $result['yxhbKfMaterielApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'KfMaterielApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'KfMaterielApply','system'=>'yxhb'))
        );   
        // $result['kkFhfMaterielApply'] = array(
        //     'process' => U('Light/Process/ApplyProcess',array('modname'=>'FhfMaterielApply','system' => 'kk')),
        //     'info'    => U('Light/Apply/applyInfo',array('modname'=>'FhfMaterielApply','system'=>'kk'))
        // ); 
        return $result;  
    }

       // 其他收入
    public function AddMoneyQt(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保其他收入','url' => U('Light/View/View',array('modname'=>'AddMoneyQt','system' => 'yxhb')),'modname' => 'yxhbAddMoneyQt'),
            array('name' => '建材其他收入','url' => U('Light/View/View',array('modname'=>'AddMoneyQt','system' => 'kk')),'modname' => 'kkAddMoneyQt'),
            array('name' => '投资其他收入','url' => U('Light/View/View',array('modname'=>'AddMoneyQtTz','system' => 'kk')),'modname' => 'kkAddMoneyQtTz'),
        );
   
        $result['yxhbAddMoneyQt'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'AddMoneyQt','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'AddMoneyQt','system'=>'yxhb'))
        );   
        $result['kkAddMoneyQt'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'AddMoneyQt','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'AddMoneyQt','system'=>'kk'))
        ); 
        $result['kkAddMoneyQtTz'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'AddMoneyQtTz','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'AddMoneyQtTz','system'=>'kk'))
        ); 
        return $result;   
    }

    // 其他收入
    public function AddMoneyQtTz(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保其他收入','url' => U('Light/View/View',array('modname'=>'AddMoneyQt','system' => 'yxhb')),'modname' => 'yxhbAddMoneyQt'),
            array('name' => '建材其他收入','url' => U('Light/View/View',array('modname'=>'AddMoneyQt','system' => 'kk')),'modname' => 'kkAddMoneyQt'),
            array('name' => '投资其他收入','url' => U('Light/View/View',array('modname'=>'AddMoneyQtTz','system' => 'kk')),'modname' => 'kkAddMoneyQtTz'),
        );
    
        $result['yxhbAddMoneyQt'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'AddMoneyQt','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'AddMoneyQt','system'=>'yxhb'))
        );   
        $result['kkAddMoneyQt'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'AddMoneyQt','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'AddMoneyQt','system'=>'kk'))
        ); 
        $result['kkAddMoneyQtTz'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'AddMoneyQtTz','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'AddMoneyQtTz','system'=>'kk'))
        ); 
        return $result;   
    }

    // 费用用款
    public function CostMoney(){
        $result = array();
        $result['url'] = array(
            //array('name' => '环保用款费用','url' => U('Light/View/View',array('modname'=>'CostMoney','system' => 'yxhb')),'modname' => 'yxhbCostMoney'),
            array('name' => '建材用款费用','url' => U('Light/View/View',array('modname'=>'CostMoney','system' => 'kk')),'modname' => 'kkCostMoney'),
        );
   
        $result['yxhbCostMoney'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CostMoney','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CostMoney','system'=>'yxhb'))
        );   
        $result['kkCostMoney'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CostMoney','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CostMoney','system'=>'kk'))
        ); 
        return $result;   
    }
    // 生控记录(矿粉)
    public function kfScjl(){
        $result = array();
        $result['url'] = array(
            array('name' => '生控记录(矿粉)','url' => U('Light/View/View',array('modname'=>'kfScjl','system' => 'yxhb')),'modname' => 'yxhbkfScjl'),
            //array('name' => '建材用款费用','url' => U('Light/View/View',array('modname'=>'kfScjl','system' => 'kk')),'modname' => 'kkCostMoney'),
        );
   
        $result['yxhbkfScjl'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'kfScjl','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'kfScjl','system'=>'yxhb'))
        );   
        // $result['kkCostMoney'] = array(
        //     'process' => U('Light/Process/ApplyProcess',array('modname'=>'CostMoney','system' => 'kk')),
        //     'info'    => U('Light/Apply/applyInfo',array('modname'=>'CostMoney','system'=>'kk'))
        // ); 
        return $result;   
    }
    // 生控记录(矿粉)
    public function KfMaterielAmend(){
        $result = array();
        $result['url'] = array(
            array('name' => '矿粉物料补录','url' => U('Light/View/View',array('modname'=>'KfMaterielAmend','system' => 'yxhb')),'modname' => 'yxhbKfMaterielAmend'),
           
        );
   
        $result['yxhbKfMaterielAmend'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'KfMaterielAmend','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'KfMaterielAmend','system'=>'yxhb'))
        );   
      
        return $result;   
    }

    // 新增客户申请
    public function GuestApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保新增总客户'  ,'url' => U('Light/View/View',array('modname'=>'Contract_guest_Apply','system' => 'yxhb')),'modname' => 'yxhbContract_guest_Apply'),
            array('name' => '建材新增总客户'  ,'url' => U('Light/View/View',array('modname'=>'Contract_guest_Apply','system' => 'kk')),'modname' => 'kkContract_guest_Apply'),
            array('name' => '环保新增子客户'  ,'url' => U('Light/View/View',array('modname'=>'Contract_guest_Apply2','system' => 'yxhb')),'modname' => 'yxhbContract_guest_Apply2'),
            array('name' => '建材新增子客户'  ,'url' => U('Light/View/View',array('modname'=>'Contract_guest_Apply2','system' => 'kk')),'modname' => 'kkContract_guest_Apply2'),
            array('name' => '环保新增备案客户'  ,'url' => U('Light/View/View',array('modname'=>'NewGuestApply','system' => 'yxhb')),'modname' => 'yxhbNewGuestApply'),
            array('name' => '建材新增备案客户'  ,'url' => U('Light/View/View',array('modname'=>'NewGuestApply','system' => 'kk')),'modname' => 'kkNewGuestApply'),
        );

        $result['kkContract_guest_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'Contract_guest_Apply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'Contract_guest_Apply','system'=>'kk'))
        );
        $result['kkContract_guest_Apply2'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'Contract_guest_Apply2','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'Contract_guest_Apply2','system'=>'kk'))
        );
        $result['yxhbContract_guest_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'Contract_guest_Apply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'Contract_guest_Apply','system'=>'yxhb'))
        );
        $result['yxhbContract_guest_Apply2'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'Contract_guest_Apply2','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'Contract_guest_Apply2','system'=>'yxhb'))
        );
        $result['yxhbNewGuestApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'NewGuestApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'NewGuestApply','system'=>'yxhb'))
        );
        $result['kkNewGuestApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'NewGuestApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'NewGuestApply','system'=>'kk'))
        );

        return $result;
    }
    // 新增备案客户
    public function NewGuestApply(){
        return $this->GuestApply();
    }
    
    // 新增合同总客户
    public function Contract_guest_Apply(){
        return $this->GuestApply();
    }

    // 新增合同子客户
    public function Contract_guest_Apply2(){
        return $this->GuestApply();
    }

    // 生控记录(矿粉)
    public function  ContractApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保新增合同价格','url' => U('Light/View/View',array('modname'=>'ContractApply','system' => 'yxhb')),'modname' => 'yxhbContractApply'),
            array('name' => '建材新增合同价格','url' => U('Light/View/View',array('modname'=>'ContractApply','system' => 'kk')),'modname' => 'kkContractApply'),
        );
    
        $result['yxhbContractApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'ContractApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'ContractApply','system'=>'yxhb'))
        );   
        $result['kkContractApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'ContractApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'ContractApply','system'=>'kk'))
        ); 
        return $result;   
    }

}