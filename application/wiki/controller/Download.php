<?php
/**
 *
 * @since   2017/07/27 创建
 * @author  zhaoxiang <zhaoxiang051405@gmail.com>
 */

namespace app\wiki\controller;


use app\model\AdminApp;
use app\model\AdminFields;
use app\model\AdminGroup;
use app\model\AdminList;
use app\util\DataType;
use app\util\ReturnCode;
use app\util\Tools;

class Download extends \think\Controller {

   
    public function index() {
        
    }
    
    
    public function webexdownload() {
       
        
        $filepath=DOWNLOAD_PATH.'webex/webex.apk.1.1';
        $filename='1.apk';
        downloadFile($filepath, $filename);
    } 



}
