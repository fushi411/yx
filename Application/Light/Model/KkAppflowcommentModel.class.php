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
        $sql = "SELECT
                    *,
                    sum(1) AS count
                FROM
                    (
                        SELECT
                            id,
                            app_word,
                            time,
                            per_name,
                            per_id,
                            comment_to_id,
                            comment_img,
                            comment_ready
                        FROM
                            kk_appflowcomment
                        WHERE
                            mod_name = '$mod_name'
                        AND aid = $aid
                        AND per_id = 9999
                        AND app_stat = 1
                        ORDER BY
                            time DESC
                    ) a
                GROUP BY
                    comment_to_id ORDER BY time DESC";
        $res =  M()->query($sql);
        $pushArr = array();
        if(!empty($res)){
            $res_count = count($res);
            foreach($res as $k=>$v){
                $count = $v['count'];
                $tmp = explode('发起了',$v['app_word']);
                $str = str_replace('系统','已',$tmp[0])."发起{$count}次".$tmp[1];
                $class = $k == 0?($res_count > 1?'up':''):'dn';
                $tmpArr = array(
                    'id'            =>  0,
                    'app_word'      =>  strpos($str,'自动催审')?str_replace("自动催审","自动催审<br />",$str):str_replace("自动催收（每日9:30和15:30各一次）","自动催收<br />（每30分钟一次）",$str),
                    "time"          =>  $v['time'],
                    "per_name"      =>  "系统定时任务",
                    "per_id"        =>  "9999",
                    "comment_to_id" =>  $v['comment_to_id'],
                    'comment_ready' =>  $v['comment_ready'],
                    'class'         =>  $class,
                );
                $pushArr[] = $tmpArr;
            }
        }
        $delArr = $this->field('id,app_word,time,per_name,per_id,comment_to_id,comment_img,comment_ready')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>1,'per_id' =>8888))->order('time desc')->select();
        $cl = $this->field('id,app_word,time,per_name,per_id,comment_to_id,comment_img,comment_ready')->where(array('aid'=>$aid, 'mod_name'=>$mod_name, 'app_stat'=>1,'per_id' =>array('not in',array(9999,8888))))->order('time desc')->select();
        $cl = array_merge($pushArr,$cl);
        $cl = array_merge($delArr,$cl);
        $boss = D('kk_boss');
        foreach ($cl as $v) {
              $cwxUID = $boss->getWXFromID($v['per_id']);
              $avatar = $boss->getAvatar($v['per_id']);

              if (!empty($v['comment_to_id'])) {
                  $commentUserArr = explode(',', $v['comment_to_id']);
                  $commentUserArr = array_filter($commentUserArr);
                  $commentUserArr = array_map(function($wxid) use ($boss) {
                      $cid       = $boss->getIDFromWX($wxid);
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

              if(strpos("烦躁".$v['app_word'],'@所有人') || $v['per_id'] == 9999 || $v['per_id'] == 8888){
                $commentUser = " ";
              }
              if(strpos($v['app_word'],'发起了催审! 催审理由')){
                $v['app_word'] = str_replace("催审理由","<br>催审理由",$v['app_word']);
              }
              # 已读人员检查
              $comment_ready = 'no_this_param';
              if($v['per_id'] != 9999 && $v['per_id'] != 8888){
                $comment_ready = $this->getReadyNum($v['comment_to_id'],$v['comment_ready']);
              }
              // 图片检查 
              $file = '';
              if($v['comment_img']){
                $file = explode('|',$v['comment_img']);
                $file = array_filter($file);
              }
              $comment_list[] = array(
                'id'       => $v['id'], 
                'is_img'   => $v['comment_img']?1:0 , 
                'file'     => $file, 
                'pid'      => $v['per_id'], 
                'avatar'   => $avatar, 
                'name'     => $v['per_name'], 
                'time'     => $v['time'], 
                'word'     => $commentUser.$v['app_word'], 
                'del_able' => $v['del_able'],
                'wxid'     => $cwxUID,
                'class'    => $v['class']?$v['class']:'',
                'comment_ready' => $comment_ready         
            );
        }

        return list_sort_by($comment_list,'time','desc');
    }

    /**
     * 获取已读信息
     * @param  string $comment_to_id 流程名
     * @param  int    $comment_ready 记录ID
     * @return string   摘要数组
     */
    public function getReadyNum($comment_to_id,$comment_ready){
        $comment_id_array    = explode(',',$comment_to_id);
        $comment_ready_array = explode(',',$comment_ready);
        if(empty($comment_ready_array)) return 'readynone';
        $res = array_intersect($comment_id_array,$comment_ready_array);
        $ready_count = count($res);
        $all_count   = count($comment_id_array);
        if($ready_count == $all_count) return 'readyall';
        return "{$ready_count}人已读";
        // 查找已读人数
    }

    /**
     * 获取自动催收，自动催审条数
     * @param  string $modname 流程名
     * @param  int    $aid 记录ID
     * @param  string $comment_to_id 推送人
     * @return array   摘要数组
     */
    public function autoMessageNumber($mod_name, $aid,$comment_to_id){
        $map = array(
            'mod_name'      => $mod_name,
            'aid'           => $aid,
            'per_id'        => 9999,
            'app_stat'      => 1,
            'comment_to_id' => $comment_to_id
        );
        
        $res =  $this->where($map)->count();
        return $res+1;
    }
    /**
     * 获取信息读取人员
     * @param  string $system 系统名
     * @param  int    $aid 记录ID
     * @return array   摘要数组
     */
    public  function getReadManHtml($id){
        $map = array(
            'id' => $id
        );
        $data = $this->where($map)->find();
        $comment_id_array    = explode(',',$data['comment_to_id']);
        $comment_ready_array = explode(',',$data['comment_ready']);
        $read = array();
        $noread = array();
        foreach( $comment_id_array as $val){
            if(in_array($val,$comment_ready_array)){
                $read[] = $val;
                continue;
            } 
            $noread[] = $val;
        }
        $User = D('kk_boss');
        $resArr = array();
        $html = '';
        foreach ($read as $key => $value) {
            $info = $User->getWXInfo($value);
            if (!empty($info)) {
                $avatar =$info['avatar']?$info['avatar']:'Public/assets/i/defaul.png';
                $html .= '<a class="weui-cell weui-cell_access select-comment-user" href="javascript:;" data-id="'.$info['id'].'" data-uid="'.$value['userid'].'" data-type="user" data-img="'.$info['avatar'].'" data-name="'.$info['name'].'" style="text-decoration:none;"><div class="weui-cell__hd"><img src="'.$avatar.'" alt="" style="width:20px;margin-right:5px;display:block"></div><div class="weui-cell__bd"><p style="margin-bottom: 0px;">'.$info['name'].'</p></div><div class="weui-cell__ft"></div></a>';
            }
        }
        $res['read'] = $html;
        $html = '';
        foreach ($noread as $key => $value) {
            $info = $User->getWXInfo($value);
            if (!empty($info)) {
                $avatar =$info['avatar']?$info['avatar']:'Public/assets/i/defaul.png';
                $html .= '<a class="weui-cell weui-cell_access select-comment-user" href="javascript:;" data-id="'.$info['id'].'" data-uid="'.$value['userid'].'" data-type="user" data-img="'.$info['avatar'].'" data-name="'.$info['name'].'" style="text-decoration:none;"><div class="weui-cell__hd"><img src="'.$avatar.'" alt="" style="width:20px;margin-right:5px;display:block"></div><div class="weui-cell__bd"><p style="margin-bottom: 0px;">'.$info['name'].'</p></div><div class="weui-cell__ft"></div></a>';
            }
        }
        $res['noread'] = $html;
        return $res;
    }

    /**
     * 评论审批记录已读
     * @param  string $mod_name 审批名
     * @param  int $aid      审批记录ID
     * @param  int $pid      kk用户ID
     * @return int           修改影响记录数
     */
    public function readCommentApply($mod_name, $aid)
    {
        
        $wxid = session('wxid');
        $mergeReader = array();
        $map = array(
            'aid'      => $aid, 
            'mod_name' => $mod_name,
            'app_stat' => 1,
        );
        $copytoRes = $this->where($map)->select();
        foreach ($copytoRes as $key => $value) {
            $readedArr = explode(',', $value['comment_ready']);
            if(in_array($wxid,$readedArr)) continue;
            $readedArr[] = $wxid;
            $comment_ready = implode(',',$readedArr);
            $comment_ready = trim($comment_ready,',');
            $save = array(
                'comment_ready' => $comment_ready,
            );
            $this->where(array('id' => $value['id']))->save($save);
        }
    }

}
