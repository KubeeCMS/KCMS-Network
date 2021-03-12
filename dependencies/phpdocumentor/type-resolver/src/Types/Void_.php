<?php

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */
namespace WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types;

use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Type;
/**
 * Value Object representing the pseudo-type 'void'.
 *
 * Void is generally only used when working with return types as it signifies that the method intentionally does not
 * return any value.
 */
final class Void_ implements \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Type
{
    /**
     * Returns a rendered output of the Type as it would be used in a DocBlock.
     *
     * @return string
     */
    public function __toString()
    {
        return 'void';
    }
}
