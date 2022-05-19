<?php

namespace WP_Ultimo\Dependencies\Http\Message;

/**
 * Factory for PSR-7 Request and Response.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
interface MessageFactory extends RequestFactory, ResponseFactory
{
}
