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
namespace WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Import;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * An `@import` rule.
 *
 * @internal
 */
final class ImportRule implements Statement
{
    /**
     * @var Import[]
     * @readonly
     */
    private $imports;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param Import[] $imports
     */
    public function __construct(array $imports, FileSpan $span)
    {
        $this->imports = $imports;
        $this->span = $span;
    }
    /**
     * @return Import[]
     */
    public function getImports() : array
    {
        return $this->imports;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitImportRule($this);
    }
}
