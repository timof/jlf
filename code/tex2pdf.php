<?php

$texencode_maps = array(
  '/\\\\/' => '\\backslash'
// , '/\\&quot;/' => "''"
// , '/\\&#039;/' => "'"
, '/([$%_#~])/' => '\\\\$1'
// , '/\\&amp;/' => '\\&'
, '/\\&/' => '\\&'
// , '/\\&lt;/' => '$<$'
// , '/\\&gt;/' => '$>$'
, '/[}]/' => '$\}$'
, '/[{]/' => '$\{$'
, '/ä/' => '{\"a}'
, '/Ä/' => '{\"A}'
, '/ö/' => '{\"o}'
, '/Ö/' => '{\"O}'
, '/ü/' => '{\"u}'
, '/Ü/' => '{\"U}'
, '/ß/' => '{\ss}'
, '/\\\\backslash/' => '\\$\\backslash{}\\$'
);

function tex_encode( $s ) {
  foreach( $GLOBALS['texencode_maps'] as $pattern => $to ) {
    $s = preg_replace( $pattern, $to, $s );
  }
  $len = strlen( $s );
  $i = 0;
  $out = '';
  while( $i < $len ) {
    $c = $s[ $i ];
    $n = ord( $c );
    $bytes = 1;
    if( $n < 128 ) {
      // skip most control characters:
      if( $n < 32 ) {
        switch( $n ) {
          case  9: // tab
            $out .= ' ';
            break;
          case 10: // lf
            $out .= '\\newline{}';
            break;
          case 13: // cr
            break;
          default:
            break;
        }
      } else {
        $out .= $c;
      }
    } else {
      // skip remaining utf-8 characters:
      if( $n > 247 ) continue;
      elseif( $n > 239 ) $bytes = 4;
      elseif( $n > 223 ) $bytes = 3;
      elseif( $n > 191 ) $bytes = 2;
      else continue;
    }
    $i += $bytes;
  }
  return $out;
}

function tex_insert( $template, $row = array() ) {
  $needles = array();
  $replace = array();
  while( ( $n = strpos( $template, '%#I:' ) ) ) {
    $head = substr( $template, 0, $n );
    preg_match( '/^%#I:([^;])*;(.*)$/', substr( $template, $n ), /* & */ $matches );
    $insertion = tex_insert( file_get_contents( "{$GLOBALS['jlf_application_name']}/textemplates/{$matches[1]}" ) );
    $template = $head . $insertion . $matches[ 2 ];
    unset( $matches );
  }
  foreach( $row as $key => $value ) {
    $needles[] = "%#R:$key;"; // cannot occur in $value after tex_encode
    $replace[] = tex_encode( $value );
  }
  return( str_replace( $needles, $replace, $template ) );
}

// PDF generation (via pdflatex):
//
//
function tex2pdf( $tex, $opts = array() ) {
  $opts = parameters_explode( $opts );
  if( adefault( $opts, 'loadfile' ) ) {
    $tex = file_get_contents( "{$GLOBALS['jlf_application_name']}/textemplates/$tex" );
    need( $tex, 'failed to load TeX template' );
  }
  if( ( $row = adefault( $opts, 'row' ) ) ) {
    $tex = tex_insert( $tex, $row );
  }
  $cwd = getcwd();
  need( $tmpdir = get_tmp_working_dir() );
  need( chdir( $tmpdir ) );
  file_put_contents( 'tex2pdf.tex', $tex );
  // exec( "TEXINPUTS=$cwd/textemplates pdflatex tex2pdf.tex", /* & */ $output, /* & */ $rv );
  exec( "pdflatex tex2pdf.tex", /* & */ $output, /* & */ $rv );
  if( ! $rv ) {
    $pdf = file_get_contents( 'tex2pdf.pdf' );
    // open_div( 'ok', '', 'ok: '.  implode( ' ', $output ) );
  } else {
    open_div( 'warn', '', 'error: '. file_get_contents( 'tex2pdf.log' ) );
    logger( 'tex2pdf failed', LOG_LEVEL_ERROR, LOG_FLAG_CODE | LOG_FLAG_USER, 'tex2pdf' ); 
    $pdf = false;
  }
  @ unlink( 'tex2pdf.tex' );
  @ unlink( 'tex2pdf.aux' );
  @ unlink( 'tex2pdf.log' );
  @ unlink( 'tex2pdf.pdf' );
  chdir( $cwd );
  rmdir( $tmpdir );

  return $pdf;
}


?>
