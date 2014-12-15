$(document).ready(function(){
    $('div button#refreshQueuesTable').click(function(){
        refreshQueuesTable();
    });
});

function refreshQueuesTable() {
    $.ajax
    ({
        type: "GET",
        url: "queues/getTable",
        dataType: "json"
    })
        .done(function(data)
        {
            $('div table#queueTable tbody tr').remove();
            for (var i = 0; i < data["results"].length; i++) {
                var gatewayQueue = data["results"][i]["GatewayQueues"];
                var server = gatewayQueue["server"] == null ? "" : gatewayQueue["server"];
                var activeQueue = gatewayQueue["active_queue"] == null ? "" : gatewayQueue["active_queue"];
                var deferredQueue = gatewayQueue["deferred_queue"] == null ? "" : gatewayQueue["deferred_queue"];

                var rowString = "<tr>" +
                    "<td>" + server + "</td>" +
                    "<td>" + activeQueue + "</td>" +
                    "<td>" + deferredQueue + "</td>" +
                    "</tr>";

                $('div table#queueTable tbody').append(rowString);
            }
        });
}