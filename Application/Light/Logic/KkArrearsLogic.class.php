<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkArrearsLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_arrears';
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
                                     'value'=>'建材欠款通知',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['date'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['date'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'商户全称：',
                                     'value'=> $res['g_name'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $color = $res['zhye']<20000?'#f12e2e':'black';
        $result['content'][] = array('name'=>'账户余额：',
                                     'value'=> "&yen;".number_format( $res['zhye'],2,'.',',').'元' ,
                                     'type'=>'string',
                                     'color' => $color
                                    );       

        $result['content'][] = array('name'=>'发货余额：',
                                     'value'=> "&yen;".number_format( $res['fhye'],2,'.',',').'元' ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );   
        $info = $this->getInfo($res);
        $result['content'][] = array('name'=>'信用额度：',
                                     'value'=> "&yen;".number_format($info['line'],2,'.',',').'元',
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'已有临额：',
                                     'value'=>"&yen;".number_format($info['tmp'],2,'.',',')."元",
                                     'type'=>'string',
                                     'color' => 'black'
                                    );  
        
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromWX($res['tjr']);
        $result['applyerName'] = '机器人';
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
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['date'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['date'])), 
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'商户全称：',
                                     'value'=>$res['g_name'],
                                     'type'=>'string'
                                    );

        $result[] = array('name'=>'账户余额：',
                                    'value'=> number_format($res['zhye'],2,'.',',').'元',
                                    'type'=>'string',
                                   );                    
       $result[] = array('name'=>'发货余额：',
                                    'value'=> number_format($res['fhye'],2,'.',',').'元',
                                    'type'=>'string',
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
        return $this->field(true)->where($map)->getField('tjr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $temp = array(
            array('title' => '商户全称','content' => $res['g_name']?$res['g_name']:'无'),
            array('title' => '账户余额','content' => "&yen;".number_format($res['zhye'],2,'.',',').'元' ),
            array('title' => '发货余额','content' => "&yen;".number_format( $res['fhye'],2,'.',',').'元' ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['stat'],
            'applyerName'    => '机器人',
        );
        return $result;
    }
    /**
     * 获取用户信息
     */
    public function getInfo($res){
        if(empty($res) || !is_array($res)) return '';
        $id   = $res['g_name']; 
        $date = $res['date'];
        // 信用额度
        $temp = A('TempQuote');
        $ye = $temp->getkkline($res['clientid'],$date);
        // 计算应收额度
        $post_data = array(
            'name' => $id,
            'auth' => data_auth_sign($id),
            'date' => $date
        );
        $data = send_post('http://www.fjyuanxin.com/sngl/include/getClientCreditApi.php', $post_data);
        $data['line'] = $ye;
        return $data;
    }
    public function submit(){
       
    }
    
}