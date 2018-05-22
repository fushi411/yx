<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkCreditLineApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_creditlineconfig';

    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array('aid' => $id);
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
        if(date('Y-m-d',strtotime($res['date'])) == date('Y-m-d',strtotime($res['dtime'])) ){
            $date = date('Y-m-d',strtotime($res['date'].'-1 day'));
        }
        $info = $this->getInfo($res['clientid'],$date,$res['clientname']);
        $clientname = M('kk_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $color = $info['flag']?'#f12e2e':'black';
        
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'应收额度：',
                                     'value'=>$info['ye'],
                                     'type'=>'number',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'信用额度：',
                                     'value'=>number_format($res['oline'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'已有临额：',
                                     'value'=>$info['ed'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请人员：',
                                     'value'=>$res['sales'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
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
        $map = array('aid' => $id);
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
        if(date('Y-m-d',strtotime($res['date'])) == date('Y-m-d',strtotime($res['dtime'])) ){
            $date = date('Y-m-d',strtotime($res['date'].'-1 day'));
        }
        $info = $this->getInfo($res['clientid'],$date,$res['clientname']);
        $clientname = M('kk_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $result[] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'应收额度：',
                                     'value'=>$info['ye'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'信用额度：',
                                     'value'=>number_format($res['oline'],2,'.',',')."元",
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'已有临额：',
                                     'value'=>$info['ed'],
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

    public function forTest($id){
        $res = $this->record($id);
        $result = array();
        if(date('Y-m-d',strtotime($res['date'])) == date('Y-m-d',strtotime($res['dtime'])) ){
            $date = date('Y-m-d',strtotime($res['date'].'-1 day'));
        }
        $info = $this->getInfo($res['clientid'],$date,$res['clientname']);
        $clientname = M('kk_guest2')->field('g_khjc')->where(array('id' => $res['clientid']))->find();
        $color = $info['flag']?'#f12e2e':'black';
        
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>$res['date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'客户名称：',
                                     'value'=>$clientname['g_khjc'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'应收额度：',
                                     'value'=>$info['ye'],
                                     'type'=>'number',
                                     'color' => $color
                                    );
        $result['content'][] = array('name'=>'信用额度：',
                                     'value'=>number_format($res['oline'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'已有临额：',
                                     'value'=>$info['ed'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请额度：',
                                     'value'=>number_format($res['line'],2,'.',',')."元",
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请人员：',
                                     'value'=>$res['sales'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['notice'],
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        
    }
    public function getInfo($clientid,$date,$name){
        $result = array();
        $temp = A('tempQuote');
       // $date = '2018-05-18';
       
        $ye = $temp->getkkline($clientid,$date); // 信用额度
        // 计算应收额度
        $post_data = array(
            'name' => $name,
            'auth' => data_auth_sign($name),
            'date' => $date
          );
        $res = send_post('http://www.fjyuanxin.com/sngl/include/getClientCreditApi.php', $post_data);
        
        // $result['ye'] = number_format(-($ye-$res['ye']+$res['tmp']),2,'.',',')."元";

        $ysye = $res['ye']-($ye+$res['tmp']);
        
        $result['flag'] = $ysye<20000?true:false;
        $result['ye'] =  number_format($ysye,2,'.',',')."元";  // 应收
       // $result['line'] =  number_format($ye['line'],2,'.',',')."元"; 
        $result['ed']   = number_format($res['tmp'],2,'.',',')."元"; // 已有额度
       // $result['fhye'] = number_format($ed+$ye['line']-$ye['ysye'],2,'.',',')."元"; 发货余额
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