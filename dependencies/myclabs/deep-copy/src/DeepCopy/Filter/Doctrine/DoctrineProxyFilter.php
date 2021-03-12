<?php

namespace WP_Ultimo\Dependencies\DeepCopy\Filter\Doctrine;

use WP_Ultimo\Dependencies\DeepCopy\Filter\Filter;
/**
 * @final
 */
class DoctrineProxyFilter implements \WP_Ultimo\Dependencies\DeepCopy\Filter\Filter
{
    /**
     * Triggers the magic method __load() on a Doctrine Proxy class to load the
     * actual entity from the database.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        $object->__load();
    }
}
