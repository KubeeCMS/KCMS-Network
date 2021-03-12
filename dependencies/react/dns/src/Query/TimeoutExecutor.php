<?php

namespace WP_Ultimo\Dependencies\React\Dns\Query;

use WP_Ultimo\Dependencies\React\EventLoop\LoopInterface;
use WP_Ultimo\Dependencies\React\Promise\Timer;
final class TimeoutExecutor implements \WP_Ultimo\Dependencies\React\Dns\Query\ExecutorInterface
{
    private $executor;
    private $loop;
    private $timeout;
    public function __construct(\WP_Ultimo\Dependencies\React\Dns\Query\ExecutorInterface $executor, $timeout, \WP_Ultimo\Dependencies\React\EventLoop\LoopInterface $loop)
    {
        $this->executor = $executor;
        $this->loop = $loop;
        $this->timeout = $timeout;
    }
    public function query(\WP_Ultimo\Dependencies\React\Dns\Query\Query $query)
    {
        return \WP_Ultimo\Dependencies\React\Promise\Timer\timeout($this->executor->query($query), $this->timeout, $this->loop)->then(null, function ($e) use($query) {
            if ($e instanceof \WP_Ultimo\Dependencies\React\Promise\Timer\TimeoutException) {
                $e = new \WP_Ultimo\Dependencies\React\Dns\Query\TimeoutException(\sprintf("DNS query for %s timed out", $query->name), 0, $e);
            }
            throw $e;
        });
    }
}
