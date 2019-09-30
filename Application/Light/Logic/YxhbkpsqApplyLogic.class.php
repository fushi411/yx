<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author
 */

class yxhbkpsqApplyLogic extends Model {

    // 实际表名
    protected $trueTableName = 'kk_gys';

    public function getTableName()
    {
        return $this->trueTableName;
    }

    public function recordContent($id)
    {
        $data = D('yxhb_xskp')->where(array('pid'=>$id))->select();
        if (empty($data)) {
            exit('err ');
        }

        $table = array();
        $kpzje = 0;
        foreach ($data as $info) {
            $kpzje = bcadd($info['kp_je'], $kpzje, 2);
            $table[] = array(
                'kp_pz' => $info['kp_pz'],
                'kp_je' => $info['kp_je'],
                'kp_dj' => $info['kp_dj'],
                'kp_sl' => $info['kp_sl']
            );
        }

        $res = $data[0];
        $res['table'] = $table;
        $result = array();
        $result['content'][] = array('name'=>'系统类型：',
            'value'=>'建材客户开票申请',
            'type'=>'string',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'开票日期：',
            'value'=>$res['kp_da'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'提货单位：',
            'value'=>$res['kp_thdw'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'开票单位：',
            'value'=>$res['kp_hkdw'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'发票种类：',
            'value'=>$res['kp_fpzl'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'所属时间：',
            'value'=>$res['kp_sda'].'至'.$res['kp_eda'],
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'开票总金额：',
            'value'=>"￥ ".$kpzje,
            'type'=>'date',
            'color' => 'black'
        );

        $result['content'][] = array('name'=>'开票详情：',
            'value'=>'查看开票详情',
            'type'=>'date',
            'color' => '#337ab7'
        );

        $result['mydata'] = $res;
        $result['imgsrc'] = '';
        $userInfo = M('yxhb_boss')->where(array('name'=>$res['kp_jbr']))->select();
        $result['applyerID'] =  $userInfo[0]['id'];     //申请者的id
        $result['applyerName'] = $res['kp_jbr'];     //申请者的姓名
        $result['stat'] = $res['kp_stat'];//审批状态
        return $result;
    }

    public function sealNeedContent($id){
        $res = D('kk_xskp')->where(array('pid'=>$id))->find();
        $temp = array(
            array('title' => '提交时间' , 'content' => date('Y-m-d H:i',strtotime($res['ji_date']. '00:00:00'))),
            array('title' => '相关说明' , 'content' => $res['kp_zy']?$res['kp_zy']:'无' ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['kp_stat'],
            'applyerName'    => $res['kp_rdy'],
        );
        return $result;
    }

    /**
     * 获取有效总客户
     */
    public function getCustomerList(){
        $data = I('math');
        $res = D('Guest')->getGuest('yxhb',$data,'kpsq_Apply');
        return $res;
    }

    public function getFormData()
    {
        $arr = array();
        $arr['kp_pz'] = I('kp_bzfs');
        $arr['kp_wlfs'] = I('kp_pz');
        $arr['client'] = I('client');
        $arr['sday'] = I('sday');
        $arr['eday'] = I('eday');
        $arr['hash'] = hash('md5', $arr['kp_pz'].$arr['kp_wlfs'].'fa39@q2@E3rdwaijs.*&&23da(&@#dwa');

        $qpData = $this->curl_request('http://www.fjyuanxin.com/yxhb/include/loadKPStatus.php', $arr);
        return array('code'=>200, 'data'=>json_decode($qpData, true));
    }

    public function saveFormData()
    {
        $formData = I('data');
        $kpThdw = I('user_name');
        $kpThdwId = I('user_id');
        $tpHkdw = I('ejkh');
        $kpDa = I('kp_date');
        $kpSda = I('start_date');
        $kpEda = I('end_date');
        $fpFpzl = I('kp_fpzl');
        $kpZy =  I('reason');
        $ejkhSel = I('ejkh_sel');
        $copyto_id = I('post.copyto_id');

        if (empty($formData) || empty($kpThdw) || empty($kpThdwId) || empty($kpDa) || empty($kpSda) || empty($kpEda) || empty($fpFpzl) || empty($kpZy)){
            return array('code'=>400, 'msg'=>'抱歉! 您当前存在必填项没有填写');
        }

        if ($ejkhSel == 'new_kpdw') {
            //新增单位
            // 实例化User模型
            $User = M('yxhb_invoice_company');
            $data['invoice_company'] = $tpHkdw;
            $data['delivery_unit'] = $kpThdw;
            $data['creat_at'] = date('Y-m-d H:i:s');
            $User->data($data)->add();
        }

        $today = date("Y-m-d 00:00:00", time());
        $toyear = date("Y", time());

        //sqbh 生成
        $sql = sprintf("select sqbh from  yxhb_xskp  where year(kp_da)='%s' group by sqbh DESC", $toyear);
        $data = $this->query($sql);
        $sqbh="SQBH".date("Ymd",time()).str_pad((count($data) + 1), 3, '0', STR_PAD_LEFT);
        //pid 生成
        $sql = sprintf("select * from yxhb_xskp where jl_date >='%s' group by pid DESC", $today);
        $data = $this->query($sql);
        $pid = date("Ymd",time()).str_pad((count($data) + 1), 3, '0', STR_PAD_LEFT);
        $saveData = array();
        foreach ($formData as $dataString) {
               $dataArr = explode(',', $dataString);
               $dataArrs = explode('-', $dataArr[0]);
               $saveData[] = array(
                   'sqbh'       =>$sqbh,
                   'kp_da'      => $kpDa,
                   'kp_sda'     =>$kpSda,
                   'kp_eda'     =>$kpEda,
                   'kp_thdw'    =>$kpThdw,
                   'kp_thdw_id' =>$kpThdwId,
                   'kp_hkdw'    =>$tpHkdw,
                   'kp_pz'      =>$dataArrs[0].$dataArrs[1],
                   'kp_sl'      =>$dataArr[3],
                   'kp_dj'      =>$dataArr[2],
                   'kp_je'      =>$dataArr[1],
                   'kp_fpzl'    =>$fpFpzl,
                   'kp_jbr'     =>session('name'), //申请人
                   'kp_zy'      =>$kpZy,
                   'kp_stat'    =>2,
                    'kp_rdy'    =>session('name'),//录单人
                   'jl_date'    =>date('Y-m-d H:i:s'),
                    'pid'       =>$pid
               );
        }

        if(!M('yxhb_xskp')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $data = M('yxhb_xskp');
        $result = $data->addAll($saveData);
        if ($result) {
            // 发送抄送消息
            $copyto_id = trim($copyto_id,',');
            if (!empty($copyto_id)) {
                D('yxhbAppcopyto')->copyTo($copyto_id,'kpsq_Apply', $pid);
            }

            // 签收通知
            $wf = A('WorkFlow');
            $salesid = session('kk_id');
            $wf->setWorkFlowSV('kpsq_Apply', $pid, $salesid, 'yxhb');
        }else{
            return array('code' => 404,'msg' =>'提交失败，请刷新页面重新尝试！');
        }

        return array('code'=>200, 'msg' => '提交成功' , 'aid' =>$pid);
    }

    public function getCustomerInfo()
    {
        $id = I('user_id');
        $kpDate = I('kp_date');
        $bigen = I('kp_sda');
        $end = I('kp_eda');
        $userName = I('user_name');

        $resArr = array();
        $sql = sprintf("SELECT ht_khmc,ht_dj,ht_yf,ht_bzfs,ht_cate,ht_stat,ht_wlfs FROM yxhb_ht AS a,yxhb_guest2 AS b WHERE a.ht_khmc=b.id AND (b.id='%s' or b.reid='%s') AND
 ht_stat='2' AND ht_enday>='%s' AND ht_stday<='%s' GROUP BY ht_wlfs,ht_cate,ht_bzfs", $id, $id, $bigen, $end);
        $data = $this->query($sql);
        if (!empty($data)) {
            foreach ($data as $info) {
                $resArr[] = $info;
            }
        }

        $invoiceData = array();
        $sql =sprintf("select ang from yxhb_dtg as b,yxhb_guest2 as c where  c.id=b.gid  and ang<>'' and  c.g_name = '%s' GROUP BY gid,ang ORDER BY gid", $userName);
        $data = $this->query($sql);
        if (!empty($data)) {
            $invoiceData = array_merge ($invoiceData, $data);
        }

        $sql = sprintf("SELECT invoice_company as ang FROM yxhb_invoice_company WHERE delivery_unit = '%s'", $userName);
        $data = $this->query($sql);
        if (!empty($data)) {
            $invoiceData = array_merge ($invoiceData, $data);
        }

        $post = array(
            "client" => $id,
            "clientname" => $userName,
            "sday" => $kpDate,
            'hash' => hash('md5',$id.$userName.$kpDate.'fa39@q2@E3rdfaw.*&&23da(&@#dwa'),
        );

        $qpData = $this->curl_request('http://www.fjyuanxin.com/yxhb/include/loadQPSJ.php', $post);

        $tableArr = array();
        if (!empty($resArr)) {
            $arr = array();
            foreach ($resArr as $resInfo) {
                $arr['kp_pz'] = $resInfo['ht_cate'];
                $arr['kp_wlfs'] = $resInfo['ht_wlfs'];
                $arr['client'] = $id;
                $arr['sday'] = $bigen;
                $arr['eday'] = $end;
                $arr['hash'] = hash('md5', $arr['kp_pz'].$arr['kp_wlfs'].'fa39@q2@E3rdwaijs.*&&23da(&@#dwa');
                $table = $this->curl_request('http://www.fjyuanxin.com/yxhb/include/loadKPStatus.php', $arr);
                $table = json_decode($table, true);
                $table['name'] = $resInfo['ht_cate'].'-'.$resInfo['ht_wlfs'];
                $tableArr[] =  $table;
            }
        }

        return array('code'=>200, 'ht_data'=>$resArr, 'invoice_data'=>$invoiceData, 'qp_data'=>json_decode($qpData, true), 'table'=>$tableArr);
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('pid' => $id);
        return M('yxhb_xskp')->where($map)->setField('kp_stat',0);
    }

    /** 参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
     * @param $url
     * @param string $post
     * @param string $cookie
     * @param int $returnCookie
     * @return bool|string
     */
    function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }
}