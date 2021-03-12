<?php

/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace WP_Ultimo\Dependencies\Aura\Session;

/**
 *
 * A factory to create session segment objects.
 *
 * @package Aura.Session
 *
 */
class SegmentFactory
{
    /**
     *
     * Creates a session segment object.
     *
     * @param Session $session
     * @param string  $name
     *
     * @return Segment
     */
    public function newInstance(\WP_Ultimo\Dependencies\Aura\Session\Session $session, $name)
    {
        return new \WP_Ultimo\Dependencies\Aura\Session\Segment($session, $name);
    }
}
