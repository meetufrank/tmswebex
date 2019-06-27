<?php
namespace app\common\jobs;

use think\queue\Job;
use think\Db;
use app\common\logic\McuLogic;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class McuStop
{
    
    private $name="admin";
    private $pwd="admin";
    private $meeting_name="MeetingRoom01";
    private $ip="172.32.64.186";
    private $api_path="/RPC2";
    private $api_port="10000";
    private $protocol="http";


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
        $m_list=Db::connect('zbsql')->name('meeting_log')->alias('pl')
                ->join(config('zbsql.prefix').'mcu_list p','pl.mcu_id = p.id','left')
                ->where($m_where)
                ->field('p.*')
                ->find();
        if(empty($m_list)){
//            file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'执行删除:步骤1 '.$m_list,FILE_APPEND);
            $job->delete();
        }else{
            
            //判断会议是否在会议进行中已经手动取消，还是会议自然结束
             $m_where2=[
            'delete_time'=>['lt',$data['stoptime']],
            'id'=>$data['log_id']
             ];
           //获取会议室信息
            $m_list2=Db::connect('zbsql')->name('meeting_log')
                ->where($m_where2)
                ->find();
            if(!empty($m_list2)){
                $job->delete();  //手动取消不在执行删除操作了
            }else{
                
            
            $this->name=$m_list['name'];
            $this->pwd=$m_list['pwd'];
            $this->meeting_name=$m_list['meeting_name'];
            $this->ip=$m_list['ip'];
            $this->api_port=$m_list['api_port'];
            $this->api_path=$m_list['api_path'];
            $this->protocol=$m_list['protocol'];
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
            $m_list=Db::connect('zbsql')->name('meeting_log')->alias('pl')
                ->join(config('zbsql.prefix').'mcu_list p','pl.mcu_id = p.id','left')
                ->where($m_where)
                ->field('p.*')
                ->find();
          
            if(empty($m_list)){
               
                $job->delete();
            }else{
                $this->name=$m_list['name'];
                $this->pwd=$m_list['pwd'];
                $this->meeting_name=$m_list['meeting_name'];
                $this->ip=$m_list['ip'];
                $this->api_port=$m_list['api_port'];
                $this->api_path=$m_list['api_path'];
                $this->protocol=$m_list['protocol'];
                
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

       try{
            //执行终端任务
            $this->execute($data);
            file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$data['meetingkey']."会议删除进程执行成功\n",FILE_APPEND);
            
        } catch (\Exception $e){
            file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$data['meetingkey'].'会议出现了删除执行错误,错误信息为：'.$e->getMessage()."\n",FILE_APPEND);
            
        }
        
    }
    
  private function execute($data){
        //初始化mcu接口类
        $mculogic=new McuLogic($this->name, $this->pwd, $this->ip, $this->api_port, $this->api_path);
        
        $mculogic->stop_task($this->meeting_name);  //结束会议
        
        //删除终端
        $mculogic->delete_terminal($mculogic->get_terminal_name($this->meeting_name, 'sip')); 
        $mculogic->delete_terminal($mculogic->get_terminal_name($this->meeting_name, 'rtmp')); 
        
        
    }
}
