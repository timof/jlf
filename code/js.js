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
      if( s = $( 'action_template_'+envs[i] ) )
        s.style.display = 'none';
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

function do_on_submit( id ) {
  f = document.forms[ id ];
  if( f )
    if( f.elements.offs )
      f.elements.offs.value = window.pageXOffset + 'x' + window.pageYOffset;
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

function submit_form( id, s, field, value ) {
  f = document.forms[ id ];
  f.elements.s.value = s;
  f.elements.extra_field.value = ( field ? field : '' );
  f.elements.extra_value.value = ( value ? value : '0' );
  if( f.onsubmit ) {
    f.onsubmit();
  }
  f.submit();
}

function submit_input( id, name ) {
  i = $( 'input_' + id );
  if( ! name )
    name = i.name;
  // if( confirm( name + ': ' + i.value ) )
    submit_form( 'update_form', '', name, i.value );
}

function load_url( url, window_name, window_options ) {
  url = url + '&offs=' + window.pageXOffset + 'x' + window.pageYOffset;
  url = url.replace( /&amp;/g, '&' );
  if( window_name ) {
    window.open( url, window_name, window_options ).focus();
  } else {
    if( warn_if_unsaved_changes() )
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
  nav = 1;
  $('navigation').style.display = 'none';
}


function add_shadow( id ) {
  popup = $( 'popup_'+id );
  shadow = $( 'shadow_'+id );
  shadow.style.width = popup.getWidth();
  shadow.style.min_width = popup.getWidth();
  shadow.style.max_width = popup.getWidth();
  shadow.style.height = popup.getHeight();
  shadow.style.min_height = popup.getHeight();
  shadow.style.max_height = popup.getHeight();
  shadow.style.display = '';
}
  
