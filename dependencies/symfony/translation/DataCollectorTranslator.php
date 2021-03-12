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

use WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use WP_Ultimo\Dependencies\Symfony\Contracts\Translation\LocaleAwareInterface;
use WP_Ultimo\Dependencies\Symfony\Contracts\Translation\TranslatorInterface;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class DataCollectorTranslator implements \WP_Ultimo\Dependencies\Symfony\Contracts\Translation\TranslatorInterface, \Symfony\Component\Translation\TranslatorBagInterface, \WP_Ultimo\Dependencies\Symfony\Contracts\Translation\LocaleAwareInterface, \WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface
{
    const MESSAGE_DEFINED = 0;
    const MESSAGE_MISSING = 1;
    const MESSAGE_EQUALS_FALLBACK = 2;
    /**
     * @var TranslatorInterface|TranslatorBagInterface
     */
    private $translator;
    private $messages = [];
    /**
     * @param TranslatorInterface $translator The translator must implement TranslatorBagInterface
     */
    public function __construct(\WP_Ultimo\Dependencies\Symfony\Contracts\Translation\TranslatorInterface $translator)
    {
        if (!$translator instanceof \Symfony\Component\Translation\TranslatorBagInterface || !$translator instanceof \WP_Ultimo\Dependencies\Symfony\Contracts\Translation\LocaleAwareInterface) {
            throw new \Symfony\Component\Translation\Exception\InvalidArgumentException(\sprintf('The Translator "%s" must implement TranslatorInterface, TranslatorBagInterface and LocaleAwareInterface.', get_debug_type($translator)));
        }
        $this->translator = $translator;
    }
    /**
     * {@inheritdoc}
     */
    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        $trans = $this->translator->trans($id = (string) $id, $parameters, $domain, $locale);
        $this->collectMessage($locale, $domain, $id, $trans, $parameters);
        return $trans;
    }
    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale)
    {
        $this->translator->setLocale($locale);
    }
    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }
    /**
     * {@inheritdoc}
     */
    public function getCatalogue(string $locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }
    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function warmUp(string $cacheDir)
    {
        if ($this->translator instanceof \WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface) {
            return (array) $this->translator->warmUp($cacheDir);
        }
        return [];
    }
    /**
     * Gets the fallback locales.
     *
     * @return array The fallback locales
     */
    public function getFallbackLocales()
    {
        if ($this->translator instanceof \Symfony\Component\Translation\Translator || \method_exists($this->translator, 'getFallbackLocales')) {
            return $this->translator->getFallbackLocales();
        }
        return [];
    }
    /**
     * Passes through all unknown calls onto the translator object.
     */
    public function __call(string $method, array $args)
    {
        return $this->translator->{$method}(...$args);
    }
    /**
     * @return array
     */
    public function getCollectedMessages()
    {
        return $this->messages;
    }
    private function collectMessage(?string $locale, ?string $domain, string $id, string $translation, ?array $parameters = [])
    {
        if (null === $domain) {
            $domain = 'messages';
        }
        $catalogue = $this->translator->getCatalogue($locale);
        $locale = $catalogue->getLocale();
        $fallbackLocale = null;
        if ($catalogue->defines($id, $domain)) {
            $state = self::MESSAGE_DEFINED;
        } elseif ($catalogue->has($id, $domain)) {
            $state = self::MESSAGE_EQUALS_FALLBACK;
            $fallbackCatalogue = $catalogue->getFallbackCatalogue();
            while ($fallbackCatalogue) {
                if ($fallbackCatalogue->defines($id, $domain)) {
                    $fallbackLocale = $fallbackCatalogue->getLocale();
                    break;
                }
                $fallbackCatalogue = $fallbackCatalogue->getFallbackCatalogue();
            }
        } else {
            $state = self::MESSAGE_MISSING;
        }
        $this->messages[] = ['locale' => $locale, 'fallbackLocale' => $fallbackLocale, 'domain' => $domain, 'id' => $id, 'translation' => $translation, 'parameters' => $parameters, 'state' => $state, 'transChoiceNumber' => isset($parameters['%count%']) && \is_numeric($parameters['%count%']) ? $parameters['%count%'] : null];
    }
}
