<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 发货删除查看
 * @author 
 */

class KkfhdelApplyLogic extends Model {

    protected $trueTableName = 'kk_fh';  // 实际表名
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

    //详情(点击查看之后显示)
    public function recordContent($id)
    {
        $res = $this->record($id);
        $aid = $res['fh_client'];
        $map = array(
            'id'=>$aid,
        );
        $res2 = M("kk_guest2")->where($map)->find();                         //客户表
        $res3 = M("kk_fhdel")->order('del_date DESC')->where(array('fh_id'=>$res['id']))->find();    //发货删除记录表
        $html = $this->makeDeatilHtml($res);
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'建材发货删除',
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'提交时间：',
            'value'=>substr($res3['del_date'],5,11),      //从第6位开始截取
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'申请日期：',
            'value'=>$res3['del_da'],
            'type'=>'date',
            'color' => 'black'
        );


        $result['content'][] = array('name'=>'客户名称：',
            'value'=>$res2['g_name'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'发货日期：',
            'value'=>substr($res['fh_date'],0,16),
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'发货详情：',
            'value'=>$html,
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'申请理由：',
            'value'=>$res3['del_reason'],
            'type'=>'string',
            'color' => 'black'
        );

        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromName($res3['del_person']);            //申请删除者的id
        $result['applyerName'] = $res3['del_person'];                                       //申请删除者的姓名
        $result['stat'] = $this->transStat($res['id']);                                     //审批状态
        return $result;
    }

    // 状态值转换
    public function transStat($id){
       $res = M('kk_fh')->where(array('id'=>$id))->find();
       if ($res['fh_stat'] == 1 && $res['fh_stat4'] == 1) return 2;         //審批中
       if ($res['fh_stat'] == 0) return 1;                                  //審批通過
       if ($res['fh_stat'] == 1 && $res['fh_stat4'] == 0) return 3;         //撤銷
    }


    /**
     * 发货删除详情html生成
     * @param array   $data 配比数据
     * @return string $html
     */
    public function makeDeatilHtml($data){
        $html = "<input class='weui-input' type='text' style='color: black;'  readonly value='提货单号：{$data['fh_num']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='产品品种：{$data['fh_cate']} '>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='发货库号：{$data['fh_kh']}'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='产品编号：{$data['fh_snbh']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='我方重量：{$data['fh_zl']}'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='承运车号：{$data['fh_carnum']}'> 
                  <input class='weui-input' type='text' style='color: black;'  readonly value='运输方式：{$data['fh_wlfs']}'>
                  <input class='weui-input' type='text' style='color: black;'  readonly value='开票人员：{$data['fh_kpy']}'>";
        return $html;
    }

    /**
     * 撤銷申請調用的方法
     * @param  integer $id 记录ID
     */
    public function delRecord($id)
    {
        $map = array('id' => $id);
        $res = M('kk_fh')->where($map)->find();
        if($res['fh_bzfs']!='散装'){
            $r = M('kk_gb')->where(array('fh_num'=>$res['fh_num']))->setField('gb_stat', 1);        //簡單粗暴的改為1
        }
        $g['fh_stat'] = 1;
        $data = json_decode($res['fh_zt_last']);
        $g['fh_stat4'] = $data[0];
        $g['fh_flag'] = $data[1];
        return $this->field(true)->where($map)->data($g)->save();
    }

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    //审批助手显示
    public function getDescription($id){
        $res = $this->record($id);
        $aid = $res['fh_client'];
        $map = array(
            'id'=>$aid,
        );
        $res2 = M("kk_guest2")->where($map)->find();                          //客户表
        $res3 = M("kk_fhdel")->order('del_date DESC')->where(array('fh_id'=>$res['id']))->find();    //发货删除记录表
        $result = array();
        $result[] = array('name'=>'提交时间：',
            'value'=> date('m-d H:i',strtotime($res3['del_date'])) ,
            'type'=>'date'
        );

        $result[] = array('name'=>'提交时间：',
            'value'=>$res3['del_da'],
            'type'=>'date',
        );

        $result[] = array('name'=>'客户名称：',
            'value'=>$res2['g_name'],
            'type'=>'string'
        );

        $result[] = array('name'=>'发货日期：',
            'value'=> $res['fh_date'] ,
            'type'=>'date'
        );

        $result[] = array('name'=>'申请理由：',
            'value'=>$res3['del_reason'],
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
        return $this->field(true)->where($map)->getField('salesid');
    }

    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $aid = $res['fh_client'];
        $map = array(
            'id'=>$aid,
        );
        $res2 = M("kk_guest2")->where($map)->find();                          //客户表
        $res3 = M("kk_fhdel")->where(array('fh_id'=>$res['id']))->find();    //发货删除记录表
       
        $result = array(
            'first_title'    => '提货单位',
            'first_content'  => $res2['g_name']?$res2['g_name']:'无',
            'second_title'   => '客户类型',
            'second_content' => $res['fh_wlname']?$res['fh_wlname']:'无',
            'third_title'    => '相关说明',
            'third_content'  => $res3['del_reason']?$res3['del_reason']:'无',
            'stat'           => $this->transStat($res['id']),
        );
        return $result;
    }


    /**
     * 获取采购付款 供应商信息
     */
    public function getFhList(){
        $today    = date('Y-m-d',time()+24*3600);
        $yestoday = date('Y-m-d',time()-100*24*3600);
        $clientid = I('post.client');
        $id       = I('post.id');
        $time     = I('post.date');
        $page     = I('post.page');
        $page     = $page?$page:1;
        if($time) $time = date('Y-m-d',strtotime($time));
        $complex  = array(
            '_logic'  => 'or',
            'fh_stat' => 1,
            'fh_flag' =>1
        );

        $where    = array(
            'fh_stat'  => 1,
            'fh_date'  => array(array('elt',$today),array('egt',$yestoday),'and'),
            '_complex' => $complex
        );
        if($clientid) $where['fh_client'] = $clientid;
        if($id)       $where['id']        = $id;
        if($time)     $where['fh_da']     = $time;
        $data = M('kk_fh')->where($where)->order('fh_date DESC')->limit((($page-1)*20).",20")->select();

        $data = $this->fhInfo($data);
        return $data;
    }

    /**
     * 发货信息重构
     */
    public function fhInfo($data){
        if(!is_array($data)) return $data;
        foreach($data as $k => $v){
            $clientname = M('kk_guest2')->field('g_name')->where(array('id' => $v['fh_client']))->find();
            $data[$k]['fh_clientname'] = $clientname['g_name'];
            $data[$k]['fh_newdate']    = date('Y-m-d H:i',strtotime($v['fh_date']));
        }
        return $data;
    }

    /**
     * 合同客户获取
     * @param string $data  拼音缩写
     * @return array $res   合同用户结果
     */
    public function getCustomerList(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"and (g_helpword like '%{$data}%' or g_name like '%{$data}%')":'';
        $sql  = "SELECT a.id AS id,
                        g_name as text,
                        g_khjc as jc,
                        b.ht_wlfs as wl,
                        b.ht_cate as cate
                FROM
                    kk_guest2 AS a,
                    kk_ht AS b
                WHERE
                    a.id = b.ht_khmc
                AND ht_stday <= '{$today}'
                AND ht_enday >= '{$today}'
                AND ht_stat = 2
                {$like}
                ORDER BY
                    g_name";
        $res = M()->query($sql);

        return $this->deal_with_customer($res);
    }

    /**
     * 对合同客户数据进行处理
     */
    public function deal_with_customer($data){
        if(!is_array($data) || empty($data)) return $data;
        $check_data = array();
        foreach($data as $v){
            $key = $v['id'];
            if(empty($check_data[$key]))
            {
                $check_data[$key] = $v;
            }else
            {
                if( strpos($check_data[$key]['cate'], $v['cate']) === false) $check_data[$key]['cate'] = $check_data[$key]['cate'].'|'.$v['cate'];
            }
        }
        $result = array();
        foreach( $check_data as $v){
            $result[] = $v;
        }
        return $result;
    }

    /**
     * 点击提交
     */
    public function fh_del(){
        $user       = session('name');              //申请者的姓名
        $fh_id     = I('post.fh_id');               //选中发货的数据
        $text       = I('post.text');               //申请理由
        $copyto_id  = I('post.copyto_id');          //抄送人员的ID
        $file       = I('post.file_names');         //上传的附件

        // 重复提交
        if(!M('kk_fhdel')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $addData = array(
            'del_da'       => date('Y-m-d',time()),             //申请日期
            'del_date'     => date('Y-m-d H:i:s',time()),       //申请时间
            'del_reason'   => $text,                                    //申请删除的理由
            'del_person'   => $user,                                    //申请删除的人员
            'image'        => $file,                                    //上传的附件
            'fh_id'        =>$fh_id                                     //发货的id
        );
        $result = M('kk_fhdel')->add($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        $res = M('kk_fh')->where(array('id' =>$fh_id))->find();
        $data = array(
            0=>$res['fh_stat4'],
            1=>$res['fh_flag'],
        );
        $save_fh = array(
            'fh_stat4'      => 1,                   //1删除  2修改
            'fh_flag'       => 1,                   //标志位 2 修改 1 删除 3 退货修改
            'fh_zt_last'    => json_encode($data),  //删除前的状态 存入数组要用json_encode
        );
        M('kk_fh')->where(array('id' =>$fh_id))->save($save_fh);


        //把之前的审批记录清空
        M('kk_appflowproc')->where(array('id' =>$fh_id))->setField('mod_name',null);

        //把撤销记录清空
        M('kk_appflowcomment')->where(array('id' =>$fh_id))->setField('mod_name',null);

        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'fh_del_Apply', $fh_id,1);
        }
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('fh_del_Apply', $fh_id, $salesid, 'kk');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$fh_id);

    }

    /*
   * 评论时多图上传
   */
    public function upImage() {
        $uploader = new \Think\Upload\Driver\Local;
        if(!$uploader){
            E("不存在上传驱动");
        }
        // 生成子目录名
        $savePath = date('Y-m-d')."/";

        // 生成文件名
        $img_str = I('post.imagefile');
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
        $rootPath = "/www/web/default/WE/Public/upload/fh/";
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

    //获取选中要删除的数据    需要连表查询
    public function getdata(){
        $id = I('post.id');
        $res = M('kk_fh')->where(array('id'=>$id))->find();
        $clientname = M('kk_guest2')->field('g_name')->where(array('id' => $res['fh_client']))->find();
        $res['fh_clientname'] = $clientname['g_name'];
        return $res;
    }
}