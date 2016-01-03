<?php

# texencode_maps:
# escaping is tricky here, as it is done twice (by php and by the pcre library):
# - \\\\ means a literal backslash to pcre
# - \\ means an escaping backslash to pcre
#
$texencode_maps = array(
  '/\\\\/' => '\\\\backslash'   // \  -> \backslash
, '/([$%_#~])/' => '\\\\$1'   // $  -> \$
, '/\\&/' => '\\\\&'            // &  -> \&
, '/[}]/' => '$\\\\}$'
, '/[{]/' => '$\\\\{$'
, '/ä/' => '{\\\\"a}'
, '/Ä/' => '{\\\\"A}'
, '/ö/' => '{\\\\"o}'
, '/Ö/' => '{\\\\"O}'
, '/ü/' => '{\\\\"u}'
, '/Ü/' => '{\\\\"U}'
, '/ß/' => '{\\\\ss}'
, '/\\\\backslash/' => '\\$\\\\backslash{}\\$'  // \backslash -> $\backslash$
, '/'.TEX_BS.'/' => '\\\\'
, '/'.TEX_LBR.'/' => '{'
, '/'.TEX_RBR.'/' => '}'
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
  while( ( $n = strpos( $template, '%#I:' ) ) !== false ) {
    $head = substr( $template, 0, $n );
    $tail = substr( $template, $n );
    preg_match( '/^%#I:([^;]*);(.*)$/s', $tail, /* & */ $matches );
    if( is_readable( "./{$GLOBALS['jlf_application_name']}/textemplates/{$matches[1]}" ) ) {
      $insertion = tex_insert( file_get_contents( "./{$GLOBALS['jlf_application_name']}/textemplates/{$matches[1]}" ) );
    } if( is_readable( "./code/textemplates/{$matches[1]}" ) ) {
      $insertion = tex_insert( file_get_contents( "./code/textemplates/{$matches[1]}" ) );
    } else {
      $insertion = "failed to insert: ({$matches[1]})";
    }
    $template = $head . $insertion . $matches[ 2 ];
    unset( $matches );
  }
  $needles = array(
    '%#R:_GLOBAL_font_size;'
  , '%#R:_GLOBAL_bannertext1;'
  , '%#R:_GLOBAL_bannertext2;'
  );
  $replace = array(
    adefault( $GLOBALS, 'font_size', '11' )
  , tex_encode( adefault( $GLOBALS, 'bannertext1', '' ) )
  , tex_encode( adefault( $GLOBALS, 'bannertext2', '' ) )
  );
  foreach( $row as $key => $value ) {
    $needles[] = "%#R:$key;"; // cannot occur in $value after tex_encode
    $replace[] = tex_encode( $value );
  }
  $template = str_replace( $needles, $replace, $template );
  return $template;
}

// PDF generation (via pdflatex):
//
function tex2pdf( $tex, $opts = array() ) {
  global $debug;
  $opts = parameters_explode( $opts );
  if( adefault( $opts, 'loadfile' ) ) {
    $tex = file_get_contents( "./{$GLOBALS['jlf_application_name']}/textemplates/$tex" );
    need( $tex, 'failed to load TeX template' );
  }
  $tex = tex_insert( $tex, adefault( $opts, 'row', array() ) );
  $cwd = getcwd();
  need( $tmpdir = get_tmp_working_dir() );
  need( chdir( $tmpdir ) );
  file_put_contents( 'tex2pdf.tex', $tex );

  exec( "pdflatex tex2pdf.tex", /* & */ $output, /* & */ $rv );
  if( ! $rv ) { // re-do, to get total page numbering
    unset( $output );
    exec( "pdflatex tex2pdf.tex", /* & */ $output, /* & */ $rv );
  }

  if( ! $rv ) {
    $pdf = file_get_contents( 'tex2pdf.pdf' );
    // open_div( 'ok', 'ok: '.  implode( ' ', $output ) );
  } else {
    // FIXME: open_div makes no sense with format='pdf':
    open_div( 'warn', 'error: '. file_get_contents( 'tex2pdf.log' ) );
    logger( 'tex2pdf failed', LOG_LEVEL_ERROR, LOG_FLAG_CODE | LOG_FLAG_USER, 'tex2pdf' ); 
    $pdf = false;
  }
  if( $debug && $rv ) {
    chdir( $cwd );
  } else {
    @ unlink( 'tex2pdf.log' );
    @ unlink( 'tex2pdf.tex' );
    @ unlink( 'tex2pdf.aux' );
    @ unlink( 'tex2pdf.pdf' );
    @ unlink( 'tex2pdf.out' );
    chdir( $cwd );
    rmdir( $tmpdir );
  }

  return $pdf;
}


?>
