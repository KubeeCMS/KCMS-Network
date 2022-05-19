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

use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Ast\Sass\Statement;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Exception\SassFormatException;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Logger\LoggerInterface;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Parser\CssParser;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Parser\ScssParser;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\FileSpan;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\SourceSpan\SourceFile;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Syntax;
use WP_Ultimo\Dependencies\ScssPhp\ScssPhp\Visitor\StatementVisitor;
/**
 * A Sass stylesheet.
 *
 * This is the root Sass node. It contains top-level statements.
 *
 * @extends ParentStatement<Statement[]>
 *
 * @internal
 */
final class Stylesheet extends ParentStatement
{
    /**
     * @var bool
     * @readonly
     */
    private $plainCss;
    /**
     * @var FileSpan
     * @readonly
     */
    private $span;
    /**
     * @param Statement[] $children
     */
    public function __construct(array $children, FileSpan $span, bool $plainCss = \false)
    {
        $this->span = $span;
        $this->plainCss = $plainCss;
        parent::__construct($children);
    }
    public function isPlainCss() : bool
    {
        return $this->plainCss;
    }
    public function getSpan() : FileSpan
    {
        return $this->span;
    }
    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitStylesheet($this);
    }
    /**
     * @param Syntax::* $syntax
     *
     * @throws SassFormatException when parsing fails
     */
    public static function parse(string $contents, string $syntax, ?LoggerInterface $logger = null, ?string $sourceUrl = null) : self
    {
        switch ($syntax) {
            case Syntax::SASS:
                return self::parseSass($contents, $logger, $sourceUrl);
            case Syntax::SCSS:
                return self::parseScss($contents, $logger, $sourceUrl);
            case Syntax::CSS:
                return self::parseCss($contents, $logger, $sourceUrl);
            default:
                throw new \InvalidArgumentException("Unknown syntax {$syntax}.");
        }
    }
    /**
     * @throws SassFormatException when parsing fails
     */
    public static function parseSass(string $contents, ?LoggerInterface $logger = null, ?string $sourceUrl = null) : self
    {
        $file = new SourceFile($contents, $sourceUrl);
        $span = $file->span(0, 0);
        throw new SassFormatException('The Sass indented syntax is not implemented.', $span);
    }
    /**
     * @throws SassFormatException when parsing fails
     */
    public static function parseScss(string $contents, ?LoggerInterface $logger = null, ?string $sourceUrl = null) : self
    {
        return (new ScssParser($contents, $logger, $sourceUrl))->parse();
    }
    /**
     * @throws SassFormatException when parsing fails
     */
    public static function parseCss(string $contents, ?LoggerInterface $logger = null, ?string $sourceUrl = null) : self
    {
        return (new CssParser($contents, $logger, $sourceUrl))->parse();
    }
}
