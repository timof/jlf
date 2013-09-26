<?php // pp/windows/forschung/forschung.php


sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Research','Forschung') );


function schwerpunkt( $topic, $title, $image_view, $text ) {
  open_tr('keyarea');
    open_td('textaroundphoto');
      open_span( 'floatright', $image_view );
      open_tag( 'h3', '', $title );
      open_span( 'smallskips', $text );

    open_td();

      open_tag('h3', '', we('Professors:','Professuren:') );
      $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_PROFESSOR ) );
      open_ul('plain');
      foreach( $profs as $p ) {
        open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
//        $t = html_div( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
//        if( ( $h_id = $p['head_people_id'] ) ) {
//          $t .= html_div( 'qquadl smaller', alink_person_view( $h_id ) );
//        }
//        open_li( '', $t );
      }
      close_ul('plain');

      if( ( $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_JOINT ) ) ) ) {
        open_tag('h3', '', we('by joint appointment:','gemeinsam berufene Professuren:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
        }
        close_ul();
      }

      if( ( $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_SPECIAL ) ) ) ) {
        open_tag('h3', '', we('by special appointment:','außerplanmäßige Professuren:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
        }
        close_ul();
      }

      if( ( $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_EXTERNAL ) ) ) ) {
        open_tag('h3', '', we('external:','externe:') );
        open_ul('plain');
        foreach( $profs as $p ) {
          open_li( '', alink_group_view( $p['groups_id'], 'fullname=1,showhead=1' ) );
        }
        close_ul();
      }

  close_tr();
}


echo html_tag( 'h2', 'medskips', we('Key areas and professors','Forschungsschwerpunkte und Professuren') );

open_table('keyareas td:qquads;medskipt;medskipb;solidtop,colgroup=62% 38%');

  $f = file_get_contents( './pp/fotos/crescent_small.jpg.base64' );
  schwerpunkt( 'theophys'
  , we('Theoretical and Statistical Physics','Theoretische und Statistische Physik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , we(
    'Many phenomena in Nature, society, or engineering exhibit complex dynamic
     behaviour, that usually cannot be described by first principles approaches.
     
     Our interdisciplinary research groups apply multiple techniques to such
     phenomena including statistical physics, stochasticity, and non-linear
     approaches. We describe systems over many time and length scales. Thus, we
     study the dynamics of nanoscopic materials (for instance, quantum dots or
     biological membranes) up to geophysical scales (atmosphere, groundwater) and
     astrophysical systems (Saturn). We are also interested in living systems
     ranging from biophysical processes of living cells to human motion patterns.

   ', 'Viele Phänomene in Natur, Technik und Gesellschaft zeigen ein komplexes dynamisches
       Verhalten, das nicht aus fundamentalen physikalischen Prinzipien ableitbar ist.
       
       Zur Beschreibung derartiger chaotischer Phänomene kombinieren
       unsere interdisziplinär kooperierenden Arbeitsgruppen ganz unterschiedliche
       Techniken unter anderen aus der Statistischen Physik, der nichtlinearen Physik und
       der Stochastik.
       
       Damit beschreiben wir Systeme auf sehr unterschiedlichen Zeit- und Längenskalen: von
       nanoskopischen Materialien (etwa Quantenpunkte oder biologische Membranen)
       über geophysikalische Skalen (Atmosphäre, Grundwasser) bis hin zu
       Systemen der Astrophysik (wie das Ringsystem des Saturn).
       Insbesondere befassen wir uns auch mit lebenden Systemen, von den Prozessen
       einer einzelenen Zelle bis hin zu menschlichen Bewegungsmustern.
       
       ')
  );
    
  schwerpunkt( 'softmatter'
  , we('Soft Matter Phycis','Physik Weicher Materie')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , "Die Erforschung der Struktur und der Eigenschaften weicher Materie
     (Soft Matter) ist eine der aktivsten Forschungsrichtungen der
     Physik kondensierter Materie. Diese molekularen Materialsysteme sind häufig
     nur durch schwache Kräfte wie z.B.
     van-der-Waals Wechsel{$SHY}wirkungen
     oder Wasserstoffbrücken{$SHY}bindungen gebunden. Daraus ergibt sich eine
     hohe Vielfalt an Strukturen, wie sie in den klassischen, kovalent
     gebundenen Materialien wie Metallen und anorganischen Halbleitern
     nicht zu finden ist. Das Verständnis von Struktur und Funktion
     gezielt hergestellter weicher Materie daher ist eine der zentralen
     Herausforderungen dieses modernen Forschungsgebietes und bildet den
     Schwerpunkt der Arbeiten des Forschungsschwerpunkts am Institut für
     Physik.
     "
  );
    
  schwerpunkt( 'astro'
  , we('Astrophysics','Astrophysik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam congue, mauris id ultrices ultrices, odio metus condimentum orci, eu blandit ipsum nisl et nibh. Maecenas velit quam, accumsan ac, venenatis id, pharetra cursus, risus. Vivamus imperdiet. Cras vel lacus. Sed eu sem. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nam nisl purus, fermentum ac, sagittis in, luctus vitae, lectus. Aliquam nec nulla. Maecenas sapien. Aliquam vitae est sit amet urna malesuada consequat. Fusce pellentesque ultrices lectus. Suspendisse potenti. Donec fermentum suscipit leo. Fusce nonummy dui. Sed nonummy lectus. Phasellus ipsum diam, scelerisque ut, nonummy at, fringilla in, eros. Phasellus malesuada nibh.
      Curabitur nonummy tellus eget eros consequat egestas. Ut ut nunc. Sed ante lacus, viverra ut, porttitor at, rutrum ac, nisl. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean rutrum purus et metus.
    "
  );

  schwerpunkt( 'photonik'
  , we('Photonics','Photonik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , "
      Der Forschungsschwerpunkt  Photonik/Quantenoptik an der Universität
      Potsdam
      konzentriert sich auf Fragen zum Verständnis der Physik des Lichts und
      seiner Wechselwirkung mit Materie. Von besonderem Interesse sind die dabei
      wirksam werdenden Quanteneigenschaften: merkwürdige Phänomene mit
      teils überraschenden Konsequenzen, etwa in der Quanteninformation.

      In der Anwendung spiegelt sich das wider in der Realisierung besonderer
      Lichtquellen, die es erlauben bekannte physikalische Grenzen zu
      überwinden.

      Nichtlineare optische Methoden werden sowohl in der spektroskopischen Untersuchung und
      Charakterisierung von Stoffen als auch in der Anwendung bei der Realisierung neuartiger
      Lichtquellen eingesetzt. Die theoretischen und praktischen Arbeiten beziehen
      sich dabei auf die Bereiche ultrakalte Gase, Nano-Optik, Quanteninformationsverarbeitung,
      Nichtlineare Spektroskopie, auf die Lasertechnik sowie die optische Messtechnik.

      Im Ergebnis der Forschung finden
      neuartige Konzepte ihre praktische Anwendung
      z.B. in der Medizintechnik, Informationstechnologie, Lasertechnik sowie  weiteren
      analytischen Verfahren für die Lebenswissenschaften.
    "
  );
    
  $f = file_get_contents( './pp/fotos/lehre.jpg.base64' );
  schwerpunkt( 'didaktik'
  , we('Physics Education','Didaktik der Physik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , 'bla'
  );

close_table();



















// open_div( 'medskips', inlink( 'gruppen', 'class=href smallskipt inlink,text='.we('Research Groups...','Arbeitsgruppen...') ) );




// publications = sql_publications(
//  'year >= '.( $current_year - 1 )
//  array( 'limit_from' => 1 , 'limit_to' => 3 , 'orderby' => 'year DESC, ctime DESC' )
// ;
// f( count( $publications ) >= 2 ) {
//  echo html_tag( 'h2', 'medskips', we('Recent Research Highlights','Aktuelle Forschungshighlights') );
//  echo html_tag( 'h2','bigskipt', we('Current Publications','Aktuelle Veröffentlichungen') );
//  echo publication_columns_view( $publications );
//  echo html_div( '', inlink( 'publikationen', 'text='.we('more publications...','weitere Veröffentlichungen...') ) );
// 


// publicationslist_view( '', array( 'list_options' => 'allow_download=1,orderby=year' ) );

// open_div( 'medskips', inlink( 'publicationslist', 'class=href smallskipt inlink,text='.we('more publications...','weitere Veröffentlichungen..') ) );



echo html_tag( 'h4', 'medskips', we('Open positions','Offene Stellen / Themen für Abschlussarbeiten') );

positionslist_view( '', array( 'list_options' => 'allow_download=1' ) );

open_div( 'medskips', inlink( 'positionslist', 'class=href smallskipt inlink,text='.we('more positions...','weitere Stellen/Themen...') ) );


?>
