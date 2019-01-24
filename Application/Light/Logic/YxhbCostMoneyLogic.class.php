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

    public function recordContent($id)
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
        if( $res['nfylx'] == 0 ){
            $fybx = M('yxhb_feefy3')->where(array('dh' => $res['dh']))->find();
            $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$fybx['nfylx']))->order('id asc')->find(); 
        }else{
            $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$res['nfylx']))->order('id asc')->find(); 
        }
 
        $result['content'][] = array('name'=>'申请类型：',
                                     'value'=> $res['nfylx'] == 0?'报销费用':'用款费用',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'费用类型：',
                                     'value'=> $fylx['name'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $fkfs = M('yxhb_fkfs')->field('id as val,fk_name as name')->where(array('id' =>$res['nfkfs']))->order('id asc')->find();

        $result['content'][] = array('name'=>'付款方式：',
                                     'value'=>$fkfs['name'] ,
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
        if($res['nfylx'] != 0){
            $result['content'][] = array('name'=>'收款单位：',
                                        'value'=>$res['skdw']?$res['skdw']:'无',
                                        'type'=>'string',
                                        'color' => 'black'
                                        );

            $result['content'][] = array('name'=>'收款账号：',
                                        'value'=>$res['skzh']?$res['skzh']:'无',
                                        'type'=>'string',
                                        'color' => 'black'
                                        );    
            $result['content'][] = array('name'=>'开户银行：',
                                        'value'=>$res['khyh']?$res['khyh']:'无',
                                        'type'=>'string',
                                        'color' => 'black'
                                        );  
        }
        
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['ntext']?$res['ntext']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
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
            0 => 0
        );
        return $statArr[$stat];
     }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    { 
        $data = $this->record($id);
        $map = array('dh' => $data['dh']);
        M('yxhb_feefy3')->field(true)->where($map)->setField('stat',0);
        if($data['nfylx'] == 0){
            M('yxhb_fybx')->field(true)->where($map)->setField('stat',0);
        }else{
            M('yxhb_ykfy')->field(true)->where($map)->setField('stat',0);
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
        if( $res['nfylx'] == 0 ){
            $fybx = M('yxhb_feefy3')->where(array('dh' => $res['dh']))->find();
            $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$fybx['nfylx']))->order('id asc')->find(); 
        }else{
            $fylx = M('yxhb_fylx')->field('id as val,fy_name as name')->where(array('id' =>$res['nfylx']))->order('id asc')->find(); 
        }
        $result[] = array('name'=>'费用类型：',
                                     'value'=> $fylx['name'] ,
                                     'type'=>'number'
                                    );
        $fkfs = M('yxhb_fkfs')->field('id as val,fk_name as name')->where(array('id' =>$res['nfkfs']))->order('id asc')->find();
        $result[] = array('name'=>'付款方式：',
                                     'value'=>$fkfs['name'],
                                     'type'=>'number'
                                    );

        $result[] = array('name'=>'用款金额：',
                                     'value'=>-$res['nmoney'],
                                     'type'=>'number'
                                    );

        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['njbr'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'用款用途：',
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
        $res    = $this->record($id);
        $result = array(
            'sales'   => $res['njbr'],
            'title2'  => '用款金额',
            'approve' => "&yen;".number_format(-$res['nmoney'],2,'.',',')."元",
            'notice'  => $res['ntext'],
            'date'    => $res['jl_date'],
            'title'   => '收款单位',
            'name'    => $res['skdw'], 
            'modname' => 'CostMoney',
            'stat'    => $this->transStat($res['stat'])
        );
        return $result;
        return $result;
    }

    /**
     * 矿粉物料配置
     */
    public function submit(){
        $system = 'yxhb';
        $fylx   = I('post.fylx');
        $fkfs   = I('post.fkfs');
        $skzh   = I('post.skzh');
        $khyh   = I('post.khyh');
        $skdw   = I('post.skdw');
        $ykje   = I('post.ykje');
        $text   = I('post.text');
        $sqlx   = I('post.sqlx');
        $copyto_id = I('post.copyto_id');
        $imagepath = I('post.imagepath');   
        
        if(!M($system.'_feefy')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $dh = $this->getDhId();
        $bm = D('YxDetailAuth')->authGetBmId($system);
        $stat = $sqlx == 1?3:1;
        $fyfy = $sqlx == 1?0:$fylx;
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
            'skdw'     => $skdw
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
            'nfylx'   => $fylx,
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
}