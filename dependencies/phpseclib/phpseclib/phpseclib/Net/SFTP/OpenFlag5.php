<?php

namespace phpseclib3\Net\SFTP;

/**
 * SFTPv5+ changed the flags up: https://datatracker.ietf.org/doc/html/draft-ietf-secsh-filexfer-13#section-8.1.1.3
 *
 * @internal
 */
abstract class OpenFlag5
{
    // when SSH_FXF_ACCESS_DISPOSITION is a 3 bit field that controls how the file is opened
    const CREATE_NEW = 0x0;
    const CREATE_TRUNCATE = 0x1;
    const OPEN_EXISTING = 0x2;
    const OPEN_OR_CREATE = 0x3;
    const TRUNCATE_EXISTING = 0x4;
    // the rest of the flags are not supported
    const APPEND_DATA = 0x8;
    // "the offset field of SS_FXP_WRITE requests is ignored"
    const APPEND_DATA_ATOMIC = 0x10;
    const TEXT_MODE = 0x20;
    const BLOCK_READ = 0x40;
    const BLOCK_WRITE = 0x80;
    const BLOCK_DELETE = 0x100;
    const BLOCK_ADVISORY = 0x200;
    const NOFOLLOW = 0x400;
    const DELETE_ON_CLOSE = 0x800;
    const ACCESS_AUDIT_ALARM_INFO = 0x1000;
    const ACCESS_BACKUP = 0x2000;
    const BACKUP_STREAM = 0x4000;
    const OVERRIDE_OWNER = 0x8000;
}
