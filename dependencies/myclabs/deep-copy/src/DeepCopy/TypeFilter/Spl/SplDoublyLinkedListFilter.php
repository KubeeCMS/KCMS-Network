<?php

namespace WP_Ultimo\Dependencies\DeepCopy\TypeFilter\Spl;

use Closure;
use WP_Ultimo\Dependencies\DeepCopy\DeepCopy;
use WP_Ultimo\Dependencies\DeepCopy\TypeFilter\TypeFilter;
use SplDoublyLinkedList;
/**
 * @final
 */
class SplDoublyLinkedListFilter implements \WP_Ultimo\Dependencies\DeepCopy\TypeFilter\TypeFilter
{
    private $copier;
    public function __construct(\WP_Ultimo\Dependencies\DeepCopy\DeepCopy $copier)
    {
        $this->copier = $copier;
    }
    /**
     * {@inheritdoc}
     */
    public function apply($element)
    {
        $newElement = clone $element;
        $copy = $this->createCopyClosure();
        return $copy($newElement);
    }
    private function createCopyClosure()
    {
        $copier = $this->copier;
        $copy = function (\SplDoublyLinkedList $list) use($copier) {
            // Replace each element in the list with a deep copy of itself
            for ($i = 1; $i <= $list->count(); $i++) {
                $copy = $copier->recursiveCopy($list->shift());
                $list->push($copy);
            }
            return $list;
        };
        return \Closure::bind($copy, null, \WP_Ultimo\Dependencies\DeepCopy\DeepCopy::class);
    }
}
