<?php
namespace Light\Logic;
use Think\Model;
use  Vendor\Overtrue\Pinyin\Pinyin;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkTaskLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yx_task';
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
                                     'value'=>'任务管理',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['createtime'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['submittime'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $fun = M('yx_task_mod')->select();
        $result['content'][] = array('name'=>'模块类型：',
                                    'value'=> $fun[$res['mod']]['name'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        if($res['modtwo'] != 0 ) $data = M('kk_menu')->where(array('id' => $res['modtwo']))->find();

        $result['content'][] = array('name'=>'二级类型：',
                                     'value'=> $res['modtwo'] == 0?'无':$data['name'] ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        if($res['modthree'] != 0 ) $data = M('kk_menu')->where(array('id' => $res['modthree']))->find();
        $result['content'][] = array('name'=>'三级类型：',
                                     'value'=> $res['modthree'] == 0?'无':$data['name'] ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $mod = M('yx_task_fuc')->where(array('id' => $res['function']))->find();              
        $result['content'][] = array('name'=>'功能定位：',
                                     'value'=>$mod['name'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'需求标题：',
                                     'value'=> $res['title'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'需求描述：',
                                     'value'=> $res['content'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID'] =  D('KkBoss')->getIDFromWX($res['tjr_sign']);
        $result['applyerName'] = D('KkBoss')->getNameFromWX($res['tjr_sign']);
        $result['stat'] =  $this->transStat($res['stat']);
        return $result;
    }

    public function transStat($stat){
        $statArr = array(
            0 => 0 ,
            1 => 0 ,
            2 => 0 ,
            3 => 2 ,
            4 => 1 ,
            5 => 3 ,
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
        $save = array(
            'stat' => 2,
            'is_sign' => 0,
        );
        return $this->field(true)->where($map)->save($save);
    }
    /**
     * 拒收
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function refuseRecord($id)
    {
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',5);
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
        return $this->field(true)->where($map)->getField('tjr_sign');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $fun = M('yx_task_mod')->select();
        $mod = M('yx_task_fuc')->where(array('id' => $res['function']))->find();
        $temp = array(
            array('title' => '模块类型','content' => $fun[$res['mod']]['name'] ),
            array('title' => '功能定位','content' => $mod['name'] ),
            array('title' => '需求描述','content' => $res['content'] ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['stat']),
            'applyerName'    => D('KkBoss')->getNameFromWX($res['tjr_sign']),
        );
        return $result;
    }
  
    /**
     * 任务提交 
     */
    public function submit(){
        $sign = I('post.sign');
        $sign = trim($sign,',');
        $sign_arr = explode(',',$sign);
        $task_id  = I('post.task_id'); 
        if(empty($sign) || empty($task_id)) return array('code' => 404,'msg' => '参数错误，请联系管理员');
        // 重复提交处理
        if(!M('yx_task')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        // 数据处理
        $save = array(
            'stat' => 3,
            'is_sign' => 1,
            'tjr_sign' => session('wxid'),
            'submittime' => date('Y-m-d H:i:s',time()),
        );
        $res = M('yx_task')->where(array('id' => $task_id))->save($save);
        // 签收通知
        $all_arr = array();
        foreach($sign_arr as $val){
            $per_name = M('kk_boss')->where(array('wxid'=>$val))->Field('name,id')->find();
            $data = array(
                'pro_id'        => 144,
                'aid'           => $task_id,
                'per_name'      => $per_name['name'],
                'per_id'        => $per_name['id'],
                'app_stat'      => 0,
                'app_stage'     => 1,
                'app_word'      => '',
                'time'          => date('Y-m-d H:i',time()),
                'approve_time'  => '0000-00-00 00:00:00',
                'mod_name'      => 'Task',
                'app_name'      => '签收',
                'apply_user'    => '',
                'apply_user_id' => 0, 
                'urge'          => 0,
            );
            $all_arr[]=$data;
        }
        
        $boss_id = implode('|',$sign_arr);
        M('kk_appflowproc')->addAll($all_arr);
        D('WxMessage')->ProSendCarMessage('kk','Task',$task_id,$boss_id,session('kk_id'),'QS');
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$task_id,'data' => $all_arr);
    }
    
}