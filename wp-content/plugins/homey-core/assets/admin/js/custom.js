( function( $ ) {
    'use strict';

    $( function() {

        $('.homey-clone').cloneya();

        $( '.homey-fbuilder-js-on-change' ).change( function() {
            var field_type = $( this ).val();
            $('.homey-clone').cloneya();

            if(field_type == 'select') {
                $.post( ajaxurl, { action: 'homey_load_select_options', type: field_type }, function( response ) {
                    $( '.homey_select_options_loader_js' ).html( response );
                    $('.homey-clone').cloneya();
                } );
            } else {
                $( '.homey_select_options_loader_js' ).html('');
            }
        } );
    } );
} )( jQuery );