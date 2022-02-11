<?php

/* ----------------------------------------------------------
  Env
---------------------------------------------------------- */

/* PHP
-------------------------- */

$phpversion = phpversion();
if (version_compare($phpversion, '7.4.10', '<')) {
    $wputools_errors[] = sprintf('PHP version %s is too old !', $phpversion);
}

/* Mail
-------------------------- */

$sentmail = mail('test@example.com', 'subject', 'message');
if (!$sentmail) {
    $wputools_errors[] = sprintf('PHP mail function doesn’t seem to work !', $phpversion);
}
