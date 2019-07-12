<?php
/*
 * @Description: 配置文件
 * @version: V0.0.1
 * @Author: Luke
 * @Date: 2019-07-10 15:29:46
 * @LastEditors: Luke
 * @LastEditTime: 2019-07-10 15:34:15
 */

// 数据库配置
$config['db']['host'] = 'localhost';
$config['db']['username'] = 'root';
$config['db']['password'] = 'root';
$config['db']['dbname'] = 'project';

// 默认控制器和操作名
$config['defaultController'] = 'Item';
$config['defaultAction'] = 'index';

return $config;