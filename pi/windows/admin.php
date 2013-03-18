<?php

echo "hello, world!";

// $mitarbeiter = array(
//     array(
//     'gn' => 'Ronny'
//   , 'sn' => 'Schmidt'
//   , 'gruppenkurzname' => 'photonik'
//   , 'telephonenumber' => '+49 331 977 5495'
//   , 'facsimiletelephonenumber' => '+49 331 977 5576'
//   )
//   , array(
//     'gn' => 'Ronny'
//   , 'sn' => 'Habermann'
//   , 'telephonenumber' => '+49 331 977 5634'
//   , 'gruppenkurzname' => 'exphy'
//   )
// );
// 
// $n = 1;
// foreach( $mitarbeiter as $m ) {
//   $gruppe = sql_one_group( array( 'kurzname' => $m['gruppenkurzname'] ), NULL );
//   if( ! $gruppe ) {
//     open_div( 'warn', "not found: ".$m['gruppenkurzname'].' '.$m['sn'] );
//   }
//   unset( $m['gruppenkurzname'] );
//   $p['title'] = adefault( $m, 'title', '' );
//   $sn = adefault( $m, 'sn', '' );
//   $gn = adefault( $m, 'gn', '' );
//   $p['sn'] = $sn;
//   $p['gn'] = $gn;
//   
//   $a['groups_id'] = $gruppe['groups_id'];
//   $a['roomnumber'] = adefault( $m, 'roomnumber', '' );
//   $a['telephonenumber'] = adefault( $m, 'telephonenumber', '' );
//   $a['facsimiletelephonenumber'] = adefault( $m, 'facsimiletelephonenumber', '' );
//   $a['mail'] = adefault( $m, 'mail', '' );
//   $a['priority'] = 0;
// 
//   unset( $out );
//   exec( "ldapsearch -x 'cn=$gn $sn' roomnumber | grep '^roomNumber: '", & $out );
//   if( preg_match( '/^roomNumber: (.*)$/', $out[ 0 ], $matches ) )
//     $a['roomnumber'] = $matches[ 1 ];
// 
//   debug( $p, 'p' );
//   debug( $a, 'a' );
//   debug( $out, 'out' );
//   $people_id = sql_insert( 'people', $p );
//   $a['people_id'] = $people_id;
//   sql_insert( 'affiliations', $a );
// 
//   if( $n++ > 100 )
//     break;
// }

echo 'a'.strtotime( "2012-W17-1" ).'z';

// debug( $mitarbeiter, 'mitarbeiter' );

$filters = array();


if( have_minimum_person_priv( PERSON_PRIV_ADMIN ) ) {
  medskip();
// $sessions = sql_sessions( "atime<$then" );
//  open_fieldset( 'small_form', 'maintenance', 'on' );
//    persistent_vars_view( "name=thread_atime,value<$then" );
//  close_fieldset();
 
  open_fieldset( 'small_form medskip', 'persistent variables', 'off' );
    persistent_vars_view( $filters );
  close_fieldset();
}
?>
