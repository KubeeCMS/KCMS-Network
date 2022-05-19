<?php

namespace WP_Ultimo\Dependencies\DeepCopy\Filter\Doctrine;

use WP_Ultimo\Dependencies\DeepCopy\Filter\Filter;
use WP_Ultimo\Dependencies\DeepCopy\Reflection\ReflectionHelper;
use WP_Ultimo\Dependencies\Doctrine\Common\Collections\ArrayCollection;
/**
 * @final
 */
class DoctrineEmptyCollectionFilter implements Filter
{
    /**
     * Sets the object property to an empty doctrine collection.
     *
     * @param object   $object
     * @param string   $property
     * @param callable $objectCopier
     */
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(\true);
        $reflectionProperty->setValue($object, new ArrayCollection());
    }
}
