<?php

$oid_prefixes = array(
  'tapes' => array(
    'DDS-3' => $oid_prefix    . '.3.3.1.3'
  , 'DDS-4' => $oid_prefix    . '.3.3.1.4'
  , 'SDLT-320' => $oid_prefix . '.3.3.2.1'
  , 'LTO-3' => $oid_prefix    . '.3.3.3.3'
  , 'LTO-4' => $oid_prefix    . '.3.3.3.4'
  )
, 'backupchunks' => $oid_prefix . '.3'  // append '.YYYYMMDDhhmmss.N'
, 'disks' => $oid_prefix . '.4'
, 'hosts' => $oid_prefix . '.5'
)

?>
