<?php

need_priv( 'books', 'read' );

sql_transaction_boundary('*');

init_var( 'options', 'global,type=u,sources=http persistent,set_scopes=window,default=0' );

if( $parent_script !== 'self' ) {
  $reinit = 'init';  // generate empty entry, plus initialization from http
} else if( $action === 'reset' ) {
  $reinit = 'reset'; // re-initialize from db, or generate empty entry
} else {
  $reinit = 'http';
}

do {

  switch( $reinit ) {
    case 'init':
      init_var( 'zahlungsplan_id', 'global,type=u,sources=http,default=0,set_scopes=self' );
      init_var( 'flag_problems', 'global,type=b,sources=,default=0,set_scopes=self' );
      $sources = 'http initval default';
      break;
    case 'reset':
      init_var( 'zahlungsplan_id', 'global,type=u,sources=self,default=0,set_scopes=self' );
      init_var( 'flag_problems', 'global,type=b,sources=,default=0,set_scopes=self' );
      $sources = 'initval default';
      break;
    case 'http':
      init_var( 'zahlungsplan_id', 'global,type=u,sources=self,default=0,set_scopes=self' );
      init_var( 'flag_problems', 'global,type=b,sources=self,default=0,set_scopes=self' );
      $sources = 'http self';
      break;
    case 'persistent':
      init_var( 'zahlungsplan_id', 'global,type=u,sources=self,default=0,set_scopes=self' );
      init_var( 'flag_problems', 'global,type=b,sources=self,default=0,set_scopes=self' );
      $sources = 'self';
      break;
    default:
      error( 'cannot initialize - invalid $reinit', LOG_FLAG_CODE, 'zahlungsplan,init' );
  }
  if( $action === 'save' ) {
    $flag_problems = 1;
  }

  $opts = array(
    'flag_problems' => & $flag_problems
  , 'flag_modified' => & $flag_modified
  , 'tables' => 'zahlungsplan'
  , 'failsafe' => false
  , 'sources' => $sources
  , 'set_scopes' => 'self'
  );

  if( $zahlungsplan_id ) {
    $flag_modified = 1;
    $zahlungsplan = sql_one_zahlungsplan( $zahlungsplan_id );
    init_var( 'darlehen_id', 'global,type=U,sources=,set_scopes=self,default='.$zahlungsplan['darlehen_id'] );
    init_var( 'geschaeftsjahr', 'global,type=U,sources=,set_scopes=self,default='.$zahlungsplan['geschaeftsjahr'] );
    $opts['rows'] = array( 'zahlungsplan' => $zahlungsplan );
  } else {
    $flag_modified = 0;
    $zahlungsplan = array();
    init_var( 'darlehen_id', 'global,type=U,sources=http self,set_scopes=self' );
    init_var( 'geschaeftsjahr', 'global,type=U,sources=http self,set_scopes=self,default='.$geschaeftsjahr_thread );
  }
  $darlehen = sql_one_darlehen( $darlehen_id );
  $darlehen_unterkonten_id = sql_get_folge_unterkonten_id( $darlehen['darlehen_unterkonten_id'], $geschaeftsjahr );
  $zins_unterkonten_id = ( $darlehen['zins_unterkonten_id'] ? $darlehen['zins_unterkonten_id'] : $darlehen_unterkonten_id );
  $zins_unterkonten_id = sql_get_folge_unterkonten_id( $zins_unterkonten_id, $geschaeftsjahr );

  $jahr_max = $geschaeftsjahr + 99;
  $fields = array(
    'darlehen_id' => "sources=,default=$darlehen_id"
  , 'unterkonten_id' => "sources=,default=$darlehen_unterkonten_id"
  , 'geschaeftsjahr' => "sources=,default=$geschaeftsjahr,max=$jahr_max"
  , 'valuta' => 'U,default=1231'
  , 'betrag' => 'f,format=%.2lf'
  , 'art' => 'type=W1,pattern=/^[SH]$/,auto=1'
  , 'zins' => 'b,auto=1,text=Zins'
  , 'posten_id' => 'u'
  , 'kommentar' => 'h'
  );
  if( ! $zahlungsplan_id ) {
    $fields['darlehen_id'] = "sources=,default=$darlehen_id";
    $fields['unterkonten_id'] = "sources=,default=$darlehen_unterkonten_id";
  }
  $f = init_fields( $fields, $opts );
  if( $fields['zins']['value'] ) {
    $fields['unterkonten_id']['value'] = $zins_unterkonten_id;
  }

  if( $zahlungsplan_id && $fields['unterkonten_id']['value'] ) {
    $pfilters = array(
      'unterkonten_id' => $fields['unterkonten_id']['value']
    , 'art' => $fields['art']['value']
    , 'betrag' => $fields['betrag']['value']
  // , 'valuta' => $zahlungsplan['valuta']
    );
    $posten_id = $fields['posten_id']['value'];
    if( $posten_id ) {
      $pfilters['posten_id'] = $posten_id;
    }
    $posten = sql_posten( $pfilters );
    if( ! $posten ) {
      $posten_id = $zahlungsplan['posten_id']['value'] = 0;
    }
  } else {
    $posten_id = 0;
    $posten = false;
  }

  $reinit = false;

  handle_actions( array( 'init', 'save', 'reset', 'deleteZahlungsplan' ) );
  if( $action ) switch( $action ) {

    case 'save':
      if( ! $f['_problems'] ) {
        $values = array();
        foreach( $fields as $fieldname => $r ) {
          if( isset( $tables['zahlungsplan']['cols'][ $fieldname ] ) ) {
            $values[ $fieldname ] = $f[ $fieldname ]['value'];
          }
        }
        if( ! $zahlungsplan_id ) {
          $zahlungsplan_id = sql_insert( 'zahlungsplan', $values );
        } else {
          sql_update( 'zahlungsplan', $zahlungsplan_id, $values );
        }
        reinit('reset');
      }
      break;

    case 'deleteZahlungsplan':
      need( $message > 0, 'kein zahlungsplan ausgewaehlt' );
      sql_delete_zahlungsplan( $message );
      break;

    default:
    case 'nop':
    case 'update':
      break;
  }

} while( $reinit );


if( $zahlungsplan_id ) {
  open_fieldset( 'small_form old', "Stammdaten Zahlungsplan [$zahlungsplan_id]" );
} else {
  open_fieldset( 'small_form new', 'neuer Zahlungsplan' );
}

  open_table( 'hfill,colgroup=30% 20% 50%' );
  
  open_tr();
    open_td( '', 'Darlehen:' );
      open_td( 'colspan=2', inlink( 'darlehen', array(
        'darlehen_id' => $darlehen_id, 'class' => 'href'
      , 'text' => "{$darlehen['kommentar']} {$darlehen['geschaeftsjahr']}"
      ) ) );

    open_tr( 'smallskip' );
      open_td( '', 'Kreditor:' );
      open_td( 'colspan=2', inlink( 'person', array(
        'text' => $darlehen['people_cn'], 'class' => 'people', 'people_id' => $darlehen['people_id']
      ) ) );

    open_tr( 'medskip' );
      open_td( array( 'label' => $f['geschaeftsjahr'] ), 'Geschäftsjahr:' );
      open_td( 'bold', selector_geschaeftsjahr( $f['geschaeftsjahr'] ) );
      open_td( 'qquad' );
        open_label( $f['valuta'], '', 'Valuta: ' );
        echo monthday_element( $f['valuta'] );

    open_tr( 'smallskip' );
      open_td( array( 'label' => $f['zins'] ), 'Zins:' );
      open_td();
        open_input( $f['zins'] );
          echo checkbox_element( $f['zins'] );
        close_input();
      open_td( 'qquad' );
        $uk_id = $f['zins']['value'] ? $zins_unterkonten_id : $darlehen_unterkonten_id;
        if( $uk_id ) {
          $unterkonto = sql_one_unterkonto( $uk_id );
          echo "Konto: " . inlink( 'unterkonto', array( 'text' => $unterkonto['cn'], 'unterkonten_id' => $uk_id ) );
        } else {
          echo "(Unterkonto nicht angelegt)";
        }

    open_tr();
      open_td( array( 'label' => $f['betrag'] ), 'Betrag:' );
      open_td( '', price_element( $f['betrag'] ) );
      open_td( 'qquad' );
        open_input( $f['art'] );
          echo radiobutton_element( $f['art'], array( 'value' => 'S', 'text' => 'Soll' ) );
          quad();
          echo radiobutton_element( $f['art'], array( 'value' => 'H', 'text' => 'Haben' ) );
        close_input();

    open_tr();
      open_td( 'right,colspan=3' );
        echo reset_button_view( $f['_changes'] ? '' : 'display=none' );
        echo save_button_view( $f['_changes'] ? '' : 'display=none' );

  close_table();

  if( $posten_id ) {
    open_div( 'medskip center'
    , 'gebucht und zugeordnet: '
    );

  } else if( $posten ) {
    open_div( 'medskip center'
    , 'kein Posten zugeordnet - geeignete gebuchte Posten:'
    );

  } else {
    open_div( 'medskip center'
    , 'keine passenden Posten gebucht '
    );
  }

close_fieldset();
      
?>
