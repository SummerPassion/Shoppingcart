<?php
/**
 * User: summerpassion
 * DateTime: 2019/8/27 14:08
 */
namespace Mustang\Shoppingcart;

use Mustang\Shoppingcart\contracts\Driver;
use Mustang\Shoppingcart\exception\ShoppingCartException;

/**
 * 购物车类
 * Class Cart
 * @method static ShoppingCart cartList($uid, $zone=null) 获取购物车数据
 * @method static ShoppingCart cartOper($gid, $gnum, $uid, $zone=null) 购物车添加/减少商品
 * @method static ShoppingCart cartUpdateSku($gid, $old_gid, $uid, $zone=null) 更新购物车中单件商品的SKU
 * @method static ShoppingCart cartDelSingle($gid, $uid, $zone=null) 从购物车中删除单种商品
 * @method static ShoppingCart cartClearAll($uid, $zone=null) 清空购物车
 * @method static ShoppingCart cartExistsGoods($gid, $uid, $zone=null) 购物车是否存在某商品
 * @package Mustang\Shoppingcart
 */
class ShoppingCart
{
    /**
     * @var object 存储介质
     */
    protected static $driver = null;

    /**
     * @var string 类后缀
     */
    protected static $suffix = 'Cart';

    /**
     * ShoppingCart constructor.
     */
    protected function __construct(...$params) {}

    /**
     * Magic static call.
     * @param $method
     * @param $params
     */
    public static function __callStatic($method, $params)
    {
        $instance = new self($params);
        $driver = config('cart.driver') ?: 'redis';
        $class = $instance->create($driver);

        if (method_exists($class, $method)) {
            return call_user_func_array([$class, $method], $params);
        } else {
            throw new ShoppingCartException("[{$method}] 方法不存在！");
        }
    }

    /**
     * 创建实例
     * @param string $driver
     * @return Driver
     */
    protected function create($driver): Driver
    {
        $driver = __NAMESPACE__.'\\drivers\\' . ucfirst($driver . self::$suffix);

        if (self::$driver) {
            return self::$driver;
        } else {
            if (class_exists($driver)) {
                return $this->make($driver);
            } else {
                throw new ShoppingCartException("Driver [{$driver}] Not Exists");
            }
        }
    }

    /**
     * make
     * @param string $driver
     * @return Driver
     */
    protected function make($driver) : Driver
    {
        $app = new $driver();

        if ($app instanceof Driver) {
            return $app;
        }

        throw new ShoppingCartException("[{$driver}] Must Be An Instance Of Driver");
    }
}