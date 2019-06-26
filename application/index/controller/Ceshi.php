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
        
//        $this->add_terminals();
//        $this->add_call();
//        $this->toggle_ext();
         //$this->stop_task();
//         $this->delete_terminal();
        //Queue::push('app\common\jobs\WebexLiveR@sendlive', ['aa'=>11], $queue ='webexlive');
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
    
        $mculogic->add_terminals('MeetingRoom01', '60400@ketiancloud.com', 'rtmp://pubsec.myun.tv/watch/77bk49?auth_key=2082733261-0-0-2eb5ea4e80e3bde93d5198cc3aab4fc3');
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
