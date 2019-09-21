<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkFpsmLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_fpsm';

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
        $result['content'][] = array('name'=>'系统类型：',
                                     'value'=>'建材发票上传',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $logic  = D('KkCostMoney','Logic');
        $data =  $logic->record($res['dh']);
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($data['jl_date']))  ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        
        $result['content'][] = array('name'=>'执行时间：',
                                     'value'=>date('Y-m-d',strtotime($data['jl_date'])),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        if( $data['nfylx'] == 1 ){
            $fybx = M('kk_feefy3')->where("left(dh,13)='{$data['dh']}'" )->find();
            $fylx = M('kk_fylx')->field('id as val,fy_name as name')->where(array('id' =>$fybx['nfylx']?$fybx['nfylx']:''))->order('id asc')->find(); 
        }else{
            $fylx = M('kk_fylx')->field('id as val,fy_name as name')->where(array('id' =>$data['nfylx']?$data['nfylx']:''))->order('id asc')->find(); 
        }
 
        $result['content'][] = array('name'=>'申请类型：',
                                     'value'=> $data['nfylx'] == 1?'报销费用':'用款费用',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
                                    
        $result['content'][] = array('name'=>'提交人员：',
                                     'value'=> $data['njbr'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $fpsm = array('已到','未到','无票');
        $color = $data['fpsm']-1==1?'#f12e2e':'black';
        // 补到
        $showFpsm = $fpsm[$data['fpsm']-1];
        if($res['stat'] == 1) $showFpsm = '补到';
        $result['content'][] = array('name'=>'发票说明：',
                                     'value'=> $showFpsm,
                                     'type'=>'date',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'费用类型：',
                                     'value'=> $fylx['name']?$fylx['name']:'未提交',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        
        $fkfs = M('kk_fkfs')->field('id as val,fk_name as name')->where(array('id' =>$data['nfkfs']?$data['nfkfs']:''))->order('id asc')->find();
        
        $result['content'][] = array('name'=>'付款方式：',
                                     'value'=>$fkfs['name']?$fkfs['name']:'未提交' ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'用款金额：',
                                     'value'=> "&yen;".number_format(-$data['nmoney'],2,'.',',')."元"  ,
                                     'type'=>'string',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'大写金额：',
                                     'value'=>cny( -$data['nmoney'] ),
                                     'type'=>'number',
                                     'color' => 'black; '
                                    );
        if( $data['nfylx'] == 1 ){
            $textdata = M('kk_fybx')->field('nr as ntext')->where("left(dh,13)='{$data['dh']}'" )->find();
        }else{
            $textdata = M('kk_ykfy')->field('ykyt as ntext,skdw')->where("left(dh,13)='{$data['dh']}'" )->find();
        }
        if($res['nfylx'] != 1){
            $skdw = $data['skdw']?$data['skdw']:'无';
            $result['content'][] = array('name'=>'收款单位：',
                                        'value'=>$textdata['skdw']?$textdata['skdw']:$skdw,
                                        'type'=>'string',
                                        'color' => 'black'
                                        );

            $result['content'][] = array('name'=>'收款账号：',
                                        'value'=>$data['skzh']?$data['skzh']:'无',
                                        'type'=>'string',
                                        'color' => $data['skzh']?'black;font-weight:550;':'black',
                                        );    
            $result['content'][] = array('name'=>'开户银行：',
                                        'value'=>$data['khyh']?$data['khyh']:'无',
                                        'type'=>'string',
                                        'color' => 'black'
                                        );
        }
        $xgsm = $textdata['ntext']."<br/>".$res['text'];
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$xgsm?$xgsm:'无',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );  
        $result['content'][] = array('name'=>'付款记录：',
                                        'value'=>$logic->PayHmtl($res['dh']),
                                        'type'=>'string',
                                        'color' => 'black'
                                        );    

        $imgsrc = explode('|', $res['file']) ;
        $image = array();
        $imgsrc = array_filter($imgsrc);
        foreach ($imgsrc as $key => $value) {
            $image[] = 'http://www.fjyuanxin.com/sngl/upload/fy/'.$value;
        }
        $result['imgsrc'] = $image;
        $result['applyerID'] = D('KkBoss')->getIDFromName($res['jbr']);
        $result['applyerName'] = $res['jbr'];
        $result['stat'] = $res['stat'];
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
        return $this->field(true)->where($map)->setField('stat',0);
    }
    /**
     * 拒收
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function refuseRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',3);
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
                                     'value'=> date('m-d H:i',strtotime($res['date'])) ,
                                     'type'=>'date'
                                    );
        $logic  = D('KkCostMoney','Logic');
        $data =  $logic->record($res['dh']);
        $result[] = array('name'=>'付款方式：',
                                'value'=>$data['nfylx'] == 1?'报销费用':'用款费用',
                                'type'=>'number'
                            );
        $fpsm = array('已到','未到','无票');
        $showFpsm = $fpsm[$data['fpsm']-1];
        if($res['stat'] == 1) $showFpsm = '补到';
        $result[] = array('name'=>'发票说明：',
                                        'value'=> $showFpsm,
                                        'type'=>'number'
                                    );
        $fkfs = M('kk_fkfs')->field('id as val,fk_name as name')->where(array('id' =>$data['nfkfs']?$data['nfkfs']:''))->order('id asc')->find();
        $result[] = array('name'=>'付款方式：',
                                        'value'=>$fkfs['name']?$fkfs['name']:'未提交',
                                        'type'=>'number'
                                    );

        $result[] = array('name'=>'用款金额：',
                                        'value'=> number_format(-$data['nmoney'],2,'.',',')."元",
                                        'type'=>'number'
                                    );

        $result[] = array('name'=>'申请人员：',
                                        'value'=>$res['jbr'],
                                        'type'=>'string'
                                    );
        if( $data['nfylx'] == 1 ){
            $textdata = M('kk_fybx')->field('nr as ntext')->where("left(dh,13)='{$data['dh']}'" )->find();
        }else{
            $textdata = M('kk_ykfy')->field('ykyt as ntext,skdw')->where("left(dh,13)='{$data['dh']}'" )->find();
        }
        $xgsm = $textdata['ntext']."<br/>".$res['text'];
        $result[] = array('name'=>'相关说明：',
                                        'value'=>$xgsm?$xgsm:'无',
                                        'type'=>'text'
                                    );
                                   
        return $result;
    }

    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->getField('jbr');
    }

    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $logic  = D('KkCostMoney','Logic');
        $data =  $logic->record($res['dh']);
        $fpsm = array('已到','未到','无票');
        $second_color = $data['fpsm']-1==1?"style='color:#f12e2e'":'';
        $second_content = $fpsm[$data['fpsm']-1];
        if($res['stat'] == 1) $second_content = '补到';
        $temp = array(
            array('title' => '用款金额' , 'content' => "&yen;".number_format(-$data['nmoney'],2,'.',',')."元" ),
            array('title' => '发票说明' , 'content' => $second_content , 'color' => $second_color ),
            array('title' => '相关说明' , 'content' => $res['text']?$res['text']:'无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['stat'],
            'applyerName'    => $res['jbr'],
        );
        return $result;
    }

   


    // 临时额度增加
    public function submit(){
        $id        = I('post.id');
        $datetime  = I('post.datetime');
        $imagepath = I('post.imagepath');   
        $text      = I('post.text');
        $copyto_id = I('post.copyto_id');
        $sign_id   = I('post.sign');
        $sign_arr  = explode(',',$sign_id);
        $sign_arr  = array_filter($sign_arr);// 去空
        $sign_arr  = array_unique($sign_arr); // 去重
        if(!$id) return  array('code' => 404,'msg' => '请选择费用记录');

        $fpsm = array(
            'dh'  => $id,
            'stat' => 2,
            'text' => $text,
            'file' => $imagepath,
            'jbr'  => session('name'),
            'date' => date('Y-m-d H:i:s'),
		);
        if(!M('kk_fpsm')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        // 审批中 禁止提交
        $pass = M('kk_fpsm')->where(array('dh' => $id,'type'=>1,'stat' => array(array('eq',1),array('eq',2),'or')))->find();
        if(!empty($pass)) return array('code' => 404,'msg' =>'此记录已在签收中！');
        $result = M('kk_fpsm')->add($fpsm);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'Fpsm', $result);
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
                'mod_name'      => 'Fpsm',
                'app_name'      => '签收',
                'apply_user'    => '',
                'apply_user_id' => 0, 
                'urge'          => 0,
            );
            $all_arr[]=$data;
        }
        $boss_id = implode('|',$sign_arr);
        M('kk_appflowproc')->addAll($all_arr);
        D('WxMessage')->ProSendCarMessage('kk','Fpsm',$result,$boss_id,session('kk_id'),'QS');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
     
    public function getContent(){
        $id     = I('post.id');
        $system = I('post.system');
        return $this->FYrecordContent($id,$system);
    }
    // 费用开支内容
    public function FYrecordContent($id,$system){
        $logic  = D(ucfirst($system).'CostMoney','Logic');
        $res    = $logic->recordContent($id);
        $boss   = D($system.'Boss');
        $avatar = $boss->getAvatar($res['applyerID']);
        $res['avatar'] = $avatar;
        $res['fylx'] = $logic->getFylx();
        $res['fylxRecord'] = $logic->getFylxRecord();
        return $res;
    }
    /**
     * 获取未到发票数据
     */
    public function getDataOfFp(){
        $system = I('post.system');
        $fylx   = I('post.fylx');
        $money  = I('post.money');
        // 排除退审的费用开支
        $map = array(
            'app_stat' => 1,
            'mod_name' => 'CostMoney',
        );
        $feefy = M("{$system}_appflowproc")->field('aid')->where($map)->select();
        $feefy = array_unique($feefy);
        $fee = array();
        foreach($feefy as $val){
            $fee[] = $val['aid'];
        }
        // 未到发票
        $map = array(
            'fpsm' => 2,
            'stat' => array('gt',0),
            'njbr' => session('name'),
        );
        if($fylx){
            $map['nfylx'] = $fylx == 'bx'?1:array('gt',1);
        }
        if($money){
            $map['nmoney'] = -$money;
        }

        $data = M("{$system}_feefy")->where($map)->order('jl_date desc')->select();
        $res = array();
        foreach($data as $val){
            if( in_array($val['id'],$fee)) continue;
            if( $val['nfylx'] == 1 ){
                $textdata = M($system.'_fybx')->field('nr as ntext')->where("left(dh,13)='{$val['dh']}'" )->find();
            }else{
                $textdata = M($system.'_ykfy')->field('ykyt as ntext,skdw')->where("left(dh,13)='{$val['dh']}'" )->find();
            }
            $res[] = array(
                'id'   => $val['id'],
                'date' => date('Y-m-d H:i:s',strtotime($val['jl_date'])),
                'sqlx' => $val['nfylx'] == 1?'报销费用':'用款费用',
                'money'=> "&yen;".number_format(-$val['nmoney'],2,'.',',')."元",
                'dh'   => $val['dh'],
                'text' => $textdata['ntext']?$textdata['ntext']:'无',
            );
        }
        return $res;
    }

}