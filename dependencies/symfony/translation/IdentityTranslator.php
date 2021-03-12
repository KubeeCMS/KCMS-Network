<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Symfony\Component\Translation;

use WP_Ultimo\Dependencies\Symfony\Contracts\Translation\LocaleAwareInterface;
use WP_Ultimo\Dependencies\Symfony\Contracts\Translation\TranslatorInterface;
use WP_Ultimo\Dependencies\Symfony\Contracts\Translation\TranslatorTrait;
/**
 * IdentityTranslator does not translate anything.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IdentityTranslator implements \WP_Ultimo\Dependencies\Symfony\Contracts\Translation\TranslatorInterface, \WP_Ultimo\Dependencies\Symfony\Contracts\Translation\LocaleAwareInterface
{
    use TranslatorTrait;
}
