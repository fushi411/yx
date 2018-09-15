<?php
namespace Light\Controller;

class TestController extends \Think\Controller {
 
    public function copyTo(){
        header("Content-type:text/html;charset=utf-8");
        $id = 2946;
        $res = D('YxhbSalesReceiptsApply','Logic')->forTest($id);
        dump($res);
       
    } 
 
   
} 

