<?php
namespace Light\Controller;
use  Vendor\Overtrue\Pinyin\Pinyin;
class TestController extends BaseController {
   
    public function Sign()
    {   
        header('Content-Type: text/html; charset=utf-8');
       
        $rootPath = "/data/wwwroot/default/sngl/upload/fy/";

        echo "__FILE__:  ===>  ".__FILE__;
    }
        
    // 流程失效，重置
    public function flow(){
        $wf = A('WorkFlow');
        $result = 147991;
        $salesid = 19;
        $res = $wf->setWorkFlowSV('fh_refund_Apply', $result, $salesid, 'yxhb');
    }

    // 重置 发票上传未同步更新spsm字段
    public function updateFpsm(){
        // 发票上传
        $map = array(
            'stat' => 1,
            'type' => 2,
        );
        $data = M('yxhb_fpsm')->where($map)->select();
        // 采购申请单
        $idStr = '';
        foreach($data as $v){
            $idStr .= $v['dh'].',';
        }
        $map = array(
            'id' => array('in',trim($idStr,',') ),
        );
        $data = M('yxhb_cgfksq')->where($map)->select();
        // 采购费用
        foreach( $data as $v){
            $map = array(
                'sqdh' => $v['dh'] ,
                'fpsm' => 2,
            );
            $res = M('yxhb_feecg')->where($map)->find();
            if(empty($res)) continue;
            dump($res);
            // 发票状态重置
            $res = M('yxhb_feecg')->where(array('id' => $res['id']))->setField('fpsm',1);
            dump(M()->_sql());
        }
    }
 
} 


