<?php
namespace app\common\logic;

use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;
class McuLogic extends Logic {
    
    protected $username;  //用户名
    protected $password;    //密码
    protected $api_domain;   //接口域
    protected $api_path;    //接口路径
    protected $api_port;    //接口端口
    protected $return_type;   //返回类型
    public function __construct($username,$password,$api_domin,$api_port,$api_path,$return_type='xml') {
        $this->username=$username;
        $this->password=$password;
        $this->api_domain=$api_domin;
        $this->api_port=$api_port;
        $this->api_path=$api_path;
        $this->return_type=$return_type;
        
    }
    
    
    /*
     * 请求mcu接口
     */
    public function send_msg($methodname,$params){
        $xmlrpc=new Request($methodname,$params);
        
        $params=$xmlrpc->serialize('UTF-8');
        //print_r($params);exit;
        /*** client side ***/
        $c = new Client($this->api_path,$this->api_domain,$this->api_port);

        // tell the client to return raw xml as response value
        $c->return_type = $this->return_type;
        $c->setCredentials($this->username, $this->password);  //设置用户验证
        // let the native xmlrpc extension take care of encoding request parameters
        $r = $c->send($params);

        return $this->response_msg($r);
        
        
    }
    
    /*
     * 返回消息
     */
   public function response_msg($r=array(),$msg=''){
       if ($r->faultCode()){ //失败时
//           return "An error occurred: ";
           return "Code: " . htmlspecialchars($r->faultCode()) . " Reason: '" . htmlspecialchars($r->faultString()) . "'\n";
        }else
        {
          
           
           $value = xmlrpc_decode($r->value());
           
           switch ($value) {
               case 0:

               return '成功';
                   break;
               case 1:

               return '其它错误';
                   break;
               case 2:

               return '资源不存在';
                   break;
               case 3:

               return '超出资源限制';
                   break;
               case 4:

               return '已经存在';
                   break;
               case 5:

               return '资源忙';
                   break;

               default:
                   
                return '意料之外的错误';
                   break;
           }
           
          
        }
   }
     /*
      * 开始会议
      */
    public function start_task($meetingname) {
        
        
        //创建xmlrpc格式参数
        $params=new Value(
                new Value($meetingname,'string')
                
               ,'string');
        
        
       $this->send_msg('start_task', $params);
        
     
    
    
    }
    
    /*
     * 添加sip终端和rtmp终端、拉流终端到mcu
     */
    public function add_terminals($meetname,$sipurl='',$rtmpurl='',$rtmp_l=''){  //会议室名称，sip地址，rtmp地址,rtmp拉流地址
               
        $sipobj=[];
        $rtmpobj=[];
        $rtmp_lobj=[];
        $rtmp_l_terminal_name='';
        $sip_terminal_name='';
        $rtmp_terminal_name='';
        if(!empty($sipurl)){
           //拆分sip地址
            $siparr=@explode('@',$sipurl,2);
            $sip_num=$siparr[0];
            $sip_domain=$siparr[1].';transport=tcp';

            //创建sip终端名称
            $sip_terminal_name= $this->get_terminal_name($meetname,'sip'); 
           
            //构造呼叫sip参数集
            $sipobj=new Value(
                                        array(
                                            '_name'=> new Value($sip_terminal_name,'string'),
                                            '_sortname' => new Value('','string'),
                                            '_bandwidth' => new Value(500000,'int'),
                                            '_interface_cat' => new Value('','string'),
                                            '_type' => new Value(3,'int'), //sip类型
                                            '_sip_option' => new Value(
                                                    array(
                                                        '_user'=>new Value($sip_num,'string'),
                                                        '_domain'=>new Value($sip_domain,'string')
                                                    )
                                                    ,'struct'),
                                            '_caps' => new Value(
                                                    array(
                                                       new Value(
                                                               array(
                                                                   '_type'=>new Value(0,'int'),
                                                                   '_codec'=>new Value(13,'int'),
                                                                   '_channel'=>new Value(1,'int')
                                                               )
                                                               ,'struct'), 
                                                       new Value(
                                                               array(
                                                                   '_type'=>new Value(1,'int'),
                                                                   '_codec'=>new Value(9,'int'),
                                                                   '_bitrate'=>new Value(0,'int'),
                                                                   '_fps'=>new Value(30,'int'),
                                                                   '_sizes'=>new Value('1280x720','string')
                                                               )
                                                               ,'struct'), 
                                                       new Value(
                                                               array(
                                                                   '_type'=>new Value(2,'int'),
                                                                   '_codec'=>new Value(9,'int'),
                                                                   '_bitrate'=>new Value(0,'int'),
                                                                   '_fps'=>new Value(30,'int'),
                                                                   '_sizes'=>new Value('1280x720','string')
                                                               )
                                                               ,'struct')
                                                    )
                                                    ,'array')
                                        ),'struct');
        }
        if(!empty($rtmpurl)){
            //创建rtmp终端名称
           $rtmp_terminal_name=$this->get_terminal_name($meetname,'rtmp'); ;
           //构造rtmp推流参数集
           $rtmpobj=new Value(
                                        array(
                                            '_name'=>new Value($rtmp_terminal_name,'string'),
                                            '_sortname'=>new Value('','string'),
                                            '_bandwidth'=>new Value(500000,'int'),
                                            '_interface_cat'=>new Value('','string'),
                                            '_type'=>new Value(5,'int'),
                                            '_rtmp_option'=>new Value(
                                                    array(
                                                        '_push_url0'=>new Value($rtmpurl,'string')
                                                    )
                                                    ,'struct'),
                                            '_caps'=>new Value(
                                                    array(
                                                        new Value(
                                                                array(
                                                                   '_type'=>new Value(0,'int'),
                                                                   '_codec'=>new Value(13,'int'),
                                                                   '_channel'=>new Value(1,'int')
                                                                )
                                                                ,'struct'),
                                                        new Value(
                                                                array(
                                                                   '_type'=>new Value(1,'int'),
                                                                   '_codec'=>new Value(9,'int'),
                                                                   '_bitrate'=>new Value(0,'int'),
                                                                   '_fps'=>new Value(30,'int'),
                                                                   '_sizes'=>new Value('1280x720','string')
                                                                )
                                                                ,'struct'),
                                                        new Value(
                                                                array(
                                                                   '_type'=>new Value(2,'int'),
                                                                   '_codec'=>new Value(9,'int'),
                                                                   '_bitrate'=>new Value(0,'int'),
                                                                   '_fps'=>new Value(30,'int'),
                                                                   '_sizes'=>new Value('1280x720','string')
                                                                )
                                                                ,'struct')
                                                    )
                                                    ,'array')
                                        )
                                        ,'struct');
        }
        if(!empty($rtmp_l)){
            //创建rtmp拉流终端名称
           $rtmp_l_terminal_name=$this->get_terminal_name($meetname,'rtmp_l'); ;
           //构造rtmp拉流参数集
           $rtmp_lobj=new Value(
                                        array(
                                            '_name'=>new Value($rtmp_l_terminal_name,'string'),
                                            '_sortname'=>new Value('','string'),
                                            '_bandwidth'=>new Value(50000,'int'),
                                            '_interface_cat'=>new Value('','string'),
                                            '_type'=>new Value(5,'int'),
                                            '_rtmp_option'=>new Value(
                                                    array(
                                                        '_pull_url'=>new Value($rtmp_l,'string')
                                                    )
                                                    ,'struct'),
                                            '_caps'=>new Value(
                                                    array(
                                                        new Value(
                                                                array(
                                                                   '_type'=>new Value(0,'int'),
                                                                   '_codec'=>new Value(13,'int'),
                                                                   '_channel'=>new Value(1,'int')
                                                                )
                                                                ,'struct'),
                                                        new Value(
                                                                array(
                                                                   '_type'=>new Value(1,'int'),
                                                                   '_codec'=>new Value(9,'int'),
                                                                   '_bitrate'=>new Value(0,'int'),
                                                                   '_fps'=>new Value(30,'int'),
                                                                   '_sizes'=>new Value('1280x720','string')
                                                                )
                                                                ,'struct'),
                                                        new Value(
                                                                array(
                                                                   '_type'=>new Value(2,'int'),
                                                                   '_codec'=>new Value(9,'int'),
                                                                   '_bitrate'=>new Value(0,'int'),
                                                                   '_fps'=>new Value(30,'int'),
                                                                   '_sizes'=>new Value('1280x720','string')
                                                                )
                                                                ,'struct')
                                                    )
                                                    ,'array')
                                        )
                                        ,'struct');
        }       
        $all_arr=[];
        $all_arr=array($sipobj,$rtmpobj,$rtmp_lobj);
        foreach ($all_arr as $key => $value) {
            if(empty($value)){
                unset($all_arr[$key]);
            }
        }
        
        
        
        //创建xml参数
        $params=new Value(
               
                    new Value(
                        array(
                            '_calls'=>new Value(
                            $all_arr
                            ,'array')
                        )
                        ,'struct')
                  
                ,'string');
        
       
        
        $this->send_msg('add_terminals', $params);
        
        //返回终端名称
        $result=[
            'rtmp_lname'=>$rtmp_l_terminal_name,
            'sipname'=>$sip_terminal_name,
            'rtmpname'=>$rtmp_terminal_name

        ];
        foreach ($result as $key => $value) {
            if(empty($value)){
                unset($result[$key]);
            }
        }
        
        return $result;
    }
    
    /*
     * 返回终端名称组合规则
     */
    public function get_terminal_name($meetname,$type='sip') {
        if($type=='sip'){
            return $meetname.'|sip';
        }elseif($type=='rtmp'){
             return $meetname.'|live';
        }elseif($type=='rtmp_l'){
             return $meetname.'|rtmp_l';  
         }else{
            return $meetname."|".$type;
        }
    }
    /*
     * 邀请终端到会议中
     * 
     */
    public function add_call($meetname,$terminal){  //会议室名称，终端名称
        
        
        //创建xml参数
        $params=new Value(
               array(
                   new Value($meetname,'string'),
                   new Value(
                           array(
                               '_name'=>new Value($terminal,'string')
                           )
                           ,'struct'),
               )
                ,'array');
        
        
        
        $this->send_msg('add_call', $params);
        
    }
    
    /*
     * 打开或关闭rtmp的双流状态
     */
    public function toggle_ext($meetname,$terminal,$status=1){   //会议室名称，终端名称,打开还是关闭（0关闭）
        //创建xml参数
        $params=new Value(
               array(
                   new Value($meetname,'string'),
                   new Value($terminal,'string'),
                   new Value($status,'int')
                  
               )
                ,'array');
        
        
        
        $this->send_msg('toggle_ext', $params);
    }
    
    /*
     * 结束会议
     */
    public function stop_task($meetname){   //会议室名称
        //创建xml参数
        $params=new Value(
               new Value($meetname,'string')
                ,'string');
        
        
        
        $this->send_msg('stop_task', $params);
    }
    
    /*
     * 删除终端
     */
    public function delete_terminal($terminal) {  //终端名称
        //创建xml参数
        $params=new Value(
               new Value($terminal,'string')
                ,'string');
        
        
        
        $this->send_msg('delete_terminal', $params);
    }

}
