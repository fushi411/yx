<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
    protected $trueTableName = 'kk_appflowtable';
    public function copyTo(){
        header("Content-type:text/html;charset=utf-8");
        //echo 1;
        $mod_name = 'TempCreditLineApply';
        $aid      = 494;
        $proInfo = array();
        $temp    = array();
        $res = $this->field('pro_name,pro_id,per_name,per_id,stage_id,point')->where(array('pro_mod'=>$modname, 'stat'=>1))->order('stage_id asc')->select();
        $boss = D('kk_boss');
        dump($res);
        foreach($res as $key => $value){
            $k = $value['stage_id']-1;
            $temp[$k][] = $value;
        }
        foreach ($temp as $key => $value) {
            foreach($value as $k => $val){
                
                $procIDList = $val['per_id'].",";
                $procNameList = $val['per_name'].",";
                $wxid = $boss->getWXFromID($val['per_id']);
                $avatar = $boss->getAvatar($val['per_id']);
                $procWXIDList = $wxid['wxid'].",";
                $sign = count($value) < 2?'':1;
                $proInfo[] = array('id'=>$val['per_id'],
                                   'realname'=>$val['per_name'],
                                   'wxid'=>$wxid,
                                   'avatar'=>$avatar,
                                   'sign'  => $sign
                                  );
                
            }
        }
        $firstProc = $proInfo[0];
        return array('first'=>$firstProc,
                     'proInfo'=>$proInfo,
                     'res'=>$res,
                     'title'=>$res[0]['pro_name']
                    );
        
    } 
  

    

    // http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=kk&aid=748&modname=CgfkApply
   
} 


