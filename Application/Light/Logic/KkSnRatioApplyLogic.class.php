<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkSnRatioApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_zlddtz';

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
        $hour = str_replace('时','',$res['scxs']);
        $fz   = str_replace('分','',$res['scfz']);
        $hour = (int)($hour);
        $hour = $hour>9?$hour:'0'.$hour;
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'建材水泥配比通知',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['jlsj'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产时间：',
                                     'value'=>$res['scrq'].' '.$hour.':'.$fz,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产品种：',
                                     'value'=>$res['scpz'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'磨别：',
                                     'value'=>$res['mb'].'#磨',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );

        $html = $this->makeDeatilHtml($res);
        $result['content'][] = array('name'=>'配比详情：',
                                     'value'=> $html,
                                     'type'=>'number',  
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['tznr']?$res['tznr']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
       
        $result['imgsrc'] = '';
        $result['applyerID'] = D('KkBoss')->getIDFromName($res['zby']);
        $result['applyerName'] = $res['zby'];
        $result['stat'] = $res['STAT'];
        return $result;
    }

    /**
     * 配比详情html生成
     * @param array   $data 配比数据
     * @return string $html 
     */
    public function makeDeatilHtml($data){
        $html = "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨内'>";
        if(!empty($data['sl']) ) $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='熟料：{$data['sl']}%'>";
        
        if(!empty($data['sg']) )  $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='石膏：{$data['sg']}%'>";
        
        if(!empty($data['shs']))  $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='石灰石：{$data['shs']}%'>";

        if(!empty($data['kf']) ) {
            $name = $data['mb'] == 1? "矿粉14#":"矿粉16#";
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$name}：{$data['kf']}%'>";
        }

        if(!empty($data['fmh']) ) $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='粉煤灰2：{$data['fmh']}%'>";
        if(!empty($data['wl3']) ) $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='物料3：{$data['wl3']}%'>";
        if(!empty($data['hhczl']) ) $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='混合材总量：{$data['hhczl']}%'>";
        $rksl = $data['sl'] / ($data['sl'] + $data['shs'])*(100-$data['hhczl']);
        $rksl = number_format($rksl,2);  
        $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='入库熟料：{$rksl}%'>";

        // 库号
        $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨尾'>";
        $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='补充库号：{$data['bckh']}'>";
        
        // 指标
        $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='指标'>";
        $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='细度方式： {$data['xdfs']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='细度：≤ {$data['sy']} %'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='SO3：{$data['so']}%'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='比表：≥ {$data['bb']} m2/kg'>";
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
        return $this->field(true)->where($map)->setField('STAT',0);
    }

    /**
     * 拒收
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function refuseRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('STAT',3);
    }

     /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $hour = str_replace('时','',$res['scxs']);
        $fz   = str_replace('分','',$res['scfz']);
        $hour = $hour>9?$hour:'0'.$hour;
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['jlsj'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产时间：',
                                     'value'=>$res['scrq'].' '.$hour.':'.$fz,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产品种：',
                                     'value'=>$res['scpz'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'磨别：',
                                     'value'=>$res['mb'].'#磨',
                                     'type'=>'string'
                                    );        
        $result[] = array('name'=>'补充库号：',
                                     'value'=>$res['bckh'],
                                     'type'=>'number'
                                    );                          
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['tznr']?$res['tznr']:'无',
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
        return $this->field(true)->where($map)->getField('zby');
    }
    /**
     * 我的审批，抄送，提交 所需信息 // 签收区分
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $hour = str_replace('时','',$res['scxs']);
        $fz   = str_replace('分','',$res['scfz']);
        $hour = $hour>9?$hour:'0'.$hour;
        $result = array(
            array('生产时间',$res['scrq'].' '.$hour.':'.$fz),
            array('生产品种',$res['scpz']),
            array('相关说明',$res['tznr']?$res['tznr']:'无')
        );
        return $result;
    }

    /**
     * 矿粉提交
     */
    public function Snsubmit(){
        $scale     = I('post.scale');
        $out_scale = I('post.out_scale');
        $type      = I('post.type');
        $mb        = I('post.scx');
        $kh        = I('post.kh');
        $text      = I('post.text');
        $datetime  = I('post.datetime');
        $copyto_id = I('post.copyto_id');
        $sign_id   = I('post.sign');
        $sign_arr  = explode(',',$sign_id);
        $sign_arr  = array_filter($sign_arr);// 去空
        $sign_arr  = array_unique($sign_arr); // 去重


        if(empty($type)) return array('code' => 404,'msg' => '请选择生产品种');
        if(empty($mb))  return array('code' => 404,'msg' => '请选择磨别'  );
        if(empty($kh))   return array('code' => 404,'msg' => '请选择补充库号');
        $count = 0;
        foreach($scale as $val){
            if($val['value'] == '混合材总量') continue;
            $num = $val['name']?$val['name']:0;
            $count += $val['name'];
        }
        $seek   = A('Seek');
        $config = $seek->config_api($type,'SnRatioApply');
        $so3Arr = str_replace('|','±',$config[2]['value']);
        $so     = explode('|',$config[2]['value']);
        if($count != 100)   return array('code' => 404,'msg' => '总配置值须为100%，请检查后输入');

        
        $insert = array(
            'scrq'   => Date('Y-m-d',strtotime($datetime)),
            'scxs'   => Date('H',strtotime($datetime)).'时',
            'scfz'   => Date('i',strtotime($datetime)).'分',
            'scpz'   => $type ,
            'bb'     => $config[3]['value'],
            'sy'     => $config[1]['value'],
            'so'     => $so3Arr,
            'sl'     => $scale[0]['name']?$scale[0]['name']:0 ,
            'sg'     => $scale[1]['name']?$scale[1]['name']:0 ,
            'shs'    => $scale[2]['name']?$scale[2]['name']:0 ,
            'kf'     => $scale[3]['name']?$scale[3]['name']:0 ,
            'fmh'    => $scale[4]['name']?$scale[4]['name']:0 ,
            'hhczl'  => $scale[6]['name']?$scale[6]['name']:0 ,
            'tznr'   => $text,
            'xdfs'   => $config[0]['value'],
            'zby'    => session('name'),
            'STAT'   => 2,
            'jlsj'   => date('Y-m-d H:i:s',time()),
            'mb'     => $mb,
            'wl3'    => $scale[5]['name']?$scale[5]['name']:0 ,
            'wckf'   => '',
            'sobase' => $so[0],
            'soparm' => $so[1],
            'bckh'   => $out_scale[0]['name']
        );
       
        if(!M('kk_zlddtz')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $result = M('kk_zlddtz')->add($insert);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'SnRatioApply', $result);
        }
        // 签收通知
        $all_arr = array();
        foreach($sign_arr as $val){
            $per_name = M('kk_boss')->where(array('wxid'=>$val))->Field('name,id')->find();
            $data = array(
                'pro_id'        => 31,
                'aid'           => $result,
                'per_name'      => $per_name['name'],
                'per_id'        => $per_name['id'],
                'app_stat'      => 0,
                'app_stage'     => 1,
                'app_word'      => '',
                'time'          => date('Y-m-d H:i',time()),
                'approve_time'  => '0000-00-00 00:00:00',
                'mod_name'      => 'SnRatioApply',
                'app_name'      => '签收',
                'apply_user'    => '',
                'apply_user_id' => 0, 
                'urge'          => 0,
            );
            $all_arr[]=$data;
        }
        $boss_id = implode('|',$sign_arr);
        M('kk_appflowproc')->addAll($all_arr);
        $this->sendMessage($result,$boss_id);
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
    /**
     * 通知信息发送
     * @
     */
    public function sendMessage($apply_id,$boss){
        $system = 'kk';
        $mod_name = 'SnRatioApply';
        $logic = D(ucfirst($system).$mod_name, 'Logic');
        $res   = $logic->record($apply_id);
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
        // 微信发送
        $WeChat = new \Org\Util\WeChat;
        
        $descriptionData = $logic->getDescription($apply_id);
     
        $title = '配比通知(签收)';
        $url = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$apply_id."&modname=".$mod_name;
      
        $applyerName='('.$res['zby'].'提交)';
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