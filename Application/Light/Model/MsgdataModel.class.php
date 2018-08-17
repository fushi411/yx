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
        $result['wlkkWlCgfkApply'] = array(
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
                'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply','system'=>'kk'))
            );  
            $result['yxhbSalesReceiptsApply'] = array(
                'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesReceiptsApply','system' => 'yxhb')),
                'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply','system'=>'yxhb'))
            );    
            return $result;   
        }
}