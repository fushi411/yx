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
            'id'   => $id  
        );
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

        $result['content'][] = array('name'=>'申请单位：',
                                     'value'=>'环保矿粉物料补录',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['sb_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
 
        $result['content'][] = array('name'=>'生产时间：',
                                     'value'=> $res['pruduct_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
       
        $result['content'][] = array('name'=>'生产线路：',
                                     'value'=> $res['scx'] ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
       
        $result['content'][] = array('name'=>'生产品种：',
                                     'value'=>$res['product'],
                                     'type'=>'number',
                                     'color' => 'black;'
                                    );

        $result['content'][] = array('name'=>'入库库号：',
                                     'value'=>$res['ku'],
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $html = $this->makeHtml($res['amend']);
        $result['content'][] = array('name'=>'补录详情：',
                                     'value'=>$html,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );    
       
        
        $result['imgsrc'] = '';
        $result['applyerID'] = D('YxhbBoss')->getIDFromName($res['tjr']);
        $result['applyerName'] = $res['tjr'];
        $result['stat'] = $res['stat'];
        return $result;
    }

    /**
     * 发货详情html生成
     * @param array   $data 配比数据
     * @return string $html 
     */
    public function makeHtml($data){
        $data = json_decode($data,true);
        foreach( $data as $v){
            $html.="<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='{$v['pd']}'>";
            if(count($v['data']) == 1){
                $html .= "<input class='weui-input' type='text' style='color: black;padding: 3px 0 0 10px;'  readonly value='{$v['data'][0]['name']}'>";
            }else{
                $name = '';
                $bili = '';
                foreach($v['data'] as $vData){
                    $name .= $vData['name'].':';
                    $bili .= $vData['value'].':';
                }
                $name = trim($name,':');
                $bili = trim($bili,':');
                $html .= "<input class='weui-input' type='text' style='color: black;padding: 3px 0 0 10px;'  readonly value='{$name}'>";
                $html .= "<input class='weui-input' type='text' style='color: black;padding: 3px 0 0 10px;'  readonly value='分配比例：{$bili}'>";
            }
        }
        return $html;
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
        $res    = $this->record($id);
       
        $result = array(
            'first_title'    => '生产品种',
            'first_content'  => $res['product'],
            'second_title'   => '入库库号',
            'second_content' => $res['ku'],
            'third_title'    => '相关说明',
            'third_content'  => !empty($res['info'])?$res['info']:'无',
            'stat'           => $res['stat'],
            'applyerName'    => $res['tjr'],
        );
        return $result;
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        //之前的恢复
        $data = $this->record($id);
        $map = array('id' => $data['calid']);
        $this->field(true)->where($map)->setField('stat',1);
        // 取消本条记录
        $map = array('id' => $id);
        return $this->field(true)->where($map)->setField('stat',0);
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
        $starttime = date('Y-m-d H:i:s',$start);
        $endtime   = date('Y-m-d H:i:s',$end);
        // 查看当前最近的一个物料配置 
        $data = $this->getMateriel($starttime,1);
      
        $temp = $data;
        // 重复提交
        if(!M('yxhb_materiel')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $enddata = $this->getMateriel($endtime,2);
        // 时间重叠取消
        if( strtotime($data['pruduct_date']) == $start ) {
            $this->calMateriel($data['id']);
        }
        $data['calid'] = $data['id'];
        $materiel = json_decode($data['data']);
        $materiel = array_merge($materiel,$sb);
        $data['data']  = json_encode($materiel);
        $data['amend'] = json_encode($sb);
        $data['type']  = 2;
        $data['tjr']   = $user;
        $data['pruduct_date'] = $starttime;
        
        unset($data['id']);
        $result = $this->add($data);
       
        // 结束时间不为下一个开始时间，插入原来的
        if( strtotime($enddata['pruduct_date']) != $end ){
            unset($temp['id']);
            $temp['pruduct_date'] = $endtime;
            $this->add($temp);
        }

       
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
   
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }

    // 获取物料配置 
    public function getMateriel($date,$id){
        $str = 'egt';
        $sort = 'asc';
        if($id == 1){
            $str = 'elt';
            $sort = 'desc,id desc';
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
       // 110 品管
       $wxid   = session('wxid');
       $res   = M('yx_bm_access')->where(array('wx_id' => $wxid,'bm_id' => 110,'main' => 1))->find();
       return empty($res)?2:1; // 1 品管 2 生产
    }

}