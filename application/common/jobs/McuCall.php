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
class McuCall
{
    
    private $name="admin";
    private $pwd="admin";
    private $meeting_name="MeetingRoom01";
    private $ip="172.32.64.186";
    private $api_path="/RPC2";
    private $api_port="10000";
    private $protocol="http";
    
    /**
     * 呼叫sip地址和rtmp
     * @param array $data  内容
     * @return 
     */
    public function sendlive(Job $job, $data) 
    {
       
        //查询会议是否提前取消了
        $m_where=[
            'delete_time'=>['gt',$data['startime']],
            'id'=>$data['log_id']
        ];
        
        //获取会议室信息
        $m_list=Db::connect('zbsql')->name('meeting_log')
                ->where($m_where)
                ->find();
        
       
        if(empty($m_list)){
            
            //file_put_contents(RUNTIME_PATH.'redislog.txt', '开始执行sip',FILE_APPEND);
            $job->delete();
        }else{ 
            //查询分配的会议室是否因为并发原因重复了
             //查询当前会议室列表
                    $meet_list=Db::connect('zbsql')->name('mcu_list') 
                        ->select();

                    $where=[

                    'pl.start_time'=>['elt',$data['stoptime']],
                    'pl.delete_time'=>[
                        ['egt',$data['startime']],
                        ['egt',time()],
                        ],
                    'pl.id'=>['neq',$data['log_id']]
                    ];
                    $wedata=Db::connect('zbsql')->name('meeting_log')->alias('pl')
                        ->join(config('zbsql.prefix').'mcu_list p','pl.mcu_id = p.id','left')
                        ->where($where)
                        ->field('p.*,pl.mcu_id as ml_mcu_id')
                        ->select();
                    $n_m_list=[];
                    if(empty($wedata)&&!empty($meet_list)){

                         $m_list=$meet_list[0];
                     }else{



                        foreach ($wedata as $key => $value) {
                            foreach ($meet_list as $kk => $vv) {
                               if($value['ml_mcu_id']==$vv['id']){
                                   unset($meet_list[$kk]);  
                               } 
                            }
                        }
                        if(!empty($meet_list)){
                            foreach ($meet_list as $key => $value) {
                                $n_m_list[]=$value;
                            }
                           $m_list=$n_m_list[0];
                        }else{
                            //无空闲会议室
                            $job->delete();
                            return true;
                        }
                    }

                    if($m_list['id']!=$data['mcu_id']){
                        $data['mcu_id']=$m_list['id'];
                        $updatedata=[
                            'mcu_id'=>$m_list['id']
                        ];
                         $log_id=Db::connect('zbsql')->name('meeting_log')->where(['id'=>$data['log_id']])->update($updatedata);
                    }
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
            
            $result=$this->mcucall($data);
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
    private function mcucall($data) {
        
        
        try{
//            file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$data['meetingkey']."会议开始执行\n",FILE_APPEND);
            //执行终端任务
            $this->execute($data);
           file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$data['meetingkey']."会议进程执行成功\n",FILE_APPEND);
            return true;
        } catch (\Exception $e){
            file_put_contents(RUNTIME_PATH.'redislog.txt', date('Y-m-d H:i:s').'    '.$data['meetingkey'].'会议出现了执行错误,错误信息为：'.$e->getMessage()."\n",FILE_APPEND);
            return true;
        }
        
        

    }
    
    private function execute($data){
       ;
        //初始化mcu接口类
        $mculogic=new McuLogic($this->name, $this->pwd, $this->ip, $this->api_port, $this->api_path);
        if($data['is_live']!=1){  //是否直播
          $data['rtmpurl']='';
        }
        if($data['is_pull']!=1){  //是否添加拉流
           $data['pull_url']=''; 
        }
        //替换ketiancloud为移动地址
        $cloudstr='ketiancloud.com';
        $data['sipurl']=str_replace($cloudstr,'111.13.160.94', $data['sipurl']);
        $mculogic->start_task($this->meeting_name); //开始会议
        $terminals_arr=$mculogic->add_terminals($this->meeting_name, $data['sipurl'], $data['rtmpurl'],$data['pull_url']);  //添加终端
        foreach (@$terminals_arr as $key => $value) {
            if(!empty($value)){
                $mculogic->add_call($this->meeting_name, $value);  //邀请终端
                $mculogic->toggle_ext($this->meeting_name, $value);  //打开双流
            }
        }
        
        
        
    }
}
