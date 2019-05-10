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
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'环保矿粉配比通知',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['cretime']))  ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
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
                                     'value'=>round($res['enterid'],0).'#',
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
        $scale = json_decode($data['scale']);
        $html = "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨内'>";
        if(!empty($data['scale1']) ) {
            $proportion1 = ceil($data['proportion1']) == $data['proportion1']?ceil($data['proportion1']): $data['proportion1'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale1']}：{$proportion1}%'>";
        }
        if(!empty($data['scale2']) ) {
            $proportion2 = ceil($data['proportion2']) == $data['proportion2']?ceil($data['proportion2']): $data['proportion2'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale2']}：{$proportion2}%'>";
        }
        if(!empty($data['scale3'])) {
            $proportion3 = ceil($data['proportion3']) == $data['proportion3']?ceil($data['proportion3']): $data['proportion3'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale3']}：{$proportion3}%'>";
        }
        if(!empty($data['scale4']) ) {
            $proportion4 = ceil($data['proportion4']) == $data['proportion4']?ceil($data['proportion4']): $data['proportion4'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale4']}：{$proportion4}%'>";
        }
        if(!empty($data['scale5']) ) {
            $proportion5 = ceil($data['proportion5']) == $data['proportion5']?ceil($data['proportion5']): $data['proportion5'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale5']}：{$proportion5}%'>";
        }

        if(!empty($scale[5]) && !empty($scale[5]->name)) {
            $proportion5 = ceil($scale[5]->name) == $scale[5]->name?ceil($scale[5]->name): $scale[5]->name;
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$scale[5]->value}：{$scale[5]->name}%'>";
        }
        if(!empty($scale[6]) && !empty($scale[6]->name)) {
            $proportion5 = ceil($scale[6]->name) == $scale[6]->name?ceil($scale[6]->name): $scale[6]->name;
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$scale[6]->value}：{$scale[6]->name}%'>";
        }
        if(!empty($scale[7]) && !empty($scale[7]->name)) {
            $proportion5 = ceil($scale[7]->name) == $scale[7]->name?ceil($scale[7]->name): $scale[7]->name;
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$scale[7]->value}：{$scale[7]->name}%'>";
        }
        $out_scale = json_decode($data['out_scale']);
        // if(!empty($out_scale[0]->name)) {
        //     $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨外'>";
        //     foreach($out_scale as $val){
        //         $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$val->value}：{$val->name}%'>";
        //     }
            
        // }
        $temp_html = '';
        $content   = ''; 
        foreach($out_scale as $k =>$v){
            $name      = $v->name;
            if($name){
                $temp_html = "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨外'>"; 
                $content  .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$v->value}：{$v->name}%'>";
            } 
        }
        $html .= $temp_html.$content;
        $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='指标'>";

        $tailover = ceil($data['tailover']) == $data['tailover']?ceil($data['tailover']): $data['tailover'];
        $moisture = ceil($data['moisture']) == $data['moisture']?ceil($data['moisture']): $data['moisture'];
        $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='筛余：≤ {$tailover}% (45μm)'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='比表：{$data['bibiao']} - {$data['bbsx']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='水份：≤ {$moisture}%'> ";
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
        return $this->field(true)->where($map)->setField('state',0);
    }

    /**
     * 拒收
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function refuseRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('state',3);
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
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['cretime']))  ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产时间：',
                                     'value'=> date('m-d H:i',strtotime($res['date'].' '.$hour.':'.$scfz)) ,
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
                                     'value'=>round($res['enterid'],0).'#',
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
        $hour = $res['hour']>9?$res['hour']:'0'.$res['hour'];
        $scfz = $res['scfz']>9?$res['scfz']:'0'.$res['scfz'];

        $result = array(
            'first_title'    => '生产时间',
            'first_content'  => $res['date'].' '.$hour.':'.$scfz,
            'second_title'   => '生产品种',
            'second_content' => $res['variety'],
            'third_title'    => '相关说明',
            'third_content'  => $res['bz']?$res['bz']:'无',
            'stat'           => $res['state'],
            'applyerName'    => $res['name'],
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
        $seek   = A('Seek');
        $config = $seek->config_api($type,'KfRatioApply');
        $bibiaoArr = explode('|',$config[1]['value']);
     
        if($count != 100)   return array('code' => 404,'msg' => '总配置值须为100%，请检查后输入');
        $insert = array(
            'bbsx'    => $bibiaoArr[1],
            'scx'     => $scx,  
            'date'    => Date('Y-m-d',strtotime($datetime)),
            'hour'    => Date('H',strtotime($datetime)),
            'variety' => $type,
            'scale1'  => $scale[0]['name']?$scale[0]['value']:0 , 'proportion1' => $scale[0]['name']?$scale[0]['name']:0 ,
            'scale2'  => $scale[1]['name']?$scale[1]['value']:0 , 'proportion2' => $scale[1]['name']?$scale[1]['name']:0 ,
            'scale3'  => $scale[2]['name']?$scale[2]['value']:0 , 'proportion3' => $scale[2]['name']?$scale[2]['name']:0 ,
            'scale4'  => $scale[3]['name']?$scale[3]['value']:0 , 'proportion4' => $scale[3]['name']?$scale[3]['name']:0 ,
            'scale5'  => $scale[4]['name']?$scale[4]['value']:0 , 'proportion5' => $scale[4]['name']?$scale[4]['name']:0 ,
            'tailover'=> $config[0]['value'],
            'scale'   => json_encode($scale),
            'out_scale' => json_encode($out_scale),
            'bibiao'  => $bibiaoArr[0],
            'moisture'=> $config[2]['value'],
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
                'approve_time'  => '0000-00-00 00:00:00',
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
        D('WxMessage')->ProSendCarMessage('yxhb','KfRatioApply',$result,$boss_id,session('yxhb_id'),'QS');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
   

}