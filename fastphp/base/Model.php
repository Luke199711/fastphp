<?php

namespace fastphp\base;

use fastphp\db\Sql;

/*
 * @Description: 模型基类
 * @version: V0.01
 * @Author: Luke
 * @Date: 2019-07-11 15:29:46
 * @LastEditors: Luke
 * @LastEditTime: 2019-07-11 15:46:36
 */
class Model extends Sql
{
    protected $model;

    public function __construct()
    {
        // 获取数据库表名
        if (!$this->table) {

            // 获取模型类名称
            $this->model = get_class($this);

            // 删除类名最后的Model字符
            $this->model = substr($this->model, 0, -5);

            // 数据库表名与类名一致
            $this->table = strtolower($this->model);
        }
    }
}