<?php
namespace Light\Logic;
use Think\Model;


/**
 * 建材新增总客户
 * @author 
 */

class KkContractApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'kk_ht';
    


    /**
     * 备案客户获取
     * @param string $data  拼音缩写
     * @return array $res   备案客户结果
     */
    public function getCustomerList(){
        $keyWord = I('math');
        $model   = D('Guest');
        $data    = $model->getKkrecordUser($keyWord);
        return $data;
    }
    
    /**
     * 子客户列表信息
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    public function getCustomerInfo(){
        $id = I('post.user_id');
        $map = array(
            'reid'=>$id,
            'g_stat3'=>1
        );
        $data = array();
        $res = M('kk_guest2')->field('id,g_name')->where($map)->select();
        $data['model'] = $this->getModel($id);
        if (empty($res)) return array('code' => 404,'data' => $data);
        $data['data']  = $res;
        
        return array('code' => 200 , 'data' =>$data);
       
    }
    /**
     * 总客户的 运输和结算方式
     * @param  integer $id 记录ID
     * @return array    所需内容
     */
    public function  getModel($id){
        $field = 'g_kpfs as kf,g_jsfs as js';
        $info = M('kk_guest2')->field($field)->where('id='.$id)->find();
        return $info;
    }

}