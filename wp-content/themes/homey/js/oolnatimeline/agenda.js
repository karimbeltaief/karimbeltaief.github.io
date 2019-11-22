
class Agenda extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            calendarList: []
        };
        
        this.fetchCalendarList = this.fetchCalendarList.bind(this);
        this.setSelectedCalendar = this.setSelectedCalendar.bind(this);
    }
    render() {
        const {hasAccount} = this.props;
        var selectedCalendar = this.props.selectedCalendar;
        const {calendarList} = this.state;
        if(typeof calendarList == 'undefined' || calendarList.length == 0 ) return null;
        const calendarItems = calendarList.map(function(calendarItem, key){
            return <option value={calendarItem.id} key={calendarItem.id}>{calendarItem.name}</option>
        });
        return (
            <div className="agenda">
                {hasAccount &&
                <div className="form-group">
                    <label title="Cliquer sur 'Update' pour le rattacher">Votre agenda:</label>
                    <select className="form-control" name="listing_calendar_id" onChange={this.setSelectedCalendar} value={selectedCalendar} title="Cliquer sur 'Update' pour le rattacher">
                        {calendarItems}
                    </select>
                    <i className="fa fa-chevron-down" style={{
                        'float': "right",
                        'marginTop': '-30px',
                        'marginRight': '5px',
                        'pointerEvents': 'none',
                        'backgroundColor': 'transparent',
                        'paddingRight': '5px'
                    }}></i>
                </div>
                }
            </div>
        );
    }
    
    fetchCalendarList(){
        const {googleAccount, hasAccount, setHasCalendar, postId} = this.props;
        var _this = this;
        if(hasAccount) {
            jQuery.ajax({
                url: "/api/google-account/" + googleAccount.id + "/calendar",
                type: "get",
                data:{
                    postId: postId
                },
                success: function (list) {
                    _this.setState({
                        calendarList: list
                    });
                    if(list.length > 0) {
                        setHasCalendar(true);
                    }else{
                        setHasCalendar(false);
                    }
                }
            });
        }else {
            _this.setState({
                calendarList: []
            });
        }
    }
    setSelectedCalendar(e){
        this.props.setSelectedCalendar(e.target.value);
    }
    componentDidMount(){
        this.fetchCalendarList();
    }
}
