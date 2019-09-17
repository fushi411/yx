<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkArrearsfmhLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_arrears_fmh';
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
                                     'value'=>'粉煤灰欠款通知',
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
                                     'value'=> $info['line'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'已有临额：',
                                     'value'=>$info['ed'],
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
        $result = array();
        if(empty($res) || !is_array($res)) return '';
        $clientid   = $res['clientid']; 
        $date = $res['date'];
        // 计算应收额度
        $post_data = array(
            'name' => $clientid,
            'auth' => data_auth_sign($clientid),
            'date' => $date
          );
        $res = send_post('http://www.fjyuanxin.top/sngl/getFmhClientCreditApi.php', $post_data);
        
        $ysye = $res['ysye'];
        if(empty($res['xyed'])) $res['xyed'] = 0;
        $result['flag'] = $ysye<20000?true:false;
        $result['ye'] =  number_format($ysye,2,'.',',')."元";  // 应收
        $result['line'] =  number_format($res['xyed'],2,'.',',')."元";  // 信用额度
        $result['ed']   = number_format($res['tmp'],2,'.',',')."元"; // 临时额度
        $result['fhye'] = number_format($res['ye'],2,'.',',')."元"; // 发货余额
        return $result;
    }
    public function submit(){
       
    }
    
}