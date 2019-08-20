<?php
namespace Light\Logic;
use Think\Model;

/**
 * 临时额度逻辑模型
 * @author 
 */

class YxhbCostMoneyPayLogic extends Model {
    // 实际表名
    protected $trueTableName = 'yxhb_feefy';
    public function getContent(){
        $id     = I('post.id');
        $system = I('post.system');
        $res    = D(ucfirst($system).'CostMoney','Logic')->recordContent($id,'pay');
        $boss   = D($system.'Boss');
        $avatar = $boss->getAvatar($res['applyerID']);
        $res['avatar'] = $avatar;
        $data = $this->getDh($id,$system);
        $res['fkfs']  =  $this->getFkfs();
        return $res;
    }
    // 获取 cdhp 
    public function getCdhp(){
        $id     = I('post.id');
        $system = I('post.system');
        $data = M("{$system}_cdhp")->where(array('stat' => 1))->select();
        return $data;
    }
    // 判断是否已经全部付完
    public function isOver(){
        $id     = I('post.id');
        $system = I('post.system');
        $all = $this->getAllMoney($id,$system);
        $payed = $this->getPayed($id,$system);
        return $all === $payed?1:0;
    }
    // 已付记录
    public function getPayedRec($id,$system)
    {
        $flag   = $id?1:0;
        $id     = $id?$id:I('post.id');
        $system = $system?$system:I('post.system');
        $map = array(
            'a.id' => $id,
            'b.stat' => 1,
        );
        $data = M($system.'_feefy a')
                ->join("{$system}_feefy2 b on a.dh=b.dh")
                ->where($map)
                ->select();
        foreach($data as $k=>$v){
            $bank = M("{$system}_bank")->where(array('id' => $v['nbank']))->find();
            $bl         = strlen($bank['bank_account'])-4;
            $nbank      = $bl <=4 ? $bank['bank_account']:substr($bank['bank_account'],$bl);
            $temp['ac'] = $nbank;
            $temp['wz'] = $this->getbklx($bank['bank_lx_sub']);
            $temp['nb'] = $bank['bank_name'];
            $data[$k]['money'] = "&yen;".number_format(-$v['nmoney'],2,'.',',');
            $data[$k]['bank'] = $temp;
        }
        return $data;
    }
     //获取已付金额
     public function getPayed($id,$system){
        $flag   = $id?1:0;
        $id     = $id?$id:I('post.id');
        $system = $system?$system:I('post.system');
        $map = array(
            'a.id' => $id,
            'b.stat' => 1,
            'b.nfylx' => array('neq',27),
        );
        $data = M($system.'_feefy a')
                ->join("{$system}_feefy2 b on a.dh=b.dh")
                ->field('sum(b.nmoney) as nmoney')
                ->where($map)
                ->select();

        if($flag) return $data[0]['nmoney']?-$data[0]['nmoney']:0;
        return $data[0]['nmoney']?"&yen;".number_format(-$data[0]['nmoney'],2,'.',','):0;
    }
    
    // 获取总的付款数
    public function getAllMoney($id,$system){
        $id     = $id?$id:I('post.id');
        $system = $system?$system:I('post.system');
        $data = M("{$system}_feefy")->where(array('id' => $id))->find();
        return -$data['nmoney'];
    }

    // 获取单号
    public function getDh($id,$system){
        $data = M("{$system}_feefy")->where(array('id' => $id))->find();
        return $data;
    }

    // 获取银行
    public function getBank(){
        $id     = I('post.id');
        $system = I('post.system');
        $fkfs   = I('post.fkfs');
        $data = M("{$system}_bank")->where(array('bank_lx' => $fkfs))->order('id asc')->select();
        $result = array();

        foreach($data as $k=>$v){
            $bl         = strlen($v['bank_account'])-4;
            $nbank      = $bl <=4 ? $v['bank_account']:substr($v['bank_account'],$bl);
            $temp['id'] = $v['id'];
            $temp['ac'] = $nbank;
            $temp['wz'] = $this->getbklx($v['bank_lx_sub']);
            $temp['nb'] = $v['bank_name'];
            $result[]   = $temp; 
        }
        return $result; 
    }   

    public function getbklx($lx){
        if($lx==1) $bklx='基本户';
        elseif($lx==2) $bklx='一般户';
        elseif($lx==3) $bklx='临时户';
        elseif($lx==4) $bklx='自开';
        elseif($lx==5) $bklx='外开';
        return $bklx;
    }

    /**
     * 获取付款方式
     */
    public function getFkfs(){
        $system = I('post.system');
        $tmp = array();
        $fkfs = M($system.'_fkfs')->field('id as val,fk_name as name')->order('id asc')->select();
        return $fkfs;
    }
    // 提交
    public function submit()
    {
        $id     = I('post.id');
        $system = I('post.system');
        $bcfk   = I('post.bcfk');
        $cdhp   = I('post.cdhp');
        $sxfy   = I('post.sxfy');
        $bank   = I('post.bank');
        $bz     = I('post.text');
        $fkfs   = I('post.fkfs');
        $date   = I('post.date');
        if(empty($id)) return array('code' => 404 ,'msg' => '付款单号错误');
        $payed  = $this->getPayed($id,$system);
        $all    = $this->getAllMoney($id,$system);
        $nm  = $all-$payed-$bcfk;
        if($nm<0) return array('code' => 404 ,'msg' => '付款金额不能超过申请金额');
        $res = $this->getDh($id,$system);
        if(!M($system.'_feefy2')->autoCheckToken($_POST)) return array('code' => 404,'msg' => '网络延迟，请勿点击提交按钮！');
        $save = array(
            'nfkfs'   => $fkfs,
        );
        if($nm == 0){
            $save = array(
                'nfkfs'   => $fkfs,
                'stat'    => 2,
                'nbank'   => $bank,
                'sj_date' => $date,
            );
        }
        $map = array(
            'dh' => $res['dh'],
            'stat' => 4,
        );
        M("{$system}_feefy")->where($map)->save($save);
        $map = array(
            'dh'      => $res['dh'],
            'nmoney'  => -$bcfk,
            'nbank'   => $bank,
            'sj_date' => $date,
            'jl_date' => $date, 
            'npeople' => session('name'),
            'ntext'   => $bz,
            'nfkfs'   => $fkfs,
            'nfylx'   => $res['nfylx'],
            'njbr'    => $res['njbr'],
            'nbm'     => $res['nbm'],
            'stat'    => 1,
        );
        $result = M("{$system}_feefy2")->add($map);
        if($sxfy >0 ){
            $map['nfylx'] = 27;
            $map['nmoney'] = -$sxfy;
            M("{$system}_feefy2")->add($map);
        }
        if($fkfs == 3){
            M("{$system}_cdhp")->where(array('id' => $cdhp ,'stat' => 1))->save(array($stat => 2,'tdh' => $res['dh']));
        }
        return array('code' => 200,'msg' => '提交成功' , 'aid' =>$result);
    }
    // 删除记录
    public function delPay(){
        $id     = I('post.id');
        $system = I('post.system');
        if(empty($id)) return array('code'=> 404 ,'msg' => '请刷新重试');
        $data = M($system.'_feefy2')->where(array('id' => $id))->find();
        M($system.'_feefy')->where(array('dh' => $data['dh']))->save(array('nbank' => '','nfkfs' =>'','stat' => 4));
        $res = M($system.'_feefy2')->where(array('id' => $id))->save(array('stat' => '0'));
        return array('code' => 200,'data' => $res);
    }
    
}