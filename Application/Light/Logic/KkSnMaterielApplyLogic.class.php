<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class KkSnMaterielApplyLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_materiel';

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
                                     'value'=>'环保矿粉物料配置',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['sb_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=> date('Y-m-d',strtotime($res['sb_date'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'生产时间：',
                                     'value'=> $res['pruduct_date'],
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $ratio = M('kk_zlddtz')->where('id='.$res['rid'])->find();
        $no   = str_replace('生产线',' ' ,$ratio['scx']);
        $no   = $no.date('Ymd H:i',strtotime($ratio['date'])+$ratio['hour']*3600); 
        $result['content'][] = array('name'=>'配比通知：',
                                     'value'=>$no ,
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
        $html = $this->makeHtml($res['data']);
        $result['content'][] = array('name'=>'配置详情：',
                                     'value'=>$html,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );    
        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['info'],
                                     'type'=>'text',
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
            foreach($v['data'] as $val){
                $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$val['name']}：{$val['value']}%'>";
            }
        }
        return $html;
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
        $result = array();
        $result[] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['sb_date'])),
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['sb_date'])),
                                     'type'=>'date'
                                    );
    
        $ratio = M('kk_zlddtz')->where('id='.$res['rid'])->find();
        $no   = str_replace('生产线',' ' ,$ratio['scx']);
        $no   = $no.date('Ymd H:i',strtotime($ratio['date'])+$ratio['hour']*3600); 
        $result[] = array('name'=>'配比通知：',
                                     'value'=> $no ,
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'生产品种：',
                                     'value'=>$res['product'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'入库库号：',
                                     'value'=>$res['ku'],
                                     'type'=>'number'
                                    );

        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['tjr'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'相关说明：',
                                     'value'=>$res['info'],
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
        return $this->field(true)->where($map)->getField('tjr');
    }
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res    = $this->record($id);
        $temp = array(
            array('title' => '生产品种' , 'content' => $res['product']?$res['product']:'无' ),
            array('title' => '入库库号' , 'content' => $res['ku'],
            array('title' => '相关说明' , 'content' => $res['info']?$res['info']:'无'  ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $res['stat'],
            'applyerName'    => $res['tjr'],
        );
        return $result;
    }

    /**
     * 矿粉物料配置
     */
    public function submit(){
        $user  = session('name');

        $pdate = I('post.time');
        $select= I('post.select');
        $kh    = I('post.kh');
        $sb    = I('post.sb');
        $notice    = I('post.notice');
        $mod_name  = I('post.modname');
        $copyto_id = I('post.copyto_id');
        $sec = $this->getUserSection();
        // 流程检验
        $pro = D('KkAppflowtable')->havePro('KfMaterielApply','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        // 重复提交
        if(!M('yxhb_materiel')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
       
        $addData = array(
            'rid'           => $select['id'],
            'ku'            => $kh.'#',
            'product'       => $select['product'],
            'tjr'           => $user,
            'sb_date'       => date('Y-m-d H:i:s',time()),
            'data'          => json_encode($sb),
            'info'          => $notice,
            'stat'          => 2,
            'mod_name'      => $mod_name,
            'pruduct_date'  => $pdate,
            'section'       => $sec,
        ); 
        $result = M('yxhb_materiel')->add($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'KfMaterielApply', $result,'KfMaterielApply');
        }
        
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('KfMaterielApply', $result, $salesid, 'kk');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }
 
    /**
     * 获取近期有效的A,B线的配比通知单
     * select id,date,scx,scale,out_scale from yxhb_assay WHERE scx='B#生产线' and state=1 ORDER BY date desc LIMIT 1
     */
    public function getRatio(){
        $result = array();
        $map = array(
            'scx'   => 'A#生产线',
            'state' => 1
        );
        $res   = M('kk_zlddtz')->where($map)->order('date desc')->find();
        $result[] = $res;
        $map = array(
            'scx'   => 'B#生产线',
            'state' => 1
        );
        $res   = M('kk_zlddtz')->where($map)->order('date desc')->find();
        $result[] = $res;
        $res = array();
        foreach($result as $val){
            $name = array_merge( $this->getType($val['scale']),$this->getType($val['out_scale']));
            $no   = str_replace('生产线',' ' ,$val['scx']);
            $no   = $no.date('Ymd H:i',strtotime($val['date'])+$val['hour']*3600); 
            $tmp = array(
                'id'   => $val['id'],
                'date' => $val['date'],
                'no'   => $no,
                'product' => $val['variety'],
                'scx'  => $val['scx'],
                'name' => $name
            );
            $res[] = $tmp;
        }
        return $res;
    }
    
    public function getType($data){
        $tmp = array();
        $data = json_decode($data,true);
        foreach($data as $val){
            if( !$val['name']) continue;
            $tmp[] = $val['value'];
        }
        return $tmp;
    }
    // 判断是那个部门的人
    public function getUserSection(){
        // 110 品管
        $wxid   = session('wxid');
        $res   = M('yx_bm_access')->where(array('wx_id' => $wxid,'bm_id' => 110,'main' => 1))->find();
        return empty($res)?2:1; // 1 品管 2 生产
    }

    public function getRatioDetail(){
        $id    = I('post.ratioid'); 
        $Logic = D('KkSnRatioApply','Logic');
        $res   = $Logic->record($id);
        $html  = $this->makeDeatilHtml($res);
        return $html;
    }

        /**
     * 配比详情html生成
     * @param array   $data 配比数据
     * @return string $html 
     */
    public function makeDeatilHtml($data){
        $no   = str_replace('生产线',' ' ,$data['scx']);
        $no   = $no.date('Ymd H:i',strtotime($data['date'])+$data['hour']*3600); 
        $html = "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='{$no}'>";
        if(!empty($data['scale1']) ) {
            $proportion1 = ceil($data['proportion1']) == $data['proportion1']?ceil($data['proportion1']): $data['proportion1'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale1']}：{$proportion1}%'>";
        }
        if(!empty($data['scale2']) ) {
            $proportion2 = ceil($data['proportion2']) == $data['proportion2']?ceil($data['proportion2']): $data['proportion2'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale2']}：{$proportion2}%'>";
        }
        if(!empty($data['scale3'])) {
            $proportion3 = ceil($data['proportion3']) == $data['proportion3']?ceil($data['proportion3']): $data['proportion3'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale3']}：{$proportion3}%'>";
        }
        if(!empty($data['scale4']) ) {
            $proportion4 = ceil($data['proportion4']) == $data['proportion4']?ceil($data['proportion4']): $data['proportion4'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale4']}：{$proportion4}%'>";
        }
        if(!empty($data['scale5']) ) {
            $proportion5 = ceil($data['proportion5']) == $data['proportion5']?ceil($data['proportion5']): $data['proportion5'];
            $html .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$data['scale5']}：{$proportion5}%'>";
        }

        $out_scale = json_decode($data['out_scale']);
        $temp_html = '';
        $content   = ''; 
        foreach($out_scale as $k =>$v){
            $name      = $v->name;
            if($name){
                $content  .= "<input class='weui-input' type='text' style='color: black;'  readonly value='{$v->value}：{$v->name}%'>";
            } 
        }
        $html .= $temp_html.$content;
        return $html;
    } 
}