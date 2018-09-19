<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
 
    public function copyTo(){
        header("Content-type:text/html;charset=utf-8");
        $id     = 985;
        $seek =  D('Seek');
        $tab = $seek->getAppTable();

        $arr = array();
        foreach ($tab as $k => $v ) {
            $where = array(
                'a.app_stat' => 0,
                'b.'.$v['stat'] => $v['submit']['stat'] 
            );
            // 采购付款特殊处理
            if($v['mod_name'] == 'CgfkApply'){
                $where['a.mod_name'] = array(array('eq',$v['mod_name']),array('eq','PjCgfkApply'),array('eq','WlCgfkApply'),'OR');
            }else{
                $where['a.mod_name'] = $v['mod_name'];
            }
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']} b on a.aid=b.{$v['id']}")
                    ->field("a.aid,a.per_id,a.mod_name,a.per_name, 1 as {$v[system]}")
                    ->where($where)
                    ->select();
            $arr = array_merge($res,$arr);
        }
        dump($arr);
        // $res = D('YxhbKfRatioApply','Logic')->record($id );
        // $scale = json_decode($res['scale']);
        // dump($scale);
    } 
 
   
} 

