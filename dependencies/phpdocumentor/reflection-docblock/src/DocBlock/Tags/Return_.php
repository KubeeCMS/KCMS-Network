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
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Type;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\TypeResolver;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context as TypeContext;
use WP_Ultimo\Dependencies\Webmozart\Assert\Assert;
/**
 * Reflection class for a {@}return tag in a Docblock.
 */
final class Return_ extends \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\TagWithType implements \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod
{
    public function __construct(\WP_Ultimo\Dependencies\phpDocumentor\Reflection\Type $type, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Description $description = null)
    {
        $this->name = 'return';
        $this->type = $type;
        $this->description = $description;
    }
    /**
     * {@inheritdoc}
     */
    public static function create($body, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\TypeResolver $typeResolver = null, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\DescriptionFactory $descriptionFactory = null, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context $context = null)
    {
        \WP_Ultimo\Dependencies\Webmozart\Assert\Assert::string($body);
        \WP_Ultimo\Dependencies\Webmozart\Assert\Assert::allNotNull([$typeResolver, $descriptionFactory]);
        list($type, $description) = self::extractTypeFromBody($body);
        $type = $typeResolver->resolve($type, $context);
        $description = $descriptionFactory->create($description, $context);
        return new static($type, $description);
    }
    public function __toString()
    {
        return $this->type . ' ' . $this->description;
    }
}
