<?php

$oid_prefixes = array(
  'tapes' => array(
    'dds-3' => $oid_prefix    . '.3.3.1.3'
  , 'dds-4' => $oid_prefix    . '.3.3.1.4'
  , 'sdlt-320' => $oid_prefix . '.3.3.2.1'
  , 'lto-3' => $oid_prefix    . '.3.3.3.3'
  , 'lto-4' => $oid_prefix    . '.3.3.3.4'
  )
, 'backupchunks' => $oid_prefix . '.3'  // append '.YYYYMMDDhhmmss.N'
, 'disks' => $oid_prefix . '.4'
, 'hosts' => $oid_prefix . '.5'
)

?>
