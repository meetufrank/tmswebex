<?php
namespace app\common\jobs;

use think\queue\Job;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PexipJob
{
    
    private $username="admin";
    private $password="Cisco123";
    private $conference_alias="10000@ketianlive.com";
    private $system_location="BJDC-Internal";
    
    /**
     * 开始执行
     * @param array $data  内容
     * @return 
     */
    public function sendlive(Job $job, $data) 
    {
        
        if($data['stoptime']>time()){
            $time=$data['stoptime']-time();
             $job->release($time);
        }elseif($data['stoptime']<=time()){
           $result=$this->send($data);
           $job->delete();
        }  
        
        
        
     
       
    }
    /**
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function send($data) 
    {

        $authstr= base64_encode($this->username.":". $this->password);
        $arr_header[] = "Content-Type:application/json";
        $arr_header[] = "Authorization: Basic ".$authstr; //添加头，在name和pass处填写对应账号密码
        $result=httpRequest("https://106.38.228.233:65443/api/admin/status/v1/conference/?service_type=conference", 'get', [],$arr_header);
        if($result){
            $result=json_decode($result,true);
            if(isset($result['objects'])){
                //先查询会议列表，后查询会议中的呼叫数，根据呼叫列表判断是否需要断开
                if(!empty($result['objects'])){
                    $objects=$result['objects'];
                    foreach ($objects as $key => $value) {
                        $objid=$value['id'];
                        $objname=$value['name'];
                        $p_result=httpRequest("https://106.38.228.233:65443/api/admin/status/v1/participant/?conference=".$objname, 'get', [],$arr_header);
                        if($p_result){
                            $p_result=json_decode($p_result,true);
                            if(isset($p_result['objects'])){
                                $p_objects=$p_result['objects'];
                                $status_arr=[
                                    'rtmp'=>false,
                                    'sip'=>false
                                ];
                                foreach ($p_objects as $k => $v) {
                                    if(strtolower($v['protocol'])=='rtmp'){
                                        $status_arr['rtmp']=true;
                                    }elseif (strtolower($v['protocol'])=='sip') {
                                        $status_arr['sip']=true;
                                    }
                                    
                                }
                                //判断是否sip和rtmp同时存在
                                if($status_arr['sip']!=$status_arr['rtmp']){
                                    $json_data=[
                                        'conference_id'=>$objid
                                    ];
                                    
                                   $d_result=httpRequest("https://106.38.228.233:65443/api/admin/command/v1/conference/disconnect/", 'post', json_encode($json_data),$arr_header);
                                    
                                }
                            }
                        }
                    }
                }
            }
        }
        
    }
    

}
