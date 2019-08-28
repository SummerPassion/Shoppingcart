#mustang/shoppingcart
>thinkphp5.0 购物车基于redis的实现，多存储介质待扩展。

##安装方法
```
composer require mustang/shoppingcart
```

##在项目中使用shoppingcart
>V1.0版本仅更新了以Redis为存储介质的购物车，后期可能扩展其他存储介质。

>需要在config.php中配置以下信息


```php
// redis相关配置
'redis' => [
    'host' => '127.0.0.1',
    'port' => 6379,
    'auth' => '',
    'db_id' => 4
]

// 购物车存储介质配置，如不配置默认使用redis。
'cart' => [
    'driver' => 'redis'
]
```

##"分片"
>为处理商城中不止一个购物车的情况，增加了分片的概念。

**获取购物车数据**
>获取用户特定购物车中商品|SKU的ID及数量。

```php
/**
 * 获取购物车全数据
 * 商品数据(商品 id|SKU id)&对应数量
 * @param int $uid 用户ID
 * @param null $zone 分片
 * @return array
 */
ShoppingCart::cartList($uid, $zone=null);
```

**购物车添加/减少商品**
>特定购物车中针对单个商品的更新数量，数量可增加可减少。

```php
/**
 * 购物车添加/减少商品
 * @param int $gid 商品|sku id
 * @param int $gnum 操作数量
 * @param int $uid 用户id
 * @param null $zone 分片
 * @return bool
 */
ShoppingCart::cartOper($gid, $gnum, $uid, $zone=null);
```

**更新购物车中单件商品的SKU**
>如果购物车中使用到了SKU，存储的key实际上是SKU_ID，可能存在以下情况，一个商品的2种SKU同时存在在购物车中，修改其中的某个到另外一个，需要合并数量。

```php
/**
 * 更新购物车中单件商品的SKU
 * @param $gid int 目标商品|SKU id
 * @param $old_gid int 原商品|SKU id
 * @param $uid int 用户id
 * @param $zone null 分片
 */
 ShoppingCart::cartUpdateSku($gid, $old_gid, $uid, $zone=null);
```

**删除购物车中单种商品**
>从特定购物车中删除单一种商品

```php
/**
 * 从购物车中删除单种商品
 * @param int $gid 商品标识
 * @param int $uid 用户id
 * @param null $zone 分片
 */
ShoppingCart::cartDelSingle($gid, $uid, $zone=null);
```

**清空购物车**
>清空特定购物车中的所有商品
```php
/**
 * 清空购物车
 * @param int $uid 用户id
 * @param null $zone 分片
 */
ShoppingCart::cartClearAll($uid, $zone=null);
```

**购物车是否存在某商品**
>判断购物车中是否存在某商品或者某商品SKU

```php
/**
 * 购物车是否存在某商品
 * @param $gid int 商品标识
 * @param $uid int 用户id
 * @param $zone null
 * @return bool
 */
ShoppingCart::cartExistsGoods($gid, $uid, $zone=null)
```

>该扩展只负责处理购物车数据部分，而不关心操作购物车之前的各种数据校验过程。
