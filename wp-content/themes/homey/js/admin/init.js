(function($) {
    "use strict";  
    
    $(document).ready(function ($) {
    
        $('#homey_page_sidebar').on('change', function(){
            Check_Page_Sidebar();
        });
        $('#page_template').on('change', function(){
            checkTemplate();
        });
        
        function Check_Page_Sidebar() {
            var page_sidebar = jQuery('#homey_page_sidebar').attr('value');
            if( page_sidebar == 'yes' ) {
                jQuery('.homey_selected_sidebar').stop(true,true).fadeIn(500);
            } else {
                jQuery('.homey_selected_sidebar').hide();
            }
        }

        function checkTemplate() {

            var template = jQuery('#page_template').attr('value');

            if( template == 'template/template-listing-list.php' || template == 'template/template-listing-grid.php' || template == 'template/template-listing-card.php' || template == 'template/template-listing-sticky-map.php' ) {
                jQuery('#homey_listing_template').stop(true,true).fadeIn(500);
        
            } else {
                jQuery('#homey_listing_template').hide();
            }

            if( template == 'template/template-half-map.php' ) {
                jQuery('#homey_listing_template_halfmap').stop(true,true).fadeIn(500);
        
            } else {
                jQuery('#homey_listing_template_halfmap').hide();
            }

        }

        jQuery(window).load(function(){ 
            Check_Page_Sidebar();
            checkTemplate();
            
        });
    	
    });
        
})(jQuery);