<?php

namespace app\index\controller;

use think\Db;

use think\Session;
use think\Controller;
use think\Queue;

use app\common\logic\McuLogic;


class Ceshi extends Controller {
   
     //mcu接口测试
    public function ceshi() {
        
        return view();
        
      //$this->add_terminals();
//        $this->add_call();
//        $this->toggle_ext();
         //$this->stop_task();
//         $this->delete_terminal();
//         $data=[
//                'mcu_id'=>1,
//                'startime'=>1561712940,
//                'stoptime'=>1561716600,
//                'meetingkey'=>123123123,
//                'sipurl'=>123123,
//                'rtmpurl'=> 123123,
//                'name'=>123123,
//                'is_live'=>1,
//                'log_id'=>174 ,  //主要判断字段
//            ];
//         //查询当前会议室列表
//            $meet_list=Db::connect('zbsql')->name('mcu_list')
//                ->field('id')
//                ->select();
//            
//            $where=[
//            
//            'pl.start_time'=>['elt',$data['stoptime']],
//            'pl.delete_time'=>[
//                ['egt',$data['startime']],
//                ['egt',time()],
//                ],
//            'pl.id'=>['neq',$data['log_id']]
//            ];
//            $wedata=Db::connect('zbsql')->name('meeting_log')->alias('pl')
//                ->join(config('zbsql.prefix').'mcu_list p','pl.mcu_id = p.id','left')
//                ->where($where)
//                ->field('p.*,pl.mcu_id as ml_mcu_id')
//                ->select();
//           
//            $n_m_list=[];
//            if(empty($wedata)&&!empty($meet_list)){
//            
//                 $m_list=$meet_list[0];
//             }else{
//            
//              
//          
//                foreach ($wedata as $key => $value) {
//                    foreach ($meet_list as $kk => $vv) {
//                       if($value['ml_mcu_id']==$vv['id']){
//                           unset($meet_list[$kk]);  
//                       } 
//                    }
//                }
//                 
//                if(!empty($meet_list)){
//                    foreach ($meet_list as $key => $value) {
//                        $n_m_list[]=$value;
//                    }
//                   $m_list=$n_m_list[0];
//                }else{
//                    //无空闲会议室
//                    $job->delete();
//                    return true;
//                }
//            }
//           
//            if($m_list['id']!=$data['mcu_id']){
//                $data['mcu_id']=$m_list['id'];
//                $updatedata=[
//                    'mcu_id'=>$m_list['id']
//                ];
//                 $log_id=Db::connect('zbsql')->name('meeting_log')->where(['id'=>$data['log_id']])->update($updatedata);
//            }
//        $this->add_terminals();
    }
    
    /*
     * 开始会议
     */
    public function start_task() {
        $mculogic=new McuLogic('admin', 'admin', '172.32.64.186', '10000', '/RPC2');
     
        $mculogic->start_task('MeetingRoom01');
    }

    
    /*
     * 添加sip和rtmp终端
     */
    public function add_terminals() {
        
        $mculogic=new McuLogic('admin', 'admin', '172.32.64.186', '10000', '/RPC2');
    
        $mculogic->add_terminals('MeetingRoom01', '60400@ketiancloud.com', 'rtmp://pubsec.myun.tv/watch/77bk49?auth_key=2082733261-0-0-2eb5ea4e80e3bde93d5198cc3aab4fc3','');
    }
    
    /*
     * 邀请终端进入会议
     * 
     */
    public function add_call() {
        
        $mculogic=new McuLogic('admin', 'admin', '172.32.64.186', '10000', '/RPC2');
    
        $mculogic->add_call('MeetingRoom01', 'MeetingRoom01|sip'); //添加sip终端
        $mculogic->add_call('MeetingRoom01', 'MeetingRoom01|live');  //添加rtmp终端
    }
    
    /*
     * 打开sip和rtmp终端的双流状态
     * 
     */
    public function toggle_ext() {
        
        $mculogic=new McuLogic('admin', 'admin', '172.32.64.186', '10000', '/RPC2');
    
        $mculogic->toggle_ext('MeetingRoom01', 'MeetingRoom01|sip'); 
        $mculogic->toggle_ext('MeetingRoom01', 'MeetingRoom01|live');  
    }
    
    /*
     * 结束会议
     */
    
    public function stop_task() {
        
        $mculogic=new McuLogic('admin', 'admin', '172.32.64.186', '10000', '/RPC2');
    
        $mculogic->stop_task('MeetingRoom01'); 
        
    }
    
    /*
     * 删除终端
     */
    public function delete_terminal() {
        $mculogic=new McuLogic('admin', 'admin', '172.32.64.186', '10000', '/RPC2');
    
        $mculogic->delete_terminal('MeetingRoom01|sip'); 
        $mculogic->delete_terminal('MeetingRoom01|live'); 
    }
}
