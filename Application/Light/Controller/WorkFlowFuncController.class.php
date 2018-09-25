<?php
namespace Light\Controller;
use Think\Controller;

class WorkFlowFuncController extends Controller {

	private $WeChat = null;

  	public function __construct(){
        $this->WeChat = new \Org\Util\WeChat;
    }
	public function __call($name, $arguments)
	{
		$receivers='wk';
		$content = $name."审批后方法不存在！";
		$info=$this->WeChat->sendMessage($receivers,$content,15,$arguments[1]);
		return array("status"=>"error");
	}

	/**
	* 合同审批通过后调用函数
	* @param  [integre] $aid [合同记录父ID]
	* @return [array]      [状态]
	*/
	public function YxhbContractApplyEnd($aid)
	{
		$updateQuery="update #@__ht set ht_stat=2 where ht_stat=1 and pid=$aid";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		if ($res) {
	  		$resArr = array("status"=>"success");
		} else {
	  		$resArr = array("status"=>"failure");
		}
		return $resArr;
	}
	/**
	* 销售日计划审批通过后调用函数
	* @param  [integre] $aid [记录ID]
	* @return [array]      [状态]
	*/
	public function YxhbSalesPlantApplyEnd($aid)
	{
		$sql=new dedesql(false);
		$q="SELECT relationid FROM yxhb_sale_plan where id=$aid";
		$sql->setquery($q);
		$sql->execute();
		$g=$sql->getOne();
		$updateQuery="update #@__sale_plan set stat=1 where stat=2 and id=$aid";
		$updateQuery="update #@__sales_plan set stat=1 where stat=2 and relationid='{$g['relationid']}'";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		if ($res) {
	  		$resArr = array("status"=>"success");
		} else {
  			$resArr = array("status"=>"failure");
		}
		$jssdk = new JSSDK();
		$receiver='csl|wk|shh';
		$sj=date('m月d日',strtotime($g['dtime']));
		$rq=date('m月d日',strtotime($g['date']));
		$fbsj=date('m月d日H点',strtotime($g['dtime']));
		$title=$sj.' '."新销售日计划";
		$description =  '环保'.$rq.'销售计划<br><div class=\"highlight\">发布时间：'.$fbsj.'</div>';
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com%2fyxhb/add_sale_plan_wx.php?params='.$aid.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
		//新闻消息模式
		$info=$jssdk->sendCardMessage($receiver,$title,$description,$url,16);
		$sendStat=json_decode($info);
		if($sendStat->errcode==0){
	  		$stat2='Success!';
	  	} else {
		  	$stat2='ErrorCode'.$sendStat->errcode;
	  	}
		return $resArr;
	}
	  /**
	* 销售月计划审批通过后调用函数
	* @param  [integre] $aid [记录ID]
	* @return [array]      [状态]
	*/
	public function YxhbSalesPlanApply_monthEnd($aid)
	{
		$sql=new dedesql(false);
		$q="SELECT * FROM yxhb_sale_plan_month where id=$aid";
		$sql->setquery($q);
		$sql->execute();
		$g=$sql->getOne();
		$updateQuery="update #@__sale_plan_month set stat=1 where stat=2 and id=$aid";
		$updateQuery="update #@__sales_planmonth set stat=1 where stat=2 and relationid='{$g['relationid']}'";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		if ($res) {
			$resArr = array("status"=>"success");
		} else {
			$resArr = array("status"=>"failure");
		}
		$jssdk = new JSSDK();
		$receiver='csl|wk|shh';
		$sj=date('m月d日',strtotime($g['dtime']));
		$month=date('m月',strtotime($g['date']));
		$fbsj=date('m月d日H点',strtotime($g['dtime']));
		$title=$sj.' '."新销售月计划";
		$description = '环保'.$month.'销售计划<br><div class=\"highlight\">发布时间：'.$fbsj.'</div>';
		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx133a00915c785dec&redirect_uri=http%3a%2f%2fwww.fjyuanxin.com%2fyxhb/add_sale_plan_month_wx.php?params='.$aid.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
		//新闻消息模式
		$info=$jssdk->sendCardMessage($receiver,$title,$description,$url,16);
		return $resArr;
	}
	//发货修改
	public function Yxhbfh_edit_ApplyEnd($aid)
	{
		$xg_date=getDatetimeMk(time()+8*60*60);
		$sql=new dedesql(false);
		$q="SELECT fh_num FROM yxhb_fh where id=$aid";
		$sql->setquery($q);
		$sql->execute();
		$g=$sql->getOne();
		$q2="SELECT * FROM yxhb_fhxg where fh_num='".$g['fh_num']."'";
		$sql->setquery($q2);
		$sql->execute();
		$row=$sql->getOne();
		$sql->close();
		$addQuery="insert into yxhb_fh(fh_num,fh_client,fh_anname,fh_cate,fh_kh,fh_snbh,fh_carnum,fh_thr,fh_bs,fh_kpy,fh_show,fh_qy,fh_bzfs,fh_wlfs,fh_pp,fh_stat,fh_zl,fh_bz,xg_date,fh_da,fh_date,fh_dfzl,fh_stat2,fh_wlname,fh_bid,fh_flag,fh_pz,fh_mz,fh_jz,fh_pass,fh_passtime,fh_stat4)  values('".$row['fh_num']."','".$row['fh_client']."','".$row['fh_anname']."','".$row['fh_cate']."','".$row['fh_kh']."','".$row['fh_snbh']."','".$row['fh_carnum']."','".$row['fh_thr']."','".$row['fh_bs']."','".$row['fh_kpy']."','".$row['fh_show']."','".$row['fh_qy']."','".$row['fh_bzfs']."','".$row['fh_wlfs']."','".$row['fh_pp']."','1','".$row['fh_zl']."','".$row['fh_bz']."',NOW(),'".$row['fh_da']."','".$row['fh_date']."','".$row['fh_dfzl']."','".$row['fh_stat2']."','".$row['fh_wlname']."','".$row['fh_bid']."','".$row['fh_flag']."','".$row['fh_pz']."','".$row['fh_mz']."','".$row['fh_jz']."','".$row['fh_pass']."','".$row['fh_passtime']."','2')";
		$this->db->ExecuteNoneQuery($addQuery);
		$updateQuery="update yxhb_fh set fh_stat='0' where id='".$aid."'";
		$res = $this->db->ExecuteNoneQuery($updateQuery);
		return $resArr;
	}

	/**
	* 临时额度审批通过后调用函数
	* @param  [integre] $aid [临时额度记录ID]
	* @return [array]      [状态]
	*/
	public function YxhbTempCreditLineApplyEnd($aid)
	{
		$res = M('yxhb_tempcreditlineconfig')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
  		$resArr = $res?array("status"=>"success"):array("status"=>"failure");
		return $resArr;
	}
    /**
     * 临时额度审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
    public function KkTempCreditLineApplyEnd($aid)
    {
        $res = M('kk_tempcreditlineconfig')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
    }
    /**
     * 临时额度审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
    public function YxhbCreditLineApplyEnd($aid)
    {
        $res = M('yxhb_creditlineconfig')->where(array('stat'=>2, 'aid'=>$aid))->setField('stat', 1);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
    }
    /**
     * 临时额度审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
    public function KkCreditLineApplyEnd($aid)
    {
		$res = M('kk_creditlineconfig')->where(array('stat'=>2, 'aid'=>$aid))->setField('stat', 1);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}
	/**
     * 采购付款审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function KkCgfkApplyEnd($aid)
    {
		$res = M('kk_cgfksq')->where(array('stat'=>3, 'id'=>$aid))->setField('stat', 2);
		// 生成一条付款记录信息
		$this->makeUnpayRecord('kk',$aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}
	/**
     * 采购付款审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function YxhbCgfkApplyEnd($aid)
    {
		$res = M('yxhb_cgfksq')->where(array('stat'=>3, 'id'=>$aid))->setField('stat', 2);
		// 生成一条付款记录信息
		$this->makeUnpayRecord('yxhb',$aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
	 * 采购申请审批通过，生成一条付款记录
	 * @param string $system 系统
	 * @param        $aid    标识值
	 */
	public function makeUnpayRecord($system,$aid){
		// 生成一条付款记录信息
		$today = date('Y-m-d',time());
		$count = M($system.'_feecg')->where("date_format(jl_date, '%Y-%m-%d' )='$today' and dh like 'CY%'")->count();
		$time  = str_replace('-','',$today);
		$id    = "CY{$time}";
		if($count < 9)    $dh = $id.'00'.($count+1);
		elseif($count < 99)   $dh = $id.'0'.($count+1);
		else $dh = $id.($count+1);
		
		$data = D(ucfirst($system).'CgfkApply','Logic')->record($aid);
		$feeData = array(
			'dh'      => $dh,
			'sj_date' => $today,
			'nmoney'  => -$data['fkje'],
			'nbank'   => '',
			'jl_date' => date('Y-m-d h:i:m',time()),
			'npeople' => '系统',
			'ntext'   => $data['zy'],
			'nfkfs'   => $data['fkfs'],
			'nfylx'   => '',
			'njbr'    => $data['rdy'],
			'shy'     => '',
			'nbm'     => 1,
			'stat'    => 4,
			'sqdh'    => $data['dh'],
			'sqlx'    => 0,
			'att_name' => ''
		);
		$clientname = M($system.'_gys')->field('g_name')->where(array('id' => $data['gys']))->find();
		$dtgData = array(
			'dh'  => $dh,
			'gid' => $data['gys'],
			'ang' => $clientname['g_name']
		);
		// 事务？
		M($system.'_feecg') -> add($feeData);
		M($system.'_dtg')   -> add($dtgData);
	}

	/**
     * 配比通知矿粉签收通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function  YxhbKfRatioApplyEnd($aid)
    {
		$res = M('yxhb_assay')->where(array('state'=>2, 'id'=>$aid))->setField('state', 1);
		// 生成一条付款记录信息
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 配比通知水泥签收通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function  KkSnRatioApplyEnd($aid)
    {
		$res = M('kk_zlddtz')->where(array('STAT'=>2, 'id'=>$aid))->setField('STAT', 1);
		// 生成一条付款记录信息
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 配比通知水泥签收通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function  KkFhfRatioApplyEnd($aid)
    {
		$res = M('kk_zlddtz_gzf')->where(array('STAT'=>2, 'id'=>$aid))->setField('STAT', 1);
		// 生成一条付款记录信息
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}


	/**
     * 采购付款审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function KkWlCgfkApplyEnd($aid)
    {
		$res = M('kk_cgfksq')->where(array('stat'=>3, 'id'=>$aid))->setField('stat', 2);
		// 生成一条付款记录信息
		$this->makeWlUnpayRecord('kk',$aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 采购付款审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function YxhbWlCgfkApplyEnd($aid)
    {
		$res = M('yxhb_cgfksq')->where(array('stat'=>3, 'id'=>$aid))->setField('stat', 2);
		// 生成一条付款记录信息
		$this->makeWlUnpayRecord('yxhb',$aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

		/**
	 * 采购申请审批通过，生成一条付款记录
	 * @param string $system 系统
	 * @param        $aid    标识值
	 */
	public function makeWlUnpayRecord($system,$aid){
		// 生成一条付款记录信息
		$today = date('Y-m-d',time());
		$count = M($system.'_feecg')->where("date_format(jl_date, '%Y-%m-%d' )='$today' and dh like 'CY%'")->count();
		$time  = str_replace('-','',$today);
		$id    = "CY{$time}";
		if($count < 9)    $dh = $id.'00'.($count+1);
		elseif($count < 99)   $dh = $id.'0'.($count+1);
		else $dh = $id.($count+1);
		
		$data = D(ucfirst($system).'WlCgfkApply','Logic')->record($aid);
		$feeData = array(
			'dh'      => $dh,
			'sj_date' => $today,
			'nmoney'  => -$data['fkje'],
			'nbank'   => '',
			'jl_date' => date('Y-m-d h:i:m',time()),
			'npeople' => '系统',
			'ntext'   => $data['zy'],
			'nfkfs'   => $data['fkfs'],
			'nfylx'   => '',
			'njbr'    => $data['rdy'],
			'shy'     => '',
			'nbm'     => 1,
			'stat'    => 4,
			'sqdh'    => $data['dh'],
			'sqlx'    => 0,
			'att_name' => ''
		);
		$clientname = M($system.'_wl')->field('g_name')->where(array('id' => $data['gys']))->find();
		$gysid      = M($system.'_gys')->field('id')->where(array('g_name' => $clientname['g_name']))->find();
		$dtgData = array(
			'dh'  => $dh,
			'gid' => $gysid['id'],
			'ang' => $clientname['g_name']
		);
		// 事务？
		M($system.'_feecg') -> add($feeData);
		M($system.'_dtg')   -> add($dtgData);
	}


		/**
     * 采购付款审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function KkPjCgfkApplyEnd($aid)
    {
		$res = M('kk_cgfksq')->where(array('stat'=>3, 'id'=>$aid))->setField('stat', 2);
		// 生成一条付款记录信息
		$this->makePjUnpayRecord('kk',$aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 采购付款审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function YxhbPjCgfkApplyEnd($aid)
    {
		$res = M('yxhb_cgfksq')->where(array('stat'=>3, 'id'=>$aid))->setField('stat', 2);
		// 生成一条付款记录信息
		$this->makePjUnpayRecord('yxhb',$aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

			/**
	 * 采购申请审批通过，生成一条付款记录
	 * @param string $system 系统
	 * @param        $aid    标识值
	 */
	public function makePjUnpayRecord($system,$aid){
		// 生成一条付款记录信息
		$today = date('Y-m-d',time());
		$count = M($system.'_feecg')->where("date_format(jl_date, '%Y-%m-%d' )='$today' and dh like 'CY%'")->count();
		$time  = str_replace('-','',$today);
		$id    = "CY{$time}";
		if($count < 9)    $dh = $id.'00'.($count+1);
		elseif($count < 99)   $dh = $id.'0'.($count+1);
		else $dh = $id.($count+1);
		
		$data = D(ucfirst($system).'PjCgfkApply','Logic')->record($aid);
		$feeData = array(
			'dh'      => $dh,
			'sj_date' => $today,
			'nmoney'  => -$data['fkje'],
			'nbank'   => '',
			'jl_date' => date('Y-m-d h:i:m',time()),
			'npeople' => '系统',
			'ntext'   => $data['zy'],
			'nfkfs'   => $data['fkfs'],
			'nfylx'   => '',
			'njbr'    => $data['rdy'],
			'shy'     => '',
			'nbm'     => 1,
			'stat'    => 4,
			'sqdh'    => $data['dh'],
			'sqlx'    => 0,
			'att_name' => ''
		);
		
		$dtgData = array(
			'dh'  => $dh,
			'gid' => $data['gys'],
			'ang' => $data['pjs']
		);
		// 事务？
		M($system.'_feecg') -> add($feeData);
		M($system.'_dtg')   -> add($dtgData);
	}

	/**
     * 环保量库库存审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function YxhbLkStockApplyEnd($aid)
    {
		$res = M('yxhb_produce_stock')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
		// 同步更新 177 数据库
		D('OtherMysql')->LkStockUpdate($aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 建材销售收款审批通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function KkSalesReceiptsApplyEnd($aid)
    {
		$res = M('kk_feexs')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
		// ->承兑汇票
		$data = D('KkSalesReceiptsApply','Logic')->record($aid);
		if($data['nfkfs'] == 3){
			M('kk_cdhp')->where(array('stat'=>3, 'odh'=>$data['dh']))->setField('stat', 1);
		}
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}
	/**
     * 环保销售收款审批通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function YxhbSalesReceiptsApplyEnd($aid)
    {
		$res = M('yxhb_feexs')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
		// ->承兑汇票
		$data = D('YxhbSalesReceiptsApply','Logic')->record($aid);
		if($data['nfkfs'] == 3){
			M('yxhb_cdhp')->where(array('stat'=>3, 'odh'=>$data['dh']))->setField('stat', 1);
		}
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 环保发货修改通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function Kkfh_edit_Apply_hbEnd($aid)
    {
		$res = D('Kkfh_edit_Apply_hb','Logic')->theEnd($aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}
		/**
     * 建材发货修改通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function Kkfh_edit_ApplyEnd($aid)
    {
		$res = D('Kkfh_edit_Apply','Logic')->theEnd($aid);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 矿粉物料配置审批通过后调用函数
     * @param  [integre] $aid [临时额度记录ID]
     * @return [array]      [状态]
     */
	public function YxhbKfMaterielApplyEnd($aid)
    {
		$res = M('yxhb_materiel')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}
	/**
     * 建材其他收入通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function KkAddMoneyQtEnd($aid)
    {
		$res = M('kk_feeqt')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
		// ->承兑汇票
		$data = D('KkAddMoneyQt','Logic')->record($aid);
		if($data['nfkfs'] == 3){
			M('kk_cdhp')->where(array('stat'=>3, 'odh'=>$data['dh']))->setField('stat', 1);
		}
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 环保其他收入通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function YxhbAddMoneyQtEnd($aid)
    {
		$res = M('yxhb_feeqt')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
		// ->承兑汇票
		$data = D('YxhbAddMoneyQt','Logic')->record($aid);
		if($data['nfkfs'] == 3){
			M('yxhb_cdhp')->where(array('stat'=>3, 'odh'=>$data['dh']))->setField('stat', 1);
		}
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}

	/**
     * 环保其他收入通过后调用函数
     * @param  [integre] $aid [记录ID]
     * @return [array]      [状态]
     */
	public function KkAddMoneyQtTzEnd($aid)
    {
		$res = M('yxtz_feeqt')->where(array('stat'=>2, 'id'=>$aid))->setField('stat', 1);
		// ->承兑汇票
		$data = D('KkAddMoneyQtTz','Logic')->record($aid);
		if($data['nfkfs'] == 3){
			M('yxtz_cdhp')->where(array('stat'=>3, 'odh'=>$data['dh']))->setField('stat', 1);
		}
        $resArr = $res?array("status"=>"success"):array("status"=>"failure");
        return $resArr;
	}
	
// -----END------
}