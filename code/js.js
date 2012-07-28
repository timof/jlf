//
// js.js
//
// based on:
//  das  javascript der foodsoft  
//  copyright Fc Schinke09 2006 
//
// modified: timo, 2007..2011


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
  var i;
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


// warp: animate element 'id', submit form form_id after some time
//
var wp_id = 0;
var wp_countdown = 0;

function warp( id, form_id, field, value ) {
  var td = document.getElementById( id );
  if( wp_id != id ) {
    td.className = 'warp_button warp0';
    return;
  }
  td.className = 'warp_button warp' + wp_countdown;
  if( --wp_countdown > 0 ) {
    window.setTimeout( "warp( '"+id+"','"+form_id+"','"+field+"','"+value+"' )", 70 );
  } else {
    submit_form( form_id ? form_id : 'update_form', '', '', field, value );
    td.className = 'warp_button warp0'; // restore, in case we submitted to another window
  }
}

function schedule_warp( id, form_id, field, value ) {
  wp_id = id;
  wp_countdown = 9;
  window.setTimeout( "warp( '"+id+"','"+form_id+"','"+field+"','"+value+"' )", 200 );
}

function cancel_warp() {
  wp_id = 0;
}

var scroll_dir = '';
function scroll() {
  switch( scroll_dir ) {
    case 'up':
      window.scrollBy( 0, -1 );
      break;
    case 'down':
      window.scrollBy( 0, 1 );
      break;
    case 'left':
      window.scrollBy( -1, 0 );
      break;
    case 'right':
      window.scrollBy( 1, 0 );
      break;
    default:
      return false;
  }
  window.setTimeout( "scroll()", 50 );
  return true;
}


// navigation tools:

var nav = 0;
function nav_on() {
  nav = 1;
  $('navigation').style.display = '';
}

function nav_off() {
  nav = 0;
  $('navigation').style.display = 'none';
}


function fix_shadow( popup, shadow ) {
  popup = $( popup );
  shadow = $( shadow );
  shadow.style.width = popup.getWidth();
  shadow.style.min_width = popup.getWidth();
  shadow.style.max_width = popup.getWidth();
  shadow.style.height = popup.getHeight();
  shadow.style.min_height = popup.getHeight();
  shadow.style.max_height = popup.getHeight();
  // shadow.style.top = popup.style.top;
  // shadow.style.left = popup.style.left + 20;
  // shadow.style.display = '';

  // payload = $( 'payload' );
  // payload.style.opacity=0.5;
}


var popup_count = 0;
var popup_do_fade = 0;
function fade_popup() {
  popup = $( 'popupframe' );
  payload = $( 'payload' );
  body = $( 'thebody' );

  if( popup_count > 0 ) {
    c1 = 'fedcb'.substr( popup_count / 4, popup_count / 4 );
    c2 = 'fb73'.substr( popup_count % 4, popup_count % 4 );
    color = '#'+c1+c2+c1+c2+c1+c2;
    body.style.background_color = color;
    payload.style.background_color = color;

    popup.style.opacity = popup_count / 20.0;
    payload.style.opacity = 1.0 - popup_count / 40.0;
    popup.style.z_index = +1;
    popup.style.display = 'block';
  } else {
    popup.style.display = 'none';
    body.style.background_color = '#ffffff;'
    payload.style.background_color = '#ffffff;'
  }

  if( popup_do_fade ) {
    if( popup_count > 0 ) {
      popup_count--;
      setTimeout( "fade_popup();", 50 );
    } else {
      popup.style.display = 'none';
    }
  } else {
    if( popup_count <= 20 ) {
      popup_count++;
      setTimeout( "fade_popup();", 50 );
    }
  }
}


function popup( msg, on_confirm ) {
  popup_count = 0;
  popup_do_fade = 0;
  fade_popup();
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

function center( id ) {
  var box, xoff, yoff;
  box = $( id );

  box.style.position = fixed;

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
function flash() {
  msgid = $('flashmessage');
  payloadid = $('payload');
  flashcounter++;
  payloadid.style.opacity = ( 40 - flashcounter ) / 40.0;
  if( flashcounter < 20 ) {
    msgid.style.opacity = flashcounter / 20.0;
    window.setTimeout( "flash();", 40.0 );
  } else if( flashcounter < 40 ) {
    msgid.style.opacity = ( 40 - flashcounter ) / 20.0;
    window.setTimeout( "flash();", 70.0 );
  } else {
    window.close();
  }
}

function flash_close_message( m ) {
  id = $('flashmessage');
  id.firstChild.data = m;
  id.style.opacity = '0.0';
  id.style.display = 'block';
  flashcounter = 0;
  flash();
  // window.setTimeout( "close();", 3500 );
}

