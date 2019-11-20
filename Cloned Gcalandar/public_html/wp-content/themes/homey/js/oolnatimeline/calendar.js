
class Calendar extends React.Component{
    constructor(props){
        super(props);
        const {useOldCalendar} = this.props;
        this.weekChanged = this.weekChanged.bind(this);
        this.nextWeek = this.nextWeek.bind(this);
        this.previousWeek = this.previousWeek.bind(this);
        this.updateTimeline = this.updateTimeline.bind(this);
        var momentDateFormat = "DD/MM/YYYY";
        this.state = {
            dateFormat : "dd/mm/yy",
            momentDateFormat : momentDateFormat,
            bookedHours: [],
            weekStart: null,
            weekEnd: null
        };
        if(!useOldCalendar) this._timeline = React.createRef();
    }
    componentDidMount(){
        const {useOldCalendar} = this.props;
        let s = moment().startOf("week");
        s.set({hour: 0, minute: 0, second: 0, millisecond: 0});
        let e = moment().endOf("week");
        e.set({hour: 0, minute: 0, second: 0, millisecond: 0});
        this.setState({
            weekStart: s,
            weekEnd: e
        });
        const {weekStart, weekEnd} = this.state;
        this.setState({
            bookedHours: this.props.applyDateFilter(this.props.getHoursSlot(), weekStart, weekEnd)
        });
        window.google.charts.load('current', {
            'packages': ['timeline']
        });
        if(!useOldCalendar) window.google.charts.setOnLoadCallback(this._timeline.current.drawTimeline);
    }
    nextWeek(event){
        const {useOldCalendar} = this.props;
        var {weekStart, weekEnd} = this.state;
        let s = weekStart.add(7,"days");
        let e = weekEnd.add(7, "days");
        this.setState({
            weekStart: s,
            weekEnd: e
        });
        if(!useOldCalendar) this._timeline.current.drawTimeline();
        event.stopPropagation();
    }
    previousWeek(event){
        const {useOldCalendar} = this.props;
        var {weekStart, weekEnd} = this.state;
        let s = weekStart.subtract(7,"days");
        let e = weekEnd.subtract(7, "days");
        this.setState({
            weekStart: s,
            weekEnd: e
        });
        if(!useOldCalendar) this._timeline.current.drawTimeline();
        event.stopPropagation();
    }
    weekChanged(e){
        const {useOldCalendar} = this.props;
        let d = e.target.value;
        var weekS = moment(d, this.state.momentDateFormat).utcOffset(3);
        var weekE = moment(d, this.state.momentDateFormat).utcOffset(3).add(7,"days");
        this.setState({
            weekStart: weekS,
            weekEnd: weekE
        });
        if(!useOldCalendar) this._timeline.current.drawTimeline();
    }
    updateTimeline(e){
        const {useOldCalendar} = this.props;
        if(!useOldCalendar) this._timeline.current.drawTimeline();
        else{
            console.log("triggering event : oolnatimeline:reload");
            jQuery(document).trigger("oolnatimeline:reload"); // custom event
        }
    }
    render(){
        const {calendarId, postId, useOldCalendar} = this.props;
        const {dateFormat, momentDateFormat, weekStart, weekEnd, bookedHours} = this.state;
        const screenWidth = (window.innerWidth > 0) ? window.innerWidth : window.screen.width;
        const cEFormat1 = (screenWidth > 576) ? "ddd DD MMM" : "DD/MM",
            cEFormat2 = (screenWidth > 576) ? "ddd DD MMM YYYY" : "DD/MM/YYYY";
        return (
            <div>
                <Tools
                    calendarId={calendarId}
                    postId={postId}
                    weekStart={weekStart}
                    weekEnd={weekEnd}
                    updateTimeline={this.updateTimeline}
                />
                {!useOldCalendar &&
                <WeekSelect
                    weekChanged={this.weekChanged}
                    datePickerFormat={dateFormat}
                    momentDateFormat={momentDateFormat}
                    cEFormat1={cEFormat1}
                    cEFormat2={cEFormat2}
                    weekStart={weekStart}
                    weekEnd={weekEnd}
                    nextWeek={this.nextWeek}
                    previousWeek={this.previousWeek}
                />
                }
                {!useOldCalendar &&
                <Timeline
                    weekStart={weekStart}
                    weekEnd={weekEnd}
                    ref={this._timeline}
                    postId={this.props.postId}
                    getHoursSlot={this.props.getHoursSlot}
                    generateResource={this.props.generateResource}
                    applyDateFilter={this.props.applyDateFilter}
                    getIndisponibilite={this.props.getIndisponibilite}
                />
                }
            </div>
        );
    }
}
var container = document.getElementById("oolnaCalendar");

if(container != null) {
    ReactDOM.render(<Calendar
        calendarId={window.calendarId ? window.calendarId: "null"}
        postId={window.postId}
        getHoursSlot={window.getHoursSlot}
        generateResource={window.generateResource}
        applyDateFilter={window.applyDateFilter}
        getIndisponibilite={window.getIndisponibilite}
        useOldCalendar={window.useOldCalendar}
    />, container);
}