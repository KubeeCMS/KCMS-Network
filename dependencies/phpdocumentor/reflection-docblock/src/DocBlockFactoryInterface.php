<?php

namespace WP_Ultimo\Dependencies\phpDocumentor\Reflection;

interface DocBlockFactoryInterface
{
    /**
     * Factory method for easy instantiation.
     *
     * @param string[] $additionalTags
     *
     * @return DocBlockFactory
     */
    public static function createInstance(array $additionalTags = []);
    /**
     * @param string $docblock
     * @param Types\Context $context
     * @param Location $location
     *
     * @return DocBlock
     */
    public function create($docblock, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context $context = null, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Location $location = null);
}
