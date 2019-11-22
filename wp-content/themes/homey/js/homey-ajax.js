if ( typeof HOMEY_ajax_vars !== "undefined" ) {
    var booking_start_hour = HOMEY_ajax_vars.booking_start_hour;
    var booking_end_hour = HOMEY_ajax_vars.booking_end_hour;
    var homey_min_book_days = HOMEY_ajax_vars.homey_min_book_days;
    var booked_hours_array = HOMEY_ajax_vars.booked_hours_array;
    var pending_hours_array = HOMEY_ajax_vars.pending_hours_array;

    if( booked_hours_array !=='' && booked_hours_array.length !== 0 ) {
        booked_hours_array   = JSON.parse (booked_hours_array);
    }

    if( pending_hours_array !=='' && pending_hours_array.length !== 0 ) {
        pending_hours_array   = JSON.parse (pending_hours_array);
    }
}
var googleAgendaList = [];
function setCostError(costs, index, errorValue) {
    return costs.map(function(cost){
        if(cost.index == index){
            cost.error = errorValue;
        }
        return cost;
    });
}

function getCostErrors(costs) {
    return costs.filter(function(cost){
        return (typeof cost.error == "undefined" || typeof cost.data.guests == "undefined") ||
            ((typeof cost.error != "undefined" && typeof cost.data.guests != "undefined") && (cost.error == true || cost.data.guests == ""));
    });
}

function disableReservationButton(disable) {
    var $ = jQuery;
    if (!disable) {
        $('#request_hourly_reservation, #request_hourly_reservation_mobile').removeAttr("disabled");
        $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').removeAttr("disabled");
    } else {
        $('#request_hourly_reservation, #request_hourly_reservation_mobile').attr("disabled", true);
        $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').attr("disabled", true);
    }
}

jQuery(document).ready(function ($) {
    "use strict";

    if ( typeof HOMEY_ajax_vars !== "undefined" ) {
        
        var ajaxurl = HOMEY_ajax_vars.admin_url+ 'admin-ajax.php';
        var login_redirect_type = HOMEY_ajax_vars.redirect_type;
        var login_redirect = HOMEY_ajax_vars.login_redirect;
        var is_singular_listing = HOMEY_ajax_vars.is_singular_listing;
        var paypal_connecting = HOMEY_ajax_vars.paypal_connecting;
        var login_sending = HOMEY_ajax_vars.login_loading;
        var process_loader_spinner = HOMEY_ajax_vars.process_loader_spinner;
        var currency_updating_msg = HOMEY_ajax_vars.currency_updating_msg;
        var homey_date_format = HOMEY_ajax_vars.homey_date_format;
        var userID = HOMEY_ajax_vars.user_id;
        var homey_reCaptcha = HOMEY_ajax_vars.homey_reCaptcha;
        var is_listing_detail = HOMEY_ajax_vars.is_listing_detail;
        var is_tansparent = HOMEY_ajax_vars.homey_tansparent;
        var retina_logo = HOMEY_ajax_vars.retina_logo;
        var retina_logo_splash = HOMEY_ajax_vars.retina_logo_splash;
        var retina_logo_mobile = HOMEY_ajax_vars.retina_logo_mobile;
        var retina_logo_mobile_splash = HOMEY_ajax_vars.retina_logo_mobile_splash;
        var no_more_listings = HOMEY_ajax_vars.no_more_listings;
        var allow_additional_guests = HOMEY_ajax_vars.allow_additional_guests;
        var allowed_guests_num = HOMEY_ajax_vars.allowed_guests_num;
        var agree_term_text = HOMEY_ajax_vars.agree_term_text;
        var choose_gateway_text = HOMEY_ajax_vars.choose_gateway_text;
        var success_icon = HOMEY_ajax_vars.success_icon;
        var calendar_link = HOMEY_ajax_vars.calendar_link;
        var focusedInput_2 = null;

        var compare_url = HOMEY_ajax_vars.compare_url;
        var add_compare = HOMEY_ajax_vars.add_compare;
        var remove_compare = HOMEY_ajax_vars.remove_compare;
        var compare_limit = HOMEY_ajax_vars.compare_limit;
        var homey_booking_type = HOMEY_ajax_vars.homey_booking_type;

        var homey_is_rtl = HOMEY_ajax_vars.homey_is_rtl;

        if( homey_is_rtl == 'yes' ) {
            homey_is_rtl = true;
        } else {
            homey_is_rtl = false;
        }

         var homey_timeStamp_2 = function(str) {
          return new Date(str.replace(/^(\d{2}\-)(\d{2}\-)(\d{4})$/,
            '$2$1$3')).getTime();
        };
        /*--------------------------------------------------------------------------
         *   Retina Logo
         * -------------------------------------------------------------------------*/
        if (window.devicePixelRatio == 2) {

            if(is_tansparent) {
                if(retina_logo_splash != '') {
                    $(".transparent-header .homey_logo img").attr("src", retina_logo_splash);
                }

                if(retina_logo_mobile_splash != '') {
                    $(".mobile-logo img").attr("src", retina_logo_mobile_splash);
                }

            } else {
                if(retina_logo != '') {
                    $(".homey_logo img").attr("src", retina_logo);
                }

                if(retina_logo_mobile != '') {
                    $(".mobile-logo img").attr("src", retina_logo_mobile);
                }
            }
        }

        /*--------------------------------------------------------------------------
         *  Currency Switcher
         * -------------------------------------------------------------------------*/
        var currencySwitcherList = $('#homey-currency-switcher-list');
        if( currencySwitcherList.length > 0 ) {

            $('#homey-currency-switcher-list > li').on('click', function(e) {
                e.stopPropagation();
                currencySwitcherList.slideUp( 200 );

                var selectedCurrencyCode = $(this).data( 'currency-code' );

                if ( selectedCurrencyCode ) {

                    $('.homey-selected-currency span').html( selectedCurrencyCode );
                    homey_processing_modal('<i class="'+process_loader_spinner+'"></i> '+currency_updating_msg);

                    $.ajax({
                        url: ajaxurl,
                        dataType: 'JSON',
                        method: 'POST',
                        data: {
                            'action' : 'homey_currency_converter',
                            'currency_to_converter' : selectedCurrencyCode,
                        },
                        success: function (res) {
                            if( res.success ) {
                                window.location.reload();
                            } else {
                                console.log( res );
                            }
                        },
                        error: function (xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err.Message);
                        }
                    });

                }

            });
        }

        $('.homey-currency-switcher').on('change', function(e) {
    
            var selectedCurrencyCode = $(this).val();

            if ( selectedCurrencyCode ) {

                homey_processing_modal('<i class="'+process_loader_spinner+'"></i> '+currency_updating_msg);

                $.ajax({
                    url: ajaxurl,
                    dataType: 'JSON',
                    method: 'POST',
                    data: {
                        'action' : 'homey_currency_converter',
                        'currency_to_converter' : selectedCurrencyCode,
                    },
                    success: function (res) {
                        if( res.success ) {
                            window.location.reload();
                        } else {
                            console.log( res );
                        }
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }
                });

            }

        });

        /*--------------------------------------------------------------------------
         *  Module Ajax Pagination
         * -------------------------------------------------------------------------*/
        var listings_module_section = $('#listings_module_section');
        if( listings_module_section.length > 0 ) {

            $("body").on('click', '.homey-loadmore a', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $wrap = $this.closest('#listings_module_section').find('#module_listings');

                var limit = $this.data('limit');
                var paged = $this.data('paged');
                var style = $this.data('style');
                var type = $this.data('type');
                var roomtype = $this.data('roomtype');
                var country = $this.data('country');
                var state = $this.data('state');
                var city = $this.data('city');
                var area = $this.data('area');
                var featured = $this.data('featured');
                var offset = $this.data('offset');
                var sortby = $this.data('sortby');
                var author = $this.data('author');
                var authorid = $this.data('authorid');

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_loadmore_listings',
                        'limit': limit,
                        'paged': paged,
                        'style': style,
                        'type': type,
                        'roomtype': roomtype,
                        'country': country,
                        'state': state,
                        'city': city,
                        'area': area,
                        'featured': featured,
                        'sort_by': sortby,
                        'offset': offset,
                        'author': author,
                        'authorid': authorid,
                    },
                    beforeSend: function( ) {
                        $this.find('i').css('display', 'inline-block');
                    },
                    success: function (data) {
                        if(data == 'no_result') {
                             $this.closest('#listings_module_section').find('.homey-loadmore').text(no_more_listings);
                             return;
                        }
                        $wrap.append(data);
                        $this.data("paged", paged+1);

                        homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
                        homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);
                        compare_for_ajax();

                    },
                    complete: function(){
                        $this.find('i').css('display', 'none');
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });

            }); 
        }

        /*--------------------------------------------------------------------------
         *   Add or remove favorites
         * -------------------------------------------------------------------------*/
        homey_init_add_favorite(ajaxurl, userID, is_singular_listing);
        homey_init_remove_favorite(ajaxurl, userID, is_singular_listing);

        /*--------------------------------------------------------------------------
         *   Compare for ajax
         * -------------------------------------------------------------------------*/
        var compare_for_ajax = function() {
            var listings_compare = homeyGetCookie('homey_compare_listings');
            var limit_item_compare = 4;
            add_to_compare(compare_url, add_compare, remove_compare, compare_limit, listings_compare, limit_item_compare );
            remove_from_compare(listings_compare, add_compare, remove_compare);
        }

        /* ------------------------------------------------------------------------ */
        /*  Paypal single listing payment
         /* ------------------------------------------------------------------------ */
        $('#homey_complete_order').on('click', function(e) {
            e.preventDefault();
            var hform, payment_gateway, listing_id, is_upgrade;

            payment_gateway = $("input[name='homey_payment_type']:checked").val();
            is_upgrade = $("input[name='is_upgrade']").val();

            listing_id = $('#listing_id').val();

            if( payment_gateway == 'paypal' ) {
                homey_processing_modal( paypal_connecting );
                homey_paypal_payment( listing_id, is_upgrade);

            } else if ( payment_gateway == 'stripe' ) {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
            }
            return;

        });

        var homey_processing_modal = function ( msg ) {
            var process_modal ='<div class="modal fade" id="homey_modal" tabindex="-1" role="dialog" aria-labelledby="homeyModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body homey_messages_modal">'+msg+'</div></div></div></div></div>';
            jQuery('body').append(process_modal);
            jQuery('#homey_modal').modal();
        }

        var homey_processing_modal_close = function ( ) {
            jQuery('#homey_modal').modal('hide');
        }


        /* ------------------------------------------------------------------------ */
        /*  Paypal payment function
         /* ------------------------------------------------------------------------ */
        var homey_paypal_payment = function( listing_id, is_upgrade ) {

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_listing_paypal_payment',
                    'listing_id': listing_id,
                    'is_upgrade': is_upgrade,
                },
                success: function( response ) {
                    window.location.href = response;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }


        if($('#add_review').length > 0) {
            $('#add_review').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                    var rating = $('#rating').val();
                    var review_action = $('#review_action').val();
                    var review_content = $('#review_content').val();
                    var review_reservation_id = $('#review_reservation_id').val();
                    var security = $('#review-security').val();
                    var parentDIV = $this.parents('.user-dashboard-right');

                    $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_add_review',
                        'rating': rating,
                        'review_action': review_action,
                        'review_content': review_content,
                        'review_reservation_id': review_reservation_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {

                        parentDIV.find('.alert').remove();
                        if(data.success) {
                            $this.attr("disabled", true);
                            window.location.reload();
                        } else {
                            parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>'+data.message+'</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });
                            
            });
        }

        if($('#add_guest_review').length > 0) {
            $('#add_guest_review').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                    var rating = $('#rating').val();
                    var review_action = $('#review_guest_action').val();
                    var review_content = $('#review_content').val();
                    var review_guest_reservation_id = $('#review_guest_reservation_id').val();
                    var security = $('#review-security').val();
                    var parentDIV = $this.parents('.user-dashboard-right');

                    $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_add_guest_review',
                        'rating': rating,
                        'review_action': review_action,
                        'review_content': review_content,
                        'review_guest_reservation_id': review_guest_reservation_id,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {

                        parentDIV.find('.alert').remove();
                        if(data.success) {
                            $this.attr("disabled", true);
                            window.location.reload();
                        } else {
                            parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>'+data.message+'</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });
                            
            });
        }

        var listing_review_ajax = function(sortby, listing_id, paged) {
            var review_container = $('#homey_reviews');
            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_ajax_review',
                    'sortby': sortby,
                    'listing_id': listing_id,
                    'paged': paged
                },
                beforeSend: function( ) {
                
                },
                success: function(data) {
                    review_container.empty();
                    review_container.html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    
                }

            });
        }

        if($('#sort_review').length > 0) {
            $('#sort_review').on('change', function() {
                var sortby = $(this).val();
                var listing_id = $('#review_listing_id').val();
                var paged = $('#review_paged').val();
                listing_review_ajax(sortby, listing_id, paged);
                return;
            }); 
        }

        if($('#review_next').length > 0) {
            $('#review_next').on('click', function(e) {
                e.preventDefault();
                $('#review_prev').removeAttr('disabled');
                var sortby = $('#page_sort').val();
                var total_pages = $('#total_pages').val();
                var listing_id = $('#review_listing_id').val();
                var paged = $('#review_paged').val();
                paged = Number(paged)+1;
                $('#review_paged').val(paged);

                if(paged == total_pages) {
                    $(this).attr('disabled', true);
                }
                listing_review_ajax(sortby, listing_id, paged);
                return;
            }); 
        }

        if($('#review_prev').length > 0) {
            $('#review_prev').on('click', function(e) {
                e.preventDefault();
                $('#review_next').removeAttr('disabled');
                var sortby = $('#page_sort').val();
                var listing_id = $('#review_listing_id').val();
                var paged = $('#review_paged').val();
                paged = Number(paged)-1;
                $('#review_paged').val(paged);
                if(paged <= 1) {
                    $(this).attr('disabled', true);
                }
                listing_review_ajax(sortby, listing_id, paged);
                return;
            }); 
        }

        /* ------------------------------------------------------------------------ */
        /* Set date format
        /* ------------------------------------------------------------------------ */
        var homey_convert_date = function(date) {

            if(date == '') {
                return '';
            }
     
            var d_format, return_date;
            
            d_format = homey_date_format.toUpperCase();

            var changed_date_format = d_format.replace("YY", "YYYY");
            var return_date = moment(date, changed_date_format).format('YYYY-MM-DD');

            return return_date;
         
        }


        var homey_calculate_booking_cost = function(check_in_date, check_out_date, guests, listing_id, security) {
            var $this = $(this);
            var notify = $('.homey_notification');
            notify.find('.notify').remove();

            if(check_in_date === '' || check_out_date === '') {
                $('#homey_booking_cost, .payment-list').empty();
                return;
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_calculate_booking_cost',
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'guests': guests,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    $('#homey_booking_cost, .payment-list').empty().html(data);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var homey_calculate_hourly_booking_cost = function(index, check_in_date, start_hour, end_hour, guests, listing_id, security) {
            var $this = $(this);
            var notify = $('.homey_notification');
            //notify.find('.notify').remove();
            var $parent = $('.panel:nth-child('+(index + 1)+')', $("#accordion"));
            
            if(check_in_date === '' || start_hour === '' || end_hour === '') {
                $('#homey_booking_cost, .payment-list').empty();
                setCostError(costs,index, true);
                return;
            }

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'homey_calculate_hourly_booking_cost',
                    'json': true,
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    var eH = moment(end_hour, "HH:mm").utcOffset(3);
                    var sH = moment(start_hour, "HH:mm").utcOffset(3);

                    var nbHeure = eH.diff(sH, "hours", true);
                    var guests_message = (index == 0) ? $('textarea[name="guest_message"]').val() : "";
                    var cost = {
                        index: index,
                        nbHeure: nbHeure,
                        cost: listing_price, // voir sidebar-booking-hourly.php
                        data: {
                            'check_in_date' : check_in_date,
                            'start_hour': start_hour,
                            'end_hour': end_hour,
                            'guests': guests,
                            'guests_message': guests_message
                        },
                        json: data
                    };
                    var res = costs.filter(function(c){
                        return c.index == cost.index;
                    });

                    const ind = (res.length > 0) ? costs.indexOf(res.pop()) : -1;
                    if(ind != -1){
                        costs[ind] = cost;
                    }
                    if(nbHeure>0) updateTotal();
                    changeClass();
                    costs = setCostError(costs,index, false);
                    var errorExists = getCostErrors(costs).length > 0;
                    disableReservationButton(errorExists);
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                    setCostError(costs,index, true);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var check_booking_availability_on_date_change = function(check_in_date, check_out_date, listing_id, security) {
            var $this = $(this);

            var notify = $('.homey_notification');
            notify.find('.notify').remove();
        
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'check_booking_availability_on_date_change',
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $('#homey_booking_cost, .payment-list').empty();
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    if( data.success ) {
                        disableReservationButton(data.success);
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');
                        disableReservationButton(data.success);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    notify.find('.homey_preloader').hide();
                }

            });
        }

        var check_booking_availability_on_hour_change = function(check_in_date, start_hour, end_hour, listing_id, security, parent, successCallback) {
            var $this = $(this);
            var $accordion = parent.parents("div[id^=accordion]");
            var parentIndex = $(".panel", $accordion).index(parent);
            var $momentDateFormat = "YY-MM-DD HH:mm";
            var notify = $('.homey_notification', parent);
            notify.find('.notify', parent).remove();

            var paramStart  = check_in_date + ' ' + start_hour;
            var paramEnd    = check_in_date + ' ' + end_hour;
 
            var mparamStart = moment(paramStart, $momentDateFormat);
            var mparamEnd   = moment(paramEnd, $momentDateFormat);


            var costresult = costs.filter(function(cost){
                if(cost.data == undefined){
                    return false;
                }
                if(cost.index == parentIndex) {
                    return false;
                }
                var costStart   = cost.data.check_in_date + ' ' + cost.data.start_hour;
                var costEnd     = cost.data.check_in_date + ' ' + cost.data.end_hour;

                var mcostStart  = moment(costStart, $momentDateFormat);
                var mcostEnd    = moment(costEnd, $momentDateFormat);
                var firsttest = mparamStart.isBetween(mcostStart, mcostEnd) ? true : mparamStart.isSame(mcostStart),
                    secondtest = mparamEnd.isBetween(mcostStart, mcostEnd) ? true : mparamEnd.isSame(mcostEnd);
                return firsttest && secondtest;
            });

            if(costresult.length > 0){
                notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">Cette salle n\'est pas disponible à cette heure.</div>');
                setCostError(costs, parentIndex,true);
                return;
            }
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'check_booking_availability_on_hour_change',
                    'check_in_date': check_in_date,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    //$('#homey_booking_cost, .payment-list').empty();
                    notify.find('.homey_preloader').show();
                },
                success: function(data) {
                    setCostError(costs, parentIndex, data.success);
                    disableReservationButton(!data.success);
                    if(data.success){
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">' + data.message + '</div>');
                        successCallback(data);
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">' + data.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    notify.find('.homey_preloader').hide();
                }

            });

        }

        // Single listing booking form
        $(document).on('click', "div[id^='single-listing-date-range'] input", function() {
            var $panel = $(this).parents(".panel");
            var cal = $('div[id^="single-booking-search-calendar"]', $panel);
            $('div[id^="single-booking-search-calendar"]', $panel).css("display", "block");
            $('div[id^="single-booking-search-calendar"]', $panel).addClass("arrive_active");
            $('.single-form-guests-js', $panel).css("display", "none");
            focusedInput_2 = $(this).attr('name');
            $('div[id^="single-booking-search-calendar"]', $panel).removeClass('arrive_active depart_active').addClass(focusedInput_2+'_active');
        });


        $(document).on('click', ".single-guests-js input", function() {
            var $singleGuestJs = $(this).parents(".single-guests-js");
            $(this).prev("label").css("display", "block");
            $(this).addClass("on-focus");
            $('.single-form-guests-js', $singleGuestJs).css("display", "block");
        });

        var numClicks = 0;
        var fromTimestamp_2, toTimestamp_2 = 0; // init start and end timestamps

        var homey_booking_dates = function() {
            
            $(document).on('click', '.single-listing-booking-calendar-js ul li', function() {
                var $this = $(this);
                var $parent = $(this).parents(".panel");

                if($this.hasClass('past-day') || $this.hasClass('homey-not-available-for-booking')) {
                    return false;
                }

                numClicks += 1;
                var vl = $this.data('formatted-date');
                var timestamp = $this.data('timestamp');

                // if modify days after selecting once
                if (focusedInput_2 == 'depart' && timestamp > fromTimestamp_2) {

                    $('.single-listing-calendar-wrap ul', $parent).find('li.to-day').removeClass('selected')
                        .siblings().removeClass('to-day in-between');

                    numClicks = 2;
                }

                if( numClicks == 1 ) {
                    fromTimestamp_2 = timestamp;

                    //day nodes
                    $('.single-listing-calendar-wrap ul li', $parent).removeClass('to-day from-day selected in-between');
                    $this.addClass('from-day selected');
                    // move caret
                    $('.single-listing-booking-calendar-js', $parent).removeClass('arrive_active').addClass('depart_active');

                    $('input[name="arrive"]', $parent).val(vl);
                    $('input[name="depart"]', $parent).val('');

                    if(homey_booking_type == 'per_day') {
                        homey_calculate_price_checkin();
                    }
                    
                } else if(numClicks == 2) {

                    toTimestamp_2 = timestamp;
                    //day end node
                    $this.addClass('to-day selected');
                    $('.single-listing-booking-calendar-js', $parent).removeClass('depart_active').addClass('arrive_active');

                    var check_in_date = $('input[name="arrive"]', $parent).val();
                    check_in_date = homey_timeStamp_2(check_in_date);
                    var check_out_date = homey_timeStamp_2(vl);

                    if(check_in_date >= check_out_date) {
                        fromTimestamp_2 = timestamp;
                        toTimestamp_2 = 0;
                        //day nodes
                        $('.single-listing-calendar-wrap ul li', $parent).removeClass('to-day from-day selected in-between');
                        $this.addClass('from-day selected');

                        // move caret
                        $('.single-listing-booking-calendar-js', $parent).removeClass('arrive_active').addClass('depart_active');

                        $('input[name="arrive"]', $parent).val(vl);
                        numClicks = 1;
                    } else {
                        setInBetween_2(fromTimestamp_2, toTimestamp_2);
                        $('input[name="depart"]', $parent).val(vl);
                        $('div[id^=single-booking-search-calendar]', $parent).hide();

                        if(homey_booking_type == 'per_day') {
                            homey_calculate_price_checkout();
                        }
                    }
                }
                if(numClicks == 2) { 
                    numClicks = 0; 
                }

            });
        }
        

        //Run only for daily/nighty booking

        if(homey_booking_type == 'per_day') {

            homey_booking_dates();

            $('.single-listing-calendar-wrap ul li').on('hover', function () {

                var ts = $(this).data('timestamp');
                if (numClicks == 1) {
                    setInBetween_2(fromTimestamp_2, ts);
                }
            });
            /*
            * method to send in-between days
            * */
            var setInBetween_2 = function(fromTime, toTime) {
                $('.single-listing-calendar-wrap ul li').removeClass('in-between')
                    .filter(function () {
                        var currentTs = $(this).data('timestamp');
                        return currentTs > fromTime && currentTs < toTime;
                    }).addClass('in-between');
            }


            var homey_calculate_price_checkin = function() {
                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var guests = $('input[name="guests"]').val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_booking_cost(check_in_date, check_out_date, guests, listing_id, security);
            }

            var homey_calculate_price_checkout = function() {
                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var guests = $('input[name="guests"]').val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();
                homey_calculate_booking_cost(check_in_date, check_out_date, guests, listing_id, security);
                check_booking_availability_on_date_change(check_in_date, check_out_date, listing_id, security);
            }
            
            $('.apply_guests').on('click', function () {
                var $panel = $(this).parents(".panel");
                var check_in_date = $('input[name="arrive"]').val();
                check_in_date = homey_convert_date(check_in_date);

                var check_out_date = $('input[name="depart"]').val();
                check_out_date = homey_convert_date(check_out_date);

                var guests = $('input[name="guests"]').val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();

                $('.single-form-guests-js', $panel).css("display", "none");
                homey_calculate_booking_cost(check_in_date, check_out_date, guests, listing_id, security);
                check_booking_availability_on_date_change(check_in_date, check_out_date, listing_id, security);
            });
        }

        $(document).on('click', '.hourly-js-desktop ul li', function () {

            if(homey_booking_type == 'per_hour') {
                
                    var $this = $(this);
                    var $parent = $(this).parents(".panel");
                    var $accordion = $parent.parents("div[id^=accordion]");
                    var parentIndex = $(".panel", $accordion).index($parent);
                    var vl = $this.data('formatted-date');
                    $('input[name="arrive"]', $parent).val(vl);

                    $('.single-listing-hourly-calendar-wrap ul li', $parent).removeClass('selected');
                    $this.addClass('selected');

                    var check_in_date = $('input[name="arrive"]', $parent).val();
                    check_in_date = homey_convert_date(check_in_date);

                    var start_hour = $('select[name="start_hour"]', $parent).val();
                    var end_hour = $('select[name="end_hour"]', $parent).val();
                    var guests = $('input[name="guests"]', $parent).val();
                    
                    var listing_id = $('#listing_id').val();
                    var security = $('#reservation-security').val();

                    var cal = $('div[id^=single-booking-search-calendar]', $parent);
                    cal.hide();

                    if(check_in_date === '' || start_hour === '' || end_hour === '')
                        return;
                    check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                        homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                    });

                
            }
        });

        $(document).on('change', '#start_hour', function () {

            if(homey_booking_type == 'per_hour') {
            
                var $parent = $(this).parents(".panel");
                var $accordion = $parent.parents("div[id^=accordion]");
                var parentIndex = $(".panel", $accordion).index($parent);
                var check_in_date = $('input[name="arrive"]', $parent).val();
                check_in_date = homey_convert_date(check_in_date);

                var start_hour = $('select[name="start_hour"]', $parent).val();
                var end_hour = $('select[name="end_hour"]', $parent).val();
                var guests = $('input[name="guests"]', $parent).val();
                
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();

                check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                    homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                });
            }
        });

        $(document).on('change', '#end_hour',function () {
            if(homey_booking_type == 'per_hour') {        
                var $parent = $(this).parents(".panel");
                var $accordion = $parent.parents("div[id^=accordion]");
                var parentIndex = $(".panel", $accordion).index($parent);
                var check_in_date = $('input[name="arrive"]', $parent).val();
                check_in_date = homey_convert_date(check_in_date);

                var start_hour = $('select[name="start_hour"]', $parent).val(); 
                var end_hour = $('select[name="end_hour"]', $parent).val();
                var guests = $('input[name="guests"]', $parent).val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();
                
                check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                    homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                });
            }
        });

        $(document).on('click', '.hourly-js-mobile ul li', function () {
            if(homey_booking_type == 'per_hour') {        
                var $this = $(this);
                var $accordion = $(this).parents("div[id^=accordion]");
                var $panel = $this.parents(".panel");
                var parentIndex = $(".panel", $accordion).index($panel);
                var vl = $this.data('formatted-date');
                $('input[name="arrive"]',$panel).val(vl);

                $('.single-listing-hourly-calendar-wrap ul li', $panel).removeClass('selected');
                $this.addClass('selected');

                $('#single-booking-search-calendar, #single-overlay-booking-search-calendar', $panel).hide();

                var check_in_date = $('input[name="arrive"]', $panel).val();
                check_in_date = homey_convert_date(check_in_date);

                var start_hour = $('#start_hour_overlay',$panel).val();
                var end_hour = $('#end_hour_overlay',$panel).val();
                var guests = $('input[name="guests"]',$panel).val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();$('div[id^=single-booking-search-calendar]', $panel).hide();

                if(check_in_date === '' || start_hour === '' || end_hour === '')
                    return;
                    check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $panel, function(d){
                        homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                    });
            }
        });

        $(document).on('change', '#start_hour_overlay', function () {
            if(homey_booking_type == 'per_hour') {        
            var $parent = $(this).parents(".panel");
            var $accordion = $parent.parents("div[id^=accordion]");
            var parentIndex = $(".panel", $accordion).index($parent);
            var check_in_date = $('input[name="arrive"]', $parent).val();
            check_in_date = homey_convert_date(check_in_date);

            var start_hour = $('#start_hour_overlay', $parent).val();
            var end_hour = $('#end_hour_overlay', $parent).val();
            var guests = $('input[name="guests"]', $parent).val();
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            if(check_in_date === '' || start_hour === '' || end_hour === '')
                return;
                check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                    homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                });
            }
        });

        $(document).on('change', '#end_hour_overlay',  function () {
            if(homey_booking_type == 'per_hour') {        
            var $parent = $(this).parents(".panel");
            var $accordion = $parent.parents("div[id^=accordion]");
            var parentIndex = $(".panel", $accordion).index($parent);
            var check_in_date = $('input[name="arrive"]', $parent).val();
            check_in_date = homey_convert_date(check_in_date);
            var start_hour = $('#start_hour_overlay', $parent).val(); 
            var end_hour = $('#end_hour_overlay', $parent).val();
            var guests = $('input[name="guests"]', $parent).val();
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
            });
            }
        });

        $(document).on('click', '.apply_guests', function () {
            if(homey_booking_type == 'per_hour') {        
            var $parent = $(this).parents(".panel");
            var $accordion = $parent.parents("div[id^=accordion]");
            var parentIndex = $(".panel", $accordion).index($parent);
            var check_in_date = $('input[name="arrive"]', $parent).val();
            check_in_date = homey_convert_date(check_in_date);

            var start_hour = $('select[name="start_hour"]', $parent).val();
            var end_hour = $('select[name="end_hour"]', $parent).val();
            var guests = $('input[name="guests"]', $parent).val();
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();
            if(check_in_date === '' || start_hour === '' || end_hour === '')
                return;
                check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                    homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                });

                disableReservationButton(getCostErrors(costs).length > 0);
            }
        });

        $(document).on('blur', '.search-message textarea', function(){
            if(homey_booking_type == 'per_hour') {        
            var $parent = $(this).parents(".panel");
            var $accordion = $parent.parents("div[id^=accordion]");
            var parentIndex = $(".panel", $accordion).index($parent);
            var check_in_date = $('input[name="arrive"]', $parent).val();
            check_in_date = homey_convert_date(check_in_date);

            var start_hour = $('select[name="start_hour"]', $parent).val();
            var end_hour = $('select[name="end_hour"]', $parent).val();
            var guests = $('input[name="guests"]', $parent).val();
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $parent, function(d){
                homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
            });
        
            }
        });

        $(document).on('click', '#apply_guests_hourly', function () {
            if(homey_booking_type === 'per_hour') {
                var $panel = $(this).parents(".panel");
                var $accordion = $panel.parents("div[id^=accordion]");
                var parentIndex = $(".panel", $accordion).index($panel);
                var check_in_date = $('input[name="arrive"]', $panel).val();
                check_in_date = homey_convert_date(check_in_date);

                var start_hour = $('#start_hour_overlay', $panel).val();
                var end_hour = $('#end_hour_overlay', $panel).val();
                var guests = $('input[name="guests"]', $panel).val();
                var listing_id = $('#listing_id').val();
                var security = $('#reservation-security').val();

                check_booking_availability_on_hour_change(check_in_date, start_hour, end_hour, listing_id, security, $panel, function(d){
                    homey_calculate_hourly_booking_cost(parentIndex, check_in_date, start_hour, end_hour, guests, listing_id, security);
                });

                disableReservationButton(getCostErrors(costs).length > 0);
            }
        });

        /* ------------------------------------------------------------------------ */
        /*  Guests count
        /* ------------------------------------------------------------------------ */

        var single_listing_guests = function() {
            $(document).on('click', '.adult_plus',function(e) {
                e.preventDefault();
                var $parent = $(this).parents(".panel");
                var guests = parseInt($('input[name="guests"]', $parent).val()) || 0;
                var adult_guest = parseInt($('input[name="adult_guest"]', $parent).val());
                var child_guest = parseInt($('input[name="child_guest"]', $parent).val());

                adult_guest++;
                $('.homey_adult', $parent).text(adult_guest);
                $('input[name="adult_guest"]', $parent).val(adult_guest);

                var total_guests = adult_guest + child_guest;

                if( (allow_additional_guests != 'yes') && (total_guests == allowed_guests_num)) {
                    $('.adult_plus', $parent).attr("disabled", true);
                    $('.child_plus', $parent).attr("disabled", true);
                }

                $('input[name="guests"]', $parent).val(total_guests);
            });

            $(document).on('click', '.adult_minus', function(e) {
                e.preventDefault();
                var $parent = $(this).parents(".panel");
                var guests = parseInt($('input[name="guests"]',$parent).val()) || 0;
                var adult_guest = parseInt($('input[name="adult_guest"]',$parent).val());
                var child_guest = parseInt($('input[name="child_guest"]',$parent).val());
                
                if (adult_guest == 0) return;
                adult_guest--;
                $('.homey_adult',$parent).text(adult_guest);
                $('input[name="adult_guest"]',$parent).val(adult_guest);

                var total_guests = adult_guest + child_guest;
                $('input[name="guests"]',$parent).val(total_guests);

                $('.adult_plus',$parent).removeAttr("disabled");
                $('.child_plus',$parent).removeAttr("disabled");
            });

            $('.child_plus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="guests"]').val());
                var child_guest = parseInt($('input[name="child_guest"]').val());
                var adult_guest = parseInt($('input[name="adult_guest"]').val());

                child_guest++;
                $('.homey_child').text(child_guest);
                $('input[name="child_guest"]').val(child_guest);

                var total_guests = child_guest + adult_guest;

                if( (allow_additional_guests != 'yes') && (total_guests == allowed_guests_num)) {
                    $('.adult_plus').attr("disabled", true);
                    $('.child_plus').attr("disabled", true);
                }

                $('input[name="guests"]').val(total_guests);

            });

            $('.child_minus').on('click', function(e) {
                e.preventDefault();
                var guests = parseInt($('input[name="guests"]').val());
                var child_guest = parseInt($('input[name="child_guest"]').val());
                var adult_guest = parseInt($('input[name="adult_guest"]').val());

                if (child_guest == 0) return;
                child_guest--;
                $('.homey_child').text(child_guest);
                $('input[name="child_guest"]').val(child_guest);

                var total_guests = child_guest + adult_guest;

                $('input[name="guests"]').val(total_guests);

                $('.adult_plus').removeAttr("disabled");
                $('.child_plus').removeAttr("disabled");

            });
        }
        single_listing_guests();

         /* ------------------------------------------------------------------------ */
         /*  Reservation Request
         /* ------------------------------------------------------------------------ */
         $('#request_for_reservation, #request_for_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var $accordion = $this.parents("div[id^='accordion']");
            var check_in_date = $('input[name="arrive"]', $accordion).val();
            check_in_date = homey_convert_date(check_in_date);

            var check_out_date = $('input[name="depart"]', $accordion).val();
            check_out_date = homey_convert_date(check_out_date);

            var guest_message = $('textarea[name="guest_message"]', $accordion).val();

            var guests = $('input[name="guests"]', $accordion).val();
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            var notify = $this.parents('.homey_notification', $accordion);
            notify.find('.notify').remove();
            
            if( parseInt( userID, 10 ) === 0 ) {
                $('#modal-login').modal('show');
            } else {

                costs.forEach(function(cost){
                    if(typeof cost.data == "undefined") return;

                    var check_in_date = cost.data.check_in_date;
                    //check_in_date = homey_convert_date(check_in_date);

                    var start_hour = cost.data.start_hour;
                    var end_hour = cost.data.end_hour;
                    var guest_message = cost.data.guests_message;
                    var guests = cost.data.guests;
                    var notify = $this.parents('.homey_notification');
                    notify.find('.notify').remove();
                    $.ajax({
                        type: 'post',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            'action': 'homey_add_reservation',
                            'check_in_date': check_in_date,
                            'check_out_date': check_out_date,
                            'guests': guests,
                            'listing_id': listing_id,
                            'guest_message': guest_message,
                            'security': security
                        },
                        beforeSend: function( ) {
                            $this.children('i').remove();
                            $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                        },
                        success: function(data) {
                            if( data.success ) {
                                $('.check_in_date, .check_out_date').val('');
                                notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                                
                            } else {
                                notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');
                                
                            }
                        },
                        error: function(xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err.Message);
                        },
                        complete: function(){
                            $this.children('i').removeClass(process_loader_spinner);
                        }

                    }); //
                });
            }
         });

         /* ------------------------------------------------------------------------ */
         /*  Hourly Reservation Request
         /* ------------------------------------------------------------------------ */
         $('#request_hourly_reservation, #request_hourly_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            var notify = $this.parents('.sidebar-booking-module-body');
            notify.find('.notify').remove();

            if( parseInt( userID, 10 ) === 0 ) {
                $('#modal-login').modal('show');
            } else {
                costs.forEach(function(cost){
                    if(typeof cost.data == "undefined") return;
                    var check_in_date = cost.data.check_in_date;
                    //check_in_date = homey_convert_date(check_in_date);

                    var start_hour = cost.data.start_hour;
                    var end_hour = cost.data.end_hour;
                    var guest_message = cost.data.guests_message;
                    var guests = cost.data.guests;
                    $.ajax({
                        type: 'post',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            'action': 'homey_add_hourly_reservation',
                            'check_in_date': check_in_date,
                            'start_hour': start_hour,
                            'end_hour': end_hour,
                            'guests': guests,
                            'guest_message': guest_message,
                            'listing_id': listing_id,
                            'security': security
                        },
                        beforeSend: function( ) {
                            $this.children('i').remove();
                            $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                        },
                        success: function(data) {
                            if( data.success ) {
                                $('.check_in_date, .check_out_date').val('');
                                if($(".notify").length == 0){
                                    notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width" style="line-height: 1.5 !important; padding: 10px 30px !important; white-space: normal !important; margin-top: 10px;">'+data.message+'</div>');
                                }
                            } else {
                                notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width" style="line-height: 1.5 !important; padding: 10px 30px !important; white-space: normal !important; margin-top: 10px;">'+data.message+'</div>');
                                console.log(notify);
                            }
                        },
                        error: function(xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err.Message);
                        },
                        complete: function(){
                            $this.children('i').removeClass(process_loader_spinner);
                        }

                    }); //

                });
            }

         });


         /* ------------------------------------------------------------------------ */
        /*  Reserve a period host
         /* ------------------------------------------------------------------------ */
         $('#reserve_period_host').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in_date = $('#period_start_date').val();
            //check_in_date = homey_convert_date(check_in_date);

            var check_out_date = $('#period_end_date').val();
            //check_out_date = homey_convert_date(check_out_date);

            var listing_id = $('#period_listing_id').val();
            var period_note = $('#period_note').val();
            var security = $('#period-security').val();
            var notify = $('.homey_notification');
            notify.find('.notify').remove();
            
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_reserve_period_host',
                    'check_in_date': check_in_date,
                    'check_out_date': check_out_date,
                    'period_note': period_note,
                    'listing_id': listing_id,
                    'security': security
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width">'+data.message+'</div>');
                        window.location.href = calendar_link;
                    } else {
                        notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width">'+data.message+'</div>');
                        
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

         });
        //alert(HOMEY_ajax_vars.homey_timezone);

               
        /**
         * Filtre datepicker
         */

        var dateFormat  = "dd/mm/yy",
            momentDateFormat = "DD/MM/YYYY",
            fromDate    = $("#fromDate").datepicker({
                changeMonth : true,
                changeYear  : true,
                dateFormat  : dateFormat
            }),
            toDate      = $("#toDate").datepicker({
                changeMonth : true,
                changeYear  : true,
                dateFormat  : dateFormat
            }),
            debutSemaine= $("#debutSemaine").datepicker({
                changeMonth : true,
                changeYear  : true,
                dateFormat  : dateFormat
            }),
            btnNext     = $(".btn-calendar-next"),
            btnPrevious = $(".btn-calendar-previous"),
            weekS = fromDate.val(),
            weekE = toDate.val(),
            cacheEcran  = $(".cacheEcran").on("click", function(){
                debutSemaine.trigger("focus");
            }),
            screenWidth = (window.innerWidth > 0) ? window.innerWidth : screen.width;
        var cEFormat1 = (screenWidth > 576) ? "ddd DD MMM" : "DD/MM",
            cEFormat2 = (screenWidth > 576) ? "ddd DD MMM YYYY" : "DD/MM/YYYY";

        var todayMoment = moment();
        if(weekS === undefined || weekS ===""){
                weekS  = todayMoment.utc().format(momentDateFormat);
            }
            if(weekE === undefined || weekE ==="") {
                var temp = todayMoment.add(6, "days");
                if (temp.month() === todayMoment.month()) {
                    weekE = temp.utc().format(momentDateFormat);
                }else{
                    var startEndMonth = weekS.endOf("month");
                    const diff = startEndMonth.diff(todayMoment, "days");
                    weekE = todayMoment.add(diff, "days").format(momentDateFormat);
                }
            }


            fromDate.val(moment(weekS, momentDateFormat).format(momentDateFormat)).trigger("change");
            toDate.val(moment(weekE, momentDateFormat).format(momentDateFormat)).trigger("change");
            $(".cacheEcran").text("Du " + moment(weekS, momentDateFormat).format(cEFormat1) +" au " + moment(weekE, momentDateFormat).format(cEFormat2));
            debutSemaine.on("change", function(){
                var mDebutSemaine = moment($(this).val(), momentDateFormat);
                var mFinSemaine = moment($(this).val(), momentDateFormat);
                var temp = moment(mFinSemaine);
                var nbJour = 6;
                temp.add(nbJour, "days");
                if(mDebutSemaine.month() != temp.month()) {
                    temp.subtract(nbJour, "days");
                    const finDuMois = temp.endOf("month");
                    const diffJour = finDuMois.diff(mDebutSemaine, "days");
                    nbJour = diffJour;
                }
                mFinSemaine.add(nbJour, "days");

                var $parent          = $(this).parent();
                var $cE              = $(".cacheEcran", $parent);
                $cE.text("Du " + mDebutSemaine.format(cEFormat1) +" au " + mFinSemaine.format(cEFormat2));
                fromDate.val(mDebutSemaine.format(momentDateFormat)).trigger("change");
                toDate.val(mFinSemaine.format(momentDateFormat)).trigger("change");
            });

            btnNext.on("click", function(){
                var mWeekStart = moment(toDate.val(), momentDateFormat);
                mWeekStart.add(1, "days");
                var mWeekEnd = moment(mWeekStart);
                /**
                 * trying to add 6 days if only mWeekStart and mWeekEnd are in the same month
                 */
                var temp = moment(toDate.val(), momentDateFormat);
                var nbJour = 6;
                temp.add(nbJour, "days");
                if(mWeekStart.month() != temp.month()) {
                    temp.subtract(nbJour, "days");
                    const finDuMois = temp.endOf("month");
                    const diffJour = finDuMois.diff(mWeekStart, "days");
                    nbJour = diffJour;
                }
                mWeekEnd.add(nbJour, "days");
                var $parent = $(this).parent();
                var $cE = $(".cacheEcran", $parent);
                $cE.text("Du " + mWeekStart.format(cEFormat1) +" au " + mWeekEnd.format(cEFormat2));
                fromDate.val(mWeekStart.format(momentDateFormat)).trigger("change");
                toDate.val(mWeekEnd.format(momentDateFormat)).trigger("change");
            });

            btnPrevious.on("click", function(){
                var nbJour = 6;
                var mWeekStart = moment(fromDate.val(), momentDateFormat);
                mWeekStart.subtract(1,"days");
                var mWeekEnd = moment(mWeekStart);
                /**
                 * trying to sub 6 days if only mWeekStart and mWeekEnd are in the same month
                 */
                var temp = moment(mWeekEnd);
                temp.subtract(nbJour, "days");
                if(mWeekEnd.month() != temp.month()) {
                    temp.add(nbJour, "days");
                    const debutMois = mWeekEnd.startOf("month");
                    const diffJour = mWeekEnd.diff(debutMois, "days");
                    nbJour = diffJour;
                }
                mWeekStart.subtract(nbJour, "days");
                var $parent = $(this).parent();
                var $cE = $(".cacheEcran", $parent);
                $cE.text("Du " + mWeekStart.format(cEFormat1) +" au " + mWeekEnd.format(cEFormat2));
                fromDate.val(mWeekStart.format(momentDateFormat)).trigger("change");
                toDate.val(mWeekEnd.format(momentDateFormat)).trigger("change");
            });

            fromDate.on("change", function(){
                toDate.datepicker("option", "minDate", getDate(this));
            });
            toDate.on("change", function(e){
                $(".single-listing-calendar").find('.homey_preloader').show();
                fromDate.datepicker("option", "maxDate", getDate(this));
                /**
                 * Week start - Week end
                 * Timezone transformation
                 */
                var t = moment(this.value, momentDateFormat);
                t.hour(23); t.minute(59); t.second(59);
                if($("#fromDate").val() === "") {
                    return;
                }
                var f = moment($("#fromDate").val(), momentDateFormat);
                f.hour(0); f.minute(0); f.second(0);
                var fCopy = moment($("#fromDate").val(), momentDateFormat);
                var resources = generateResource(fCopy, t);
                /**
                 * Date 1 & Date 2 NULL => Range = Cette semaine
                 */
                var timezone_offset_minutes = new Date().getTimezoneOffset();
                timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;

                var hs = jQuery.merge(getHoursSlot(), googleAgendaList.slice()); //shallow copy
                var events =  applyDateFilter(hs, f, t);

                getIndisponibilite(f, t, postId, timezone_offset_minutes,function(indisponibilites){
                    if(typeof indisponibilites != "undefined" && indisponibilites != null){
                        var sqlFormat   = "YYYY-MM-DD HH:mm:ss";
                        var now         = moment();
                        indisponibilites.forEach(function(indispo){
                            var started_at = indispo.started_at;
                            var ended_at = indispo.ended_at;
                            var diffTimestamp = ended_at - started_at;
                            if (started_at.hasOwnProperty('date')) {
                                started_at = started_at.date;
                                ended_at = ended_at.date;
                            }
                            var start   = moment.unix(started_at),
                                end     = moment.unix(ended_at);
                            start.set("date", now.get("date"));
                            var endOfDay    = moment(start).endOf("day").unix();
                            // end.set("date", now.get("date"));
                            var endTimestamp = Math.min(start.unix() + diffTimestamp, endOfDay);
                            end = moment.unix(endTimestamp);
                            var event           = [];
                            event["name"]       = "Indisponible";
                            event["location"]   = moment.unix(started_at).format("DD/MM");
                            event["start"]      = start.utc().format(sqlFormat);
                            event["end"]        = end.utc().format(sqlFormat);
                            event["className"]  = "busy";
                            event["source"]     = "indisponible";
                            event["data"]       = {_key:indispo.token};
                            events.push(event);
                        });
                    }
                     console.log(events);
                    buildSkedTape('#homey_hourly_calendar', resources, events);
                });
            });

        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                console.log(error);
                date = null;
            }

            return date;
        }

        if(homey_booking_type == 'per_hour') {
            if(document.getElementById('homey_hourly_calendar')) {
                homey_hourly_availability_calendar();

                $('ul#form_tabs li').on('click', function(e) {
                    e.preventDefault();

                    if($(this).hasClass('calendar-js')) {
                        $('#calendar-tab').css('display', 'block');
                        $('#calendar-tab').css('visibility', 'visible');
                    } else {
                        $('#calendar-tab').css('display', 'none');
                        $('#calendar-tab').css('visibility', 'hidden');
                    }
                });
            }
        }

        if(homey_booking_type == 'per_hour' && is_listing_detail == 'yes') {
            homey_hourly_availability_calendar();
        }

         /* ------------------------------------------------------------------------ */
        /*  Instace Booking
         /* ------------------------------------------------------------------------ */
         $('#instance_reservation, #instance_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            var notify = $this.parents('.sidebar-booking-module-body');
            notify.find('.notify').remove();

            if( parseInt( userID, 10 ) === 0 ) {
                $('#modal-login').modal('show');
            } else {

                var notify = $this.parents('.homey_notification');
                notify.find('.notify').remove();
                costs.forEach(function(cost){
                    if(typeof cost.data == "undefined") return;
                    var check_in_date = cost.data.check_in_date;
                    //check_in_date = homey_convert_date(check_in_date);

                    var start_hour = cost.data.start_hour;
                    var end_hour = cost.data.end_hour;
                    var guest_message = cost.data.guests_message;
                    var guests = cost.data.guests;

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            'action': 'homey_instance_booking',
                            'check_in_date': check_in_date,
                            'check_out_date': check_out_date,
                            'guests': guests,
                            'listing_id': listing_id,
                            'security': security
                        },
                        beforeSend: function( ) {
                            $this.children('i').remove();
                            $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                        },
                        success: function (data) {

                            if( data.success ) {
                                $('.check_in_date, .check_out_date').val('');
                                notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width" style="line-height: 1.5 !important; padding: 10px 30px !important; white-space: normal !important; margin-top: 10px;">'+data.message+'</div>');
                                window.location.href = data.instance_url;
                            } else {
                                notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width" style="line-height: 1.5 !important; padding: 10px 30px !important; white-space: normal !important; margin-top: 10px;">'+data.message+'</div>');
                                
                            }
                        },
                        error: function(xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err.Message);
                        },
                        complete: function(){
                            $this.children('i').removeClass(process_loader_spinner);
                        }
                    });
                });
            }

         });

         /* ------------------------------------------------------------------------ */
        /*  Hourly instace Booking
         /* ------------------------------------------------------------------------ */
         $('#instance_hourly_reservation, #instance_hourly_reservation_mobile').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var listing_id = $('#listing_id').val();
            var security = $('#reservation-security').val();

            var notify = $this.parents('.sidebar-booking-module-body');
            notify.find('.notify').remove();

            if( parseInt( userID, 10 ) === 0 ) {
                $('#modal-login').modal('show');
            } else {

                costs.forEach(function(cost){
                    if(typeof cost.data == "undefined") return;
                    var check_in_date = cost.data.check_in_date;
                    //check_in_date = homey_convert_date(check_in_date);

                    var start_hour = cost.data.start_hour;
                    var end_hour = cost.data.end_hour;
                    var guest_message = cost.data.guests_message;
                    var guests = cost.data.guests;

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            'action': 'homey_instance_hourly_booking',
                            'check_in_date': check_in_date,
                            'start_hour': start_hour,
                            'end_hour': end_hour,
                            'guests': guests,
                            'listing_id': listing_id,
                            'security': security
                        },
                        beforeSend: function( ) {
                            $this.children('i').remove();
                            $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                        },
                        success: function (data) {

                            if( data.success ) {
                                $('.check_in_date, .start_hour, .end_hour').val('');

                                if($(".notify").length == 0){
                                    notify.prepend('<div class="notify text-success text-center btn-success-outlined btn btn-full-width" style="line-height: 1.5 !important; padding: 10px 30px !important; white-space: normal !important; margin-top: 10px;">'+data.message+'</div>');
                                    window.location.href = data.instance_url;
                                }
                            } else {
                                notify.prepend('<div class="notify text-danger text-center btn-danger-outlined btn btn-full-width" style="line-height: 1.5 !important; padding: 10px 30px !important; white-space: normal !important; margin-top: 10px;">'+data.message+'</div>');
                                
                            }
                        },
                        error: function(xhr, status, error) {
                            var err = eval("(" + xhr.responseText + ")");
                            console.log(err.Message);
                        },
                        complete: function(){
                            $this.children('i').removeClass(process_loader_spinner);
                        }
                    });
                });
            }

         });

         /* ------------------------------------------------------------------------ */
        /*  Confirm Reservation
         /* ------------------------------------------------------------------------ */
         $('.confirm-reservation').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $this.data('reservation_id');
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_confirm_reservation',
                    'reservation_id': reservation_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                        $this.remove();
                    } else {
                        parentDIV.find('.dashboard-area').prepend(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

         });

         /* ------------------------------------------------------------------------ */
        /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
         $('#decline').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_decline_reservation',
                    'reservation_id': reservation_id,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) { 
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

         });

         /* ------------------------------------------------------------------------ */
        /*  Decline Reservation
         /* ------------------------------------------------------------------------ */
         $('#decline_hourly').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_decline_hourly_reservation',
                    'reservation_id': reservation_id,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) { 
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

         });

         /* ------------------------------------------------------------------------ */
        /*  Cancel Reservation
         /* ------------------------------------------------------------------------ */
         $('#cancelled').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var host_cancel = $('#host_cancel').val();
            var parentDIV = $this.parents('.user-dashboard-right');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_cancelled_reservation',
                    'reservation_id': reservation_id,
                    'host_cancel': host_cancel,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) { 
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

         });

         /* ------------------------------------------------------------------------ */
        /*  Cancel Hourly Reservation
         /* ------------------------------------------------------------------------ */
         $('#cancelled_hourly').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservationID').val();
            var reason = $('#reason').val();
            var host_cancel = $('#host_cancel').val();
            var parentDIV = $this.parents('.user-dashboard-right');


            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_cancelled_hourly_reservation',
                    'reservation_id': reservation_id,
                    'host_cancel': host_cancel,
                    'reason': reason
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {

                    parentDIV.find('.alert').remove();
                    if( data.success ) { 
                        $this.attr("disabled", true);
                        window.location.reload();
                    } else {
                        parentDIV.find('.dashboard-area').prepend('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-hide="alert" aria-label="Close"><i class="fa fa-close"></i></button>'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

         });

        var homey_booking_paypal_payment = function($this, reservation_id, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_booking_paypal_payment',
                    'reservation_id': reservation_id,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    $('#homey_notify').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        var homey_hourly_booking_paypal_payment = function($this, reservation_id, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_hourly_booking_paypal_payment',
                    'reservation_id': reservation_id,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    $('#homey_notify').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(!data){
                        window.location.href = "/reservations";
                    }
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        $('#make_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservation_id').val();
            var security = $('#checkout-security').val();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }
            
            if(payment_gateway === 'paypal') {
                homey_booking_paypal_payment($this, reservation_id, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
                $('#homey_notify').html('');
            }
            return;
        });

        $('#make_hourly_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var reservation_id = $('#reservation_id').val();
            var security = $('#checkout-security').val();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#homey_notify').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }
            
            if(payment_gateway === 'paypal') {
                homey_hourly_booking_paypal_payment($this, reservation_id, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('.dashboard-area');
                hform.find('.homey_stripe_simple button').trigger("click");
                $('#homey_notify').html('');
            }
            return;

        });

        var homey_instance_booking_paypal_payment = function($this, check_in, check_out, guests, listing_id, renter_message, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_instance_booking_paypal_payment',
                    'check_in': check_in,
                    'check_out': check_out,
                    'guests': guests,
                    'listing_id': listing_id,
                    'renter_message': renter_message,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    $('#instance_noti').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        var homey_hourly_instance_booking_paypal_payment = function($this, check_in, check_in_hour, check_out_hour, start_hour, end_hour, guests, listing_id, renter_message, security) {
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_hourly_instance_booking_paypal_payment',
                    'check_in': check_in,
                    'check_in_hour': check_in_hour,
                    'check_out_hour': check_out_hour,
                    'start_hour': start_hour,
                    'end_hour': end_hour,
                    'guests': guests,
                    'listing_id': listing_id,
                    'renter_message': renter_message,
                    'security': security,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    $('#instance_noti').html('<div class="alert alert-success alert-dismissible" role="alert">'+paypal_connecting+'</div>');
                },
                success: function( data ) {
                    if(!data){
                        window.location.href = "/reservations";
                    }
                    
                    if(data.success) {
                        window.location.href = data.payment_execute_url;
                    } else {
                        $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+data.message+'</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        $('#make_instance_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in   = $('#check_in_date').val();
            check_in = homey_convert_date(check_in);

            var check_out  = $('#check_out_date').val();
            check_out = homey_convert_date(check_out);

            var guests     = $('#guests').val();
            var listing_id = $('#listing_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();

            $('#instance_noti').empty();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }
            
            if(payment_gateway === 'paypal') {
                homey_instance_booking_paypal_payment($this, check_in, check_out, guests, listing_id, renter_message, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('form');
                hform.find('.homey_stripe_simple button').trigger("click");

            }
            return;
        });

        $('#make_hourly_instance_booking_payment').on('click', function(e){
            e.preventDefault();

            var $this = $(this);
            var check_in   = $('#check_in_date').val();

            var check_in_hour  = $('#check_in_hour').val();
            var check_out_hour  = $('#check_out_hour').val();
            var start_hour  = $('#start_hour').val();
            var end_hour  = $('#end_hour').val();
            var guests     = $('#guests').val();
            var listing_id = $('#listing_id').val();
            var renter_message = $('#renter_message').val();
            var security   = $('#checkout-security').val();

            $('#instance_noti').empty();

            var payment_gateway = $("input[name='payment_gateway']:checked").val();
            if(payment_gateway == undefined ) {
                $('#instance_noti').html('<div class="alert alert-danger alert-dismissible" role="alert">'+choose_gateway_text+'</div>');
            }
            
            if(payment_gateway === 'paypal') {
                homey_hourly_instance_booking_paypal_payment($this, check_in, check_in_hour, check_out_hour, start_hour, end_hour, guests, listing_id, renter_message, security);

            } else if(payment_gateway === 'stripe') {
                var hform = $(this).parents('form');
                hform.find('.homey_stripe_simple button').trigger("click");

            }
            return;

        });

        $('button.homey-booking-step-1').on('click', function(e){
            e.preventDefault();
            var $this = $(this);

            var first_name = $('#first-name').val();
            var last_name = $('#last-name').val();
            var phone = $('#phone').val();
            var renter_message = $('#renter_message').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_instance_step_1',
                    'first_name': first_name,
                    'last_name': last_name,
                    'phone': phone,
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $('.homey-booking-block-body-1 .continue-block-button p.error').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        $('.homey-booking-block-title-2').removeClass('inactive mb-0');
                        $('.homey-booking-block-body-2').slideDown('slow');

                        $('.homey-booking-block-title-1').addClass('mb-0');
                        $('.homey-booking-block-body-1').slideUp('slow');
                        $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').removeClass('hidden');
                        $('.homey-booking-block-title-1 .text-success, .homey-booking-block-title-1 .edit-booking-form').show();
                        $('#guest_message').val(renter_message);
                    } else {
                        $('.homey-booking-block-body-1 .continue-block-button').prepend('<p class="error text-danger"><i class="fa fa-close"></i> '+ data.message +'</p>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                }

            });

        });

        $('button.homey-booking-step-2').on('click', function(e){
            e.preventDefault();

            var agreement = $("input[name='agreement']:checked").val();

            $('.homey-booking-block-body-2 .continue-block-button p.error').remove();

            if(agreement != undefined) {

                $('.homey-booking-block-title-3').removeClass('inactive mb-0');
                $('.homey-booking-block-body-3').slideDown('slow');

                $('.homey-booking-block-title-2').addClass('mb-0');
                $('.homey-booking-block-body-2').slideUp('slow');
                $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').removeClass('hidden');
                $('.homey-booking-block-title-2 .text-success, .homey-booking-block-title-2 .edit-booking-form').show();
            } else {
                $('.homey-booking-block-body-2 .continue-block-button').prepend('<p class="error text-danger"><i class="fa fa-close"></i> '+ agree_term_text +'</p>');
            }

        });

        $('.homey-booking-block-title-1 .edit-booking-form').on('click', function(e){
            e.preventDefault();

            $('.homey-booking-block-title-2, .homey-booking-block-title-3').addClass('mb-0');
            $('.homey-booking-block-body-2, .homey-booking-block-body-3').slideUp('slow');

            $('.homey-booking-block-title-1').removeClass('mb-0');
            $('.homey-booking-block-body-1').slideDown('slow');

        });

        $('.homey-booking-block-title-2 .edit-booking-form').on('click', function(e){
            e.preventDefault();

            $('.homey-booking-block-title-1, .homey-booking-block-title-3').addClass('mb-0');
            $('.homey-booking-block-body-1, .homey-booking-block-body-3').slideUp('slow');

            $('.homey-booking-block-title-2').removeClass('mb-0');
            $('.homey-booking-block-body-2').slideDown('slow');

        });


        /*--------------------------------------------------------------------------
         *  Contact listing host
         * -------------------------------------------------------------------------*/
        $( '.contact_listing_host').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $host_contact_wrap = $this.parents( '.host-contact-wrap' );
            var $form = $this.parents( 'form' );
            var $messages = $host_contact_wrap.find('.homey_contact_messages');

            $.ajax({
                url: ajaxurl,
                data: $form.serialize(),
                method: $form.attr('method'),
                dataType: "JSON",

                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(response) {
                    if( response.success ) {
                        $messages.empty().append(response.msg);
                        $form.find('input').val('');
                        $form.find('textarea').val('');
                    } else {
                        $messages.empty().append(response.msg);
                        $this.children('i').removeClass(process_loader_spinner);
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    $this.children('i').addClass(success_icon);
                }
            });
        });

        /*--------------------------------------------------------------------------
         *   Contact host form on host detail page
         * -------------------------------------------------------------------------*/
        $('#host_detail_contact').on('click', function(e) {
            e.preventDefault();
            var current_element = $(this);
            var $this = $(this);
            var $form = $this.parents( 'form' );

            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: $form.serialize(),
                method: $form.attr('method'),
                dataType: "JSON",

                beforeSend: function( ) {
                    current_element.children('i').remove();
                    current_element.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function( res ) {
                    current_element.children('i').removeClass(process_loader_spinner);
                    if( res.success ) {
                        $('#form_messages').empty().append(res.msg);
                        current_element.children('i').addClass(success_icon);
                    } else {
                        $('#form_messages').empty().append(res.msg);
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }

            });
        });

        
         /*--------------------------------------------------------------------------
         *   Print Property
         * -------------------------------------------------------------------------*/
        if( $('#homey-print').length > 0 ) {
            $('#homey-print').on('click', function (e) {
                e.preventDefault();
                var listingID, printWindow;

                listingID = $(this).attr('data-listing-id');

                printWindow = window.open('', 'Print Me', 'width=850 ,height=842');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_create_print',
                        'listing_id': listingID,
                    },
                    success: function (data) {
                        printWindow.document.write(data);
                        printWindow.document.close();
                        printWindow.focus();
                    },
                    error: function (xhr, status, error) {
                        var err = eval("(" + xhr.responseText + ")");
                        console.log(err.Message);
                    }

                });
            });
        }

        /* ------------------------------------------------------------------------ */
        /*  Homey login and regsiter
         /* ------------------------------------------------------------------------ */
        $('.homey_login_button').on('click', function(e){
            e.preventDefault();
            var current = $(this);
            homey_login( current );
        });

        $('.homey-register-button').on('click', function(e){
            e.preventDefault();
            var current = $(this);
            homey_register( current );
        });

        var homey_login = function( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: $form.serialize(),
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function( response ) {
                    if( response.success ) {
                        $messages.empty().append('<p class="success text-success"><i class="fa fa-check"></i> '+ response.msg +'</p>');
                        
                        if( login_redirect_type == 'same_page' ) {
                            window.location.reload();
                        } else {
                            window.location.href = login_redirect;
                        }

                    } else {
                        $messages.empty().append('<p class="error text-danger"><i class="fa fa-close"></i> '+ response.msg +'</p>');
                    }

                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
        
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            })

        } // end homey_login

        var homey_register = function ( currnt ) {

            var $form = currnt.parents('form');
            var $messages = $('.homey_register_messages');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: $form.serialize(),
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function( response ) {
                    if( response.success ) {
                        $messages.empty().append('<p class="success text-success"><i class="fa fa-check"></i> '+ response.msg +'</p>');
                        $('.homey_login_messages').empty().append('<p class="success text-success"><i class="fa fa-check"></i> '+ response.msg +'</p>');
                        $('#modal-register').modal('hide');
                        $('#modal-login').modal('show');
                    } else {
                        $messages.empty().append('<p class="error text-danger"><i class="fa fa-close"></i> '+ response.msg +'</p>');
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                    if(homey_reCaptcha == 1) {
                        homeyReCaptchaReset();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /* ------------------------------------------------------------------------ */
        /*  Reset Password
         /* ------------------------------------------------------------------------ */
        $( '#homey_forgetpass').on('click', function(e){
            e.preventDefault();
            var user_login = $('#user_login_forgot').val(),
                security    = $('#homey_resetpassword_security').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_reset_password',
                    'user_login': user_login,
                    'security': security
                },
                beforeSend: function () {
                    $('#homey_msg_reset').empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function( response ) {
                    if( response.success ) {
                        $('#homey_msg_reset').empty().append('<p class="success text-success"><i class="fa fa-check"></i> '+ response.msg +'</p>');
                    } else {
                        $('#homey_msg_reset').empty().append('<p class="error text-danger"><i class="fa fa-close"></i> '+ response.msg +'</p>');
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });

        });


        if( $('#homey_reset_password').length > 0 ) {
            $('#homey_reset_password').on('click', function(e) {
                e.preventDefault();

                var $this = $(this);
                var rg_login = $('input[name="rp_login"]').val();
                var rp_key = $('input[name="rp_key"]').val();
                var new_pass = $('input[name="new_password"]').val();
                var security = $('input[name="homey_resetpassword_security"]').val();

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_reset_password_2',
                        'rq_login': rg_login,
                        'password': new_pass,
                        'rp_key': rp_key,
                        'security': security
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {
                        if( data.success ) {
                            jQuery('#password_reset_msgs, .homey_login_messages').empty().append('<p class="success text-success"><i class="fa fa-check"></i> '+ data.msg +'</p>');
                            jQuery('#new_password').val('');
                            $('#modal-login').modal('show');
                        } else {
                            jQuery('#password_reset_msgs').empty().append('<p class="error text-danger"><i class="fa fa-close"></i> '+ data.msg +'</p>');
                        }
                    },
                    error: function(errorThrown) {

                    },
                    complete: function(){
                        $this.children('i').removeClass(process_loader_spinner);
                    }

                });

            } );
        }

        /*--------------------------------------------------------------------------
         *   Facebook login
         * -------------------------------------------------------------------------*/
        $('.homey-facebook-login').on('click', function() {
            var current = $(this);
            homey_login_via_facebook( current );
        });

        var homey_login_via_facebook = function ( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action' : 'homey_facebook_login_oauth'
                },
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function (data) { 
                    window.location.href = data;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /*--------------------------------------------------------------------------
         *  Social Logins
         * -------------------------------------------------------------------------*/
        $('.homey-yahoo-login').on('click', function () {
            var current = $(this);
            homey_login_via_yahoo( current );
        });

        var homey_login_via_yahoo = function ( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action' : 'homey_yahoo_login'
                },
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function (data) {
                    window.location.href = data;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        $('.homey-google-login').on('click', function () {
            var current = $(this);
            homey_login_via_google( current );
        });


        var homey_login_via_google = function ( current ) {
            var $form = current.parents('form');
            var $messages = $('.homey_login_messages');

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action' : 'homey_google_login_oauth'
                },
                beforeSend: function () {
                    $messages.empty().append('<p class="success text-success"> '+ login_sending +'</p>');
                },
                success: function (data) { 
                    window.location.href = data;
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /*
         * Property Message Notifications
         * -----------------------------*/
        var homey_message_notifications = function () {

            $.ajax({
                url: ajaxurl,
                data: {
                    action : 'homey_chcek_messages_notifications'
                },
                method: "POST",
                dataType: "JSON",

                beforeSend: function( ) {
                    // code here...
                },
                success: function(response) {
                    if( response.success ) {
                        if ( response.notification ) {
                            $( '.user-alert' ).show();
                            $( '.msg-alert' ).show();
                        } else {
                            $( '.user-alert' ).hide();
                            $( '.msg-alert' ).hide();
                        }
                    }
                }
            });

        };

        $( document ).ready(function() {
            homey_message_notifications();
            if( parseInt( userID, 10 ) != 0 ) {
                setInterval(function() { homey_message_notifications(); }, 60000);
            }
        });



    }// typeof HOMEY_ajax_vars

}); // end document ready

function today(hours, minutes) {
    var date = new Date();
    date.setHours(hours, minutes, 0, 0);
    return date;
}
function yesterday(hours, minutes) {
    var date = today(hours, minutes);
    date.setTime(date.getTime() - 24 * 60 * 60 * 1000);
    return date;
}
function tomorrow(hours, minutes) {
    var date = today(hours, minutes);
    date.setTime(date.getTime() + 24 * 60 * 60 * 1000);
    return date;
}

function firstDayMonthDate(hours, minutes, month = -1) {
    var date = today(hours, minutes);
    if (month != -1) {
        date.setMonth(month);
    }
    return date;
}

/**
 * Filtrer par 2 date
 * @param hs
 * @param date1
 * @param date2
 * @param toGoogleCalendar
 * @returns {*}
 */
var applyDateFilter = function (hs, date1 , date2, toGoogleCalendar) {
    var now = moment();
    var sqlFormat   = "YYYY-MM-DD HH:mm:ss";
    var wS  = date1 === undefined ? now.startOf("week") : date1;
    var wE  = date2 === undefined ? now.endOf("week") : date2;
    var events = hs.filter(function(s){
        var start = moment(s.start);
        var end = moment(s.end);
        return start.isBetween(wS, wE) && end.isBetween(wS, wE);
    });
    if(toGoogleCalendar === undefined || toGoogleCalendar === false){
        events = events.map(function(event){
            var start = moment(event.start).unix();
            var end = moment(event.end).unix();
            var diffTimestamp = end - start;
            start = moment.unix(start).set("date", now.get('date'));
            var endOfDay    = moment(start).endOf("day").unix();
            var endTimestamp = Math.min(start.unix() + diffTimestamp, endOfDay);
            end = moment.unix(endTimestamp);
            event.start = start.format(sqlFormat);
            event.end = end.format(sqlFormat);
            return event;
        });
    }

    return events;
}
/**
 * Generate date rows.
 * @param {*} wS Start date
 * @param {*} wE End date
 */
var generateResource = function(wS, wE){
    if(wS === undefined){
        wS  = moment().startOf("week").utcOffset(3);
        //if(!(wS instanceof moment)) throw Exception('Start date must be instance of moment object');
    }
    if(wE === undefined){
        wE  = moment().endOf("week").utcOffset(3);
        //if(!(wE instanceof moment)) throw Exception('End date must be instance of moment object');
    }

    var resources = [];

    while(wE.diff(wS) >= 0){
        var row = {
            id: wS.format('DD/MM'),
            name: wS.format('ddd DD/MM')
        };
        resources.push(row);
        wS.add(1,"d");
    }
    return resources;
}

var minStartEvent        = function(events){
    var minEv   = events.shift();
    while(events.length>0){
        var ev = events.shift();
        if(moment(minEv.start).diff(moment(ev.start)) >= 0 ){
            minEv = ev;
        }
    }
    return minEv.start;
}
var maxEndEvent         = function(events){
    var maxEv   = events.shift();
    while(events.length>0){
        var ev = events.shift();
        if(moment(maxEv.start).diff(moment(ev.start)) <= 0 ){
            maxEv = ev;
        }
    }
    return maxEv.end;
}
/**
 * 
 * @param {*} intervalCalendar timestamp fin-debut
 */
var getTick         = function(intervalCalendar){
    return (1.5 * intervalCalendar) / (9*60*60); //9h = 1.5 tick. Règle de trois
}

var buildSkedTape   = function($el, resources, events){
    if(!($el instanceof jQuery)) $el = jQuery($el);
    var start   = moment.unix(openTime).utc(),
        end     = moment.unix(closeTime).utc();
    var diff    = end.diff(start, "seconds");
    var mDiff   = moment("00:00", "HH:mm").utc().add(end.diff(start, "seconds"), "seconds");
    if(mDiff.format("mm") == "30") {
        end.subtract(30, "minutes");
        diff = end.diff(start, "seconds");
    }

    var evs = [];
    if(role_user == 'homey_host'){
        evs = events;
    } else {
        /**
         * Hide bar label
         */
        events.forEach(function(ev){
            ev.name = '';
            evs.push(ev);
        });
    }
    var tick    = getTick(diff);
    var current_month = -1;
    if (resources[0]['id']!== undefined) {
        var day_month = resources[0]['id'];
        var slash_pos = parseInt(day_month.indexOf('/'));
        current_month = parseInt(day_month.substring(slash_pos+1)) -1;
    }

    var sked = $el.skedTape({
        start: firstDayMonthDate(start.format("HH"),start.format("mm"), current_month), // Timeline starts this date-time (UTC)
        end:  firstDayMonthDate(end.format("HH"),end.format("mm"), current_month),       // Timeline ends this date-time (UTC)
        showEventTime: true,     // Whether to show event start-end time
        showEventDuration: true, // Whether to show event duration
        locations: resources,
        events: evs,
        showDates: false,
        scrollWithYWheel: true,
        tick: tick              //Heure io an! 
    });
    jQuery(".single-listing-calendar").find('.homey_preloader').hide();
}
var getHoursSlot        = function(){
    var listing_booked_dates=[];
    var listing_pending_dates=[];
    var sqlFormat   = "YYYY-MM-DD HH:mm:ss";
    
    var tzKey = "studioTz";
    for (var key in booked_hours_array) {
        if (booked_hours_array.hasOwnProperty(key) && key!=='' && key !== tzKey){
            var temp_book=[];
            var startUnix = moment.unix(key); // .tz(booked_hours_array[tzKey]);
            //startUnix.add(timezoneOffsetMinutes, "minutes");
            //startUnix.add(booked_hours_array[tzKey], "seconds");
            var endUnix = moment.unix(booked_hours_array[key]); // .tz(booked_hours_array[tzKey]);
            //endUnix.add(timezoneOffsetMinutes, "minutes");
            //endUnix.add(booked_hours_array[tzKey], "seconds");
            temp_book['name']     =   HOMEY_ajax_vars.hc_reserved_label;
            temp_book['location']  =   startUnix.format('DD/MM');
            temp_book ['start']    =   startUnix.format(sqlFormat);
            temp_book ['end']      =   endUnix.format(sqlFormat);
            temp_book['source']    =   'wp';
            listing_booked_dates.push(temp_book);
        }
    }
    // Desactivation suite au retour 4
/*
    for (var key_pending in pending_hours_array) {
        if (pending_hours_array.hasOwnProperty(key_pending) && key_pending!=='') { 
            var temp_pending=[];
            temp_pending['name']      =   HOMEY_ajax_vars.hc_pending_label,
            temp_pending['location']  =   moment.unix(key_pending).utc().format('DD/MM'),
            temp_pending ['start']    =   moment.unix(key_pending).utc().format(),
            temp_pending ['end']      =   moment.unix( pending_hours_array[key_pending]).utc().format(),
            listing_pending_dates.push(temp_pending);
        }
    }
*/

    var hours_slot = jQuery.merge(listing_booked_dates, listing_pending_dates);
    return hours_slot;
}
/* ------------------------------------------------------------------------ */
/* Per Hour availability calendar
/* ------------------------------------------------------------------------ */
var homey_hourly_availability_calendar = function(){
    /**
     * Week start - Week end
     * Timezone transformation
     */
    var $fromDate           = jQuery("#fromDate"),
        $toDate             = jQuery("#toDate");

    var resources           = [],
        momentDateFormat    = "DD/MM/YYYY";
    /**
     * Date 1 & Date 2 NULL => Range = Cette semaine
     */

    var timezone_offset_minutes = new Date().getTimezoneOffset();
    timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
    const f = moment($fromDate.val(), momentDateFormat); // save data from pointer
    const f0 = moment($fromDate.val(), momentDateFormat);
    f.hour(0); f.minute(0); f.second(0);
    f0.hour(0); f0.minute(0); f0.second(0);
    const t = moment($toDate.val(), momentDateFormat);
    t.hour(23); t.minute(59); t.second(59);

    var hs = getHoursSlot();
    if($fromDate.val() !== "" && $toDate.val() !== ""){
        resources   = generateResource(f0, t);
        var events  = applyDateFilter(hs, f, t);
    } else{
        /**
         * Date 1 & Date 2 NULL => Range = Cette semaine
         */
        resources   = generateResource();
        var events  = applyDateFilter(hs);
    }
    getIndisponibilite(f, t, postId, timezone_offset_minutes,function(indisponibilites){
        if(typeof indisponibilites != "undefined" && indisponibilites != null){
            var sqlFormat   = "YYYY-MM-DD HH:mm:ss";
            var now         = moment();
            indisponibilites.forEach(function(indispo){
                var started_at = indispo.started_at;
                var ended_at = indispo.ended_at;
                var diffTimestamp = ended_at - started_at;
                if (started_at.hasOwnProperty('date')) {
                    started_at = started_at.date;
                    ended_at = ended_at.date;
                }
                var start   = moment.unix(started_at),
                    end     = moment.unix(ended_at);
                start.set("date", now.get("date"));
                var endOfDay    = moment(start).endOf("day").unix();
                // end.set("date", now.get("date"));
                var endTimestamp = Math.min(start.unix() + diffTimestamp, endOfDay);
                end = moment.unix(endTimestamp);
                var event           = [];
                event["name"]       = "Indisponible";
                event["location"]   = moment.unix(started_at).format("DD/MM");
                event["start"]      = start.utc().format(sqlFormat);
                event["end"]        = end.utc().format(sqlFormat);
                event["className"]  = "busy";
                event["source"]     = "indisponible";
                event["data"]       = {_key:indispo.token};
                events.push(event);
            });
        }
        buildSkedTape('#homey_hourly_calendar', resources, events);
    });
}


/**
 * Obtenir les heures de fermeture du studio
 */
function getIndisponibilite(mDebut, mFin, postId, tz, callback){
    var $ = jQuery;
    var sqlFormat   = "YYYY-MM-DD HH:mm";
    var $btnNext     = $(".btn-calendar-next"),
        $btnPrevious = $(".btn-calendar-previous");
    var params = {
        postId: postId,
        tz: tz,
        start: mDebut.format(sqlFormat), // Y-m-d 00:00
        end: mFin.format(sqlFormat), // Y-m-d 00:00
    };
    /**
     * return $.Deffered()
     */
    return $.ajax({
        url: "/api/calendar/indisponibilites",
        type: "get",
        dataType: 'json',
        data: params,
        beforeSend: function(){
            //TODO: Disable buttons next/prev
            $btnNext.prop('disabled', true);
            $btnPrevious.prop('disabled', true);
        },
        success: function(res){
            callback(res);
        },
        complete: function(){
            //TODO: Enable buttons next/prev
            $btnNext.prop('disabled', false);
            $btnPrevious.prop('disabled', false);
        }
    });
}