<?php

namespace WP_Ultimo\Dependencies\DeepCopy\Filter;

use WP_Ultimo\Dependencies\DeepCopy\Reflection\ReflectionHelper;
/**
 * @final
 */
class SetNullFilter implements Filter
{
    /**
     * Sets the object property to null.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, null);
    }
}
