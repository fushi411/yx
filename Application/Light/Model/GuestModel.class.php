<?php
namespace Light\Model;
use Think\Model;

/**
 * 页面数据逻辑模型
 * @author 
 */

class GuestModel extends Model {
    // 虚拟模型
    protected $autoCheckFields = false;
    // 获取建材账户有效客户
    public function get_kk_guest($data){
        $like = $data?"and (g_helpword like '%{$data}%' or g_name like '%{$data}%')":'';
        $res = D('Customer')->getVaildUser();
        $result = $this->getKkHtUserId();
        $res = array_merge($res,$result);
        $res = array_unique($res);
        $id = '';
        foreach( $res as $v){
            $id .= "{$v},";
        }
        
        $id = trim($id,',');
        $sql = "SELECT
                    id,
                    g_name AS text,
                    g_khjc AS jc
                FROM
                    kk_guest2
                WHERE
                    id  IN ({$id})  {$like} 
                GROUP BY
                    g_name
                ORDER BY
                    g_name ASC";
        $res  = M()->query($sql);
        return $res;
    }
    
    // 获取环保账户有效客户
    public function get_yxhb_guest($data){
        $today = date('Y-m-d',time());
        
        $like = $data?"and (g_helpword like '%{$data}%' or g_name like '%{$data}%')":'';
        $res = D('Customer')->getYxhbVaildUser();
        $result = $this->getYxhbHtUserId();
        $res = array_merge($res,$result);
        $res = array_unique($res);
        $id = '';
        foreach( $res as $v){
            $id .= "{$v},";
        }

        $id = trim($id,',');
        $sql = "SELECT
                    id,
                    g_name AS text,
                    g_khjc AS jc
                FROM
                    yxhb_guest2
                WHERE
                    id  IN ({$id})  {$like} 
                GROUP BY
                    g_name
                ORDER BY
                    g_name ASC";
        $res  = M()->query($sql);
        return $res;
    }

    // 获取建材合同有效客户
    public function getKkHtUser(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
        $res = M()->query($sql);
        return $res;
    }

    // 获取环保合同有效客户
    public function getYxhbHtUser(){
        $today = date('Y-m-d',time());
        $data = I('math');
        $like = $data?"where g_helpword like '%{$data}%' or g_name like '%{$data}%'":'';
        $sql = "select id,g_name as text,g_khjc as jc from (select a.id as id,g_name,g_helpword,g_khjc FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM yxhb_guest2 where id=any(select a.reid as id FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t {$like} order by g_name ASC";
        $res = M()->query($sql);
        return $res;
    }
    
    // 获取建材合同有效客户id
    public function getKkHtUserId(){
        $today = date('Y-m-d',time());
        $sql = "select id from (select a.id as id,g_name,g_helpword,g_khjc FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM kk_guest2 where id=any(select a.reid as id FROM kk_guest2 as a,kk_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t  order by g_name ASC";
        $res = M()->query($sql);
        $id  = array();
    
        foreach($res as $val){
                $id[] = $val['id'];
        }
        return $id;
    }

    // 获取环保合同有效客户id
    public function getYxhbHtUserId(){
        $today = date('Y-m-d',time());
        $sql = "select id from (select a.id as id,g_name,g_helpword,g_khjc FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2 and reid=0 group by ht_khmc UNION select id,g_name,g_helpword,g_khjc FROM yxhb_guest2 where id=any(select a.reid as id FROM yxhb_guest2 as a,yxhb_ht as b where a.id=b.ht_khmc and reid!= 0 and ht_stday<='{$today}' and ht_enday>='{$today}' and ht_stat=2  group by ht_khmc)) as t order by g_name ASC";
        $res = M()->query($sql);
        $id  = array();
        foreach($res as $val){
                $id[] = $val['id'];
        }
        return $id;
    }

}