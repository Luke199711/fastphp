# fastphp

project                 WEB部署根目录
├─app                   应用目录
│  ├─controllers        控制器目录
│  ├─models             模块目录
│  ├─views              视图目录
├─config                配置文件目录
├─fastphp               框架核心目录
│ ├─base                MVC基类目录
│ ├─db                  数据库操作类目录
│ ├─Fastphp.php         内核文件  
├─static                静态文件目录
├─index.php             入口文件


in nginx 

location / {
    # 重新向所有非真是存在的请求到index.php
    try_files $uri $uri/ /index.php$args;
}

核心框架类主要
1、类自动加载
2、环境检查
3、过滤敏感字符
4、移除全局变量的老用法
5、路由处理