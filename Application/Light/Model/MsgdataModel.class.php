<?php
namespace Light\Model;
use Think\Model;

/**
 * 页面数据逻辑模型
 * @author 
 */

class MsgdataModel extends Model {
    // 实际表名
    protected $trueTableName = 'action';
    
    /**
     * 获取所需的页面跳转信息、审批信息
     * @param  string $mod_name 模块信息
     * @return array  $result   
     */
    public function GetMessage($mod_name){
        // 工厂模式 -> 模块名决定使用函数
        $obj = new MsgdataModel();
        $func = $mod_name;
        $result = method_exists($obj,$func) ? $this->$func():array();
        return $result;
    }

    // 
    public function CgfkApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保原材料采购付款','url' => U('Light/Cgfk/yxhbCgfk'),'modname' => 'yxhbCgfkApply'),
            array('name' => '建材原材料采购付款','url' => U('Light/Cgfk/kkCgfk'),'modname' => 'kkCgfkApply'), 
        );
        
        $result['kk']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CgfkApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CgfkApply','system'=>'kk'))
        );
        $result['yxhb'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CgfkApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CgfkApply','system'=>'yxhb'))
        );   
        return $result;      
    }

        

}