<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Queue;
class Ceshi extends Base {
    
//    public function ceshi() {
//       //查询会议室列表
////      $postdata=[
////            'conference_alias'=> $this->conference_alias,
////            'destination'=>$data['sipurl'],
////            'remote_display_name'=>$data['name'],
////            'protocol'=>'sip',
////            'system_location'=> $this->system_location,
////            'role'=>'chair',
////            'call_type'=>'video',
////            'routing'=>'routing_rule'
////        ];
////        $postdata= json_encode($postdata);
//        $authstr= base64_encode("admin:Cisco123");
//        $arr_header[] = "Content-Type:application/json";
//        $arr_header[] = "Authorization: Basic ".$authstr; //添加头，在name和pass处填写对应账号密码
//        $result=httpRequest("https://106.38.228.233:65443/api/admin/status/v1/conference/?service_type=conference", 'get', [],$arr_header);
//        if($result){
//            $result=json_decode($result,true);
//            if(isset($result['objects'])){
//                //先查询会议列表，后查询会议中的呼叫数，根据呼叫列表判断是否需要断开
//                if(!empty($result['objects'])){
//                    $objects=$result['objects'];
//                    foreach ($objects as $key => $value) {
//                        $objid=$value['id'];
//                        $objname=$value['name'];
//                        $p_result=httpRequest("https://106.38.228.233:65443/api/admin/status/v1/participant/?conference=".$objname, 'get', [],$arr_header);
//                        if($p_result){
//                            $p_result=json_decode($p_result,true);
//                            if(isset($p_result['objects'])){
//                                $p_objects=$p_result['objects'];
//                                $status_arr=[
//                                    'rtmp'=>false,
//                                    'sip'=>false
//                                ];
//                                foreach ($p_objects as $k => $v) {
//                                    if(strtolower($v['protocol'])=='rtmp'){
//                                        $status_arr['rtmp']=true;
//                                    }elseif (strtolower($v['protocol'])=='sip') {
//                                        $status_arr['sip']=true;
//                                    }
//                                    
//                                }
//                                //判断是否sip和rtmp同时存在
//                                if($status_arr['sip']!=$status_arr['rtmp']){
//                                    $json_data=[
//                                        'conference_id'=>$objid
//                                    ];
//                                    
//                                   $d_result=httpRequest("https://106.38.228.233:65443/api/admin/command/v1/conference/disconnect/", 'post', json_encode($json_data),$arr_header);
//                                    print_r($d_result);exit;
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        
//    }
    public function ceshi() {
        
        Queue::push('app\common\jobs\PexipJob@sendlive', [], $queue ='PexipJob');
    }
    
}
