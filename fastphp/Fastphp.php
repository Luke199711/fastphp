<?php

namespace fastphp;

defined('CORE_PATH') or define('CORE_PATH', __DIR__);

/*
 * @Description: fastphp核心框架类
 * @version: V0.01
 * @Author: Luke
 * @Date: 2019-07-10 15:35:21
 * @LastEditors: Luke
 * @LastEditTime: 2019-07-11 17:38:31
 */
class Fastphp
{
    // 配置内容
    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 运行程序
     */
    public function run()
    {
        spl_autoload_register(array($this, 'loadClass')); // 类自动加载
        $this->setReporting();      // 设置错误报告级别
        $this->removeMagicQuotes(); // 删除魔术引导
        $this->unregisterGlobals(); // 移除全局变量的老用法
        $this->setDbConfig();       // 数据库配置
        $this->route();             // 路由处理
    }

    /**
     * 路由处理
     */
    public function route()
    {
        $controllerName = $this->config['defaultController'];
        $actionName     = $this->config['defaultAction'];
        $param          = array();

        $url = $_SERVER['REQUEST_URI'];
        // 清楚 ? 之后的内容
        $position = strpos($url, '?');
        $url = $position === false ? $url : substr($url, 0 , $position);
        // 删除前后的"/"
        $url = trim($url, '/');

        if ($url) {
            // 使用"/"分割字符串,并保存在数组中
            $urlArray = explode('/', $url);
            // 删除空的数组元素
            $urlArray = array_filter($urlArray);

            // 获取控制器名
            $controllerName = ucfirst($urlArray[0]);
            
            // 获取动作名
            array_shift($urlArray);
            $actionName = $urlArray ? $urlArray[0] : $actionName;

            // 获取URL参数
            array_shift($urlArray);
            $param = $urlArray ? $urlArray : array();
        }

        // 判断控制器和操作是否存在
        $controller = 'app\\controllers\\' . $controllerName . 'Controller';
        if (!class_exists($controller)) {
            exit($controller . '控制器不存在');
        }
        if (!method_exists($controller, $actionName)) {
            exit($actionName . '方法不存在');
        }

        // 如果控制器和操作名存在，则实例化控制器，因为控制器对象里面
        // 还会用到控制器名和操作名，所以实例化的时候把他们俩的名称也
        // 传进去。结合Controller基类一起看
        $dispatch = new $controller($controllerName, $actionName);

        // $dispatch保存控制器实例化后的对象，我们就可以调用它的方法，
        // 也可以像方法中传入参数，以下等同于：$dispatch->$actionName($param)
        call_user_func_array(array($dispatch, $actionName), $param);
    }

    /**
     * 设置错误报告级别 
     */
    public function setReporting()
    {
        // 判断是否开启调试模式
        if (APP_DEBUG === true) {
            // error_reporting(E_ALL); // 报告所有错误
            error_reporting(E_ALL & ~E_NOTICE); // 报告 E_NOTICE 之外的所有错误
            ini_set('display_errors', 'On'); // 开启错误回显
        } else {
            error_reporting(E_ALL & ~E_NOTICE); // 报告 E_NOTICE 之外的所有错误
            ini_set('display_errors', 'On'); // 开启错误回显 
            ini_set('log_errors', 'On'); // 将错误输出到日志文件中
        }
    }

    /**
     * 删除敏感字符(删除魔术引号带来的反斜线)
     * stripslashes 删除反斜杠
     */
    public function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value); 
        return $value;
    }

    /**
     * 检测敏感字符并删除
     */
    public function removeMagicQuotes()
    {
        // get_magic_quotes_gpc: 获取当前 magic_quotes_gpc 的配置选项设置 
        // 如果 magic_quotes_gpc 为关闭时返回 0，否则返回 1。在 PHP 5.4.O 起将始终返回 FALSE。

        // 魔术引号指令：magic_quotes_gpc, 影响到 HTTP 请求数据（GET，POST 和 COOKIE）。不能在运行时改变。在 PHP 中默认值为 on
        // 魔术引号 :本特性已自 PHP 5.3.0 起废弃并将自 PHP 5.4.0 起移除。
        if (get_magic_quotes_gpc()) {
            $_GET     = isset($_GET) ? $this->stripSlashesDeep($_GET) : '';
            $_POST    = isset($_POST) ? $this->stripSlashesDeep($_POST) : '';
            $_COOKIE  = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }

    /** 
     * 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果
     * 已经弃用的 register_globals 指令被设置为 on，那么局部变量也将
     * 在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的
     * 形式存在，这样写是不好的实现，会影响代码中的其他变量。 相关信息，
     * 参考: http://php.net/manual/zh/faq.using.php#faq.register-globals
     */ 
    public function unregisterGlobals()
    {
        if (ini_get('register_globals')) {

            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', 
                '_REQUEST', '_SERVER', '_ENV', '_FILES');

            foreach ($array as $value) {
                foreach($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    /**
     * 配置数据库信息
     */
    public function setDbConfig()
    {
        if ($this->config['db']) {
            define('DB_HOST', $this->config['db']['host']);
            define('DB_NAME', $this->config['db']['dbname']);
            define('DB_USER', $this->config['db']['username']);
            define('DB_PASS', $this->config['db']['password']);
        }
    }

    /**
     * 自动加载类
     */
    public function loadClass($className)
    {
        $classMap = $this->classMap();

        if (isset($classMap[$className])) {
            // 包含内核文件
            $file = $classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            // 包含应用(application目录)文件
            $file = APP_PATH . str_replace('\\', '/', $className) . '.php';
            if (!is_file($file)) {
                return;
            }
        } else {
            return;
        }

        include $file;

        // 这里可以加入判断，如果名为$className的类、接口或者性状不存在，则在调试模式下抛出错误
    }

    /**
     * 内核文件命名空间映射关系
     */
    protected function classMap()
    {
        return [
            'fastphp\base\Controller' => CORE_PATH . '/base/Controller.php',
            'fastphp\base\Model'      => CORE_PATH . '/base/Model.php',
            'fastphp\base\View'       => CORE_PATH . '/base/View.php',
            'fastphp\db\Db'           => CORE_PATH . '/db/Db.php',
            'fastphp\db\Sql'          => CORE_PATH . '/db/Sql.php',
        ];
    }
}

