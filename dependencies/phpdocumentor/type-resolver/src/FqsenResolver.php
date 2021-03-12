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
namespace WP_Ultimo\Dependencies\phpDocumentor\Reflection;

use WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context;
class FqsenResolver
{
    /** @var string Definition of the NAMESPACE operator in PHP */
    const OPERATOR_NAMESPACE = '\\';
    public function resolve($fqsen, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context $context = null)
    {
        if ($context === null) {
            $context = new \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context('');
        }
        if ($this->isFqsen($fqsen)) {
            return new \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Fqsen($fqsen);
        }
        return $this->resolvePartialStructuralElementName($fqsen, $context);
    }
    /**
     * Tests whether the given type is a Fully Qualified Structural Element Name.
     *
     * @param string $type
     *
     * @return bool
     */
    private function isFqsen($type)
    {
        return \strpos($type, self::OPERATOR_NAMESPACE) === 0;
    }
    /**
     * Resolves a partial Structural Element Name (i.e. `Reflection\DocBlock`) to its FQSEN representation
     * (i.e. `\phpDocumentor\Reflection\DocBlock`) based on the Namespace and aliases mentioned in the Context.
     *
     * @param string $type
     * @param Context $context
     *
     * @return Fqsen
     * @throws \InvalidArgumentException when type is not a valid FQSEN.
     */
    private function resolvePartialStructuralElementName($type, \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Types\Context $context)
    {
        $typeParts = \explode(self::OPERATOR_NAMESPACE, $type, 2);
        $namespaceAliases = $context->getNamespaceAliases();
        // if the first segment is not an alias; prepend namespace name and return
        if (!isset($namespaceAliases[$typeParts[0]])) {
            $namespace = $context->getNamespace();
            if ('' !== $namespace) {
                $namespace .= self::OPERATOR_NAMESPACE;
            }
            return new \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Fqsen(self::OPERATOR_NAMESPACE . $namespace . $type);
        }
        $typeParts[0] = $namespaceAliases[$typeParts[0]];
        return new \WP_Ultimo\Dependencies\phpDocumentor\Reflection\Fqsen(self::OPERATOR_NAMESPACE . \implode(self::OPERATOR_NAMESPACE, $typeParts));
    }
}
