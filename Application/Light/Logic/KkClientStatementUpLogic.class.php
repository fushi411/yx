<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkClientStatementUpLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_clientstatement_up';

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
        $result['content'][] = array('name'=>'系统类型：',
                                     'value'=>'建材上传对账回执',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );  
        $result['content'][] = array('name'=>'对账详情：',
                                     'value'=> '查看对账信息',
                                     'type'=>'string',
                                     'color' => '#337ab7',
                                     'id' => 'look',
                                    );          
        $data = M('kk_clientstatement')->where(array('id' => $res['aid']))->find();
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>D('kk_guest2')->getName($data['client']),
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'期初余额：',
                                     'value'=>"&yen;".number_format(-$data['qcqk'],2,'.',','),
                                     'type'=>'data',
                                     'color' => $data['qcqk']>0?'black;font-weight: 600;':'#f12e2e;font-weight: 600;',
                                    );
        $result['content'][] = array('name'=>'本期合计：',
                                     'value'=>"&yen;".number_format($data['totalje'],2,'.',','),
                                     'type'=>'data',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'本期付款：',
                                     'value'=>"&yen;".number_format($data['bqyf'],2,'.',','),
                                     'type'=>'data',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'其他调整：',
                                     'value'=>"&yen;".number_format($data['qtje'],2,'.',','),
                                     'type'=>'data',
                                     'color' => 'black;font-weight: 600;'
                                    );
        $result['content'][] = array('name'=>'期末余额：',
                                     'value'=>"&yen;".number_format(-$data['qmje'],2,'.',','),
                                     'type'=>'data',
                                     'color' => $data['qmje']>0?'black;font-weight: 600;':'#f12e2e;font-weight: 600;',
                                    );
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['bz']?$res['bz']:'无',
                                     'type'=>'data',
                                     'color' => 'black;'
                                    );
        $mydata = array(
            'id' => $data['client'],
            'auth' => data_auth_sign($data['client']),
            'stday' => $data['stday'],
            'enday' =>$data['enday'],
            'user_name' => D('kk_guest2')->getName($data['client']));
        $result['mydata'] = $mydata;
        $imgsrc = explode('|', $res['file']) ;
        $image = array();
        $imgsrc = array_filter($imgsrc);
        foreach ($imgsrc as $key => $value) {
            $image[] = 'http://www.fjyuanxin.com/WE/Public/upload/dzd/'.$value;
        }
        $result['imgsrc'] = $image;
        $result['applyerName'] = $res['rdy'];
        $result['applyerID'] = D('kk_boss')->getIDFromName($res['rdy']);
        $result['stat'] = $res['stat'];
        return $result;
    }

    /**
     * 退审 stat 为3
     */
    public function refuseRecord($id){
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
                                     'value'=> date('m-d H:i',strtotime($res['dtime'])),
                                     'type'=>'date'
                                    );
         $res = $this->record($id);
        $result = array();
        $result[] = array('name'=>'系统类型：',
                                     'value'=>'建材',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $data = M('kk_clientstatement')->where(array('id' => $res['aid']))->find();
        $result[] = array('name'=>'客户名称：',
                                     'value'=>D('kk_guest2')->getName($data['client']),
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'起始日期：',
                                     'value'=>$data['stday'],
                                     'type'=>'data'
                                    );
        $result[] = array('name'=>'结束日期：',
                                     'value'=>$data['enday'],
                                     'type'=>'data'
                                    );
        return $result;
    }

        /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res    = $this->record($id);
        $ntext = $res['bz']?$res['bz']:'无';
        $data = M('kk_clientstatement')->where(array('id' => $res['aid']))->find();
        $temp = array(
            array('title' => '客户名称' , 'content' => D('kk_guest2')->getName($data['client']) ),
            array('title' => '开始时间' , 'content' => $data['stday']  ),
            array('title' => '结束时间' , 'content' => $data['enday'] ),
            array('title' => '相关说明' , 'content' => $ntext  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['stat'],
            'applyerName'    => $res['rdy'],
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
        return $this->field(true)->where($map)->setField('stat',0);
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
     * 获取有效总客户
     */
    public function getCustomerList(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
        $res = M()->query($sql);
        return $res;
    }

    // 获取用户详情
    public function getCustomerInfo(){
        $user_id    = I('post.user_id');
        return data_auth_sign($user_id);
    }
    // 提交
    public function submit(){
        $info      = I('post.info');
        $text      = I('post.text');
        $files     = I('post.file_names');
        $copyto_id = I('post.copyto_id');
        // 流程检验
        $pro = D('KkAppflowtable')->havePro('ClientStatementUp','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        if(!M('kk_clientstatement_up')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $add = array(
            'aid' => $info['id'],
            'stat' => 2,
            'file' => $files,
            'bz'   => $text,
            'dtime'=> date('Y-m-d H:i:s'),
            'rdy'  => session('name'),
        );
        $result = M('kk_clientstatement_up')->add($add);
        if(!$result) return array('code' => 404,'msg' => '提交失败，请重新尝试！');
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'ClientStatementUp', $result);
        }
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('ClientStatementUp', $result, $salesid, 'kk');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }

    // 交集时间获取
    public function getList(){
        $user_id   = I('post.client');
        $start_day = I('post.stday');
        $end_day   = I('post.enday');
        $page      = I('post.page');
        $system    = I('post.system');
        $page = empty($page)?1:$page;
        $where =array(
            'att_sel' => array('neq','有'),
            'stat'  => 1,
        );
        if($user_id) $where['client'] = $user_id; 
        if($start_day) $where['stday'] = array('egt',$start_day); 
        if($end_day)   $where['enday'] = array('elt',$end_day);
        $data = M($system.'_clientstatement')->where($where)->order('dtime DESC')->limit((($page-1)*20).",20")->select();
        $data = $this->reInfo($data);
        return $data;
    }
    // 对账信息重构
    public function reInfo($data){
        $res = array();
        if(!is_array($data) || empty($data) ) return $res;
        $boss = D('Kk_boss');
        foreach($data as $v){
            $temp = array();
            $temp['id'] = $v['id'];
            $temp['client_name'] = D('kk_guest2')->getName($v['client']);
            $temp['datetime'] = $v['stday'].' 至 '.$v['enday'];
            $temp['stday'] = $v['id'];
            $temp['enday'] = $v['id'];
            $temp['client'] = $v['id'];
            $temp['tjdate'] = date('Y-m-d',strtotime($v['dtime']));
            $temp['newdate'] = date('Y-m-d H:i',strtotime($v['dtime']));
            $temp['qcqk'] = "&yen;".number_format($v['qcqk'],2,'.',',');
            $temp['qc_int'] = $v['qcqk'];
            $temp['qmje_int'] = $v['qmje'];
            $temp['totalje'] = "&yen;".number_format($v['totalje'],2,'.',',');
            $temp['bqyf'] = "&yen;".number_format($v['bqyf'],2,'.',',');
            $temp['qtje'] = "&yen;".number_format($v['qtje'],2,'.',',');
            $temp['qmje'] = "&yen;".number_format($v['qmje'],2,'.',',');
            $temp['tjr'] = $boss->getNameFromID($v['applyuser']);
            $temp['hz'] = $v['att_sel'] == '有'?'有':'无';
            $res[] = $temp;
        }
        return $res;
    }

    // 关联对账单获取
    public function getInfo(){
        $id = I('post.id');
        $system    = I('post.system');
        $data = M($system.'_clientstatement')->where(array( 'id' => $id ))->select();
        $data = $this->reInfo($data);
        return $data[0];
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
        $rootPath = "/www/web/default/WE/Public/upload/dzd/";
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