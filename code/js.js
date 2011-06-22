//
// js.js
//
// based on:
//  das  javascript der foodsoft  
//  copyright Fc Schinke09 2006 
//
// modified: timo, 2007..2010



function closeCurrentWindow() {
  // this function is a workaround for the spurious " 'window.close()' is not a function" -bug
  // (occurring in some uses of onClick='window.close();'; strangely, the following works:):
  window.close();
}

// function on_change( id ) {
//   if( id ) {
//     if( s = document.getElementById( 'submit_button_'+id ) )
//       s.className = 'button';
//     if( s = document.getElementById( 'reset_button_'+id ) )
//       s.className = 'button';
//     if( s = document.getElementById( 'floating_submit_button_'+id ) )
//       s.style.display = 'inline';
//   }
// }
// 
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

function submit_form( id, action, message, field, value ) {
  f = document.forms[ id ];
  f.elements.action.value = action ? action : 'nop';
  f.elements.message.value = message ? message : '0';
  f.elements.extra_field.value = field ? field : '';
  f.elements.extra_value.value = value ? value : '0';
  f.elements.offs.value = window.pageXOffset + 'x' + window.pageYOffset;
  if( f.onsubmit ) {
    f.onsubmit();
  }
  f.submit();
}

function load_url( url, window_name, window_options ) {
  url = url + '&offs=' + window.pageXOffset + 'x' + window.pageYOffset;
  url = url.replace( /&amp;/g, '&' );
  if( window_name )
    window.open( url, window_name, window_options ).focus();
  else
    self.location.href = url;
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


