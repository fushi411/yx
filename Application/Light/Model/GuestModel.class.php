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
        $id = '';
        foreach( $res as $v){
            $id .= "{$v},";
        }
        $id .= '341,';
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





}