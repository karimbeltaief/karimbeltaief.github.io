function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
function resetModalForm(){
    var $modal      = jQuery('#calendarModal'),
        $hStart     = jQuery("#hstart", $modal),
        $hEnd       = jQuery("#hend", $modal),
        $params     = jQuery("#params", $modal);
    viderMessage(jQuery(".modal-body", $modal));
    $hStart.val("");
    $hStart.selectpicker("refresh");
    $hEnd.val("");
    $hEnd.selectpicker("refresh");
    $params.val("");

}

function viderMessage($container){
    jQuery('.alert', $container).remove();
}
function heureVide(hV){
    return hV == "";
}
function heureOk(hS, hE){
    return hE.diff(hS) > 0;
}

class Tools extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            selectedCalendar: "",
            googleAccount: [],
            hasAccount: null,
            hasCalendar: false,
            tzOffset: "",
            synchronizing: false
        };
        this.login = this.login.bind(this);
        this.fetchGoogleAccount = this.fetchGoogleAccount.bind(this);
        this.setSelectedCalendar = this.setSelectedCalendar.bind(this);
        this.logout = this.logout.bind(this);
        this.syncCalendar = this.syncCalendar.bind(this);
        this.hasCalendar = this.hasCalendar.bind(this);
    }
    fetchGoogleAccount () {
        var _this = this;
        if(window.userId != null){
            jQuery.ajax({
                url: "/api/user/"+window.userId+"/google-account",
                type: "get",
                beforeSend: function(){
                    _this.setState({
                        synchronizing: true
                    });
                },
                success: function (json) {
                    _this.setState({
                        googleAccount: json,
                        hasAccount: json != null && json !== "",
                        synchronizing: false
                    });
                },
                error: function(){
                    _this.setState({
                        googleAccount: {},
                        hasAccount: false,
                        synchronizing: false
                    });
                }
            });
        }
    }
    setSelectedCalendar(calendarId){
        this.setState({
            selectedCalendar: calendarId
        });
        _this.props.updateTimeline();
    }
    login () {
        const {postId} = this.props;
        /**
         * Ce cookies permettra de se rédiriger sur cette page.
         */
        setCookie("redirect", window.location.href, 1);
        setCookie("postId", postId);
        /**
         * Lorisleiva google calendar auth
         * @type {string}
         */
        window.location.href = "/google/oauth?postId=" + postId;
    }
    syncCalendar(){
        const {selectedCalendar, tzOffset} = this.state;
        const {postId, weekStart, weekEnd} = this.props;
        var _this = this;
        let params = {
            postId: postId,
            tz: tzOffset,
            start: weekStart.format("YYYY-MM-DD HH:mm"), // Y-m-d 00:00
            end: weekEnd.format("YYYY-MM-DD HH:mm"), // Y-m-d 00:00
        };
        /**
         * DB to Google Calendar
         */
        jQuery.ajax({
            url: "/api/calendar/"+selectedCalendar+"/sync",
            type: "get",
            data: params,
            dataType:"json",
            beforeSend:function(){
                _this.setState({
                    synchronizing: true
                });
            },
            success: function(response){
                if(response != null && response !== ""){
                    if("success" === response.status){
                        alert(response.message);
                    }else{
                        alert(response.message);
                    }
                }

                _this.setState({
                    synchronizing: false
                });
                _this.props.updateTimeline();
            }
        });
    }

    logout () {
        const {googleAccount} = this.state;
        var _this = this;
        jQuery.ajax({
            url: "/google/delete/" + googleAccount.id,
            type: "DELETE",
            beforeSend: function(){
                _this.setState({
                    synchronizing: true
                });
            },
            success: function (response) {
                if(response != null && response !== ""){
                    if ("success" === response.status){
                        _this.fetchGoogleAccount();
                    }else{
                        alert(response.message);
                    }
                }
                _this.setState({
                    synchronizing: false
                });
                _this.props.updateTimeline();
            }
        });
    }
    componentDidMount(){
        var _this = this;
        const {calendarId, postId} = this.props;
        this.fetchGoogleAccount();
        var timezone_offset_minutes = new Date().getTimezoneOffset();
        timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
        _this.setState({
            selectedCalendar: calendarId,
            tzOffset: timezone_offset_minutes
        });
        _this.props.updateTimeline();
        jQuery(document).on("click", "#okButton", function(){
            var $modal      = jQuery("#calendarModal");
            var momentDateFormat    = "DD/MM/YYYY HH:mm";
            var $modalBody  = jQuery(".modal-body", $modal);
            var startValue  = jQuery("#hstart",$modal).val(),
                endValue        = jQuery("#hend", $modal).val();
            var dateValue   = jQuery("#datePicker", $modal).val();

            var alertTemplate = jQuery("#alertTemplate").html();
            Mustache.parse(alertTemplate);

            viderMessage($modalBody);
            var alertDom = "";
            if(heureVide(startValue) || heureVide(endValue)){
                alertDom = Mustache.render(alertTemplate, {
                    class: "warning",
                    message: "Ne laissez aucun champ vide!"
                });
                $modalBody.prepend(alertDom);
                return;
            }
            var startMoment = moment(dateValue+" "+startValue, momentDateFormat),
                endMoment       = moment(dateValue+" "+endValue, momentDateFormat),
                $this           = jQuery(this);
            if(!heureOk(startMoment, endMoment)){
                alertDom = Mustache.render(alertTemplate, {
                    class: "warning",
                    message: "Fin doit être supérieur au début!"
                });
                $modalBody.prepend(alertDom);
                return;
            }


            let outputDateFormat = "YYYY-MM-DD HH:mm";
            const params = jQuery("#params", $modal).val();
            const paramsObj = params !== "" ? JSON.parse(params) : null;
            const token = (params && paramsObj.token) ? paramsObj.token : "";
            /**
             * @Todo create indisponibilite
             */
            jQuery.ajax({
                url: "/api/calendar/"+ (_this.state.selectedCalendar) +"/indisponibilites",
                type: "post",
                dataType: 'json',
                data:{
                    postId: postId,
                    tz: timezone_offset_minutes,
                    start: startMoment.format(outputDateFormat), // Y-m-d 00:00
                    end: endMoment.format(outputDateFormat), // Y-m-d 00:00,
                    token: token, // if token => update
                },
                beforeSend: function(){
                    $this.attr("disabled","disabled");
                },
                success: function(response){
                    if(typeof response != "undefined"){
                        var alertDom = '';
                        if(response.status == "success"){
                            alertDom    = Mustache.render(alertTemplate,{
                                class   : "success",
                                message : response.message
                            });
                            $modalBody.prepend(alertDom);
                            // Temporiser pour voir le message
                            setTimeout(function(){
                                $modal.modal("hide");
                                _this.props.updateTimeline(); //Actualisation du calendrier
                            }, 2000);

                        }else{
                            alertDom    = Mustache.render(alertTemplate,{
                                class   : "danger",
                                message : response.message
                            });
                            $modalBody.prepend(alertDom);
                        }
                    }
                },
                complete: function(){
                    $this.removeAttr("disabled");
                }
            });
        });

    }

    hasCalendar(has){
        this.setState({
            hasCalendar: has
        });
    }
    showModal(){
        var $modal  = jQuery("#calendarModal");
        window.resetModalForm();
        $modal.modal("show");
    }
    render () {
        const {hasAccount, googleAccount, selectedCalendar, hasCalendar, synchronizing} = this.state;
        const {postId} = this.props;
        if(hasAccount == null) return null;
        return (
            <div className='controls no-gutters'>
                <div className='col-sm-8 col-md-7 col-xs-6'>
                    {hasAccount &&
                    <Agenda googleAccount={googleAccount}
                            hasAccount={hasAccount}
                            selectedCalendar={selectedCalendar}
                            setSelectedCalendar={this.setSelectedCalendar}
                            postId={postId}
                            hasCalendar={hasCalendar}
                            setHasCalendar={this.hasCalendar}
                    />
                    }
                </div>
                <div className='col-sm-4 col-md-5 col-xs-6'>
                    {!hasAccount &&
                    <div className="gapi-buttons">
                        <button
                            title="Se connecter à Google Calendar"
                            className="btn btn-sm btn-default"
                            type="button"
                            onClick={this.login}
                        >
                            <i className="fa fa-sign-in"/>
                            <i className="fa fa-google"/>
                        </button>
                    </div>
                    }
                    {hasAccount &&
                    <div className="gapi-buttons">
                        <button
                            title="Synchroniser l'Agenda"
                            className="btn btn-primary"
                            type="button"
                            onClick={this.syncCalendar}
                            disabled={!hasCalendar || synchronizing}
                        >
                            <i className="fa fa-refresh"/>
                        </button>
                        <button title="Ajouter indisponibilité"
                                className="btn btn-secondary"
                                type="button"
                                onClick={this.showModal}
                        >
                            <i className="fa fa-plus"/>
                        </button>
                        <button
                            title="Se déconnecter de Google Calendar"
                            className="btn btn-danger"
                            type="button"
                            onClick={this.logout}
                            disabled={synchronizing}
                        >
                            <i className="fa fa-sign-out"/>
                        </button>
                    </div>
                    }

                </div>
            </div>
        );
    }
}
// var element = document.getElementById('timeline-controls');
//
// if(element != null){
//     ReactDOM.render(<Tools
//         calendarId={window.calendarId}
//         postId={window.postId}
//     />, element);
// }