<?php

namespace WP_Ultimo\Dependencies\Gemz\Dns;

use WP_Ultimo\Dependencies\React\Dns\Model\Record;
use WP_Ultimo\Dependencies\React\Dns\Query\CoopExecutor;
use WP_Ultimo\Dependencies\React\Dns\Query\Query;
use WP_Ultimo\Dependencies\React\Dns\Query\TimeoutExecutor;
use WP_Ultimo\Dependencies\React\EventLoop\Factory;
use WP_Ultimo\Dependencies\React\Dns\Model\Message;
use WP_Ultimo\Dependencies\Gemz\Dns\Exceptions\InvalidArgument;
use WP_Ultimo\Dependencies\React\Dns\Query\TcpTransportExecutor;
class Dns
{
    /** @var string */
    protected $domain;
    /** @var string */
    protected $nameserver;
    /** @var array */
    protected $result;
    /** @var string */
    protected $defaultNameserver = '8.8.8.8';
    /** @var float */
    protected $timeout = 3.0;
    /** @var array */
    protected $recordTypes = ['A' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_A, 'CAA' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_CAA, 'CNAME' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_CNAME, 'SOA' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_SOA, 'TXT' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_TXT, 'MX' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_MX, 'AAAA' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_AAAA, 'SRV' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_SRV, 'NS' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_NS, 'PTR' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_PTR, 'SSHFP' => \WP_Ultimo\Dependencies\React\Dns\Model\Message::TYPE_SSHFP];
    public static function for(string $domain, string $nameserver = '') : self
    {
        return new self($domain, $nameserver);
    }
    public function __construct(string $domain, string $nameserver = '')
    {
        $this->domain = $this->sanitizeDomain($domain);
        $this->nameserver = $this->resolveNameserver($nameserver);
    }
    public function useNameserver(string $nameserver) : self
    {
        $this->nameserver = $this->resolveNameserver($nameserver);
        return $this;
    }
    public function allowedRecordTypes() : array
    {
        return \array_keys($this->recordTypes);
    }
    protected function resolveNameserver(string $nameserver) : string
    {
        return empty($nameserver) ? $this->defaultNameserver : $nameserver;
    }
    protected function sanitizeDomain(string $domain) : string
    {
        if (empty($domain)) {
            throw \WP_Ultimo\Dependencies\Gemz\Dns\Exceptions\InvalidArgument::domainIsNotValid($domain);
        }
        $domain = \str_replace(['http://', 'https://'], '', $domain);
        return \strtolower($domain);
    }
    /**
     * @param string|array ...$types
     *
     * @return array
     */
    public function records(...$types) : array
    {
        $types = $this->resolveTypes($types);
        $loop = \WP_Ultimo\Dependencies\React\EventLoop\Factory::create();
        foreach ($types as $type) {
            $query = new \WP_Ultimo\Dependencies\React\Dns\Query\Query($this->domain, $this->recordTypes[$type], \WP_Ultimo\Dependencies\React\Dns\Model\Message::CLASS_IN);
            $executor = new \WP_Ultimo\Dependencies\React\Dns\Query\CoopExecutor(new \WP_Ultimo\Dependencies\React\Dns\Query\TimeoutExecutor(new \WP_Ultimo\Dependencies\React\Dns\Query\TcpTransportExecutor($this->nameserver, $loop), $this->timeout, $loop));
            $executor->query($query)->then(function (\WP_Ultimo\Dependencies\React\Dns\Model\Message $message) use($type) {
                $this->addResult($type, $message->answers);
            });
        }
        $loop->run();
        return $this->result;
    }
    protected function addResult(string $type, array $values) : void
    {
        if (empty($values)) {
            $this->result[$type] = [];
        }
        foreach ($values as $value) {
            if ($value instanceof \WP_Ultimo\Dependencies\React\Dns\Model\Record) {
                $this->result[$type][] = ['ttl' => $value->ttl, 'data' => $value->data];
                continue;
            }
            $this->result[$type][] = $value;
        }
    }
    protected function resolveTypes(array $types = []) : array
    {
        if (empty($types)) {
            $types = \array_keys($this->recordTypes);
        }
        $types = \is_array($types[0] ?? null) ? $types[0] : $types;
        $types = \array_map('strtoupper', $types);
        foreach ($types as $type) {
            if (!\array_key_exists($type, $this->recordTypes)) {
                throw \WP_Ultimo\Dependencies\Gemz\Dns\Exceptions\InvalidArgument::typeIsNotValid($type, $this->recordTypes);
            }
        }
        return $types;
    }
    public function getNameserver() : string
    {
        return $this->nameserver;
    }
    public function getDomain() : string
    {
        return $this->domain;
    }
}
