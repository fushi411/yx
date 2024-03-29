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
        $copy = $this->getFiexdCopy($system,$mod_name);
        if(!empty($copy)){
            $array[$system.$mod_name]['fiexd_copy_id'] = $copy['fiexd_copy_id'];
            foreach($copy['copydata'] as $k=>$v){
                $copy['copydata'][$k]['url'] = $v['avatar'];
            }
            $array[$system.$mod_name]['copydata'] = $copy['copydata'];
        }
    }
    // 获取固定抄送人员
    public function getFiexdCopy($system,$mod){
        $data = M($system.'_appflowtable')->where(array('pro_mod' => $mod,'stat' => 1))->select();
        $view_id = 0;
        foreach ($data as $k => $v) {
            $view_id = $v['view_id'];
            if( empty($v['condition'])) continue;
            $condition = explode(',',trim($condition,','));
            if(in_array(session('wxid'),$condition)) {
                $view_id = $v['view_id'];break;
            }            
        }
        $copy = M('yx_config_viewpro')->where(array('id' => $view_id))->order('id desc')->find();
        $fiexd_copy = D(ucfirst($system).'Appcopyto')->getFiexdCopyHtml($copy['fiexd_copy_id']);
        return $fiexd_copy;
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
        return $this->CreditLineApplyData();
    }
    public function CreditLineApply_fmh(){
        return $this->CreditLineApplyData();
    }
    // 信用额度
    private function CreditLineApplyData(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保信用额度','url' => U('Light/View/View',array('modname'=>'CreditLineApply','system' => 'yxhb')),'modname' => 'yxhbCreditLineApply'),
            array('name' => '建材信用额度','url' => U('Light/View/View',array('modname'=>'CreditLineApply','system' => 'kk')),'modname' => 'kkCreditLineApply'), 
            array('name' => '粉煤灰信用额度','url' => U('Light/View/View',array('modname'=>'CreditLineApply_fmh','system' => 'kk')),'modname' => 'kkCreditLineApply_fmh'), 
        );

        $result['kkCreditLineApply']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CreditLineApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CreditLineApply','system'=>'kk'))
        );
        $result['kkCreditLineApply_fmh']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CreditLineApply_fmh','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CreditLineApply_fmh','system'=>'kk'))
        );
        $result['yxhbCreditLineApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CreditLineApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CreditLineApply','system'=>'yxhb'))
        );   
        return $result;  
    }
    // 临时额度
    public function TempCreditLineApply(){
        return $this->TempCreditLineData();     
    }
    public function TempCreditLineApply_fmh(){
        return $this->TempCreditLineData();
    }
    private  function TempCreditLineData(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保临时额度','url' => U('Light/View/View',array('modname'=>'TempCreditLineApply','system' => 'yxhb')),'modname' => 'yxhbTempCreditLineApply'),
            array('name' => '建材临时额度','url' => U('Light/View/View',array('modname'=>'TempCreditLineApply','system' => 'kk')),'modname' => 'kkTempCreditLineApply'), 
            array('name' => '粉煤灰临时额度','url' => U('Light/View/View',array('modname'=>'TempCreditLineApply_fmh','system' => 'kk')),'modname' => 'kkTempCreditLineApply_fmh'), 
        );

        $result['kkTempCreditLineApply']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'TempCreditLineApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'TempCreditLineApply','system'=>'kk'))
        );
        $result['kkTempCreditLineApply_fmh']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'TempCreditLineApply_fmh','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'TempCreditLineApply_fmh','system'=>'kk'))
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
        return $this->SalesReceiptsApplyData(); 
    }
    // 粉煤灰销售收款
    public function SalesReceiptsApply_fmh(){
        return $this->SalesReceiptsApplyData(); 
    }
    private function SalesReceiptsApplyData(){
        $result = array();
        $result['url'] = array(
            array('name' => '建材销售收款','url' => U('Light/View/View',array('modname'=>'SalesReceiptsApply','system' => 'kk')),'modname' => 'kkSalesReceiptsApply'),
            array('name' => '环保销售收款','url' => U('Light/View/View',array('modname'=>'SalesReceiptsApply','system' => 'yxhb')),'modname' => 'yxhbSalesReceiptsApply'),
            array('name' => '粉煤灰销售收款','url' => U('Light/View/View',array('modname'=>'SalesReceiptsApply_fmh','system' => 'kk')),'modname' => 'kkSalesReceiptsApply_fmh'),
        );
    
        $result['kkSalesReceiptsApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesReceiptsApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply','system'=>'kk')),
        );  
        $result['kkSalesReceiptsApply_fmh'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesReceiptsApply_fmh','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply_fmh','system'=>'kk')),
        ); 
        $result['yxhbSalesReceiptsApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesReceiptsApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesReceiptsApply','system'=>'yxhb')),
        );    
        return $result;   
    }
    // 销售退款
    public function SalesRefundApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '建材销售退款','url' => U('Light/View/View',array('modname'=>'SalesRefundApply','system' => 'kk')),'modname' => 'kkSalesRefundApply'),
            array('name' => '环保销售退款','url' => U('Light/View/View',array('modname'=>'SalesRefundApply','system' => 'yxhb')),'modname' => 'yxhbSalesRefundApply'),
        );

        $result['kkSalesRefundApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesRefundApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesRefundApply','system'=>'kk')),
            // 'fiexd_copy_id' => 'xly',
            // 'copydata' => array(
            //     array('name' =>'许丽颖','url' => 'http://shp.qpic.cn/bizmp/nFsVdIIiaLZsmAv2LEDiaFwmzW1KMfkUIA3IW1c9PibAfaPCICgibpibQNA/'),
            // )
        );  
        $result['yxhbSalesRefundApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'SalesRefundApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'SalesRefundApply','system'=>'yxhb')),
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

    // 发货删除
    public function fh_del_Apply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保发货删除','url' => U('Light/View/View',array('modname'=>'fh_del_Apply','system' => 'yxhb')),'modname' => 'yxhbfh_del_Apply'),
            array('name' => '建材发货删除','url' => U('Light/View/View',array('modname'=>'fh_del_Apply','system' => 'kk')),'modname' => 'kkfh_del_Apply'),
        );

        $result['yxhbfh_del_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'fh_del_Apply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'fh_del_Apply','system'=>'yxhb'))
        );
        $result['kkfh_del_Apply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'fh_del_Apply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'fh_del_Apply','system'=>'kk'))
        );
        return $result;
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
            array('name' => '建材费用开支','url' => U('Light/View/View',array('modname'=>'CostMoney','system' => 'kk')),'modname' => 'kkCostMoney'),
            array('name' => '环保费用开支','url' => U('Light/View/View',array('modname'=>'CostMoney','system' => 'yxhb')),'modname' => 'yxhbCostMoney'),
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

    public function unProCostMoney(){
        $result = array();
        $system = I('get.system');
        $result['url'] = array();
        if($system=='kk') $result['url'][] = array('name' => '建材自动审批费用开支','url' => U('Light/View/View',array('modname'=>'unProCostMoney','system' => 'kk')),'modname' => 'kkunProCostMoney');
        if($system=='yxhb') $result['url'][] = array('name' => '环保自动审批费用开支','url' => U('Light/View/View',array('modname'=>'unProCostMoney','system' => 'yxhb')),'modname' => 'yxhbunProCostMoney');
        
        $result['yxhbunProCostMoney'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'unProCostMoney','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'unProCostMoney','system'=>'yxhb'))
        );   

        $result['kkunProCostMoney'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'unProCostMoney','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'unProCostMoney','system'=>'kk'))
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
            array('name' => '粉煤灰新增总客户'  ,'url' => U('Light/View/View',array('modname'=>'Contract_guest_Apply_fmh','system' => 'kk')),'modname' => 'kkContract_guest_Apply_fmh'),
            array('name' => '粉煤灰新增子客户'  ,'url' => U('Light/View/View',array('modname'=>'Contract_guest_Apply_fmh2','system' => 'kk')),'modname' => 'kkContract_guest_Apply_fmh2'),
            array('name' => '粉煤灰新增备案客户'  ,'url' => U('Light/View/View',array('modname'=>'NewGuestApply_fmh','system' => 'kk')),'modname' => 'kkNewGuestApply_fmh'),
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
        $result['kkContract_guest_Apply_fmh'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'Contract_guest_Apply_fmh','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'Contract_guest_Apply_fmh','system'=>'kk'))
        );
        $result['kkContract_guest_Apply_fmh2'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'Contract_guest_Apply_fmh2','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'Contract_guest_Apply_fmh2','system'=>'kk'))
        );
        $result['kkNewGuestApply_fmh'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'NewGuestApply_fmh','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'NewGuestApply_fmh','system'=>'kk'))
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

    //粉煤灰新增总客户
    public function Contract_guest_Apply_fmh(){
        return $this->GuestApply();
    }

    //粉煤灰新增子客户
    public function Contract_guest_Apply_fmh2(){
        return $this->GuestApply();
    }

    //粉煤灰新增备案客户
    public function NewGuestApply_fmh(){
        return $this->GuestApply();
    }

    // 新增价格合同
    public function  ContractApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保新增合同价格','url' => U('Light/View/View',array('modname'=>'ContractApply','system' => 'yxhb')),'modname' => 'yxhbContractApply'),
            array('name' => '建材新增合同价格','url' => U('Light/View/View',array('modname'=>'ContractApply','system' => 'kk')),'modname' => 'kkContractApply'),
            array('name' => '粉煤灰新增合同价格','url' => U('Light/View/View',array('modname'=>'ContractApply_fmh','system' => 'kk')),'modname' => 'kkContractApply_fmh'),
        );
        $result['yxhbContractApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ContractApply','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ContractApply','system'=>'yxhb')),
            // 'fiexd_copy_id' => 'csl,LanYanHong',
            // 'copydata'      => array(
            //     array('name' =>'陈尚霖','url' => 'http://shp.qpic.cn/bizmp/nFsVdIIiaLZsDpCS95SJ0M61tqqOJG9IriaiaZzaMzJfZ11YZy68j90ow/'),
            //     array('name' =>'兰艳红','url' => 'http://p.qlogo.cn/bizmail/VniaCv2mTAjm0YEuIz9e7snSNgGykQgUGsOiarhyyDO2FHHXyNiapzibicQ/0'),
            // )
        );   
        $result['kkContractApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ContractApply','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ContractApply','system'=>'kk')),
            // 'fiexd_copy_id' => 'csl,LanYanHong',
            // 'copydata'      => array(
            //     array('name' =>'陈尚霖','url' => 'http://shp.qpic.cn/bizmp/nFsVdIIiaLZsDpCS95SJ0M61tqqOJG9IriaiaZzaMzJfZ11YZy68j90ow/'),
            //     array('name' =>'兰艳红','url' => 'http://p.qlogo.cn/bizmail/VniaCv2mTAjm0YEuIz9e7snSNgGykQgUGsOiarhyyDO2FHHXyNiapzibicQ/0'),
            // )
        );
        $result['kkContractApply_fmh'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ContractApply_fmh','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ContractApply_fmh','system'=>'kk')),
            // 'fiexd_copy_id' => 'csl,LanYanHong',
            // 'copydata'      => array(
            //     array('name' =>'陈尚霖','url' => 'http://shp.qpic.cn/bizmp/nFsVdIIiaLZsDpCS95SJ0M61tqqOJG9IriaiaZzaMzJfZ11YZy68j90ow/'),
            //     array('name' =>'兰艳红','url' => 'http://p.qlogo.cn/bizmail/VniaCv2mTAjm0YEuIz9e7snSNgGykQgUGsOiarhyyDO2FHHXyNiapzibicQ/0'),
            // )
        );
        return $result;   
    }

    //粉煤灰新增合同价格
    public function ContractApply_fmh(){
        return $this->ContractApply();
    }

    // 费用开支付款
    public function CostMoneyPay(){
        $result = array();
        $result['url'] = array(
            array('name' => '建材付款','url' => U('Light/View/View',array('modname'=>'CostMoneyPay','system' => 'kk')),'modname' => 'kkCostMoneyPay'),
            array('name' => '环保付款','url' => U('Light/View/View',array('modname'=>'CostMoneyPay','system' => 'yxhb')),'modname' => 'yxhbCostMoneyPay'),
        );

        $result['kkCostMoneyPay'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CostMoneyPay','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CostMoneyPay','system'=>'kk')),    
        );  
        $result['yxhbCostMoneyPay'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CostMoneyPay','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CostMoneyPay','system'=>'yxhb')),
        );    
        return $result;   
    }

    //汪同津测试
    public function CeShi(){
        $result = array();
        $result['url'] = array(
            array('name' => '汪同津测试','url' => U('Light/View/View',array('modname'=>'CeShi','system' => 'kk')),'modname' => 'kkCeShi'),
        );

        $result['kkCeShi'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'CeShi','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'CeShi','system'=>'kk')),
        );

        return $result;
    }

    //查询客户调价
    public function  search_guest_tj(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保查询客户调价','url' => U('Light/View/View',array('modname'=>'search_guest_tj','system' => 'yxhb')),'modname' => 'yxhbsearch_guest_tj'),
            array('name' => '建材查询客户调价','url' => U('Light/View/View',array('modname'=>'search_guest_tj','system' => 'kk')),'modname' => 'kksearch_guest_tj'),
        );

        $result['yxhbsearch_guest_tj'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'search_guest_tj','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'search_guest_tj','system'=>'yxhb')),
        );
        $result['kksearch_guest_tj'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'search_guest_tj','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'search_guest_tj','system'=>'kk')),
        );
        return $result;
    }
    // 添加供应商
    public function  AddGys(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保新增供应商','url' => U('Light/View/View',array('modname'=>'AddGys','system' => 'yxhb')),'modname' => 'yxhbAddGys'),
            array('name' => '建材新增供应商','url' => U('Light/View/View',array('modname'=>'AddGys','system' => 'kk')),'modname' => 'kkAddGys'),
        );

        $result['yxhbAddGys'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'AddGys','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'AddGys','system'=>'yxhb')),
        );
        $result['kkAddGys'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'AddGys','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'AddGys','system'=>'kk')),
        );
        return $result;
    }
    // 新增对账单
    public function  ClientStatementApply(){
        return $this->ClientStatementData();
    }
    public function  ClientStatementApply_fmh(){
        return $this->ClientStatementData();
    }
    // 客户结算
    public function GuestJsApply(){
        return $this->ClientStatementData();
    }

    public function GuestJsApply_fmh(){
        return $this->ClientStatementData();
    }

    private function ClientStatementData(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保新增对账单','url' => U('Light/View/View',array('modname'=>'ClientStatementApply','system' => 'yxhb')),'modname' => 'yxhbClientStatementApply'),
            array('name' => '建材新增对账单','url' => U('Light/View/View',array('modname'=>'ClientStatementApply','system' => 'kk')),'modname' => 'kkClientStatementApply'),
            array('name' => '粉煤灰新增对账单','url' => U('Light/View/View',array('modname'=>'ClientStatementApply_fmh','system' => 'kk')),'modname' => 'kkClientStatementApply_fmh'),
            array('name' => '环保客户结算','url' => U('Light/View/View',array('modname'=>'GuestJsApply','system' => 'yxhb')),'modname' => 'yxhbGuestJsApply'),
            array('name' => '建材客户结算','url' => U('Light/View/View',array('modname'=>'GuestJsApply','system' => 'kk')),'modname' => 'kkGuestJsApply'),
            array('name' => '粉煤灰客户结算','url' => U('Light/View/View',array('modname'=>'GuestJsApply_fmh','system' => 'kk')),'modname' => 'kkGuestJsApply_fmh'),
        );

        $result['yxhbClientStatementApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ClientStatementApply','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ClientStatementApply','system'=>'yxhb')),
        );
        $result['kkClientStatementApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ClientStatementApply','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ClientStatementApply','system'=>'kk')),
        );
        $result['kkClientStatementApply_fmh'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ClientStatementApply_fmh','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ClientStatementApply_fmh','system'=>'kk')),
        );
        $result['yxhbGuestJsApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'GuestJsApply','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'GuestJsApply','system'=>'yxhb')),
        );
        $result['kkGuestJsApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'GuestJsApply','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'GuestJsApply','system'=>'kk')),
        );
        $result['kkGuestJsApply_fmh'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'GuestJsApply_fmh','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'GuestJsApply_fmh','system'=>'kk')),
        );
        return $result;
    }
    // 上传对账回执
    public function ClientStatementUp(){
        return $this->ClientStatementUpData();
    }
    public function ClientStatementUp_fmh(){
        return $this->ClientStatementUpData();
    }
    public function ClientStatementUpData(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保上传对账回执','url' => U('Light/View/View',array('modname'=>'ClientStatementUp','system' => 'yxhb')),'modname' => 'yxhbClientStatementUp'),
            array('name' => '建材上传对账回执','url' => U('Light/View/View',array('modname'=>'ClientStatementUp','system' => 'kk')),'modname' => 'kkClientStatementUp'),
            array('name' => '粉煤灰上传对账回执','url' => U('Light/View/View',array('modname'=>'ClientStatementUp_fmh','system' => 'kk')),'modname' => 'kkClientStatementUp_fmh'),
        );

        $result['yxhbClientStatementUp'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ClientStatementUp','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ClientStatementUp','system'=>'yxhb')),
        );
        $result['kkClientStatementUp'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ClientStatementUp','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ClientStatementUp','system'=>'kk')),
        );
        $result['kkClientStatementUp_fmh'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'ClientStatementUp_fmh','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'ClientStatementUp_fmh','system'=>'kk')),
        );
        return $result;
    }


    // 发票上传
    public function Fpsm(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保发票上传','url' => U('Light/View/View',array('modname'=>'Fpsm','system' => 'yxhb')),'modname' => 'yxhbFpsm'),
            array('name' => '建材发票上传','url' => U('Light/View/View',array('modname'=>'Fpsm','system' => 'kk')),'modname' => 'kkFpsm'),
        );

        $result['yxhbFpsm'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'Fpsm','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'Fpsm','system'=>'yxhb')),
        );
        $result['kkFpsm'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'Fpsm','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'Fpsm','system'=>'kk')),
        );
        return $result;
    }

    // 采购发票上传
    public function CgFpsm(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保采购发票上传','url' => U('Light/View/View',array('modname'=>'CgFpsm','system' => 'yxhb')),'modname' => 'yxhbCgFpsm'),
            array('name' => '建材采购发票上传','url' => U('Light/View/View',array('modname'=>'CgFpsm','system' => 'kk')),'modname' => 'kkCgFpsm'),
        );

        $result['yxhbCgFpsm'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'CgFpsm','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'CgFpsm','system'=>'yxhb')),
        );
        $result['kkCgFpsm'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'CgFpsm','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'CgFpsm','system'=>'kk')),
        );
        return $result;
    }
    // 建材环保欠票通知
    public function Arrears(){
        return $this->ArrearsData();
    }
    public function Arrears_fmh(){
        return $this->ArrearsData();
    }
    public function ArrearsData(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保欠票通知','url' => U('Light/View/View',array('modname'=>'Arrears','system' => 'yxhb')),'modname' => 'yxhbArrears'),
            array('name' => '建材欠票通知','url' => U('Light/View/View',array('modname'=>'Arrears','system' => 'kk')),'modname' => 'kkArrears'),
            //array('name' => '粉煤灰欠票通知','url' => U('Light/View/View',array('modname'=>'Arrears_fmh','system' => 'kk')),'modname' => 'kkArrears_fmh'),
        );

        $result['yxhbArrears'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'Arrears','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'Arrears','system'=>'yxhb')),
        );
        $result['kkArrears'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'Arrears','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'Arrears','system'=>'kk')),
        );
        $result['kkkkArrears_fmh'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'kkArrears_fmh','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'kkArrears_fmh','system'=>'kk')),
        );
        return $result;
    }

    // 调价
    public function GuesttjApply(){
        return $this->GuesttjApplyData();
    }
    public function GuesttjApply_fmh(){
        return $this->GuesttjApplyData();
    }
    public function GuesttjApplyData(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保客户调价','url' => U('Light/View/View',array('modname'=>'GuesttjApply','system' => 'yxhb')),'modname' => 'yxhbGuesttjApply'),
            array('name' => '建材客户调价','url' => U('Light/View/View',array('modname'=>'GuesttjApply','system' => 'kk')),'modname' => 'kkGuesttjApply'),
            array('name' => '粉煤灰客户调价','url' => U('Light/View/View',array('modname'=>'GuesttjApply_fmh','system' => 'kk')),'modname' => 'kkGuesttjApply_fmh'),
        );

        $result['yxhbGuesttjApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'GuesttjApply','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'GuesttjApply','system'=>'yxhb')),
        );
        $result['kkGuesttjApply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'GuesttjApply','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'GuesttjApply','system'=>'kk')),
        );
        $result['kkGuesttjApply_fmh'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'GuesttjApply_fmh','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'GuesttjApply_fmh','system'=>'kk')),
        );
        return $result;
    }

    public function kpsq_Apply()
    {
        $result['url'] = array(
            array('name' => '环保客户开票申请','url' => U('Light/View/View',array('modname'=>'kpsq_Apply','system' => 'yxhb')),'modname' => 'yxhbkpsq_Apply'),
            array('name' => '建材客户开票申请','url' => U('Light/View/View',array('modname'=>'kpsq_Apply','system' => 'kk')),'modname' => 'kkkpsq_Apply')
        );
        $result['yxhbkpsq_Apply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'kpsq_Apply','system' => 'yxhb')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'kpsq_Apply','system'=>'yxhb')),
        );
        $result['kkkpsq_Apply'] = array(
            'process'       => U('Light/Process/ApplyProcess',array('modname'=>'kpsq_Apply','system' => 'kk')),
            'info'          => U('Light/Apply/applyInfo',array('modname'=>'kpsq_Apply','system'=>'kk')),
        );
        return $result;
    }
}