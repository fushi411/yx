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

    public function Sign()
    {
        header('Content-Type: text/html; charset=utf-8');
        // iconv('gbk','UTF-8',$v['approve']),
        //$data = D('KkAppflowtable')->getConditionStepHtml($modname,$condition);
        $system = 'yxhb';
        $mod_name = 'CostMoney';
        $id = 6260;
        $map = array(
            'pro_mod' => $mod_name,
            'stat'    => 1
        );
        $res = M($system.'_pushlist')->where($map)->select();
        if(!empty($res)){
            if(count($res) == 1){
                $push_id = trim('"',$res[0]['push_name']);
            }else{
                $push_id = '';
                foreach($res as $val){
                    $cons=substr($val['rule'],1,-1);
                    $cons_sub=explode("|",$cons);
                    //数据组装
                    foreach($cons_sub as $c_s){
                        $cons_sub_sub=explode(":",$c_s);
                        $cons_array[$cons_sub_sub[0]]=$cons_sub_sub[1];
                    }
                    $con_query="SELECT 1 from ".$cons_array['table']." WHERE ".$cons_array['table']."=$id ".$cons_array['conditions'];
                    $res = M()->query($con_query);
                    $count=count($res);
                    if($count == 1) {
                        $push_id = $val['push_name'];
                        break;
                    }
                }
            }
            $del_arr = M($system.'_appflowproc a')->join($system.'_boss b on b.id=a.per_id')->field('b.wxid')->where(array('a.aid' => $id,'a.mod_name' => $mod_name))->order('a.app_stage desc')->find();
            $del_id = $del_arr['wxid'];
            $res = array_search($del_id,$push_id);
            if($res !== false){
                $push_id = str_replace($del_id,'',$push_id);
                $push_id = explode(',',$push_id);
                $push_id = implode(',',array_filter($push_id));
            }
            return $push_id;
//            dump($push_id);
            //D($system.'Appcopyto')->copyTo($push_id, $mod_name, $id,2);
        }
    }

}