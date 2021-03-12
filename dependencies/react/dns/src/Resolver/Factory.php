<?php

namespace WP_Ultimo\Dependencies\React\Dns\Resolver;

use WP_Ultimo\Dependencies\React\Cache\ArrayCache;
use WP_Ultimo\Dependencies\React\Cache\CacheInterface;
use WP_Ultimo\Dependencies\React\Dns\Config\HostsFile;
use WP_Ultimo\Dependencies\React\Dns\Query\CachingExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\CoopExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\ExecutorInterface;
use WP_Ultimo\Dependencies\React\Dns\Query\HostsFileExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\RetryExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\SelectiveTransportExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\TcpTransportExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\TimeoutExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\UdpTransportExecutor;
use WP_Ultimo\Dependencies\React\EventLoop\LoopInterface;
final class Factory
{
    /**
     * @param string        $nameserver
     * @param LoopInterface $loop
     * @return \React\Dns\Resolver\ResolverInterface
     */
    public function create($nameserver, \WP_Ultimo\Dependencies\React\EventLoop\LoopInterface $loop)
    {
        $executor = $this->decorateHostsFileExecutor($this->createExecutor($nameserver, $loop));
        return new \WP_Ultimo\Dependencies\React\Dns\Resolver\Resolver($executor);
    }
    /**
     * @param string          $nameserver
     * @param LoopInterface   $loop
     * @param ?CacheInterface $cache
     * @return \React\Dns\Resolver\ResolverInterface
     */
    public function createCached($nameserver, \WP_Ultimo\Dependencies\React\EventLoop\LoopInterface $loop, \WP_Ultimo\Dependencies\React\Cache\CacheInterface $cache = null)
    {
        // default to keeping maximum of 256 responses in cache unless explicitly given
        if (!$cache instanceof \WP_Ultimo\Dependencies\React\Cache\CacheInterface) {
            $cache = new \WP_Ultimo\Dependencies\React\Cache\ArrayCache(256);
        }
        $executor = $this->createExecutor($nameserver, $loop);
        $executor = new \WP_Ultimo\Dependencies\React\Dns\Query\CachingExecutor($executor, $cache);
        $executor = $this->decorateHostsFileExecutor($executor);
        return new \WP_Ultimo\Dependencies\React\Dns\Resolver\Resolver($executor);
    }
    /**
     * Tries to load the hosts file and decorates the given executor on success
     *
     * @param ExecutorInterface $executor
     * @return ExecutorInterface
     * @codeCoverageIgnore
     */
    private function decorateHostsFileExecutor(\WP_Ultimo\Dependencies\React\Dns\Query\ExecutorInterface $executor)
    {
        try {
            $executor = new \WP_Ultimo\Dependencies\React\Dns\Query\HostsFileExecutor(\WP_Ultimo\Dependencies\React\Dns\Config\HostsFile::loadFromPathBlocking(), $executor);
        } catch (\RuntimeException $e) {
            // ignore this file if it can not be loaded
        }
        // Windows does not store localhost in hosts file by default but handles this internally
        // To compensate for this, we explicitly use hard-coded defaults for localhost
        if (\DIRECTORY_SEPARATOR === '\\') {
            $executor = new \WP_Ultimo\Dependencies\React\Dns\Query\HostsFileExecutor(new \WP_Ultimo\Dependencies\React\Dns\Config\HostsFile("127.0.0.1 localhost\n::1 localhost"), $executor);
        }
        return $executor;
    }
    private function createExecutor($nameserver, \WP_Ultimo\Dependencies\React\EventLoop\LoopInterface $loop)
    {
        $parts = \parse_url($nameserver);
        if (isset($parts['scheme']) && $parts['scheme'] === 'tcp') {
            $executor = $this->createTcpExecutor($nameserver, $loop);
        } elseif (isset($parts['scheme']) && $parts['scheme'] === 'udp') {
            $executor = $this->createUdpExecutor($nameserver, $loop);
        } else {
            $executor = new \WP_Ultimo\Dependencies\React\Dns\Query\SelectiveTransportExecutor($this->createUdpExecutor($nameserver, $loop), $this->createTcpExecutor($nameserver, $loop));
        }
        return new \WP_Ultimo\Dependencies\React\Dns\Query\CoopExecutor($executor);
    }
    private function createTcpExecutor($nameserver, \WP_Ultimo\Dependencies\React\EventLoop\LoopInterface $loop)
    {
        return new \WP_Ultimo\Dependencies\React\Dns\Query\TimeoutExecutor(new \WP_Ultimo\Dependencies\React\Dns\Query\TcpTransportExecutor($nameserver, $loop), 5.0, $loop);
    }
    private function createUdpExecutor($nameserver, \WP_Ultimo\Dependencies\React\EventLoop\LoopInterface $loop)
    {
        return new \WP_Ultimo\Dependencies\React\Dns\Query\RetryExecutor(new \WP_Ultimo\Dependencies\React\Dns\Query\TimeoutExecutor(new \WP_Ultimo\Dependencies\React\Dns\Query\UdpTransportExecutor($nameserver, $loop), 5.0, $loop));
    }
}
