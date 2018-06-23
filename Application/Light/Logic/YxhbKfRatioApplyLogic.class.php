<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbKfRatioApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_assay';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $hour = $res['hour']>9?$res['hour']:'0'.$res['hour'];
        $scfz = $res['scfz']>9?$res['scfz']:'0'.$res['scfz'];
        $result['content'][] = array('name'=>'生产时间：',
                                     'value'=>$res['date'].' '.$hour.':'.$scfz,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产品种：',
                                     'value'=>$res['variety'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产线：',
                                     'value'=>$res['scx'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'入库号：',
                                     'value'=>round($res['tailover'],0).'#',
                                     'type'=>'number',
                                     'color' => 'black'
                                    );

        $html = $this->makeDeatilHtml($res);
        $result['content'][] = array('name'=>'配比详情：',
                                     'value'=> $html,
                                     'type'=>'number',  
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['bz']?$res['bz']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
       
        $result['imgsrc'] = '';
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['name']);
        $result['applyerName'] = $res['name'];
        $result['stat'] = $res['state'];
        return $result;
    }

    /**
     * 配比详情html生成
     * @param array   $data 配比数据
     * @return string $html 
     */
    public function makeDeatilHtml($data){
        $html = "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨内'>";
        if(!empty($data['scale1']) ) {
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale1']}：{$data['proportion1']}%'>";
        }
        if(!empty($data['scale2']) ) {
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale2']}：{$data['proportion2']}%'>";
        }
        if(!empty($data['scale3'])) {
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale3']}：{$data['proportion3']}%'>";
        }
        if(!empty($data['scale4']) ) {
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale4']}：{$data['proportion4']}%'>";
        }
        if(!empty($data['scale5']) ) {
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale5']}：{$data['proportion5']}%'>";
        }

        $out_scale = json_decode($data['out_scale']);
        if(!empty($out_scale[0]->name)) {
            $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨外'>";
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$out_scale[0]->value}：{$out_scale[0]->name}%'>";
        }
        $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='参数'>";
        $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='筛余：≤ {$data['tailover']}% (45μm)'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='比表：{$data['bibiao']} - {$data['bbsx']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='水份：≤ {$data['moisture']}%'> ";
        return $html;
    } 


    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
    }

         /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $hour = $res['hour']>9?$res['hour']:'0'.$res['hour'];
        $scfz = $res['scfz']>9?$res['scfz']:'0'.$res['scfz'];
        $result[] = array('name'=>'生产时间：',
                                     'value'=>$res['date'].' '.$hour.':'.$scfz,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产品种：',
                                     'value'=>$res['variety'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'生产线：',
                                     'value'=>$res['scx'],
                                     'type'=>'string'
                                    );        
        $result[] = array('name'=>'入库号：',
                                     'value'=>round($res['tailover'],0).'#',
                                     'type'=>'number'
                                    );                          
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['bz']?$res['bz']:'无',
                                     'type'=>'string'
                                    );
        return $result;
    }
    /**
     * 通知详情
     */
    public function getDetailStr($data){

    }
    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('rdy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            'sales'   => $res['rdy'],
            'approve' => number_format($res['fkje'],2,'.',',')."元",
            'notice'  => $res['zy'],
            'date'    => $res['zd_date'],
            'stat'    => $res['stat']
        );
        return $result;
    }

    /**
     * 矿粉提交
     */
    public function kfsubmit(){
        $scale     = I('post.scale');
        $out_scale = I('post.out_scale');
        $type      = I('post.type');
        $scx       = I('post.scx');
        $kh        = I('post.kh');
        $text      = I('post.text');
        $datetime  = I('post.datetime');
        $copyto_id = I('post.copyto_id');
        $sign_id   = I('post.sign');
        $sign_arr  = explode(',',$sign_id);
        $sign_arr  = array_filter($sign_arr);// 去空
        $sign_arr  = array_unique($sign_arr); // 去重
        if(empty($type)) return array('code' => 404,'msg' => '请选择生产品种');
        if(empty($scx))  return array('code' => 404,'msg' => '请选择生产线'  );
        if(empty($kh))   return array('code' => 404,'msg' => '请选择生产库号');
        $count = 0;
        foreach($scale as $val){
            if($val['value'] == '脱硫灰' || $val['value'] == '激发剂' ) continue;
            $num = $val['name']?$val['name']:0;
            $count += $val['name'];
        }

        if($count != 100)   return array('code' => 404,'msg' => '总配置值须为100%，请检查后输入');
        $insert = array(
            'bbsx'    => 480,
            'scx'     => $scx,  
            'date'    => Date('Y-m-d',strtotime($datetime)),
            'hour'    => Date('H',strtotime($datetime)),
            'variety' => $type,
            'scale1'  => $scale[0]['name']?$scale[0]['value']:0 , 'proportion1' => $scale[0]['name']?$scale[0]['name']:0 ,
            'scale2'  => $scale[1]['name']?$scale[1]['value']:0 , 'proportion2' => $scale[1]['name']?$scale[1]['name']:0 ,
            'scale3'  => $scale[2]['name']?$scale[2]['value']:0 , 'proportion3' => $scale[2]['name']?$scale[2]['name']:0 ,
            'scale4'  => $scale[3]['name']?$scale[3]['value']:0 , 'proportion4' => $scale[3]['name']?$scale[3]['name']:0 ,
            'scale5'  => $scale[4]['name']?$scale[4]['value']:0 , 'proportion5' => $scale[4]['name']?$scale[4]['name']:0 ,
            'tailover'=> 5,
            'scale'   => json_encode($scale),
            'out_scale' => json_encode($out_scale),
            'bibiao'  => 430,
            'moisture'=> 1,
            'enterid' => $kh,
            'state'    => 2,
            'scfz'    => Date('i',strtotime($datetime)),
            'name'    => session('name'),
            'bz'      => $text,
            'cretime' => date('Y-m-d H:i',time())
        );
        if(!M('yxhb_assay')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('yxhb_assay')->add($insert);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'KfRatioApply', $result);
        }
        // 签收通知
        $all_arr = array();
        foreach($sign_arr as $val){
            $per_name = M('yxhb_boss')->where(array('wxid'=>$val))->Field('name,id')->find();
            $data = array(
                'pro_id'        => 31,
                'aid'           => $result,
                'per_name'      => $per_name['name'],
                'per_id'        => $per_name['id'],
                'app_stat'      => 0,
                'app_stage'     => 1,
                'app_word'      => '',
                'time'          => date('Y-m-d H:i',time()),
                'approve_time'  => date('Y-m-d H:i',time()),
                'mod_name'      => 'KfRatioApply',
                'app_name'      => '签收',
                'apply_user'    => '',
                'apply_user_id' => 0, 
                'urge'          => 0,
            );
            $all_arr[]=$data;
        }
        $boss_id = implode('|',$sign_arr);
        M('yxhb_appflowproc')->addAll($all_arr);
        $this->sendMessage($result,$boss_id);
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
    /**
     * 通知信息发送
     * @
     */
    public function sendMessage($apply_id,$boss){
        $system = 'yxhb';
        $mod_name = 'KfRatioApply';
        $logic = D(ucfirst($system).$mod_name, 'Logic');
        $res   = $logic->record($apply_id);
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
        // 微信发送
        $WeChat = new \Org\Util\WeChat;
        
        $descriptionData = $logic->getDescription($apply_id);
     
        $title = '配比通知(签收)';
        $url = "http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$apply_id."&modname=".$mod_name;
      
        $applyerName='('.$res['name'].'提交)';
        $description = "您有一个流程需要签收".$applyerName;

        $receviers = "wk|HuangShiQi|".$boss;
        foreach( $descriptionData as $val ){
            $description .= "\n{$val['name']}{$val['value']}";
        }
        $agentid = 15;
        $WeChat = new \Org\Util\WeChat;
        $info = $WeChat->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod_name,$system);
    }

}