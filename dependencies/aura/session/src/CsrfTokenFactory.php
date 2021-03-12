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
 * A factory to create CSRF token objects.
 *
 * @package Aura.Session
 *
 */
class CsrfTokenFactory
{
    /**
     *
     * A cryptographically-secure random value generator.
     *
     * @var RandvalInterface
     *
     */
    protected $randval;
    /**
     *
     * Constructor.
     *
     * @param RandvalInterface $randval A cryptographically-secure random
     * value generator.
     *
     */
    public function __construct(\WP_Ultimo\Dependencies\Aura\Session\RandvalInterface $randval)
    {
        $this->randval = $randval;
    }
    /**
     *
     * Creates a CsrfToken object.
     *
     * @param Session $session The session manager.
     *
     * @return CsrfToken
     *
     */
    public function newInstance(\WP_Ultimo\Dependencies\Aura\Session\Session $session)
    {
        $segment = $session->getSegment('WP_Ultimo\\Dependencies\\Aura\\Session\\CsrfToken');
        return new \WP_Ultimo\Dependencies\Aura\Session\CsrfToken($segment, $this->randval);
    }
}
