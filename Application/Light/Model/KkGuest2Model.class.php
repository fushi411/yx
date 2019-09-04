<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保抄送记录模型
 * @author 
 */

class KkGuest2Model extends Model {

    /**
     * 由客户名称
     * @param $id 客户ID
     * @retrun string 客户名称
     */
    public function getName($id){
        return $this->where(array('id'=>$id))->getField('g_name');
    }

    /**
     * 父级客户
     * @param $id 客户ID
     * @retrun string 客户名称
     */
    public function getParentName($id){
        $data = $this->where(array('id'=>$id))->find();
        if($data['reid'] == 0) return $data['g_name'];
        return $this->where(array('id'=>$data['reid']))->getField('g_name');
    }
}
