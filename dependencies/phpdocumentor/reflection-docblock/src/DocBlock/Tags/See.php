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

use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Description;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\FqsenResolver;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context as TypeContext;
use WP_Ultimo\Dependencies\Webmozart\Assert\Assert;
/**
 * Reflection class for an {@}see tag in a Docblock.
 */
class See extends \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\BaseTag implements \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod
{
    protected $name = 'see';
    /** @var Reference */
    protected $refers = null;
    /**
     * Initializes this tag.
     *
     * @param Reference $refers
     * @param Description $description
     */
    public function __construct(\WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference $refers, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    /**
     * {@inheritdoc}
     */
    public static function create($body, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\FqsenResolver $resolver = null, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\DescriptionFactory $descriptionFactory = null, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context $context = null)
    {
        \WP_Ultimo\Dependencies\Webmozart\Assert\Assert::string($body);
        \WP_Ultimo\Dependencies\Webmozart\Assert\Assert::allNotNull([$resolver, $descriptionFactory]);
        $parts = \preg_split('/\\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;
        // https://tools.ietf.org/html/rfc2396#section-3
        if (\preg_match('/\\w:\\/\\/\\w/i', $parts[0])) {
            return new static(new \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Reference\Url($parts[0]), $description);
        }
        return new static(new \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen($resolver->resolve($parts[0], $context)), $description);
    }
    /**
     * Returns the ref of this tag.
     *
     * @return Reference
     */
    public function getReference()
    {
        return $this->refers;
    }
    /**
     * Returns a string representation of this tag.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->refers . ($this->description ? ' ' . $this->description->render() : '');
    }
}
