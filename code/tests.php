<?

// $f = array( '||', 'art=b', array( '!', 'art,betrag>0' ), '7', 'beleg !=  none , art =  S' );
// // prettydump( $f, 'f' );
// $cf = sql_canonicalize_filters( 'people', $f, 'posten' );
// // prettydump( $cf, 'cf' );
// $sql = sql_filters2expression( $cf );
// prettydump( $sql, 'sql' );
// 
// $f2 = array( 'betrag = 0', 'art' => 'Y', $cf );
// prettydump( $f2, 'f2' );
// 
// $cf2 = sql_canonicalize_filters( 'people', $f2, 'posten' );
// prettydump( $cf2, 'cf2' );
// 
// $sql2 = sql_filters2expression( $cf2 );
// prettydump( $sql2, 'sql2' );
// 

?>

$a = 'bla';
$aa = array( '7' => & $a );
$b = & $a;
$ab = $aa;

function xxx( $in ) {
  $out = tree_merge( array( '13' => 'foo' ), $in );
  return $out;
}
$c = xxx( $a );
$ac = xxx( $aa );

$a = 'blubb';


debug( $a, 'a' );
debug( $b, 'b' );
debug( $c, 'c' );
debug( $aa, 'aa' );
debug( $ab, 'ab' );
debug( $ac, 'ac' );
