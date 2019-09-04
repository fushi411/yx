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

    public function record($id)
    {
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->find();
    }

    public function recordContent($id)
    {
        $res = $this->record($id);
        $result = array();
        $clientname = M('kk_guest2')->field('g_name,reid')->where(array('id' => $res['ht_khmc']))->find();
        $topGys     = M('kk_guest2')->field('g_name')->where(array('id' => $clientname['reid']))->find();
        $kpjs       = $this->getModel($clientname['reid']);
        $result['content'][] = array('name'=>'系统类型：',
                                     'value'=>'建材新增合同价格',
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'提交时间：',
                                     'value'=> date('m-d H:i',strtotime($res['ht_date'])) ,
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'申请日期：',
                                     'value'=> date('Y-m-d',strtotime($res['ht_date'])),
                                     'type'=>'date',
                                     'color' => 'black'
                                    );
        $topGys = str_replace('-总','',$topGys ['g_name']).'-总';
        $result['content'][] = array('name'=>'上级客户：',
                                     'value'=>$topGys,
                                     'type'=>'string',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'开票方式：',
                                     'value'=>$kpjs['kf'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'结算方式：',
                                     'value'=>$kpjs['js'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'二级客户：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'number',
                                     'color' => 'black'
                                    );
        $result['content'][] = array('name'=>'原有价格：',
                                     'value'=>$this->getYyHtml($res),
                                     'type'=>'number',
                                     'color' => 'black'
                                    );

        $result['content'][] = array('name'=>'价格详情：',
                                     'value'=>$this->getDetailHtml($id),
                                     'type'=>'number',
                                     'color' => 'black'
                                    );                      

        $result['content'][] = array('name'=>'相关说明：',
                                     'value'=>$res['ht_bz']?$res['ht_bz']:'无',
                                     'type'=>'text',
                                     'color' => 'black'
                                    );
        $result['imgsrc'] = '';
        $result['applyerID']   = D('KkBoss')->getIDFromName($res['ht_rdy']);
        $result['applyerName'] = $res['ht_rdy'];
        $result['stat'] = $this->transStat($res['ht_stat']);
        return $result;
    }

     /**
     * 记录内容
     * @param  integer $id 记录ID
     * @return array       记录数组
     */
    public function getDescription($id){
        $res = $this->record($id);
        $result = array();
        $clientname = M('kk_guest2')->field('g_name,reid')->where(array('id' => $res['ht_khmc']))->find();
        $topGys     = M('kk_guest2')->field('g_name')->where(array('id' => $clientname['reid']))->find();
        $kpjs       = $this->getModel($clientname['reid']);
        $result[] = array('name'=>'提交时间：',
                                     'value'=>date('m-d H:i',strtotime($res['ht_date'])) ,
                                     'type'=>'date'
                                    );
        $result[] = array('name'=>'申请日期：',
                                     'value'=>date('Y-m-d',strtotime($res['ht_date'])),
                                     'type'=>'date'
                                    );
        $topGys = str_replace('-总','',$topGys ['g_name']).'-总';
        $result[] = array('name'=>'上级客户：',
                                     'value'=>$topGys,
                                     'type' =>'string'
                                    );
      
    
        $result[] = array('name'=>'开票方式：',
                                     'value'=>$kpjs['kf'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'结算方式：',
                                     'value'=>$kpjs['js'],
                                     'type'=>'number'
                                    );
        $result[] = array('name'=>'二级客户：',
                                     'value'=>$clientname['g_name'],
                                     'type'=>'number'
                                    );                            
        $result[] = array('name'=>'申请人员：',
                                     'value'=>$res['ht_rdy'],
                                     'type'=>'string'
                                    );
        $result[] = array('name'=>'申请理由：',
                                     'value'=>$res['ht_bz']?$res['ht_bz']:'无',
                                     'type'=>'text'
                                    );
        return $result;
    }
    public function getYyHtml($data){
        $field = 'id,ht_bzfs,ht_cate,ht_wlfs,ht_yf,ht_dj,ht_khmc,ht_stday,ht_enday';
        $map   = array(
            'ht_stat'  => 2,
            'ht_enday' => array('gt',date('Y-m-d',strtotime($data['ht_date']))),
            'ht_khmc'  => $data['ht_khmc'],
            'pid'      => array('neq',$data['pid']),
        );
        $info = M('kk_ht')->field($field)->where($map)->order('ht_cate,ht_enday desc')->select();
        $info = $this->infoSort($info);
        foreach($info as $key => $val){
            $map  = array(
                'tj_stat'   => 2,
                'tj_client' => $val['ht_khmc'],
                'tj_cate'   => $val['ht_cate'],
                'tj_bzfs'   => $val['ht_bzfs'],
                'tj_wlfs'   => $val['ht_wlfs'],
            ); 
            $res = M('kk_tj')->where($map)->order('tj_da desc')->find();
            if(!empty($res))$info[$key]['ht_dj'] = $res['tj_dj'];
        }
        if(empty($info)) return '无';
        $html = '';
        foreach($info as $k => $v){
            $html   .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='{$v['ht_bzfs']}{$v['cate']}({$v['ht_wlfs']})'>";
            $tempStr = $v['ht_yf'] == 0? '':'('.$v['ht_yf'].')';
            $html   .= "<input class='weui-input' type='text' style='color: black;'  readonly value='单价(运费)：{$v['ht_dj']}{$tempStr}'>";
            $total   = $v['ht_yf']+$v['ht_dj'];
            $html   .= "<input class='weui-input' type='text' style='color: black;'  readonly value='合计：{$total}'>";  
        }
        return $html;
    }
    
    public function getDetailHtml($id){
        $field = 'id,ht_bzfs,ht_cate,ht_wlfs,ht_yf,ht_dj';
        $map   = array(
            'pid'  => $id
        );
        $info = M('kk_ht')->field($field)->where($map)->select();
        $info = $this->infoSort($info);
        if(empty($info)) return '无';
        $html = '';
        foreach($info as $k => $v){
            $html   .= "<input class='weui-input' type='text' style='color: black; font-weight: 700;border-bottom: 1px solid #e5e5e5; '  readonly value='{$v['ht_bzfs']}{$v['cate']}({$v['ht_wlfs']})'>";
            $tempStr = $v['ht_yf'] == 0? '':'('.$v['ht_yf'].')';
            $html   .= "<input class='weui-input' type='text' style='color: black;'  readonly value='单价(运费)：{$v['ht_dj']}{$tempStr}'>";
            $total   = $v['ht_yf']+$v['ht_dj'];
            $html   .= "<input class='weui-input' type='text' style='color: black;'  readonly value='合计：{$total}'>";
        }
        return $html;
    }
        
    /**
     * 我的审批，抄送，提交 所需信息
     * @param  integer $id 记录ID
     * @return array    所需内容      
     */
    public function sealNeedContent($id){
        $res = $this->record($id);
        $clientname = M('kk_guest2')->field('g_name,reid')->where(array('id' => $res['ht_khmc']))->find();
        $topGys     = M('kk_guest2')->field('g_name')->where(array('id' => $clientname['reid']))->find();
        $temp = array(
            array('title' => '上级客户','content' => $topGys['g_name']?$topGys['g_name']:'无' ),
            array('title' => '二级客户','content' => $clientname['g_name']?$clientname['g_name']:'无' ),
            array('title' => '相关说明','content' => $res['ht_bz']?$res['ht_bz']:'无' ),
        );
        $result = array(
            'content'        => $temp,
            'stat'           => $this->transStat($res['ht_stat']),
            'applyerName'    => $res['ht_rdy'],
        );
        return $result;
    }
    
    public function getTableName()
    {
        return $this->trueTableName;
    }

    /**
     * 删除记录
     * @param  integer $id 记录ID
     * @return integer     影响行数
     */
    public function delRecord($id)
    {
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->setField('ht_stat',0);
    }

    /**
     * 获取申请人名/申请人ID（待定）
     * @param  integer $id 记录ID
     * @return string      申请人名
     */
    public function getApplyer($id)
    {
        $map = array('pid' => $id);
        return $this->field(true)->where($map)->getField('ht_rdy');
    }
    public function transStat($stat){
        $statArr = array(
            0 => 0,
            1 => 2,
            2 => 1,
            3 => 2,
        );
        return $statArr[$stat];
    }


    /**
     * 有效+冻结 所有客户
     * @param string $data  拼音缩写
     * @return array $res   备案客户结果
     */
    public function getCustomerList(){
        $keyWord = I('math');
        $res = D('Guest')->getGuest('kk',$keyWord,'ContractApply');
        return $res;
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
            //'g_stat3'=>1
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
    public function getGuestInfo(){
        $id    = I('post.user_id');
        $today = date('Y-m-d',time());
        $field = 'id,ht_bzfs,ht_cate,ht_wlfs,ht_yf,ht_dj,ht_khmc';
        $map   = array(
            'ht_stat'  => 2,
            'ht_enday' => array('egt',$today),
            'ht_khmc'  => $id
        );
        $info = M('kk_ht')
                    ->field($field)
                    ->where($map)
                    ->order('ht_cate,ht_enday desc')
                    ->select();
        $info = $this->infoSort($info);
       
        foreach($info as $key =>$val){
            $map  = array(
                'tj_stat'   => 2,
                'tj_client' => $val['ht_khmc'],
                'tj_cate'   => $val['ht_cate'],
                'tj_bzfs'   => $val['ht_bzfs'],
                'tj_wlfs'   => $val['ht_wlfs'],
            ); 
            $res = M('kk_tj')->where($map)->order('tj_da desc')->find();
            $info[$key]['ht_dj'] = $res['tj_dj']?$res['tj_dj']:$info[$key]['ht_dj'];
        }
        return $info; 
    }
    public function infoSort($data){
        $temp  = array();
        $res   = array();
        $touch = array(); 
        foreach($data as $k=>$v){
            $key          = $this->findNum($v['ht_cate']);
            $inStr        = $v['ht_cate'].$v['ht_wlfs'].$v['ht_bzfs'];
            if(in_array($inStr,$touch))continue;
            $touch[]      = $inStr;
            $v['cate']    = $key;
            $temp[$key][] = $k;
        }
        krsort($temp);// 重新排序
        foreach($temp as $tk=>$tv){
            foreach($tv as $tvv){
                $data[$tvv]['total'] = $data[$tvv]['ht_dj']+$data[$tvv]['ht_yf'];
                $data[$tvv]['cate']  = $tk;
                $res[]               = $data[$tvv];
            }
        }
        return $res;
    }
    
    public function findNum($str=''){
        $str=trim($str);
        if(empty($str)){return '';}
        $temp=array('1','2','3','4','5','6','7','8','9','0');
        $result='';
        for($i=0;$i<strlen($str);$i++){
            if(in_array($str[$i],$temp)){
                $result.=$str[$i];
            }
        }
        return $result;
    }
    // 获取材料 
    public function getStuff(){
        // select * from kk_config_cate_sn WHERE stat=2 ORDER BY id desc LIMIT 1
        $data   = M('kk_config_cate_sn')->where('stat=2')->order('id desc')->find();
        $forArr = array('yhk','ehk','shk','sshk','whk','lhk','qhk','bhk','jhk');
        $res    = array();
        foreach($forArr as $v){
            if(in_array($data[$v],$res) || $data[$v] == '无') continue;
            $res[] = $data[$v];
        }
        return $res;
    }
    // 提交
    public function submit(){
        $uid       = I('post.user_id');
        $data      = I('post.data');
        $copyto_id = I('post.copyto_id');
        $bz        = I('post.notice');
        // 更新 总客户
        $id        = I('post.id');
        $jsfs      = I('post.jsfs');
        $kpfs      = I('post.kpfs');

        if(!$uid) return  array('code' => 404,'msg' =>'请选择二级客户');
        if( !count($data) ) return  array('code' => 404,'msg' =>'请配置价格');

        $idArr                = $this->getPid();
        $first_day            = date('Y-m-01',time());
        $next_month_last_day  = date("Y-m-d",strtotime("$first_day +2 month -1 day"));
        // 流程检验
        $pro = D('KkAppflowtable')->havePro('ContractApply','');
        if(!$pro) return array('code' => 404,'msg' => '无审批流程,请联系管理员');
        if(!M('kk_ht')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $temp = array(
            'pid'         => $idArr[0],
            'ht_xyed'     => 0,
            'ht_date'     => date('Y-m-d H:i:s',time()),
            'ht_stday'    => date('Y-m-d',time()),
            'ht_enday'    => $next_month_last_day ,
            'ht_dh'       => $idArr[1],
            'ht_khmc'     => $uid,
            'ht_rdy'      => session('name'),
            'ht_stat'     => 3,
            'ht_pp'       => '福源鑫',
            'ht_qday'     => date('Y-m-d',time()),
            'ht_htlx'     => '临时合同',
            'ht_bz'       => $bz
        );
        foreach($data as $k => $v){
            $temp['ht_cate'] = $v['ht_cate'];
            $temp['ht_dj']   = $v['ht_dj'];
            $temp['ht_wlfs'] = $v['ht_wlfs'];
            $temp['ht_yf']   = $v['ht_yf'];
            $temp['ht_bzfs'] = $v['ht_bzfs'];
            $addData[] = $temp;
        }
        $result = M('kk_ht')->addAll($addData);
        if(!$result) return array('code' => 404,'msg' =>'提交失败，请重新尝试！');
        // 更新客户表
        $guest = array();
        if($jsfs) $guest['g_jsfs'] = $jsfs;
        if($kpfs) $guest['g_kpfs'] = $kpfs;
        if(!empty($guest)){
            $map = array('id' => $id);
            M('kk_guest2')->where($map)->save($guest);
        }
        // 抄送
        $copyto_id = trim($copyto_id,',');
        if (!empty($copyto_id)) {
            // 发送抄送消息
            D('KkAppcopyto')->copyTo($copyto_id,'ContractApply', $idArr[0]);
        }
        $wf = A('WorkFlow');
        $salesid = session('kk_id');
        $res = $wf->setWorkFlowSV('ContractApply', $idArr[0], $salesid, 'kk');

        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$idArr[0]);
    }
    // 获取pid
    public function getPid(){
        $year  = date('Y',time());
        $count = M('kk_ht')->where('year(ht_stday)='.$year)->count();
        $id    = date('Ymd',time());
        $dh    = "XSHT".date("Y",time());
        if($count < 9)  return  array( $id.'00'.($count+1) ,  $dh.'00'.($count+1) );
        if($count < 99) return  array( $id.'0'.($count+1)  ,  $dh.'0'.($count+1) );
        return array( $id.($count+1) ,  $dh.($count+1) );
    }
}