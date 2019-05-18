<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCostMoneyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_feefy';

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

    public function recordContent($id,$params)
    {
        $res = $this->record($id);
        $result = array();

        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'环保费用开支',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );                      
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['jl_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=> date('Y-m-d',strtotime($res['jl_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    ); 
        if( $res['nfylx'] === '0' ){
            $fybx = M('yxhb_feefy3')->where("dh='{$res['dh']}'" )->find();
            $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$fybx['nfylx']?$fybx['nfylx']:''))->order('id asc')->find(); 
        }else{
            $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$res['nfylx']?$res['nfylx']:''))->order('id asc')->find(); 
        }
 
        $result['content'][] = array('name'=>'申请类型：',
                                     'value'=> $res['nfylx'] === '0'?'报销费用':'用款费用',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $fpsm = array('已到','未到','无票');
        $color = $res['fpsm']-1==1?'#f12e2e':'black';
        $result['content'][] = array('name'=>'发票说明：',
                                     'value'=> $fpsm[$res['fpsm']-1],
                                     'type'=>'date',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'费用类型：',
                                     'value'=> $fylx['name']?$fylx['name']:'未提交',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $fkfs = M('yxhb_fkfs')->field('id as val,fk_name as name')->where(array('id' =>$res['nfkfs']?$res['nfkfs']:''))->order('id asc')->find();

        $result['content'][] = array('name'=>'付款方式：',
                                     'value'=>$fkfs['name']?$fkfs['name']:'未提交',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'用款金额：',
                                     'value'=> "&yen;".number_format(-$res['nmoney'],2,'.',',')."元"  ,
                                     'type'=>'string',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'大写金额：',
                                     'value'=>cny( -$res['nmoney'] ),
                                     'type'=>'number',
                                     'color' => 'black;'
                                    );
        if( $res['nfylx'] === '0' ){
            $textdata = M('yxhb_fybx')->field('nr as ntext')->where("left(dh,13)='{$res['dh']}'" )->find();
        }else{
            $textdata = M('yxhb_ykfy')->field('ykyt as ntext,skdw')->where("left(dh,13)='{$res['dh']}'" )->find();
        }
        if($res['nfylx'] !== '0'){
            $skdw = $res['skdw']?$res['skdw']:'无';
            $result['content'][] = array('name'=>'收款单位：',
                                        'value'=>$textdata['skdw']?$textdata['skdw']:$skdw,
                                        'type'=>'string',
                                        'color' => 'black'
                                        );

            $result['content'][] = array('name'=>'收款账号：',
                                        'value'=>$res['skzh']?$res['skzh']:'无',
                                        'type'=>'string',
                                        'color' => $res['skzh']?'black;font-weight:550;':'black',
                                        );    
            $result['content'][] = array('name'=>'开户银行：',
                                        'value'=>$res['khyh']?$res['khyh']:'无',
                                        'type'=>'string',
                                        'color' => 'black'
                                        );
        }
        $ntext = $res['ntext']?$res['ntext']:'无';
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$textdata['ntext']?$textdata['ntext']:$ntext,
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        if(empty($params)){
            $result['content'][] = array('name'=>'付款记录：',
                                        'value'=>$this->PayHmtl($id),
                                        'type'=>'string',
                                        'color' => 'black'
                                        );    
            $result['content'][] = array('name'=>'费用记录：',
                                        'value'=>$this->fylxHmtl($id),
                                        'type'=>'string',
                                        'color' => 'black'
                                        );
        }
        $imgsrc = explode('|', $res['att_name']) ;
        $image = array();
        $imgsrc = array_filter($imgsrc);
        foreach ($imgsrc as $key => $value) {
            $image[] = 'http://www.fjyuanxin.com/yxhb/upload/fy/'.$value;
        }
        $result['imgsrc'] = $image;
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['njbr']);
        $result['applyerName'] = $res['njbr'];
        $result['stat'] = $this->transStat($res['stat']);
        return $result;
    }
     // 状态值转换
     public function transStat($stat){
        $statArr = array(
            5 => 2,
            4 => 1,
            3 => 2,
            2 => 1,
            0 => 0
        );
        return $statArr[$stat];
     }
     public function fylxHmtl($id){
        $data = $this->getFylxRecord($id,'yxhb');
        $html = '';
        foreach($data as $v){
            $date = date('Y-m-d',strtotime($v['date']));
            $html.="<input class='weui-input' type='text' style='color: black; font-weight: 700; ' readonly value='{$date}({$v['man']})'>";
            $html .= "<input class='weui-input' type='text' style='color: black;' readonly value='费用类型:{$v['fylx']}'>";
        }
        return empty($html)?'暂无':$html;
    }

    public function PayHmtl($id){
        $data = $this->getPayedRec($id,'yxhb');
        $html = '';
        foreach($data as $v){
            $html.="<input class='weui-input' type='text' style='color: black; font-weight: 700; '  readonly value='{$v['sj_date']}:{$v['npeople']}'>";
            $html .= "<input class='weui-input' type='text' style='color: black;' 
                     readonly value='{$v['money']}:{$v['bank']['nb']}-{$v['bank']['ac']}-{$v['bank']['wz']}'>";
        }
        return empty($html)?'暂无':$html;
    }
    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    { 
        $data = $this->record($id);
        if($data['stat'] == 2) return 'failure';
        $map = array("left(dh,13)='{$data['dh']}'" );
        M('yxhb_feefy3')->field(true)->where($map)->setField('stat',0);
        if($data['nfylx'] ==='0'){
            M('yxhb_fybx')->field(true)->where($map)->setField('stat',0);
        }else{
            M('yxhb_ykfy')->field(true)->where($map)->setField('stat',0);
        }   
        if($data['stat'] == 2){
            M('yxhb_feefy2')->where($map)->setField('stat',0);
        }
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
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['jl_date'])),
                                     'type'=>'date'
                                    );
        // if( $res['nfylx'] == 1 ){
        //     $fybx = M('yxhb_feefy3')->where("dh='{$res['dh']}'" )->find();
        //     $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$fybx['nfylx']))->order('id asc')->find(); 
        // }else{
        //     $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$res['nfylx']))->order('id asc')->find(); 
        // }
        $fpsm = array('已到','未到','无票');
        $result[] = array('name'=>'发票说明：',
                                     'value'=> $fpsm[$res['fpsm']-1],
                                     'type'=>'number'
                                    );
        $fkfs = M('yxhb_fkfs')->field('id as val,fk_name as name')->where(array('id' =>$res['nfkfs']?$res['nfkfs']:''))->order('id asc')->find();
        $result[] = array('name'=>'付款方式：',
                                     'value'=>$fkfs['name']?$fkfs['name']:'未提交',
                                     'type'=>'number'
                                    );

        $result[] = array('name'=>'用款金额：',
                                     'value'=>number_format(-$res['nmoney'],2,'.',',')."元",
                                     'type'=>'number'
                                    );

        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['njbr'],
                                     'type'=>'string'
                                    );
        if( $res['nfylx'] ==='0' ){
            $textdata = M('yxhb_fybx')->field('nr as ntext')->where("left(dh,13)='{$res['dh']}'" )->find();
        }else{
            $textdata = M('yxhb_ykfy')->field('ykyt as ntext')->where("left(dh,13)='{$res['dh']}'" )->find();
        }
        $ntext = $res['ntext']?$res['ntext']:'无';
        $result[] = array('name'=>'用款用途：',
                                     'value'=>$textdata['ntext']?$textdata['ntext']:$ntext,
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
        $res    = $this->record($id);
        // if( $res['nfylx'] == 1 ){
        //     $fybx = M('yxhb_feefy3')->where("dh='{$res['dh']}'" )->find();
        //     $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$fybx['nfylx']))->order('id asc')->find(); 
        // }else{
        //     $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$res['nfylx']))->order('id asc')->find(); 
        // }
        $fpsm = array('已到','未到','无票');
        $first_title    = '申请类型';
        $first_content  = '用款费用';
        $second_title   = '发票说明';
        $second_color = $res['fpsm']-1==1?"style='color:#f12e2e'":'';
        $second_content = $fpsm[$res['fpsm']-1];
        if($res['nfylx'] ==='0')$first_content  = '报销费用';
        if( $res['nfylx'] ==='0' ){
            $textdata = M('yxhb_fybx')->field('nr as ntext')->where("left(dh,13)='{$res['dh']}'" )->find();
        }else{
            $textdata = M('yxhb_ykfy')->field('ykyt as ntext')->where("left(dh,13)='{$res['dh']}'" )->find();
        }
        $ntext = $res['ntext']?$res['ntext']:'无';
        $result = array(
            'first_title'    => $first_title,
            'first_content'  => $first_content,
            'second_title'   => $second_title,
            'second_content' => $second_content,
            'second_color'   => $second_color,
            'third_title'    => '用款金额',
            'third_content'  => "&yen;".number_format(-$res['nmoney'],2,'.',',')."元",
            'fourth_title'   => '相关说明',
            'fourth_content' => $textdata['ntext']?$textdata['ntext']:$ntext,
            'stat'           => $this->transStat($res['stat']),
            'applyerName'    => $res['njbr'],
        );
        return $result;
    }

    /**
     * 矿粉物料配置
     */
    public function submit(){
        $system = 'yxhb';
        $fpsm   = I('post.fpsm');
        $fkfs   = I('post.fkfs');
        $skzh   = I('post.skzh');
        $khyh   = I('post.khyh');
        $skdw   = I('post.skdw');
        $ykje   = I('post.ykje');
        $text   = I('post.text');
        $sqlx   = I('post.sqlx');
        $copyto_id = I('post.copyto_id');
        $imagepath = I('post.imagepath');   
        // 流程检验
        $pro = D('YxhbAppflowtable')->havePro('CostMoney','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        if(!M($system.'_feefy')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $dh = $this->getDhId();
        $bm = D('YxDetailAuth')->authGetBmId($system);
        $stat = $sqlx == 1?2:1;
        $fyfy = $sqlx == 1?0:'';
        $feefy = array(
            'dh'      => $dh,
            'nmoney'  => -$ykje,
            'nbank'   => '',
            'sj_date' => date('Y-m-d',time()),
            'jl_date' => date('Y-m-d H:i:s',time()),
            'npeople' => session('name'),
            'ntext'   => $text,
            'nfkfs'   => $fkfs,
            'nfylx'   => $fyfy,
            'njbr'    => session('name'),
            'nbm'     => $bm,
            'stat'    => 5,
            'att_name' => $imagepath,
            'att_name2'=> 'image',
            'skzh'     => $skzh,
            'khyh'     => $khyh,
            'skdw'     => $skdw,
            'fpsm'     => $fpsm,
        );

        $feefy3 = array(
            'dh'      => $dh,
            'nmoney'  => -$ykje,
            'nbank'   => '',
            'sj_date' => date('Y-m-d',time()),
            'jl_date' => date('Y-m-d H:i:s',time()),
            'npeople' => session('name'),
            'ntext'   => $text,
            'nfkfs'   => $fkfs,
            'nfylx'   => '',
            'njbr'    => session('name'),
            'nbm'     => $bm,
            'stat'    => $stat,
        );

        
        $result = M($system.'_feefy')->add($feefy);
        M($system.'_feefy3')->add($feefy3);

        $ykfy = array(
            'dh'     => $dh,
            'smonth' => intval(date('m',time())),
            'stat'   => $stat,
        );
        if($sqlx == 1){
            $ykfy['nr'] = $text;
            $table      = $system.'_fybx'; 
        }else{
            $ykfy['skdw'] = $skdw;
            $ykfy['ykyt'] = $text;
            $table        = $system.'_ykfy'; 
        }
        M($table)->add($ykfy);
        if(!$result) return array('code' => 404,'msg' => '提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'CostMoney', $result);
        }
        $wf = A('WorkFlow');
        $salesid = session('yxhb_id');
        $res = $wf->setWorkFlowSV('CostMoney', $result, $salesid, 'yxhb');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }

    /**
     * 获取单号
     */
    public function getDhId(){
        $today = date('Y-m-d',time());
        $sql   = "select * from yxhb_feefy where date_format(jl_date, '%Y-%m-%d' )='{$today}' ";
        $res   = M()->query($sql);
        $count = count($res);
        $time  = str_replace('-','',$today);
        $id    = "FY{$time}";
        if($count < 9)  return  $id.'00'.($count+1);
        if($count < 99) return $id.'0'.($count+1);
        return $id.$count;
    }

    /**
     * 获取 费用类型，付款方式，用款部门
     */
    public function getSomeData(){
        return array(
            'fylx' => $this->getFylx(),
            'fkfs' => $this->getFkfs(),
            'bm'   => $this->getSection()
        );
    }
  
    /**
     * 获取费用类型
     */
    public function getFylx(){
        $tmp = array();
        $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('stat' => 1))->order('id asc')->select();
        return $fylx;
    }
    /**
     * 获取付款方式
     */
    public function getFkfs(){
        $tmp = array();
        $fkfs = M('yxhb_fkfs')->field('id as val,fk_name as name')->order('id asc')->select();
        return $fkfs;
    }

    /**
     * 用款部门
     */
    public function getSection(){
        $tmp = array();
        $bm  = M('yxhb_bm')->field('id as val,bm as name')->order('id asc')->select();
        return $bm;
    }

     /**
     * 附件上传
     */
    public function fjsc(){
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
        $rootPath = "/www/web/default/yxhb/upload/fy/";
        /* 检测上传根目录 */
        if(!$uploader->checkRootPath($rootPath)){
            $error = $uploader->getError();
            return $error;
        }
        /* 检查上传目录 */
        if(!$uploader->checkSavePath($savePath)){
            $error = $uploader->getError();
            return $error;
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
     * 获取对应的审批流程
     */
    public function getProHtml(){
        $modname   = 'CostMoney';
        $bm        = D('YxDetailAuth')->authGetBmId('yxhb');
        if(empty($bm)) return '暂无部门';
        $condition = 'nbm='.$bm;
        $data = D('YxhbAppflowtable')->getConditionStepHtml($modname,$condition);
        return $data;
    }
        // 费用类型
        public function getContent(){
            $id     = I('post.id');
            $system = I('post.system');
            $res    = $this->recordContent($id,'fylx');
            $boss   = D($system.'Boss');
            $avatar = $boss->getAvatar($res['applyerID']);
            $res['avatar'] = $avatar;
            $res['fylx'] = $this->getFylx();
            $res['fylxRecord'] = $this->getFylxRecord();
            return $res;
        }
        // 获取费用类型记录
        public function getFylxRecord($id,$system){
            $id     = $id?$id:I('post.id');
            $system = $system?$system:I('post.system');
            if(empty($id)) return ;
            $tmp = array();
            $fylxrecord = M($system.'_fylx_record a')
                        ->join($system.'_fylx b on a.fylx=b.id')
                        ->field('b.fy_name as fylx,a.date,a.man')
                        ->where(array('a.stat' => 1,'a.aid' => $id))
                        ->order('a.date desc')
                        ->select();
            foreach($fylxrecord as $k=>$v){
                $fylxrecord[$k]['date'] = date('Y-m-d',strtotime($v['date']));
            }
            return $fylxrecord;
        }
        // 费用类型提交
        public function submitoffylx(){
            $id     = I('post.id');
            $system = I('post.system');
            $fylx   = I('post.fylx');
            $data   = array(
                'date' => date('Y-m-d H:i:s',time()),
                'man'  => session('name'),
                'fylx' => $fylx,
                'aid'  => $id,
            );
            if(!M("{$system}_fylx_record")->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
            // 检查费用类型
            $save = array('nfylx' => $fylx);
            $res = $this->record($id);
            if($res['nfylx'] !== '0'){
                M($system.'_feefy')->where("id={$id}")->save($save);
            }
            M($system.'_feefy3')->where("left(dh,13)='{$res['dh']}'")->save($save);
            $result = M("{$system}_fylx_record")->add($data);
            if(!$result) return array('code' => 404,'msg' => '提交失败，请重新尝试！');
            return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
        }
        
        public function getPayedRec($id,$system)
        {
            $flag   = $id?1:0;
            $id     = $id?$id:I('post.id');
            $system = $system?$system:I('post.system');
            $map = array(
                'a.id' => $id,
                'b.stat' => 1,
            );
            $data = M($system.'_feefy a')
                    ->join("{$system}_feefy2 b on a.dh=b.dh")
                    ->where($map)
                    ->select();
            foreach($data as $k=>$v){
                $bank = M("{$system}_bank")->where(array('id' => $v['nbank']))->find();
                $bl         = strlen($bank['bank_account'])-4;
                $nbank      = $bl <=4 ? $bank['bank_account']:substr($bank['bank_account'],$bl);
                $temp['ac'] = $nbank;
                $temp['wz'] = $this->getbklx($bank['bank_lx_sub']);
                $temp['nb'] = $bank['bank_name'];
                $data[$k]['money'] = "&yen;".number_format(-$v['nmoney'],2,'.',',');
                $data[$k]['bank'] = $temp;
            }
            return $data;
        }
        public function getbklx($lx){
            if($lx==1) $bklx='基本户';
            elseif($lx==2) $bklx='一般户';
            elseif($lx==3) $bklx='临时户';
            elseif($lx==4) $bklx='自开';
            elseif($lx==5) $bklx='外开';
            return $bklx;
        }
}