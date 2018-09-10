<?php

namespace Marcz\Search\Extensions;

use Extension;

class SafeguardMember extends Extension
{
    protected $unsafeColumns = [
        'TempIDHash',
        'TempIDExpired',
        'Password',
        'AutoLoginHash',
        'AutoLoginExpired',
        'PasswordEncryption',
        'Salt',
        'PasswordExpiry',
        'LockedOutUntil',
        'FailedLoginCount'
    ];

    public function updateExport(&$data, &$clientClassName)
    {
        foreach ($this->unsafeColumns as $column) {
            if (isset($data[$column])) {
                unset($data[$column]);
            }
        }
    }
}
