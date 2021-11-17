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
备注：以下所有库名都可以使用阿拉伯数字

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

+ **写入队列**

  ```
  // 插入集合：写入队列左边 并根据名称自动切换库
  \PhalApi\DI()->redis->set_Lpush(队列键名,值, 库名);
  
  //插入集合：写入队列右边 并根据名称自动切换库
  \PhalApi\DI()->redis->set_rPush(队列键名, 值, 库名);
  
  // 读取队列右边 如果没有读取到阻塞一定时间(阻塞时间或读取配置文件blocking的值)
  \PhalApi\DI()->redis->get_Brpop(队列键名,值, 库名);
  ```

+ **获取队列**

  ```
  // 读取队列左边
  \PhalApi\DI()->redis->get_lpop(队列键名, 库名);
  
  // 读取队列右边
  \PhalApi\DI()->redis->get_rPop(队列键名, 库名);
  ```

+ **获取指定位置**

  ```
  //返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）
  \PhalApi\DI()->redis->get_lRange(队列键名, $start, $end);
  ```

+ **截取指定位置**

  ```
  //截取名称为key的list，保留start至end之间的元素,end为 -1 ，返回所有
   \PhalApi\DI()->redis->get_lTrim(键名,$start, $end, 库名);
  ```

+ **获取key的生存时间**

  ```
  \PhalApi\DI()->redis->get_lTrim(键名, 库名);
  ```

+ **判断key是否存在**

  ```
  \PhalApi\DI()->redis->get_exists(键名, 库名);
  ```
  
+ **Hash：HDEL** 删除一个或多个哈希表字段

  ```
  \PhalApi\DI()->redis->HDEL($key, $field, $talbename);
  ```
  
  备注： 命令用于删除哈希表 key 中的一个或多个指定字段，不存在的字段将被忽略。
  
+ **Hash：HGETALL 获取在哈希表中指定 key 的所有字段和值**

  ```
  \PhalApi\DI()->redis->HGETALL($key, $talbename);
  ```
  
+ **Hash：HSET 将哈希表 key 中的字段 field 的值设为 value 。**

  ```
  \PhalApi\DI()->redis->HGETALL($key, $field, $value, $talbename);
  ```

  备注：Redis Hset 命令用于为哈希表中的字段赋值 。如果哈希表不存在，一个新的哈希表被创建并进行 HSET 操作。 如果字段已经存在于哈希表中，旧值将被覆盖。

  

+ **Hash：HSET 将哈希表 key 中的字段 field 的值设为 value 。**

  ```
  \PhalApi\DI()->redis->HGETALL($key, $field, $value, $talbename);
  ```

+ **Hash：HEXISTS 查看哈希表 key 中，指定的字段是否存在。**

  ```
  \PhalApi\DI()->redis->HGETALL($key, $field, $talbename);
  ```

+ **Hash：HMGET获取所有给定字段的值** 

  ```
  \PhalApi\DI()->redis->HMGET($key, $field, $talbename);
  ```

  备注：Redis Hmget 命令用于返回哈希表中，一个或多个给定字段的值。如果指定的字段不存在于哈希表，那么返回一个 nil 值。可以传数组

+ **Hash：HGET 获取指定字段值**

  ```
  \PhalApi\DI()->redis->HGET($key, $field, $talbename);
  ```

  备注：只传一个字段

+ **其他**

  ```
  // 删除一个键值队适用于所有
  \PhalApi\DI()->redis->del(键名, 库名);
  // 自动增长
  \PhalApi\DI()->redis->get_incr(键名, 库名);
  // 切换DB并且获得操作实例
  \PhalApi\DI()->redis->get_redis(键名, 库名);
  ```

