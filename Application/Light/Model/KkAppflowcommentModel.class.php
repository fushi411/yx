<?php
namespace Light\Model;
use Think\Model;
/**
 * 环保审批评论记录模型
 * @author 
 */

class KkAppflowcommentModel extends Model {

    /**
     * 获取审批评论记录
     * @param  string $modname 流程名
     * @param  int    $aid 记录ID
     * @return array   摘要数组
     */
    public function contentComment($mod_name, $aid)
    {
        // 评论名单
        $comment_list = array();
        $res =  $this->field('id,app_word,time,per_name,per_id,comment_to_id')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>1,'per_id' =>9999))->order('time desc')->select();
        if(!empty($res)){
            $count = count($res);
            $tmp = explode('发起了',$res[0]['app_word']);
            $str = $tmp[0]."发起了第{$count}次".$tmp[1];
            $res = array(array(
                'id' => 0,
                'app_word' => $str,
                "time" => $res[0]['time'],
                "per_name" =>  "系统定时任务",
                "per_id" =>  "9999",
                "comment_to_id" => $res[0]['comment_to_id']
            ));
        }
        $cl = $this->field('id,app_word,time,per_name,per_id,comment_to_id')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>1,'per_id' =>array('neq',9999)))->order('time desc')->select();
        $cl = array_merge($res,$cl);
        $boss = D('kk_boss');
        foreach ($cl as $v) {
              $cwxUID = $boss->getWXFromID($v['per_id']);
              $avatar = $boss->getAvatar($v['per_id']);
              
              if (!empty($v['comment_to_id'])) {
                  $commentUserArr = explode(',', $v['comment_to_id']);
                  $commentUserArr = array_map(function($wxid) use ($boss) {
                      $cid = $boss->getIDFromWX($wxid);
                      $crealname = $boss->getusername($cid);
                      return $crealname;
                  }, $commentUserArr);
                  // dump($commentUserArr);
                  $commentUser = "@".implode('@', $commentUserArr)." ";
              } else {
                  $commentUser = " ";
              }
              // 超过2小时不能删除
              if (time()-strtotime($v['time'])>7200) {
                  $v['del_able'] = 0;
              } else {
                  $v['del_able'] = 1;
              }
              if(strpos($v['app_word'],'@所有人')){
                $commentUser = " ";
              }
              $comment_list[] = array('id'=>$v['id'], 'pid'=>$v['per_id'], 'avatar'=>$avatar, 'name'=>$v['per_name'], 'time'=>$v['time'], 'word'=>$commentUser.$v['app_word'], 'del_able'=>$v['del_able'],'wxid'=>$cwxUID);
        }

        return $comment_list;
    }

}
