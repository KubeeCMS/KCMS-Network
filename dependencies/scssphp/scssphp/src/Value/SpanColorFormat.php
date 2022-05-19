<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Value;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
/**
 * @internal
 */
final class SpanColorFormat
{
    /**
     * @var FileSpan
     */
    private $span;
    public function __construct(FileSpan $span)
    {
        $this->span = $span;
    }
    public function getOriginal() : string
    {
        return $this->span->getText();
    }
}
