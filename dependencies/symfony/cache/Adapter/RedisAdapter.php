<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WP_Ultimo\Dependencies\Symfony\Component\Cache\Adapter;

use WP_Ultimo\Dependencies\Symfony\Component\Cache\Marshaller\MarshallerInterface;
use WP_Ultimo\Dependencies\Symfony\Component\Cache\Traits\RedisClusterProxy;
use WP_Ultimo\Dependencies\Symfony\Component\Cache\Traits\RedisProxy;
use WP_Ultimo\Dependencies\Symfony\Component\Cache\Traits\RedisTrait;
class RedisAdapter extends AbstractAdapter
{
    use RedisTrait;
    /**
     * @param \Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface|RedisProxy|RedisClusterProxy $redis           The redis client
     * @param string                                                                                $namespace       The default namespace
     * @param int                                                                                   $defaultLifetime The default lifetime
     */
    public function __construct($redis, string $namespace = '', int $defaultLifetime = 0, MarshallerInterface $marshaller = null)
    {
        $this->init($redis, $namespace, $defaultLifetime, $marshaller);
    }
}
