// -------------------------- Helpers ------------------------------
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

var $sked = $('#sked').skedTape({
    caption: 'Cities',
    start: yesterday(22, 0), // Timeline starts this date-time (UTC)
    end: today(12, 0),       // Timeline ends this date-time (UTC)
    showEventTime: true,     // Whether to show event start-end time
    showEventDuration: true, // Whether to show event duration
    locations: [
        {id: 1, name: 'San Francisco'}, // required properties only
        {
            id: 'london',
            name: 'Sydney',
            order: 1, // optional sorting order
            tzOffset: -10 * 60, // individual timezone (notice that minus sign)
            userData: {} // optional some custom data to store
        }
    ],
    events: [
        {
            name: 'Meeting 1',
            location: 'london',
            start: today(4, 15),
            end: today(7, 30)
        },
        // ...
    ]
});