<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
   

    public function tt(){
        header("Content-type:text/html;charset=utf-8");
        
        $data = M('yxhb_tempcreditlineconfig')->where(array('is_del' => 1))->select();
        echo 1;
        foreach($data as $res){
            $day = str_replace('天','',$res['yxq']);
            if(strtotime($res['dtime'].' +'.$day.' day')>time()){
                dump($res['id']);
            }
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


