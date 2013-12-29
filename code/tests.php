<?

foreach( array( 'drop', 'lock', 'edit', 'uparrow', 'downarrow', 'plus', 'equal', 'close', 'open', 'plain' ) as $s ) {
//// 'record', 'browse', 'people', 'cash', 'chart',
  open_div();
    open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:6pt", '' );
    open_tag( 'a', "class=$s button quads tinyskips,style=font-size:6pt", "test: $s" );
    open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:10pt;", '' );
    open_tag( 'a', "class=$s button quads tinyskips,style=font-size:10pt;", "test: $s" );
    open_tag( 'a', "class=$s big button quads tinyskips,style=font-size:10pt;", "test: $s" );
  close_div();
}
foreach( array( 'fant', 'file', 'leftarrow', 'rightarrow', 'outlink', 'plain' ) as $s ) {
  open_div();
    open_tag( 'a', "class=$s quads tinyskips,style=font-size:6pt;outline:1px dashed red;", '' );
    open_tag( 'a', "class=$s quads tinyskips,style=font-size:6pt;outline:1px dashed red;", "test: $s" );
    qquad();
    open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:6pt", '' );
    open_tag( 'a', "class=$s quads tinyskips,style=font-size:6pt", "test: $s" );
    open_tag( 'a', "class=$s icon quads tinyskips,style=font-size:10pt", '' );
    open_tag( 'a', "class=$s quads tinyskips,style=font-size:10pt", "test: $s" );
  close_div();
}

return;

die(42);

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

// references:

// array members which are references keep this property even if 
// assigned and passed in and out of functions (like $a['y']), but
// not with element-wise assignment ($q):

$x = 'X';
$y = 'Y';
$a = array( 'x' => 'x', 'y' => & $y, 'z' => 'z' );

function xxx( $in ) {
  $tmp = 'tmp';
  $in['x'] = & $tmp;
  $GLOBALS['c'] = $in;
  $r = $in;
  $r['new'] = 'new';
  return $r;
}

$b = $a;
$r = xxx( $a );

$p = $r;
$q = array();
foreach( $r as $i => $v ) {
  $q[ $i ] = $v;
}

debug( $a, 'a' );
debug( $b, 'b' );
debug( $c, 'c' );

debug( $r, 'r' );
debug( $p, 'p' );
debug( $q, 'q' );

$r['x'] = 'smurf';
$r['y'] = 'u';
$a['z'] = 'w';

medskip();
debug( $a, 'a' );
debug( $b, 'b' );
debug( $c, 'c' );
debug( $r, 'r' );
debug( $p, 'p' );
debug( $q, 'q' );



// null: after $a = null: isset( $a ) === false !!!

?>

