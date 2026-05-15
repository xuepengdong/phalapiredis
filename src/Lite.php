<?php
namespace Xuepengdong\Phalapiredis;

use \PhalApi\Cache\RedisCache;
/**
 * PhalApi2-Redis 拓展类
 * @author: 疏雨滴梧桐 <260288701@qq.com> 2021-12-31
 * @Maintenance: 喵了个咪 <wenzhenxi@vip.qq.com> 2017-08-19
 * @Maintenance: Axios <axioscros@aliyun.com> 于 2016-09-01 协助维护

 *
 */
class Lite extends RedisCache {

    private $db_old;

    /**
     * 重载方法，统一切换DB
     *
     * @param $name
     * @param $arguments
     * @return mixed
     *
     * @author: Axios <axioscros@aliyun.com> 2016-09-01
     */
    public function __call($name, $arguments)
    {
        $last = count($arguments)-1;
        $dbname = $arguments[$last];
        $this->switchDB($dbname);
        unset($arguments[$last]);
        $arguments = empty($arguments)? array():$arguments;
        return call_user_func_array(array($this,$name),$arguments);
    }

    /**
     * @descr: 获取Redis实例，当封装的方法未能满足时，可调用此接口获取Reids实例进行操作
     * @date: 2022/08/06 10:59
     * @example         $getRedis =  \PhalApi\DI()->redis->getRedis();
     * @example         $getRedis->select(1);
     * @example         return $getRedis->set('key20220806', 'value20220806', 20000);
     * @return bool
     */
    public function getRedis(){
        return $this->redis;
    }
    //---------------------------------------------------string类型-------------------------------------------------
    /**
     * @descr:将value 的值赋值给key,生存时间为永久 并根据名称自动切换库
     * @date: 2022/3/17 11:42
     * @example \PhalApi\DI()->redis->set_forever($key, $value, 1);
     * @param $key
     * @param $value
     * @return bool
     */
    protected function set_forever($key, $value){
        return $this->redis->set($this->formatKey($key), $this->formatValue($value));
    }

    /**
     * @descr:获取value 并根据名称自动切换库
     * @date: 2022/3/17 11:42
     * @example \PhalApi\DI()->redis->get_forever($key, 1);
     * @param $key
     * @return mixed|null
     */
    protected function get_forever($key){
        $value = $this->redis->get($this->formatKey($key));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:存入一个有实效性的键值队
     * @date: 2022/3/17 11:42
     * @example \PhalApi\DI()->redis->set_time($key, $value, 3600, 1); 存入到1 redis数据库中，过期时间3600秒
     * @param $key
     * @param $value
     * @param int $expire
     * @return bool
     */
    protected function set_time($key, $value, $expire = 600){
        return $this->redis->setex($this->formatKey($key), $expire, $this->formatValue($value));
    }

    protected function save_time($key, $value)
    {
        if($this->get_exists($key)){
            $ttl  = $this->get_time_ttl($key);
            return $this->set_time($key,$value,$ttl);
        }

        return NULL;
    }

    /**
     * @descr:统一 get/set方法，对于set_time使用get_time
     * @example \PhalApi\DI()->redis->get_time($key, 1);
     * @date: 2022/3/17 11:46
     * @param $key
     * @return mixed|null
     */
    protected function get_time($key){
        $value = $this->redis->get($this->formatKey($key));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:得到一个key的生存时间
     * @date: 2022/3/17 11:48
     * @example \PhalApi\DI()->redis->get_time_ttl($key, 1);
     * @param $key
     * @return bool|int|null
     */
    protected function get_time_ttl($key){
        $value = $this->redis->ttl($this->formatKey($key));
        return $value !== FALSE ? $value : NULL;
    }

    /**
     * @descr:批量插入k-v,请求的v需要是一个数组 如下格式：array('key0' => 'value0', 'key1' => 'value1')
     * @date: 2022/3/17 11:49
     * @example \PhalApi\DI()->redis->set_list($key, 1);
     * @param $value
     * @return bool
     */
    protected function set_list($value){
        $data = array();
        foreach($value as $k => $v){
            $data[$this->formatKey($k)] = $this->formatValue($v);
        }
        return $this->redis->mset($data);
    }

    /**
     * @descr:批量获取k-v,请求的k需要是一个数组
     * @date: 2022/3/17 11:50
     * @example \PhalApi\DI()->redis->get_list($key, 1);
     * @param $key
     * @return array
     */
    protected function get_list($key){
        $data = array();
        foreach($key as $k => $v){
            $data[] = $this->formatKey($v);
        }
        $rs = $this->redis->mget($data);
        foreach($rs as $k => $v){
            $rs[$k] = $this->unformatValue($v);
        }
        return $rs;
    }

    /**
     * @descr:判断key是否存在。存在 true 不在 false
     * @date: 2022/3/17 11:51
     * @example \PhalApi\DI()->redis->get_exists($key, 1);
     * @param $key
     * @return bool|int
     */
    protected function get_exists($key){
        return $this->redis->exists($this->formatKey($key));
    }

    /**
     * @descr:返回原来key中的值，并将value写入key
     * @date: 2022/3/17 11:51
     * @example \PhalApi\DI()->redis->get_getSet($key, 1);
     * @param $key
     * @param $value
     * @return mixed|null
     */
    protected function get_getSet($key, $value){
        $value = $this->redis->getSet($this->formatKey($key), $this->formatValue($value));
        return $value !== FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:string，名称为key的string的值在后面加上value
     * @date: 2022/3/17 11:52
     * @example \PhalApi\DI()->redis->set_append($key, $value, 1);
     * @param $key
     * @param $value
     * @return int
     */
    protected function set_append($key, $value){
        return $this->redis->append($this->formatKey($key), $this->formatValue($value));
    }

    /**
     * @descr:获取指定 key 所储存的字符串值的长度。当 key 储存的不是字符串值时，返回一个错误。
     * @date: 2022/3/17 11:56
     * @example \PhalApi\DI()->redis->set_append($key, 1);
     * @param $key
     * @return int
     */
    protected function get_strlen($key){
        return $this->redis->strlen($this->formatKey($key));
    }

    /**
     * @descr:Redis Incr 命令将 key 中储存的数字值增一。如果 key 不存在，那么 key 的值会先被初始化为 0 ，然后再执行 INCR 操作。 如果值包含错误的类型，或字符串类型的值不能表示为数字，那么返回一个错误。本操作的值限制在 64 位(bit)有符号数字表示之内。
     * @date: 2022/3/17 11:58
     * @example \PhalApi\DI()->redis->get_incr($key, 1);
     * @param $key
     * @param int $value
     * @return int
     */
    protected function get_incr($key, $value = 1){
        return $this->redis->incr($this->formatKey($key), $value);
    }

    /**
     * @descr:自动减少，value为自减少的值默认1；如果 key 不存在，那么 key 的值会先被初始化为 0 ，然后再执行 DECR 操作。如果值包含错误的类型，或字符串类型的值不能表示为数字，那么返回一个错误。本操作的值限制在 64 位(bit)有符号数字表示之内。
     * @date: 2022/3/17 11:59
     * @example \PhalApi\DI()->redis->get_decr($key, 1);
     * @param $key
     * @param int $value
     * @return int
     */
    protected function get_decr($key, $value = 1){
        return $this->redis->decr($this->formatKey($key), $value);
    }
    //------------------------------------------------List类型-------------------------------------------------
    /**
     * @descr:写入队列左边 并根据名称自动切换库
     * @date: 2022/3/17 13:34
     * @example \PhalApi\DI()->redis->set_lPush($key, $value, 1);
     * @param $key
     * @param $value
     * @return bool|int
     */
    protected function set_lPush($key, $value){
        return $this->redis->lPush($this->formatKey($key), $this->formatValue($value));
    }

    /**
     * @descr:将一个值插入到已存在的列表头部，列表不存在时操作无效。
     * @date: 2022/3/17 13:35
     * @example \PhalApi\DI()->redis->set_lPushx($key, $value, 1);
     * @param $key
     * @param $value
     * @return bool|int
     */
    protected function set_lPushx($key, $value){
        return $this->redis->lPushx($this->formatKey($key), $this->formatValue($value));
    }

    /**
     * @descr:命令用于将一个或多个值插入到列表的尾部(最右边)。如果列表不存在，一个空列表会被创建并执行 RPUSH 操作。 当列表存在但不是列表类型时，返回一个错误。并根据名称自动切换库
     * @date: 2022/3/17 13:36
     * @example \PhalApi\DI()->redis->set_rPush($key, $value, 1);
     * @param $key
     * @param $value
     * @return bool|int
     */
    protected function set_rPush($key, $value){
        return $this->redis->rPush($this->formatKey($key), $this->formatValue($value));
    }

    /**
     * @descr:命令用于将一个值插入到已存在的列表尾部(最右边)。如果列表不存在，操作无效。 并根据名称自动切换库
     * @date: 2022/3/17 13:38
     * @example \PhalApi\DI()->redis->set_rPushx($key, $value, 1);
     * @param $key
     * @param $value
     * @return bool|int
     */
    protected function set_rPushx($key, $value){
        return $this->redis->rPushx($this->formatKey($key), $this->formatValue($value));
    }

    /**
     * @descr:命令用于移除并返回列表的第一个元素。
     * @date: 2022/3/17 13:39
     * @example \PhalApi\DI()->redis->get_lPop($key, 1);
     * @param $key
     * @return mixed|null
     */
    protected function get_lPop($key){
        $value = $this->redis->lPop($this->formatKey($key));
        return $value != FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:命令用于移除列表的最后一个元素，返回值为移除的元素。
     * @date: 2022/3/17 13:41
     * @example \PhalApi\DI()->redis->get_rPop($key, 1);
     * @param $key
     * @return mixed|null
     */
    protected function get_rPop($key){
        $value = $this->redis->rPop($this->formatKey($key));
        return $value != FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:命令移出并获取列表的第一个元素， 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     * @date: 2022/3/17 13:42
     * @example \PhalApi\DI()->redis->get_blPop($key, 1);
     * @param $key
     * @return mixed|null
     */
    protected function get_blPop($key){
        $value = $this->redis->blPop($this->formatKey($key), \PhalApi\DI()->config->get('app.redis.blocking'));
        return $value != FALSE ? $this->unformatValue($value[1]) : NULL;
    }

    /**
     * @descr:命令移出并获取列表的最后一个元素， 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     * @date: 2022/3/17 13:43
     * @example \PhalApi\DI()->redis->get_brPop($key, 1);
     * @param $key
     * @return mixed|null
     */
    protected function get_brPop($key){
        $value = $this->redis->brPop($this->formatKey($key), \PhalApi\DI()->config->get('app.redis.blocking'));
        return $value != FALSE ? $this->unformatValue($value[1]) : NULL;
    }

    /**
     * @descr:命令用于返回列表的长度。 如果列表 key 不存在，则 key 被解释为一个空列表，返回 0 。 如果 key 不是列表类型，返回一个错误。
     * @date: 2022/3/17 13:43
     * @example \PhalApi\DI()->redis->get_lSize($key, 1);
     * @param $key
     * @return int
     */
    protected function get_lSize($key){
        return $this->redis->lSize($this->formatKey($key));
    }

    /**
     * @descr:命令用于返回列表的长度。 如果列表 key 不存在，则 key 被解释为一个空列表，返回 0 。 如果 key 不是列表类型，返回一个错误。
     * @date: 2022/3/17 13:43
     * @example \PhalApi\DI()->redis->get_lLen($key, 1);
     * @param $key
     * @return int
     */
    protected function get_lLen($key){
        return $this->redis->lLen($this->formatKey($key));
    }

    /**
     * @descr: 从左数通过索引来设置元素的值。当索引参数超出范围，或对一个空列表进行 LSET 时，返回一个错误。 关于列表下标的更多信息，请参考 LINDEX 命令
     * @date: 2022/3/17 13:50
     * @example \PhalApi\DI()->redis->set_lSet($key, 6, 'hello', 1);
     * @param $key
     * @param $index
     * @param $value
     * @return bool
     */
    protected function set_lSet($key, $index, $value){
        return $this->redis->lSet($this->formatKey($key), $index, $this->formatValue($value));
    }

    /**
     * @descr:命令用于通过索引获取列表中的元素。你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     * @date: 2022/3/17 13:53
     * @example \PhalApi\DI()->redis->get_lGet($key, 6, 1);
     * @param $key
     * @param $index
     * @return mixed|null
     */
    protected function get_lGet($key, $index){
        $value = $this->redis->lindex($this->formatKey($key), $index);
        return $value != FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:返回名称为key的list中start至end之间的元素（end为 -1 ，返回所有）
     * @descr:详细解释：返回列表中指定区间内的元素，区间以偏移量 START 和 END 指定。 其中 0 表示列表的第一个元素， 1 表示列表的第二个元素，以此类推。 你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     * @date: 2022/3/17 13:55
     * @example \PhalApi\DI()->redis->get_lRange($key, -6, 2, 1);
     * @param $key
     * @param $start
     * @param $end
     * @return array
     */
    protected function get_lRange($key, $start, $end){
        $rs = $this->redis->lRange($this->formatKey($key), $start, $end);
        foreach($rs as $k => $v){
            $rs[$k] = $this->unformatValue($v);
        }
        return $rs;
    }

    /**
     * @descr:截取名称为key的list，保留start至end之间的元素
     * @descr:详细解释： 对一个列表进行修剪(trim)，就是说，让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。下标 0 表示列表的第一个元素，以 1 表示列表的第二个元素，以此类推。 你也可以使用负数下标，以 -1 表示列表的最后一个元素， -2 表示列表的倒数第二个元素，以此类推。
     * @date: 2022/3/17 13:56
     * @example \PhalApi\DI()->redis->get_lTrim($key, 0, -1, 1);
     * @param $key
     * @param $start
     * @param $end
     * @return array|bool|mixed
     */
    protected function get_lTrim($key, $start, $end){
        $rs = $this->redis->lTrim($this->formatKey($key), $start, $end);
        if(is_array($rs)){
            foreach($rs as $k => $v){
                $rs[$k] = $this->unformatValue($v);
            }
        }else{
            $rs = $this->unformatValue($rs);
        }
        return $rs;
    }

    /**
     * @descr:移除列表中指定数量的指定值元素。count > 0: 从表头开始向表尾搜索，移除count个匹配元素；count < 0: 从表尾开始向表头搜索，移除-count个匹配元素；count = 0: 移除所有匹配元素
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_lRem('mylist', 'value', 2, 1);
     * @param $key
     * @param $value
     * @param int $count
     * @return int 移除的元素数量
     */
    protected function set_lRem($key, $value, $count = 0){
        return $this->redis->lRem($this->formatKey($key), $this->formatValue($value), $count);
    }

    /**
     * @descr:在列表中指定元素的前面或后面插入新元素。当指定元素不存在于列表中时，操作无效。
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_lInsert('mylist', Redis::AFTER, 'pivot', 'newvalue', 1);
     * @param $key
     * @param $position Redis::BEFORE 或 Redis::AFTER
     * @param $pivot 参考元素
     * @param $value 要插入的值
     * @return int 插入后列表的长度，或 -1 表示pivot不存在
     */
    protected function set_lInsert($key, $position, $pivot, $value){
        return $this->redis->lInsert($this->formatKey($key), $position, $this->formatValue($pivot), $this->formatValue($value));
    }

    /**
     * @descr:将列表srcKey的最后一个元素弹出，并将其插入到列表dstKey的头部。原子操作。
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_rpoplpush('srcList', 'dstList', 1);
     * @param $srcKey 源列表
     * @param $dstKey 目标列表
     * @return mixed|null 被移动的元素值
     */
    protected function set_rpoplpush($srcKey, $dstKey){
        $value = $this->redis->rpoplpush($this->formatKey($srcKey), $this->formatKey($dstKey));
        return $value != FALSE ? $this->unformatValue($value) : NULL;
    }

    //----------------------------------------------------set类型---------------------------------------------------
    /**
     * @descr:向集合添加一个或多个成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_sAdd('myset', 'member1', 'member2', 1);
     * @param $key
     * @param mixed ...$members 一个或多个成员
     * @return int 添加成功的成员数量
     */
    protected function set_sAdd($key, ...$members){
        return $this->redis->sAdd($this->formatKey($key), ...array_map([$this, 'formatValue'], $members));
    }

    /**
     * @descr:移除集合中一个或多个成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_sRem('myset', 'member1', 'member2', 1);
     * @param $key
     * @param mixed ...$members 一个或多个成员
     * @return int 移除成功的成员数量
     */
    protected function set_sRem($key, ...$members){
        return $this->redis->sRem($this->formatKey($key), ...array_map([$this, 'formatValue'], $members));
    }

    /**
     * @descr:移除并返回集合中的一个随机元素
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sPop('myset', 1);
     * @param $key
     * @return mixed|null 被弹出的元素，集合为空返回NULL
     */
    protected function get_sPop($key){
        $value = $this->redis->sPop($this->formatKey($key));
        return $value != FALSE ? $this->unformatValue($value) : NULL;
    }

    /**
     * @descr:返回集合中一个或多个随机元素，不会移除元素
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sRandMember('myset', 3, 1);
     * @param $key
     * @param int|null $count 返回的元素数量，为NULL时返回单个元素
     * @return mixed|null|array 单个元素或元素数组
     */
    protected function get_sRandMember($key, $count = null){
        $value = $this->redis->sRandMember($this->formatKey($key), $count);
        if(is_array($value)){
            foreach($value as $k => $v){
                $value[$k] = $this->unformatValue($v);
            }
        }else{
            $value = $value != FALSE ? $this->unformatValue($value) : NULL;
        }
        return $value;
    }

    /**
     * @descr:判断指定成员是否是集合的成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sIsMember('myset', 'member1', 1);
     * @param $key
     * @param $member 成员值
     * @return bool true表示是成员，false表示不是
     */
    protected function get_sIsMember($key, $member){
        return $this->redis->sIsMember($this->formatKey($key), $this->formatValue($member));
    }

    /**
     * @descr:获取集合的成员数量
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sCard('myset', 1);
     * @param $key
     * @return int 集合的成员数量
     */
    protected function get_sCard($key){
        return $this->redis->sCard($this->formatKey($key));
    }

    /**
     * @descr:返回集合中的所有成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sMembers('myset', 1);
     * @param $key
     * @return array 集合成员数组
     */
    protected function get_sMembers($key){
        $rs = $this->redis->sMembers($this->formatKey($key));
        foreach($rs as $k => $v){
            $rs[$k] = $this->unformatValue($v);
        }
        return $rs;
    }

    /**
     * @descr:返回给定所有集合的交集
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sInter('set1', 'set2', 'set3', 1);
     * @param mixed ...$keys 一个或多个集合键
     * @return array 交集成员数组
     */
    protected function get_sInter(...$keys){
        $formattedKeys = array_map([$this, 'formatKey'], $keys);
        $rs = $this->redis->sInter(...$formattedKeys);
        if(is_array($rs)){
            foreach($rs as $k => $v){
                $rs[$k] = $this->unformatValue($v);
            }
        }
        return $rs;
    }

    /**
     * @descr:将给定所有集合的交集存储到指定的目标集合中
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_sInterStore('destSet', 'set1', 'set2', 1);
     * @param $destination 目标集合键
     * @param mixed ...$keys 一个或多个集合键
     * @return int 目标集合中的成员数量
     */
    protected function set_sInterStore($destination, ...$keys){
        $formattedKeys = array_map([$this, 'formatKey'], $keys);
        return $this->redis->sInterStore($this->formatKey($destination), ...$formattedKeys);
    }

    /**
     * @descr:返回给定所有集合的并集
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sUnion('set1', 'set2', 'set3', 1);
     * @param mixed ...$keys 一个或多个集合键
     * @return array 并集成员数组
     */
    protected function get_sUnion(...$keys){
        $formattedKeys = array_map([$this, 'formatKey'], $keys);
        $rs = $this->redis->sUnion(...$formattedKeys);
        if(is_array($rs)){
            foreach($rs as $k => $v){
                $rs[$k] = $this->unformatValue($v);
            }
        }
        return $rs;
    }

    /**
     * @descr:将给定所有集合的并集存储到指定的目标集合中
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_sUnionStore('destSet', 'set1', 'set2', 1);
     * @param $destination 目标集合键
     * @param mixed ...$keys 一个或多个集合键
     * @return int 目标集合中的成员数量
     */
    protected function set_sUnionStore($destination, ...$keys){
        $formattedKeys = array_map([$this, 'formatKey'], $keys);
        return $this->redis->sUnionStore($this->formatKey($destination), ...$formattedKeys);
    }

    /**
     * @descr:返回第一个集合与其他集合之间的差集
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->get_sDiff('set1', 'set2', 'set3', 1);
     * @param mixed ...$keys 一个或多个集合键，第一个是源集合
     * @return array 差集成员数组
     */
    protected function get_sDiff(...$keys){
        $formattedKeys = array_map([$this, 'formatKey'], $keys);
        $rs = $this->redis->sDiff(...$formattedKeys);
        if(is_array($rs)){
            foreach($rs as $k => $v){
                $rs[$k] = $this->unformatValue($v);
            }
        }
        return $rs;
    }

    /**
     * @descr:将第一个集合与其他集合之间的差集存储到指定的目标集合中
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_sDiffStore('destSet', 'set1', 'set2', 1);
     * @param $destination 目标集合键
     * @param mixed ...$keys 一个或多个集合键，第一个是源集合
     * @return int 目标集合中的成员数量
     */
    protected function set_sDiffStore($destination, ...$keys){
        $formattedKeys = array_map([$this, 'formatKey'], $keys);
        return $this->redis->sDiffStore($this->formatKey($destination), ...$formattedKeys);
    }

    /**
     * @descr:将指定成员从源集合移动到目标集合
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->set_sMove('srcSet', 'dstSet', 'member1', 1);
     * @param $srcKey 源集合键
     * @param $dstKey 目标集合键
     * @param $member 要移动的成员
     * @return bool true表示移动成功，false表示成员不存在于源集合
     */
    protected function set_sMove($srcKey, $dstKey, $member){
        return $this->redis->sMove($this->formatKey($srcKey), $this->formatKey($dstKey), $this->formatValue($member));
    }

    //----------------------------------------------------zset类型---------------------------------------------------
    /**
     * @descr:向有序集合添加一个或多个成员，或者更新已存在成员的分数  ZADD KEY_NAME SCORE1 VALUE1.. SCOREN VALUEN
     * @date: 2022/08/05 13:59
     * @example \PhalApi\DI()->redis->ZADD('runoobkey', 3,'mysql',  0);
     * @param $key
     * @param $SCORE
     * @param $VALUE
     * @param $talbename
     * @return bool|int  0:失败, 1:成功
     */
    public function ZADD($key, $SCORE, $VALUE, $talbename){
        //ZADD KEY_NAME SCORE1 VALUE1.. SCOREN VALUEN
        $this->redis->select($talbename);
        return $this->redis->ZADD($key, $SCORE, $VALUE);
    }

    /**
     * @descr:Redis Zcard 命令用于计算集合中元素的数量。
     * @date: 2022/08/05 13:59
     * @example \PhalApi\DI()->redis->ZCARD('runoobkey', 0);
     * @param $key
     * @param $talbename
     * @return int
     */
    public function ZCARD($key,$talbename){
        $this->redis->select($talbename);
        return $this->redis->ZCARD($key);
    }

    /**
     * @descr: Redis Zcount 命令用于计算有序集合中指定分数区间的成员数量。
     * @date: 2022/08/05 13:59
     * @example \PhalApi\DI()->redis->ZCARD('runoobkey', 5, 10, 0);
     * @param $key
     * @param $min
     * @param $max
     * @param $talbename
     * @return int
     */
    public function ZCOUNT($key, $min, $max, $talbename){
        $this->redis->select($talbename);
        return $this->redis->ZCOUNT($key, $min, $max);
    }

    /**
     * @descr: Redis Zincrby
     * @date: 2022/08/05 13:59
     * @example \PhalApi\DI()->redis->ZINCRBY('myzset', 2, "one", 0);
     * @param $key
     * @param $increment
     * @param $member
     * @param $talbename
     * @return int
     */
    public function ZINCRBY($key, $increment, $member, $talbename){
        $this->redis->select($talbename);
        return $this->redis->ZINCRBY($key, $increment, $member);
    }
    /**
     * @descr: Redis Zrem 命令用于移除有序集中的一个或多个成员，不存在的成员将被忽略。当 key 存在但不是有序集类型时，返回一个错误。
     * @date: 2022/08/05 13:59
     * @example \PhalApi\DI()->redis->ZREM(0, 'myzset', 'one', 'two', 'five');
     * @param $key
     * @param $increment
     * @param $member
     * @param $talbename
     * @return int
     */
    public function ZREM($talbename, $key, $member1, ...$otherMembers){
        $this->redis->select($talbename);
        return $this->redis->zRem($key, $member1, ...$otherMembers);
    }

    /**
     * @descr:计算给定有序集合的交集，并将结果存储到目标集合中
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZINTERSTORE('destKey', ['key1', 'key2'], [1, 2], 'SUM', 0);
     * @param $destination 目标集合键
     * @param $keys 有序集合键数组
     * @param array $weights 权重数组
     * @param string $aggregate 聚合函数: SUM, MIN, MAX
     * @param $talbename 数据库名或索引
     * @return int 目标集合中的成员数量
     */
    public function ZINTERSTORE($destination, $keys, $weights = [], $aggregate = 'SUM', $talbename){
        $this->redis->select($talbename);
        return $this->redis->zInterStore($destination, $keys, $weights, $aggregate);
    }

    /**
     * @descr:计算有序集合中指定字典区间内的成员数量
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZLEXCOUNT('myzset', '-', '+', 0);
     * @param $key
     * @param $min 最小字典值，'-'表示负无穷，'+'表示正无穷
     * @param $max 最大字典值
     * @param $talbename 数据库名或索引
     * @return int 成员数量
     */
    public function ZLEXCOUNT($key, $min, $max, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zLexCount($key, $min, $max);
    }

    /**
     * @descr:返回有序集合中指定索引区间内的成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZRANGE('myzset', 0, -1, true, 0);
     * @param $key
     * @param $start 起始索引
     * @param $end 结束索引，-1表示最后一个
     * @param bool $withscores 是否返回分数
     * @param $talbename 数据库名或索引
     * @return array 成员数组或成员-分数关联数组
     */
    public function ZRANGE($key, $start, $end, $withscores = false, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRange($key, $start, $end, $withscores);
    }

    /**
     * @descr:返回有序集合中指定字典区间内的成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZRANGEBYLEX('myzset', '[a', '[z', 0, 10, 0);
     * @param $key
     * @param $min 最小字典值
     * @param $max 最大字典值
     * @param int|null $offset 偏移量
     * @param int|null $limit 返回数量限制
     * @param $talbename 数据库名或索引
     * @return array 成员数组
     */
    public function ZRANGEBYLEX($key, $min, $max, $offset = null, $limit = null, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRangeByLex($key, $min, $max, $offset, $limit);
    }

    /**
     * @descr:返回有序集合中指定分数区间内的成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZRANGEBYSCORE('myzset', 1, 10, ['WITHSCORES' => true], 0);
     * @param $key
     * @param $min 最小分数
     * @param $max 最大分数
     * @param array $options 选项数组，如 ['WITHSCORES' => true, 'LIMIT' => [0, 10]]
     * @param $talbename 数据库名或索引
     * @return array 成员数组或成员-分数关联数组
     */
    public function ZRANGEBYSCORE($key, $min, $max, $options = [], $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRangeByScore($key, $min, $max, $options);
    }

    /**
     * @descr:返回有序集合中指定成员的排名（按分数从小到大）
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZRANK('myzset', 'member', 0);
     * @param $key
     * @param $member 成员值
     * @param $talbename 数据库名或索引
     * @return int|null 排名（从0开始），不存在返回NULL
     */
    public function ZRANK($key, $member, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRank($key, $member);
    }

    /**
     * @descr:移除有序集合中指定分数区间内的所有成员
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZREMRANGEBYSCORE('myzset', 1, 10, 0);
     * @param $key
     * @param $min 最小分数
     * @param $max 最大分数
     * @param $talbename 数据库名或索引
     * @return int 移除的成员数量
     */
    public function ZREMRANGEBYSCORE($key, $min, $max, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRemRangeByScore($key, $min, $max);
    }

    /**
     * @descr:返回有序集合中指定索引区间内的成员（按分数从大到小）
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZREVRANGE('myzset', 0, -1, true, 0);
     * @param $key
     * @param $start 起始索引
     * @param $end 结束索引，-1表示最后一个
     * @param bool $withscores 是否返回分数
     * @param $talbename 数据库名或索引
     * @return array 成员数组或成员-分数关联数组
     */
    public function ZREVRANGE($key, $start, $end, $withscores = false, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRevRange($key, $start, $end, $withscores);
    }

    /**
     * @descr:返回有序集合中指定分数区间内的成员（按分数从大到小）
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZREVRANGEBYSCORE('myzset', 10, 1, ['WITHSCORES' => true], 0);
     * @param $key
     * @param $max 最大分数
     * @param $min 最小分数
     * @param array $options 选项数组
     * @param $talbename 数据库名或索引
     * @return array 成员数组或成员-分数关联数组
     */
    public function ZREVRANGEBYSCORE($key, $max, $min, $options = [], $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRevRangeByScore($key, $max, $min, $options);
    }

    /**
     * @descr:返回有序集合中指定成员的排名（按分数从大到小）
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZREVRANK('myzset', 'member', 0);
     * @param $key
     * @param $member 成员值
     * @param $talbename 数据库名或索引
     * @return int|null 排名（从0开始），不存在返回NULL
     */
    public function ZREVRANK($key, $member, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zRevRank($key, $member);
    }

    /**
     * @descr:返回有序集合中指定成员的分数
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZSCORE('myzset', 'member', 0);
     * @param $key
     * @param $member 成员值
     * @param $talbename 数据库名或索引
     * @return float|null 分数值，不存在返回NULL
     */
    public function ZSCORE($key, $member, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zScore($key, $member);
    }

    /**
     * @descr:计算给定有序集合的并集，并将结果存储到目标集合中
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZUNIONSTORE('destKey', ['key1', 'key2'], [1, 2], 'SUM', 0);
     * @param $destination 目标集合键
     * @param $keys 有序集合键数组
     * @param array $weights 权重数组
     * @param string $aggregate 聚合函数: SUM, MIN, MAX
     * @param $talbename 数据库名或索引
     * @return int 目标集合中的成员数量
     */
    public function ZUNIONSTORE($destination, $keys, $weights = [], $aggregate = 'SUM', $talbename){
        $this->redis->select($talbename);
        return $this->redis->zUnionStore($destination, $keys, $weights, $aggregate);
    }

    /**
     * @descr:迭代有序集合中的元素
     * @date: 2024/01/01
     * @example \PhalApi\DI()->redis->ZSCAN('myzset', $iterator, '*pattern*', 10, 0);
     * @param $key
     * @param int &$iterator 迭代器，第一次调用传0
     * @param string|null $pattern 匹配模式
     * @param int|null $count 返回数量提示
     * @param $talbename 数据库名或索引
     * @return array [元素数组, 下一个迭代器]
     */
    public function ZSCAN($key, &$iterator, $pattern = null, $count = null, $talbename){
        $this->redis->select($talbename);
        return $this->redis->zScan($key, $iterator, $pattern, $count);
    }

    //----------------------------------------------------Hash类型---------------------------------------------------
    /**
     * @descr:删除哈希表 key 中的一个或多个指定字段，不存在的字段将被忽略。
     * @date: 2022/3/17 13:59
     * @example \PhalApi\DI()->redis->HDEL('test', array('testkey1','testkey2'), 0);
     * @param $key
     * @param $field
     * @param $talbename
     * @return bool|int
     */
    public function HDEL($key, $field, $talbename){
        $this->redis->select($talbename);
        $i = 0;
        if(is_array($field)){
            foreach($field as $hashkey){
                $hashDelState = $this->redis->HDEL($this->formatKey($key), $hashkey);
                if($hashDelState){
                    $i++;
                }
            }
        }else{
            $i = $this->redis->HDEL($this->formatKey($key), $field);
        }
        return $i;
    }

    /**
     * @descr:Redis Hgetall 命令用于返回哈希表中，所有的字段和值。在返回值里，紧跟每个字段名(field name)之后是字段的值(value)，所以返回值的长度是哈希表大小的两倍。
     * @date: 2022/3/17 15:38
     * @example \PhalApi\DI()->redis->HGETALL('test', 0);
     * @param $key
     * @param $talbename
     * @return array
     */
    public function HGETALL($key, $talbename){
        $this->redis->select($talbename);
        $array =  $this->redis->HGETALL($this->formatKey($key));
        if(is_array($array) && !empty($array)){
            foreach($array as $key=> $value){
                $array[$key] = json_decode($value, true);
            }
        }
        return $array;
    }

    /**
     * @descr:Redis Hset 命令用于为哈希表中的字段赋值 。如果哈希表不存在，一个新的哈希表被创建并进行 HSET 操作。如果字段已经存在于哈希表中，旧值将被覆盖。
     * @date: 2022/3/17 15:39
     * @example \PhalApi\DI()->redis->HSET($key, $field, $value, 0);
     * @param $key
     * @param $field
     * @param $value
     * @param $talbename
     * @return bool|int
     */
    public function HSET ($key, $field, $value, $talbename){
        $this->redis->select($talbename);
        return $this->redis->HSET ($this->formatKey($key), $field, json_encode($value));
    }

    /**
     * @descr:Redis Hexists 命令用于查看哈希表的指定字段是否存在。
     * @date: 2022/3/17 15:40
     * @example \PhalApi\DI()->redis->HEXISTS($key, $field, $talbename);
     * @param $key
     * @param $field
     * @param $talbename
     * @return bool
     */
    public function HEXISTS ($key, $field, $talbename){
        $this->redis->select($talbename);
        return $this->redis->HEXISTS ($this->formatKey($key), $field);
    }

    /**
     * @descr:Redis Hmget 命令用于返回哈希表中，一个或多个给定字段的值。如果指定的字段不存在于哈希表，那么返回一个 nil 值。
     * @date: 2022/3/17 15:40
     * @example \PhalApi\DI()->redis->HMGET($key, $field, $talbename);
     * @param $key
     * @param $field
     * @param $talbename
     * @return array
     */
    public function HMGET ($key, $field, $talbename){
        $this->redis->select($talbename);
        if(is_array($field)){
            $return_field = $this->redis->HMGET ($this->formatKey($key), $field);
        }else{
            $fieldArray[] = $field;
            $return_field = $this->redis->HMGET ($this->formatKey($key), $fieldArray);
        }
        foreach ($return_field as $key => $value){
            $return_field[$key] = json_decode($value, true);
        }
        return $return_field;
    }

    /**
     * @descr:Redis Hget 命令用于返回哈希表中指定字段的值。
     * @date: 2022/3/17 15:41
     * @example \PhalApi\DI()->redis->HMGET($key, $field, $talbename);
     * @param $key
     * @param $field
     * @param $talbename
     * @return mixed
     */
    public function HGET($key, $field, $talbename){
        $this->redis->select($talbename);
        return json_decode($this->redis->HGET($this->formatKey($key), $field));
    }
    //----------------------------------------------------通用方法---------------------------------------------------

    /**
     * 永久计数器,回调当前计数
     * @author Axios <axioscros@aliyun.com>
     */
    public function counter_forever($key,$dbname=0){
        $this->switchDB($dbname);
        if($this->get_exists($key)){
            $count = $this->get_forever($key);
            $count++;
            $this->set_forever($key,$count);
        }else{
            $count = 1;
            $this->set_forever($key,$count);
        }

        return $count;
    }
    /**
     * 创建具有有效时间的计数器,回调当前计数,单位毫秒ms
     * @author Axios <axioscros@aliyun.com>
     */
    public function counter_time_create($key,$expire  = 1000,$dbname=0){
        $this->switchDB($dbname);
        $count = 1;
        $this->set_time($key,$count,$expire);
        $this->redis->pSetEx($this->formatKey($key), $expire, $this->formatValue($count));
        return $count;
    }
    /**
     * 更新具有有效时间的计数器,回调当前计数
     * @author Axios <axioscros@aliyun.com>
     */
    public function counter_time_update($key,$dbname=0){
        $this->switchDB($dbname);
        if($this->get_exists($key)){
            $count = $this->get_time($key);
            $count++;
            $expire = $this->redis->pttl($this->formatKey($key));
            $this->set_time($key,$count,$expire);
            return $count;
        }
        return false;
    }
    /**
     * 设定一个key的活动时间（s）
     */
    protected function setTimeout($key, $time = 600){
        return $this->redis->setTimeout($key, $time);
    }

    /**
     * 返回key的类型值
     */
    protected function type($key){
        return $this->redis->type($key);
    }

    /**
     * key存活到一个unix时间戳时间
     */
    protected function expireAt($key, $time = 600){
        return $this->redis->expireAt($key, $time);
    }

    /**
     * 随机返回key空间的一个key
     */
    public function randomKey($tablename){
        $this->switchDB($tablename);
        return $this->redis->randomKey();
    }

    /**
     * 返回满足给定pattern的所有key
     */
    protected function keys($key, $pattern){
        return $this->redis->keys($key, $pattern);
    }

    /**
     * 查看现在数据库有多少key
     */
    protected function dbSize(){
        return $this->redis->dbSize();
    }

    /**
     * 转移一个key到另外一个数据库
     */
    protected function move($key, $db){
        $arr = \PhalApi\DI()->config->get('app.redis.DB');
        $rs  = isset($arr[$db]) ? $arr[$db] : $db;
        return $this->redis->move($key, $rs);
    }

    /**
     * 给key重命名
     */
    protected function rename($key, $key2){

        return $this->redis->rename($key, $key2);
    }

    /**
     * 给key重命名 如果重新命名的名字已经存在，不会替换成功
     */
    protected function renameNx($key, $key2){
        return $this->redis->renameNx($key, $key2);
    }

    /**
     * 删除键值 并根据名称自动切换库(对所有通用)
     */
    protected function del($key){
        return $this->redis->del($this->formatKey($key));
    }

    /**
     * 返回redis的版本信息等详情
     */
    public function info(){
        return $this->redis->info();
    }

    /**
     * 切换DB并且获得操作实例
     */
    public function get_redis(){
        return $this->redis;
    }

    /**
     * 查看连接状态
     */
    public function ping(){
        return $this->redis->ping();
    }

    /**
     * 内部切换Redis-DB 如果已经在某个DB上则不再切换
     */
    protected function switchDB($name){
        $arr = \PhalApi\DI()->config->get('app.redis.DB');
        if(is_int($name)){
            $db = $name;
        }else{
            $db = isset($arr[$name]) ? $arr[$name] : 0;
        }
        if($this->db_old != $db){
            $this->redis->select($db);
            $this->db_old = $db;
        }
    }

    //-------------------------------------------------------谨慎使用------------------------------------------------

    /**
     * 清空当前数据库
     */
    protected function flushDB(){
        return $this->redis->flushDB();
    }

    /**
     * 清空所有数据库
     */
    public function flushAll(){
        return $this->redis->flushAll();
    }

    /**
     * 选择从服务器
     */
    public function slaveof($host, $port){
        return $this->redis->slaveof($host, $port);
    }

    /**
     * 将数据同步保存到磁盘
     */
    public function save(){
        return $this->redis->save();
    }

    /**
     * 将数据异步保存到磁盘
     */
    public function bgsave(){
        return $this->redis->bgsave();
    }

    /**
     * 返回上次成功将数据保存到磁盘的Unix时戳
     */
    public function lastSave(){
        return $this->redis->lastSave();
    }

    /**
     * 使用aof来进行数据库持久化
     */
    protected function bgrewriteaof(){
        return $this->redis->bgrewriteaof();
    }
}
