<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
   

     public function tt(){
        $sessid = cookie('PHPSESSID');
        $system = I('get.systempath');
        if( !$system ) return;
    
        $path   = "/www/web/default/{$system}/data/sessions/sess_{$sessid}";
        $cont   = file_get_contents($path);
        dump($cont);
        $cont   = explode(';',$cont);
        $cont   = array_filter($cont);
        $tmp    = array();
        foreach ($cont as $k => $v) {
            if( empty($v) ) continue;
            $v   = explode('|',$v); 
            $key = $v[0];
            $arr = explode(':',$v[1]);
            $val = $arr[2];
            $tmp[$key] = trim($val,'"'); 
        }
    
        $wxid = str_replace('OvSQq4967K','',$tmp['VioomaUserID']);
        if($system == 'sngl'){
            $main_system = 'kk';
        }else{
            $main_system = 'yxhb';
        }
        $user =  M($main_system.'_boss')->field("id")->where(array('boss' => $wxid))->find();
        $id = $user['id'];
        D($main_system.'Boss')->login($id);
        return true;
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


