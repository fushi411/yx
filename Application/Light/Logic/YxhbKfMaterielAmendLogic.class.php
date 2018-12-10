<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbKfMaterielAmendLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_materiel';
    /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function record($id)
    {
        $map = array(
            'id'   => $id,
            'type' => 2   
        );
        return $this->field(true)->where($map)->find();
    }


    // 取消某个配置
    public function calMateriel($id){
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
    }

    //提交补录的
    public function submit(){
        $user  = session('name');
        $sb    = I('post.sb');
        $start = I('post.start');
        $end   = I('post.end');
        $sec   = $this->getUserSection();
        $start = date('Y-m-d H:i:s',strtotime($start));
        $end   = date('Y-m-d H:i:s',strtotime($end));
        // 查看当前最近的一个物料配置 
        $data = $this->getMateriel($end,1);
        
        // 重复提交
        if(!M('yxhb_materiel')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        // 时间重叠取消
        if( strtotime($data['pruduct_date']) == $start ) $this->calMateriel($data['id']);
        $materiel = json_decode($data['data']);
        $materiel = array_merge($materiel,$sb);
        $data['data']  = json_encode($materiel);
        $data['amend'] = json_encode($sb);
        $data['type']  = 2;
        $data['tjr']   = $user;
        unset($data['id']);

        $enddata = $this->getMateriel($start,2);
        if( strtotime($enddata['pruduct_date']) != $end ){
            $data['type'] = 1;
            $this->add($data);
        }
        return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！','data' =>$data);
        
        
        // return array('code' => 404,'msg' =>'提交失败，请重新尝试！','data' => $addData);
        $result = M('yxhb_materiel')->add($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
   
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }
    // 获取物料配置 
    // SELECT *,2 from yxhb_materiel where pruduct_date >= '2018-12-07 03:00:00' ORDER BY pruduct_date desc LIMIT 1
    public function getMateriel($date,$id){
        $str = 'gt';
        $sort = 'desc';
        if($id == 2){
            $str = 'elt';
            $sort = 'asc';
        }
        $map = array(
            'pruduct_date' => array($str,$date),
            'stat'         => 1
        );
        $data = M('yxhb_materiel')->where($map)->order("pruduct_date {$sort}")->find();
       
        return $data;
    }

    // 判断是那个部门的人
    public function getUserSection(){
        $wxid = session('wxid');
        // 9 品管 10 生产
        $res = M('auth_group_access a')
                ->join('auth_group b on a.group_id=b.id')
                ->where(array(
                    'a.uid'      => $wxid,
                    'a.group_id' => array('neq',2),
                    'b.id'    => 9))
                ->find();
        return empty($res)?2:1; // 1 品管 2 生产
    }

}