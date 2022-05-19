<?php

namespace WP_Ultimo\Dependencies\Amp\Process\Internal\Posix;

use WP_Ultimo\Dependencies\Amp\Deferred;
use WP_Ultimo\Dependencies\Amp\Process\Internal\ProcessHandle;
/** @internal */
final class Handle extends ProcessHandle
{
    public function __construct()
    {
        $this->pidDeferred = new Deferred();
        $this->joinDeferred = new Deferred();
        $this->originalParentPid = \getmypid();
    }
    /** @var Deferred */
    public $joinDeferred;
    /** @var resource */
    public $proc;
    /** @var resource */
    public $extraDataPipe;
    /** @var string */
    public $extraDataPipeWatcher;
    /** @var string */
    public $extraDataPipeStartWatcher;
    /** @var int */
    public $originalParentPid;
}
