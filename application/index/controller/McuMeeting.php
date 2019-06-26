<?php

namespace app\index\controller;
ob_start(); 
//header("Access-Control-Allow-Origin:http://www.rflinker.com");          //设置允许发起的跨域请求
use think\Db;
use think\queue;
use think\Session;
use think\Request;
class McuMeeting extends Base {
    private $apidomain='https://api.meetv.com.cn';
    public function _initialize() {
        
        $this->assign('apidomain', $this->apidomain);
//        $ca_user=cookie('ca_user');
//        $ca_pwd=cookie('ca_pwd');
//        $ca_channer=cookie('ca_channer');
//        //检查入口的权限账号密码和频道号是否失效
//        if(empty($ca_user)||empty($ca_pwd)||empty($ca_channer)){
//           //检测是否有账号密码入口过来
//           if(input('post.ca_user')&&input('post.ca_pwd')&&input('post.ca_channer')){
//                  $ca_user= stripslashes(trim(input('post.ca_user')));
//                  $ca_pwd= stripslashes(trim(input('post.ca_pwd')));
//                  $ca_channer= stripslashes(trim(input('post.ca_channer')));
//           }else{
//                 $ca_user='admin';
//                 $ca_pwd='admin';
//                 $ca_channer=66;
//           }
//        }

    }
    public function login() {
        
            
            
        
            $this->assign('re_webExId',cookie('re_webExId'));
            $this->assign('re_siteName',cookie('re_siteName'));
            $this->assign('re_password',cookie('re_password'));
            return view();
        
        
        
        
        
    }
    public function postLogin(){
          $webexid= trim(input('post.webExId'));
            $password=input('post.password');
            $sitename=trim(input('post.siteName'));
            $remember=input('post.remember');
            
            $postdata=[
                'webExId'=>$webexid,
                'password'=>$password,
                'siteName'=>$sitename,
            ];
            
            
            $header=[
            'Content-Type'=>'textml; charset=utf-8', 
            ];
           
            $result= httpRequest($this->apidomain.'/api/5d131411de7bb', 'post', $postdata,$header,false);
            
            if($result){
                if(json_decode($result,true)['code']==1){
                    
                    if($remember=='on'){
                        cookie('re_webExId',$webexid);
                        cookie('re_siteName',$sitename);
                        cookie('re_password',$password);
                     }else{
                        cookie('re_webExId',null);
                        cookie('re_siteName',null);
                        cookie('re_password',null); 
                     }
                   
                }
                cookie('sessionid',json_decode($result,true)['sessionid']);
                echo $result;exit;
            }else{
                $this->buildFailed(-1, '网络连接错误,请稍后再试');
            }
            
    }
    
    //webex列表
    public function  webex_list($type=1){
        
        if($type==1){
            $status='Pending';
        }else{
            $status='Ongoing';
        }
        $postdata=[
          'ConferenceStatus'  =>$status,
          
        ];
        if(cookie('sessionid')){
            $postdata['sessionid']=cookie('sessionid');
        }
        $header=[
            'Content-Type'=>'textml; charset=utf-8', 
            ];
           
     
       
        
        
         
        $result= httpRequest($this->apidomain.'/api/5d1314e4916d5', 'post', $postdata,$header,false);
       
        if($result){
            $r_arr=json_decode($result,true);  //列表数组
           
            if($r_arr['code']==1){
                 $this->assign('type', $type);
                 $this->assign('list', $r_arr['data']);
                 
               return view('list');
            }elseif($r_arr['code']==-7) {
                $this->redirect('/webex_login');
            }    
           
       }
            
            
    }
    
    
       //webex会议详情
    public function  webex_content(){
        $meetingKey= input('meetid/d');;
         
        if($meetingKey){
            
            $postdata=[
              'meetingKey'  =>$meetingKey,
          
            ];
            if(cookie('sessionid')){
                $postdata['sessionid']=cookie('sessionid');
            }
            $header=[
                'Content-Type'=>'textml; charset=utf-8', 
                ];






            $result= httpRequest($this->apidomain.'/api/5d13157955b1d', 'post', $postdata,$header,false);
       
            if($result){
                $r_arr=json_decode($result,true);  //列表数组

                if($r_arr['code']==1){
                     
                     $this->assign('info', $r_arr['data']);

                   return view('details');
                }elseif($r_arr['code']==-7) {
                    $this->redirect('/webex_login');
                }    

           }
            
        }
            
            
    }
    //webex会议删除
    
    public function delete() {
        if(request()->isPost()){
            $meetingKey= trim(input('post.meetingKey'));
          
            $postdata=[
                'meetingKey'=>$meetingKey,
               
            ];
            if(cookie('sessionid')){
                
               $postdata['sessionid']=cookie('sessionid');
               
             }
             
            $header=[
            'Content-Type'=>'textml; charset=utf-8', 
            ];
            $result= httpRequest($this->apidomain.'/api/5d1315c5f22bf', 'post', $postdata,$header,false);
          
            
            if($result){
                
                echo $result;exit;
            }else{
                $this->buildFailed(-1, '网络连接错误,请稍后再试');
            }
            
            
        }
        
        
        
        
    }
    
    
    //webex会议预约
    public function webex_add() {
        
        if(request()->isPost()){
            $meetingName= input('post.meetingName')?trim(input('post.meetingName')):'';
            $meetingStart=input('post.meetingStart');
            $meetduration=input('post.meetduration');
            $meetingPassword=input('post.meetingPassword')?input('post.meetingPassword'):'';
            $postdata=[
                'meetingName'=>$meetingName,
                'meetingStart'=>$meetingStart,
                'meetduration'=>$meetduration,
                'meetingPassword'=>$meetingPassword
            ];
            if(cookie('sessionid')){
                
               $postdata['sessionid']=cookie('sessionid');
               
             }
             
            $header=[
            'Content-Type'=>'textml; charset=utf-8', 
            ];
            $result= httpRequest($this->apidomain.'/api/5d13125061e1a', 'post', $postdata,$header,false);
          
            
            if($result){
                
                echo $result;exit;
            }else{
                $this->buildFailed(-1, '网络连接错误,请稍后再试');
            }
            
            
        }
         return view('add');
    }

    
    //webex会议立即开始
    public function begin() {
        
        
        return view();
    }
    
     //webex接口测试
    public function ceshi() {
        
        
        return view();
    }
}
