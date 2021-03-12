<?php

namespace WP_Ultimo\Dependencies\DeepCopy\TypeFilter;

/**
 * @final
 */
class ShallowCopyFilter implements \WP_Ultimo\Dependencies\DeepCopy\TypeFilter\TypeFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        return clone $element;
    }
}
