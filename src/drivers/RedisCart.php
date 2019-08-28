<?php
/**
 * User: summerpassion
 * DateTime: 2019/8/27 17:57
 */
namespace Mustang\Shoppingcart\drivers;

use Mustang\Shoppingcart\contracts\Driver;
use Mustang\Shoppingcart\exception\ShoppingCartException;
use Mustang\Utils\Utils;

/**
 * 购物车类 - Redis 实现
 * Class RedisCart
 * @package app\app\controller\shoppingcart\drivers
 */
class RedisCart extends Driver
{
    /**
     * redis实例
     * @var object
     */
    private $redis_obj = null;

    /**
     * RedisCart constructor.
     */
    public function __construct()
    {
        $this->redis_obj = Utils::redis();
    }

    /**
     * 获取购物车全数据
     * 商品数据(商品 id|SKU id)&对应数量
     * @param int $uid 用户ID
     * @param null $zone 分片
     * @return array
     */
    public function cartList($uid, $zone=null) {

        if (!$uid || !is_int($uid) || 0 >= $uid) {
            throw new \InvalidArgumentException('无效的uid。');
        }

        $key = $this->cart_prefix . ($zone ?? $this->default_zone) . ':' . $this->user_prefix . $uid;

        return $this->redis_obj->hGetAll($key);
    }

    /**
     * 购物车添加/减少商品
     * @param int $gid 商品|sku id
     * @param int $gnum 操作数量
     * @param int $uid 用户id
     * @param null $zone 分片
     * @return bool
     */
    public function cartOper($gid, $gnum, $uid, $zone=null) {

        if (!$gid || !is_int($gid) || 0 >= $gid) {
            throw new \InvalidArgumentException('无效的商品标识。');
        }

        if (!is_int($gnum) || 0 == $gnum) {
            throw new \InvalidArgumentException('无效的更新数量。');
        }

        if (!$uid || !is_int($uid) || 0 >= $uid) {
            throw new \InvalidArgumentException('无效的uid。');
        }

        $key = $this->cart_prefix . ($zone ?? $this->default_zone) . ':' . $this->user_prefix . $uid;
        $hash_key = $gid;

        if (false !== $ret = $this->redis_obj->hIncrBy($key, $hash_key, $gnum)) {
            if ($this->redis_obj->hGet($key, $hash_key) <= 0) {
                if (!$this->redis_obj->hdel($key, $hash_key)) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * 更新购物车中单件商品的SKU
     * @param $gid int 目标商品|SKU id
     * @param $old_gid int 原商品|SKU id
     * @param $uid int 用户id
     * @param $zone null 分片
     */
    public function cartUpdateSku($gid, $old_gid, $uid, $zone=null) {

        if (!$gid || !is_int($gid) || 0 >= $gid) {
            throw new \InvalidArgumentException('无效的商品标识。');
        }

        if (!$old_gid || !is_int($old_gid) || 0 >= $old_gid) {
            throw new \InvalidArgumentException('无效的原商品标识。');
        }

        if (!$uid || !is_int($uid) || 0 >= $uid) {
            throw new \InvalidArgumentException('无效的uid。');
        }

        $key = $this->cart_prefix . ($zone ?? $this->default_zone) . ':' . $this->user_prefix . $uid;
        $hash_key_old = $old_gid;
        $hash_key = $gid;

        if (!$this->redis_obj->hExists($key, $hash_key_old)) {
            throw new ShoppingCartException("原商品不存在于购物车中。");
        }

        // 原有数量
        $old_num = $this->redis_obj->hGet($key, $hash_key_old);

        if (!$this->redis_obj->hdel($key, $hash_key_old) || false === $this->redis_obj->hIncrBy($key, $hash_key, $old_num)) {
            return false;
        }

        return true;
    }

    /**
     * 从购物车中删除单种商品
     * @param int $gid 商品标识
     * @param int $uid 用户id
     * @param null $zone 分片
     */
    public function cartDelSingle($gid, $uid, $zone=null) {

        if (!$gid || !is_int($gid) || 0 >= $gid) {
            throw new \InvalidArgumentException('无效的商品标识。');
        }

        if (!$uid || !is_int($uid) || 0 >= $uid) {
            throw new \InvalidArgumentException('无效的uid。');
        }

        $key = $this->cart_prefix . ($zone ?? $this->default_zone) . ':' . $this->user_prefix . $uid;
        $hash_key = $gid;

        if (false === $this->redis_obj->hdel($key, $hash_key)) {
            return false;
        }

        return true;
    }

    /**
     * 清空购物车
     * @param int $uid 用户id
     * @param null $zone 分片
     */
    public function cartClearAll($uid, $zone=null) {

        if (!$uid || !is_int($uid) || 0 >= $uid) {
            throw new \InvalidArgumentException('无效的uid。');
        }

        $key = $this->cart_prefix . ($zone ?? $this->default_zone) . ':' . $this->user_prefix . $uid;

        if (false === $this->redis_obj->del($key)) {
            return false;
        }

        return true;
    }

    /**
     * 购物车是否存在某商品
     */
    public function cartExistsGoods($gid, $uid, $zone=null) {
        if (!$gid || !is_int($gid) || 0 >= $gid) {
            throw new \InvalidArgumentException('无效的商品标识。');
        }

        if (!$uid || !is_int($uid) || 0 >= $uid) {
            throw new \InvalidArgumentException('无效的uid。');
        }

        $key = $this->cart_prefix . ($zone ?? $this->default_zone) . ':' . $this->user_prefix . $uid;
        $hash_key = $gid;

        return 1 == $this->redis_obj->hExists($key, $hash_key);
    }
}
