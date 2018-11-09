<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
   

    public function tt(){
        header("Content-type:text/html;charset=utf-8");
        $system = 'kk';
        $user_id =553;
        $money = 2;
        $lineArr = array(20000,50000,100000);

         // 有效期校验
         $res     = M($system.'_tempcreditlineconfig')
                    ->field('date,dtime,stat,yxq')
                    ->where(array('clientid' => $user_id ,'stat' => array('neq',0),'line' => $lineArr[$money]))
                    ->order('date desc')
                    ->find();
        dump($res);
        //  为过审的
        if($res['stat'] == 2){
            $app_stat = M($system.'_appflowproc')
                        ->where(array('aid' =>$res['id'],'mod_name' => 'TempCreditLineApply' ,'app_stat' => 1))
                        ->find();
                        dump($app_stat);
            if(empty($app_stat)) return array('code' => 404,'msg' => '已有一条同等额度申请在审批');
        }
    
   } 



    public function arraySort($arr, $keys, $type = 'asc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v){
            $keysvalue[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
           $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
} 


