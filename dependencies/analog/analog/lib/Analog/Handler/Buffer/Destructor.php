<?php

namespace WP_Ultimo\Dependencies\Analog\Handler\Buffer;

/**
 * A destructor object to call close() for us at the end of the request.
 */
class Destructor
{
    public function __destruct()
    {
        \WP_Ultimo\Dependencies\Analog\Handler\Buffer::close();
    }
}
