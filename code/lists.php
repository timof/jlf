<?php
//
// lists.php
//
// for lists of tabular data; in particular, this is to support the following features:
// - toggling columns on and off
// - sorting columns by clicking on the th element
// - output a list in various formats (html <table>, pdf, csv)
//


$current_list = NULL;

// orderby_join(): update an "order by" expression (for sorting lists by clicking on th elements):
// - explode and return array of order keys from $orderby string
// - if $ordernew is non-empty, it will become primary order key
// - if $ordernew is already primary key, sort order will be reversed for this key (indicated by '-R'-suffix)
// - if $ordernew is anywhere else in $orderby, this occurence will be deleted
//
function orderby_join( $orderby = '', $ordernew = '' ) {
  if( $orderby ) {
    $order_keys = explode( ',', $orderby );
    if( $ordernew ) {
      if( $order_keys[0] === $ordernew ) {
        $order_keys[0] = "$ordernew-R";
      } else if( $order_keys[0] === "$ordernew-R" ) {
        $order_keys[0] = "$ordernew";
      } else {
        $order_keys_new[] = $ordernew;
        foreach( $order_keys as $key ) {
          if( $key === $ordernew || $key === "$ordernew-R" )
            continue;
          $order_keys_new[] = $key;
        }
        $order_keys = $order_keys_new;
      }
    }
    return $order_keys;
  } else {
    return $ordernew ? array( $ordernew ) : array();
  }
}


////////////////////////////////////////////
// list handling: must be done in steps:
//   - handle_list_options(): will (among other things) compute and return 'orderby_sql' expression
//   - (..perform SELECT query...)
//   - handle_list_limits(): actually set limit fields based on row count of sql result
//   - open_list()

// handle_list_options():
//   - initialize and normalize options for lists and returns normalized array of options
//   - handles persistent and http variables for toggling and sorting
// $options: array of options (all optional; missing entries will be created):
//   'select': string: variable name to take key of selected (and highlighted) list entry
//   'orderby_sql': string to be appended to sql 'ORDER BY' clause (computed value - input is overwritten)
//   'limits': numeric: 0 display all elements;
//             otherwise: if list has more than this many entries, allow paging
//   'limit_from': start display at this entry
//   'limit_count': display that many entries (0: all)
//     * with 'limits' === false, 'limit_from' and 'limit_count' are set hard
//     * when paging is on, they provide initial defaults for the view
//   'cols': column options: array( 'tag' => array( 'opt' => 'value', ... ), [, ... ] ) where column options can be
//     't' / 'toggle':
//       'on' (default): always on
//       'off': always off
//       '0': off by default, override by persistent
//       '1': on by default, override by persistent
//     's' / 'sort': expression to be used in sql ORDER BY clause to sort by this column (1: use column tag as key)
//
//  special values for $options:
//    $options === true: choose defaults for all options (mostly on)
//    $options === false: switch most options off
//
function handle_list_options( $options, $list_name = '', $columns = array() ) {
  static $unique_ids = array();
  $a = array(
    'select' => ''
  , 'limits' => false
  , 'limit_from' => 0
  , 'limit_count' => 0  // means 'all'
  , 'sort_prefix' => false
  , 'limits_prefix' => false
  , 'orderby_sql' => true  // implies default sorting
  , 'toggle_prefix' => false
  , 'relation_table' => false  // reserved - currently unused
  , 'allow_download' => false
  , 'cols' => array()
  );
  if( $options === false ) {
    return $a;
  } else if( $options === true ) {
    $options = array();
  } else {
    $options = parameters_explode( $options );
  }
  if( ! isset( $options['format'] ) ) {
    $options['format'] = $GLOBALS['global_format'];
  }
  $toggle_prefix = '';
  $toggle_command = '';
  $sort_prefix = '';
  if( ! isset( $unique_ids[ $list_name ] ) ) {
    $num = $unique_ids[ $list_name ] = 0;
  } else {
    $num = ++$unique_ids[ $list_name ];
  }
  $a['list_id'] = $list_id = 'list_'.$list_name.$num;

  // allowing to select list entries:
  $a['select'] = adefault( $options, 'select', '' );
  //
  // paging: just set defaults here - to be updated by handle_list_limits() once $count of list entries is known:
  //
  $a['limits'] = adefault( $options, 'limits', 10 );
  $a['limit_from'] = adefault( $options, 'limit_from', 1 );
  $a['limit_count'] = adefault( $options, 'limit_count', 20 );
  $a['limits_prefix'] = adefault( $options, 'limits_prefix', $list_id.'_' );
  //
  $a['download_item'] = adefault( $options, 'download_item', $list_id );
  $allow_download = adefault( $options, 'allow_download', 'csv,pdf' );
  if( "$allow_download" === '1' ) {
    $allow_download = 'pdf,csv';
  }
  $a['allow_download'] = parameters_explode( $allow_download );
  //
  // per-column settings:
  //
  $a['columns_toggled_off'] = 0;
  $a['col_default'] = adefault( $options, 'col_default', 'toggle,sort' );
  $col_options = parameters_explode( adefault( $options, 'columns', array() ) );

  foreach( $columns as $tag => $col ) {
    if( is_numeric( $tag ) ) {
      $tag = $col;
      $col = $a['col_default'];
    }
    $col = parameters_explode( $col );
    $col = parameters_merge( $col, parameters_explode( adefault( $col_options, $tag, array() ) ) );
    foreach( $col as $opt => $val ) {
      if( is_numeric( $opt ) ) {
        $opt = $val;
        $val = 1;
      }
      switch( $opt ) {
        case 't': // toggle
          if( ! $toggle_prefix )
            $toggle_prefix = $a['toggle_prefix'] = adefault( $options, 'toggle_prefix', $list_id.'_' );
          if( ! $toggle_command )
            $toggle_command = init_var( $toggle_prefix.'toggle', 'type=w,sources=http,default=' );
          switch( $val ) {
            case '0':
            case '1':
              $r = init_var( $toggle_prefix.'toggle_'.$tag, "global,type=b,sources=persistent,default=$val,set_scopes=script" );
              $val = $r['value'];
              if( $toggle_command['value'] === $tag )
                $val ^= 1;
              if( ! $val )
                $a['columns_toggled_off']++;
              // $GLOBALS[ $toggle_prefix.'toggle_'.$tag ] = $val;
              break;
            case 'off':
              $a['columns_toggled_off']++;
              break;
            default:
            case 'on':
              $val = 'on';
              break;
          }
          $r['value'] = $val;
          $a['cols'][ $tag ]['toggle'] = & $r['value'];
          unset( $r );
          break;
        case 's': // sort
          if( ! $sort_prefix ) {
            $sort_prefix = $a['sort_prefix'] = adefault( $options, 'sort_prefix', $list_id.'_' );
          }
          if( $val == 1 ) {
            $val = "`$tag`";
          }
          $a['cols'][ $tag ]['sort'] = $val;
          break;
        case 'h': // header
          $a['cols'][ $tag ]['header'] = $val;
          break;
        default:
          error( "undefined column option: [$opt]", LOG_FLAG_CODE, 'lists' );
      }
    } // loop: column-opts
  } // loop: columns
  //
  // sorting:
  //
  if( $sort_prefix ) {
    $orderby = init_var( $sort_prefix.'orderby', array(
      'type' => 'l'
    , 'sources' => 'persistent'
    , 'default' => adefault( $options, 'orderby', '' )
    , 'set_scopes' => 'script'
    ) );

    $ordernew = init_var( $sort_prefix.'ordernew', 'type=l,sources=http,default=' );
    $order_keys = orderby_join( $orderby['value'], $ordernew['value'] );
    $orderby['value'] = ( $order_keys ? implode( ',', $order_keys ) : '' );

    // construct SQL clause:
    $sql = '';
    $comma = '';
    foreach( $order_keys as $n => $tag ) {
      if( ( $reverse = preg_match( '/-R$/', $tag ) ) )
        $tag = preg_replace( '/-R$/', '', $tag );
      need( isset( $a['cols'][ $tag ]['sort'] ), "unknown order keyword: $tag" );
      $expression = $a['cols'][ $tag ]['sort'];
      $a['cols'][ $tag ]['sort_level'] = ( $reverse ? (-$n-1) : ($n+1) );
      if( $reverse ) {
        if( preg_match( '/ DESC$/', $expression ) )
          $expression = preg_replace( '/ DESC$/', '', $expression );
        else
          $expression = "$expression DESC";
      }
      $sql .= "$comma $expression";
      $comma = ',';
    }
    $a['orderby_sql'] = $sql;
  }
  //
  // relations:
  //
  // $a['relation_table'] = adefault( $options, 'relation_table', false );
  //
  return $a;
}

// handle_list_limits():
// return array, based on $opts and actual list entry $count:
//  'limits': whether paging is on
//  'limit_from', 'limit_count': the actual values to be used
//
function handle_list_limits( $opts, $count ) {
  $limit_from = adefault( $opts, 'limit_from', 1 );
  $limit_count = adefault( $opts, 'limit_count', 0 );
  if( $opts['limits'] === false ) {
    $limits = false;
  } else {
    $r = init_var( $opts['limits_prefix'].'limit_from', "type=U,sources=http persistent,default=$limit_from,set_scopes=script" );
    $limit_from = & $r['value'];
    unset( $r );
    $r = init_var( $opts['limits_prefix'].'limit_count', "type=u,sources=http persistent,default=$limit_count,set_scopes=script" );
    $limit_count = & $r['value'];
    unset( $r );
    $limit_count_tmp = $limit_count;
    if( $opts['limits'] > $count ) {
      $limits = false;
      $limit_from = 1;
      $limit_count_tmp = $count;
    } else {
      $limits = true;
      $limit_count_tmp = ( $limit_count ? min( $count, $limit_count ) : $count );
      if( $count < $limit_from )
        $limit_from = $count;
    }
  }
  if( ! $limit_count_tmp )
    $limit_count_tmp = $count;
  if( $limit_from + $limit_count_tmp > $count )
    $limit_from = $count - $limit_count_tmp;
  if( $limit_from < 1 )
    $limit_from = 1;
  $limit_to = min( $count, $limit_from + $limit_count_tmp );
  $l = array(
    'limits' => $limits
  , 'limit_from' => $limit_from
  , 'limit_to' => $limit_to
  , 'limit_count' => $limit_count
  , 'prefix' => $opts['limits_prefix']
  , 'count' => $count
  );
  // debug( $l, 'l' );
  return $l;
}


function open_list( $opts = array() ) {
  global $current_list;
  need( ! $current_list, 'cannot nest lists' );

  $opts = parameters_explode( $opts, 'format' );

  $format = adefault( $opts, 'format', $GLOBALS['global_format'] );
  $cols = adefault( $opts, 'cols', array() );
  $list_id = adefault( $opts, 'list_id', '' );
  $toggle_prefix = adefault( $opts, 'toggle_prefix', false );
  $sort_prefix = adefault( $opts, 'sort_prefix', false );
  $limits = adefault( $opts, 'limits', false ); 
  $allow_download = adefault( $opts, 'allow_download', array() );
  $download_item = adefault( $opts, 'download_item', 'list' );
  $class = merge_classes( 'list', adefault( $opts, 'class', '' ) );

  if( $list_id ) {
    if( ! begin_deliverable( $opts['list_id'], $allow_download ) ) {
      $current_list = false;
      return $current_list;
    }
  }

  $current_list = array(
    'format' => $format                 // output format of this list: html, pdf, csv, ...?
  , 'allow_download' => $allow_download // alternative formats to offer
  , 'limits' => $limits                 // from handle_list_limits(), see above
  , 'cols' => $cols                     // per-column options - see handle_list_options() above
  , 'toggle_prefix' => $toggle_prefix   // unique cgi-prefix; if specified, allows toggling colums on and off
  , 'sort_prefix' => $sort_prefix       // unique cgi-prefix; if specified, allows sorting
  , 'row_number_header' => 0
  , 'row_number_body' => 0
  , 'list_id' => $list_id
  );

  switch( $format ) {
    case 'html':

      open_table( $class );
      $toggle_on_choices = array();
      if( $toggle_prefix ) {
        foreach( $cols as $tag => $col ) {
          if( (string)( adefault( $col, 'toggle', 1 ) ) === '0' ) {
            $header = adefault( $col, 'header', $tag );
            $toggle_on_choices[ $tag ] = $header;
          }
        }
      }
      if( $limits || $toggle_on_choices || $allow_download ) {
        open_caption();
          open_div('center small smallskips'); // no other way(?) to center <caption>
            if( $toggle_on_choices ) {
              open_span( 'floatleft', dropdown_element( array(
                'name' => $toggle_prefix.'toggle'
              , 'choices' => $toggle_on_choices
              , 'default_display' => we('show column...','einblenden...')
              , 'title' => we('select additional columnns to display...','weitere Spalten fuer Anzeige auswaehlen...')
              ) ) );
            }
            if( $allow_download ) {
              open_span( 'floatright', download_button( $download_item, $allow_download ) );
            }
            echo H_AMP.'nbsp;';
            if( $limits ) {
              form_limits( $limits );
            }
          close_div();
        close_caption();
      }


      break;

    case 'pdf':
      $current_list['listpreample'] = '';
      $current_list['listbody'] = '';
      break;

    case 'csv':
      break;
  }

  return $current_list;
}

function close_list() {
  global $current_list;

  if( $current_list === false ) {
    $current_list = NULL;
    return;
  }
  need( $current_list );

  switch( $current_list['format'] ) {
    case 'html':
      close_table();
      break;
    case 'csv':
      echo "\n";
      break;
    case 'pdf':
      echo tex2pdf( file_get_contents( './code/textemplates/texlist.tex' ), array( 'row' => array(
        'listpreample' => $current_list['listpreample']
      , 'listbody' => $current_list['listbody']
      , 'row_number_header' => $current_list['row_number_header']
      ) ) );
      break;
  }
  if( ( $list_id = $current_list['list_id'] ) ) {
    end_deliverable( $list_id );
  }

  $current_list = NULL;
}

function open_list_row( $opts = array() ) {
  global $current_list, $current_table;

  if( $current_list === false ) {
    return;
  }
  need( $current_list );
  $opts = parameters_explode( $opts, 'class' );
  $classes = adefault( $opts, 'class', array() );
  if( is_string( $classes ) ) {
    $classes = explode( ' ', $classes );
  }
  $format = $current_list['format'];
  $current_list['is_header'] = $is_header = in_array( 'header', $classes );
  $row_number = & $current_list[ $is_header ? 'row_number_header' : 'row_number_body' ];
  // $class = merge_classes( ( ( $row_number % 2 ) ? 'odd' : 'even' ), adefault( $opts, 'class', '' ) );
  $current_list['col_number'] = 0;

  switch( $format ) {
    case 'html':
      // sync row numbers it, so header does not count and first line of body is 'even'
      $current_table['row_number'] = $row_number;
      open_tr( array( 'class' => $classes ) );
    break;

    case 'pdf':
      // $current_list['listbody'] .= "\n\\cr\\noalign{\hrule height2pt depth2pt width0pt\hrule height0.3pt}\\cr\n";
      $current_list['listbody'] .= "\n".TEX_BS."cr\n";
      // preample for \halign is derived
      // - from first header row
      // - from first body row (will override header preample unless body is empty)
      // - from line with class 'preample' (will override any previous preample)
      $current_list['generate_preample'] = false;
      if( ( $row_number == 0 ) || in_array( 'preample', $classes ) ) {
        $current_list['generate_preample'] = true;
        $current_list['listpreample'] = '';
      }
    break;

    case 'csv':
      echo "\n";
    break;
  }
  $row_number++;
}

function open_list_cell( $tag_in, $payload = false, $opts = array() ) {
  global $current_list;

  if( $current_list === false ) {
    return;
  }
  need( $current_list );
  $opts = parameters_explode( $opts, 'class' );
  $tag = strtolower( $tag_in );
  $col_opts = parameters_merge( adefault( $current_list['cols'], $tag, array() ), $opts );
  $toggle = adefault( $col_opts, 'toggle', 'on' );
  if( ( ! $toggle ) || ( $toggle === 'off' ) ) {
    return;
  }

  $format = $current_list['format'];
  $is_header = $current_list['is_header'];
  $row_number = $current_list[ $is_header ? 'row_number_header' : 'row_number_body' ];

  $classes = merge_classes( adefault( $col_opts, 'class', '' ), adefault( $opts, 'class', '' ) );
  $colspan = adefault( $col_opts, 'colspan', 1 );

  if( $payload === false ) {
    if( $is_header ) {
      $payload = adefault( $col_opts, 'header', $tag_in );
    } else {
      $payload = '';
    }
  }
  $popts = array();
  if( is_array( $payload ) ) {
    $popts = $payload;
    unset( $popts['payload'] );
    $payload = $payload['payload'];
  }
  $popts['format'] = $format;

  switch( $format ) {

    case 'html':
      $attr = array( 'class' => $classes );
      if( $colspan > 1 ) {
        $attr['colspan'] = $colspan;
      }
      if( $is_header ) {
        if( $tag ) {
          if( adefault( $col_opts, 'sort', false ) ) {
            switch( ( $n = adefault( $col_opts, 'sort_level', 0 ) ) ) {
              case 1:
              case 2:
              case 3:
                $attr['class'][] = 'sort_down_'.$n;
                break;
              case -1:
              case -2:
              case -3:
                $attr['class'][] = 'sort_up_'.(-$n);
                break;
            }
            $sort_prefix = $current_list['sort_prefix'];
            $payload = inlink( '', array(
              $sort_prefix.'ordernew' => $tag
            , 'text' => $payload
            , 'title' => we('sort table','Tabelle sortieren')
            ) );
          }
          if( "$toggle" === '1' ) {
            $toggle_prefix = $current_list['toggle_prefix'];
            $close_link = html_tag( 'span'
            , array( 'style' => 'float:right;' )
            , inlink( '', array(
                'class' => 'close_small'
              , 'text' => '', $toggle_prefix.'toggle' => $tag
              , 'title' => we('hide this column','diese Spalte ausblenden')
              ) )
            );
            $payload = $close_link . $payload;
          }
        }
        open_th( $attr, $payload );
      } else {
        if( in_array( 'url', $classes ) ) {
          $payload = url_view( $payload, $popts );
        }
        open_td( $attr, $payload );
      }
    break;

    case 'pdf':
      if( $current_list['generate_preample'] ) {
        $preample = classes2TeXcell( $classes, ( TEX_BS.'texhash'.TEX_LBR.TEX_RBR ) );
        $n = $current_list['col_number'];
        if( $current_list['col_number'] > 0 ) {
          $preample = ( TEX_BS.'texamp'.TEX_LBR.TEX_RBR ) . $preample;
        }
        $current_list['listpreample'] .= $preample;
      }
      if( $current_list['col_number'] > 0 ) {
        $current_list['listbody'] .= ( TEX_BS.'texamp'.TEX_LBR.TEX_RBR );
      }
      if( in_array( 'url', $classes ) ) {
        $payload = url_view( $payload, $popts );
      }
      $current_list['listbody'] .= $payload;
      for( $i=1; $i < $colspan; $i ++ ) {
        $current_list['listbody'] .= ( TEX_BS. 'span' .TEX_LBR.TEX_RBR );
      }
    break;

    case 'csv':
      echo csv_encode( $payload );
      for( $i=1; $i < $colspan; $i ++ ) {
        echo csv_encode(' ');
      }
    break;
  }
  $current_list['col_number'] += $colspan;
}


function classes2TeXcell( $classes, $payload = '#' ) {
  $lskip = '2pt';
  $lfill = '0fill';
  $rskip = '2pt';
  $rfill = '1fill';
  $tpadd = $bpadd = '2pt';
  $fs = '';
  foreach( $classes as $c ) {
    switch( $c ) {
      case 'larger':
        $fs = TEX_BS.'large';
        break;
      case 'smaller':
        $fs = TEX.BS.'small';
        break;
      case 'left':
      case 'unit':
        $lfill = '0fill';
        $rfill = '1fill';
        break;
      case 'right':
      case 'number':
        $lfill = '1fill';
        $rfill = '0fill';
        break;
      case 'center':
        $lfill = '1fill';
        $rfill = '1fill';
        break;
      case 'mult':
        $lfill = '1fill';
        $rfill = '1fill';
        $rskip = '2pt';
        break;
      case 'quadl':
        $lskip = '0.5ex';
        break;
      case 'quadr':
        $rskip = '0.5ex';
        break;
      case 'quads':
        $lskip = $rskip = '0.5ex';
        break;
      case 'qquadl':
        $lskip = '1ex';
        break;
      case 'qquadr':
        $rskip = '1ex';
        break;
      case 'quads':
        $lskip = $rskip = '1ex';
        break;
      case 'smallskipt':
        $tpadd = '0.5ex';
        break;
      case 'smallskipb':
        $bpadd = '0.5ex';
        break;
      case 'smallskips':
        $tpadd = $bpadd = '0.5ex';
        break;
      case 'medskipt':
        $tpadd = '1ex';
        break;
      case 'medskipb':
        $bpadd = '1ex';
        break;
      case 'medskips':
        $tpadd = $bpadd = '1ex';
        break;
    }
  }
  $pattern = $fs;
  $pattern .= TEX_LBR.TEX_RBR.TEX_BS.'mypadding'.TEX_LBR.$tpadd.TEX_RBR.TEX_LBR.$bpadd.TEX_RBR;
  $pattern .= TEX_BS."hskip $lskip plus $lfill".TEX_LBR.TEX_RBR;
  $pattern .= $payload;
  $pattern .= TEX_LBR.TEX_RBR.TEX_BS."hskip $rskip plus $rfill".TEX_LBR.TEX_RBR;
  return $pattern;
}

?>
