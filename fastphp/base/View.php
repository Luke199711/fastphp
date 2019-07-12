<?php

namespace fastphp\base;

/*
 * @Description: 视图基类
 * @version: V0.01
 * @Author: Luke
 * @Date: 2019-07-11 15:29:53
 * @LastEditors: Luke
 * @LastEditTime: 2019-07-11 17:47:36
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;

    function __construct($controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action     = strtolower($action);
    }

    /**
     * 分配变量
     */
    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /**
     * 渲染显示
     */
    public function render()
    {
        extract($this->variables); // 该函数使用数组键名作为变量名，使用数组键值作为变量值。
        $defaultHeader = APP_PATH . 'app/views/header.php';
        $defaultFooter = APP_PATH . 'app/views/footer.php';

        $controllerHeader = APP_PATH . 'app/views/' . $this->_controller . '/header.php';
        $controllerFooter = APP_PATH . 'app/views/' . $this->_controller . '/footer.php';
        $controllerLayout = APP_PATH . 'app/views/' . $this->_controller . '/' . $this->_action . '.php';

        // 页头文件
        if (is_file($controllerHeader)) {
            include ($controllerHeader);
        } else {
            include ($defaultHeader);
        }

        // 判断视图文件是否存在
        if (is_file($controllerLayout)) {
            include ($controllerLayout);
        } else {
            echo "<h1>无法找到视图文件</h1>";
        }

        // 页脚文件
        if (is_file($controllerFooter)) {
            include ($controllerFooter);
        } else {
            include ($defaultFooter);
        }
    }
}