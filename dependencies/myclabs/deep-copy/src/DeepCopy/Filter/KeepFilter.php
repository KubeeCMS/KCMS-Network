<?php

namespace WP_Ultimo\Dependencies\DeepCopy\Filter;

class KeepFilter implements \WP_Ultimo\Dependencies\DeepCopy\Filter\Filter
{
    /**
     * Keeps the value of the object property.
     *
     * {@inheritdoc}
     */
    public function apply($object, $property, $objectCopier)
    {
        // Nothing to do
    }
}
