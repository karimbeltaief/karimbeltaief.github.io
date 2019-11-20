
class WeekSelect extends React.Component{
    constructor(props){
        super(props);
        this.clickCacheEcran = this.clickCacheEcran.bind(this);
    }
    componentDidMount(){
        const {datePickerFormat} = this.props;
        var $datepicker = jQuery(".cache-ecran-wrapper .datepicker");
        $datepicker.datepicker({
            changeMonth : true,
            changeYear  : true,
            dateFormat  : datePickerFormat
        });
        $datepicker.on("change", this.props.weekChanged);
        jQuery(".cacheEcranBo").off("click");

    }
    clickCacheEcran(e){
        var $this = jQuery(e.target);
        var $parent = $this.parents(".cache-ecran-wrapper");
        var $datepicker = jQuery("input.datepicker", $parent);
        $datepicker.trigger("focus");
    }
    render(){
        const {weekStart, weekEnd,momentDateFormat, cEFormat1, cEFormat2} = this.props;
        return(
            <div className="week-select input-group">
                <div className="week-select-wrapper">
                    <div>
                        <button title="Semaine précédente"
                                className="btn btn-default btn-calendarBo-previous"
                                type="button"
                                onClick={this.props.previousWeek}
                        >
                            <i className="fa fa-chevron-left"/>
                        </button>
                        <button title="Semaine suivante"
                                className="btn btn-default btn-calendarBo-next"
                                type="button"
                                onClick={this.props.nextWeek}
                        >
                            <i className="fa fa-chevron-right"/>
                        </button>
                        <div className="input-icon-calendar cache-ecran-wrapper">
                            <input id="debutSemainePicker" type="text" className="datepicker" value={weekStart ? weekStart.format(momentDateFormat) : ""} onChange={this.props.weekChanged}/>
                            <div className="cacheEcranBo" title="Cette semaine" onClick={this.clickCacheEcran}>
                                {weekStart && "Du " + weekStart.format(cEFormat1) + " au " + weekEnd.format(cEFormat2)}
                            </div>
                            <i className="fa fa-calendar icon"/>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}