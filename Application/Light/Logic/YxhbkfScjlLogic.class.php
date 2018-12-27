<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbkfScjlLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_gckz';

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
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'环保生控记录(矿粉)',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
         $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['jlsj'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );                            
        $result['content'][] = array('name'=>'生产时间：',
                                     'value'=> date('Y-m-d H:i',strtotime($res['scrq'].' '.$res['scsj'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产品种：',
                                     'value'=>$res['scpz'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产线：',
                                     'value'=>$res['scx'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'入库号：',
                                     'value'=>$res['kh'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'45μm：',
                                     'value'=> $res['xd']?$res['xd']:'无',
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'比表：',
                                     'value'=>$res['bbmj'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $sf = $this->getSfInfo($res);
        if($sf[0] == 1){
            $result['content'][] = array('name'=>'水份详情：',
                                    'value'=>$sf[1],
                                    'type'=>'text',
                                    'color' => 'black'
                                );
        }
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['bz']?$res['bz']:'无',
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['jyry']);
        $result['applyerName'] = $res['jyry'];
        $result['stat'] = $res['STAT'];
        return $result;
    }

    // 水份数据详情
    public function getSfInfo($data){
        $tempArr = array(
            'rmbgszsf' => '宝钢水渣',
            'rmkzsf'   => '矿渣'     ,
            'krlzsf'   => '矿热炉渣',
            'rmcxpzsf' => '电厂炉渣',
            'rmzlzsf'  => '精炼炉渣',
            'rmshssf'  => '石灰石',
            'rmtwzsf'  => '铜尾渣'
        );
        $flag = 0;
        $html = "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='磨内(单位:%)'>";
        foreach( $tempArr as $k => $v){
            if($data[$k] == 0 || $data[$k] == 99999 ) continue;
            $value = $v.':'.number_format($data[$k],2,'.',',').'%';
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='$value'>";
            $flag = 1;
        }
        $cpsf = $data["cpsf"] ?$data["cpsf"].'%':'';
        $qtwlsf = $data["qtwlsf"]?$data["qtwlsf"].'%':'';

        if( $cpsf !=0 || $qtwlsf != 0){
            $html .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='成品(单位:%)'>";
        }
        if($qtwlsf != 0 ) $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='煤水份:{$qtwlsf}'>";
        if($cpsf != 0)   $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='成品水份:{$cpsf}'>";
       
        return array($flag,$html);
    }
     /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['jlsj'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产时间：',
                                     'value'=>date('m-d H:i',strtotime($res['scrq'].' '.$res['scsj'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'生产品种：',
                                     'value'=>$res['scpz'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'生产线：',
                                     'value'=>$res['scx'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'入库号：',
                                     'value'=>$res['kh'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'45μm：',
                                     'value'=>$res['xd'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'比表：',
                                     'value'=>$res['bbmj'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['jyry'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['bz']?$res['bz']:'无',
                                     'type'=>'text'
                                    );
        return $result;
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
        return $this->field(true)->where($map)->setField('STAT',0);
    }
    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('jyry');
    }

        /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            'sales'   => $res['jyry'],
            'title2'  => '比表',
            'approve' => $res['bbmj'],
            'notice'  => $res['bz'],
            'date'    => $res['scrq'],
            'title'   => '生产品种',
            'name'    => $res['scpz'], 
            'modname' => 'kfScjl',
            'stat'    => $res['STAT']
        );
        return $result;
    }
    
    /**
    * 获取客户用户 各项余额
    * @param int $client_id 客户id
    * @return array $res 各项余额  
    */
    public function  getProductInfo(){
        $datetime = I('post.time');
        $wlid     = I('post.wlid');
        $map      = array();
        if($wlid){
            $map['id'] = $wlid;
        }else{
            $map['pruduct_date'] = array('elt',$datetime);
        }
        

        $field = 'ku,product,scx';
        $res = M('yxhb_materiel')->field($field)->where($map)->order('pruduct_date desc')->find();
        return $res;
    } 
    // 检查数据是否已经存在
    public function check_is_set(){
        // 数据校验 生产时间校验
        $datetime = I('post.datetime');
        $sign     = I('post.sign');
        $type     = I('post.type');
        $scx      = I('post.scx');

        $kh       = I('post.kh');
        $xd       = I('post.xd');
        $bb       = I('post.bb');
        $text     = I('post.text');
        $datetime = I('post.datetime');
        $copyto_id = I('post.copyto_id');
        $sf        = I('post.scale');
        $cpsf      = I('post.out_scale');

        $map = array(
            'scrq' => date('Y-m-d',strtotime($datetime)),
            'scx'  => $scx.'生产线',
            'scsj' => date('H:i:s',strtotime($datetime)),
            'scpz' => $type,
            'kh'   => $kh.'库',
            'STAT' => array('neq',0)
        );
        $res = M('yxhb_gckz')->where($map)->find();
        if(!empty($res)){
            $str = $res['STAT'] == 1 ?'已有一条记录通过审批':'已有一条记录在审核中';
            return array('code' => 404 , 'msg' =>  $str);
        }
        return array('code' => 200 );
    }

    // 添加数据
    public function submit(){
        // 数据校验 生产时间校验
        $datetime = I('post.datetime');
        $sign     = I('post.sign');
        $type     = I('post.type');
        $scx      = I('post.scx');
        $kh       = I('post.kh');
        $xd       = I('post.xd');
        $bb       = I('post.bb');
        $text     = I('post.text');
        $datetime = I('post.datetime');
        $copyto_id = I('post.copyto_id');
        $sf        = I('post.scale');
        $cpsf      = I('post.out_scale');
        // 先检验  生产品种 生产线 入库号
        if( !$datetime ||!$type || !$scx || !$kh) return array('code' => 404 , 'msg' => '请截图联系管理员');
        // 是否改时间段记录  scx='{$scx}' AND scrq='{$scrq}' AND scsj='{$scsj}' AND scpz='{$scpz}' AND kh='{$kh}' AND stat>0"
        $map = array(
            'scrq' => date('Y-m-d',strtotime($datetime)),
            'scx'  => $scx.'生产线',
            'scsj' => date('H:i:s',strtotime($datetime)),
            'scpz' => $type,
            'kh'   => $kh.'库',
            'STAT' => array('neq',0)
        );
        // 细度校验
        if($xd){
            if($xd > 20 || $xd <0) return array('code' => 404 , 'msg' => '细度范围为0-20');
        }
        // 比表
        if( $bb > 600 || $bb < 300) return array('code' => 404 , 'msg' => '比表范围为300-600');
        // 防止重复提交
        if(!M('yxhb_gckz')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $add = $map;
        $add['STAT'] = 1;
        $add['xd']   = $xd;
        $add['bbmj'] = $bb;
        $add['bz']   = $text;
        $add['jyry']   = session('name');
        $add['jlsj'] = date('Y-m-d H:i:s',time());
        if(!empty($cpsf)){
            $add['cpsf'] = $cpsf[0]['name'];
            $add['qtwlsf'] = $cpsf[1]['name'];
        }
        $sfData = $this->sfDataMake($sf);
        $add = array_merge($add,$sfData);
        $result = M('yxhb_gckz')->add($add);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！','');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'kfScjl', $result);
        }
        // 报警接口
        $name = session('wxid');
        $post_data = array(
            'user_name' => $name,
            'auth'      => data_auth_sign($name),
            'date'      => date('Y-m-d',strtotime($datetime)),
            'loginip'   => get_client_ip(0),
            'scpz'      => $type,
            'cpsf'      => $cpsf[1]['name'],
            'scsj'      => date('H:i:s',strtotime($datetime)),
            'id'        => $result,
            'xd'        => $xd,
            'scx'       => $scx.'生产线',
          );
        $res = send_post('http://www.fjyuanxin.com/sngl/AlarmInfoApi.php', $post_data);
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
        
    }

     // $sign_id   = I('post.sign');
        // $sign_arr  = explode(',',$sign_id);
        // $sign_arr  = array_filter($sign_arr);// 去空
        // $sign_arr  = array_unique($sign_arr); // 去重
        // 签收通知
        // $all_arr = array();
        // foreach($sign_arr as $val){
        //     $per_name = M('yxhb_boss')->where(array('wxid'=>$val))->Field('name,id')->find();
        //     $data = array(
        //         'pro_id'        => 31,
        //         'aid'           => $result,
        //         'per_name'      => $per_name['name'],
        //         'per_id'        => $per_name['id'],
        //         'app_stat'      => 0,
        //         'app_stage'     => 1,
        //         'app_word'      => '',
        //         'time'          => date('Y-m-d H:i',time()),
        //         'approve_time'  => '0000-00-00 00:00:00',
        //         'mod_name'      => 'kfScjl',
        //         'app_name'      => '签收',
        //         'apply_user'    => '',
        //         'apply_user_id' => 0, 
        //         'urge'          => 0,
        //     );
        //     $all_arr[]=$data;
        // }
        // $boss_id = implode('|',$sign_arr);
        // M('yxhb_appflowproc')->addAll($all_arr);
        // $this->sendMessage($result,$boss_id);
    /**
     * 通知信息发送
     * @
     */
    public function sendMessage($apply_id,$boss){
        $system = 'yxhb';
        $mod_name = 'kfScjl';
        $logic = D(ucfirst($system).$mod_name, 'Logic');
        $res   = $logic->record($apply_id);
        $systemName = array('kk'=>'建材', 'yxhb'=>'环保');
        // 微信发送
        $WeChat = new \Org\Util\WeChat;
        
        $descriptionData = $logic->getDescription($apply_id);
     
        $title = '生控记录(矿粉)(签收)';
        $url = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=".$system."&aid=".$apply_id."&modname=".$mod_name;
      
        $applyerName='('.$res['rdy'].'提交)';
        $description = "您有一个流程需要签收".$applyerName;
        $receviers = "HuangShiQi|".$boss;
        foreach( $descriptionData as $val ){
            $description .= "\n{$val['name']}{$val['value']}";
        }
        $agentid = 15;
        $WeChat = new \Org\Util\WeChat;
        $info = $WeChat->sendCardMessage($receviers,$title,$description,$url,$agentid,$mod_name,$system);
    }

    // 水份数据
    protected function sfDataMake($sfData){
        $temp = array(
            '宝钢水渣' => 'rmbgszsf',
            '矿渣'     => 'rmkzsf',
            '矿热炉渣' => 'krlzsf',
            '电厂炉渣' => 'rmcxpzsf',
            '精炼炉渣' => 'rmzlzsf',
            '石灰石'   => 'rmshssf',
            '铜尾渣'   => 'rmtwzsf',
        );
        $res = array();
        foreach($sfData as $v){
            if( $v['name'] != 0 ) $res[$temp[$v['value']]] = $v['name'];
        }
        return $res;
    }



}