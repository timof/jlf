<?php


sql_transaction_boundary('*');

echo html_tag( 'h1', '', we('Research','Forschung') );


function schwerpunkt( $topic, $title, $image_view, $text ) {
  open_tr('keyarea');
    open_td();
      open_span( 'floatright', $image_view );
      open_tag( 'h3', '', $title );
      open_span( 'quads smallskips', $text );

    open_td();

      open_tag('h3', '', we('Professors:','Professuren:') );
      $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_PROFESSOR ) );
      open_ul('plain');
      foreach( $profs as $p ) {
        open_li( '',  alink_group_view( $p['groups_id'], 'fullname=1' ) );
      }
      close_ul('plain');

      if( ( $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_JOINT ) ) ) ) {
        open_tag('h3', '', we('by joint appointment:','gemeinsam berufene Professuren:') );
        foreach( $profs as $p ) {
          echo alink_group_view( $p['groups_id'] );
        }
      }

      if( ( $profs = sql_groups( array( 'keyarea' => $topic, 'status' => GROUPS_STATUS_SPECIAL ) ) ) ) {
        open_tag('h3', '', we('by special appointment:','ausserplanmäßige Professuren:') );
        foreach( $profs as $p ) {
          echo alink_group_view( $p['groups_id'] );
        }
      }


  close_tr();
}


echo html_tag( 'h2', 'medskips', we('Key areas and professors','Forschungsschwerpunkte und Professuren') );

open_table('keyareas td:quads;smallskipt;medskipb;solidtop,colgroup=62% 38%');

  $f = file_get_contents( './pp/fotos/crescent_small.jpg.base64' );
  schwerpunkt( 'theophys'
  , we('Theoretical and Statistical Physics','Theoretische und Statistische Physik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , we('
Many phenomena in Nature, society, or engineering exhibit complex dynamic
behaviour, that usually cannot be described by first principles approaches.

Our interdisciplinary research groups apply multiple techniques to such
phenomena including statistical physics, stochasticity, and non-linear
approaches. We describe systems over many time and length scales. Thus, we
study the dynamics of nanoscopic materials (for instance, quantum dots or
biological membranes) up to geophysical scales (atmosphere, groundwater) and
astrophysical systems (Saturn). We are also interested in living systems
ranging from biophysical processes of living cells to human motion patterns.

','

Viele Phänomene in Natur, Technik und Gesellschaft zeigen ein komplexes dynamisches
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
  , 'bla'
  );
    
  schwerpunkt( 'astro'
  , we('Astrophysics','Astrophysik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , 'bla'
  );

  schwerpunkt( 'photonik'
  , we('Photonics','Photonik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , 'bla'
  );
    
  schwerpunkt( 'photonik'
  , we('Physics Education','Didaktik der Physik')
  , photo_view( $f, 3, 'style=width:240px;height:120px;' )
  , 'bla'
  );

close_table();



















// open_div( 'medskips', inlink( 'gruppen', 'class=href smallskipt inlink,text='.we('Research Groups...','Arbeitsgruppen...') ) );


echo html_tag( 'h2', 'medskips', we('Recent Research Highlights','Aktuelle Forschungshighlights') );

publicationslist_view( '', array( 'list_options' => 'allow_download=1,orderby=year' ) );

open_div( 'medskips', inlink( 'publicationslist', 'class=href smallskipt inlink,text='.we('more publications...','weitere Veröffentlichungen..') ) );



echo html_tag( 'h4', 'medskips', we('Open positions','Offene Stellen / Themen für Abschlussarbeiten') );

positionslist_view( '', array( 'list_options' => 'allow_download=1' ) );

open_div( 'medskips', inlink( 'positionslist', 'class=href smallskipt inlink,text='.we('more positions...','weitere Stellen/Themen...') ) );


?>
