<?php
namespace app\api\controller;

use think\Db;
use think\Request;
class Webex extends Base {
    
    private $requesturl;
    private $ossurl;
    private $sitename;
    private $webexid;
    private $password;
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
     * 创建用户
     */
    public function CreateUser() {
        $this->responseurl="java:com.webex.service.binding.user.CreateUser";
        $postdata=[
            'firstName'=> 'jin',
            'lastName'=> 'wang',
            'webExId'=> 'wangjin',
            'email'=> '972270516@qq.com',
            'password'=> '123456',
            'active'=> 'ACTIVATED',
            'privilege'=>[
                'host'=>true
            ]
        ];
        
        $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
       
       $this->buildSuccess($getdata, '创建成功');
        
        
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
           $getdata=[];
           $this->buildSuccess($getdata, '登陆成功');
       }
    }
    /*
     * 创建会议
     */
    public function CreateMeeting(){
        $this->responseurl="java:com.webex.service.binding.meeting.CreateMeeting";
        
        $postdata=[
            'accessControl'=>[
                'meetingPassword'=> input('post.meetingPassword')
            ],
            'metaData'=>[
                'confName'=>input('post.meetingName'),   //会议名称
                'agenda'=>input('post.meetingName')  //议程
            ],
            'schedule'=>[
                'startDate'=>date('n/j/Y H:i:s', strtotime(input('post.meetingStart'))),
                //'duration'=>ceil((strtotime(input('post.meetingStop'))-strtotime(input('post.meetingStart')))%86400/60)  //会议时间 单位（20分钟）
                'duration'=> intval(input('post.meetduration')) //会议时间 单位（20分钟）
                
            ]
        ];
        //当不勾选cmr和直播时
       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
       if(!empty($getdata['meetmeetingkey'])){   //拿到会议id查询主持人开会地址
           
           
           $returndata=[
             'meetingkey' => $getdata['meetmeetingkey']
           ];
           $this->buildSuccess($returndata, '创建成功');
       }
       
       
       
    }
    
    
    /*
     * TMS预约webex
     */
    public function tmsCreatMeet() {
        $postdata=[
            'Conference'=>[
                'ConferenceId'=>'-1',
                'Title'=>input('post.meetingName'),
                'StartTimeUTC'=>date('Y-m-d H:i:s', strtotime(input('post.meetingStart'))-date('Z')).'Z',
                'EndTimeUTC'=>date('Y-m-d H:i:s', strtotime(input('post.meetingStart'))+input('post.meetduration')-date('Z')).'Z',
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
                        'JoinBeforeHostTime'=>'00:05:00',
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
        $this->tmsCurl($postdata,'SaveConference');
        
    }
    /*
     * 获取加会地址
     * 
     */
    private function getJoinAddress($meetingkey) {
   
           $this->responseurl="java:com.webex.service.binding.meeting.GethosturlMeeting";
           $postdata=[
               'meetingKey'=>$meetingkey
           ];
           
           $getdata=$this->curlGetData($postdata);   //调用webex接口获取到的数据
           $returndata['meethostMeetingUrl']= str_replace('https//','https://',$getdata['meethostMeetingURL']);
           
           
           //会议ID获取加会地址
           $this->responseurl="java:com.webex.service.binding.meeting.GetjoinurlMeeting";
           $postdata=[
               'meetingKey'=>$meetingkey
           ];
           
           $getdata=$this->curlGetData($postdata);   //调用webex接口获取到的数据
           $returndata['joinMeetingUrl']= str_replace('https//','https://',$getdata['meetjoinMeetingURL']);
           
           return $returndata;
    }
    /*
     * 获取会议列表
     * 
     */
    public function getMeetingList(){
        $this->responseurl="java:com.webex.service.binding.meeting.LstsummaryMeeting";
        $maximumNum=100;  //每页十条
        $page=input('post.page');
        //过滤页数参数
        if (empty($page)||!is_numeric($page)||strpos($page, '.')) {
        
            $page=1;
        }
        
        //整理条件
        $postdata=[
            'listControl'=>[
                'startFrom'=>$maximumNum*($page-1)+1,
                'maximumNum'=>$maximumNum,
                'listMethod'=>'OR'
            ],
            'order'=>[
                'orderBy'=>'STARTTIME',
                'orderAD'=>'DESC'
            ]
        ];
        $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
        
        $listdata=[];

        if(arrayLevel($getdata['meetmeeting'])>1){
            
        
        foreach ($getdata['meetmeeting'] as $key => $value) {
            $listdata[$key]['starttime']=date('Y-m-d H:i:s', strtotime($value['meetstartDate']));
            $listdata[$key]['stoptime']=date('Y-m-d H:i:s', strtotime($value['meetstartDate'])+$value['meetduration']*60);
            $listdata[$key]['meetingkey']=$value['meetmeetingKey'];
            $listdata[$key]['meetingname']=$value['meetconfName'];
            $listdata[$key]['meetstatus']=$value['meetstatus'];
            $listdata[$key]['meethostJoined']=$value['meethostJoined'];
            $listdata[$key]['meetparticipantsJoined']=$value['meetparticipantsJoined'];
            $listdata[$key]['meettelePresence']=$value['meettelePresence'];
            $listdata[$key]['meetlistStatus']=$value['meetlistStatus'];

            if($value['meetstatus']=='INPROGRESS'){
                $listdata[$key]['meetstatus']='进行中';
                $listdata[$key]['meetstatusnum']=1;
            }elseif(($value['meetstatus']=='NOT_INPROGRESS'&&time()<strtotime($value['meetstartDate'])) ||($value['meetstatus']=='NOT_INPROGRESS'&&(time()>strtotime($value['meetstartDate'])&&time()<strtotime($listdata[$key]['stoptime'])))){
                
                $listdata[$key]['meetstatus']='未开始';
                $listdata[$key]['meetstatusnum']=0;
            }elseif($value['meetstatus']=='NOT_INPROGRESS'&&time()>strtotime($listdata[$key]['stoptime'])){
                $listdata[$key]['meetstatus']='已结束';
                $listdata[$key]['meetstatusnum']=-1;
            }else{
                $listdata[$key]['meetstatus']='已过期';
                $listdata[$key]['meetstatusnum']=-1;
            }
        }
        }else{
            $listdata[0]['starttime']=date('Y-m-d H:i:s', strtotime($getdata['meetmeeting']['meetstartDate']));
            $listdata[0]['stoptime']=date('Y-m-d H:i:s', strtotime($getdata['meetmeeting']['meetstartDate'])+$getdata['meetmeeting']['meetduration']*60);
            $listdata[0]['meetingkey']=$getdata['meetmeeting']['meetmeetingKey'];
            $listdata[0]['meetingname']=$getdata['meetmeeting']['meetconfName'];
            $listdata[0]['meetstatus']=$getdata['meetmeeting']['meetstatus'];
            $listdata[0]['meethostJoined']=$getdata['meetmeeting']['meethostJoined'];
            $listdata[0]['meetparticipantsJoined']=$getdata['meetmeeting']['meetparticipantsJoined'];
            $listdata[0]['meettelePresence']=$getdata['meetmeeting']['meettelePresence'];
            $listdata[0]['meetlistStatus']=$getdata['meetmeeting']['meetlistStatus'];
            if($getdata['meetmeeting']['meetstatus']=='INPROGRESS'){
                $listdata[0]['meetstatus']='进行中';
                $listdata[$key]['meetstatusnum']=1;
            }elseif(($value['meetstatus']=='NOT_INPROGRESS'&&time()<strtotime($value['meetstartDate'])) ||($value['meetstatus']=='NOT_INPROGRESS'&&(time()>strtotime($value['meetstartDate'])&&time()<strtotime($listdata[$key]['stoptime'])))){
                $listdata[0]['meetstatus']='未开始';
                $listdata[$key]['meetstatusnum']=0;
            }elseif($getdata['meetmeeting']['meetstatus']=='NOT_INPROGRESS'&&time()>strtotime($listdata[$key]['stoptime'])){
                $listdata[0]['meetstatus']='已结束';
                $listdata[$key]['meetstatusnum']=-1;
            }else{
                $listdata[0]['meetstatus']='已过期';
                $listdata[$key]['meetstatusnum']=-1;
            }
        }
       
        $this->buildSuccess($listdata, '获取列表成功');
    }
    /*
     * 获取会议信息
     */
    public function getMeetingData(){
        $this->responseurl="java:com.webex.service.binding.meeting.GetMeeting";
        $postdata=[
            'meetingKey'=> input('post.meetingKey')
        ];
       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
       $meetingkey=$getdata['meetmeetingkey'];
       $meeturl=$this->getJoinAddress($meetingkey);
       
       $returndata=[
           'meetingkey'=>$getdata['meetmeetingkey'],
           'meetingpassword'=>$getdata['meetaccessControl']['meetmeetingPassword'],
           'hostpassword'=>$getdata['meethostKey'],
           'meetingname'=>$getdata['meetmetaData']['meetconfName'],
           'starttime'=>date('Y-m-d H:i:s', strtotime($getdata['meetschedule']['meetstartDate'])),
           'duration'=>$getdata['meetschedule']['meetduration'],
           'hostjoinurl'=>$meeturl['meethostMeetingUrl'],
           'guestjoinurl'=>$meeturl['joinMeetingUrl']
       ];
       $this->buildSuccess($returndata, '获取成功');
       
    }
    /*
     * 删除会议
     * 
     */
    public function deleteMeeting(){
        $this->responseurl="java:com.webex.service.binding.meeting.DelMeeting";
        $postdata=[
            'meetingKey'=> input('post.meetingKey')
        ];
       $getdata= $this->curlGetData($postdata);   //调用webex接口获取到的数据
       
       
       $this->buildSuccess($getdata, '删除成功');
       
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
    public function tmsCurl($data,$apiname,$method='post'){
        
        $xml=$this->tmsXmlEncode($data,$apiname);
        $header=[
            'Content-Type'=>'application/soap+xml',
            'Authorization'=>'Basic dGVzdDE6UEBzczEyMzQ='
        ];
        httpRequest($url, $method, $postdata,$header);
        print_r($xml);exit;
    }
    /*
     * TMS的xml转换
     * 
     */
    public function tmsXmlEncode($result=array(),$apiname) {    //数组,接口名称
        if(!session('ClientSession')){
            $clientsession='b7f55bd8-440c-420d-948d-522542a36df5';
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
