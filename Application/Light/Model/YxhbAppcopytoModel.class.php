<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保抄送记录模型
 * @author 
 */

class YxhbAppcopytoModel extends Model {

    /**
     * 获取抄送记录
     * @param  string $modname 流程名
     * @param  int    $aid 记录ID
     * @param  arry    $authArr 权限数组
     * @return array   摘要数组
     */
    public function contentCopyto($mod_name, $aid, $authArr,$type='')
    {
        if($type=='')$type=1;
        // 抄送名单
        $already_cp = array();
        $readedArr = array();
        $fixedArr = array();
        $isCopyto = 0;
        $wxid = session('wxid');
        $boss = D('yxhb_boss');
        $cp = $this->field('fixed_copyto_id,copyto_id,from_id,readed_id')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'stat'=>1,'type'=>$type))->select();
        
        foreach ($cp as $v) {
          $idArr = explode(',', $v['copyto_id']);
          if ($v['fixed_copyto_id']) {
              $fixedArr = explode(",", $v['fixed_copyto_id']);    //固定抄送人
          }
          $readedArr =explode(",", $v['readed_id']);          //已读抄送ID
          foreach ($idArr as $cid) {
            if($cid){
              $cpid = $boss->getIDFromWX($cid);
              $cpname = $boss->getusername($cpid);
              $url = $boss->getAvatar($cpid);
              $already_cp[] = array('id'=>$cid, 'url'=>$url, 'name'=>$cpname);
              array_push($authArr, $cid);
              if ($wxid==$cid) {
                $isCopyto = 1;
              }
            }
          }
        }
        unset($v);
        $copyArr = array('readedArr'=>$readedArr,'fixed_id'=>$fixedArr,'already_cp'=>$already_cp,'authArr'=>$authArr,'isCopyto'=>$isCopyto);
        return $copyArr;
    }

    /**
     * 抄送审批记录已读
     * @param  string $mod_name 审批名
     * @param  int $aid      审批记录ID
     * @param  int $pid      YXHB用户ID
     * @return int           修改影响记录数
     */
    public function readCopytoApply($mod_name, $aid, $pid='',$type='')
    {
        if (empty($pid)) {
            $pid = session('yxhb_id');
        }
        $res = 0;
        $wxid = D('yxhb_boss')->getWXFromID($pid);
        $mergeReader = array();
        $copytoRes = $this->field('readed_id')->where("mod_name='{$mod_name}' and aid='{$aid}' and stat='1' and find_in_set('{$wxid}', copyto_id) and type='{$type}'")->select();
        foreach ($copytoRes as $key => $value) {
            $readedArr = explode(',', $value['readed_id']);
            $mergeReader = array_merge($mergeReader, $readedArr);
        }
        unset($value);
        if (!in_array($wxid, $mergeReader)) {
            $mergeReader = array_unique($mergeReader);
            $readList = implode(',', $mergeReader).','.$wxid;
            $readList = trim($readList, ',');
            $res = $this->where("mod_name='{$mod_name}' and aid='{$aid}' and stat='1' and find_in_set('{$wxid}', copyto_id) and type='{$type}'")->setField('readed_id', $readList);
        }
        return $res;
    }


    public function copyTo($cpid, $mod_name, $aid,$type='')
    {
        if($type=='')$type=1;
        $recevier = str_replace(',', '|', $cpid);
        $flowTable = M('yxhb_appflowtable');
        $mod_cname = $flowTable->getFieldByProMod($mod_name, 'pro_name');
        $title = '环保ERP'.str_replace('表','',$mod_cname);
        $copy_man = session('name');
        $WeChat = new \Org\Util\WeChat;
        $url = "http://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=yxhb&aid=".$aid."&modname=".$mod_name;    
        if($type == 1 ){
            $str = '抄送了';
            $description = $copy_man.$str.$mod_cname."给你!";
            $WeChat->sendCardMessage($recevier,$title,$description,$url,15,$mod_name,'yxhb');
        }else{
            $logic = D('Yxhb'.$mod_name,'Logic');
            // 提交人同为推送人
            $applyerArr  = $logic->recordContent($aid);
            $applyerID   = D('YxhbBoss')->getWXFromID($applyerArr['applyerID']); // -- 申请人id
            // -- 去除是提交人的推送的人
            $recevierArr = explode('|',$recevier);
            $recevierArr = array_merge(array_diff($recevierArr, array($applyerID)));
            $recevier     = implode('|',$recevierArr);
            $cpid = implode(',',$recevierArr);
            $title = str_replace('表','',$mod_cname);

            $qsArr = array('KfRatioApply');
            $template =  in_array($mod_name,$qsArr)?"【签收后推送信息】\n申请单位：环保\n申请类型：{$title}":"【审批后推送信息】\n申请单位：环保\n申请类型：{$title}";
              
            $descriptionData = $logic->getDescription($aid);
            $description = $this->ReDescription($descriptionData);
            $template =$template."\n".$description."<a href='".$url."'>点击查看审批详情</a>";
            $WeChat->sendMessage("wk|HuangShiQi|".$recevier,$template,15,'yxhb');
        } // 保存抄送消息
        $cpdata['aid'] = $aid;
        if($type==2){
            $cpdata['fixed_copyto_id'] = $cpid;
        }else{
            $model = D('Msgdata');
            $message = $model->GetMessage($mod_name);
            $cpdata['fixed_copyto_id'] = $message['yxhb'.$mod_name]['fiexd_copy_id'];
        }
        $cpdata['copyto_id'] = $cpid;
        $cpdata['from_id'] = session('wxid');
        $cpdata['time'] = date('Y-m-d H:i:s');
        $cpdata['mod_name'] = $mod_name;
        $cpdata['type'] = $type;
        $insertID = $this->add($cpdata);
        return $insertID;
    }
     /**
     *  根据返回值，重组字符串
     * @param  array $data 重组数组
     * @return string       description
     */
    public function ReDescription($data){
        $description = '';
        foreach($data as $k =>$v){
          $description.=$v['name'].$v['value']."\n";
        }
        return $description;
    }
}
