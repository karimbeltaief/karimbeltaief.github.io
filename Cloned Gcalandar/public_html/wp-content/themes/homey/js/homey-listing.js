
if ( typeof Homey_Listing !== "undefined" ) {

    var booked_hours_array = Homey_Listing.booked_hours_array;
    var pending_hours_array = Homey_Listing.pending_hours_array;

    if( booked_hours_array !=='' && booked_hours_array.length !== 0 ) {
        booked_hours_array   = JSON.parse (booked_hours_array);
    }

    if( pending_hours_array !=='' && pending_hours_array.length !== 0 ) {
        pending_hours_array   = JSON.parse (pending_hours_array);
    }
}
jQuery(document).ready( function($) {
    "use strict";

    if ( typeof Homey_Listing !== "undefined" ) {

        var dtGlobals = {}; // Global storage
        dtGlobals.isMobile	= (/(Android|BlackBerry|iPhone|iPad|Palm|Symbian|Opera Mini|IEMobile|webOS)/.test(navigator.userAgent));
        dtGlobals.isAndroid	= (/(Android)/.test(navigator.userAgent));
        dtGlobals.isiOS		= (/(iPhone|iPod|iPad)/.test(navigator.userAgent));
        dtGlobals.isiPhone	= (/(iPhone|iPod)/.test(navigator.userAgent));
        dtGlobals.isiPad	= (/(iPad|iPod)/.test(navigator.userAgent));

        var ajaxurl = Homey_Listing.ajaxURL;

        var homey_booking_type = Homey_Listing.homey_booking_type;
        var are_you_sure_text = Homey_Listing.are_you_sure_text;
        var delete_btn_text = Homey_Listing.delete_btn_text;
        var cancel_btn_text = Homey_Listing.cancel_btn_text;
        var process_loader_refresh = Homey_Listing.process_loader_refresh;
        var process_loader_spinner = Homey_Listing.process_loader_spinner;
        var process_loader_circle = Homey_Listing.process_loader_circle;
        var process_loader_cog = Homey_Listing.process_loader_cog;
        var btn_save = Homey_Listing.btn_save;
        var success_icon = Homey_Listing.success_icon;
        var verify_nonce = Homey_Listing.verify_nonce;
        var verify_file_type = Homey_Listing.verify_file_type;
        var add_listing_msg = Homey_Listing.add_listing_msg;
        var processing_text = Homey_Listing.processing_text;
        var acc_bedroom_name = Homey_Listing.acc_bedroom_name;
        var acc_bedroom_name_plac = Homey_Listing.acc_bedroom_name_plac;
        var acc_guests = Homey_Listing.acc_guests;
        var acc_guests_plac = Homey_Listing.acc_guests_plac;
        var acc_no_of_beds = Homey_Listing.acc_no_of_beds;
        var acc_no_of_beds_plac = Homey_Listing.acc_no_of_beds_plac;
        var acc_bedroom_type = Homey_Listing.acc_bedroom_type;
        var acc_bedroom_type_plac = Homey_Listing.acc_bedroom_type_plac;
        var acc_btn_remove_room = Homey_Listing.acc_btn_remove_room;

        var service_name = Homey_Listing.service_name;
        var service_name_plac = Homey_Listing.service_name_plac;
        var service_price = Homey_Listing.service_price;
        var service_price_plac = Homey_Listing.service_price_plac;
        var service_des = Homey_Listing.service_des;
        var service_des_plac = Homey_Listing.service_des_plac;
        var btn_remove_service = Homey_Listing.btn_remove_service;
        var pricing_link = Homey_Listing.pricing_link;
        var calendar_link = Homey_Listing.calendar_link;
        var geo_coding_msg = Homey_Listing.geo_coding;
        var avail_label = Homey_Listing.avail_label;
        var unavail_label = Homey_Listing.unavail_label;
        var geo_country_limit = Homey_Listing.geo_country_limit;
        var geocomplete_country = Homey_Listing.geocomplete_country;
        var homey_is_rtl = Homey_Listing.homey_is_rtl;

        var booking_start_hour = Homey_Listing.booking_start_hour;
        var booking_end_hour = Homey_Listing.booking_end_hour;

        $(document).ready(function() {
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });

        /* ------------------------------------------------------------------------ */
        /*  parseInt Radix 10
        /* ------------------------------------------------------------------------ */
        function parseInt10(val) {
            return parseInt(val, 10);
        }

        /**
         * Filtre datepicker
         */
        var dateFormat  = "dd/mm/yy",
            momentDateFormat = "DD/MM/YYYY",
            fromDate    = $("#fromDateBo").datepicker({
                changeMonth : true,
                changeYear  : true,
                dateFormat  : dateFormat
            }),
            toDate      = $("#toDateBo").datepicker({
                changeMonth : true,
                changeYear  : true,
                dateFormat  : dateFormat
            }),
            debutSemaine= $("#debutSemaineBo").datepicker({
                changeMonth : true,
                changeYear  : true,
                dateFormat  : dateFormat
            });

        var btnNext     = $(".btn-calendarBo-next"),
            btnPrevious = $(".btn-calendarBo-previous"),
            weekS = fromDate.val(),
            weekE = toDate.val(),
            cacheEcran  = $(".cacheEcranBo").on("click", function(){
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
                var startEndMonth = todayMoment.endOf("month");
                const diff = startEndMonth.diff(todayMoment, "days");
                weekE = todayMoment.add(diff, "days").format(momentDateFormat);
            }
        }

        fromDate.val(moment(weekS, momentDateFormat).format(momentDateFormat)).trigger("change");
        toDate.val(moment(weekE, momentDateFormat).format(momentDateFormat)).trigger("change");

        $(".cacheEcranBo").text("Du " + moment(weekS, momentDateFormat).format(cEFormat1) +" au " + moment(weekE, momentDateFormat).format(cEFormat2));
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
            var $cE              = $(".cacheEcranBo", $parent);
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
            var $cE = $(".cacheEcranBo", $parent);
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
            var $cE = $(".cacheEcranBo", $parent);
            $cE.text("Du " + mWeekStart.format(cEFormat1) +" au " + mWeekEnd.format(cEFormat2));
            fromDate.val(mWeekStart.format(momentDateFormat)).trigger("change");
            toDate.val(mWeekEnd.format(momentDateFormat)).trigger("change");
        });

        fromDate.on("change", function(){
            toDate.datepicker("option", "minDate", getDate(this));
        });
        toDate.on("change", function(){
            $('.spin').find('.homey_preloader').show();
            fromDate.datepicker("option", "maxDate", getDate(this));
            /**
             * Week start - Week end
             * Timezone transformation
             */
            var t = moment(this.value, momentDateFormat);
            t.hour(23); t.minute(59); t.second(59);
            if($("#fromDateBo").val() === "") {
                return;
            }
            var f = moment($("#fromDateBo").val(), momentDateFormat);
            f.hour(0); f.minute(0); f.second(0);
            var fCopy = moment($("#fromDateBo").val(), momentDateFormat);
            var resources = generateResource(fCopy, t);
            /**
             * Date 1 & Date 2 NULL => Range = Cette semaine
             */
            var timezone_offset_minutes = new Date().getTimezoneOffset();
            timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
            //updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get(), true);
            var hs = jQuery.merge(getHoursSlot(), googleAgendaList.slice());

            var events =  applyDateFilter(hs, f, t);
            /**
             * @TODO Fetch indisponibilites
             */

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
                buildSkedTape('#homey_hourly_calendar_edit_listing', resources, events);
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
            if(document.getElementById('homey_hourly_calendar_edit_listing')) {
                homey_hourly_availability_calendar_dash();

                $('ul#form_tabs li').on('click', function(e) {
                    e.preventDefault();

                    if($(this).hasClass('calendar-js')) {
                        $('#calendar-tab').css('display', 'block');
                        $('#calendar-tab').css('visibility', 'visible');
                    } else {
                        $('#calendar-tab').css('display', 'none');
                        $('#calendar-tab').css('visibility', 'hidden');
                    }
                })
            }
        }

        /*--------------------------------------------------------------------------
        * Add/Edit listing for autocomplete
        *---------------------------------------------------------------------------*/
        var componentForm_listing = {
            locality: 'long_name',
            administrative_area_level_1: 'long_name',
            country: 'long_name',
            postal_code: 'short_name',
            neighborhood: 'long_name',
            sublocality_level_1: 'long_name',
            political: 'long_name'
        };

        if (document.getElementById('listing_address')) {
            var inputField, defaultBounds, autocomplete;
            inputField = (document.getElementById('listing_address'));
            defaultBounds = new google.maps.LatLngBounds(
                new google.maps.LatLng(-90, -180),
                new google.maps.LatLng(90, 180)
            );
            var options = {
                bounds: defaultBounds,
                types: ['geocode'],
            };

            var mapDiv = $('#map');
            var maplat = mapDiv.data('add-lat');
            var maplong = mapDiv.data('add-long');

            var map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: maplat, lng: maplong},
            });


            /*if (document.getElementById('homey_edit_map')) {
                var latlng = {lat: parseFloat(maplat), lng: parseFloat(maplong)};
                var marker = new google.maps.Marker({
                    position: latlng,
                    map: map
                });
                map.setZoom(16);
            } else {
                var marker = new google.maps.Marker({
                  map: map,
                  anchorPoint: new google.maps.Point(0, -29)
                });
                map.setZoom(13); 
            }*/

            if (document.getElementById('homey_edit_map')) {
                var latlng = {lat: parseFloat(maplat), lng: parseFloat(maplong)};
                var marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                    draggable:true
                });
                google.maps.event.addListener(marker, 'dragend', function(evt) {
                    document.getElementById('lat').value = this.getPosition().lat();
                    document.getElementById('lng').value = this.getPosition().lng();
                });

                map.setZoom(16);
            } else {
                var marker = new google.maps.Marker({
                    map: map,
                    draggable:true,
                    anchorPoint: new google.maps.Point(0, -29)
                });
                google.maps.event.addListener(marker, 'dragend', function(evt) {
                    document.getElementById('lat').value = this.getPosition().lat();
                    document.getElementById('lng').value = this.getPosition().lng();
                });
                map.setZoom(13);
            }

            autocomplete = new google.maps.places.Autocomplete(inputField, options);

            if(geo_country_limit != 0 && geocomplete_country != '') {
                autocomplete.setComponentRestrictions(
                    {'country': [geocomplete_country]});
            }

            autocomplete.bindTo('bounds', map);

            var geocoder = new google.maps.Geocoder();

            document.getElementById('find').addEventListener('click', function() {
                marker.setVisible(false);
                homey_geocodeAddress(geocoder, map, marker);
            });


            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                fillInAddress_for_form(place);

                marker.setVisible(false);
                //var place = autocomplete.getPlace();
                if (!place.geometry) {
                    // User entered the name of a Place that was not suggested and
                    // pressed the Enter key, or the Place Details request failed.
                    window.alert("No details available for input: '" + place.name + "'");
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);  // Why 17? Because it looks good.
                }
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                console.log(place);

            });
        }

        function homey_geocodeAddress(geocoder, resultsMap, marker) {
            var lat = document.getElementById('lat').value;
            var lng = document.getElementById('lng').value;
            var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};

            geocoder.geocode({'location': latlng}, function(results, status) {
                if (status === 'OK') {
                    var i, has_city, addressType, val;

                    has_city = 0;

                    $('#city').val('');
                    $('#countyState').val('');
                    $('#zip').val('');
                    $('#area').val('');
                    $('#homey_country').val('');

                    document.getElementById('lat').value = results[0].geometry.location.lat();
                    document.getElementById('lng').value = results[0].geometry.location.lng();
                    document.getElementById('listing_address').value = results[0].formatted_address;

                    // Get each component of the address from the result details
                    // and fill the corresponding field on the form.
                    for (i = 0; i < results[0].address_components.length; i++) {
                        addressType = results[0].address_components[i].types[0];
                        val = results[0].address_components[i][componentForm_listing[addressType]];

                        if (addressType === 'neighborhood') {
                            $('#area').val(val);

                        } else if (addressType === 'political' || addressType === 'locality' || addressType === 'sublocality_level_1') {

                            $('#city').val(val);
                            if(val !== '') {
                                has_city = 1;
                            }
                        } else if(addressType === 'country') {
                            $('#homey_country').val(val);

                        } else if(addressType === 'postal_code') {
                            $('#zip').val(val);

                        } else if(addressType === 'administrative_area_level_1') {
                            $('#countyState').val(val);
                        }
                    }

                    if(has_city === 0) {
                        get_new_city_2('city', results[0].adr_address);
                    }

                    // If the place has a geometry, then present it on a map.
                    if (results[0].geometry.viewport) {
                        resultsMap.fitBounds(results[0].geometry.viewport);
                    } else {
                        resultsMap.setCenter(results[0].geometry.location);
                        resultsMap.setZoom(17);  // Why 17? Because it looks good.
                    }
                    marker.setPosition(results[0].geometry.location);
                    marker.setVisible(true);
                    console.log(results);

                } else {
                    alert(geo_coding_msg +': '+ status);
                }
            });
        }


        function fillInAddress_for_form(place) {
            var i, has_city, addressType, val;

            has_city = 0;

            $('#city').val('');
            $('#countyState').val('');
            $('#zip').val('');
            $('#area').val('');
            $('#homey_country').val('');

            document.getElementById('lat').value = place.geometry.location.lat();
            document.getElementById('lng').value = place.geometry.location.lng();

            // Get each component of the address from the place details
            // and fill the corresponding field on the form.
            for (i = 0; i < place.address_components.length; i++) {
                addressType = place.address_components[i].types[0];
                val = place.address_components[i][componentForm_listing[addressType]];


                if (addressType === 'neighborhood') {
                    $('#area').val(val);

                } else if (addressType === 'locality') {

                    $('#city').val(val);
                    if(val !== '') {
                        has_city = 1;
                    }
                } else if(addressType === 'country') {
                    $('#homey_country').val(val);

                } else if(addressType === 'postal_code') {
                    $('#zip').val(val);

                } else if(addressType === 'administrative_area_level_1') {
                    $('#countyState').val(val);
                }
            }

            $('#address-place').html(place.adr_address);

            if(has_city === 0) {
                get_new_city_2('city', place.adr_address);
            }
        }

        function get_new_city_2(stringplace, adr_address) {
            var new_city;
            new_city = $(adr_address).filter('span.locality').html() ;
            $('#'+stringplace).val(new_city);
        }

        /* ------------------------------------------------------------------------ */
        /*  Custom Period Prices 
        /* ------------------------------------------------------------------------ */
        $('#cus_btn_save').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            var cus_start_date = $('#cus_start_date').val();
            var cus_end_date = $('#cus_end_date').val();
            var cus_night_price = $('#cus_night_price').val();
            var cus_additional_guest_price = $('#cus_additional_guest_price').val();
            var cus_weekend_price = $('#cus_weekend_price').val();
            var listing_id = $('#listing_id_for_custom').val();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_add_custom_period',
                    'start_date': cus_start_date,
                    'end_date': cus_end_date,
                    'night_price': cus_night_price,
                    'additional_guest_price': cus_additional_guest_price,
                    'weekend_price': cus_weekend_price,
                    'listing_id': listing_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        window.location.href = pricing_link;
                    } else {
                        alert(data.message);
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
        /*  Delete Custom Period Prices 
        /* ------------------------------------------------------------------------ */
        $('.homey_delete_period').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            var startdate = $this.data('startdate');
            var enddate = $this.data('enddate');
            var listing_id = $this.data('listingid');

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_delete_custom_period',
                    'start_date': startdate,
                    'end_date': enddate,
                    'listing_id': listing_id
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        $this.parents('tr').remove();
                    } else {
                        alert(data.message);
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

        var homey_validation = function( field_required ) {
            if( field_required != 0 ) {
                return true;
            }
            return false;
        };

        $('ul#form_tabs li').on('click', function(e) {
            e.preventDefault();
            var current_tab = $(this).data('tab');
            $('#current_tab').val(current_tab);
        })

        /* ------------------------------------------------------------------------ */
        /*  START CREATE LISTING FORM STEPS AND VALIDATION
        /* ------------------------------------------------------------------------ */
        $("[data-hide]").on("click", function() {
            $(this).closest("." + $(this).attr("data-hide")).hide();
        });

        var current = 1;

        var form = $("#submit_listing_form");
        var formStep = $(".form-step");
        var formStepGal = $(".form-step-gal");
        var btnnext = $(".btn-step-next");
        var btnback = $(".btn-step-back");
        var btnsubmitBlock = $(".btn-step-submit");
        var btnsubmit = btnsubmitBlock.find("button[type='submit']");
        var total_steps = $('#total-steps');
        var steps_counter = $('#step-counter');
        var nav_item = $('.steps-breadcrumb li');


        var errorBlock = $(".validate-errors");
        var errorBlockGal = $(".validate-errors-gal");
        var galThumbs = $(".listing-thumb");

        total_steps.html(formStep.length);
        steps_counter.html(current);

        // Init buttons and UI
        formStep.not(':eq(0)').hide();
        formStep.eq(0).addClass('active');
        hideButtons(current);

        $('ul#form_tabs li, .btn-save-listing').on('click', function() {

            var currentTab = $('#form_tabs li.active').index();

            if (form.valid()) {
                errorBlock.hide();
            } else {

                $("html, body").animate({
                    scrollTop: 0
                }, "slow");

                setTimeout(function() {

                    $('#form_tabs li, .tab-content div').removeClass('active in');
                    $('#form_tabs li a').attr('aria-expanded', 'false');
                    $('#form_tabs li').eq(currentTab).addClass('active');
                    $('#form_tabs li a').attr('aria-expanded', 'true');
                    $('.tab-content .tab-pane').eq(currentTab).addClass('active in');

                }, 200);

                errorBlock.show();
            }
        });

        // Next button click action
        btnnext.on('click', function() {
            $("html, body").animate({
                scrollTop: 0
            }, "slow");

            if(dtGlobals.isiOS) {
                listing_gallery_images();
            }

            if (current < formStep.length) {
                // Check validation
                if ($(formStepGal).is(':visible')) {
                    if (!$(galThumbs).length > 0) {
                        errorBlockGal.show();
                        return
                    } else {
                        errorBlockGal.hide();
                    }
                }
                if (form.valid()) {

                    formStep.removeClass('active').css({display:'none'});
                    formStep.eq(current++).addClass('active').css({display:'block'});

                    errorBlock.hide();
                } else {
                    errorBlock.show();
                }
            }
            hideButtons(current);
            steps_counter.html(current);
        });

        // Back button click action
        btnback.on('click', function() {
            $("html, body").animate({
                scrollTop: 0
            }, "slow");
            if (current > 1) {
                current = current - 2;
                if (current < formStep.length) {
                    formStep.show();
                    formStep.not(':eq(' + (current++) + ')').hide();
                    nav_item.eq(current).removeClass('active');
                }
            }
            hideButtons(current);
            steps_counter.html(current);
        });

        // Submit button click
        btnsubmit.on('click', function(event) {
            event.preventDefault();
            // Check validation
            if ($(formStepGal).is(':visible')) {
                if (!$(galThumbs).length > 0) {
                    errorBlockGal.show();
                    return
                } else {
                    errorBlockGal.hide();
                }
            }
            if (form.valid()) {
                errorBlock.hide();
                btnsubmit.attr('disabled', true);
            } else {
                errorBlock.show();
                $("html, body").animate({
                    scrollTop: 0
                }, "slow");
            }
        });

        if (form.length > 0) {
            form.validate({ // initialize plugin
                ignore: ":hidden:not(.form-step.active .selectpicker)",
                errorPlacement: function(error, element) {
                    return false;
                },
                rules: {
                    night_price: {
                        number: true,
                    }

                }
            });
        }

        // Hide buttons according to the current step
        function hideButtons(current) {
            var limit = parseInt10(formStep.length);

            $(".action").hide();

            if (current < limit) btnnext.show();
            if (current > 1) btnback.show();
            if (current === limit) {
                btnnext.hide();
                btnsubmitBlock.show();
            }
        }

        /* ------------------------------------------------------------------------ */
        /*  Print Invoice
        /* ------------------------------------------------------------------------ */
        if( $('#invoice-print-button').length > 0 ) {

            $('#invoice-print-button').on('click', function (e) {
                e.preventDefault();
                var invoiceID, printWindow;
                invoiceID = $(this).attr('data-id');

                printWindow = window.open('', 'Print Me', 'width=700 ,height=842');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_create_invoice_print',
                        'invoice_id': invoiceID,
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

        /*--------------------------------------------------------------------------
         *  Invoice Filter
         * -------------------------------------------------------------------------*/
        $('#invoice_status, #invoice_type').on('change', function() {
            homey_invoices_filter();
        });

        $('#startDate, #endDate').on('focusout', function() {
            homey_invoices_filter();
        })

        var homey_invoices_filter = function() {
            var inv_status = $('#invoice_status').val(),
                inv_type   = $('#invoice_type').val(),
                startDate  = $('#startDate').val(),
                endDate  = $('#endDate').val();

            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                type: 'POST',
                data: {
                    'action': 'homey_invoices_ajax_search',
                    'invoice_status': inv_status,
                    'invoice_type'  : inv_type,
                    'startDate'     : startDate,
                    'endDate'       : endDate
                },
                success: function(res) {
                    if(res.success) {
                        $('#invoices_content').empty().append( res.result );
                        $( '#invoices_total_price').empty().append( res.total_price );
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                }
            });
        }

        /* ------------------------------------------------------------------------ */
        /*  Print Reservation
        /* ------------------------------------------------------------------------ */
        if( $('#printReservation').length > 0 ) {

            $('#printReservation').on('click', function (e) {
                e.preventDefault();
                var reservationID, printWindow;
                reservationID = $(this).attr('data-resvID');

                printWindow = window.open('', 'Print Me', 'width=700 ,height=842');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'homey_create_reservation_print',
                        'reservation_id': reservationID,
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
        /*  START LISTING VIEW
        /* ------------------------------------------------------------------------ */
        var get_title = $("#listing_title");
        var view_title = $("#property-title-fill");
        var selected = null;

        function keyup_fill(ele, ele_place) {
            $(ele).on("keyup", function(event) {
                if ($(ele).attr("name") === "night_price") {
                    if (!$.isNumeric($(ele).val())) {
                        return
                    }
                }

                if ($(ele).attr("name") === "listing_bedrooms" || $(ele).attr("name") === "guests" || $(ele).attr("name") === "baths") {
                    if (!$.isNumeric($(ele).val())) {
                        return
                    }
                }



                var newText = event.target.value;
                $(ele_place).html(newText);
            });
        }

        keyup_fill("#listing_title", "#title-place");
        keyup_fill("#listing_address", "#address-place");
        keyup_fill("#night_price", "#price-place");
        keyup_fill("#hour_price", "#price-place");
        keyup_fill("#listing_bedrooms", "#total-beds");
        keyup_fill("#guests", "#total-guests");
        keyup_fill("#baths", "#total-baths");

        function amenities_selector(ele, view_ele, is_text) {
            $(ele).on('change', function() {
                if(is_text == 'yes') {
                    var selected = $(this).find("option:selected").text();
                } else {
                    var selected = $(this).find("option:selected").val();
                }
                $(view_ele).html(selected);
            });
        }
        amenities_selector("#listing_type", "#listing-type-view", 'yes');


        /*--------------------------------------------------------------------------
         *  Delete property
         * -------------------------------------------------------------------------*/
        $( '.delete-listing' ).on('click', function () {

            var $this = $( this );
            var listing_id = $this.data('id');
            var nonce = $this.data('nonce');

            bootbox.confirm({
                message: "<p><strong>"+are_you_sure_text+"</strong></p>",
                buttons: {
                    confirm: {
                        label: delete_btn_text,
                        className: 'btn btn-primary btn-half-width'
                    },
                    cancel: {
                        label: cancel_btn_text,
                        className: 'btn btn-grey-outlined btn-half-width'
                    }
                },
                callback: function (result) {

                    if(result==true) {

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: ajaxurl,
                            data: {
                                'action': 'homey_delete_listing',
                                'listing_id': listing_id,
                                'security': nonce
                            },
                            beforeSend: function( ) {
                                $this.find('i').removeClass('fa-trash');
                                $this.find('i').addClass('fa-spin fa-spinner');
                            },
                            success: function(data) {
                                if ( data.success == true ) {
                                    window.location.reload();
                                } else {
                                    jQuery('#homey_modal').modal('hide');
                                    alert( data.reason );
                                }
                            },
                            error: function(errorThrown) {

                            }
                        }); // $.ajax
                    } // result
                } // Callback
            });

            return false;

        });

        /*---------------------------------------------------------------------------
         *
         * Messaging system
         * -------------------------------------------------------------------------*/

        /*
         * Message Thread Form
         * -----------------------------*/
        $('.start_thread_form').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $form = $this.parents( 'form' );
            var $result = $('.messages-notification');

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
                        $result.empty().append(response.msg);
                        $form.find('input').val('');
                        $form.find('textarea').val('');
                        window.location.replace( response.redirect_link );
                    } else {
                        $result.empty().append(response.msg);
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


        /*
         * Property Message Notifications
         * -----------------------------*/
        var houzez_message_notifications = function () {

            $.ajax({
                url: ajaxurl,
                data: {
                    action : 'houzez_chcek_messages_notifications'
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


        /*
         * Property Thread Message Form
         * -----------------------------*/
        $('.start_thread_message_form').on('click', function(e) {

            e.preventDefault();

            var $this = $(this);
            var $form = $this.parents( 'form' );
            var $result = $('.messages-notification');

            $.ajax({
                url: ajaxurl,
                data: $form.serialize(),
                method: $form.attr('method'),
                dataType: "JSON",

                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function( response ) {
                    if( response.success ) {
                        window.location.replace( response.url );
                    } else {
                        $result.empty().append(response.msg);
                    }
                },
                complete: function(){
                    $this.children('i').removeClass(process_loader_spinner);
                    $this.children('i').addClass(success_icon);
                }
            });

        });


        $('.homey_delete_msg_thread').on('click', function(e) {
            e.preventDefault();

            var $this = $( this );
            var thread_id = $this.data('thread-id');
            var sender_id = $this.data('sender-id');
            var receiver_id = $this.data('receiver-id');

            bootbox.confirm({
                message: "<p><strong>"+are_you_sure_text+"</strong></p>",
                buttons: {
                    confirm: {
                        label: delete_btn_text,
                        className: 'btn btn-primary btn-half-width'
                    },
                    cancel: {
                        label: cancel_btn_text,
                        className: 'btn btn-grey-outlined btn-half-width'
                    }
                },
                callback: function (result) {

                    if(result==true) {

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: ajaxurl,
                            data: {
                                'action': 'homey_delete_message_thread',
                                'thread_id': thread_id,
                                'sender_id': sender_id,
                                'receiver_id': receiver_id
                            },
                            beforeSend: function( ) {
                                $this.find('i').removeClass('fa-trash');
                                $this.find('i').addClass('fa-spin fa-spinner');
                            },
                            success: function(data) {
                                if ( data.success == true ) {
                                    window.location.reload();
                                } else {
                                    jQuery('#homey_modal').modal('hide');
                                }
                            },
                            error: function(errorThrown) {

                            }
                        }); // $.ajax
                    } // result
                } // Callback
            });

        });

        $('.homey_delete_message').on('click', function(e) {
            e.preventDefault();

            var $this = $( this );
            var message_id = $this.data('message-id');
            var created_by = $this.data('created-by');

            bootbox.confirm({
                message: "<p><strong>"+are_you_sure_text+"</strong></p>",
                buttons: {
                    confirm: {
                        label: delete_btn_text,
                        className: 'btn btn-primary btn-half-width'
                    },
                    cancel: {
                        label: cancel_btn_text,
                        className: 'btn btn-grey-outlined btn-half-width'
                    }
                },
                callback: function (result) {

                    if(result==true) {

                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: ajaxurl,
                            data: {
                                'action': 'homey_delete_message',
                                'message_id': message_id,
                                'created_by': created_by
                            },
                            beforeSend: function( ) {
                                $this.find('i').removeClass('fa-trash');
                                $this.find('i').addClass('fa-spin fa-spinner');
                            },
                            success: function(data) {
                                if ( data.success == true ) {
                                    window.location.reload();
                                } else {
                                    jQuery('#homey_modal').modal('hide');
                                }
                            },
                            error: function(errorThrown) {

                            }
                        }); // $.ajax
                    } // result
                } // Callback
            });

        });


        var homey_processing_modal = function ( msg ) {
            var process_modal ='<div class="modal fade" id="homey_modal" tabindex="-1" role="dialog" aria-labelledby="faveModalLabel" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body homey_messages_modal">'+msg+'</div></div></div></div></div>';
            jQuery('body').append(process_modal);
            jQuery('#homey_modal').modal();
        };

        var homey_processing_modal_close = function ( ) {
            jQuery('#homey_modal').modal('hide');
        };

        /* ------------------------------------------------------------------------ */
        /*  Listing Thumbnails actions ( make features & delete )
         /* ------------------------------------------------------------------------ */
        var lisitng_thumbnail_event = function() {

            // Set Featured Image
            $('.icon-featured').on('click', function(e){
                e.preventDefault();

                var $this = jQuery(this);
                var thumb_id = $this.data('attachment-id');
                var thumb = $this.data('thumb');
                var icon = $this.find( 'i');

                $('.upload-view-media .media-image img').attr('src',thumb);
                $('.upload-gallery-thumb-buttons .featured_image_id').remove();
                $('.upload-gallery-thumb-buttons .icon-featured i').removeClass('fa-star').addClass('fa-star-o');

                $this.closest('.upload-gallery-thumb-buttons').append('<input type="hidden" class="featured_image_id" name="featured_image_id" value="'+thumb_id+'">');
                icon.removeClass('fa-star-o').addClass('fa-star');
            });

            //Remove Image
            $('.icon-delete').on('click', function(e){
                e.preventDefault();

                var $this = $(this);
                var thumbnail = $this.closest('.listing-thumb');
                var loader = $this.siblings('.icon-loader');
                var listing_id = $this.data('listing-id');
                var thumb_id = $this.data('attachment-id');

                loader.show();

                var ajax_request = $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_remove_listing_thumbnail',
                        'listing_id': listing_id,
                        'thumb_id': thumb_id,
                        'removeNonce': verify_nonce
                    }
                });

                ajax_request.done(function( response ) {
                    if ( response.remove_attachment ) {
                        thumbnail.remove();
                    } else {

                    }
                });

                ajax_request.fail(function( jqXHR, textStatus ) {
                    alert( "Request failed: " + textStatus );
                });

            });

        }

        lisitng_thumbnail_event();


        /*--------------------------------------------------------------------------
         *  Uplaod listing gallery
         * -------------------------------------------------------------------------*/
        var listing_gallery_images = function() {

            $( "#homey_gallery_container" ).sortable({
                placeholder: "sortable-placeholder"
            });

            var plup_uploader = new plupload.Uploader({
                browse_button: 'select_gallery_images',
                file_data_name: 'listing_upload_file',
                container: 'homey_gallery_dragDrop',
                drop_element: 'homey_gallery_dragDrop',
                url: ajaxurl + "?action=homey_listing_gallery_upload&verify_nonce=" + verify_nonce,
                filters: {
                    mime_types : [
                        { title : verify_file_type, extensions : "jpg,jpeg,gif,png" }
                    ],
                    max_file_size: '10m',//image_max_file_size,
                    prevent_duplicates: false
                }
            });
            plup_uploader.init();

            plup_uploader.bind('FilesAdded', function(up, files) {
                var homey_thumbs = "";
                var maxfiles = '50';//max_prop_images;
                if(up.files.length > maxfiles ) {
                    up.splice(maxfiles);
                    alert('no more than '+maxfiles + ' file(s)');
                    return;
                }
                plupload.each(files, function(file) {
                    homey_thumbs += '<div id="thumb-holder-' + file.id + '" class="col-sm-2 col-xs-4 listing-thumb">' + '' + '</div>';
                });
                document.getElementById('homey_gallery_container').innerHTML += homey_thumbs;
                up.refresh();
                plup_uploader.start();
            });


            plup_uploader.bind('UploadProgress', function(up, file) {
                document.getElementById( "thumb-holder-" + file.id ).innerHTML = '<span>' + file.percent + "%</span>";
            });

            plup_uploader.bind('Error', function( up, err ) {
                document.getElementById('homey_errors').innerHTML += "<br/>" + "Error #" + err.code + ": " + err.message;
            });

            plup_uploader.bind('FileUploaded', function ( up, file, ajax_response ) {
                var response = $.parseJSON( ajax_response.response );


                if ( response.success ) {

                    var gallery_thumbnail = '<figure class="upload-gallery-thumb">' +
                        '<img src="' + response.url + '" alt="thumb">' +
                        '</figure>' +
                        '<div class="upload-gallery-thumb-buttons">' +
                        '<button class="icon-featured" data-thumb="' + response.thumb + '" data-listing-id="' + 0 + '"  data-attachment-id="' + response.attachment_id + '"><i class="fa fa-star-o"></i></button>' +
                        '<button class="icon-delete" data-listing-id="' + 0 + '"  data-attachment-id="' + response.attachment_id + '"><i class="fa fa-trash-o"></i></button>' +
                        '<input type="hidden" class="listing-image-id" name="listing_image_ids[]" value="' + response.attachment_id + '"/>' +
                        '</div>'+
                        '<span style="display: none;" class="icon icon-loader"><i class="fa fa-spinner fa-spin"></i></span>';

                    document.getElementById( "thumb-holder-" + file.id ).innerHTML = gallery_thumbnail;

                    lisitng_thumbnail_event();

                } else {
                    console.log ( response );
                }
            });

        }
        listing_gallery_images();

        /* ------------------------------------------------------------------------ */
        /*  Bedrooms
         /* ------------------------------------------------------------------------ */

        $( '#add_more_bedrooms' ).on('click', function( e ){
            e.preventDefault();

            var numVal = $(this).data("increment") + 1;
            $(this).data('increment', numVal);
            $(this).attr({
                "data-increment" : numVal
            });

            var newBedroom = '' +
                '<div class="more_rooms_wrap">'+
                '<div class="row">'+
                '<div class="col-sm-6 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="acc_bedroom_name">'+acc_bedroom_name+'</label>'+
                '<input type="text" name="homey_accomodation['+numVal+'][acc_bedroom_name]" class="form-control" placeholder="'+acc_bedroom_name_plac+'">'+
                '</div>'+
                '</div>'+
                '<div class="col-sm-6 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="acc_guests">'+acc_guests+'</label>'+
                '<input type="text" name="homey_accomodation['+numVal+'][acc_guests]" class="form-control" placeholder="'+acc_guests_plac+'">'+
                '</div>'+
                '</div>'+
                '</div>'+
                '<div class="row">'+
                '<div class="col-sm-6 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="acc_no_of_beds">'+acc_no_of_beds+'</label>'+
                '<input type="text" name="homey_accomodation['+numVal+'][acc_no_of_beds]" class="form-control" placeholder="'+acc_no_of_beds_plac+'">'+
                '</div>'+
                '</div>'+
                '<div class="col-sm-6 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="acc_bedroom_type">'+acc_bedroom_type+'</label>'+
                '<input type="text" name="homey_accomodation['+numVal+'][acc_bedroom_type]" class="form-control" placeholder="'+acc_bedroom_type_plac+'">'+
                '</div>'+
                '</div>'+
                '</div>'+
                '<div class="row">'+
                '<div class="col-sm-12 col-xs-12">'+
                '<button type="button" data-remove="'+numVal+'" class="btn btn-primary remove-beds">'+acc_btn_remove_room+'</button>'+
                ' </div>'+
                '</div>'+
                '<hr>';
            '</div>';

            $( '#more_bedrooms_main').append( newBedroom );
            removeBedroom();
        });

        var removeBedroom = function (){

            $( '.remove-beds').on('click', function( event ){
                event.preventDefault();
                var $this = $( this );
                $this.closest( '.more_rooms_wrap' ).remove();
            });
        }
        removeBedroom();

        /* ------------------------------------------------------------------------ */
        /*  Services
         /* ------------------------------------------------------------------------ */

        $( '#add_more_service' ).on('click', function( e ){
            e.preventDefault();

            var numVal = $(this).data("increment") + 1;
            $(this).data('increment', numVal);
            $(this).attr({
                "data-increment" : numVal
            });

            var newService = '' +
                '<div class="more_services_wrap">'+
                '<div class="row">'+
                '<div class="col-sm-6 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="service_name">'+service_name+'</label>'+
                '<input type="text" name="homey_services['+numVal+'][service_name]" class="form-control" placeholder="'+service_name_plac+'">'+
                '</div>'+
                '</div>'+
                '<div class="col-sm-6 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="service_price">'+service_price+'</label>'+
                '<input type="text" name="homey_services['+numVal+'][service_price]" class="form-control" placeholder="'+service_price_plac+'">'+
                '</div>'+
                '</div>'+
                '</div>'+
                '<div class="row">'+
                '<div class="col-sm-12 col-xs-12">'+
                '<div class="form-group">'+
                '<label for="service_des">'+service_des+'</label>'+
                '<textarea placeholder="'+service_des_plac+'" rows="3" name="homey_services['+numVal+'][service_des]" class="form-control"></textarea>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '<div class="row">'+
                '<div class="col-sm-12 col-xs-12">'+
                '<button type="button" data-remove="'+numVal+'" class="btn btn-primary remove-service">'+btn_remove_service+'</button>'+
                ' </div>'+
                '</div>'+
                '<hr>';
            '</div>';

            $( '#more_services_main').append( newService );
            removeService();
        });

        var removeService = function (){

            $( '.remove-service').on('click', function( event ){
                event.preventDefault();
                var $this = $( this );
                $this.closest( '.more_services_wrap' ).remove();
            });
        }
        removeService();

        /*--------------------------------------------------------------------------
         *  Thread Message Attachment
         * -------------------------------------------------------------------------*/
        var thread_message_attachment = function() {

            /* initialize uploader */
            var uploader = new plupload.Uploader({
                browse_button: 'thread-message-attachment',
                file_data_name: 'messages_upload_file',
                container: 'listing-thumbs-container',
                multi_selection: true,
                url: ajaxurl + "?action=homey_message_attacment_upload&verify_nonce=" + verify_nonce,
                filters: {

                    max_file_size: '20m',
                    prevent_duplicates: true
                }
            });
            uploader.init();

            uploader.bind('FilesAdded', function(up, files) {
                var html = '';
                var listingThumb = "";
                var maxfiles = '10';
                if(up.files.length > maxfiles ) {
                    up.splice(maxfiles);
                    alert('no more than '+maxfiles + ' file(s)');
                    return;
                }
                plupload.each(files, function(file) {
                    listingThumb += '<li id="thumb-holder-' + file.id + '" class="listing-thumb">' + '' + '</li>';
                });
                document.getElementById('listing-thumbs-container').innerHTML += listingThumb;
                up.refresh();
                uploader.start();
            });


            uploader.bind('UploadProgress', function(up, file) {
                document.getElementById( "thumb-holder-" + file.id ).innerHTML = '<li><lable>' + file.name + '<span>' + file.percent + "%</span></lable></li>";
            });

            uploader.bind('Error', function( up, err ) {
                document.getElementById('errors-log').innerHTML += "<br/>" + "Error #" + err.code + ": " + err.message;
            });

            uploader.bind('FileUploaded', function ( up, file, ajax_response ) {
                var response = $.parseJSON( ajax_response.response );

                if ( response.success ) {

                    console.log( ajax_response );

                    var message_html =
                        '<div class="attach-icon delete-attachment">' +
                        '<i class="fa fa-trash remove-message-attachment" data-attachment-id="' + response.attachment_id + '"></i>' +
                        '</div>' +
                        '<span class="attach-text">' + response.file_name + '</span>' +
                        '<input type="hidden" class="listing-image-id" name="listing_image_ids[]" value="' + response.attachment_id + '"/>' ;

                    document.getElementById( "thumb-holder-" + file.id ).innerHTML = message_html;

                    messageAttachment();
                    thread_message_attachment();

                } else {
                    console.log ( response );
                    alert('error');
                }
            });

            uploader.refresh();

        }
        thread_message_attachment();

        var messageAttachment = function() {

            $( '.remove-message-attachment' ).on('click', function () {

                var $this = $(this);
                var thumbnail = $this.closest('li');
                var thumb_id = $this.data('attachment-id');
                $this.removeClass( 'fa-trash' );
                $this.addClass( 'fa-spinner' );

                var ajax_request = $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_remove_message_attachment',
                        'thumbnail_id': thumb_id,
                    }
                });

                ajax_request.done(function( response ) {
                    if ( response.attachment_remove ) {
                        thumbnail.remove();
                    } else {

                    }
                    thread_message_attachment();
                });

                ajax_request.fail(function( jqXHR, textStatus ) {
                    alert( "Request failed: " + textStatus );
                });

            });

        }

        /*---------------------------------------------------------------------------
        *  iCalendar
        *--------------------------------------------------------------------------*/
        $('#import_ical_feeds').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var ical_feed_name = [];
            var ical_feed_url = [];

            var listing_id = $('input[name="listing_id"]').val();

            $('.ical_feed_name').each(function() {
                ical_feed_name.push($(this).val())
            });

            $('.ical_feed_url').each(function() {
                ical_feed_url.push($(this).val())
            });

            if(ical_feed_name == '' || ical_feed_url == '') {
                alert(Homey_Listing.add_ical_feeds);
                return;
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: "JSON",
                data: {
                    'action' : 'homey_add_ical_feeds',
                    'listing_id' : listing_id,
                    'ical_feed_name' : ical_feed_name,
                    'ical_feed_url' : ical_feed_url,
                },

                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(response) {
                    if( response.success ) {
                        window.location.href = response.url;
                    } else {
                        $this.children('i').remove();
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    //$this.children('i').removeClass(process_loader_spinner);
                }
            });

        }) // end #import_ical_feeds

        $( '#add_more_feed' ).on('click', function( e ){
            e.preventDefault();

            var ical_feed_name = $('.enter_ical_feed_name').val();
            var ical_feed_url = $('.enter_ical_feed_url').val();

            if(ical_feed_name == '' || ical_feed_url == '') {
                alert(Homey_Listing.both_required);
                return;
            }

            var numVal = $(this).data("increment") + 1;
            $(this).data('increment', numVal);
            $(this).attr({
                "data-increment" : numVal
            });

            var newFeed = '' +
                '<div class="imported-calendar-row clearfix">'+
                '<div class="imported-calendar-50">'+
                '<input type="text" name="ical_feed_name[]" class="form-control ical_feed_name" value="'+ical_feed_name+'">'+
                '</div>'+
                '<div class="imported-calendar-50">'+
                '<input type="text" name="ical_feed_url[]" class="form-control ical_feed_url" value="'+ical_feed_url+'">'+
                '</div>';
            '</div>';

            $( '#ical-feeds-container').append( newFeed );
            removeICalFeed();
            $('.ical-dummy').val('');
        });

        var removeICalFeed = function (){

            $( '.remove-ical-feed').on('click', function( event ){
                event.preventDefault();

                var $this = $( this );
                var listing_id = $('input[name="listing_id"]').val();
                var remove_index = $this.data('remove');

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    dataType: "JSON",
                    data: {
                        'action' : 'homey_remove_ical_feeds',
                        'listing_id' : listing_id,
                        'remove_index' : remove_index
                    },

                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                    },
                    success: function(response) {  console.log(response.message);
                        if( response.success ) {
                            $this.closest( '.imported-calendar-row' ).remove();
                            window.location.reload();
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

                var numVal = $('#add_more_feed').data("increment")
                $('#add_more_feed').attr({
                    "data-increment" : numVal-1
                });
            });
        }
        removeICalFeed();


        /*--------------------------------------------------------------------------
        * unavailable dates
        *---------------------------------------------------------------------------*/
        function homey_unavailable_dates() {
            $('.available, .unavailable').on('click', function() {
                var $this = $(this);
                var selected_date = $this.data('formatted-date');
                var listing_id = $('#period_listing_id').val();

                if($this.hasClass('past-day')) {
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        'action': 'homey_make_date_unavaiable',
                        'selected_date': selected_date,
                        'listing_id': listing_id
                    },
                    beforeSend: function( ) {
                        $this.children('i').remove();
                        $this.prepend('<i class="icon-center '+process_loader_spinner+'"></i>');
                    },
                    success: function(data) {
                        if( data.success ) {
                            if(data.message == 'made_available') {
                                $this.removeClass('unavailable');
                                $this.addClass('available');
                                $this.find('.day-status').text(avail_label);
                            } else {
                                $this.removeClass('available');
                                $this.addClass('unavailable');
                                $this.find('.day-status').text(unavail_label);
                            }
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
            })
        }
        homey_unavailable_dates(); // end homey_unavailable_dates

        $('.choose_payout_method').on('click', function(e) {
            var $this = $(this);
            var current_method = $this.val();

            if(current_method == 'wire') {
                $('#wire_transfer').show();
                $('#paypal').hide();
                $('#skrill').hide();

            } else if(current_method == 'paypal') {
                $('#wire_transfer').hide();
                $('#paypal').show();
                $('#skrill').hide();

            } else if(current_method == 'skrill') {
                $('#wire_transfer').hide();
                $('#paypal').hide();
                $('#skrill').show();
            }

        });

        function homey_show_payout_method() {
            var current_method = $("input[name='payout_method']:checked").val();

            if(current_method == 'wire') {
                $('#wire_transfer').show();
                $('#paypal').hide();
                $('#skrill').hide();

            } else if(current_method == 'paypal') {
                $('#wire_transfer').hide();
                $('#paypal').show();
                $('#skrill').hide();

            } else if(current_method == 'skrill') {
                $('#wire_transfer').hide();
                $('#paypal').hide();
                $('#skrill').show();
            }
        }

        if($('.choose_payout_method').length > 0) {
            jQuery(window).load(function(){
                homey_show_payout_method();
            });
        }

        $('.define-payout-methods input').on('focusin', function() {
            $(this).removeClass('error');
        });

        $('.homey_save_payout_method').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            errorBlock.hide();
            $('.date-saved-success').hide();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_save_payout_method_info',
                    'ben_first_name': $('#ben_first_name').val(),
                    'ben_last_name': $('#ben_last_name').val(),
                    'ben_company_name': $('#ben_company_name').val(),
                    'ben_tax_number': $('#ben_tax_number').val(),
                    'ben_street_address': $('#ben_street_address').val(),
                    'ben_apt_suit': $('#ben_apt_suit').val(),
                    'ben_city': $('#ben_city').val(),
                    'ben_state': $('#ben_state').val(),
                    'ben_zip_code': $('#ben_zip_code').val(),
                    'bank_account': $('#bank_account').val(),
                    'swift': $('#swift').val(),
                    'bank_name': $('#bank_name').val(),
                    'wir_street_address': $('#wir_street_address').val(),
                    'wir_aptsuit': $('#wir_aptsuit').val(),
                    'wir_city': $('#wir_city').val(),
                    'wir_state': $('#wir_state').val(),
                    'wir_zip_code': $('#wir_zip_code').val(),
                    'paypal_email': $('#paypal_email').val(),
                    'skrill_email': $('#skrill_email').val(),
                    'payout_method': $("input[name='payout_method']:checked").val(),
                    'security' : $('#homey_payout_method_security').val()
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        $('.date-saved-success').show();
                    } else {
                        var i;
                        var required_fields = data.req;
                        for (i = 0; i < required_fields.length; i++) {
                            $('#'+required_fields[i]).addClass('error');
                        }
                        errorBlock.show();
                    }
                    $("html, body").animate({
                        scrollTop: 0
                    }, "slow");
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').remove();
                }
            });

        });

        $('#homey_change_payout_status').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_update_payout_status',
                    'payout_status': $('#payout_status').val(),
                    'payout_id': $('#payout_id').val(),
                    'transfer_fee': $('#transfer_fee').val(),
                    'transfer_note': $('#transfer_note').val(),
                    'security' : $('#homey_payout_status_security').val()
                },
                beforeSend: function( ) {
                    $this.empty();
                    $this.html('<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
                },
                success: function(data) {
                    if( data.success ) {
                        window.location.reload();
                    } else {
                        $this.empty();
                        $this.text(btn_save);
                        alert(data.msg);
                    }
                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    //$this.children('i').remove();
                }
            });

        });

        $('#payout_status').on('change', function() {
            if($(this).val() == 3) {
                $('.transfer_fee, .transfer_note').show();
            } else {
                $('.transfer_fee, .transfer_note').hide();
            }
        });

        $( window ).load(function() {
            var payout_status = $( "#payout_status" ).val();
            if(payout_status == 3) {
                $('.transfer_fee, .transfer_note').show();
            } else {
                $('.transfer_fee, .transfer_note').hide();
            }
        });


        $('#request_payout').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            var payout_alert = $('#payout_alert');
            var payout_msg = $('#payout_alert span');
            payout_msg.empty();
            payout_alert.hide();

            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    'action': 'homey_add_payout',
                    'payout_amount': $('#payout_amount').val(),
                    'security' : $('#homey_payout_request_security').val()
                },
                beforeSend: function( ) {
                    $this.children('i').remove();
                    $this.prepend('<i class="fa-left '+process_loader_spinner+'"></i>');
                },
                success: function(data) {
                    if( data.success ) {
                        payout_alert.show();
                        payout_alert.removeClass('alert-danger');
                        payout_alert.addClass('alert-success');
                        payout_msg.html(data.msg);
                    } else {
                        payout_alert.show();
                        payout_alert.removeClass('alert-success');
                        payout_alert.addClass('alert-danger');
                        payout_msg.html(data.msg);
                    }

                },
                error: function(xhr, status, error) {
                    var err = eval("(" + xhr.responseText + ")");
                    console.log(err.Message);
                },
                complete: function(){
                    $this.children('i').remove();
                }
            });
        });


        /*--------------------------------------------------------------------------
         *  Admin adjust payment
         * -------------------------------------------------------------------------*/
        $( '#btn_make_adjustment').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $form = $this.parents( 'form' );
            var $messages = $form.find('.homey_messages');

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
                        window.location.reload();
                    } else {
                        $messages.empty().append(response.msg);
                        $this.children('i').removeClass(process_loader_spinner);
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
         *  Admin adjust payment for guest
         * -------------------------------------------------------------------------*/
        $( '#btn_guest_adjustment').on('click', function(e) {
            e.preventDefault();

            var $this = $(this);
            var $form = $this.parents( 'form' );
            var $messages = $form.find('.homey_messages');

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
                        window.location.reload();
                    } else {
                        $messages.empty().append(response.msg);
                        $this.children('i').removeClass(process_loader_spinner);
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


    } // End Type Of

    var locationAnnee       = null;
    var $modal              = jQuery("#calendarModal");
    var events              = null;
    var locationId          = null;
    var momentDateFormat    = "DD/MM/YYYY HH:mm";

    function resetModalForm(){
        var $modal      = $('#calendarModal'),
            $hStart     = $("#hstart", $modal),
            $hEnd       = $("#hend", $modal);
        viderMessage($(".modal-body", $modal));
        $hStart.val("");
        $hStart.selectpicker("refresh");
        $hEnd.val("");
        $hEnd.selectpicker("refresh");
    }
    function viderMessage($container){
        $('.alert', $container).remove();
    }
    function heureVide(hV){
        return hV == "";
    }
    function heureOk(hS, hE){
        return hE.diff(hS) > 0;
    }

    function hourChange(){
        var startValue  = $("#hstart", $modal).val();
        var $modalBody  = $(".modal-body", $modal);
        var endValue    = $("#hend", $modal).val();
        var dateValue   = $("#datePicker", $modal).val();

        var alertTemplate = $("#alertTemplate").html();
        Mustache.parse(alertTemplate);

        viderMessage($modalBody);

        var hstart      = moment(dateValue+" "+startValue, momentDateFormat);
        var hend        = moment(dateValue+" "+endValue, momentDateFormat);

        if(!heureOk(hstart, hend)){
            var alertDom = Mustache.render(alertTemplate, {
                class: "warning",
                message: "Fin doit être supérieur au début!"
            });
            $modalBody.prepend(alertDom);
        }

    }

    $("#hstart", $modal).on("change",hourChange);
    $("#hend", $modal).on("change",hourChange);


    /**
     * Catch reload event handler :)
     * trigger the event by calling $(document).trigger("oolnatimeline:reload")
     */

    $(document).on("oolnatimeline:reload", function(e){
        homey_hourly_availability_calendar_dash();
    });
});

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
    var wS  = date1 === undefined ? now.startOf("week").utcOffset(3) : date1;
    var wE  = date2 === undefined ? now.endOf("week").utcOffset(3) : date2;
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
    if(wS === undefined || !(wS instanceof moment)){
        wS  = moment().startOf("week").utcOffset(3);
        //if(!(wS instanceof moment)) throw Exception('Start date must be instance of moment object');
    }
    if(wE === undefined || !(wE instanceof moment)){
        wE  = moment().endOf("week").utcOffset(3);
        //if(!(wE instanceof moment)) throw Exception('End date must be instance of moment object');
    }

    var resources = [];
    var month = wS.month();

    while(wE.diff(wS) >= 0 ){ // && wS.month() === month
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

var buildSkedTape = function($el, resources, events){
    if(!($el instanceof jQuery)) $el = jQuery($el);
    var start   = moment.unix(openTime).utc(),
        end     = moment.unix(closeTime).utc();
    var diff    = end.diff(start, "seconds");
    var mDiff   = moment("00:00", "HH:mm").utc().add(end.diff(start, "seconds"), "seconds");
    if(mDiff.format("mm") == "30") {
        end.subtract(30, "minutes");
        diff = end.diff(start, "seconds");
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
        events: events,
        showDates: false,
        tzOffset: 0,
        scrollWithYWheel: true,
        tick: tick            //Heure io an!
    });
    jQuery('.spin').find('.homey_preloader').hide();
}

jQuery(document).on("timeline:click.skedtape", "#homey_hourly_calendar_edit_listing", function(e, api){
    var event       = e.detail;
    var mDate       = moment(event.date);
    locationId      = event.locationId; // DD/MM -> DD/MM/YYYY
    locationAnnee   = locationId + "/" + mDate.format("YYYY");
    var $modal  = jQuery("#calendarModal");

    jQuery("#datePicker", $modal).val(locationAnnee);

    var selectedDate= moment(locationAnnee, "DD/MM/YYYY");
    jQuery("#date").text("le " + selectedDate.format("DD MMMM YYYY"));
    resetModalForm();
    var $btnTrash = jQuery('#trash-event');
    $btnTrash.hide();
    $modal.off('shown.bs.modal');  // prevent loop event listener
    $modal.modal("show");
});
jQuery(document).on('event:click.skedtape', "#homey_hourly_calendar_edit_listing", function(e, api) { // clique sur la brique.
    var mDate       = moment(event.date);
    var dateStr = e.detail.event.location + mDate.format("/YYYY");
    var date = moment(dateStr, "DD/MM/YYYY").toDate();
    var hstart = new Date(Date.parse(e.detail.event.start));
    var hend = new Date(Date.parse(e.detail.event.end));
    var params={};
    var $paramsInput = jQuery("#params");
    var $modal  = jQuery("#calendarModal");
    var $btnTrash = jQuery('#trash-event');
    location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){params[k]=v});
    params.date =  moment(date).format("DD/MM/YYYY");
    params.hstart = moment(hstart).format("HH:mm");
    params.hend = moment(hend).format("HH:mm");
    params.name = e.detail.event.name;
    params.token = e.detail.event.data._key;
    $modal.off('shown.bs.modal'); // prevent loop event listener
    $modal.on('shown.bs.modal', function(){
        resetModalForm();
        jQuery(this).find('#datePicker').val(params.date);
        jQuery("#date").text("le " + moment(date).format("DD MMMM YYYY"));
        const $hstart = jQuery(this).find('#hstart');
        $hstart.val(params.hstart);
        $hstart.selectpicker("refresh");
        const $hend = jQuery(this).find('#hend');
        $hend.val(params.hend); // @todo modifier
        $hend.selectpicker("refresh");
        $paramsInput.val(JSON.stringify(params));
        $btnTrash.show();
    });
    resetModalForm();

    $modal.modal("show");
});
jQuery(document).on('click', '#trash-event', function(e){
    e.preventDefault();
    var $modal  = jQuery("#calendarModal");
    var $paramsInput = jQuery("#params");
    var params = JSON.parse($paramsInput.val());
    jQuery.ajax({
        url: "/api/calendar/trash",
        type: "get",
        dataType: 'json',
        data: params,
        success: function(res){
            jQuery(document).trigger("oolnatimeline:reload");
            $modal.modal('toggle');
            window.resetModalForm();
        }
        ,
        error: function(xhr, status, error) {
            var err = eval("(" + xhr.responseText + ")");
            console.log(err.Message);
        }
    });
});

var getHoursSlot = function(){
    var listing_events=[];
    var tzKey = "studioTz";
    var sqlFormat   = "YYYY-MM-DD HH:mm:ss";
    
    for (var key in booked_hours_array) {
        if (booked_hours_array.hasOwnProperty(key) && key !=='' && key!=="renter" && key !== tzKey) {
            var temp_book=[];
            var startUnix = moment.unix(key); // .tz(booked_hours_array[tzKey]);
            //startUnix.add(timezoneOffsetMinutes, "minutes");
            //startUnix.add(booked_hours_array[tzKey], "seconds");
            var endUnix = moment.unix(booked_hours_array[key]); // .tz(booked_hours_array[tzKey]);
            //endUnix.add(timezoneOffsetMinutes, "minutes");
            //endUnix.add(booked_hours_array[tzKey], "seconds");
            temp_book['name']      =   Homey_Listing.hc_reserved_label;
            temp_book['location']  =   startUnix.format('DD/MM');
            temp_book ['start']    =   startUnix.format(sqlFormat);
            temp_book ['end']      =   endUnix.format(sqlFormat);
            temp_book['source']    =   'wp';
            listing_events.push(temp_book);
        }
    }

    return listing_events;
}

/* ------------------------------------------------------------------------ */
/* Per Hour availability calendar
/* ------------------------------------------------------------------------ */
function homey_hourly_availability_calendar_dash(){
    /**
     * Week start - Week end
     * Timezone transformation
     */
    var $fromDate           = jQuery("#fromDateBo"),
        $toDate             = jQuery("#toDateBo");

    var resources           = [],
        momentDateFormat    = "DD/MM/YYYY";
    var timezone_offset_minutes = new Date().getTimezoneOffset();
    timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
    const f = moment($fromDate.val(), momentDateFormat); // save data from pointer
    const f0 = moment($fromDate.val(), momentDateFormat);
    f.hour(0); f.minute(0); f.second(0);
    f0.hour(0); f0.minute(0); f0.second(0);
    const t = moment($toDate.val(), momentDateFormat);
    t.hour(23); t.minute(59); t.second(59);
    var hs = jQuery.merge(getHoursSlot(), googleAgendaList);

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
        buildSkedTape('#homey_hourly_calendar_edit_listing', resources, events);
    });
}


/**
 * Obtenir les heures de fermeture du studio
 */
function getIndisponibilite(mDebut, mFin, postId, tz, callback){
    var $ = jQuery;
    var sqlFormat   = "YYYY-MM-DD HH:mm";
    var $btnNext     = $(".btn-calendarBo-next"),
        $btnPrevious = $(".btn-calendarBo-previous");
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