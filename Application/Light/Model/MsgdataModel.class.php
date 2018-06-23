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
    public function GetMessage($mod_name){
        // 工厂模式 -> 模块名决定使用函数
        $obj = new MsgdataModel();
        $func = $mod_name;
        $result = method_exists($obj,$func) ? $this->$func():array();
        $this->activeOn($result);
        return $result;
    }

    /**
     * 对于页面显示当前页的处理
     * @param array 需要处理的输出
     */
    public function activeOn(&$array){
        if(empty($array)) return ;
        $system = I('system');
        $mod_name = I('modname');
        $str = $system.$mod_name;
        foreach($array['url'] as $k => $v){
            if(strcasecmp($v['modname'],$str) == 0) {
                $array['url'][$k]['on'] = 1;
                $array['title'] = $v['name'];
            }
        }
    }

    // 采购付款
    public function CgfkApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '环保原材料采购付款','url' => U('Light/View/View',array('modname'=>'CgfkApply','system' => 'yxhb')),'modname' => 'yxhbCgfkApply'),
            array('name' => '建材原材料采购付款','url' => U('Light/View/View',array('modname'=>'CgfkApply','system' => 'kk')),'modname' => 'kkCgfkApply'), 
        );
        
        $result['kkCgfkApply']   =  array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CgfkApply','system' => 'kk')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CgfkApply','system'=>'kk')),
            'fiexd_copy_id' => 'HeYuanShen,LvHongSheng',
            'copydata' => array(
                array('name' => '何渊深','url' => 'http://p.qlogo.cn/bizmail/ftVl6voFONER5G11Fo1KCLdmnWy3cJKmjBToMnIpCXiaN4lzcdPFDqQ/0'),
                array('name' => '吕红胜','url' => 'http://p.qlogo.cn/bizmail/5FmUowOFcibSEawmsrSg3tXHjHXJEFNza3tSq4VN4sLjaFsaAEfsLwA/0') 
            )
        );
        $result['yxhbCgfkApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'CgfkApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'CgfkApply','system'=>'yxhb')),
            'fiexd_copy_id' => 'HeYuanShen',
            'copydata' => array(
                array('name' => '何渊深','url' => 'http://p.qlogo.cn/bizmail/ftVl6voFONER5G11Fo1KCLdmnWy3cJKmjBToMnIpCXiaN4lzcdPFDqQ/0')
            )
        );   
        return $result;      
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
    public function KfRatioApply(){
        $result = array();
        $result['url'] = array(
            array('name' => '配比通知(矿粉)','url' => U('Light/View/View',array('modname'=>'KfRatioApply','system' => 'yxhb')),'modname' => 'yxhbKfRatioApply'),
            // array('name' => '配比通知(水泥)','url' => U('Light/View/View',array('modname'=>'SnRatioApply','system' => 'kk')),'modname' => 'kkRatioApply'),
            // array('name' => '配比通知(钢渣粉)','url' => U('Light/View/View',array('modname'=>'GzyRatioApply','system' => 'kk')),'modname' => 'kkGzfRatioApply'), 
        );

        // $result['kk']   =  array(
        //     'process' => U('Light/Process/ApplyProcess',array('modname'=>'TempCreditLineApply','system' => 'kk')),
        //     'info'    => U('Light/Apply/applyInfo',array('modname'=>'TempCreditLineApply','system'=>'kk'))
        // );
        $result['yxhbKfRatioApply'] = array(
            'process' => U('Light/Process/ApplyProcess',array('modname'=>'KfRatioApply','system' => 'yxhb')),
            'info'    => U('Light/Apply/applyInfo',array('modname'=>'KfRatioApply','system'=>'yxhb'))
        );   
        return $result;      
    }

}