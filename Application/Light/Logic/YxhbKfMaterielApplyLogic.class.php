<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbKfMaterielApplyLogic extends Model {
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
            'type' => 1
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
                                     'value'=>'环保矿粉物料配置',
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
        // $ratio = M('yxhb_assay')->where('id='.$res['rid'])->find();
        // $no   = str_replace('生产线',' ' ,$ratio['scx']);
        // $no   = $no.date('Ymd H:i',strtotime($ratio['date'])+$ratio['hour']*3600); 
        $result['content'][] = array('name'=>'生产线路：',
                                     'value'=> $res['scx'] ,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        // $result['content'][] = array('name'=>'配比详情：',
        //                              'value'=> "<span  onclick=ratioDetail({$res['rid']}) id='ratio_btn_look' style='color: #337ab7;cursor: pointer;'>点击查看详情</span>" ,
        //                              'type'=>'string',
        //                              'color' => 'black'
        //                             );
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
        $result['content'][] = array('name'=>'申请理由：',
                                     'value'=>$res['info']?$res['info']:'无',
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
        $result[] = array('name'=>'生产时间：',
                                     'value'=>date('m-d H:i',strtotime($res['pruduct_date'])),
                                     'type'=>'date'
                                    );
    
        // $ratio = M('yxhb_assay')->where('id='.$res['rid'])->find();
        // $no   = str_replace('生产线',' ' ,$ratio['scx']);
        //$no   = $no.date('Ymd H:i',strtotime($ratio['date'])+$ratio['hour']*3600); 
        $result[] = array('name'=>'生产线路：',
                                     'value'=>$res['scx']  ,
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
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['info']?$res['info']:'无',
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
        $result = array(
            'sales'   => $res['tjr'],
            'title2'  => '入库库号',
            'approve' => $res['ku'],
            'notice'  => $res['info'],
            'date'    => $res['sb_date'],
            'title'   => '生产品种',
            'name'    => $res['product'], 
            'modname' => 'KfMaterielApply',
            'stat'    => $res['stat'],
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
        $scx   = I('post.scx');
        $prod  = I('post.prod');
        $kh    = I('post.kh');
        $sb    = I('post.sb');
        $notice    = I('post.notice');
        $mod_name  = I('post.modname');
        $copyto_id = I('post.copyto_id');
        $sec = $this->getUserSection();
     
        // 重复提交
        if(!M('yxhb_materiel')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
       
        $addData = array(
            'rid'           => 0,
            'ku'            => $kh.'#',
            'product'       => $prod,
            'tjr'           => $user,
            'sb_date'       => date('Y-m-d H:i:s',time()),
            'data'          => json_encode($sb),
            'info'          => $notice,
            'stat'          => 2,
            'mod_name'      => $mod_name,
            'pruduct_date'  => date('Y-m-d H:i:s',strtotime($pdate)),
            'section'       => $sec,
            'scx'           => $scx
        ); 
        // return array('code' => 404,'msg' =>'提交失败，请重新尝试！','data' => $addData);
        $result = M('yxhb_materiel')->add($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('YxhbAppcopyto')->copyTo($copyto_id,'KfMaterielApply', $result,'KfMaterielApply');
        }
        
        $wf = A('WorkFlow');
        $salesid = session('yxhb_id');
        $res = $wf->setWorkFlowSV('KfMaterielApply', $result, $salesid, 'yxhb');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);

    }
 
    /**
     * 获取近期有效的A,B线的配比通知单
     * select id,date,scx,scale,out_scale from yxhb_assay WHERE scx='B#生产线' and state=1 ORDER BY date desc LIMIT 1
     */
    public function getRatio(){
        // $result = array();
        // $map = array(
        //     'scx'   => 'A#生产线',
        //     'state' => 1
        // );
        // $res   = M('yxhb_assay')->where($map)->order('cretime desc')->find();
        // $result[] = $res;
        // $map = array(
        //     'scx'   => 'B#生产线',
        //     'state' => 1
        // );
        //$res   = M('yxhb_assay')->where($map)->order('cretime desc')->find();
        //$result[] = $res;
        $result = M('yxhb_assay')->where(array('state' => 1 , 'date' => array('egt','2018-08-01' )))->order('cretime desc')->select();
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

    public function getRatioDetail(){
        $id    = I('post.ratioid'); 
        $Logic = D('YxhbKfRatioApply','Logic');
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
        return 0;
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