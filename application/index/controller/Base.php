<?php
/**
>
 */

namespace app\index\controller;



use think\Controller;

class Base extends Controller {


      private $debug = [];
   


    public function buildSuccess($data, $msg = '操作成功', $code = ReturnCode::SUCCESS) {
        $return = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        if ($this->debug) {
            $return['debug'] = $this->debug;
        }
        ob_clean();
        echo json_encode($return);
        exit;
    }

    public function buildFailed($code, $msg, $data = []) {
        $return = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        if ($this->debug) {
            $return['debug'] = $this->debug;
        }
       ob_clean();
       echo json_encode($return);
       exit;
    }
    
    
    protected function debug($data) {
        if ($data) {
            $this->debug[] = $data;
        }
    }

}