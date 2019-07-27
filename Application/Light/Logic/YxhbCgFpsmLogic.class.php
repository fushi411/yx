<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCgFpsmLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_fpsm';

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
        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'环保采购发票上传',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $data    = M("yxhb_cgfksq")->where(array('id'=>$res['dh']))->find();
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($data['date']))  ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        
        if($data['fylx'] == 1){
            $clientname = M('yxhb_gys')->field('g_name')->where(array('id' => $data['gys']))->find();
            $suffix = "(汽运)";
            if($data['htlx'] == '海运') $suffix = "(海运)";
            $clientname['g_name'] .= $suffix;
            $nfylx = '原材料采购付款';
        }elseif($data['fylx'] == 2 || $data['fylx'] == 4|| $data['fylx'] == 7){
            $clientname = M('yxhb_wl')->field('g_name')->where(array('id' => $data['gys']))->find();
            $nfylx = '物流采购付款';
        }else{
            $clientname = array( 'g_name' => $data['pjs']);
            $nfylx = '配件采购付款';
        }
      
        $result['content'][] = array('name'=>'执行时间：',
                                     'value'=>date('Y-m-d',strtotime($data['date'])),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
 
        $result['content'][] = array('name'=>'申请类型：',
                                     'value'=> $nfylx,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交人员：',
                                     'value'=> $data['rdy'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'付款金额：',
                                    'value'=>"&yen;".number_format($data['fkje'],2,'.',',')."元",
                                    'type'=>'number',
                                    'color' => 'black;font-weight: 600;'
                                   );
       $result['content'][] = array('name'=>'大写金额：',
                                    'value'=>cny($data['fkje']),
                                    'type'=>'string',
                                    'color' => 'black'
                                   );
       $fpsm = array('已到','未到','无票');
       $color = $data['fpsm']-1==1?'#f12e2e':'black';
       $showFpsm = $fpsm[$data['fpsm']-1];
       if($res['stat'] == 1) $showFpsm = '补到';
       $result['content'][] = array('name'=>'发票说明：',
                                    'value'=> $showFpsm,
                                    'type'=>'date',
                                    'color' => $color
                                   );
       $fkfs = '暂无';
       if($data['fkfs'] == 4 ){
           $fkfs = '现金';
       }elseif($data['fkfs'] == 2 ){
           $fkfs = '公户';
       }elseif ($data['fkfs'] == 3 ) {
           $fkfs = '汇票';
       }
       $result['content'][] = array('name'=>'付款方式：',
                                    'value'=>$fkfs,
                                    'type'=>'string',
                                    'color' => 'black'
                                   );                   
        $xgsm = $data['zy']."<br/>".$res['text'];                 
       $result['content'][] = array('name'=>'相关说明：',
                                    'value'=>$xgsm?$xgsm :'无',
                                    'type'=>'text',
                                    'color' => 'black'
                                   ); 
    
        $imgsrc = explode('|', $res['file']) ;
        $image = array();
        $imgsrc = array_filter($imgsrc);
        foreach ($imgsrc as $key => $value) {
            $image[] = 'http://www.fjyuanxin.com/yxhb/upload/fy/'.$value;
        }
        $result['imgsrc'] = $image;
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['jbr']);
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
        $res = $this->record($id);
        // 已签收通过的 
        if( $res['stat'] == 1){
            M('yxhb_feecg')->where(array('sqdh',$res['dh']))->setField('fpsm',2);
        }
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
        $data    = M("yxhb_cgfksq")->where(array('id'=>$res['dh']))->find();
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($data['date'])) ,
                                     'type'=>'date'
                                    );
        if($data['fylx'] == 1){
            $nfylx = '原材料采购付款';
        }elseif($data['fylx'] == 2 || $data['fylx'] == 4|| $data['fylx'] == 7){
            $nfylx = '物流采购付款';
        }else{
            $nfylx = '配件采购付款';
        }    
        $result[] = array('name'=>'费用类型：',
                                        'value'=> $nfylx,
                                        'type'=>'number'
                                    );
        $fpsm = array('已到','未到','无票');
        $showFpsm = $fpsm[$data['fpsm']-1];
        if($res['stat'] == 1) $showFpsm = '补到';
        $result[] = array('name'=>'发票说明：',
                                        'value'=> $showFpsm,
                                        'type'=>'number'
                                    );
        $fkfs = '暂无';
        if($data['fkfs'] == 4 ){
            $fkfs = '现金';
        }elseif($data['fkfs'] == 2 ){
            $fkfs = '公户';
        }elseif ($data['fkfs'] == 3 ) {
            $fkfs = '汇票';
        }
        $result[] = array('name'=>'付款方式：',
                                        'value'=>$fkfs,
                                        'type'=>'number'
                                    );

        $result[] = array('name'=>'用款金额：',
                                        'value'=> number_format($data['fkje'],2,'.',',')."元",
                                        'type'=>'number'
                                    );

        $result[] = array('name'=>'申请人员：',
                                        'value'=>$res['jbr'],
                                        'type'=>'string'
                                    );
        $xgsm = $data['zy']." ".$res['text'];   
        $result[] = array('name'=>'相关说明：',
                                        'value'=>$xgsm?$xgsm :'无',
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
        $data    = M("yxhb_cgfksq")->where(array('id'=>$res['dh']))->find();
        $fpsm = array('已到','未到','无票');
        $second_color = $data['fpsm']-1==1?"style='color:#f12e2e'":'';
        $second_content = $fpsm[$data['fpsm']-1];
        if($res['stat'] == 1) $second_content = '补到';
        $temp = array(
            array('title' => '用款金额' , 'content' => "&yen;".number_format($data['fkje'],2,'.',',')."元" ),
            array('title' => '发票说明' , 'content' => $second_content ,'color' => $second_color ),
            array('title' => '相关说明' , 'content' => $res['text']?$res['text']:'无'  ),
        );
        $result = array(
            'content'        => $temp ,
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
            'type' => 2,
		);
        if(!M('yxhb_fpsm')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        // 审批中 禁止提交
        $pass = M('yxhb_fpsm')->where(array('dh' => $id,'type'=>2,'stat' => array(array('eq',1),array('eq',2),'or')))->find();
        if(!empty($pass)) return array('code' => 404,'msg' =>'此记录已在签收中！');
        $result = M('yxhb_fpsm')->add($fpsm);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'CgFpsm', $result);
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
                'mod_name'      => 'CgFpsm',
                'app_name'      => '签收',
                'apply_user'    => '',
                'apply_user_id' => 0, 
                'urge'          => 0,
            );
            $all_arr[]=$data;
        }
        $boss_id = implode('|',$sign_arr);
        M('yxhb_appflowproc')->addAll($all_arr);
        D('WxMessage')->ProSendCarMessage('yxhb','CgFpsm',$result,$boss_id,session('yxhb_id'),'QS');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
     
    public function getContent(){
        $id     = I('post.id');
        $system = I('post.system');
        
        return $this->FYrecordContent($id,$system);
    }
    // 费用开支内容
    public function FYrecordContent($id,$system){
        $res    = M("{$system}_cgfksq")->where(array('id'=>$id))->find();
        if($res['fylx'] == 1){
            $mod = 'CgfkApply';
        }elseif($res['fylx'] == 2 || $res['fylx'] == 4|| $res['fylx'] == 7){
            $mod = 'WlCgfkApply';
        }else{
            $mod = 'PjCgfkApply';
        }    
        $logic  = D(ucfirst($system).$mod,'Logic');
        $res    = $logic->recordContent($id);
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
            'mod_name' => array(array('eq','CgfkApply'),array('eq','WlCgfkApply'),array('eq','PjCgfkApply'),'or'),
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
            'rdy' => session('name'),
        );
        if($fylx){
            if($fylx=='ycl'){
                $map['fylx'] = '1';
            }elseif($fylx=='wl'){
                $map['fylx'] = array(array('eq',2),array('eq',7),array('eq',4),'or');
            }else{
                $map['fylx'] = 6;
            }
        }
        if($money){
            $map['fkje'] = $money;
        }

        $data = M("{$system}_cgfksq")->where($map)->order('zd_date desc')->select();
        $res = array();
        foreach($data as $val){
            if( in_array($val['id'],$fee)) continue;
            if($val['fylx'] == 1){
                $nfylx = '原材料采购付款';
            }elseif($val['fylx'] == 2 || $val['fylx'] == 4|| $val['fylx'] == 7){
                $nfylx = '物流采购付款';
            }else{
                $nfylx = '配件采购付款';
            }
            $res[] = array(
                'id'   => $val['id'],
                'date' => date('Y-m-d H:i:s',strtotime($val['zd_date'])),
                'sqlx' => $nfylx,
                'money'=> "&yen;".number_format($val['fkje'],2,'.',',')."元",
                'dh'   => $val['dh'],
                'text' => $val['zy']?$val['zy']:'无',
            );
        }
        return $res;
    }
}