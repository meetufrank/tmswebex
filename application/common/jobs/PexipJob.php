<?php
namespace app\common\jobs;

use think\queue\Job;
use think\Db;
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
    private $delete_name='MeetingRoom01';


    /**
     * 开始执行
     * @param array $data  内容
     * @return 
     */
    public function sendlive(Job $job, $data) 
    {
      
       try {
//           file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'开始执行删除操作',FILE_APPEND); 
        
        //判断队列类型
       if($data['type']==1){  //创建会议取消入口
           //判断该会议是否已经在呼叫前取消
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
            file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'执行删除:步骤1 '.$m_list,FILE_APPEND);
            $job->delete();
        }else{
            
            //判断会议是否在会议进行中已经手动取消，还是会议自然结束
             $m_where2=[
            'delete_time'=>['lt',$data['stoptime']],
            'id'=>$data['log_id']
             ];
           //获取会议室信息
            $m_list2=Db::connect('zbsql')->name('pexip_log')
                ->where($m_where2)
                ->find();
            if(!empty($m_list2)){
                $job->delete();  //手动取消不在执行删除操作了
            }else{
                
            
            $this->username=$m_list['pexip_name'];
            $this->password=$m_list['pexip_pwd'];
            $this->conference_alias=$m_list['conference_alias'];
            $this->system_location=$m_list['system_location'];
            $this->delete_name=$m_list['meeting_name'];
            if ($job->attempts() > 3) {              
                // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
                //$job->release(10); 
                // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
                //$job->failed();   
                // 第3种处理方式：删除任务
                 $job->delete();  
                 }else{
                    if($data['stoptime']-time()>0){
                        $rel=$data['stoptime']-time();
                        file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'延迟执行: '.$rel,FILE_APPEND);
                            $job->release($rel);
                     }else{
                       $result=$this->send($data);
                       $job->delete();  
                     } 
                 }
            }
        }
       }elseif($data['type']==2){    //删除入口
           
            
              
               //查询会议信息
           $m_where=[
            'pl.stop_time'=>['gt',time()],
            'pl.id'=>$data['log_id']
           ];
            //获取会议室信息
            $m_list=Db::connect('zbsql')->name('pexip_log')->alias('pl')
                ->join(config('zbsql.prefix').'pexip_list p','pl.pexip_id = p.id','left')
                ->where($m_where)
                ->field('p.*')
                ->find();
          
            if(empty($m_list)){
               
                $job->delete();
            }else{
                $this->username=$m_list['pexip_name'];
                $this->password=$m_list['pexip_pwd'];
                $this->conference_alias=$m_list['conference_alias'];
                $this->system_location=$m_list['system_location'];
                $this->delete_name=$m_list['meeting_name'];
                
               $result=$this->send($data);
               $job->delete(); 
            }
            
       }
    } catch (\Exception $e) {
        
        file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'删除操作执行异常: '.$e->getMessage(),FILE_APPEND);
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
        file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$result."\n",FILE_APPEND);
        if($result){
            $result=json_decode($result,true);
            if(isset($result['objects'])){
                //先查询会议列表，后查询会议中的呼叫数，根据呼叫列表判断是否需要断开
                if(!empty($result['objects'])){
                    $objects=$result['objects'];
                    foreach ($objects as $key => $value) {
                        
                        $objid=$value['id'];
                        $objname=$value['name'];
                        
                        if($objname==$this->delete_name){
                           
                         $json_data=[
                                        'conference_id'=>$objid
                                    ];
                                    
                         $d_result=httpRequest("https://106.38.228.233:65443/api/admin/command/v1/conference/disconnect/", 'post', json_encode($json_data),$arr_header);
                         return true;
                        }
                    }
                }
            }
        }
        
    }
    

}
