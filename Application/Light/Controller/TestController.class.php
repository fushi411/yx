<?php
namespace Light\Controller;
use  Vendor\Overtrue\Pinyin\Pinyin;
class TestController extends BaseController {
   
    public function Sign()
    {   
        header('Content-Type: text/html; charset=utf-8');
        $config = $this->config(); 
        $result = array();
        $sub = array();
        
        foreach ($config as $k => $v ) {
            $map = array(
                'a.app_stat'                      => 1,
                "{$v['table_name']}.{$v['stat']}" => $v['submit']['stat'],
                'a.mod_name'                      => $v['mod_name']
            );
            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field("a.aid")
                    ->where($map)
                    ->select();  
            $aid = '';
            foreach($res as $val){
                $aid .= ",{$val['aid']}";
            };
            // $aid 退审记录
            $id=session($v['system'].'_id');
            $aid               = trim($aid,',');
            $map['a.app_stat'] = 0;
            $map['a.per_id']   = $id;
            $map['a.aid']      = array('not in',$aid);

            $res = M($v['system'].'_appflowproc a')
                    ->join("{$v['table_name']}  on a.aid={$v['table_name']}.{$v['id']}")
                    ->field($v['copy_field'])
                    ->where($map)
                    ->select(); 
            dump(M()->_sql());     
            if(!empty($res)){
                foreach($res as $key => $val){
                    $res[$key]['system']  = $v['system'];
                    $res[$key]['mod']     = $v['mod_name'];
                    $res[$key]['modname'] = $v['toptitle'];
                }
            }
            $sub = array_merge($sub,$res);
        }
        dump($sub);
        return $sub;
    }
    public function config(){
        return D('Seek')->configSign();
    }
   


} 


