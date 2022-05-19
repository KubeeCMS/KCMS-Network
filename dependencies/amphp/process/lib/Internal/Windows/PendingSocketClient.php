<?php

namespace WP_Ultimo\Dependencies\Amp\Process\Internal\Windows;

use WP_Ultimo\Dependencies\Amp\Struct;
/**
 * @internal
 * @codeCoverageIgnore Windows only.
 */
final class PendingSocketClient
{
    use Struct;
    public $readWatcher;
    public $timeoutWatcher;
    public $receivedDataBuffer = '';
    public $pid;
    public $streamId;
}
