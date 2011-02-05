function bsuite_linktome_selectall() {
	jQuery('input.linktome_input').click( function() { 
		jQuery(this).select();
	} )
}
jQuery(document).ready(bsuite_linktome_selectall);