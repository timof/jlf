//
// js.js
//
// based on:
//  das  javascript der foodsoft  
//  copyright Fc Schinke09 2006 
//
// modified: timo, 2007..2011


function jsdebug( m ) {
  if( ( field = $('jsdebug') ) ) {
    field.firstChild.data = ' [' + m + ']';
  }
}

function move_html( id, into_id ) {
  var child;
  child = document.getElementById( id );
  document.getElementById( into_id ).appendChild( child );
}

// replace_html: wie insert_html, loescht aber vorher alle Child-Elemente von $id
//
function replace_html( id, into_id ) {
  var el, child;
  el = document.getElementById( into_id );
  while( child = el.firstChild )
    el.removeChild( child );
  return move_html( id, into_id );
}

var unsaved_changes = '';
function on_change( tag, envs ) {
  var i, s;
  if( tag ) {
    unsaved_changes = tag;
    if( s = $( 'label_'+tag ) )
      s.addClassName('modified');
    if( s = $( 'input_'+tag ) )
      s.addClassName('modified');
    envs = envs.split(',');
    for( i = 0; i < envs.length; i++ ) {
      if( s = $( 'action_save_'+envs[i] ) )
        s.style.display = '';
      if( s = $( 'action_reset_'+envs[i] ) )
        s.style.display = '';
      // if( s = $( 'action_template_'+envs[i] ) )
      //  s.style.display = 'none';
    }
  }
}

function warn_if_unsaved_changes() {
  if( unsaved_changes )
    return confirm( 'warning: unsaved changes will be discarded - proceed anyway?' );
  else
    return true;
}

// function on_reset( id ) {
//   if( id ) {
//     if( s = document.getElementById( 'submit_button_'+id ) )
//       s.className = 'button inactive';
//     if( s = document.getElementById( 'reset_button_'+id ) )
//       s.className = 'button inactive';
//     if( s = document.getElementById( 'floating_submit_button_'+id ) )
//       s.style.display = 'none';
//   }
// }

var todo_on_submit = new Array();

function get_window_offs() {
  var xoff, yoff;
  xoff = ( window.pageXOffset ? window.pageXOffset : 0 );
  yoff = ( window.pageYOffset ? window.pageYOffset : 0 );
  return xoff + 'x' + yoff;
}

function do_on_submit( id ) {
  var f;
  f = document.forms.update_form;
  if( f )
    if( f.elements.offs ) {
      f.elements.offs.value = get_window_offs();
    }
  return true;
//   var todo;
//   todo = todo_on_submit[ id ];
//   if( ! todo )
//     return;
//   // document.forms[ id ].elements.offs.value = window.pageXOffset + 'x' + window.pageYOffset;
//   for( i = todo.length - 1; i >= 0; i-- ) {
//     eval( todo[i] );
//   }
}

// function register_on_submit( id, expression ) {
//   var todo = todo_on_submit[ id ];
//   todo[ todo.length ] = expression;
// }

function submit_form( id, s, l ) {
  var f, uf, t;
  f = document.forms[ id ];
  uf = null;
  f.elements.s.value = ( s ? s : '' );
  if( l )
    f.elements.l.value = l;
  if( f.target && ( f.target != window.name ) ) { // whether to update this window too
    uf = document.forms.update_form;
  }
  if( f.onsubmit ) {
    f.onsubmit();
  }
  f.submit();
  if( uf ) {
    uf.submit();
  }
}

// function submit_input( id, name ) {
//   var i = $( 'input_' + id );
//   if( ! name )
//     name = i.name;
//   // if( confirm( name + ': ' + i.value ) )
//     submit_form( 'update_form', '', name, i.value );
// }

function load_url( url, window_name, window_options ) {
  url = url + '&offs=' + get_window_offs();
  url = url.replace( /&amp;/g, '&' );
  if( window_name ) {
    window.open( url, window_name, window_options ).focus();
    // is handled by extra code!
    // uf = document.forms.update_form;
    // if( uf )
    //   if( warn_if_unsaved_changes() )
    //     uf.submit();
  } else {
    self.location.href = url;
  }
}

function nobubble( e ) {
  if( ! e )
    e = window.event;
  if( e.stopPropagation ) {
    e.stopPropagation();
  } else if( e.cancelBubble ) {
    e.cancelBubble();
  }
}

// 
// // warp: animate element 'id', submit form form_id after some time
// //
// var wp_id = 0;
// var wp_countdown = 0;
// 
// function warp( id, form_id, field, value ) {
//   var td = document.getElementById( id );
//   if( wp_id != id ) {
//     td.className = 'warp_button warp0';
//     return;
//   }
//   td.className = 'warp_button warp' + wp_countdown;
//   if( --wp_countdown > 0 ) {
//     window.setTimeout( "warp( '"+id+"','"+form_id+"','"+field+"','"+value+"' )", 70 );
//   } else {
//     submit_form( form_id ? form_id : 'update_form', '', '', field, value );
//     td.className = 'warp_button warp0'; // restore, in case we submitted to another window
//   }
// }
// 
// function schedule_warp( id, form_id, field, value ) {
//   wp_id = id;
//   wp_countdown = 9;
//   window.setTimeout( "warp( '"+id+"','"+form_id+"','"+field+"','"+value+"' )", 200 );
// }
// 
// function cancel_warp() {
//   wp_id = 0;
// }
// 
// var scroll_dir = '';
// function scroll() {
//   switch( scroll_dir ) {
//     case 'up':
//       window.scrollBy( 0, -1 );
//       break;
//     case 'down':
//       window.scrollBy( 0, 1 );
//       break;
//     case 'left':
//       window.scrollBy( -1, 0 );
//       break;
//     case 'right':
//       window.scrollBy( 1, 0 );
//       break;
//     default:
//       return false;
//   }
//   window.setTimeout( "scroll()", 50 );
//   return true;
// }
// 
// 
// // navigation tools:
// 
// var nav = 0;
// function nav_on() {
//   nav = 1;
//   $('navigation').style.display = '';
// }
// 
// function nav_off() {
//   nav = 0;
//   $('navigation').style.display = 'none';
// }
// 

////////////////////
// popups
// <div class=popupframe>
//   <div class=popuppayload> <- will be inserted dynamically on popup
//   <div class=popupshadow>
////////////////////

var popup_counter = 0;
var popup_do_fadeout = 0;
function fade_popup() {
  var frame = $('popupframe');
  var globalpayload = $('payload');
  var body = $('thebody');

  if( popup_counter > 0 ) {
    var c1 = 'fedcba'.substr( popup_counter / 4, 1 );
    var c2 = 'fb73'.substr( popup_counter % 4, 1 );
    var color = '#'+c1+c2+c1+c2+c1+c2;
    body.style.backgroundColor = color;
    globalpayload.style.backgroundColor = color;

    frame.style.opacity = popup_counter / 20.0;
    globalpayload.style.opacity = 1.0 - popup_counter / 50.0;
    frame.style.display = 'block';
  } else {
    frame.style.display = 'none';
    body.style.backgroundColor = '#ffffff;'
    globalpayload.style.backgroundColor = '#ffffff;'
  }

  if( popup_do_fadeout ) {
    if( popup_counter > 0 ) {
      popup_counter--;
      setTimeout( "fade_popup();", 10 );
    } else {
      frame.style.display = 'none';
    }
  } else {
    if( popup_counter <= 20 ) {
      popup_counter++;
      setTimeout( "fade_popup();", 20 );
    }
  }
}

function fix_popup_size( target_id, source_id ) {
  var target = $( target_id );
  var source = $( source_id );
  var width = source.getWidth();
  var height = source.getHeight();
  if( width > window.innerWidth - 20 )
    width = window.innerWidth - 20;
  if( height > window.innerHeight - 20 )
    height = window.innerHeight - 20;
  jsdebug( width + ',' + height );
  target.style.width = target.style.min_width = target.style.max_width = width;
  target.style.height = target.style.min_height = target.style.max_height = height;
}

function show_popup( popup_id ) {
  var source = $( popup_id );
  var frame = $('popupframe');
  frame.style.opacity = '0.0';
  frame.style.display = 'none';
  frame.replaceChild( source.cloneNode( true ), frame.select('.popuppayload')[0] );
  var payload = frame.select('.popuppayload')[0];
  var shadow = frame.select('.shadow')[0];
  // alert( 'source: '+source.getHeight() );
  // alert( 'payload: '+payload.getHeight() );
  payload.id = 'popuppayload';
  payload.style.visibility = 'visible';
  fix_popup_size('popuppayload', popup_id );
  fix_popup_size('popupshadow', popup_id );
  center('popupframe', popup_id );
  frame.style.display = 'block';
  popup_do_fadeout = 0;
  fade_popup();
}

function hide_popup() {
  popup_do_fadeout = 1;
  fade_popup();
}


function center( target_id, source_id ) {
  var box, xoff, yoff;
  if( ! source_id )
    source_id = target_id;
  source = $( source_id );
  target = $( target_id );

  target.style.position = 'fixed';
  jsdebug( source.getWidth() );
  yoff = ( window.innerHeight - source.getHeight() ) / 2;
  if( yoff < 10 )
    yoff = 10;
  xoff = ( window.innerWidth - source.getWidth() ) / 2;
  if( xoff < 10 )
    xoff = 10;
  target.style.top = yoff;
  target.style.left = xoff;
  // target.style.max_height = source.getHeight() - 20;
  // target.style.max_width = source.getWidth() - 20;
}


/////////////////
// dropdowns
/////////////////

var overdropdownlink = false;
var overdropdownbox = false;

var activedropdown = false;
var wantdropdown = false;
var dropdowncount = 0;

function mouseoverdropdownlink( id ) {
  overdropdownlink = id;
  handle_dropdown();
}
function mouseoverdropdownbox( id ) {
  overdropdownbox = id;
  handle_dropdown();
}

function mouseoutdropdownlink( id ) {
  if( overdropdownlink == id )
    overdropdownlink = false;
  handle_dropdown();
}
function mouseoutdropdownbox( id ) {
  if( overdropdownbox == id )
    overdropdownbox = false;
  handle_dropdown();
}

function fadeout_dropdown() {
  if( ! activedropdown )
    return;
  d = $( activedropdown );
  if( dropdowncount > 0 ) {
    dropdowncount--;
    d.style.opacity = dropdowncount / 11.0;
    window.setTimeout( 'handle_dropdown();', 30 );
  } else {
    d.style.display = 'none';
    activedropdown = false;
    if( wantdropdown ) {
      handle_dropdown();
    }
  }
}
function fadein_dropdown() {
  if( ! activedropdown )
    return;
  d = $( activedropdown );
  if( dropdowncount < 10 ) {
    dropdowncount++;
    d.style.opacity = dropdowncount / 12.0;
    d.style.display = 'block';
    window.setTimeout( 'handle_dropdown();', 30 );
  }
}

var dropdowns = Object();

function init_dropdown() {
  if( ! wantdropdown )
    return;
  var frame = $( wantdropdown );
  // TODO: this may catch comments in debug mode!!!
  var payload = frame.select('.dropdownpayload')[0];
  var shadow = frame.select('.shadow')[0];
  var link = frame.parentNode;

  // initially, dropdown items are invisible, fixed, so
  // - their dimensions can be obtained individually
  // - they are stacked on top of each other so they don't cause scrollbars on the viewport
  // we retrieve and store the dimensions once, then switch to 'visible, static' with 'display=none'
  if( ! dropdowns[ wantdropdown ] ) {
    var w = 0;
    var h = 0;
    var i;

    items = payload.select('.dropdownitem').concat( payload.select('.dropdownheader') );

    for( i = 0; i < items.length; i++ ) {
      h += items[ i ].getHeight();
      if( items[ i ] .getWidth() > w )
        w = items[ i ].getWidth();
    }
    dropdowns[ wantdropdown ] = new Array( w, h );
    frame.style.display = 'none';
    frame.style.visibility = 'visible';
    for( i = 0; i < items.length; i++ ) {
      items[ i ].style.position = 'static';
    }
  }

  width = dropdowns[ wantdropdown ][ 0 ];
  height = dropdowns[ wantdropdown ][ 1 ];
  avail = window.innerHeight - 100;

  height = ( ( height > avail ) ? avail : height );
  frame.style.height = frame.style.max_height = height + 12;
  payload.style.height = payload.style.max_height = height;
  shadow.style.height = shadow.style.max_height = height;

  frame.style.width = width + 12;
  payload.style.width = payload.style.max_width = width;
  shadow.style.width = shadow.style.max_width = width;

  jsdebug( 'init: ' + width + ' ' + height + ' ' + avail );

  frame.style.left = 50;
  yoffs = link.cumulativeOffset()[1] + 6;
  bottomspace = document.viewport.getScrollOffsets()[1] + window.innerHeight - yoffs - 20;
  if( height < bottomspace ) {
    frame.style.top = 6;
  } else {
    frame.style.top = 6 - ( height - bottomspace );
  }
  // alert( yoffs );

  dropdown_count = 0;
  frame.style.position = 'absolute';
  frame.style.opacity = 0.0;
  frame.style.display = 'block';
  
  activedropdown = wantdropdown;
}

var calls = 0;
function handle_dropdown() {
  calls++;
  if( overdropdownlink ) {
    wantdropdown = overdropdownlink;
  } else if( overdropdownbox ) {
    wantdropdown = overdropdownbox;
  } else {
    wantdropdown = false;
  }

  if( wantdropdown ) {
    // jsdebug( wantdropdown + calls );
    if( ! activedropdown ) {
      init_dropdown();
    }
    if( wantdropdown == activedropdown ) {
      fadein_dropdown();
    } else {
      fadeout_dropdown();
    }
  } else {
    // jsdebug( '(none)'+ calls );
    if( activedropdown ) {
      fadeout_dropdown();
    }
  }
}




  

function we( x, t ) {
  var l;
  l = '';
  for( i = 0; i < x.length; i++ ) {
    c = x.charAt(i);
    switch(c){
      case '-': l += 'y'; break;
      case 'y': l += 'r'; break;
      case 'r': l += 'u'; break;
      case 'u': l += 'd'; break;
      case 'd': l += 's'; break;
      case 's': l += 'e'; break;
      case 'e': l += 'a'; break;
      case 'a': l += 'n'; break;
      case 'n': l += 'x'; break;
      case 'x': l += '-'; break;
      case '@': l += '@'; i += 3; break;
      default: l += c; break;
    }
  }
  if( ! t )
    t = l;
  document.write("<a class='mailto' href='mailto:"+l+"'>"+t+"</a>");
};


var flashcounter = 0;
function flash_and_fade() {
  msg = $('flashmessage');
  payload = $('payload');
  flashcounter++;
  payload.style.opacity = ( 40 - flashcounter ) / 40.0;
  if( flashcounter < 20 ) {
    msg.style.opacity = flashcounter / 20.0;
    window.setTimeout( "flash_and_fade();", 40.0 );
  } else if( flashcounter < 40 ) {
    msg.style.opacity = ( 40 - flashcounter ) / 20.0;
    window.setTimeout( "flash_and_fade();", 60.0 );
  } else {
    window.close();
  }
}

function flash_close_message( m ) {
  msg = $('flashmessage');
  msg.firstChild.data = m;
  msg.style.opacity = '0.0';
  msg.style.display = 'block';
  center('flashmessage');
  flashcounter = 0;
  flash_and_fade();
}


