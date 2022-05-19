<?php

namespace WP_Ultimo\Dependencies\Amp\Process\Internal;

use WP_Ultimo\Dependencies\Amp\Deferred;
use WP_Ultimo\Dependencies\Amp\Process\ProcessInputStream;
use WP_Ultimo\Dependencies\Amp\Process\ProcessOutputStream;
use WP_Ultimo\Dependencies\Amp\Struct;
abstract class ProcessHandle
{
    use Struct;
    /** @var ProcessOutputStream */
    public $stdin;
    /** @var ProcessInputStream */
    public $stdout;
    /** @var ProcessInputStream */
    public $stderr;
    /** @var Deferred */
    public $pidDeferred;
    /** @var int */
    public $status = ProcessStatus::STARTING;
}
