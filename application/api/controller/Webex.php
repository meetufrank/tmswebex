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
    private $tmsusername="ketiancloud\\api_admin";
    private $tmspassword="P@ss1234";
    private $responseurl;
    private $isSpecial=false;

    public function _initialize() {
       

        
        if(strtolower(Request::instance()->action())!='webexlogin' && strtolower(Request::instance()->action())!='getclientid'){
            
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
        }
        $this->requesturl="https://".$this->sitename.".webex.com.cn/WBXService/xml8.0.0/XMLService";
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
        
        $this->responseurl="java:com.webex.service.binding.user.GetUser";
        session('webexid',$webexid);
        session('password',$password);
        session('sitename',$sitename);
        $this->fieldSet();
        $postdata=[
            'webExId'=> $webexid
        ];
       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
 
       if(!empty($getdata)){
           session('userName',$getdata['usefirstName'].$getdata['uselastName']);
           $getdata=[];
           $this->buildSuccess($getdata, '登陆成功');
       }
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
        $this->validateMeeting($timedata);
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
                'ConferenceLanguage'=>'zh-CN',
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
            var_dump($result);exit;
        }
        if(!empty($result)){
            
                
            
            $data=$result['data']['SaveConferenceResponse']['SaveConferenceResult'];
            
            $returndata=[
             'meetingkey' => $data['ConferenceId']
            ];
            //加入sip呼叫队列
            $sipurl='/[1-9]\d*@ketiancloud.com/';
            preg_match($sipurl,$data['ConferenceInfoText'],$siparr);
            if(!empty($siparr)){
                $cmrurl=$siparr[0];
            }else{
                $cmrurl='';
            }
           
            $queuedata=[
                'startime'=>strtotime($data['StartTimeUTC'])+2*60,
                'meetingkey'=>$data['ConferenceId'],
//                'startime'=>strtotime($data['StartTimeUTC']),
                'sipurl'=>$cmrurl,
                'name'=>$data['Title'],
             
            ];
            
            Queue::push('app\common\jobs\WebexLive@sendlive', $queuedata, $queue ='webexlive');
            
            //加入rtmp呼叫队列
            $queuertmpdata=[
                'startime'=>strtotime($data['StartTimeUTC'])+2*60,
                'meetingkey'=>$data['ConferenceId'],
//                'startime'=>strtotime($data['StartTimeUTC']),
                'rtmpurl'=>'rtmp://pubsec.myun.tv/watch/1gba4y?auth_key=2082733261-0-0-e9c7cb4321521807f645465bc45729b3',
                'name'=>$data['Title']."|直播"
            ];
            
            Queue::push('app\common\jobs\WebexLiveR@sendlive', $queuertmpdata, $queue ='webexlive');
            
            //加入pexip队列
            $queuertmpdata=[
                'meetingkey'=>$data['ConferenceId'],
                'stoptime'=>strtotime($data['EndTimeUTC']),
                'id'=>time()
            ];
            
            Queue::push('app\common\jobs\PexipJob@sendlive', $queuertmpdata, $queue ='PexipJob');
            
            $this->buildSuccess($returndata, '创建成功');
        
        }
        
    }
    
    //验证会议是否冲突
    protected function validateMeeting($timedata) {
        $this->responseurl="https://tms.ketiancloud.com/tms/external/booking/bookingservice.asmx";
        $where=[
            'Pending',
            'Ongoing'
        ];
        foreach ($where as $key => $value) {
            
        
         $postdata=[
            'ConferenceStatus'=>stripslashes($value)
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
            if(strtotime($value['StartTimeUTC'])>$timedata['stoptime']||$timedata['startime']>strtotime($value['EndTimeUTC'])){
                return true;
            }else{
                echo $this->buildFailed(-1, '会议时段被占用，请重新选择时间。',[],false); 
                
                exit;
            }
            $listdata[$key]['starttime']=date('Y-m-d H:i:s', strtotime($value['StartTimeUTC'])+3*60);
            $listdata[$key]['stoptime']=date('Y-m-d H:i:s', strtotime($value['EndTimeUTC']));
            $listdata[$key]['meetingkey']=$value['ConferenceId'];
            $listdata[$key]['meetingname']=$value['Title'];
            $listdata[$key]['meetstatus']=$value['ConferenceState']['Status'];
            
         }
        }else{
            if(strtotime($getdata['StartTimeUTC'])>$timedata['stoptime']||$timedata['startime']>strtotime($getdata['EndTimeUTC'])){
                return true;
            }else{
                echo $this->buildFailed(-1, '会议时段被占用，请重新选择时间。',[],false); 
                
                exit;
            }
           
        }
        
        }
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
            $sipurl='/[1-9]\d*@ketiancloud.com/';
            preg_match($sipurl,$getdata['ConferenceInfoText'],$siparr);
            if(!empty($siparr)){
                $cmrurl=$siparr[0];
            }else{
                $cmrurl='';
            }
            
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
               'rtmpurl'=>'http://live.ketianyun.com/watch/2173624'
               ];
            
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
        cache(input('post.meetingKey'),input('post.meetingKey'));
        $quedata=[
            'timestamp'=>time()
        ];
        Queue::push('app\common\jobs\PexipJob@sendlive',$quedata, $queue ='PexipJob');
        $this->buildSuccess([], '删除成功');
       
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
                    
        $authstr= base64_encode($this->tmsusername.":". $this->tmspassword);
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
