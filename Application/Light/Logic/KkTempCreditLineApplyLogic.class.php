<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkTempCreditLineApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_tempcreditlineconfig';

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
        $info = $this->getInfo($res['clientid'],$res['date'],$res['clientname']);
        $info['flag'] =true;
        $result = array();
        $result['content']['date'] = $res['date'];
        $clientname = M('kk_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $result['content']['clientname'] = $clientname['g_khjc'];
        //计算应收额度
        if($info['tmpline']-$info['ye']+$res['ed'] <20000) $info['flag'] =false;
        $info['ye'] = "&yen;".number_format(-($info['tmpline']-$info['ye']+$res['ed']),2,'.',',')."元";
        $result['content']['ye'] = "&yen;".number_format($res['ye'],2,'.',',')."元";
        $result['content']['ed'] = "&yen;".number_format($res['ed'],2,'.',',')."元";
        $result['content']['line'] = "&yen;".number_format($res['line'],2,'.',',')."元";
        $result['content']['yxq'] = $res['yxq'];
        $result['content']['notice'] = $res['notice'];
        $result['content']['info'] = $info;
        $result['imgsrc'] = '';
        $result['applyerID'] = $res['salesid'];
        $result['applyerName'] = $res['sales'];
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

    public function getInfo($clientid,$date,$name){
        $result = array();
        $temp = A('TempQuote');

        $info = $temp->getQuoteTimes($clientid,'kk',$date);
        $result['two'] = 5-count($info[0]);
        $result['five'] = 3-count($info[1]);
        $result['ten'] = 1-count($info[2]);

        $ye = $temp->getkkline($clientid,$date);
        // 计算应收额度
        $post_data = array(
            'name' => $name,
            'auth' => data_auth_sign($name),
            'date' => $date
          );
        $res = send_post('http://www.fjyuanxin.com/sngl/include/getClientCreditApi.php', $post_data);
        
        // $result['ye'] = number_format(-($ye-$res['ye']+$res['tmp']),2,'.',',')."元";
        //$result['flag'] = -$res['ye']<20000?true:false;

        $result['ye'] = $res['ye'];
        $result['line'] =  "&yen;".number_format($ye,2,'.',',')."元"; // 信用额度
        $result['tmpline'] = $ye;
        return $result;
    }

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $info = $this->getInfo($res['clientid'],$res['date'],$res['clientname']);
        $clientname = M('kk_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'应收余额：',
                                     'value'=>number_format(-($info['tmpline']-$info['ye']+$res['ed']),2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'已有临额：',
                                     'value'=>number_format($res['ed'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['sales'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
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
        return $this->field(true)->where($map)->getField('sales');
    }

    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $result = array(
            'sales'   => $res['sales'],
            'approve' => number_format($res['line'],2,'.',',')."元",
            'notice'  => $res['notice'],
            'date'    => $res['date'],
            'stat'    => $res['stat']
        );
        return $result;
    }
    
}