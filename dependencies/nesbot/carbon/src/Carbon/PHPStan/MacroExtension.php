<?php

namespace WP_Ultimo\Dependencies\Carbon\PHPStan;

use WP_Ultimo\Dependencies\PHPStan\Reflection\ClassReflection;
use WP_Ultimo\Dependencies\PHPStan\Reflection\MethodReflection;
use WP_Ultimo\Dependencies\PHPStan\Reflection\MethodsClassReflectionExtension;
use WP_Ultimo\Dependencies\PHPStan\Reflection\Php\PhpMethodReflectionFactory;
use WP_Ultimo\Dependencies\PHPStan\Type\TypehintHelper;
/**
 * Class MacroExtension.
 *
 * @codeCoverageIgnore Pure PHPStan wrapper.
 */
final class MacroExtension implements \WP_Ultimo\Dependencies\PHPStan\Reflection\MethodsClassReflectionExtension
{
    /**
     * @var PhpMethodReflectionFactory
     */
    protected $methodReflectionFactory;
    /**
     * @var MacroScanner
     */
    protected $scanner;
    /**
     * Extension constructor.
     *
     * @param PhpMethodReflectionFactory $methodReflectionFactory
     */
    public function __construct(\WP_Ultimo\Dependencies\PHPStan\Reflection\Php\PhpMethodReflectionFactory $methodReflectionFactory)
    {
        $this->scanner = new \WP_Ultimo\Dependencies\Carbon\PHPStan\MacroScanner();
        $this->methodReflectionFactory = $methodReflectionFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function hasMethod(\WP_Ultimo\Dependencies\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        return $this->scanner->hasMethod($classReflection->getName(), $methodName);
    }
    /**
     * {@inheritdoc}
     */
    public function getMethod(\WP_Ultimo\Dependencies\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : \WP_Ultimo\Dependencies\PHPStan\Reflection\MethodReflection
    {
        $builtinMacro = $this->scanner->getMethod($classReflection->getName(), $methodName);
        return $this->methodReflectionFactory->create($classReflection, null, $builtinMacro, $classReflection->getActiveTemplateTypeMap(), [], \WP_Ultimo\Dependencies\PHPStan\Type\TypehintHelper::decideTypeFromReflection($builtinMacro->getReturnType()), null, null, $builtinMacro->isDeprecated()->yes(), $builtinMacro->isInternal(), $builtinMacro->isFinal(), $builtinMacro->getDocComment());
    }
}
