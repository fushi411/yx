<?php
namespace Light\Controller;

class TaskController extends \Think\Controller {
   
    private $titleArr;

    public function __construct(){
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");

        $this->titleArr = array(
            array('title' => '我的代办','url' => U('Light/Task/commission'),'on' => ''),
            array('title' => '项目查看','url' => U('Light/Task/look'),'on' => ''),
            array('title' => '配置中心','url' => U('Light/Task/config'),'on' => ''),
        );

    }
    
   public function commission(){
       
       //$this->display("Task/index");
   }

    
} 


