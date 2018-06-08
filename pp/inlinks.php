<?php


// $sidenav: defines tree-structure of main menu
//
$sidenav_map = array(
  'menu' => 1
, 'lehre' => array( 'menu' => 1, 'childs' => array(
    'intro' => 0
  , 'modul' => 0
  , 'studiengaenge' => array( 'menu' => 1, 'childs' => array(
      'bsc' => 1
    , 'bed' => 1
    , 'msc' => 1
    , 'med' => 1
    , 'mastro' => 1
    , 'phd' => 1
    , 'diplom' => 0
    ) )
  , 'praktika' => 1
  , 'tutorium' => 1
  , 'terminelehre' => 1
  , 'studierendenvertretung' => 1
  ) )
, 'forschung' => array( 'menu' => 1, 'childs' => array(
     'themen' => array( 'menu' => 0, 'childs' => array(
        'thema' => 0
     ) )
     , 'publikationen' => array( 'menu' => 0, 'childs' => array(
       'publikation' => 0
     ) )
  ) )
, 'institut' => array( 'menu' => 1, 'childs' => array(
      'veranstaltungsarchiv' => array( 'menu' => 1, 'childs' => array(
        'veranstaltung' => 0
      ) )
    , 'gremien' => 1
//    , 'institutsrat' => 1
//    , 'pruefungsausschuss' => 1
    , 'labore' => 1
    , 'werkstatt' => 1
    , 'impressum' => 1
  ) )
, 'mitarbeiter' => array( 'menu' => 1, 'childs' => array(
    'visitenkarte' => 0
  ) )
, 'professuren' => array( 'menu' => 1, 'childs' => array(
        'gruppe' => 0
  ) )
, 'download' => array( 'menu' => 1, 'childs' => array(
      'vorlesungsverzeichnisse' => 1
    , 'ordnungen' => 1
  ) )
);



// script_defaults: define default parameters and default options for views:
//
function script_defaults( $target_script ) {
  global $uUML;
  // for the public pages, we don't need most of the functionality here:
  // - we don't open new windows here (yet), so $options and $parameters['window'] are pretty meaningless
  // - 
  $options = array();
  $parameters = array(
    'window' => 'menu'
  , 'script' => $target_script
  , 'class'=> 'href inlink'
  );

  switch( strtolower( $target_script ) ) {
    //
    // Anzeige im Hauptfenster (aus dem Hauptmenue) oder in "grossem" Fenster moeglich:
    //
    case 'menu':
    case 'main':
    case 'index':
    case 'start':
      $parameters['text'] = we('Home','Start');
      $parameters['title'] = ''; // we('Start page','Startseite');
      $parameters['script'] = 'menu';
      $file = 'menu/menu.php';
      break;
//     case 'aktuelles':
//       $parameters['text'] = we('News','Aktuelles');
//       $parameters['title'] = we('News','Aktuelles');
//       $file = 'aktuelles/aktuelles.php';
//       break;
    case 'institut':
      $parameters['text'] = we('Institute','Institut');
      $parameters['title'] = we('Institute','Institut');
      $file = 'institut/institut.php';
      break;
    case 'veranstaltungsarchiv':
      $parameters['text'] = we('Events','Veranstaltungen');
      $parameters['title'] = we('Events','Veranstaltung');
      $file = 'institut/veranstaltungsarchiv.php';
      break;
    case 'gremien':
      $parameters['text'] = we('Committees','Gremien');
      $parameters['title'] = we('Committees','Gremien');
      $file = 'institut/gremien.php';
      break;
    case 'institutsrat':
      $parameters['text'] = we('Institute board','Institutsrat');
      $parameters['title'] = we('Institute board','Institutsrat');
      $file = 'institut/gremien.php';
      $parameters['anchor'] = 'irat';
      $parameters['script'] = 'gremien';
      break;
    case 'labore':
      $parameters['text'] = we('Safety','Sicherheit');
      $parameters['title'] = we('Safety','Sicherheit');
      $file = 'institut/labore.php';
      break;
    case 'werkstatt':
      $parameters['text'] = we('Workshop','Werkstatt');
      $parameters['title'] = we('Workshop','Werkstatt');
      $file = 'institut/werkstatt.php';
      break;
    case 'impressum':
      $parameters['text'] = we('Impressum','Impressum');
      $parameters['title'] = we('Impressum','Impressum');
      $file = 'institut/impressum.php';
      break;
    case 'professuren':
      $parameters['title'] = we('Professors','Professuren');
      $parameters['text'] = we('Professors','Professuren');
      $file = 'professuren/professuren.php';
      break;
    case 'gruppe':
    case 'group_view': // used in /shared/views.php
      $parameters['text'] = we('Details on Group','Details zur Gruppe');
      $parameters['title'] = '';
      $file = 'professuren/gruppe.php';
      $parameters['script'] = 'gruppe';
      break;
    case 'mitarbeiter':
      $parameters['text'] = we('People','Personen');
      $parameters['title'] = we('People','Personen');
      $file = 'mitarbeiter/mitarbeiter.php';
      break;
    case 'visitenkarte':
    case 'person_view': // used in /shared/views.php
      $parameters['text'] = we('Details on Person','Details zur Person');
      $parameters['title'] = '';
      $file = 'mitarbeiter/visitenkarte.php';
      $parameters['script'] = 'visitenkarte';
      break;
    case 'professuren':
      $parameters['text'] = we('Professors','Professuren');
      $parameters['title'] = we('Professors','Professuren am Institut');
      $parameters['function'] = 'full';
      $file = 'professuren/professuren.php';
      break;

    case 'forschung':
      $parameters['text'] = we('Research','Forschung');
      $parameters['title'] = we('Research','Forschung');
      $file = 'forschung/forschung.php';
      break;
    case 'schwerpunkte':
      $parameters['text'] = we('Key areas','Schwerpunkte');
      $parameters['title'] = we('Key areas','Schwerpunkte');
      $file = 'forschung/schwerpunkte.php';
      break;
    case 'publikationen':
      $parameters['text'] = we('Publications','Veröffentlichungen');
      $parameters['title'] = we('Publications','Veröffentlichungen');
      $file = 'forschung/publikationen.php';
      break;
    case 'publikation':
    case 'publication_view':
      $parameters['text'] = we('Publication','Artikel');
      $parameters['title'] = we('Publications','Artikel');
      $file = 'forschung/publikation.php';
      $parameters['script'] = 'publikation';
      break;
    case 'veranstaltung':
    case 'event_view':
      $parameters['text'] = we('Event','Veranstaltung');
      $parameters['title'] = we('Event','Veranstaltung');
      $file = 'institut/veranstaltung.php';
      $parameters['script'] = 'veranstaltung';
      break;
    case 'themen':
      $parameters['text'] = we('Topics','Themenvorschläge');
      $parameters['title'] = we('Topics','Themenvorschläge');
      $file = 'forschung/themen.php';
      break;
    case 'thema':
    case 'position_view':
      $parameters['text'] = we('Topic','Themenvorschlag');
      $parameters['title'] = we('Topic','Themenvorschlag');
      $file = 'forschung/thema.php';
      $parameters['script'] = 'thema';
      break;

    case 'lehre':
      $parameters['text'] = we('Studies','Studium');
      $parameters['title'] = we('Studies','Studium');
      $file = 'lehre/lehre.php';
      break;
//     case 'einschreibung':
//       $parameters['text'] = we('Prospective Students',"Studieninteressierte");
//       $parameters['title'] = we('Prospective Students',"Studieninteressierte");
//       $file = 'lehre/einschreibung.php';
//       break;
    case 'modul':
      $parameters['text'] = we('Module information','Details zum Modul');
      $parameters['title'] = we('Module information','Details zum Modul');
      $file = 'lehre/modul.php';
      break;
    case 'einschreibung':
    case 'studiengaenge':
      $parameters['text'] = we('Programs','Studienangebot');
      $parameters['title'] = we('Programs','Studienangebot');
      $file = 'lehre/studiengaenge.php';
      break;
    case 'bsc':
      $parameters['text'] = 'Bachelor of Science';
      $parameters['title'] = we('Bachelor of Science program','Bachelor of Science Studiengang');
      $file = 'lehre/bsc.php';
      break;
    case 'msc':
      $parameters['text'] = 'Master of Science';
      $parameters['title'] = we('Master of Science program','Master of Science Studiengang');
      $file = 'lehre/msc.php';
      break;
    case 'bed':
      $parameters['text'] = 'Bachelor of Education';
      $parameters['title'] = we('Bachelor of Education program','Bachelor of Education Studiengang');
      $file = 'lehre/bed.php';
      break;
    case 'med':
      $parameters['text'] = 'Master of Education';
      $parameters['title'] = we('Master of Education program','Master of Education Studiengang');
      $file = 'lehre/med.php';
      break;
    case 'mastro':
      $parameters['text'] = 'Astrophysics | Master';
      $parameters['title'] = we('Astrophysics | Master (MSc) program','Studiengang Astrophysics | Master (MSc)');
      $file = 'lehre/mastro.php';
      break;
    case 'phd':
      $parameters['text'] = we('PhD program','Promotionsstudium');
      $parameters['title'] = we('PhD program','Promotionsstudium');
      $file = 'lehre/phd.php';
      break;
    case 'diplom':
      $parameters['text'] = we('Diploma program','Diplomstudium');
      $parameters['title'] = we('Diploma program','Diplomstudium');
      $file = 'lehre/diplom.php';
      break;
    case 'praktika':
      $parameters['text'] = we('Lab Courses',"Praktika");
      $parameters['title'] = we('Lab Courses',"Praktika");
      $file = 'lehre/praktika.php';
      break;
    case 'terminelehre':
      $parameters['text'] = we('Dates','Termine');
      $parameters['title'] = we('Dates','Termine');
      $file = 'lehre/terminelehre.php';
      break;
    case 'studierendenvertretung':
      $parameters['text'] = we('Student representation','Studierendenvertretung');
      $parameters['title'] = we('Student representation','Studierendenvertretung');
      $file = 'lehre/studierendenvertretung.php';
      break;
    case 'intro':
      $parameters['text'] = we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen");
      $parameters['title'] = we('Introductory courses',"Einf{$uUML}hrungsveranstaltungen");
      $file = 'lehre/intro.php';
      break;
    case 'tutorium':
      $parameters['text'] = we('Tutorials','Gemeinsam Lernen');
      $parameters['title'] = we('Tutorials','Gemeinsam Lernen');
      $file = 'lehre/tutorium.php';
      break;
    case 'pruefungsausschuss':
      $parameters['text'] = we('Examination board','Prüfungsausschuss');
      $parameters['title'] = we('Examination board','Prüfungsausschuss');
      $file = 'institut/gremien.php';
      $parameters['anchor'] = 'examBoardMono';
      $parameters['script'] = 'gremien';
      break;
    case 'praktika':
      $parameters['text'] = we('Lab courses','Praktika');
      $parameters['title'] = we('Lab courses','Praktika');
      $file = 'lehre/praktika.php';
      break;

    case 'links':
      $parameters['text'] = we('Links','Links');
      $parameters['title'] = we('Links','Links');
      $file = 'links/links.php';
      break;

    case 'download':
    case 'document_view':
      $parameters['script'] = 'download';
      $parameters['text'] = 'Download';
      $parameters['title'] = 'Download';
      $file = 'download/download.php';
      break;
    case 'vorlesungsverzeichnisse':
      $parameters['script'] = 'vorlesungsverzeichnisse';
      $parameters['text'] = we('Course Directories','Vorlesungsverzeichnisse' );
      $parameters['title'] = we('Course Directories','Vorlesungsverzeichnisse' );
      $file = 'download/vorlesungsverzeichnisse.php';
      break;
    case 'ordnungen':
      $parameters['script'] = 'ordnungen';
      $parameters['text'] = we('Regulations','Ordnungen' );
      $parameters['title'] = we('Regulations','Ordnungen' );
      $file = 'download/ordnungen.php';
      break;
    //
    default:
      // logger( "unexpected target script: [$target_script]", LOG_LEVEL_ERROR, LOG_FLAG_CODE | LOG_FLAG_INPUT, 'links' );
      return NULL;
  }
  return array( 'parameters' => $parameters, 'options' => $options, 'file' => $file );
}

$cgi_get_vars = array(
  'function' => array( 'type' => 'W32', 'persistent' => 'url' )
, 'people_id' => array( 'type' => 'u6', 'persistent' => 'url' )
, 'groups_id' => array( 'type' => 'u6', 'persistent' => 'url' )
, 'exams_id' => array( 'type' => 'u' )
, 'teaching_id' => array( 'type' => 'u' )
, 'positions_id' => array( 'type' => 'u' )
, 'publications_id' => array( 'type' => 'u', 'persistent' => 'url' )
, 'degree_id' => array( 'type' => 'u' )
, 'programme_flags' => array( 'type' => 'u' )
, 'item' => array( 'type' => 'w' )
, 'term' => array( 'type' => 'W1', 'pattern' => '/^[WS0]$/', 'default' => '0' )
, 'id' => array( 'type' => 'u', 'persistent' => 'url' )
, 'year' => array( 'type' => 'u4', 'format' => '%04u' )
, 'month' => array( 'type' => 'u2', 'format' => '%02u' )
, 'day' => array( 'type' => 'u2', 'format' => '%02u' )
, 'modul' => array( 'type' => 'a20', 'pattern' => '/^[0-9a-zA-Z]+$/' )
);

$jlf_cgi_vars = array(
  'hour' => array( 'type' => 'u2', 'format' => '%02u' )
, 'minute' => array( 'type' => 'u2', 'format' => '%02u' )
);

function inlink( $target = '', $parameters = array(), $opts = array() ) {
  global $script, $global_format, $pseudo_parameters, $H_SQ, $jlf_persistent_vars;

  $parameters = parameters_explode( $parameters );
  $opts = parameters_explode( $opts );

  if( $global_format !== 'html' ) {
    // \href makes no sense for (deep) inlinks - and neither should it look like a link if it isn't one:
    return adefault( $parameters, 'text', ' - ' );
  }
  $self = 0;
  if( ! $target ) {
    $target = $script;
    $self = 1;
  }
  if( $target[ 0 ] === '?' ) {
    $target = substr( $target, 1 );
    if( $target === $script ) {
      $self = 1;
    }
  }

  $context = adefault( $parameters, 'context', 'a' );
  $inactive = adefault( $parameters, 'inactive', false );
  $inactive = adefault( $inactive, 'problems', $inactive );
  $js = '';
  $url = '';

  if( $target[ 0 ] === '!' ) {
    $post = 1;
    $form_id = substr( $target, 1 );
    if( ! $form_id ) {
      $self = 1;
    }
    $r = array();
    foreach( $parameters as $key => $val ) {
      if( in_array( $key, $pseudo_parameters ) ) {
        continue;
      }
      if( $key == 'login' ) {
        $key = 'l';
      }
      $r[ $key ] = bin2hex( $val );
    }

    $s = parameters_implode( $r );
    // debug( $s, 's' );

  } else {
    $post = 0;
    $script_defaults = script_defaults( $target );
    if( ! $script_defaults ) {
      need( $context === 'a', "broken link in context [$context]" );
      return html_tag( 'img', array( 'class' => 'icon brokenlink', 'src' => 'img/broken.tiny.trans.gif', 'title' => "broken: $target" ), NULL );
    }

    // force canonical script name:
    $target = $script_defaults['parameters']['script'];

    if( $self ) {
      $parameters = array_merge( $jlf_persistent_vars['url'], $parameters );
    }
    $parameters = array_merge( $script_defaults['parameters'], $parameters );
    $parameters['m'] = $target;
    if( $self ) {
      $parameters['m'] .= ',self';
    }
    $url = get_internal_url( $parameters );
  }

  switch( $context ) {
    case 'a':
      $attr = parameters_explode( adefault( $parameters, 'attr', array() ) );
      $baseclass = array( 'a', 'inlink' ); // basic type - should always apply
      $linkclass = 'href';                 // look of the link - default, may be changed
      foreach( $parameters as $a => $val ) {
        switch( $a ) {
          case 'title':
          case 'text':
          case 'id':
            $attr[ $a ] = $val;
            break;
          case 'class':
            $linkclass = $val;
            break;
          case 'display':
            $attr['style'] = "display:$val;";
            break;
        }
      }
      if( $inactive ) {
        $baseclass[] = 'inactive';
        $attr['class'] = merge_classes( $baseclass, $linkclass );
        if( isarray( $inactive ) ) {
          $inactive = implode( ' / ', $inactive );
        }
        if( isstring( $inactive ) && ! isnumber( $inactive ) ) {
          $attr['title'] = ( ( strlen( $inactive ) > 80 ) ? substr( $inactive, 0, 72 ) .'...' : $inactive );
        }
        $text = adefault( $attr, 'text', '' );
        unset( $attr['text'] );
        return html_span( $attr, $text );
      } else {
        $attr['class'] = merge_classes( $baseclass, $linkclass );
        if( $post ) {
          return html_button( $form_id, $attr, $s );
        } else {
          return html_alink( $url , $attr );
        }
      }
    case 'url':
      need( $url, 'inlink(): no plain url available' );
      return $url;
    case 'js':
      if( $inactive ) {
        return 'true;';
      } else if( $post ) {
        return "submit_form( {$H_SQ}$form_id{$H_SQ}, {$H_SQ}$s{$H_SQ} );";
      } else {
        return "load_url( {$H_SQ}$url{$H_SQ} );";
      }
    case 'form':
      need( $url, 'inlink(): need plain url in context form' );
      $r = array( 'target' => '', 'action' => '#', 'onsubmit' => '', 'onclick' => '' );
      if( $inactive ) {
        return $r;
      }
      need( $form_id = adefault( $parameters, 'form_id', false ), 'context form requires parameter form_id' );
      $r['action'] = $url;
      return $r;
    default:
      error( 'undefined context: [$context]', LOG_FLAG_CODE, 'links' );
  }

}

?>
