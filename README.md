# 安装步骤

## 1、前言

PhalApi2-Redis 官方提供的有BUG，并且没有人维护，后来放到自己github和packagist下维护；有问题欢迎指正。

+ 官网地址：[http://www.phalapi.net/](http://www.phalapi.net/ "PhalApi官网")

+ GitHub HomePage：[https://github.com/xuepengdong/phalapiredis](https://github.com/xuepengdong/phalapiredis)
+ github Issues： [https://github.com/xuepengdong/phalapiredis/issues](https://github.com/xuepengdong/phalapiredis/issues)

## 2、安装：项目根目录composer.json添加

```
{
    "require": {
        "xuepengdong/phalapiredis": "dev-master"
    }
}
```

+ 执行composer update更新

## 3、惰性加载Redis配置：**/config/di.php** 

```
// 惰性加载Redis
$di->redis = function () {
    return new \Xuepengdong\Phalapiredis\Lite(\PhalApi\DI()->config->get("app.redis.servers"));
};
```

## 4、配置redis账号密码：/config/app.php
```
    /**
     * 扩展类库 - Redis扩展
     */
    'redis' => array(
        //Redis链接配置项
        'servers'  => array(
            'host'   => '127.0.0.1',        //Redis服务器地址
            'port'   => '6379',             //Redis端口号
            'prefix' => 'PhalApi_',         //Redis-key前缀
            'auth'   => 'phalapi',          //Redis链接密码
        ),
        // Redis分库对应关系操作时直接使用名称无需使用数字来切换Redis库
        'DB'       => array(
            'developers' => 1,
            'user'       => 2,
            'code'       => 3,
        ),
        //使用阻塞式读取队列时的等待时间单位/秒
        'blocking' => 5,
    ),

```

## 5、入门使用

具体API可以参考vendor\xuepengdong\phalapiredis\src\Lite.php

+ **永久键值队**

  ```
  // 存入永久的键值队
  \PhalApi\DI()->redis->set_forever(键名,值,库名);
  // 获取永久的键值队
  \PhalApi\DI()->redis->get_forever(键名, 库名);
  ```

+ **有时效性键值队**

  ```
  // 存入一个有时效性的键值队,默认600秒
  \PhalApi\DI()->redis->set_Time(键名,值,有效时间,库名);
  // 获取一个有时效性的键值队
  \PhalApi\DI()->redis->get_Time(键名, 库名);
  ```

+ **写入固定位置**

  ```
  // 写入队列左边
  \PhalApi\DI()->redis->set_Lpush(队列键名,值, 库名);
  // 读取队列右边
  \PhalApi\DI()->redis->get_lpop(队列键名, 库名);
  // 读取队列右边 如果没有读取到阻塞一定时间(阻塞时间或读取配置文件blocking的值)
  \PhalApi\DI()->redis->get_Brpop(队列键名,值, 库名);
  ```

+ **其他**

  ```
  // 删除一个键值队适用于所有
  \PhalApi\DI()->redis->del(键名, 库名);
  // 自动增长
  \PhalApi\DI()->redis->get_incr(键名, 库名);
  // 切换DB并且获得操作实例
  \PhalApi\DI()->redis->get_redis(键名, 库名);
  ```

