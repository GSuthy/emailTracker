$(document).ready(function(){
    $('div button#refreshQueuesTable').click(function(){
        refreshQueuesTable();
    });
});

function refreshQueuesTable() {
    $.ajax
    ({
        type: "GET",
        url: "queues/index",
        dataType: "json"
    })
        .done(function(data)
        {
            var rowCount = 0;
            $('div table#queueTable tbody tr').remove();
            var clusters = data["results"];
            for (var key in clusters) {
                var subHeader = "<tr class='sub-header'>" +
                                    "<td colspan='3'>" + key + "</td>" +
                                "</tr>";
                $('div table#queueTable tbody').append(subHeader);

                for (var i in clusters[key]) {
                    var server = clusters[key][i]["server"] == null ? "" : clusters[key][i]["server"];
                    var activeQueue = clusters[key][i]["active_queue"] == null ? "" : clusters[key][i]["active_queue"];
                    var deferredQueue = clusters[key][i]["deferred_queue"] == null ? "" : clusters[key][i]["deferred_queue"];

                    var rowString = "<tr class='" + (i++ % 2 == 0 ? "even" : "odd") + "'>" +
                        "<td>" + server + "</td>" +
                        "<td>" + activeQueue + "</td>" +
                        "<td>" + deferredQueue + "</td>" +
                        "</tr>";
                    $('div table#queueTable tbody').append(rowString);
                }
            }
        });
}