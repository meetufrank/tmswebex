<?php
//return [
//    'connector' => 'redis',
//    'table' => 'manage_job'
//];
   return [
//       'connector'  => 'Redis',		    // Redis 驱动
//       'expire'     => null,				// 任务的过期时间，默认为60秒; 若要禁用，则设置为 null 
//       'default'    => 'apiadmin',		// 默认的队列名称
//       'host'       => '127.0.0.1',	    // redis 主机ip
//       'port'       => 6379,			// redis 端口
//       'password'   => '123456',				// redis 密码
//       'select'     => 0,				// 使用哪一个 db，默认为 db0
//       'timeout'    => 0,				// redis连接的超时时间
//       'persistent' => false,			// 是否是长连接
     
       'connector' => 'Database',   // 数据库驱动
       'expire'    => null,           // 任务的过期时间，默认为60秒; 若要禁用，则设置为 null
       'default'   => 'default',    // 默认的队列名称
       'table'     => 'manage_job',       // 存储消息的表名，不带前缀
       'dsn'       => [
           // 数据库类型
            'type'            => 'mysql',
            // 服务器地址
            'hostname'        => 'rm-bp19d3e41secoh6wao.mysql.rds.aliyuncs.com',
            // 数据库名
            'database'        => 'zbcms',
            // 用户名
            'username'        => 'zbcms',
            // 密码
            'password'        => 'Cisco123!@#',
            // 端口
            'hostport'        => '3306',
            // 连接dsn
            'dsn'             => '',
            // 数据库连接参数
            'params'          => [],
            // 数据库编码默认采用utf8
            'charset'         => 'utf8',
            // 数据库表前缀
            'prefix'          => 'clt_',
            // 数据库调试模式
            'debug'           => true,
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'          => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'     => false,
            // 读写分离后 主服务器数量
            'master_num'      => 1,
            // 指定从服务器序号
            'slave_no'        => '',
            // 是否严格检查字段是否存在
            'fields_strict'   => true,
            // 数据集返回类型
            'resultset_type'  => 'array',
            // 自动写入时间戳字段
            'auto_timestamp'  => false,
            // 时间字段取出后的默认时间格式
            'datetime_format' => 'Y-m-d H:i:s',
            // 是否需要进行SQL性能分析
            'sql_explain'     => false,
       ],

   //    'connector'   => 'Topthink',	// ThinkPHP内部的队列通知服务平台 ，本文不作介绍
   //    'token'       => '',
   //    'project_id'  => '',
   //    'protocol'    => 'https',
   //    'host'        => 'qns.topthink.com',
   //    'port'        => 443,
   //    'api_version' => 1,
   //    'max_retries' => 3,
   //    'default'     => 'default',

   //    'connector'   => 'Sync',		// Sync 驱动，该驱动的实际作用是取消消息队列，还原为同步执行
   ];