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
namespace WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags;

use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tag;
interface Formatter
{
    /**
     * Formats a tag into a string representation according to a specific format, such as Markdown.
     *
     * @param Tag $tag
     *
     * @return string
     */
    public function format(\WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tag $tag);
}
