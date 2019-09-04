<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCgfkApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_cgfksq';

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
        if($res['fylx'] == 1){
            $clientname = M('yxhb_gys')->field('g_name')->where(array('id' => $res['gys']))->find();
            $suffix = "(汽运)";
            if($res['htlx'] == '海运') $suffix = "(海运)";
            $clientname['g_name'] .= $suffix;
        }elseif($res['fylx'] == 2 || $res['fylx'] == 7 || $res['fylx'] == 4){
            $clientname = M('yxhb_wl')->field('g_name')->where(array('id' => $res['gys']))->find();
        }elseif($res['fylx'] == 6){
            $clientname = array( 'g_name' => $res['pjs']);
        }
        
        $color = $res['yfye'] > 0? '#f12e2e':'black';
        $result['content'][] = array('name'=>'系统类型：',
                                     'value'=>'环保原材料采购',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['zd_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
    
        if($res['fylx'] == 1){
            $result['content'][] = array('name'=>'应付余额：',
            'value'=> "&yen;".number_format($res['yfye'],2,'.',',')."元",
            'type'=>'number',
            'color' => $color
           );
        }
        $result['content'][] = array('name'=>'付款金额：',
                                     'value'=>"&yen;".number_format($res['fkje'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'大写金额：',
                                     'value'=>cny($res['fkje']),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $fpsm = array('已到','未到','无票');
        $color = $res['fpsm']-1==1?'#f12e2e':'black';
        $result['content'][] = array('name'=>'发票说明：',
                                     'value'=> $fpsm[$res['fpsm']-1],
                                     'type'=>'date',
                                     'color' => $color
                                    );
        $fkfs = '暂无';
        if($res['fkfs'] == 4 ){
            $fkfs = '现金';
        }elseif($res['fkfs'] == 2 ){
            $fkfs = '公户';
        }elseif ($res['fkfs'] == 3 ) {
            $fkfs = '汇票';
        }
        $result['content'][] = array('name'=>'付款方式：',
                                     'value'=>$fkfs,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );    
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['zy'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $imgsrc = explode('|', $res['fj']) ;
        $image = array();
        $imgsrc = array_filter($imgsrc);
        foreach ($imgsrc as $key => $value) {
            $image[] = 'http://www.fjyuanxin.top/WE/Public/upload/cg/'.$value;
        }
        $result['imgsrc'] = $image;
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['rdy']);
        $result['applyerName'] = $res['rdy'];
        $result['stat'] = $this->transStat($res['stat']);
        return $result;
    }

    public function transStat($stat){
        $statArr = array(
            4 => 2 ,
            3 => 2 ,
            2 => 1 ,
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
        if($res['fylx'] == 1){
            $clientname = M('yxhb_gys')->field('g_name')->where(array('id' => $res['gys']))->find();
            
        }elseif($res['fylx'] == 2 || $res['fylx'] == 7 || $res['fylx'] == 4){
            $clientname = M('yxhb_wl')->field('g_name')->where(array('id' => $res['gys']))->find();

        }elseif($res['fylx'] == 6){
            $clientname = array( 'g_name' => $res['pjs']);
        }
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['date'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=> date('m-d H:i',strtotime($res['zd_date'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'string'
                                    );    
        if($res['fylx'] == 1){
            $result[] = array('name'=>'应付余额：',
                                     'value'=> number_format($res['yfye'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        }

        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['fkje'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $fpsm = array('已到','未到','无票');
        $result[] = array('name'=>'发票说明：',
                                     'value'=> $fpsm[$res['fpsm']-1],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['rdy'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['zy'],
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
        return $this->field(true)->where($map)->getField('rdy');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res    = $this->record($id);
        if($res['fylx'] == 1 ){
            $name = M('yxhb_gys')->field('g_name')->where(array('id' => $res['gys']))->find();
            $modname = 'CgfkApply';
            $title = $res['fylx'] == 1?'供货单位':'卸船码头';
        }elseif($res['fylx'] == 2 || $res['fylx'] == 7 || $res['fylx'] == 4){
            $name = M('yxhb_wl')->field('g_name')->where(array('id' => $res['gys']))->find();
            $modname = 'WlCgfkApply';
            $title = '运输公司';
        }elseif($res['fylx'] == 6){
            $name = array( 'g_name' => $res['pjs']);
            $modname = 'PjCgfkApply';
            $title = '配件公司';
        }
        $temp = array(
            array('title' => $title , 'content' => $name['g_name']?$name['g_name']:'无' ),
            array('title' => '申请金额' , 'content' => number_format($res['fkje'],2,'.',',')."元"  ),
            array('title' => '相关说明' , 'content' => $res['zy']  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['stat']),
            'applyerName'    => $res['rdy'],
        );
        return $result;
    }

    /**
     * 获取采购付款 供应商信息
     */
    public function getCustomerList(){

        $word = I('math');
        $sql = "select 
                    a.id as id,g_name as text 
                from 
                    yxhb_gys as a,yxhb_cght as b 
                where 
                    a.id=b.ht_gys and b.ht_stat=2 and (a.g_helpword like '%{$word}%' or a.g_name like '%{$word}%')
                group by 
                    a.id 
                order by 
                    g_name asc";
        $res = M()->query($sql);
        $res = $this->addSuffix($res,'(汽运)');
        $sql = "SELECT
                    a.id as id,
                    a.g_name as text
                FROM
                    yxhb_gys as a,
                    yxhb_cght_dz AS b
                WHERE
                    a.id=b.ht_gys
                AND b.ht_stat = 2
                and (a.g_helpword like '%{$word}%' or a.g_name like '%{$word}%')
                GROUP BY
                    a.id
                ORDER BY
                    a.g_name ASC";
            $hyres = M()->query($sql);
            $hyres = $this->addSuffix($hyres,'(海运)');
            $res = array_merge($res,$hyres);
        return $res;
    }

      /**
     * 添加尾缀
     */ 
    public function addSuffix($data,$suffix){
        if(!is_array($data)) return;
        $temp = array();
        foreach($data as $k=>$v){
            $v['text'] .= $suffix;
            $temp[] = $v;
        }
        return $temp;
    }


    /**
     * 单号获取
     * @param string $system 系统
     * @return string $id    单号
     */
    public function getDhId(){
        $today = date('Y-m-d',time());
        $sql   = "select * from yxhb_cgfksq where date_format(date, '%Y-%m-%d' )='{$today}' and dh like 'CS%'";
        $res   = M()->query($sql);
        $count = count($res);
        $time  = str_replace('-','',$today);
        $id    = "CS{$time}";
        if($count < 9)  return  $id.'00'.($count+1);
        if($count < 99) return $id.'0'.($count+1);
        return $id.$count;
    }

    /**
     * 原材料采购付款提交 
     */
    public function submit(){
        $today = date('Y-m-d',time());
        $user  = session('name');
        $val   = $this->cgfkValidata();
        $ysye  = I('post.ysye');
        $bank  = I('post.type');
        $is_hy = I('post.is_hy');
        $gyszh = I('post.gyszh');
        $fpsm  = I('post.fpsm');
        $file_names = I('post.imagepath');
        $copyto_id = I('post.copyto_id');
        if(!$val['bool']) return $val;
        list($user_id, $notice,$money,$system) = $val['data'];
        // 流程检验
        $pro = D('YxhbAppflowtable')->havePro('CgfkApply','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        // 重复提交
        if(!M('yxhb_cgfksq')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $addData = array(
            'dh'      => $this->getDhId(),
            'zd_date' => $today,
            'fkje'    => $money,
            'gys'     => $user_id,
            'zy'      => $notice,
            'clmc'    => '',
            'fkfs'    => $bank,
            'rdy'     => $user,
            'bm'      => 1,
            'stat'    => 3,
            'sqr'     => $user,
            'clgg'    => '无',
            'htbh'    => '无',
            'cwbz'    => '',
            'jjyy'    => '',
            'gyszh'   => $gyszh,
            'date'    => date('Y-m-d H:i:s',time()),
            'fylx'    => 1,
            'htlx'    => $is_hy,
            'yfye'    =>  $ysye,
            'fpsm'    => $fpsm,
            'fj'      => $file_names,
        ); 
       
        $result = M('yxhb_cgfksq')->add($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'CgfkApply', $result);
        }
        
        $wf = A('WorkFlow');
        $salesid = session('yxhb_id');
        $res = $wf->setWorkFlowSV('CgfkApply', $result, $salesid, 'yxhb');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }

    /**
     * 采购付款提交信息校验
     */
    public function cgfkValidata(){
        $user_id = I('post.user_id');
        $notice = I('post.text');
        $money = I('post.money');

        // 公司检测
        if($user_id == '' || $user_id <= 0) return array('bool'=> false, 'msg' => '请选择供货公司');
        // 金额 
        if($money == '' || $money > 1000000000000 ) return array('bool'=> false, 'msg' => '付款金额错误');
        // 备注检测
        if(strlen($notice)<5) return array('bool'=> false, 'msg' => '备注不能少于5个字');

        return array('bool'=> true, 'data' => array($user_id, $notice,$money,$system));
    }  

    /**
     * 获取环保应收余额
     */
    public function getSupplyPaymentApi(){
        $id   = I('post.user_id');
        $auth = data_auth_sign($id);
        // 计算应收额度
        $post_data = array(
            'auth' => $auth,
            'id'   => $id
        );
        $res = send_post('http://www.fjyuanxin.top/yxhb/include/getSupplyPaymentApi.php', $post_data);
        return $res;
    }

    /**
     * 获取银行账号信息
     */
    public function bankInfo(){
        $gys   = I('post.user_id'); 
        $type  = I('post.type');
        $where = array(
            'bank_stat' => 1,
            'bank_gys'  => $gys,
            'bank_lx'   => $type
        );
        $data  = M('yxhb_bankgys')->field('bank_gys,bank_zhmc,bank_account,bank_khh,bank_lx,id')->where($where)->select();
        foreach($data as $k => $v){
            $account = $v['bank_account'];
            $data[$k]['bank_account'] = substr($account,0,4).'****'.substr($account,-4);
        }
        return $data;
    }

    /**
     * 合同信息获取
     */
    public function getHtbh(){
        $sql = "select ht_gys,ht_clmc,ht_clgg,ht_dh,ht_wl from yxhb_cght where ht_stat='2' group by ht_gys,ht_clmc,ht_clgg,ht_dh";
        $data = M()->query($sql);
        return $data;
    }

      /**
     * 合同信息获取
     */
    public function getHyht(){
        $gys   = I('post.user_id'); 
        $wlht = M('yxhb_cght_dz')
                ->field('ht_dh')
                ->where("ht_gys={$gys}")
                ->select();
        return $wlht;
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
        $rootPath = "/data/wwwroot/default/WE/Public/upload/cg/";
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

}