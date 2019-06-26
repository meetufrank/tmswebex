<?php
namespace app\common\logic;
class PexipLogic extends Logic {
    
    
   /*
    * pexip断开会议室
    */
   public function PexipList() {
       $authstr= base64_encode(":");
        $arr_header[] = "Content-Type:application/json";
        $arr_header[] = "Authorization: Basic ".$authstr; //添加头，在name和pass处填写对应账号密码
        $result=httpRequest("https://106.38.228.233:65443/api/admin/status/v1/conference/?service_type=conference", 'get', [],$arr_header);
        if($result){
            $result=json_decode($result,true);
            if(isset($result['objects'])){
                //先查询会议列表，后查询会议中的呼叫数，根据呼叫列表判断是否需要断开
                if(!empty($result['objects'])){
                    $objects=$result['objects'];
                    return $objects;
                }
            }
        }
   }
   

}
