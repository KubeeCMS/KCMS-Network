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
use WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Reference;
/**
 * Adds tagged translation.formatter services to translation writer.
 */
class TranslationDumperPass implements \WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private $writerServiceId;
    private $dumperTag;
    public function __construct(string $writerServiceId = 'translation.writer', string $dumperTag = 'translation.dumper')
    {
        $this->writerServiceId = $writerServiceId;
        $this->dumperTag = $dumperTag;
    }
    public function process(\WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->writerServiceId)) {
            return;
        }
        $definition = $container->getDefinition($this->writerServiceId);
        foreach ($container->findTaggedServiceIds($this->dumperTag, \true) as $id => $attributes) {
            $definition->addMethodCall('addDumper', [$attributes[0]['alias'], new \WP_Ultimo\Dependencies\Symfony\Component\DependencyInjection\Reference($id)]);
        }
    }
}
