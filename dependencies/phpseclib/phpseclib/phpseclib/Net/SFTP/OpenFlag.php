<?php

namespace phpseclib3\Net\SFTP;

/**
 * http://tools.ietf.org/html/draft-ietf-secsh-filexfer-04#section-6.3
 * the flag definitions change somewhat in SFTPv5+.  if SFTPv5+ support is added to this library, maybe name
 * the array for that $this->open5_flags and similarly alter the constant names.
 *
 * @internal
 */
abstract class OpenFlag
{
    const READ = 0x1;
    const WRITE = 0x2;
    const APPEND = 0x4;
    const CREATE = 0x8;
    const TRUNCATE = 0x10;
    const EXCL = 0x20;
    const TEXT = 0x40;
    // defined in SFTPv4
}
