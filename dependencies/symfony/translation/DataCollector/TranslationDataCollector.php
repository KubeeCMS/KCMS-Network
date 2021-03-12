<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Symfony\Component\Translation\DataCollector;

use WP_Ultimo\Dependencies\Symfony\Component\HttpFoundation\Request;
use WP_Ultimo\Dependencies\Symfony\Component\HttpFoundation\Response;
use WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\DataCollector\DataCollector;
use WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use WP_Ultimo\Dependencies\Symfony\Component\VarDumper\Cloner\Data;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 *
 * @final
 */
class TranslationDataCollector extends \WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\DataCollector\DataCollector implements \WP_Ultimo\Dependencies\Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface
{
    private $translator;
    public function __construct(\Symfony\Component\Translation\DataCollectorTranslator $translator)
    {
        $this->translator = $translator;
    }
    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        $messages = $this->sanitizeCollectedMessages($this->translator->getCollectedMessages());
        $this->data += $this->computeCount($messages);
        $this->data['messages'] = $messages;
        $this->data = $this->cloneVar($this->data);
    }
    /**
     * {@inheritdoc}
     */
    public function collect(\WP_Ultimo\Dependencies\Symfony\Component\HttpFoundation\Request $request, \WP_Ultimo\Dependencies\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception = null)
    {
        $this->data['locale'] = $this->translator->getLocale();
        $this->data['fallback_locales'] = $this->translator->getFallbackLocales();
    }
    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
    }
    /**
     * @return array|Data
     */
    public function getMessages()
    {
        return isset($this->data['messages']) ? $this->data['messages'] : [];
    }
    /**
     * @return int
     */
    public function getCountMissings()
    {
        return isset($this->data[\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_MISSING]) ? $this->data[\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_MISSING] : 0;
    }
    /**
     * @return int
     */
    public function getCountFallbacks()
    {
        return isset($this->data[\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK]) ? $this->data[\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK] : 0;
    }
    /**
     * @return int
     */
    public function getCountDefines()
    {
        return isset($this->data[\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_DEFINED]) ? $this->data[\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_DEFINED] : 0;
    }
    public function getLocale()
    {
        return !empty($this->data['locale']) ? $this->data['locale'] : null;
    }
    /**
     * @internal
     */
    public function getFallbackLocales()
    {
        return isset($this->data['fallback_locales']) && \count($this->data['fallback_locales']) > 0 ? $this->data['fallback_locales'] : [];
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translation';
    }
    private function sanitizeCollectedMessages(array $messages)
    {
        $result = [];
        foreach ($messages as $key => $message) {
            $messageId = $message['locale'] . $message['domain'] . $message['id'];
            if (!isset($result[$messageId])) {
                $message['count'] = 1;
                $message['parameters'] = !empty($message['parameters']) ? [$message['parameters']] : [];
                $messages[$key]['translation'] = $this->sanitizeString($message['translation']);
                $result[$messageId] = $message;
            } else {
                if (!empty($message['parameters'])) {
                    $result[$messageId]['parameters'][] = $message['parameters'];
                }
                ++$result[$messageId]['count'];
            }
            unset($messages[$key]);
        }
        return $result;
    }
    private function computeCount(array $messages)
    {
        $count = [\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_DEFINED => 0, \Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_MISSING => 0, \Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK => 0];
        foreach ($messages as $message) {
            ++$count[$message['state']];
        }
        return $count;
    }
    private function sanitizeString(string $string, int $length = 80)
    {
        $string = \trim(\preg_replace('/\\s+/', ' ', $string));
        if (\false !== ($encoding = \mb_detect_encoding($string, null, \true))) {
            if (\mb_strlen($string, $encoding) > $length) {
                return \mb_substr($string, 0, $length - 3, $encoding) . '...';
            }
        } elseif (\strlen($string) > $length) {
            return \substr($string, 0, $length - 3) . '...';
        }
        return $string;
    }
}