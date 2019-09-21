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
            $idArr = array_unique($idArr);
            foreach($idArr as $key => $val){
               $temp[$key]['wxid'] = $val;
               $temp[$key]['sortwxid'] = strtolower($val); 
            }
           
           $top  = array('ChenBiSong','csh','csl');
           $array = array('','','');
           $temp  = list_sort_by($temp,'sortwxid','asc');
           foreach($temp as $value){
               if($value['wxid'] == $top[0]){ $array[0] = $value['wxid'];continue;}
               if($value['wxid'] == $top[1]){ $array[1] = $value['wxid'];continue;}
               if($value['wxid'] == $top[2]){ $array[2] = $value['wxid'];continue;}
               $array[] = $value['wxid'];
           }
           $idArr = array_filter($array);
          if ($v['fixed_copyto_id']) {
              $fixedArr = explode(",", $v['fixed_copyto_id']);    //固定抄送人
              $fixedArr = array_unique($fixedArr);
          }
          $readedArr =explode(",", $v['readed_id']);          //已读抄送ID
          $readedArr = array_unique($readedArr);
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
            $readedArr = array_unique($readedArr);
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


    public function copyTo($cpid, $mod_name, $aid,$type='',$ture_mod='')
    {
        if($type=='')$type=1;
        $url_mod = $ture_mod == ''?$mod_name:$ture_mod;
        $recevier = str_replace(',', '|', $cpid);
        $flowTable = M('yxhb_appflowtable');
        $seek = D('Seek');
        $mod_cname = $seek->getTitle($mod_name,'yxhb');
        $title = '环保'.$mod_cname;
        $copy_man = session('name');
        $WeChat = new \Org\Util\WeChat;
        $url = "https://www.fjyuanxin.com/WE/index.php?m=Light&c=Apply&a=applyInfo&system=yxhb&aid=".$aid."&modname=".$url_mod;    
        if($type == 1 ){
            $str = '抄送了';
            $description = $copy_man.$str.$mod_cname."给你!";
            $WeChat->sendCardMessage($recevier,$title,$description,$url,15,$mod_name,'yxhb');
        }else{
            $logic = D('Yxhb'.$mod_name,'Logic');
            $applyerArr  = $logic->recordContent($aid);
            // 提交人同为推送人
            $applyerID   = D('YxhbBoss')->getWXFromID($applyerArr['applyerID']); // -- 申请人id
            // -- 去除是提交人的推送的人
            $recevierArr = explode('|',$recevier);
            $recevierArr = array_unique($recevierArr);
            $recevierArr = array_merge(array_diff($recevierArr, array($applyerID)));
            $recevier     = implode('|',$recevierArr);
            $cpid = implode(',',$recevierArr);
            $qsRes =  M('yx_config_title')->field('name')->where(array('stat' => '3'))->select();
            $qsArr = array();
            foreach($qsRes as $val){
                $qsArr[] = $val['name'];
            }
            $title    = str_replace('环保','',$title);
            $title    = str_replace('建材','',$title);
            $template =  in_array($mod_name,$qsArr)?"【{$title}】推送\n申请单位：环保":"【{$title}】推送\n申请单位：环保";
              
            $descriptionData = $logic->getDescription($aid);
            $description = $this->ReDescription($descriptionData);
            $click           = in_array($mod_name,$qsArr)?'签收':'审批';
            $template        = $template."\n".$description."<a href='".$url."'>点击查看{$click}详情</a>";
           
            $agentid = M('yx_push_agentid')
                    ->field('agentid')
                    ->where(array('mod' => $mod_name))
                    ->find();
            $agentid = $agentid['agentid']?$agentid['agentid']:15;
            // $modArr = array('TempCreditLineApply_fmh');
            // if(in_array($mod_name,$modArr)){
                $wx = D('WxMessage');
                $wx -> PushSendCarMessage('yxhb',$mod_name,$aid,$agentid,"wk|HuangShiQi|".$recevier);
            // }else{
            //     $WeChat->sendMessage("wk|HuangShiQi|".$recevier,$template,$agentid,'yxhb');
            // }
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
     * 获取推送人员
     */
    public function getPushId($system,$mod_name,$id){
        // 用户推送
        $map = array(
            'pro_mod' => $mod_name,
            'stat'    => 1 
        );
        $push_id = '';
        $res = M($system.'_pushlist')->where($map)->select();
        if(!empty($res)){
            if(count($res) == 1){
                $push_id = trim($res[0]['push_name'],'"');
            }else{
                $push_id = '';
                foreach($res as $val){
                    $cons=substr($val['rule'],1,-1);
                    $cons_sub=explode("|",$cons);
                    //数据组装
                    foreach($cons_sub as $c_s){
                        $cons_sub_sub=explode(":",$c_s);
                        $cons_array[$cons_sub_sub[0]]=$cons_sub_sub[1];
                    }
                    $con_query="SELECT 1 from ".$cons_array['table']." WHERE ".$cons_array['id']."=$id ".$cons_array['conditions'];
                    $push_list = M()->query($con_query);
                    $count=count($push_list);
                    if($count == 1) {
                        $push_id = trim($val['push_name'],'"');
                        break;
                    }
                }
            }
            $del_arr = M($system.'_appflowproc a')->join($system.'_boss b on b.id=a.per_id')->field('b.wxid')->where(array('a.aid' => $id,'a.mod_name' => $mod_name))->order('a.app_stage desc,approve_time DESC')->find();
            $del_id = $del_arr['wxid'];
            $res = array_search($del_id,$push_id);
            if($res !== false){
                $push_id = str_replace($del_id,'',$push_id);
                $push_id = explode(',',$push_id);
                $push_id = implode(',',array_filter($push_id));
            }
        }
        return $push_id;
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

    /**
     * 固定抄送
     * @param  array  $copyid 重组数组
     * @return string $html
     */
    public function getFiexdCopyHtml($copyid){
        $data = explode(',',$copyid);
        $data = array_unique($data);
        $data = array_filter($data);
        $res  = array();
        $boss = D('YxhbBoss');
        if(empty($data)) return $res;
        $res['fiexd_copy_id'] = $copyid;
        foreach($data as $val){
            $res['copydata'][] = array(
                'wxid'   => $val,
                'name'   => $boss->getNameFromWX($val),
                'avatar' => $boss->getAvatarFromWX($val),
            );
        }
        $res['html'] = D('Html')->fiexdCopyHtml($res['copydata']);
        return $res;
    }
}
