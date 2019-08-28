<?php
/**
 * User: summerpassion
 * DateTime: 2019/8/27 15:16
 */
namespace Mustang\Shoppingcart\contracts;

/**
 * Abstact Class Driver
 * @package app\app\controller\shoppingcart
 */
abstract class Driver
{
    /**
     * 购物车前缀
     * @var string
     */
    public $cart_prefix = 'Cart_';

    /**
     * 默认分片
     * @var string
     */
    public $default_zone = 'Default';

    /**
     * 用户标识前缀
     * @var string
     */
    public $user_prefix = 'Uid_';

    /**
     * 获取购物车全数据
     */
    abstract function cartList($uid, $zone=null) ;

    /**
     * 购物车添加/减少商品
     */
    abstract function cartOper($gid, $gnum, $uid, $zone=null) ;

    /**
     * 更新购物车中单件商品的SKU
     */
    abstract function cartUpdateSku($gid, $old_gid, $uid, $zone=null) ;

    /**
     * 从购物车中删除单种商品
     */
    abstract function cartDelSingle($gid, $uid, $zone=null) ;

    /**
     * 清空购物车
     */
    abstract function cartClearAll($uid, $zone=null) ;

    /**
     * 购物车是否存在某商品
     */
    abstract function cartExistsGoods($gid, $uid, $zone=null) ;
}