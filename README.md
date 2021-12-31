# 安装步骤

## 1、前言

基于PhalApi2-Redis 官方做了一些优化和新增，有问题欢迎指正；欢迎pull request

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

+ **永久键值**

  + 存入永久的单个键值

    ```
    \PhalApi\DI()->redis->set_forever(键名, 值, 库名);
    ```

  + 获取单个永久的键值

    ```
    \PhalApi\DI()->redis->get_forever(键名, 库名);
    ```

  + 返回原来key中的值，并将新的value重新写入key

    ```
    \PhalApi\DI()->redis->get_getSet(键名, 值, 库名);
    ```

  + 给key后面加上value

    ```
    \PhalApi\DI()->redis->set_append(键名 , 值, 库名);
    ```

  + 返回key 的长度

    ```
    \PhalApi\DI()->redis->get_strlen('key1' , 'users');
    ```

  + 存入永久的多个键值

    ```
    \PhalApi\DI()->redis->set_list(数组, 库名);
    例子：\PhalApi\DI()->redis->set_list(array('key0' => 'value0', 'key1' => 'value1'), 'users');
    ```

  + 获取多个键值

    ```
    \PhalApi\DI()->redis->get_list(数组, 库名);
    例子：\PhalApi\DI()->redis->get_list(array('key0' , 'key1'), 'users');
    ```

  + 判断key是否存在

    ```
    \PhalApi\DI()->redis->get_exists(键名, 库名);
    ```

  +  删除一个键值队适用于所有

    ```
    \PhalApi\DI()->redis->del(键名, 库名);
    ```

  +  切换DB并且获得操作实例

    ```
    \PhalApi\DI()->redis->get_redis(键名, 库名);
    ```

    

+ **有时效键值**

  + 存入一个有时效性的键值队,默认600秒
  
    ```
    \PhalApi\DI()->redis->set_Time(键名,值,有效时间,库名);
    ```
  
  + 修改值，不改变失效时间
  
    ```
    \PhalApi\DI()->redis->save_Time(键名,新的值,库名);
    ```
  
  + 获取一个有时效性的键值
  
    ```
    \PhalApi\DI()->redis->get_Time(键名, 库名);
    ```
  
  + 获取一个key的失效时间，-1：持久化；-2：不存在；其他：失效时间秒
  
    ```
    \PhalApi\DI()->redis->get_time_ttl(键名, 库名);
    ```
  
    
  
+ **队列**

  + 插入集合：写入队列左边 并根据名称自动切换库
  
    ```
    \PhalApi\DI()->redis->set_Lpush(队列键名,值, 库名);
    ```
  
  + 插入集合：写入队列左边 如果value已经存在，则不添加 并根据名称自动切换库
  
    ```
     \PhalApi\DI()->redis->set_lPushx(队列键名, 值, 库名);
    ```
  
  +  读取队列左边
  
    ```
    \PhalApi\DI()->redis->get_lpop(队列键名, 库名);
    ```
  
  + 读取队列左边 如果没有读取到阻塞一定时间 并根据名称自动切换库
  
    ```
    \PhalApi\DI()->redis->get_blPop(队列键名,值, 库名);
    ```
  
  + 插入集合：写入队列右边 并根据名称自动切换库
  
    ```
    \PhalApi\DI()->redis->set_rPush(队列键名, 值, 库名);
    ```
  
  + 写入队列右边 如果value已经存在，则不添加 并根据名称自动切换库
  
    ```
    \PhalApi\DI()->redis->set_rPushx(队列键名, 值, 库名);
    ```
  
  + 读取队列右边
  
    ```
    \PhalApi\DI()->redis->get_rPop(队列键名, 库名);
    ```
  
  + 读取队列右边 如果没有读取到阻塞一定时间 并根据名称自动切换库
  
    ```
    \PhalApi\DI()->redis->get_brPop(队列键名,值, 库名);
    ```
  
  + 读取list有多少个元素
  
    ```
    \PhalApi\DI()->redis->get_lSize(队列键名, 库名);
    ```
  
  + 从左数设置list中指定位置为新的值
  
    ```
    \PhalApi\DI()->redis->get_lSize(队列键名, 位置, 值, 库名);
    ```
  
  + 获取指定位置 返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）
  
    ```
    \PhalApi\DI()->redis->get_lRange(队列键名, $start, $end);
    ```
  
  + 截取指定位置 截取名称为key的list，保留start至end之间的元素,end为 -1 ，返回所有
  
    ```
    \PhalApi\DI()->redis->get_lTrim(键名,$start, $end, 库名);
    ```
    
    
  
+ **Hash 类型**

  + **HSET：** 将哈希表 key 中的字段 field 的值设为 value 。

    ```
    \PhalApi\DI()->redis->HSET($key, $field, $value, $tablename);
    例子：\PhalApi\DI()->redis->HSET('hash1', 'field2', 'value2', 1);
    ```

  + **Hdel：** 命令用于删除哈希表 key 中的一个或多个指定字段，不存在的字段将被忽略。

    ```
    \PhalApi\DI()->redis->HDEL($key, $field, $tablename);
    ```

  + **HGETALL：** 获取在哈希表中指定 key 的所有字段和值

    ```
    \PhalApi\DI()->redis->HGETALL($key, $tablename);
    备注：Redis Hset 命令用于为哈希表中的字段赋值 。如果哈希表不存在，一个新的哈希表被创建并进行 HSET 操作。 如果字段已经存在于哈希表中，旧值将被覆盖。
    ```
  + **HEXISTS ：**查看哈希表 key 中，指定的字段是否存在。

    ```
    \PhalApi\DI()->redis->HEXISTS($key, $field, $tablename);
    ```

  + **HMGET：**获取所有给定字段的值

    ```
    \PhalApi\DI()->redis->HMGET($key, $field, $tablename);
    备注：Redis Hmget 命令用于返回哈希表中，一个或多个给定字段的值。如果指定的字段不存在于哈希表，那么返回一个 nil 值。可以传数组
    ```

  + **HGET：** 获取指定字段值

    ```
    \PhalApi\DI()->redis->HGET($key, $field, $tablename);
    备注：只传一个字段
    ```

+ **计数器：**

  + **counter_forever：**永久计数器，回调当前计数：

    ```
    \PhalApi\DI()->redis->counter_forever($key, $tablename);
    ```

    


+ **公用：**

  + 返回key的类型值：

    ```
    \PhalApi\DI()->redis->type($key, $tablename);
    ```

  + 查看现在数据库有多少key

    ```
    \PhalApi\DI()->redis->dbSize($tablename);
    ```

  + 转移一个key到另外一个数据库

    ```
    \PhalApi\DI()->redis->move($key, $tablename);
    ```

  + 给key重命名

    ```
    \PhalApi\DI()->redis->rename($OLD_KEY_NAME, $NEW_KEY_NAME);
    ```

  +  给key重命名 如果重新命名的名字已经存在，不会替换成功

    ```
    \PhalApi\DI()->redis->renameNx($OLD_KEY_NAME, $NEW_KEY_NAME);
    ```

  + 删除键值 并根据名称自动切换库(对所有通用)

    ```
    \PhalApi\DI()->redis->del($key);
    ```

  + 返回redis的版本信息等详情

    ```
    \PhalApi\DI()->redis->info();
    ```

  + 查看redis链接状态

    ```
    \PhalApi\DI()->redis->ping();
    ```

  + 切换DB

    ```
    \PhalApi\DI()->redis->switchDB();
    ```

  + 清空当前数据库

    ```
    \PhalApi\DI()->redis->flushDB();
    ```

  + 清空所有数据库

    ```
    \PhalApi\DI()->redis->flushAll();
    ```

  + 选择从服务器

    ```
    \PhalApi\DI()->redis->slaveof ($host, $port);
    ```

  + 将数据同步保存到磁盘

    ```
    \PhalApi\DI()->redis->save();
    ```

  + 将数据异步保存到磁盘

    ```
    \PhalApi\DI()->redis->bgsave();
    ```

  + 返回上次成功将数据保存到磁盘的Unix时戳

    ```
    \PhalApi\DI()->redis->lastSave();
    ```

  + 使用aof来进行数据库持久化

    ```
    \PhalApi\DI()->redis->bgrewriteaof();
    ```

    

  

