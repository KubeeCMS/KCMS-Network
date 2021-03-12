<?php

namespace WP_Ultimo\Dependencies\Carbon\PHPStan;

use WP_Ultimo\Dependencies\Carbon\CarbonInterface;
use ReflectionClass;
use ReflectionException;
final class MacroScanner
{
    /**
     * Return true if the given pair class-method is a Carbon macro.
     *
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @param string $methodName
     *
     * @return bool
     */
    public function hasMethod(string $className, string $methodName) : bool
    {
        return \is_a($className, \WP_Ultimo\Dependencies\Carbon\CarbonInterface::class, \true) && \is_callable([$className, 'hasMacro']) && $className::hasMacro($methodName);
    }
    /**
     * Return the Macro for a given pair class-method.
     *
     * @param string $className
     * @phpstan-param class-string $className
     *
     * @param string $methodName
     *
     * @throws ReflectionException
     *
     * @return Macro
     */
    public function getMethod(string $className, string $methodName) : \WP_Ultimo\Dependencies\Carbon\PHPStan\Macro
    {
        $reflectionClass = new \ReflectionClass($className);
        $property = $reflectionClass->getProperty('globalMacros');
        $property->setAccessible(\true);
        $macro = $property->getValue()[$methodName];
        return new \WP_Ultimo\Dependencies\Carbon\PHPStan\Macro($className, $methodName, $macro);
    }
}
