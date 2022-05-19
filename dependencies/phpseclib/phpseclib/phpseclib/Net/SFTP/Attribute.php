<?php

namespace phpseclib3\Net\SFTP;

/**
 * http://tools.ietf.org/html/draft-ietf-secsh-filexfer-13#section-7.1
 * the order, in this case, matters quite a lot - see \phpseclib3\Net\SFTP::_parseAttributes() to understand why
 *
 * @internal
 */
abstract class Attribute
{
    const SIZE = 0x1;
    const UIDGID = 0x2;
    // defined in SFTPv3, removed in SFTPv4+
    const OWNERGROUP = 0x80;
    // defined in SFTPv4+
    const PERMISSIONS = 0x4;
    const ACCESSTIME = 0x8;
    const CREATETIME = 0x10;
    // SFTPv4+
    const MODIFYTIME = 0x20;
    const ACL = 0x40;
    const SUBSECOND_TIMES = 0x100;
    const BITS = 0x200;
    // SFTPv5+
    const ALLOCATION_SIZE = 0x400;
    // SFTPv6+
    const TEXT_HINT = 0x800;
    const MIME_TYPE = 0x1000;
    const LINK_COUNT = 0x2000;
    const UNTRANSLATED_NAME = 0x4000;
    const CTIME = 0x8000;
    // 0x80000000 will yield a floating point on 32-bit systems and converting floating points to integers
    // yields inconsistent behavior depending on how php is compiled.  so we left shift -1 (which, in
    // two's compliment, consists of all 1 bits) by 31.  on 64-bit systems this'll yield 0xFFFFFFFF80000000.
    // that's not a problem, however, and 'anded' and a 32-bit number, as all the leading 1 bits are ignored.
    const EXTENDED = -1 << 31 & 0xffffffff;
    /**
     * @return array
     */
    public static function getConstants()
    {
        $reflectionClass = new \ReflectionClass(static::class);
        return $reflectionClass->getConstants();
    }
}
