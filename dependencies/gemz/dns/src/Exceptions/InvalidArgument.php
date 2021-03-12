<?php

namespace WP_Ultimo\Dependencies\Gemz\Dns\Exceptions;

class InvalidArgument extends \InvalidArgumentException
{
    public static function typeIsNotValid(string $type, array $allowedTypes) : self
    {
        $typesString = \implode(', ', \array_keys($allowedTypes));
        return new self("The given type `{$type}` is not valid. It should be one of {$typesString}");
    }
    public static function domainIsNotValid(string $domain) : self
    {
        return new self("The given domain `{$domain}` is not a valid domain.");
    }
}
