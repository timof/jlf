<?php

function html_options_people( $selected = 0, $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_people( $filters ) as $p ) {
    $id = $p['people_id'];
    $options[$id] = $p['cn'];
  }
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(Person waehlen)</option>" . $output;
  return $output;
}

function filter_people( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  global $form_id;
  $people_id = & $GLOBALS[$prefix.'people_id'];
  open_select( $prefix.'people_id', '', html_options_people( $people_id, $filters, $option_0 ), $form_id ? 'submit' : 'reload' );
}

function html_options_jperson( $selected = '', $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  $options['N'] = 'nat&uuml;rlich';
  $options['J'] = 'juristisch';
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='' selected>(Personenart w&auml;hlen)</option>" . $output;
  return $output;
}

function filter_jperson( $prefix = '', $option_0 = '(beide)' ) {
  global $form_id;
  if( ! isset( $GLOBALS[$prefix.'jperson'] ) )
    $GLOBALS[$prefix.'jperson'] = 0;
  open_select( $prefix.'jperson', '', html_options_jperson( $GLOBALS[$prefix.'jperson'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_kontoart( $selected = '', $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  $options['B'] = 'Bestand';
  $options['E'] = 'Erfolg';
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='' selected>(Kontoart w&auml;hlen)</option>" . $output;
  return $output;
}

function filter_kontoart( $prefix = '', $option_0 = '(beide)' ) {
  global $form_id;
  if( ! isset( $GLOBALS[$prefix.'kontoart'] ) )
    $GLOBALS[$prefix.'kontoart'] = 0;
  open_select( $prefix.'kontoart', '', html_options_kontoart( $GLOBALS[$prefix.'kontoart'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_seite( $selected = '', $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options['0'] = $option_0;
  $options['A'] = 'Aktiv';
  $options['P'] = 'Passiv';
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='' selected>(Seite w&auml;hlen)</option>" . $output;
  return $output;
}

function filter_seite( $prefix = '', $option_0 = '(beide)' ) {
  global $form_id;
  if( ! isset( $GLOBALS[$prefix.'seite'] ) )
    $GLOBALS[$prefix.'seite'] = 0;
  open_select( $prefix.'seite', '', html_options_seite( $GLOBALS[$prefix.'seite'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_geschaeftsbereiche( $selected = '0', $option_0 = false ) {
  $output = '';

  $options = sql_unique_values( 'kontoklassen', 'geschaeftsbereich' );
  foreach( $options as $k => $v ) {
    if( ! $v )
      unset( $options[$k] );
  }
  if( $option_0 )
    $options[0] = $option_0;
  $output .= html_options( & $selected, $options );
  if( $selected != -1 ) {
    $output = "<option value='0' selected>(Geschaeftsbereich waehlen)</option>" . $output;
  }
  return $output;
}

function filter_geschaeftsbereich( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  if( ! isset( $GLOBALS[$prefix.'geschaeftsbereiche_id'] ) )
    $GLOBALS[$prefix.'geschaeftsbereiche_id'] = 0;
  open_select( $prefix.'geschaeftsbereiche_id', '', html_options_geschaeftsbereiche( $GLOBALS[$prefix.'geschaeftsbereiche_id'], $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_kontoklassen( $selected = 0, $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_kontoklassen( $filters ) as $k ) {
    $id = $k['kontoklassen_id'];
    $options[$id] = "$id {$k['kontoart']} {$k['seite']} {$k['cn']}";
    if( $k['geschaeftsbereich'] )
      $options[$id] .= " / " . $k['geschaeftsbereich'];
  }
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(Kontoklasse waehlen)</option>" . $output;
  return $output;
}

function filter_kontoklasse( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  global $form_id;
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontoart', 'geschaeftsbereiche_id' ) ) )
      $filters[ $k ] = $v;
  }
  $k_id = & $GLOBALS[$prefix.'kontoklassen_id'];
  open_select( $prefix.'kontoklassen_id', '', html_options_kontoklassen( $k_id, $filters, $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_hauptkonten( $selected = 0, $filters = array(), $option_0 = false ) {
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_hauptkonten( $filters ) as $k ) {
    $id = $k['hauptkonten_id'];
    $options[$id] = "{$k['kontoart']} {$k['seite']} {$k['rubrik']} : {$k['titel']}";
  }
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(Hauptkonto waehlen)</option>" . $output;
  return $output;
}

function filter_hauptkonto( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  global $form_id;
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontoart', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr' ) ) )
      $filters[ $k ] = $v;
  }
  // $filters = filter_hauptkonto_prepare( $prefix, $filters );
  $hk_id = & $GLOBALS[$prefix.'hauptkonten_id'];
  open_select( $prefix.'hauptkonten_id', '', html_options_hauptkonten( $hk_id, $filters, $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_unterkonten( $selected = 0, $filters = array(), $option_0 = false ) {
  // need( isset( $filters['hauptkonten_id'] ) && ( $filters['hauptkonten_id'] > 0 ) );
  $options = array();
  if( $option_0 )
    $options[0] = $option_0;
  foreach( sql_unterkonten( $filters, 'cn' ) as $k ) {
    $options[$k['unterkonten_id']] = $k['cn'];
  }
  $output = html_options( & $selected, $options );
  if( $selected != -1 )
    $output = "<option value='0' selected>(Unterkonto waehlen)</option>" . $output;
  return $output;
}

function filter_unterkonto( $prefix = '', $filters = array(), $option_0 = '(alle)' ) {
  global $form_id;
  foreach( adefault( $GLOBALS, $prefix.'filters', array() ) as $k => $v ) {
    if( in_array( $k, array( 'seite', 'kontoart', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr', 'hauptkonten_id' ) ) )
      $filters[ $k ] = $v;
  }
  // $filters = filter_unterkonto_prepare( $prefix, $filters );
  $uk_id = & $GLOBALS[$prefix.'unterkonten_id'];
  open_select( $prefix.'unterkonten_id', '', html_options_unterkonten( $uk_id, $filters, $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_things( $selected = 0, $filters = array(), $option_0 = false ) {
  $output = '';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( "$selected" == '0' ) {
      $output .= " selected";
      $selected = -1;
    }
    $output .= ">$option_0</option>";
  }
  foreach( sql_things( $filters ) as $thing ) {
    $output .= "<option value='{$thing['things_id']}'";
    if( $selected == $thing['things_id'] ) {
      $output .= ' selected';
      $selected = -1;
    }
    $output .= ">{$thing['cn']}";
    if( $thing['anschaffungsjahr'] )
      $output .= " ({$thing['anschaffungsjahr']}) ";
    $output .= "</option>";
  }
  if( $selected != -1 )
    $output = "<option value='0' selected>(Gegenstand w&auml;hlen)</option>" . $output;
  return $output;
}


function html_options_anschaffungsjahr( $selected = 0, $option_0 = false ) {
  $output = '';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( "$selected" == '0' ) {
      $output .= " selected";
      $selected = -1;
    }
    $output .= ">$option_0</option>";
  }
  foreach( sql_unique_values( 'things', 'anschaffungsjahr' ) as $r ) {
    $j = $r['anschaffungsjahr'];
    $output .= "<option value='$j'";
    if( $selected == $j ) {
      $output .= ' selected';
      $selected = -1;
    }
    $output .= ">$j</option>";
  }
  if( $selected != -1 )
    $output = "<option value='0' selected>(Anschaffungsjahr w&auml;hlen)</option>" . $output;
  return $output;
}

function filter_anschaffungsjahr( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id;
  global $anschaffungsjahr;
  if( ! isset( $anschaffungsjahr ) )
    $anschaffungsjahr = 0;
  open_select( $prefix.'anschaffungsjahr', '', html_options_anschaffungsjahr( $anschaffungsjahr, '', $option_0 ), $form_id ? 'submit' : 'reload' );
}


function html_options_bankkonten( $selected = 0, $filters = array(), $option_0 = false ) {
  $output = '';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( "$selected" == '0' ) {
      $output .= " selected";
      $selected = -1;
    }
    $output .= ">$option_0</option>";
  }
  foreach( sql_bankkonten( $filters ) as $bk ) {
    $output .= "<option value='{$bk['bankkonten_id']}'";
    if( $selected == $bk['bankkonten_id'] ) {
      $output .= ' selected';
      $selected = -1;
    }
    $output .= ">{$bk['cn']}</option>";
  }
  if( $selected != -1 )
    $output = "<option value='0' selected>(Bankkonto w&auml;hlen)</option>" . $output;
  return $output;
}

function html_options_geschaeftsjahre( $selected = 0, $option_0 = false ) {
  $output = '';
  if( $option_0 ) {
    $output = "<option value='0'";
    if( "$selected" == '0' ) {
      $output .= " selected";
      $selected = -1;
    }
    $output .= ">$option_0</option>";
  }
  foreach( sql_unique_values( 'hauptkonten', 'geschaeftsjahr' ) as $j ) {
    $output .= "<option value='$j'";
    if( $selected == $j ) {
      $output .= ' selected';
      $selected = -1;
    }
    $output .= ">$j</option>";
  }
  if( $selected != -1 )
    $output = "<option value='0' selected>(Gesch&auml;ftsjahr w&auml;hlen)</option>" . $output;
  return $output;
}


function filter_geschaeftsjahr( $prefix = '', $option_0 = '(alle)' ) {
  global $form_id, $geschaeftsjahr_current, $geschaeftsjahr_min, $geschaeftsjahr_max;

  $f = $prefix.'geschaeftsjahr';
  $g = & $GLOBALS[ $f ];

  if( ! $g && ! $option_0 ) {
    $g = $geschaeftsjahr_current;
  }
  if( $g ) {
    $g = max( min( $g, $geschaeftsjahr_max ), $geschaeftsjahr_min );
  }

  if( $g ) {
    selector_int( $g, $f, $geschaeftsjahr_min, $geschaeftsjahr_max );
    open_span( 'quads' );
    if( $option_0 ) {
      if( $form_id ) {
        echo inlink( '', array( 'class' => 'button', 'text' => "$option_0", 'url' => "javascript:submit_form('form_$form_id', '$f', '0' );" ) );
      } else {
        echo inlink( '', array( 'class' => 'button', 'text' => "$option_0", $f => 0 ) );
      }
    }
    close_span();
  } else {
    open_span( 'quads', '', ' (alle) ' );
    open_span( 'quads' );
      if( $form_id ) {
        echo inlink( '', array(
          'class' => 'button', 'text' => 'Filter...'
        , 'url' => "javascript:submit_form('form_$form_id', '$f', '$geschaeftsjahr_current' );" ) );
      } else {
        echo inlink( '', array( 'class' => 'button', 'text' => 'Filter...', $f => $geschaeftsjahr_current ) );
      }
    close_span();
  }
}

function filter_stichtag( $prefix = '' ) {
  global $form_id;

  $f = $prefix.'stichtag';
  $stichtag = & $GLOBALS[ $f ];

  if( ! $stichtag ) {
    $stichtag = 1231;
  }
  $stichtag = max( min( $stichtag, 1231 ), 100 );

  $p1 = array( 'class' => 'button', 'text' => 'Vortrag &lt; ', $f => 100, 'inactive' => ( $stichtag <= 100 ) );
  $p2 = array( 'class' => 'button', 'text' => '&gt; Ultimo', $f => 1231, 'inactive' => ( $stichtag >= 1231 ) );
  if( $form_id ) {
    $p1['url'] = "javascript:submit_form('form_$form_id', '$f', '100' );";
    $p2['url'] = "javascript:submit_form('form_$form_id', '$f', '1231' );";
  }
  echo inlink( '', $p1 );
  echo int_view( $stichtag, $f, 4 );
  echo inlink( '', $p2 );
}


function filters_kontodaten_prepare( $prefix = '', $fields = array() ) {
  global $geschaeftsjahr_min, $geschaeftsjahr_max, $geschaeftsjahr_current, $geschaeftsjahr_thread, $now, $jlf_url_vars;

  $all_fields = array( 'seite', 'kontoart', 'geschaeftsbereiche_id', 'kontoklassen_id', 'geschaeftsjahr', 'hauptkonten_id', 'unterkonten_id' );

  $filters = array();

  // first round: init: retrieve new or persistent values, init filters, only accept consistent values:
  //
  foreach( $all_fields as $field ) {
    $$field = 0;
    $type = $jlf_url_vars[ $field ]['type'];
    if( in_array( $field, $fields ) ) {
      init_global_var( $prefix.$field, $type, 'http,persistent,keep', 0, 'self' );
      if( ( $$field = & $GLOBALS[ $prefix.$field ] ) ) {
        $filters[ $field ] = & $$field;
        // check for and remove existing inconsistencies; strategy:
        // - if filters yield empty set, drop the most specific filter
        // thus, if a less specific filter is changed, the more specific ones will usually be dropped
        //
        switch( $field ) {
          case 'geschaeftsbereiche_id':
            if( $kontoart == 'B' ) {
              $geschaeftsbereiche_id = 0;
              unset( $filters['geschaeftsbereiche_id'] );
            }
            break;
          case 'kontoklassen_id':
            if( ! sql_kontoklassen( $filters ) ) {
              $kontoklassen_id = 0;
              unset( $filters['kontoklassen_id'] );
            }
            break;
          case 'hauptkonten_id':
            if( ! sql_hauptkonten( $filters ) ) {
              $hauptkonten_id = 0;
              unset( $filters['hauptkonten_id'] );
            }
            break;
          case 'unterkonten_id':
            if( ! sql_unterkonten( $filters ) ) {
              $unterkonten_id = 0;
              unset( $filters['unterkonten_id'] );
            }
            break;
        }
      }
    }
  }

  // second round: check for new values from http, propagate changes upward:
  // thus, the last changed filter will survive, and
  // - first round (above) makes sure more specific ones are compatible
  // - less specific ones will now be forced to match
  foreach( $all_fields as $field ) {
    if( in_array( $field, $fields ) ) {
      $type = $jlf_url_vars[ $field ]['type'];
      $val = get_http_var( $prefix.$field, $type );
      if( $val !== NULL ) {
        $$field = $val;
        switch( $field ) {
          case 'unterkonten_id':
            if( $unterkonten_id ) {
              $uk = sql_one_unterkonto( $unterkonten_id );
              $hauptkonten_id = $uk['hauptkonten_id'];
            }
            // fall-through
          case 'hauptkonten_id':
            if( $hauptkonten_id ) {
              $hk = sql_one_hauptkonto( $hauptkonten_id );
              $kontoklassen_id = $hk['kontoklassen_id'];
              $geschaeftsjahr = $hk['geschaeftsjahr'];
            }
            // fall-through
          case 'kontoklassen_id':
            if( $kontoklassen_id ) {
              $kontoklasse = sql_one_kontoklasse( $kontoklassen_id );
              $seite = $kontoklasse['seite'];
              $kontoart = $kontoklasse['kontoart'];
              if( $kontoart == 'E' ) {
                $geschaeftsbereiche_id = sql_unique_id( 'kontoklassen', 'geschaeftsbereich', $kontoklasse['geschaeftsbereich'] );
              }
            }
        }
      }
    }
  }

  $filters = array();
  foreach( $all_fields as $field ) {
    if( $$field ) {
      $filters[ $field ] = & $$field;
    } else {
      unset( $filters[ $field ] );
    }
  }
  return $filters;
}

?>
