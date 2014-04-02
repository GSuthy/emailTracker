var NUM_RESULTS_INITIAL = 30;
var ADDITIONAL_RESULTS_CONST = 20;
var numResults;

$(document).ready(function(realm, stream) {

    numResults = {'canit': NUM_RESULTS_INITIAL, 'routers': 0, 'exchange': 0};

    $('#tabs').tabs();

    $("a.view-more-results.canit").on("click", function(){
        buttonClicked("canit");
    });

    $("table tr.canit td.open-log").click(function() {
        openLog($(this).parent());
    });

    $("table.results td span.has-incident").on("click", function(){
        var clickedRow = $(this).parent().parent();

        var realm = clickedRow[0]['cells'][10].innerHTML;
        var id = clickedRow[0]['cells'][11].innerHTML;
        var stream = clickedRow[0]['cells'][5].innerHTML;
        var url = "https://emailfilter.byu.edu/canit/showincident.php?&id=" + id + "&rlm=" + realm + "&s=" + stream;
        window.open(url, '_blank');
    });

    // Initialize the datepickers
    $( "#datepickerStart" ).datepicker({
        inline: true,
        showOtherMonths: true,
        dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        showAnim: '',
        defaultDate: 0,

        // 'setDate', new Date(),
        onClose: function( selectedDate )
        {
            $( "#datepickerEnd" ).datepicker( "option", "minDate", selectedDate );
        }
    });

    $( "#datepickerEnd" ).datepicker({
        inline: true,
        showOtherMonths: true,
        defaultDate: 0,
        dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        showAnim: '',
        onClose: function( selectedDate )
        {
            $( "#datepickerStart" ).datepicker( "option", "maxDate", selectedDate );
        }
    });

    // This is for the button clicks on the server boxes
    $('div.box-selector').click(function(){
        if ($(this).hasClass('on')) {
            $(this).removeClass('on');
            checkboxHandler($(this).find('h4').text(), false);
        }
        else
        {
            $(this).addClass('on');
            checkboxHandler($(this).find('h4').text(), true);
        }
        arrowChecker($(this));
    });

    // This initially binds the mouseover function to table.results tr
    $('table.results tr').not('.table-information').on('mouseover', function() {
        rowHover($(this));
    });

    // This prevents the click/hover effect from happening when you mouseover the table header
    $('table.results tr').children('th').mouseover(function(e)
    {
        e.stopPropagation();
    });

    // This takes off the hover effect when you move off of the row
    $('div.rowOverlay').mouseleave(function() {
        $(this).hide();
        $("#canitOverlay a.view-in-canit").show();
//        $('table.results tr.tr-hover-state').removeClass('tr-hover-state');
    });

    if($('[name="routerSelect"]').is(':checked')) {
        var params = getRoutersInitialParameters();
        $.ajax
        ({
            type: "POST",
            url: "routers/routersResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                $("tr.waiting-for-results.routers").remove();
                displayMoreRoutersResults(data, "routers");
                $('table.results tr').not('.table-information').on('mouseover', function() {
                    rowHover($(this));
                });
                numResults["routers"] += data.length;

                if (data.length == 30) {
                    var button = $("a.no-more-results.routers");
                    button.text('View More Results');
                    button.removeClass("no-more-results");
                    button.addClass("view-more-results");
                    $("a.view-more-results.routers").on("click", function(){
                        buttonClicked("routers");
                    });
                }
            });
    }

    if($('[name="exchangeSelect"]').is(':checked')) {
        var params = getExchangeInitialParameters();
        $.ajax
        ({
            type: "POST",
            url: "exchange/exchangeResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                $("tr.waiting-for-results.exchange").remove();
                displayMoreExchangeResults(data, "exchange");
                $('table.results tr').not('.table-information').on('mouseover', function() {
                    rowHover($(this));
                });
                numResults["exchange"] += data.length;

                if (data.length == 30) {
                    var button = $("a.no-more-results.exchange");
                    button.text('View More Results');
                    button.removeClass("no-more-results");
                    button.addClass("view-more-results");
                    $("a.view-more-results.exchange").on("click", function(){
                        buttonClicked("exchange");
                    });
                }
            });
    }
});

function arrowChecker(currentBox) {
    var prevArrow = $(currentBox).prev('.server-arrow');
    var nextArrow = $(currentBox).next('.server-arrow');

    if (prevArrow.next().hasClass('on') && prevArrow.prev().hasClass('on'))
    {
        prevArrow.addClass('on');
    }
    else
    {
        prevArrow.removeClass('on');
    }

    if (nextArrow.next().hasClass('on') && nextArrow.prev().hasClass('on'))
    {
        nextArrow.addClass('on');
    }
    else
    {
        nextArrow.removeClass('on');
    }
};

function checkboxHandler(name, onOrOff) {
    $(document).find('input[value="' + name + '"]').prop('checked',onOrOff);
};

function rowExpander(clickedRow)
{
    if(clickedRow.hasClass("exchange")) {
        var messageId = clickedRow[0]['cells'][5].innerHTML;
        var maxResults = 1000;

        var date = clickedRow[0]['cells'][0].innerHTML;
        var time = clickedRow[0]['cells'][1].innerHTML;
        var timestamp = new Date(date + " " + time);
        var utcMilliseconds = timestamp.getTime();

        var sender = clickedRow[0]['cells'][2].innerHTML;

        var subject = clickedRow[0]['cells'][4].innerHTML;

        $.ajax
        ({
            type: "POST",
            url: "exchange/exchangeLogs",
            data: {
                message_id: messageId,
                max_results: maxResults,
                utc_milliseconds: utcMilliseconds,
                sender_address: sender,
                message_subject: subject
            },
            dataType: "json"
        })
            .done(function(data)
            {
                var insertionText = '<tr class="log ' + clickedRow.attr("class") + '"><td colspan="6"><div class="indent">';

                if(data.hasOwnProperty('error')) {
                    insertionText += '<p>error: ' + data['error'] + '</p>';
                    insertionText += '</div></td></tr>';

                    $(insertionText).insertAfter(clickedRow);
                    clickedRow.removeClass('log-opening');
                    $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
                    return;
                }

                insertionText += '<p>';
                for(var rowIndex in data) {
                    var row = data[rowIndex];
                    insertionText += row["date_time"] + ', ';
                    insertionText += 'event: ' + row["event_id"] + ', ';
                    insertionText += 'recipient: ' + row["recipient_address"] + ', ';
                    insertionText += 'client: ' + row["client_hostname"] + ', ';
                    insertionText += 'server: ' + row["server_hostname"];
                    insertionText += '<br/>';
                }
                insertionText += '</p>';
                insertionText += '</div></td></tr>';
                $(insertionText).insertAfter(clickedRow);
                clickedRow.removeClass('log-opening');
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
            })
            .fail(function(data) {
                console.log(document.URL);
                var insertionText = '<tr class="log ' + clickedRow.attr("class") + '"><td colspan="6"><div class="indent"><p>An error occurred</p></td></tr>';
                $(insertionText).insertAfter(clickedRow);
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
            });
    } else if (clickedRow.hasClass("canit")) {

        var queueId = clickedRow[0]['cells'][8].innerHTML;
        var reportingHost = clickedRow[0]['cells'][9].innerHTML;

        $.ajax
        ({
            type: "POST",
            url: "canit/canitLogs",
            data: {queue_id: queueId, reporting_host: reportingHost},
            dataType: "json"
        })
            .done(function(data) {

                var logs = doIndent(data);

                var insertionText = '<tr class="log ' + clickedRow.attr("class") + '"><td colspan="8"><p>' + logs + '</p></td></tr>';

                $(insertionText).insertAfter(clickedRow);
                clickedRow.removeClass('log-opening');
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
            });

    } else if (clickedRow.hasClass("routers")) {
        var message_id = clickedRow[0]['cells'][5].innerHTML;
        var next_id = clickedRow[0]['cells'][6].innerHTML;
        $.ajax
        ({
            type: "POST",
            url: "routers/routersLogs",
            data: {message_id: message_id,
                next_id: next_id},
            dataType: "json"
        })
            .done(function(data)
            {
                var rawLogs = simplifyArray(data);
                var logs = doIndent(rawLogs);

                var insertionText = '<tr class="log ' + clickedRow.attr("class") + '"><td colspan="7">';

                insertionText += '<p>';
                insertionText += logs;
                insertionText += '</p>';
                insertionText += '</td></tr>';
                $(insertionText).insertAfter(clickedRow);
                clickedRow.removeClass('log-opening');
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
            })
            .fail(function(data) {
                console.log(document.URL);
                var insertionText = '<tr class="log ' + clickedRow.attr("class") + '"><td colspan="7"><div class="indent"><p>An error occurred</p></td></tr>';
                $(insertionText).insertAfter(clickedRow);
                clickedRow.removeClass('log-opening');
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
            });
    }
};

function simplifyArray(array) {
    var returnArray = Array();
    for (var i = 0; i < array.length; i++) {
        if (Array.isArray(array[i])) {
           returnArray = returnArray.concat(simplifyArray(array[i]));
        } else {
            var row = array[i]['Routers'];
            var line = row["received_at"] + ', ';
            line += 'MessageID: ' + row["message_id"] + ', ';
            line += 'FromHost: ' + row["from_host"] + ', ';
            line += (row["is_type_from"] == 1 ? 'from: ' : 'to: ') + row["sender_receiver"] + ', ';
            line += 'Relay: ' + row["relay"];
            if (row["is_type_from"] == 0)
            {
                line += ', DSN: ' + row["dsn"] + ", ";
                line += 'Stat: ' + row["stat"];
                if (row["next_id"] != null) {
                    line += ', NextID: ' + row["next_id"];
                }
            }
            returnArray.push(line);
        }
    }
    return returnArray;
}

function doIndent(array) {
    var logs = "";
    var logLines = new Array();
    for (var i = 0; i < array.length; i++) {
        var lines = new Array();
        var lineLength = 100;

        var logDelimited = array[i].split(/[\s;]+/g);
        var line = "";
        for (var j = 0; j < logDelimited.length; j++) {
            var tempLine = line + logDelimited[j];
            if (tempLine.length > lineLength) {
                lines.push(line);
                line = logDelimited[j];
            } else if (tempLine.length <= lineLength && j < logDelimited.length - 1) {
                line = tempLine;
            } else {
                lines.push(tempLine);
            }
        }
        logLines.push(lines);
    }

    for (var i = 0; i < logLines.length; i++) {
        for (var j = 0; j < logLines[i].length; j++) {
            if (j > 0) {
                logs += "&nbsp&nbsp&nbsp&nbsp&nbsp";
            }
            logs += logLines[i][j].replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/,/g, ",&nbsp") + "<br/>";
        }
        if (i < logLines.length - 1) {
            logs += "<br/>";
        }
    }
    return logs;
}

function rowHover(currentHoveredRow)
{

    var overlayID;

    // this determines if it's the canit or non canit overlay to use
    if ($(currentHoveredRow).parents('table').hasClass('canit'))
    {
        overlayID = "#canitOverlay";
    }
    else
    {
        overlayID = "#nonCanitOverlay";
    }

    // alert($(this).next().attr("class"));
    if($(currentHoveredRow).next().hasClass('log'))
    {
        $(overlayID + " a.view-logs").text("Close Log");
    }
    else
    {
        $(overlayID + " a.view-logs").text("View Log");
    }

    if ($(currentHoveredRow).find("td").hasClass("has-incident") == true)
    {
        $(overlayID + " a.view-in-canit").show();

    }
    else
    {
        $(overlayID + " a.view-in-canit").hide();
    }


    // define useful variables
    var $rowOverlay = $(overlayID);
    var rowWidth = $(currentHoveredRow).width() + 2;
    var rowHeight = $(currentHoveredRow).height() + 2;
    var rowPos = $(currentHoveredRow).position();
    var rowTop = rowPos.top - 1;
    var rowLeft = rowPos.left;

    // This defines the overlay position so it's over the <tr>
    $rowOverlay.css({
        display: 'block',
        position: 'absolute',
        top: rowTop,
        left: rowLeft,
        width: rowWidth,
        height: rowHeight
    });

    // Properly define the height of the .external-link-wrap in #divOverlay
    $rowOverlay.children('.external-link-wrap').css({
        height: rowHeight - 2
    });

    // This vertically aligns the <a>s in the #rowOverlay
    $rowOverlay.find('a').css({
        'margin-top': ($rowOverlay.children('.external-link-wrap').height() - $rowOverlay.children('.external-link-wrap').children('a').innerHeight()) / 2
    });

    // This adds the class so you can change the color of the entire row
//    $(currentHoveredRow).addClass('tr-hover-state');

    // unbinds the click function so it doesn't fire tons of log queries
    $(document).find("a.view-logs").off("click");

    // Binds the click function to the "view logs"
    $rowOverlay.find("a.view-logs").on("click", function()
    {
        // Closes the log if it's currently open
        if($(currentHoveredRow).next().hasClass('log'))
        {
            $(currentHoveredRow).next().remove();
            $(this).text("View Log");
        }
        // Opens the log if it's not open
        else
        {
            $(currentHoveredRow).addClass('tr-clicked-state');
            rowExpander(currentHoveredRow);
            $(this).text("Close Log");
        }
    });
};

function buttonClicked(tableClass) {
    // this will do an AJAX method to get data

    var params = getStoredSearchParameters(tableClass);

    if (tableClass == "canit") {
        $.ajax
        ({
            type: "POST",
            url: "canit/canitResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                displayMoreCanitResults(data, tableClass);
                $('table.results tr').not('.table-information').on('mouseover', function() {
                    rowHover($(this));
                });
                numResults[tableClass] += ADDITIONAL_RESULTS_CONST;
            });
    } else if (tableClass == "routers") {
        $.ajax
        ({
            type: "POST",
            url: "routers/routersResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                displayMoreRoutersResults(data, tableClass);
                $('table.results tr').not('.table-information').on('mouseover', function() {
                    rowHover($(this));
                });
                numResults[tableClass] += ADDITIONAL_RESULTS_CONST;
            });
    } else if (tableClass == "exchange") {
        $.ajax
        ({
            type: "POST",
            url: "exchange/exchangeResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                displayMoreExchangeResults(data, tableClass);
                $('table.results tr').not('.table-information').on('mouseover', function() {
                    rowHover($(this));
                });
                numResults[tableClass] += ADDITIONAL_RESULTS_CONST;
            });
    }
}

function displayMoreCanitResults(results, tableClass) {
    var is_even;
    if ($("table.canit tr").last().hasClass('is_even')) {
        is_even = false;
    } else {
        is_even = true;
    }

    var warning_level_spam_score = parseInt($("#warningDiv").text());
    var auto_reject_spam_score = parseInt($("#rejectDiv").text());

    for (var i = 0; i < results.length; i++)
    {
        var r = results[i];
        var dateTime = new Date(r['ts'] * 1000);
        var date = padToTwo(dateTime.getMonth() + 1) + "/" + padToTwo(dateTime.getDate())/* + "/" + dateTime.getFullYear()*/;
        var time = padToTwo(dateTime.getHours()) + ":" + padToTwo(dateTime.getMinutes());
        var inputRow = "<tr class=\"" + (is_even ? "even-row" : "odd-row") + " canit\"'>" +
            "<td class='open-log'>"+date+"</td><td class='open-log'>"+time+"</td><td class='open-log'><span class='canit-sender'>"+
            r['sender']+"</span></td><td class='open-log'><span class='canit-recipients'>";
        for (var j = 0; j < r['recipients'].length; j++) {
            inputRow += r['recipients'][j] + "<br/>";
        }
        inputRow += "</span></td><td class='open-log'><span class='canit-subject'>"+(r['subject'] ? r['subject'] : "")+"</span></td>"+
            "<td class='open-log'>"+(r['stream'] ? r['stream'] : "")+"</td>" +
            "<td class='open-log'>"+(r['what'] ? r['what'] : "")+"</td>";

        var canit_spam_score_string = "";
        var canit_spam_score = r['score'];
        var incident_id = r['incident_id'];

        if (incident_id) {
            canit_spam_score_string = "has-incident ";
        }

        if (!canit_spam_score){ canit_spam_score_string += "spam-score-empty"; }
        else if (canit_spam_score < warning_level_spam_score){ canit_spam_score_string += "spam-score-good"; }
        else if (canit_spam_score < auto_reject_spam_score){ canit_spam_score_string += "spam-score-quarantined"; }
        else { canit_spam_score_string += "spam-score-rejected"; }

        inputRow += "<td><span class=\""+canit_spam_score_string+"\">"+(r['score'] ? r['score'] : "")+"</span></td>";
        inputRow += "<td hidden>" + r['queue_id'] + "</td>";
        inputRow += "<td hidden>" + r['reporting_host'] + "</td>";
        inputRow += "<td hidden>" + r['realm'] + "</td>";
        var incidentIdClass = (r['incident_id'] ? "class='has-incident' " : "");
        inputRow += "<td " + incidentIdClass + "hidden>" + r['incident_id'] + "</td></tr>";
        is_even = !is_even;
        $("table." + tableClass + " tr").last().after(inputRow);
    }

    $("table.results td span.has-incident").off('click');
    $("table.results td span.has-incident").on("click", function(){
        var clickedRow = $(this).parent().parent();

        var realm = clickedRow[0]['cells'][10].innerHTML;
        var id = clickedRow[0]['cells'][11].innerHTML;
        var stream = clickedRow[0]['cells'][5].innerHTML;
        var url = "https://emailfilter.byu.edu/canit/showincident.php?&id=" + id + "&rlm=" + realm + "&s=" + stream;
        window.open(url, '_blank');
    });

    $("table tr.canit td.open-log").off();
    $("table tr.canit td.open-log").on("click", function(){
        openLog($(this).parent());
    });

    if (results.length < 20) {
        var button = $("a.view-more-results.canit");
        button.text('No More Results');
        button.removeClass("view-more-results");
        button.addClass("no-more-results");
    }
}

function displayMoreRoutersResults(results, tableClass) {
    var is_even;
    if ($("table.routers tr").last().hasClass('is_even')) {
        is_even = false;
    } else {
        is_even = true;
    }

    for (var i = 0; i < results.length; i++)
    {
        var r = results[i];
        var date = r['Date'];
        date = date.substr(0, 5);
        var time = r['Time'];
        var inputRow = "<tr class=\"" + (is_even ? "even-row" : "odd-row") + " routers\" onclick='openLog(this)'>" +
            "<td>"+date+"</td><td>"+time+"</td><td><span class='routers-sender'>" +
            r['Sender']+"</span></td><td><span class='routers-recipients'>";
        for (var j = 0; j < r['Recipients'].length; j++) {
            inputRow += r['Recipients'][j] + "<br/>";
        }
        inputRow += "</td><td>"+r['Status']+"</td>";
        inputRow += "</td><td style='display: none'>"+r['Message_ID']+"</td>";
        inputRow += "</td><td style='display: none'>"+r['Next_ID']+"</td></tr>";

        is_even = !is_even;
        $("table." + tableClass + " tr").last().after(inputRow);
    }

    if (results.length < 20) {
        var button = $("a.view-more-results.routers");
        button.text('No More Results');
        button.removeClass("view-more-results");
        button.addClass("no-more-results");
    }
}

function displayMoreExchangeResults(results, tableClass) {
    var is_even;
    if ($("table.exchange tr").last().hasClass('is_even')) {
        is_even = false;
    } else {
        is_even = true;
    }

    for (var i = 0; i < results.length; i++)
    {
        var r = results[i];
        var date = r['Date'];
        date = date.substr(0, 5);
        var time = r['Time'];
        var inputRow = "<tr class=\"" + (is_even ? "even-row" : "odd-row") + " exchange\" onclick='openLog(this)'>" +
            "<td>"+date+"</td><td>"+time+"</td><td><span class='exchange-sender'>" +
            r['Sender']+"</span></td><td><span class='exchange-recipients'>";
        inputRow += r['Recipient'] + "<br/></span>";
        inputRow += "</td><td><span class='exchange-subject'>"+r['Subject']+"</span></td>";
        inputRow += "<td style='display: none'>"+r['ID']+"</td></tr>";

        is_even = !is_even;
        $("table." + tableClass + " tr").last().after(inputRow);
    }

    if (results.length < 20) {
        var button = $("a.view-more-results.exchange");
        button.text('No More Results');
        button.removeClass("view-more-results");
        button.addClass("no-more-results");
    }
}

// Used to make sure all month and day strings have two digits

function padToTwo(number) {
    if (number<=9) { number = ("0"+number).slice(-2); }
    return number;
}

function getStoredSearchParameters(tableClass) {
    var table = document.getElementById("paramsTable");
    var row = table.rows[0];

    var recipient = row.cells[0].innerHTML;
    var recipientContains = row.cells[1].innerHTML;
    var sender = row.cells[2].innerHTML;
    var senderContains = row.cells[3].innerHTML;
    var subject = row.cells[4].innerHTML;
    var subjectContains = row.cells[5].innerHTML;
    var startDttm = row.cells[6].innerHTML;
    var endDttm = row.cells[7].innerHTML;
    var maxResults = 20;
    var offset = numResults[tableClass];
    var results;

    if (tableClass == "routers") {
        results = {recipient: recipient, recipient_contains: recipientContains, sender: sender,
            sender_contains: senderContains, start_date: startDttm, end_date: endDttm,
            max_results: maxResults, offset: offset};
    } else {
        results = {recipient: recipient, recipient_contains: recipientContains, sender: sender,
            sender_contains: senderContains, subject: subject, subject_contains: subjectContains,
            start_date: startDttm, end_date: endDttm, max_results: maxResults, offset: offset};
    }

    return results;
}

function getRoutersInitialParameters() {
    var table = document.getElementById("paramsTable");
    var row = table.rows[0];

    var recipient = row.cells[0].innerHTML;
    var recipientContains = row.cells[1].innerHTML;
    var sender = row.cells[2].innerHTML;
    var senderContains = row.cells[3].innerHTML;
    var startDttm = row.cells[6].innerHTML;
    var endDttm = row.cells[7].innerHTML;
    var maxResults = 30;
    var offset = 0;
    var results;

    results = {recipient: recipient, recipient_contains: recipientContains, sender: sender,
        sender_contains: senderContains, start_date: startDttm, end_date: endDttm,
        max_results: maxResults, offset: offset};
    return results;
}

function getExchangeInitialParameters() {
    var table = document.getElementById("paramsTable");
    var row = table.rows[0];

    var recipient = row.cells[0].innerHTML;
    var recipientContains = row.cells[1].innerHTML;
    var sender = row.cells[2].innerHTML;
    var senderContains = row.cells[3].innerHTML;
    var subject = row.cells[4].innerHTML;
    var subjectContains = row.cells[5].innerHTML;
    var startDttm = row.cells[6].innerHTML;
    var endDttm = row.cells[7].innerHTML;
    var maxResults = 30;
    var offset = 0;
    var results;

    results = {recipient: recipient, recipient_contains: recipientContains, sender: sender,
        sender_contains: senderContains, subject: subject, subject_contains: subjectContains,
        start_date: startDttm, end_date: endDttm, max_results: maxResults, offset: offset};
    return results;
}

function openLog(row) {
    if($(row).next().hasClass('log'))
    {
        $(row).next().remove();
    }
    // Opens the log if it's not open
    else if (!$(row).hasClass('log-opening'))
    {
        $(row).addClass('log-opening');
        rowExpander($(row));
    }
}
