<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Symfony\Component\Translation\DependencyInjection;

use WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\ContainerBuilder;
use WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Reference;
/**
 * Adds tagged translation.extractor services to translation extractor.
 */
class TranslationExtractorPass implements \WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private $extractorServiceId;
    private $extractorTag;
    public function __construct(string $extractorServiceId = 'translation.extractor', string $extractorTag = 'translation.extractor')
    {
        $this->extractorServiceId = $extractorServiceId;
        $this->extractorTag = $extractorTag;
    }
    public function process(\WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->extractorServiceId)) {
            return;
        }
        $definition = $container->getDefinition($this->extractorServiceId);
        foreach ($container->findTaggedServiceIds($this->extractorTag, \true) as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new \WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('The alias for the tag "translation.extractor" of service "%s" must be set.', $id));
            }
            $definition->addMethodCall('addExtractor', [$attributes[0]['alias'], new \WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Reference($id)]);
        }
    }
}
