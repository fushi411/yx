<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbSalesReceiptsApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_feexs';

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
                                     'value'=>'建材销售收款',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('Y-m-d H:i',strtotime($res['jl_date'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );                                    
        $result['content'][] = array('name'=>'收款日期：',
                                     'value'=> $res['sj_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $dtg  = M('yxhb_dtg')->where(array('dh' => $res['dh']))->find();
        $user = M('yxhb_guest2')->where(array('id' => $dtg['gid']))->find();
        $user_name = $user['g_name'];
          
        $result['content'][] = array('name'=>'提货单位：',
                                     'value'=> $user_name,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户别名：',
                                     'value'=> $dtg['ang']?$dtg['ang']:'无',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
       $result['content'][] = array('name'=>'收款金额：',
                                     'value'=>number_format($res['nmoney'],2,'.',',')."元" ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $skfsArr = array(4 => '现金',2 => '公司账户', 3 => '承兑汇票');
        $result['content'][] = array('name'=>'收款方式：',
                                     'value'=> $skfsArr[$res['nfkfs']],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $bankinfo = M('yxhb_bank')->where(array('id' => $res['nbank']))->find();
        $bl    = mb_strlen($bankinfo['bank_account'])-4;
        $bname = mb_substr($bankinfo['bank_account'],$bl,mb_strlen($bankinfo['bank_account']),'utf-8');
        
        if(!$bname) $bname = $bankinfo['bank_account'];

        if(!$bankinfo['bank_text']){
            $name  = "{$bankinfo['bank_name']}-{$bname}";
        }else{
            $name  = $bankinfo['bank_name']."-".$bname."-".$this->getbklx($bankinfo['bank_lx_sub']);
        }
        $result['content'][] = array('name'=>'收款银行：',
                                     'value'=>$name,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['ntext']?$res['ntext']:'无',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['npeople']);
        $result['applyerName'] = $res['npeople'];
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
                                     'value'=> date('Y-m-d H:i',strtotime($res['jl_date'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'收款日期：',
                                     'value'=>$res['sj_date'],
                                     'type'=>'date'
                                    );
        $dtg  = M('yxhb_dtg')->where(array('dh' => $res['dh']))->find();
        $user = M('yxhb_guest2')->where(array('id' => $dtg['gid']))->find();
        $user_name = $user['g_name'];
        

        $result[] = array('name'=>'提货单位：',
                                     'value'=>$user_name,
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'客户别名：',
                                     'value'=>$dtg['ang']?$dtg['ang']:'无',
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'收款金额：',
                                     'value'=>number_format($res['nmoney'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['npeople'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['ntext']?$res['ntext']:'无',
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
        return $this->field(true)->where($map)->getField('njbr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            array('收款日期',$res['sj_date']),
            array('收款金额', number_format($res['nmoney'],2,'.',',')."元"),
            array('相关说明',$res['ntext']?$res['ntext']:'无')
        );
        return $result;
    }
    /**
     * 汇票上传
     */
    public function hpsc(){
        $uploader = new \Think\Upload\Driver\Local;
        if(!$uploader){
            E("不存在上传驱动");
        }
        // 生成子目录名
        $savePath = date('Y-m-d')."/";

        // 生成文件名
        $img_str = I('post.imagefile');
        $order = I('post.order');
        $img_header = substr($img_str, 0, 23);
        // echo $img_header;exit();
        if (strpos($img_header, 'png')) {
            $output_file = uniqid('comment_').'_'.$order.'.png';
        }else{
            $output_file = uniqid('comment_').'_'.$order.'.jpg';
        }
        //  $base_img是获取到前端传递的src里面的值，也就是我们的数据流文件
        $base_img = I('post.imagefile');
        if (strpos($img_header, 'png')) {
            $base_img = str_replace('data:image/png;base64,', '', $base_img);
        }else{
            $base_img = str_replace('data:image/jpeg;base64,', '', $base_img);
        }

        //  设置文件路径和文件前缀名称
        $rootPath = "/www/web/default/sngl/upload/hp/";
        /* 检测上传根目录 */
        if(!$uploader->checkRootPath($rootPath)){
            $error = $uploader->getError();
            return false;
        }
        /* 检查上传目录 */
        if(!$uploader->checkSavePath($savePath)){
            $error = $uploader->getError();
            return false;
        }
        $path = $rootPath.$savePath.$output_file;
        //  创建将数据流文件写入我们创建的文件内容中
        file_put_contents($path, base64_decode($base_img));
        $val['path'] = $path;
        $val['output_file'] = $savePath.$output_file;
        $res[] = $val;
        return $res;
    }
    /**
     * 获取销售客户列表
     */
    public function getCustomerList(){
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql  = "SELECT id,g_name as text from(SELECT id,g_name,g_helpword FROM yxhb_guest2 WHERE g_name!='' and g_helpword!='_' and g_name not like '%(删)%' ORDER BY g_name ASC)a {$like}";
        $res  = M()->query($sql);
        return $res;

    }

    /**
     * 获取银行信息
     */
    public function getBankInfo($id)
    {
        $sql = "SELECT * FROM yxhb_bank";
        $res = M()->query($sql);
        $result = array();
       
        foreach($res as $k => $v){

            $bl    = mb_strlen($v['bank_account'])-4;
            $bname = mb_substr($v['bank_account'],$bl,mb_strlen($v['bank_account']),'utf-8');
            
            if(!$bname) $bname = $v['bank_account'];

            if(!$v['bank_text']){
                $name  = "{$v['bank_name']}-{$bname}";
            }else{
                $name  = $v['bank_name']."-".$bname."-".$this->getbklx($v['bank_lx_sub']);
            }
            
            $temp  = array(
                $name, $v['bank_lx'], $v['id']
            );
            
            $result[$v['bank_lx']][] = $temp;
        }
        return $result;
    }

    /**
     * 类型获取
     */
    public function getbklx($lx){
        if($lx==1) $bklx='基本户';
        elseif($lx==2) $bklx='一般户';
        elseif($lx==3) $bklx='临时户';
        elseif($lx==4) $bklx='自开';
        elseif($lx==5) $bklx='外开';
        return $bklx;
    }
    /**
     * 汇票提交
     */
    public function hptj(){
        $hphm       = I('post.hphm');
        $mj         = I('post.mj');
        $xz         = I('post.xz');
        $kpyh1      = I('post.kpyh1');
        $bank_name  = I('post.bank_name');
        $money_1    = I('post.money_1');
        $spdate     = I('post.spdate');
        $kpdate     = I('post.kpdate');
        $dqdate     = I('post.dqdate');
        $reason     = I('post.reason');
        $imagepath  = I('post.imagepath');
        $bsqk       = I('post.bsqk');
        if($kpyh1 == '其他银行') $kpyh1 = $bank_name;
        $hpData = array(
            'spda'     => $spdate,
            'kpda'     => $kpdate,
            'dqda'     => $dqdate,
            'hphm'     => $hphm,
            'kpyh'     => $kpyh1,
            'nmoney'   => $money_1,
            'bsqk'     => $bsqk,
            'ntext'    => $reason,
            'stat'     => 3,
            'odh'      => $dh = $this->makeDh(),
            'date'     => date('Y-m-d h:i:m',time()),
            'att_name' => $imagepath,
            'mj'       => $mj,
            'xz'       => $xz
        );
        $res = M('yxhb_cdhp')   -> add($hpData);
        return  array('code' => $res ?200:404,'msg' => '汇票录入失败,请联系管理员');
    }

    /**
     * 数据提交
     */
    public function submit(){
        $fkfs      = I('post.fkfs');
        $bank      = I('post.bank');
        $money     = I('post.money');
        $jbr       = I('post.jbr');
        $jbr       = str_replace('X','',$jbr);
        $datetime  = I('post.datetime');
        $user      = I('post.user_id');
        $text      = I('post.text');
        $copyto_id = I('post.copyto_id');
        $sign_id   = I('post.sign');
        $sign_arr  = explode(',',$sign_id);
        $sign_arr  = array_filter($sign_arr);// 去空
        $sign_arr  = array_unique($sign_arr); // 去重
        if(!$datetime) return  array('code' => 404,'msg' => '请选择收款时间');
        if(!$fkfs)     return  array('code' => 404,'msg' => '请选择收款方式');
        if(!$user)  return  array('code' => 404,'msg' => '请选择收款单位');
        if(!$bank) return  array('code' => 404,'msg' => '请选择收款银行');
        if(!$money || $money<0) return  array('code' => 404,'msg' => '收款金额不能为空或小于零');
        $insert = array(
            
        );
        $dh = $this->makeDh();
        $feeData = array(
			'dh'      => $dh,
			'sj_date' => date('Y-m-d',strtotime($datetime)),
			'nmoney'  => $money,
			'nbank'   => $bank,
			'jl_date' => date('Y-m-d h:i:m',time()),
			'npeople' => session('name'),
			'ntext'   => $text,
			'nfkfs'   => $fkfs,
			'nfylx'   => '',
			'njbr'    => $jbr,
			'nbm'     => 5,
			'stat'    => 2
		);
                
        $user_other_name = I('post.user_other_name');
		$dtgData = array(
			'dh'  => $dh,
			'gid' => $user,
			'ang' => $user_other_name
        );
        
        if(!M('yxhb_feexs')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');

        $result = M('yxhb_feexs')->add($feeData);
		M('yxhb_dtg')   -> add($dtgData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'SalesReceiptsApply', $result);
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
                'mod_name'      => 'SalesReceiptsApply',
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
     * 单号生成
     */
    public function makeDh(){
        $today=date("Y-m-d",time());
        $res = M('yxhb_feexs') ->where("date_format(jl_date, '%Y-%m-%d' )='$today' and dh like 'XS%'")->count();
        $str = date('Ymd',time());
        $db  = "XS{$str}";
        $num = $res+1;
        if($res<10)     return "{$db}00{$num}";
        if($res < 100)  return "{$db}0{$num}";
        return "{$db}{$num}";
    }

    /**
     * 通知信息发送
     */
    public function sendMessage($apply_id,$boss){
        $system = 'yxhb';
        $mod_name = 'SalesReceiptsApply';
        $logic = D(ucfirst($system).$mod_name, 'Logic');
        $res   = $logic->record($apply_id);
        $systemName = array('yxhb'=>'建材', 'yxhb'=>'环保');
        // 微信发送
        $WeChat = new \Org\Util\WeChat;
        
        $descriptionData = $logic->getDescription($apply_id);
     
        $title = '销售收款(签收)';
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