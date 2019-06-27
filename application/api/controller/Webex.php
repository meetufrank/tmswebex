<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Queue;
class Webex extends Base {
    
    private $requesturl;
    private $ossurl;
    private $sitename;
    private $webexid;
    private $password;
    private $tmsconfig=array();
    private $rtmpconfig=array();
    private $channelconfig=array();
    private $tmsusername="ketiancloud\\api_admin";
    private $tmspassword="P@ss1234";
    private $responseurl;
    private $isSpecial=false;  //特殊用户 预留
    private $dbname='zbsql';
    private $select_channerid=0;  //被预约的频道ID

    public function _initialize() {
//       $insertdata=[
//                'channel_id'=>1,
//                'meeting_id'=>1
//            ];
//             Db::connect('zbsql')->name('c_meeting')->insert($insertdata);
//             exit;
//       
//        $zbdb=Db::connect('zbsql')->name('admin')->alias('ad')
//                ->join(config('zbsql.prefix').'auth_group ag','ad.group_id = ag.group_id','left')
//                ->field('ad.*,ag.title as groupname')
//                ->select();
                

//        print_r($zbdb);exit;
        if(strtolower(Request::instance()->action())!='webexlogin' && strtolower(Request::instance()->action())!='getclientid'){
          
            if(input('post.sessionid')){
                
               session_id(input('post.sessionid'));
                
            }
            
            $this->fieldSet();
        }
    }
    /*
     * 变量赋值
     */
    private function fieldSet() {
       //print_r(session('sitename'));exit;
      if($this->isSpecial){
            $this->sitename= session('sitename')?session('sitename'):$this->needlogin();
            $this->webexid=session('webexid')?session('webexid'):$this->needlogin();
            $this->password=session('password')?session('password'):$this->needlogin();
        }else{
            $this->sitename= session('sitename')?session('sitename'):$this->needlogin();
            $this->webexid=session('webexid')?session('webexid'):$this->needlogin();
            $this->password=session('password')?session('password'):$this->needlogin();
            $this->tmsconfig=session('tmsconfig')?session('tmsconfig'):$this->needlogin();
            $this->rtmpconfig=session('tmsconfig')?session('rtmpconfig'):$this->needlogin();
            $this->channelconfig=session('tmsconfig')?session('channelconfig'):$this->needlogin();
            $this->select_channerid=session('select_channerid')?session('select_channerid'):$this->needlogin();
            
        }
        
        $this->requesturl="https://".$this->sitename.".webex.com.cn/WBXService/XMLService";
        $this->ossurl="https://".$this->sitename.".webex.com.cn/WBXService/XMLService";
    }
    /*
     * 返回需要登陆信息
     */
    private function needlogin(){
        echo $this->buildFailed(-7, '账号或密码不正确或失效。',[],false); //表示未登录,或者登陆失效
                
        exit;
    }
    /*
     * 获取当前设备号和随机字符串
     * 
     */
    public function GetClientID(){
        session_start();
        $sessionid=session_id();
        $str=rand_string(11);
        $data=[
          'device_id'=> $sessionid,
          'rand_str' => $str
        ];
        $this->buildSuccess($data, '获取成功');
    }
 
    /*
     * 获取用户信息
     */
    public function GetUser() {
        $this->responseurl="java:com.webex.service.binding.user.GetUser";
        $postdata=[
            'webExId'=> input('post.webExId')
        ];
       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
      
       
       $this->buildSuccess($getdata, '获取成功');
       
       
       
    }
    
    /*
     * webex登陆接口
     */
    public function WebexLogin() {
        $webexid= trim(input('post.webExId'));
        $password=input('post.password');
        $sitename=trim(input('post.siteName'));
        
        
        //根据登陆的信息查询是否在平台录入
        $wemap=[
            'wl.webex_name'=>$webexid,
            'wl.webex_password'=>$password,
            'wl.webex_website'=>$sitename,
//            'wl.userid'=>$userid,
//            'c.userid'=>$userid,
//            'ti.userid'=>$userid
        ];
        $wedata=Db::connect('zbsql')->name('ch_we_cms')->alias('cwc')
                ->join(config('zbsql.prefix').'channel c','cwc.channels_id = c.id','left')
                ->join(config('zbsql.prefix').'webex_list wl','cwc.webex_id = wl.id','left')
                ->join(config('zbsql.prefix').'tms_list ti','cwc.tms_id = ti.id','left')
                ->where($wemap)
                ->field('cwc.*,c.channel_name,c.pushurl,ti.tms_name,ti.tms_password')
                ->select();
        //print_r($wedata);exit;
        if(empty($wedata)){
            echo $this->buildFailed(-1, '该webex账号您无权限登陆或者未绑定您的频道',[],false);
            exit;
        }
        
        
        
        //整合数组
        $rtmparr=[];
        $tmsarr=[];
        $channelarr=[];
        foreach ($wedata as $key => $value) {
            $select_channerid=$value['channels_id'];
            $rtmp[$value['channels_id']]['rtmpurl']=$value['pushurl'];
            
            $tmsarr[$value['channels_id']]['tms_name']=$value['tms_name'];
            $tmsarr[$value['channels_id']]['tms_password']=$value['tms_password'];
            
            $channelarr[$value['channels_id']]['channels_id']=$value['channels_id'];
            $channelarr[$value['channels_id']]['channel_name']=$value['channel_name'];
            break;  //执行一次，暂时为一频道一账号
        }
        
//        $tmsarr=assoc_unique($tmsarr,'tms_name');
        
        $this->responseurl="java:com.webex.service.binding.user.GetUser";
        session('webexid',$webexid);
        session('password',$password);
        session('sitename',$sitename);
        session('tmsconfig',$tmsarr);
        session('rtmpconfig',$rtmp);
        session('channelconfig',$channelarr);
        session('select_channerid',$select_channerid); //选中的频道ID
//        session('tmsusername',$wedata['tms_name']);
//        session('tmspassword',$wedata['tms_password']);
        $this->fieldSet();
        
//        $postdata=[
//            'webExId'=> $webexid
//        ];
//       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
// 
//       if(!empty($getdata)){
//           session('userName',$getdata['usefirstName'].$getdata['uselastName']);
//           $getdata=[];
//           $this->buildSuccess($getdata, '登陆成功');
//       }
       $this->buildSuccess([], '登陆成功');
    }

    
    
    /*
     * TMS预约webex
     */
    public function tmsCreatMeet() {
        //查询会议时间是否冲突
        $timedata=[
            'startime'=>strtotime(input('post.meetingStart'))-3*60,
            'stoptime'=>strtotime(input('post.meetingStart'))+input('post.meetduration')*60,
        ];
        $room_id=$this->validateMeeting($timedata);  //验证并获取会议室号
        $this->responseurl="https://tms.ketiancloud.com/tms/external/booking/bookingservice.asmx";
        $postdata=[
            'Conference'=>[
                'ConferenceId'=>'-1',
                'Title'=>input('post.meetingName')?input('post.meetingName'):session('userName').'的直播会议',
                'StartTimeUTC'=>date('Y-m-d H:i:s', strtotime(input('post.meetingStart'))-date('Z')-3*60).'Z',
                'EndTimeUTC'=>date('Y-m-d H:i:s', strtotime(input('post.meetingStart'))+input('post.meetduration')*60-date('Z')).'Z',
//                'StartTimeUTC'=>date('Y-m-d H:i:s', strtotime(input('post.meetingStart'))-date('Z')).'Z',
//                'EndTimeUTC'=>date('Y-m-d H:i:s', strtotime(input('post.meetingStart'))+input('post.meetduration')*60-date('Z')).'Z',
                'ConferenceType'=>'Automatic Call Launch',
                'Bandwidth'=>'32b/2048kbps',
                'PictureMode'=>'Continuous Presence',
                'Encrypted'=>'If Possible',
                'DataConference'=>'Yes',
                'ShowExtendOption'=>'AutomaticBestEffort',
                'ISDNRestrict'=>'false',
                'ExternalConference'=>[
                    'WebEx'=>[
                        'MeetingPassword'=>input('post.meetingPassword'),
                        'JoinBeforeHostTime'=>'00:15:00',
                        'UsePstn'=>'false',
                        'OwnedExternally'=>'false'
                    ]
                ],
                'ISDNBandwidth'=>[
                    'Bandwidth'=>'6b/384kbps'
                ],
                'IPBandwidth'=>[
                    'Bandwidth'=>'32b/2048kbps'
                ],
//                'ConferenceLanguage'=>'zh-CN',
                 'ConferenceLanguage'=>'en_US',
                'ConferenceTimeZoneRules'=>[
                    'TimeZoneRule'=>[
                        'Id'=>'China Standard Time',
                        'BaseOffsetInMinutes'=>'480',
                        'DaylightOffsetInMinutes'=>'0'
                    ]
                ]
                
                
            ]
        ];
        
        $result=$this->tmsCurl($postdata,'SaveConference');
        $status=$this->validclient($result);
        if(!$status){
            
            $result=$this->tmsCurl($postdata,'SaveConference');
        }
        if(empty($result['data'])){
            echo $this->buildFailed(-1, '系统繁忙请重新预约',[],false);
            exit;
//            var_dump($result);exit;
        }
        
        if(!empty($result)){
            
            
            $data=$result['data']['SaveConferenceResponse']['SaveConferenceResult'];
           
            //假设选择的是频道66
            $channel_id=$this->select_channerid;
            if(!$data['ConferenceId']){
                echo $this->buildFailed(-1, '创建会议失败',[],false);
                exit;
            }
            //如果会议未能成功预约出webex，则取消
            if(!isset($data['ExternalConference']['WebEx'])||empty($data['ExternalConference']['WebEx'])){
                $de_postdata=$data['ConferenceId'];
                $result=$this->tmsCurl($de_postdata,'DeleteConferenceById');
                $status=$this->validclient($result);
                if(!$status){
                    $result=$this->tmsCurl($postdata,'DeleteConferenceById');
                }
                echo $this->buildFailed(-1, '创建会议失败,请重新创建',[],false);
                exit;
            }
            
            $sipurl='/[1-9]\d*@ketiancloud.com/';
            preg_match($sipurl,$data['ConferenceInfoText'],$siparr);
            if(!empty($siparr)){
                $cmrurl=$siparr[0];
            }else{
                $cmrurl='';
            }
            $p_log_data=[
                'pexip_id'=>$room_id,
                'cloud_id'=>$cmrurl,
                'start_time'=>strtotime($data['StartTimeUTC'])+2*60,
                'stop_time'=>strtotime($data['EndTimeUTC']),
                'delete_time'=>strtotime($data['EndTimeUTC']),
                'channel_id'=> $this->select_channerid
            ];
            $log_id=Db::connect('zbsql')->name('pexip_log')->insertGetId($p_log_data);
            $insertdata=[
                'channel_id'=>$channel_id,
                'meeting_id'=>$data['ConferenceId'],
                'log_id'=>$log_id,
                'call_work'=>1 //pexip
            ];
            Db::connect('zbsql')->name('c_meeting')->insert($insertdata);
            $returndata=[
             'meetingkey' => $data['ConferenceId']
            ];
            //加入sip呼叫队列
          
           
            $queuedata=[
                'startime'=>strtotime($data['StartTimeUTC'])+2*60,
                'meetingkey'=>$data['ConferenceId'],
//                'startime'=>strtotime($data['StartTimeUTC']),
                'sipurl'=>$cmrurl,
                'name'=>$data['Title'],
                'log_id'=>$log_id   //主要判断字段
            ];
            
            Queue::push('app\common\jobs\WebexLive@sendlive', $queuedata, $queue ='webexlive');
            //获取rtmp地址
            
            //加入rtmp呼叫队列
            $queuertmpdata=[
                'startime'=>strtotime($data['StartTimeUTC'])+2*60,
                'meetingkey'=>$data['ConferenceId'],
//                'startime'=>strtotime($data['StartTimeUTC']),
//                'rtmpurl'=>'rtmp://pubsec.myun.tv/watch/1gba4y?auth_key=2082733261-0-0-e9c7cb4321521807f645465bc45729b3',
                'rtmpurl'=> $this->rtmpconfig[$channel_id]['rtmpurl'],
                'name'=>$data['Title']."|直播",
                'log_id'=>$log_id   //主要判断字段
            ];
            
            Queue::push('app\common\jobs\WebexLiveR@sendlive', $queuertmpdata, $queue ='webexlive');
            
            //加入pexip队列
            $queuertmpdata=[
                'meetingkey'=>$data['ConferenceId'],
                'stoptime'=>strtotime($data['EndTimeUTC']),
                'startime'=>strtotime($data['StartTimeUTC'])+2*60,
                'log_id'=>$log_id,   //主要判断字段
                'type'=>1 //取消会议类型 1为创建会议加入的取消队列，2为直接取消会议取消队列
            ];
            
            Queue::push('app\common\jobs\PexipJob@sendlive', $queuertmpdata, $queue ='pexipjobs');
//            
            $this->buildSuccess($returndata, '创建成功');
        
        }
        
    }
    
    //验证会议是否冲突
    protected function validateMeeting($timedata) {
        
        //先查询该频道下是否已经冲突时段的直播会议
        $c_where=[
            'channel_id'=> $this->select_channerid,
            'start_time'=>['elt',$timedata['stoptime']],
            'delete_time'=>[
                ['egt',$timedata['startime']],
                ['egt',time()],
                ]
        ];

        $count=Db::connect('zbsql')->name('pexip_log')
                ->where($c_where)
                ->count();
        if($count){
            echo $this->buildFailed(-1, '该频道在您预约的时间段内已有别会议，请重新选择时间。',[],false); 
                
                exit;
        }
        //查询当前会议室列表
        $m_list=Db::connect('zbsql')->name('pexip_list')
                ->field('id')
                ->select();
        
        //查询是否有空闲的会议室(把时间冲突的会议室id查询出来)
        $where=[
            
            'start_time'=>['elt',$timedata['stoptime']],
            'delete_time'=>[
                ['egt',$timedata['startime']],
                ['egt',time()],
                ]
        ];
        $wedata=Db::connect('zbsql')->name('pexip_log')
                ->distinct(true)
                ->field('pexip_id')
                ->where($where)
                ->select();
        if(empty($wedata)&&!empty($m_list)){
            
            return $m_list[0]['id'];
        }else{
            foreach ($wedata as $key => $value) {
                foreach ($m_list as $kk => $vv) {
                   if($value['pexip_id']==$vv['id']){
                       unset($m_list[$kk]);  
                   } 
                }
            }
            if(!empty($m_list)){
                foreach ($m_list as $key => $value) {
                    $n_m_list[]=$value;
                }
                return $n_m_list[0]['id'];
            }else{
                echo $this->buildFailed(-1, '无空闲的会议室供您创建会议，请重新选择时间。',[],false); 
                
                exit;
            }
        }
        
//       
//        $this->responseurl="https://tms.ketiancloud.com/tms/external/booking/bookingservice.asmx";
//        $where=[
//            'Pending',
//            'Ongoing'
//        ];
//        foreach ($where as $key => $value) {
//            
//        
//         $postdata=[
//            'ConferenceStatus'=>stripslashes($value)
//        ];
//        $result=$this->tmsCurl($postdata,'GetConferencesForSystems');
//        
//        $status=$this->validclient($result);
//        if(!$status){
//            $result=$this->tmsCurl($postdata,'GetConferencesForSystems');
//        }
//        
//        if(!empty($result['data']['GetConferencesForSystemsResponse']['GetConferencesForSystemsResult'])){
//            $getdata=$result['data']['GetConferencesForSystemsResponse']['GetConferencesForSystemsResult']['Conference'];
//        }else{
//            $getdata=$result['data']['GetConferencesForSystemsResponse']['GetConferencesForSystemsResult'];
//        }
//        
//        
//        $listdata=[];
//        
//        
//        if(!isset($getdata['ConferenceId'])){
//        foreach ($getdata as $key => $value) {
//            if(strtotime($value['StartTimeUTC'])>$timedata['stoptime']||$timedata['startime']>strtotime($value['EndTimeUTC'])){
//                return true;
//            }else{
//                echo $this->buildFailed(-1, '会议时段被占用，请重新选择时间。',[],false); 
//                
//                exit;
//            }
//            $listdata[$key]['starttime']=date('Y-m-d H:i:s', strtotime($value['StartTimeUTC'])+3*60);
//            $listdata[$key]['stoptime']=date('Y-m-d H:i:s', strtotime($value['EndTimeUTC']));
//            $listdata[$key]['meetingkey']=$value['ConferenceId'];
//            $listdata[$key]['meetingname']=$value['Title'];
//            $listdata[$key]['meetstatus']=$value['ConferenceState']['Status'];
//            
//         }
//        }else{
//            if(strtotime($getdata['StartTimeUTC'])>$timedata['stoptime']||$timedata['startime']>strtotime($getdata['EndTimeUTC'])){
//                return true;
//            }else{
//                echo $this->buildFailed(-1, '会议时段被占用，请重新选择时间。',[],false); 
//                
//                exit;
//            }
//           
//        }
//        
//        }
    }
  
    protected function validclient($result) {
        $resultcode=$result['code'];
        if($resultcode==-1){   //客户端id失效
                
               
                session('ClientSession',$result['data']);
                
               return false; 
        }else{
            return true; 
        }
    }
    /*
     * TMS获取会议信息
     */
    public function tmsGetMeetingInfo() {
        
        $this->responseurl="https://tms.ketiancloud.com/tms/external/booking/bookingservice.asmx";
        $postdata=[
            'ConferenceId'=>input('post.meetingKey')
        ];
        $result=$this->tmsCurl($postdata,'GetConferenceById');
        $status=$this->validclient($result);
        if(!$status){
            $result=$this->tmsCurl($postdata,'GetConferenceById');
        }
        if(!empty($result)){
            //print_r($result['data']);exit;
            $getdata=$result['data']['GetConferenceByIdResponse']['GetConferenceByIdResult'];
            if(!isset($getdata['ExternalConference'])){
                echo $this->buildFailed(-1, '该会议未成功与webex连接，请删除后重新预约。',[],false); 
                
                exit;
            }
            
            $sipurl='/[1-9]\d*@ketiancloud.com/';
            preg_match($sipurl,$getdata['ConferenceInfoText'],$siparr);
            if(!empty($siparr)){
                $cmrurl=$siparr[0];
            }else{
                $cmrurl='';
            }
            //查询直播地址
              $channeldata=Db::connect('zbsql')->name('c_meeting')->alias('cm')
                ->join(config('zbsql.prefix').'channel c','cm.channel_id = c.id','left')
                ->where(['cm.meeting_id'=>input('post.meetingKey')])
                ->field('cm.*,c.play_url')
                ->find();
              if(isset($getdata['ExternalConference']['WebEx'])){
                   $returndata=[
               'meetingkey'=>$getdata['ExternalConference']['WebEx']['MeetingKey'],
               'meetingpassword'=>$getdata['ExternalConference']['WebEx']['MeetingPassword'],
               'hostpassword'=>$getdata['ExternalConference']['WebEx']['HostKey'],
               'meetingname'=>$getdata['Title'],
               'starttime'=>date('Y-m-d H:i:s', strtotime($getdata['StartTimeUTC'])+3*60),
               'duration'=> (strtotime($getdata['EndTimeUTC'])-strtotime($getdata['StartTimeUTC'])+3*60)/60,
//               'starttime'=>date('Y-m-d H:i:s', strtotime($getdata['StartTimeUTC'])),
//               'duration'=> (strtotime($getdata['EndTimeUTC'])-strtotime($getdata['StartTimeUTC']))/60,
               'hostjoinurl'=>$getdata['ExternalConference']['WebEx']['HostMeetingUrl'],
               'guestjoinurl'=>$getdata['ExternalConference']['WebEx']['JoinMeetingUrl'],
               'cmrurl'=>$cmrurl,
//               'rtmpurl'=>'http://live.ketianyun.com/watch/2173624'
                'rtmpurl'=>$channeldata['play_url']
               ];
              }else{
                 $returndata=[
               'meetingkey'=>'',
               'meetingpassword'=>'',
               'hostpassword'=>'',
               'meetingname'=>$getdata['Title'],
               'starttime'=>date('Y-m-d H:i:s', strtotime($getdata['StartTimeUTC'])+3*60),
               'duration'=> (strtotime($getdata['EndTimeUTC'])-strtotime($getdata['StartTimeUTC'])+3*60)/60,
//               'starttime'=>date('Y-m-d H:i:s', strtotime($getdata['StartTimeUTC'])),
//               'duration'=> (strtotime($getdata['EndTimeUTC'])-strtotime($getdata['StartTimeUTC']))/60,
               'hostjoinurl'=>'',
               'guestjoinurl'=>'',
               'cmrurl'=>$cmrurl,
//               'rtmpurl'=>'http://live.ketianyun.com/watch/2173624'
                'rtmpurl'=>$channeldata['play_url']
               ]; 
              }
            
            
            $this->buildSuccess($returndata, '获取成功');
        }
    }
    
    /*
     * TMS会议列表
     */
    public function tmsGetMeetingList() {
         
        $this->responseurl="https://tms.ketiancloud.com/tms/external/booking/bookingservice.asmx";
        
//        if(stripslashes(input('post.ConferenceStatus'))=='Finished'){
//            $timetype=input('post.TimeType');
//            
//                switch ($timetype) {
//                    case 'Theredays':   //三天内
//                        $postdata['StartDate']=date('Y-m-d H:i:s', strtotime("-3 day")-date('Z')-15*60).'Z';
//                        $postdata['EndDate']=date('Y-m-d H:i:s', time()-date('Z')).'Z';
//
//                        break;
//                    case 'Aweek':   //一周内
//                        $postdata['StartDate']=date('Y-m-d H:i:s', strtotime("-1 week")-date('Z')-15*60).'Z';
//                        $postdata['EndDate']=date('Y-m-d H:i:s', time()-date('Z')).'Z';
//
//                        break;
//                    case 'Amonth':   //一个月内
//                        $postdata['StartDate']=date('Y-m-d H:i:s', strtotime("-1 month")-date('Z')-15*60).'Z';
//                        $postdata['EndDate']=date('Y-m-d H:i:s', time()-date('Z')).'Z';
//
//                        break;
//
//                    default:
//                        $postdata['StartDate']=date('Y-m-d H:i:s', strtotime("-3 day")-date('Z')-15*60).'Z';
//                        $postdata['EndDate']=date('Y-m-d H:i:s', time()-date('Z')).'Z';
//                        break;
//                }
//             $result=$this->tmsCurl($postdata,'GetConferencesForSystems');       
//             
//        }else{
//            $postdata=[
//            'ConferenceStatus'=>stripslashes(input('post.ConferenceStatus'))
//            ];
//        }
         $postdata=[
            'ConferenceStatus'=>stripslashes(input('post.ConferenceStatus'))
        ];
        $result=$this->tmsCurl($postdata,'GetConferencesForSystems');
        
        $status=$this->validclient($result);
        if(!$status){
            $result=$this->tmsCurl($postdata,'GetConferencesForSystems');
        }
        
        if(!empty($result['data']['GetConferencesForSystemsResponse']['GetConferencesForSystemsResult'])){
            $getdata=$result['data']['GetConferencesForSystemsResponse']['GetConferencesForSystemsResult']['Conference'];
        }else{
            $getdata=$result['data']['GetConferencesForSystemsResponse']['GetConferencesForSystemsResult'];
        }
        
        
        $listdata=[];
      
        if(!isset($getdata['ConferenceId'])){
        foreach ($getdata as $key => $value) {
            $listdata[$key]['starttime']=date('Y-m-d H:i:s', strtotime($value['StartTimeUTC'])+3*60);
//            $listdata[$key]['starttime']=date('Y-m-d H:i:s', strtotime($value['StartTimeUTC']));
            $listdata[$key]['stoptime']=date('Y-m-d H:i:s', strtotime($value['EndTimeUTC']));
            $listdata[$key]['meetingkey']=$value['ConferenceId'];
            $listdata[$key]['meetingname']=$value['Title'];
            $listdata[$key]['meetstatus']=$value['ConferenceState']['Status'];
            
         }
        }else{
            
            $listdata[0]['starttime']=date('Y-m-d H:i:s', strtotime($getdata['StartTimeUTC'])+3*60);
//            $listdata[0]['starttime']=date('Y-m-d H:i:s', strtotime($getdata['StartTimeUTC']));
            $listdata[0]['stoptime']=date('Y-m-d H:i:s', strtotime($getdata['EndTimeUTC']));
            $listdata[0]['meetingkey']=$getdata['ConferenceId'];
            $listdata[0]['meetingname']=$getdata['Title'];
            $listdata[0]['meetstatus']=$getdata['ConferenceState']['Status'];
        }
        $listdata=array_reverse($listdata,false);
        $this->buildSuccess($listdata, '获取成功');
    }


//    /*
//     * 删除会议
//     * 
//     */
//    public function deleteMeeting(){
//        $this->responseurl="java:com.webex.service.binding.meeting.DelMeeting";
//        $postdata=[
//            'meetingKey'=> input('post.meetingKey')
//        ];
//       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
//       
//       
//       $this->buildSuccess($getdata, '删除成功');
//       
//    }
      /*
     * 删除会议
     * 
     */
    public function deleteMeeting(){
        
        $this->responseurl="https://tms.ketiancloud.com/tms/external/booking/bookingservice.asmx";
        if(!input('post.meetingKey')){
            echo $this->buildFailed(-1, '缺少必要参数。',[],false); //表示未登录,或者登陆失效
                
            exit;
        }
        $postdata=[
            'ConferenceId'=>input('post.meetingKey')
        ];
        $result=$this->tmsCurl($postdata,'DeleteConferenceById');
        $status=$this->validclient($result);
        if(!$status){
            $result=$this->tmsCurl($postdata,'DeleteConferenceById');
        }
//        cache(input('post.meetingKey'),input('post.meetingKey'));
        //查找会议列表id
        $p_where=[
            'meeting_id'=>input('post.meetingKey'),
        ];
        $wedata=Db::connect('zbsql')->name('c_meeting')
                ->where($p_where)
                ->find();
        if(!empty($wedata)&&$wedata['call_work']==1){
               $d_where=[
                'id'=>$wedata['log_id']
               ];
               $u_data=[
                   'delete_time'=>time()
               ];
               
               Db::connect('zbsql')->name('pexip_log')
                       ->where($d_where)
                       ->update($u_data);
            $quedata=[
            'log_id'=>$wedata['log_id'],
            'type'=>2
            ];
            
            $result=Queue::push('app\common\jobs\PexipJob@sendlive',$quedata, $queue ='pexipjobs');
            $this->buildSuccess([], '删除成功');
          
        }else{
            echo $this->buildFailed(-1,'未找到该会议或使用了错误的客户端',[],false);
            exit;
        }
        
       
    }
    
    
    /*
     * 获取sessionTicket
     * 
     */
    
    public function getSessionTicket(){
        
        //先要获取tokenID
        $tokenheader=[
            'X-OpenAM-Username'=> $this->webexid,
            'X-OpenAM-Passwor'=> $this->password,
            'Content-Type'=>'application/json'
        ];
        $tokenurl="https://ci.ketianyun.com:8280/sso/json/authenticate"; 
        $result=httpRequest($tokenurl,'post', '',$tokenheader);
        $this->responseurl="java:com.webex.service.binding.user.AuthenticateUser";
        $postdata=[
            
        ];
       $getdata= $this->curlGetData($postdata,1,'post');   //调用webex接口获取到的数据
       
       
       $this->buildSuccess($getdata, '获取成功');
       
       
       
    }
    /*
     * webex curl请求webex获取数据
     * 
     */
    public function curlGetData($data,$type=0,$method='get') {
        $xml= $this->xmlEncode($data,$type);
        $header=[
            'Content-Type'=>'textml; charset=utf-8'
        ];
       
        if($type==0){
            $url=$this->requesturl;
        }else{
            $url=$this->ossurl;
        }
        if($method=='get'){  
            $result=httpRequest($url."?XML=".urlencode($xml), $method, [],$header);
            $result = str_replace(":","",$result);
        }elseif($method=='post'){
            $postdata=[
                'XML'=>$xml
            ];
            
            $result=httpRequest($url, $method, $postdata,$header);
        }else{
            echo $this->buildFailed(-1,'非法请求',[],false);
            exit;
        }
       
        
        
        $result= xmlToArray($result);
        
        //判断接口请求是否成功
        if(is_array($result)&&!empty($result)){
            
            if($result['servheader']['servresponse']['servresult']=='SUCCESS'){
                
                return $result['servbody']['servbodyContent'];
            }else{
                if($result['servheader']['servresponse']['servreason']=='Incorrect user or password'){
                    $this->needlogin(); //表示未登录,或者登陆失效
                
                   
                }elseif($result['servheader']['servresponse']['servreason']=='The site is not active'){
                    
                    echo $this->buildFailed(-1, '请确认站点是否启用',[],false);
                
                    exit;
                    
                }elseif($result['servheader']['servresponse']['servreason']=='Failed to get SiteUrl from server'){
                    
                    echo $this->buildFailed(-1, '无法从服务器获取该站点',[],false);
                
                    exit;
                    
                }elseif($result['servheader']['servresponse']['servreason']=='Meeting password must not be null'){
                    $data['accessControl']['meetingPassword']='1234';
                    $getdata=$this->curlGetData($data);
                    if(!empty($getdata['meetmeetingkey'])){   //拿到会议id查询主持人开会地址

                           $returndata=[
                             'meetingkey' => $getdata['meetmeetingkey']
                           ];
                           $this->buildSuccess($returndata, '创建成功');
                    }
                    exit;
                }else{
                    echo $this->buildFailed(-1, $result['servheader']['servresponse']['servreason'],[],false);
                
                    exit;
                }
                
            }
        }else{
           echo $this->buildFailed(-1,'请确认站点名称或网络连接。',[],false);
           exit;
        }
        
    }
    
    /*
     * TMS  curl请求
     */
    protected function tmsCurl($data,$apiname,$method='post'){
        //session('ClientSession',null);exit;
        $xml=$this->tmsXmlEncode($data,$apiname);
        //TMS账号密码设置头信息
        $channelid= $this->select_channerid;            
        $authstr= base64_encode($this->tmsconfig[$channelid]['tms_name'].":". $this->tmsconfig[$channelid]['tms_password']);
        $arr_header[] = "Content-Type:application/soap+xml";
        $arr_header[] = "Authorization: Basic ".$authstr; //添加头，在name和pass处填写对应账号密码
        $result=httpRequest($this->responseurl, $method, $xml,$arr_header);
        //禁止引用外部xml实体 
        $result=nxmlToArray($result);
        
        $result=$this->tmsReturn($result);
        if(isset($result['code'])){
            
           return $result;
            
        }
       
        
        
    }
    
   
    /*
     * TMS信息返回处理
     */
    protected function tmsReturn($data) {
        
        if(isset($data['body'])){
            echo $this->buildFailed(-1, '您的cmr账号密码需要跟webex账号密码同步',[],false);
                
            exit;
        }
        $body=$data['soap_Body'];
        if(isset($data['soap_Header'])){
            session('ClientSession', $data['soap_Header']['ExternalAPIVersionSoapHeader']['ClientSession']);
        }
        if(isset($body['soap_Fault'])){
            if($body['soap_Fault']['detail']['errorcode']=='-2147218253'){
                
                
                $returndata=[
                     'code'=>-1,  //代表客户端id失效
                     'data'=>$body['soap_Fault']['detail']['clientsessionid']
                ];
                return $returndata;
            }elseif ($body['soap_Fault']['detail']['errorcode']=='-2147218269') {
                echo $this->buildFailed(-1, '您的参数有误创建会议失败',[],false);
                
                exit;
            }
            
        }else{
            
                 $returndata=[
                     'code'=>1,  //代表成功返回数据
                     'data'=>$body
                ];
                return $returndata; 
        }
    }
    /*
     * TMS的xml转换
     * 
     */
    public function tmsXmlEncode($result=array(),$apiname) {    //数组,接口名称
        //$clientsession= session_id();
        if(!session('ClientSession')){
            $clientsession='7d2d51f4-d400-471e-8334-cf92b568e869';
        }else{
            $clientsession=session('ClientSession');
        }
        $xml='<?xml version="1.0" encoding="utf-8"?>';
        $xml.='<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
        $xml.='<soap12:Header>';
        $xml.='<ContextHeader xmlns="http://tandberg.net/2004/02/tms/external/booking/">';
        $xml.='<SendConfirmationMail>false</SendConfirmationMail>';   //会议邮件
        $xml.='<ExcludeConferenceInformation>false</ExcludeConferenceInformation>';
        $xml.='</ContextHeader>';
        $xml.='<ExternalAPIVersionSoapHeader xmlns="http://tandberg.net/2004/02/tms/external/booking/">';
        $xml.='<ClientVersionIn>15</ClientVersionIn>';
        $xml.='<ClientSession>'.$clientsession.'</ClientSession>';
        $xml.='</ExternalAPIVersionSoapHeader>';
        $xml.='</soap12:Header>';
        
        $xml.='<soap12:Body>';
        $xml.='<'.$apiname.' xmlns="http://tandberg.net/2004/02/tms/external/booking/">';
        $xml.= xmlToEncode($result);
        $xml.='</'.$apiname.'>';
        $xml.='</soap12:Body>';
        
        $xml.='</soap12:Envelope>';
        
        return $xml;
    }
    /*
     * 按xml方式输出通信数据
     */
    public function xmlEncode($result=array(),$type=0) {
        
        if($type==0){
            
       
        $xml="<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml.="<serv:message xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>\n";
        
        $xml.="<header>\n";
        $xml.="<securityContext>\n";
        $xml.="<siteName>".$this->sitename."</siteName>\n";
        $xml.="<webExID>".$this->webexid."</webExID>\n";
        $xml.="<password>".$this->password."</password>\n";
        $xml.="</securityContext>\n";
        $xml.="</header>\n";
        
        $xml.="<body>\n";
        $xml.="<bodyContent xsi:type='".$this->responseurl."'>\n";
        $xml.= xmlToEncode($result);
        $xml.="</bodyContent>\n";
        $xml.="</body>\n";
        
        $xml.="</serv:message>\n";
        
         }else{
                $xml="<?xml version='1.0' encoding='UTF-8'?>\n";
                $xml.="<serv:message xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:serv='http://www.webex.com/schemas/2002/06/service'>\n";

                $xml.="<header>\n";
                $xml.="<securityContext>\n";
                $xml.="<siteName>".$this->sitename."</siteName>\n";
                $xml.="<webExID>".$this->webexid."</webExID>\n";
                $xml.="</securityContext>\n";
                $xml.="</header>\n";

                $xml.="<body>\n";
                $xml.="<bodyContent xsi:type='".$this->responseurl."'>\n";
                $xml.= "<samlResponse>%samlResponse%</samlResponse>\n";
                $xml.="<protocol>SAML2.0</protocol>\n";
                $xml.="</bodyContent>\n";
                $xml.="</body>\n";

                $xml.="</serv:message>\n";
         }
        return $xml;
       
    }
    
   
    
}
