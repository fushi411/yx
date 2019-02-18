<?php
namespace Light\Logic;
use Think\Controller;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 新增备案客户逻辑模型
 * @author 
 */

class KkCeShiLogic extends Controller {
    // 实际表名

    public function getData(){
        $a = array(
            'a'=>'a',
            'b'=>'b',
            'c'=>'c',
        );

        $b = array('lili', 'data','aaa');
        $key = 0;
        foreach ($a as $k=>$val){
            $a[$k] = $b[$key];
            $key++;
        }
        return $a;
    }
    
}