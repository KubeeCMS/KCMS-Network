<?php

namespace WP_Ultimo\Dependencies\Gemz\Dns;

use React\Dns\Model\Record;
use React\Dns\Query\CoopExecutor;
use React\Dns\Query\Query;
use React\Dns\Query\TimeoutExecutor;
use React\EventLoop\Factory;
use React\Dns\Model\Message;
use WP_Ultimo\Dependencies\Gemz\Dns\Exceptions\InvalidArgument;
use React\Dns\Query\TcpTransportExecutor;
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
    protected $recordTypes = ['A' => Message::TYPE_A, 'CAA' => Message::TYPE_CAA, 'CNAME' => Message::TYPE_CNAME, 'SOA' => Message::TYPE_SOA, 'TXT' => Message::TYPE_TXT, 'MX' => Message::TYPE_MX, 'AAAA' => Message::TYPE_AAAA, 'SRV' => Message::TYPE_SRV, 'NS' => Message::TYPE_NS, 'PTR' => Message::TYPE_PTR, 'SSHFP' => Message::TYPE_SSHFP];
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
            throw InvalidArgument::domainIsNotValid($domain);
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
        $loop = Factory::create();
        foreach ($types as $type) {
            $query = new Query($this->domain, $this->recordTypes[$type], Message::CLASS_IN);
            $executor = new CoopExecutor(new TimeoutExecutor(new TcpTransportExecutor($this->nameserver, $loop), $this->timeout, $loop));
            $executor->query($query)->then(function (Message $message) use($type) {
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
            if ($value instanceof Record) {
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
                throw InvalidArgument::typeIsNotValid($type, $this->recordTypes);
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
