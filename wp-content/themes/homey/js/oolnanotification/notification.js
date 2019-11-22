
(function($){
    $(document).ready(function(){
        if(typeof role != 'undefined' && role != null){
            setInterval(function(){
                $.when($.ajax({
                    url: typeof Homey_Listing != "undefined" ? Homey_Listing.ajaxURL : HOMEY_ajax_vars.admin_url+ 'admin-ajax.php',
                    data: {
                        "action": "oolna_new_reservation"
                    },
                    type: "post",
                    dataType: "json",
                    async: true
                })).done(ajaxReservationsCallback);
            }, 30000);
        }
    });
    function ajaxReservationsCallback(json){
        var $span = $(".reservation-menu a span");
        var $userAlert = $(".user-alert");
        if(typeof json.reservations != "undefined"){
            if(parseInt(json.reservations,10) > 0){
                $span.css("display", "block");
                $userAlert.css("display", "block");
            }else{
                $span.css("display", "none");
                $userAlert.css("display", "none");
            }
        }
    }
})(jQuery);