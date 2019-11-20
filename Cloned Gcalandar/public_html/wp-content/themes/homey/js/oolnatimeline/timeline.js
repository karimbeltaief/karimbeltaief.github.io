class Timeline extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            rows: [],
            chart: null
        };
        this.drawTimeline = this.drawTimeline.bind(this);
        this.addColumn = this.addColumn.bind(this);
        this.addRow = this.addRow.bind(this);
    }
    componentDidMount(){
        const {weekStart, weekEnd} = this.props;
    }
    drawTimeline(){
        const {weekStart, weekEnd, postId} = this.props;
        const wS = moment(weekStart);
        const wE = moment(weekEnd);
        var _this = this;
        var bookedHours = this.props.applyDateFilter(this.props.getHoursSlot(), weekStart, weekEnd);

        var timezone_offset_minutes = new Date().getTimezoneOffset();
        timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
        var indispos = [];

        var resources = this.props.generateResource(wS, wE);
        var {chart} = this.state;
        var container = document.getElementById('timeline');
        if (weekStart != null &&weekEnd != null) {
            this.props.getIndisponibilite(weekStart, weekEnd, postId, timezone_offset_minutes, function(indisponibilites){
                if(typeof indisponibilites != "undefined" && indisponibilites != null){
                    var sqlFormat   = "YYYY-MM-DD HH:mm:ss";
                    var now         = moment();
                    indisponibilites.forEach(function(indispo){
                        var start   = moment(indispo.started_at.date),
                            end     = moment(indispo.ended_at.date);
                        start.set("date", now.get("date"));
                        end.set("date", now.get("date"));
                        var event           = [];
                        event["name"]       = "Indisponible";
                        event["location"]   = moment(indispo.started_at.date).format("DD/MM");
                        event["start"]      = start;
                        event["end"]        = end;
                        event["className"]  = "busy";
                        event["source"]     = "indisponible";
                        indispos.push(event);
                    });
                }
                if(chart == null){
                    _this.setState({
                        chart: new window.google.visualization.Timeline(container)
                    });
                    chart = _this.state.chart;
                }else{
                    chart.clearChart();
                }

                var dataTable = _this.getDataTable(resources, [...bookedHours,...indispos]);

                // Every time the table fires the "select" event, it should call your
                // selectHandler() function.
                window.google.visualization.events.addListener(chart, 'select', function (e){
                    //debugger;
                });
                // set a padding value to cover the height of title and axis values
                var paddingHeight = 50;

                // the natural height of all rows combined
                var rowHeight = dataTable.getNumberOfRows() * 41;

                // set the total chart height
                var chartHeight = rowHeight + paddingHeight;

                var colors = [];
                var colorMap = {
                    wp: "#ff1654",
                    agenda: "#247ba0",
                    indisponible: "#011627"
                };

                for(var row = 0; row < dataTable.getNumberOfRows(); row++){
                    colors.push(colorMap[dataTable.getValue(row, 4)]);
                }
                var options = {
                    hAxis: {
                        format: 'HH:mm',
                    },
                    height: chartHeight,
                    colors: colors
                };
                //chart.draw(dataTable, options);

                var view = new google.visualization.DataView(dataTable);
                view.setColumns([0,1,2,3]);
                chart.draw(view, options);
                (function(){                                            //anonymous self calling function to prevent variable name conficts
                    var el=container.getElementsByTagName("rect");      //get all the descendant rect element inside the container
                    var width=100000000;                                //set a large initial value to width
                    var elToRem=[];                                     //element would be added to this array for removal
                    for(var i=0;i<el.length;i++){                           //looping over all the rect element of container
                        var cwidth=parseInt(el[i].getAttribute("width"));//getting the width of ith element
                        if(cwidth<width){                               //if current element width is less than previous width then this is min. width and ith element should be removed
                            elToRem=[el[i]];
                            width=cwidth;                               //setting the width with min width
                        }
                        else if(cwidth==width){                         //if current element width is equal to previous width then more that one element would be removed
                            elToRem.push(el[i]);
                        }
                    }
                    for(var j=0;j<elToRem.length;j++) // now iterate JUST the elements to remove
                        elToRem[j].setAttribute("fill","none"); //make invisible all the rect element which has minimum width
                })();
            });
        }
    }

    getDataTable(resources, bookedHours) {
        const {weekStart, weekEnd} = this.props;

        var rows = [];
        var dataTable = new window.google.visualization.DataTable();
        this.addColumn(dataTable, {
            type: 'string',
            id: 'day'
        });
        this.addColumn(dataTable, {
            type: 'string',
            id: 'name'
        });
        this.addColumn(dataTable, {
            type: 'date',
            id: 'Start'
        });
        this.addColumn(dataTable, {
            type: 'date',
            id: 'End'
        });
        this.addColumn(dataTable, {
            type: 'string',
            id: 'category'
        });
        /**
         * Loop
         * TODO: AddRow([rowLabel, barLabel, start:Date, end:Date,category:Couleur]);
         */
        resources.forEach((resource, index) => {
            const memeDate = new Date("2019-09-14 00:00");
            var row = [];
            // TODO: map events
            const results = bookedHours.filter((hour) => {
                return hour.location === resource.id;
            });
            if (results.length > 0) {
                results.forEach((res) => {
                    row = [
                        resource.name, (res.source === "indisponible" ? "Indisponible" : ""), new Date(res.start), new Date(res.end), res.source
                    ];
                    rows.push(row);
                });
            } else {
                // LIgne vide sans evenements
                row = [
                    resource.name, "", memeDate, memeDate, "wp"
                ];
                rows.push(row);
            }
        });

        dataTable.addRows(rows);
        this.setState({
            rows: rows
        });
        return dataTable;
    }

    addColumn(dataTable, opts){
        dataTable.addColumn(opts);
    }
    addRow(row){
        this.setState({
            rows: [...this.state.rows, row]
        });
    }
    render(){
        return (
                <div id="timeline">

                </div>
        );
    }
}
