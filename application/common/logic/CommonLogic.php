<?php
namespace app\common\logic;
class CommonLogic extends Logic {
    
    
    /*
     * 过滤字符串中所有空格
     */
    public function TrimStr($str){
        $search = array(" ","　","\n","\r","\t");
        $replace = array("","","","","");
        return str_replace($search, $replace, $str);
    }
    
   

}
