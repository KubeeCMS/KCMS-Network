<?php

namespace WP_Ultimo\Dependencies\Amp\Dns;

use WP_Ultimo\Dependencies\Amp\Promise;
interface ConfigLoader
{
    public function loadConfig() : Promise;
}
