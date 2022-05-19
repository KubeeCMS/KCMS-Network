<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */
namespace WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Tags;

use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\Description;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Fqsen;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\FqsenResolver;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context as TypeContext;
use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Utils;
use WP_Ultimo\Dependencies\Webmozart\Assert\Assert;
use function array_key_exists;
use function explode;
/**
 * Reflection class for a {@}uses tag in a Docblock.
 */
final class Uses extends BaseTag implements Factory\StaticMethod
{
    /** @var string */
    protected $name = 'uses';
    /** @var Fqsen */
    protected $refers;
    /**
     * Initializes this tag.
     */
    public function __construct(Fqsen $refers, ?Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    public static function create(string $body, ?FqsenResolver $resolver = null, ?DescriptionFactory $descriptionFactory = null, ?TypeContext $context = null) : self
    {
        Assert::notNull($resolver);
        Assert::notNull($descriptionFactory);
        $parts = Utils::pregSplit('/\\s+/Su', $body, 2);
        return new static(self::resolveFqsen($parts[0], $resolver, $context), $descriptionFactory->create($parts[1] ?? '', $context));
    }
    private static function resolveFqsen(string $parts, ?FqsenResolver $fqsenResolver, ?TypeContext $context) : Fqsen
    {
        Assert::notNull($fqsenResolver);
        $fqsenParts = explode('::', $parts);
        $resolved = $fqsenResolver->resolve($fqsenParts[0], $context);
        if (!array_key_exists(1, $fqsenParts)) {
            return $resolved;
        }
        return new Fqsen($resolved . '::' . $fqsenParts[1]);
    }
    /**
     * Returns the structural element this tag refers to.
     */
    public function getReference() : Fqsen
    {
        return $this->refers;
    }
    /**
     * Returns a string representation of this tag.
     */
    public function __toString() : string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }
        $refers = (string) $this->refers;
        return $refers . ($description !== '' ? ($refers !== '' ? ' ' : '') . $description : '');
    }
}
