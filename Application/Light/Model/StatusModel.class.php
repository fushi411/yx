<?php
namespace Light\Model;
use Think\Model;

/**
 * 部门模型
 * @author 
 */

class StatusModel extends Model {
    
    protected $autoCheckFields = false;
    /**
     * 转换模块状态值
     * @param  int    $stat 现有状态值
     * @param  string $modname  模块名
     * @return int    $stat 转后状态值
     */
    public function transStat($modname,$stat){
        if($stat == 0) return 0;
        $statArr = array(
            'CgfkApply'   => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'WlCgfkApply' => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'PjCgfkApply' => array('4' =>2 ,'3' => 2 ,'2' => 1),
            'CostMoney'   => array('5' =>2 ,'4' => 1 ,'2' => 1),
        );

        if(!$statArr[$modname]) return 0;
        return $statArr[$modname][$stat];
    }

    /**
     * 获取当前审批的状态
     * @param int $aid  模块审批id
     * @param string $mod 模块名
     * @return int $stat 当前审批状态值
     */
    public function getNowStatus($aid,$mod){
        // 先转换状态值
        
    }

}
