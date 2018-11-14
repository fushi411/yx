<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
   

    public function tt(){
        header("Content-type:text/html;charset=utf-8");
        $data = M('yxhb_tempcreditlineconfig')->field('id,dtime,yxq')->where(array( 'is_del' => 1))->select();
        foreach($data as $k => $v){
            $day = str_replace('天','',$v['yxq']);
            if(strtotime($v['dtime'].' +'.$day.' day')<time()) dump($v['id'].'过期了');
        }
        $data = M('kk_tempcreditlineconfig')->field('id,dtime,yxq')->where(array( 'is_del' => 1))->select();
        foreach($data as $k => $v){
            $day = str_replace('天','',$v['yxq']);
            if(strtotime($v['dtime'].' +'.$day.' day')<time()) dump($v['id'].'过期了');
        }
        D('WxMessage')->autoDeleteSendMessage();
   } 

   public function rw(){
       $this->display("Task/index");
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


