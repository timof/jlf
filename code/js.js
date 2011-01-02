
// js.js
//
// based on:
//  das  javascript der foodsoft  
//  copyright Fc Schinke09 2006 
//
// modified: timo, 2007..2010


// neuesfenster: neues (grosses) Fenster oeffnen (fuer wiki)
//
function neuesfenster(url,name) {
  f=window.open(url,name,"dependent=yes,toolbar=yes,menubar=yes,location=yes,resizable=yes,scrollbars=yes");
  f.focus();
}

function closeCurrentWindow() {
  // this function is a workaround for the spurious " 'window.close()' is not a function" -bug
  // (occurring in some uses of onClick='window.close();'; strangely, the following works:):
  window.close();
}

function on_change( id ) {
  if( id ) {
    if( s = document.getElementById( 'submit_button_'+id ) )
      s.className = 'button';
    if( s = document.getElementById( 'reset_button_'+id ) )
      s.className = 'button';
    if( s = document.getElementById( 'floating_submit_button_'+id ) )
      s.style.display = 'inline';
  }
}

function on_reset( id ) {
  if( id ) {
    if( s = document.getElementById( 'submit_button_'+id ) )
      s.className = 'button inactive';
    if( s = document.getElementById( 'reset_button_'+id ) )
      s.className = 'button inactive';
    if( s = document.getElementById( 'floating_submit_button_'+id ) )
      s.style.display = 'none';
  }
}

function submit_form( id, field, value ) {
  f = document.forms[ id ];
  if( field )
    f.elements[field].value = value;
  // calling f.submit() explicitely will not trigger the onsubmit() handler, so we call it explicitely:
  if( f.onsubmit )
    f.onsubmit();
  f.submit();
}

function submit_date( id, which ) {
  f = document.forms[ id ];


}

function post_action( action, message ) {
  f = document.forms['update_form'];
  f.action.value = action;
  f.message.value = message;
  if( f.onsubmit )
    f.onsubmit();
  f.submit();
}

