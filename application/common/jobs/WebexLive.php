<?php
namespace app\common\jobs;

use think\queue\Job;
use think\Db;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class WebexLive
{
    
    private $username="admin";
    private $password="Cisco123";
    private $conference_alias="10000@ketianlive.com";
    private $system_location="BJDC-Internal";
    
    /**
     * 开启直播
     * @param array $data  内容
     * @return 
     */
    public function sendlive(Job $job, $data) 
    {
        //查询会议是否提前取消了
        $m_where=[
            'pl.delete_time'=>['gt',$data['startime']],
            'pl.id'=>$data['log_id']
        ];
        //获取会议室信息
        $m_list=Db::connect('zbsql')->name('pexip_log')->alias('pl')
                ->join(config('zbsql.prefix').'pexip_list p','pl.pexip_id = p.id','left')
                ->where($m_where)
                ->field('p.*')
                ->find();
         
       
        if(empty($m_list)){
            //file_put_contents(RUNTIME_PATH.'redislog.txt', '开始执行sip',FILE_APPEND);
            $job->delete();
        }else{
            $this->username=$m_list['pexip_name'];
            $this->password=$m_list['pexip_pwd'];
            $this->conference_alias=$m_list['conference_alias'];
            $this->system_location=$m_list['system_location'];
            if ($job->attempts() > 3) {              
                // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
                //$job->release(10); 
                // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
                //$job->failed();   
                // 第3种处理方式：删除任务
                $job->delete();  
                }else{
                    //file_put_contents(RUNTIME_PATH.'redislog.txt', json_encode($data),FILE_APPEND);
                        $result=$this->send($data);

                        if($result==1){
                            $job->delete();
                        }elseif ($result==0) {

                                $job->failed();
                        }elseif ($result==2) {
                            if($data['startime']-time()>0){
                                $rel=$data['startime']-time();
                                $job->release($rel);
                            }

                        }
                }
        }
//        if(cache($data['meetingkey'])){
//            cache($data['meetingkey'],null);
//            $job->delete();
//            
//        }else{
//             if ($job->attempts() > 3) {              
//                // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
//                //$job->release(10); 
//                // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
//                //$job->failed();   
//                // 第3种处理方式：删除任务
//                $job->delete();  
//                }else{
//                    //file_put_contents(RUNTIME_PATH.'redislog.txt', json_encode($data),FILE_APPEND);
//                        $result=$this->send($data);
//
//                        if($result==1){
//                            $job->delete();
//                        }elseif ($result==0) {
//
//                                $job->failed();
//                        }elseif ($result==2) {
//                            if($data['startime']-time()>0){
//                                $rel=$data['startime']-time();
//                                $job->release($rel);
//                            }
//
//                        }
//                }
//        
//        
//       }
    }
    /**
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function send($data) 
    {
//         && (time()-$data['starttime']<=3*60)
       if($data['startime']<=time()){
              //file_put_contents(RUNTIME_PATH.'redislog.txt', '开始执行sip',FILE_APPEND);
            
            $result=$this->pexipcurl($data);
            if($result){
                return 1;
            }else{
                return 0;
            }
        }elseif($data['startime']>time()){
             //file_put_contents(RUNTIME_PATH.'redislog.txt', '未达到执行要求需重复执行sip',FILE_APPEND);
             return 2;
        }      
        
    }
    
    /*
     * pexip呼叫
     */
    private function pexipcurl($data,$method="post") {
        $postdata=[
            'conference_alias'=> $this->conference_alias,
            'destination'=>$data['sipurl'],
            'remote_display_name'=>$data['name'],
            'protocol'=>'sip',
            'system_location'=> $this->system_location,
            'role'=>'chair',
            'call_type'=>'video',
            'routing'=>'routing_rule'
        ];
        $postdata= json_encode($postdata);
        $authstr= base64_encode($this->username.":". $this->password);
        $arr_header[] = "Content-Type:application/json";
        $arr_header[] = "Authorization: Basic ".$authstr; //添加头，在name和pass处填写对应账号密码
     
        $result=httpRequest("https://106.38.228.233:65443/api/admin/command/v1/participant/dial/", $method, $postdata,$arr_header);
        file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$result."\n",FILE_APPEND);
        return true;
        //file_put_contents(RUNTIME_PATH.'redislog.txt', '1111',FILE_APPEND);
//        if($result){
//            return true;
//            $result=json_decode($result,true);
//            if(is_array($result)){
//                if(isset($result['status'])){
//                    return true;
//                }
//            }
             
//        }else{
//            return false;
//        }
    }
}
