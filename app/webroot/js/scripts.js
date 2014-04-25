var TOOLTIP_DELAY = 350;

/**
 * The number of rows to return when the user clicks the 'Search' button.
 * @type {number}
 */
var NUM_INIT_RESULTS = 30;

/**
 * The number of rows to return when the user clicks any of the three 'View More Results' buttons.
 * @type {number}
 */
var NUM_MORE_RESULTS = 20;

/**
 * Strings used as constants throughout the file.
 * @type {string}
 */

var CANIT_CLASS = "canit";
var ROUTERS_CLASS = "routers";
var EXCHANGE_CLASS = "exchange";

var SENDER_COL = "sender";
var RECIPIENTS_COL = "recipients";
var SUBJECT_COL = "subject";

var TOOLTIP = "tooltip";

/**
 * Array containing the number of rows in the CanIt, Routers, and Exchange results table.
 */
var numResults;

/**
 * Called on page load.
 */
$(document).ready(function(realm, stream) {

    // Initialize the numResults array
    numResults = {'canit': 0, 'routers': 0, 'exchange': 0};

    $('#tabs').tabs();

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

    /*
     * If the CanIt box is selected, call CanitController's canitResults method via AJAX and return an
     * array to be displayed. Because this box is automatically selected on page load, searchParamsSet
     * is called in order to prevent the page from making an API call on empty fields.
     */
    if($('[name="canitSelect"]').is(':checked') && searchParamsSet()) {
        var params = getSearchParams(NUM_INIT_RESULTS, numResults[CANIT_CLASS]);

        var spinnerDiv =
            "<tr>"+
                "<td class='animation' colspan='8'>"+
                    "<div class='animation'>" +
                        "<div class='outer-circle'></div>" +
                        "<div class='inner-circle'></div>" +
                    "</div>"+
                "</td>"+
            "</tr>";

        $("table.canit").append(spinnerDiv);

        $.ajax
        ({
            type: "POST",
            url: "canit/canitResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                if (Object.prototype.toString.call(data) === '[object Array]') {
                    displayMoreCanItResults(data, params["max_results"]);
                    numResults[CANIT_CLASS] += data.length;
                } else {
                    alert("Error");
                }
            });
    }

    /*
     * If the Routers box is selected, call RoutersController's routersResults method via AJAX and return an
     * array to be displayed. This box is automatically deselected on page load.
     */
    if($('[name="routerSelect"]').is(':checked')) {
        var params = getSearchParams(NUM_INIT_RESULTS, numResults[ROUTERS_CLASS]);

        var spinnerDiv =
            "<tr>"+
                "<td class='animation' colspan='5'>"+
                    "<div class='animation'>" +
                        "<div class='outer-circle'></div>" +
                        "<div class='inner-circle'></div>" +
                    "</div>"+
                "</td>"+
            "</tr>";

        $("table.routers").append(spinnerDiv);

        $.ajax
        ({
            type: "POST",
            url: "routers/routersResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                displayMoreRoutersResults(data, params["max_results"]);
                numResults[ROUTERS_CLASS] += data.length;
            });
    }

    /*
     * If the Routers box is selected, call RoutersController's routersResults method via AJAX and return an
     * array to be displayed. This box is automatically deselected on page load.
     */
    if($('[name="exchangeSelect"]').is(':checked')) {
        var params = getSearchParams(NUM_INIT_RESULTS, numResults[EXCHANGE_CLASS]);

        var spinnerDiv =
            "<tr>"+
                "<td class='animation' colspan='5'>"+
                    "<div class='animation'>" +
                        "<div class='outer-circle'></div>" +
                        "<div class='inner-circle'></div>" +
                    "</div>"+
                "</td>"+
            "</tr>";

        $("table.exchange").append(spinnerDiv);

        $.ajax
        ({
            type: "POST",
            url: "exchange/exchangeResults",
            data: params,
            dataType: "json"
        })
            .done(function(data)
            {
                displayMoreExchangeResults(data, params["max_results"]);
                numResults[EXCHANGE_CLASS] += data.length;
            });
    }
});

/*
 * Checks to see if a search has been made. When the 'Search' button is clicked, the search parameters are stored
 * in a hidden table on the page.  If this table exists, return true; if the table does not exist, return false.
 */
function searchParamsSet() {
    if (document.getElementById("paramsTable") != null) {
        return true;
    } else {
        return false;
    }
}

/*
 * Retrieves search parameters from a hidden table on the page.  When the 'Search' button is clicked, the search
 * parameters are stored in a hidden table on the page.  These search parameters are returned in the form of an
 * array to the function from which they were called and passed directly into the 'data' field of an AJAX call.
 */
function getSearchParams(maxResults, offset) {
    var table = document.getElementById("paramsTable");
    var results;

    if (table != null) {
        var row = table.rows[0];

        var recipient = row.cells[0].innerHTML;
        var sender = row.cells[1].innerHTML;
        var subject = row.cells[2].innerHTML;
        var startDttm = row.cells[3].innerHTML;
        var endDttm = row.cells[4].innerHTML;

        results = {recipient: recipient, sender: sender, subject: subject, start_date: startDttm,
            end_date: endDttm, max_results: maxResults, offset: offset};
    }

    return results;
}

/*
 * Turn the arrow blue if the boxes on both sides are selected.  Otherwise, turn it gray.
 */
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

/*
 * Select or deselect a checkbox if its corresponding box is clicked.
 */
function checkboxHandler(name, onOrOff) {
    $(document).find('input[value="' + name + '"]').prop('checked',onOrOff);
};

/*
 * If the 'View More Results' button is clicked at the bottom of the CanIt table, perform the search again and
 * return additional results, appending them to the end of the CanIt Results table.
 */
function buttonClickedCanIt() {
    var params = getSearchParams(NUM_MORE_RESULTS, numResults[CANIT_CLASS]);

    var button = $("a.results.view-more.canit");
    button.removeClass("view-more");
    button.addClass("loading-more");
    button.text("Loading Results");

    var spinnerDiv =
        "<tr>"+
            "<td class='animation' colspan='8'>"+
                "<div class='animation'>" +
                    "<div class='outer-circle'></div>" +
                    "<div class='inner-circle'></div>" +
                "</div>"+
            "</td>"+
        "</tr>";

    $("table.canit").append(spinnerDiv);

    $.ajax
    ({
        type: "POST",
        url: "canit/canitResults",
        data: params,
        dataType: "json"
    })
        .done(function(data)
        {
            displayMoreCanItResults(data, params["max_results"]);
            numResults[CANIT_CLASS] += data.length;
        });
}

/*
 * If the 'View More Results' button is clicked at the bottom of the Routers table, perform the search again and
 * return additional results, appending them to the end of the Routers Results table.
 */
function buttonClickedRouters() {
    var params = getSearchParams(NUM_MORE_RESULTS, numResults[ROUTERS_CLASS]);

    var button = $("a.results.view-more.routers");
    button.removeClass("view-more");
    button.addClass("loading-more");
    button.text("Loading Results");

    var spinnerDiv =
        "<tr>"+
            "<td class='animation' colspan='5'>"+
                "<div class='animation'>" +
                    "<div class='outer-circle'></div>" +
                    "<div class='inner-circle'></div>" +
                "</div>"+
            "</td>"+
        "</tr>";

    $("table.routers").append(spinnerDiv);

    $.ajax
    ({
        type: "POST",
        url: "routers/routersResults",
        data: params,
        dataType: "json"
    })
        .done(function(data)
        {
            displayMoreRoutersResults(data, params["max_results"]);
            numResults[ROUTERS_CLASS] += data.length;
        });
}

/*
 * If the 'View More Results' button is clicked at the bottom of the Exchange table, perform the search again and
 * return additional results, appending them to the end of the Exchange Results table.
 */
function buttonClickedExchange() {
    var params = getSearchParams(NUM_MORE_RESULTS, numResults[EXCHANGE_CLASS]);

    var button = $("a.results.view-more.exchange");
    button.removeClass("view-more");
    button.addClass("loading-more");
    button.text("Loading Results");

    var spinnerDiv =
        "<tr>"+
            "<td class='animation' colspan='5'>"+
                "<div class='animation'>" +
                    "<div class='outer-circle'></div>" +
                    "<div class='inner-circle'></div>" +
                "</div>"+
            "</td>"+
        "</tr>";

    $("table.exchange").append(spinnerDiv);

    $.ajax
    ({
        type: "POST",
        url: "exchange/exchangeResults",
        data: params,
        dataType: "json"
    })
        .done(function(data)
        {
            displayMoreExchangeResults(data, params["max_results"]);
            numResults[EXCHANGE_CLASS] += data.length;
        });
}

/*
 * When a row from a table is clicked, open an additional-information log underneath the clicked row.
 * If any text is selected, the additional information will not be displayed in order to prevent the rows
 * from responding to drag events in which a user drags the mouse to select text.  If the log line is closed,
 * call rowExpander.  If the log line is open, remove the div containing additional information to "close" it.
 */
function openLog(row) {
    var selection = window.getSelection();
    if (selection == 0) {
        if($(row).next().hasClass('log')) {
            $(row).next().remove();
        } else if (!$(row).hasClass('log-opening')) {
            $(row).addClass('log-opening');
            rowExpander($(row));
        }
    }
}

/*
 * Determines which table the clicked row was from and calls a handler method for the particular class'
 * additional-information log lines.
 */
function rowExpander(clickedRow) {
    if (clickedRow.hasClass(CANIT_CLASS)) {
        rowExpanderCanIt(clickedRow);
    } else if (clickedRow.hasClass(ROUTERS_CLASS)) {
        rowExpanderRouters(clickedRow);
    } else if (clickedRow.hasClass(EXCHANGE_CLASS)) {
        rowExpanderExchange(clickedRow);
    }
}

/*
 * Gets additional log information for the clicked row by passing the queueId and reportingHost from hidden
 * columns in the row into CanitController's getLogs method via AJAX.  Inserts the additional log info into a
 * div directly below the clicked row, effectively "opening" the logs.
 */
function rowExpanderCanIt(clickedRow) {
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

            var logs = indentWrappedLogLines(data);

            var insertionText = '<tr class="log ' + clickedRow.attr("class") + '"><td colspan="8"><p>' + logs + '</p></td></tr>';

            $(insertionText).insertAfter(clickedRow);
            clickedRow.removeClass('log-opening');
            $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
        });
}

/*
 * Gets additional log information for the clicked row by passing the message_id and next_id from hidden
 * columns in the row into RoutersController's getLogs method via AJAX.  Inserts the additional log info into a
 * div directly below the clicked row, effectively "opening" the logs.
 */
function rowExpanderRouters(clickedRow) {
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
            var rawLogs = flattenNestedLogArrays(data);
            var logs = indentWrappedLogLines(rawLogs);

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

/*
 * Gets additional log information for the clicked row by passing the messageId, maxResults, utcMilliseconds,
 * sender, and subject from hidden and unhidden columns in the row into ExchangeController's getLogs method via AJAX.
 * Inserts the additional log info into a div directly below the clicked row, effectively "opening" the logs.
 */
function rowExpanderExchange(clickedRow) {
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
}

/*
 * When the RoutersController returns an array of data, it is not formatted in a way that is conducive to displaying
 * in the tables, because it may contain nested arrays with various levels of nesting.  In order to facilitate the
 * reading and displaying of this data, this method recursively traverses the nested arrays and returns an array
 * with only two levels of nesting: the first level is the container array, and the second level separates each row
 * in the table into arrays of related information.
 *
 * This method is specific to Routers data.
 */
function flattenNestedLogArrays(array) {
    var returnArray = Array();
    for (var i = 0; i < array.length; i++) {
        if (Array.isArray(array[i])) {
           returnArray = returnArray.concat(flattenNestedLogArrays(array[i]));
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

/*
 *
 */
function indentWrappedLogLines(array) {
    var logs = "";
    var logLines = new Array();
    for (var i = 0; i < array.length; i++) {
        var lines = new Array();
        var lineLength = 100;

        var logDelimited = array[i].split(/[\s;,]/g);
        var line = "";
        for (var j = 0; j < logDelimited.length; j++) {
            var tempLine = line + " " + logDelimited[j];
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

function displayMoreCanItResults(results, expectedNumResults) {
    var is_even;

    $("table.canit.results tr td.animation").parent().remove();

    if ($("table.canit tr").last().hasClass('is_even')) {
        is_even = false;
    } else {
        is_even = true;
    }

    var warning_level_spam_score = parseInt($("#warningDiv").text());
    var auto_reject_spam_score = parseInt($("#rejectDiv").text());

    var rowNumber = numResults[CANIT_CLASS] + 1;
    for (var i = 0; i < results.length; i++)
    {
        var r = results[i];

        var even_odd_class = (is_even ? "even-row" : "odd-row");
        var dateTime = new Date(r['ts'] * 1000);
        var date = padToTwo(dateTime.getMonth() + 1) + "/" + padToTwo(dateTime.getDate());
        var time = padToTwo(dateTime.getHours()) + ":" + padToTwo(dateTime.getMinutes());
        var sender = (r['sender'] ? r['sender'] : "");
        var senderToShow = sender;
        var recipients = r['recipients']
        var recipientToShow = findMatchingRecipient(recipients);
        var recipientsFormatted = printAddressesArray(recipients);
        var subject = (r['subject'] ? r['subject'] : "");
        var subjectToShow = subject;
        var stream = (r['stream'] ? r['stream'] : "");
        var what = (r['what'] ? r['what'] : "");
        var score = formatSpamScore(r['score']);
        var incidentId = r['incident_id'];
        var queueId = r['queue_id'];
        var reportingHost = r['reporting_host'];
        var realm = r['realm'];

        var senderOverflows = false;
        if (sender.length > 17) {
            senderToShow = senderToShow.substr(0, 17) + "...";
            senderOverflows = true;
        }

        var recipientOverflows = false;
        if (recipientToShow.length > 17) {
            recipientToShow = recipientToShow.substr(0, 17) + "...";
            recipientOverflows = true;
        } else if (recipients.length > 1) {
            recipientOverflows = true;
        }

        var subjectOverflows = false;
        if (subject.length > 17) {
            subjectToShow = subjectToShow.substr(0, 17) + "...";
            subjectOverflows = true;
        }

        var senderClass = "canit-sender tooltip" + rowNumber;
        var recipientClass = "canit-recipients tooltip" + rowNumber;
        var subjectClass = "canit-subject tooltip" + rowNumber;
        var scoreClass = getCanItScoreClass(incidentId, score, warning_level_spam_score, auto_reject_spam_score);
        var incidentIdClass = (r['incident_id'] ? "has-incident " : "");

        var inputRow =
            "<tr class='" + even_odd_class + " canit'>" +
                "<td class='open-log'>"+date+"</td>" +
                "<td class='open-log'>"+time+"</td>" +
                "<td class='open-log'>" +
                    "<span id='canitSender"+rowNumber+"' title class='"+senderClass+"'>"+senderToShow+"</span>" +
                "</td>" +
                "<td class='open-log'>" +
                    "<span id='canitRecipients"+rowNumber+"' title class='"+recipientClass+"'>"+recipientToShow+"</span>" +
                "</td>" +
                "<td class='open-log'>" +
                    "<span id='canitSubject"+rowNumber+"' title class='"+subjectClass+"'>"+subjectToShow+"</span>" +
                "</td>"+
                "<td class='open-log'>"+stream+"</td>" +
                "<td class='open-log'>"+what+"</td>" +
                "<td>" +
                    "<span class='"+scoreClass+"'>"+score+"</span>" +
                "</td>" +
                "<td class='hidden'>"+queueId+"</td>" +
                "<td class='hidden'>"+reportingHost+"</td>" +
                "<td class='hidden'>"+realm+"</td>" +
                "<td class='"+incidentIdClass+"hidden'>"+incidentId+"</td>" +
            "</tr>";

        $("table.canit tr").last().after(inputRow);

        if (senderOverflows) {
            addTooltip(CANIT_CLASS, rowNumber, sender, SENDER_COL, TOOLTIP_DELAY);
        }
        if (recipientOverflows) {
            addTooltip(CANIT_CLASS, rowNumber, recipientsFormatted, RECIPIENTS_COL, TOOLTIP_DELAY);
        }
        if (subjectOverflows) {
            addTooltip(CANIT_CLASS, rowNumber, subject, SUBJECT_COL, TOOLTIP_DELAY);
        }

        is_even = !is_even;
        rowNumber++;
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
    var clicked = true;
    $("table tr.canit td.open-log").on("click", function(){
        openLog($(this).parent());
    });

    // Set appropriate button
    var button = $("a.results.loading-more.canit");
    button.removeClass("loading-more");

    $("a.results.canit").off();
    if (results.length == expectedNumResults) {
        button.text('View More Results');
        button.addClass("view-more");
        $("a.results.view-more.canit").on("click", function(){
            buttonClickedCanIt();
        });
    } else {
        button.text('No More Results');
        button.addClass("no-more");
    }
}

function displayMoreRoutersResults(results, expectedNumResults) {

    $("table.routers.results tr td.animation").parent().remove();

    var is_even;
    if ($("table.routers tr").last().hasClass('is_even')) {
        is_even = false;
    } else {
        is_even = true;
    }

    var rowNumber = numResults[ROUTERS_CLASS] + 1;
    for (var i = 0; i < results.length; i++)
    {
        var r = results[i];

        var date = r['Date'].substr(0, 5);
        var time = r['Time'];
        var even_odd_class = (is_even ? "even-row" : "odd-row");
        var sender = r['Sender'];
        var senderToShow = sender;
        var recipients = r['Recipients'];
        var recipientToShow = findMatchingRecipient(recipients);
        var recipientsFormatted = printAddressesArray(recipients);
        var status = r['Status'];
        var messageId = r['Message_ID'];
        var nextId = r['Next_ID'];

        var senderOverflows = false;
        if (sender.length > 27) {
            senderToShow = senderToShow.substr(0, 27) + "...";
            senderOverflows = true;
        }

        var recipientOverflows = false;
        if (recipientToShow.length > 27) {
            recipientToShow = recipientToShow.substr(0, 27) + "...";
            recipientOverflows = true;
        } else if (recipients.length > 1) {
            recipientOverflows = true;
        }

        var senderClass = "routers-sender tooltip" + rowNumber;
        var recipientClass = "routers-recipients tooltip" + rowNumber;

        var inputRow =
            "<tr class='" + even_odd_class + " routers'>" +
                "<td>"+date+"</td>" +
                "<td>"+time+"</td>" +
                "<td>" +
                    "<span id='routersSender"+rowNumber+"' title class='"+senderClass+"'>"+senderToShow+"</span>" +
                "</td>" +
                "<td>" +
                    "<span id='routersRecipients"+rowNumber+"' title class='"+recipientClass+"'>"+recipientToShow+"</span>" +
                "</td>" +
                "<td class='status-col'>"+status+"</td>" +
                "<td class='hidden'>"+messageId+"</td>"+
                "<td class='hidden'>"+nextId+"</td>"+
            "</tr>";

        $("table.routers tr").last().after(inputRow);

        if (senderOverflows) {
            addTooltip(ROUTERS_CLASS, rowNumber, sender, SENDER_COL, TOOLTIP_DELAY);
        }
        if (recipientOverflows) {
            addTooltip(ROUTERS_CLASS, rowNumber, recipientsFormatted, RECIPIENTS_COL, TOOLTIP_DELAY);
        }

        is_even = !is_even;
        rowNumber++;
    }

    $("table tr.routers").off();
    $("table tr.routers").on("click", function(){
        openLog($(this));
    });

    // Set appropriate button
    var button = $("a.results.loading-more.routers");
    button.removeClass("loading-more");

    $("a.results.routers").off();
    if (results.length == expectedNumResults) {
        button.text('View More Results');
        button.addClass("view-more");
        $("a.results.view-more.routers").on("click", function(){
            buttonClickedRouters();
        });
    } else {
        button.text('No More Results');
        button.addClass("no-more");
    }
}

function displayMoreExchangeResults(results, expectedNumResults) {

    $("table.exchange.results tr td.animation").parent().remove();

    var is_even;
    if ($("table.exchange tr").last().hasClass('is_even')) {
        is_even = false;
    } else {
        is_even = true;
    }

    var rowNumber = numResults[EXCHANGE_CLASS] + 1;
    for (var i = 0; i < results.length; i++)
    {
        var r = results[i];

        var date = r['Date'].substr(0, 5);
        var time = r['Time'].substr(0, 5);
        var even_odd_class = (is_even ? "even-row" : "odd-row");
        var sender = r['Sender'];
        var senderToShow = sender;
        var recipient = r['Recipient'];
        var recipientToShow = recipient;
        var subject = r['Subject'];
        var subjectToShow = subject;
        var id = r['ID'];

        var senderOverflows = false;
        if (sender && sender.length > 22) {
            senderToShow = senderToShow.substr(0, 22) + "...";
            senderOverflows = true;
        }

        var recipientOverflows = false;
        if (recipient && recipient.length > 22) {
            recipientToShow = recipientToShow.substr(0, 22) + "...";
            recipientOverflows = true;
        }

        var subjectOverflows = false;
        if (subject && subject.length > 22) {
            subjectToShow = subjectToShow.substr(0, 22) + "...";
            subjectOverflows = true;
        }

        var senderClass = "exchange-sender tooltip" + rowNumber;
        var recipientClass = "exchange-recipients tooltip" + rowNumber;
        var subjectClass = "exchange-subject tooltip" + rowNumber;

        var inputRow =
            "<tr class='" + even_odd_class + " exchange'>"+
                "<td>"+date+"</td>"+
                "<td>"+time+"</td>"+
                "<td>"+
                    "<span id='exchangeSender"+rowNumber+"' title class='"+senderClass+"'>"+senderToShow+"</span>"+
                "</td>"+
                "<td>"+
                    "<span id='exchangeRecipient"+rowNumber+"' title class='"+recipientClass+"'>"+recipientToShow+"<br/></span>"+
                "</td>"+
                "<td>"+
                    "<span id='exchangeSubject"+rowNumber+"' title class='"+subjectClass+"'>"+subjectToShow+"</span>"+
                "</td>"+
                "<td class='hidden'>"+id+"</td>"+
            "</tr>";

        $("table.exchange tr").last().after(inputRow);

        if (senderOverflows) {
            addTooltip(EXCHANGE_CLASS, rowNumber, sender, SENDER_COL, TOOLTIP_DELAY);
        }
        if (recipientOverflows) {
            addTooltip(EXCHANGE_CLASS, rowNumber, recipient, RECIPIENTS_COL, TOOLTIP_DELAY);
        }
        if (subjectOverflows) {
            addTooltip(EXCHANGE_CLASS, rowNumber, subject, SUBJECT_COL, TOOLTIP_DELAY);
        }

        is_even = !is_even;
        rowNumber++;
    }

    $("table tr.exchange").off();
    $("table tr.exchange").on("click", function(){
        openLog($(this));
    });

    // Set appropriate button
    var button = $("a.results.loading-more.exchange");
    button.removeClass("loading-more");

    $("a.results.exchange").off();
    if (results.length == expectedNumResults) {
        button.text('View More Results');
        button.addClass("view-more");
        $("a.results.view-more.exchange").on("click", function(){
            buttonClickedExchange();
        });
    } else {
        button.text('No More Results');
        button.addClass("no-more");
    }
}

function printAddressesArray(addresses) {
    var size = addresses.length;
    var numRows = 10;
    var numColumns = Math.floor(size / numRows) + Math.ceil((size % numRows) / numRows);

    var index = 0;
    var addressString = "<table class='tooltip-table'>";
    for (var i = 0; i < numRows; i++) {
        if (index < size) {
            addressString += "<tr>";
            for (var j = 0; j < numColumns; j++) {
                if (index < size) {
                    addressString += "<td>"+addresses[index++]+"</td>";
                } else {
                    addressString += "<td></td>";
                }
            }
            addressString += "</tr>";
        }
    }
    addressString += "</table>";

    return addressString;
}

function findMatchingRecipient(recipients) {
    var table = document.getElementById("paramsTable");
    var row = table.rows[0];
    var recipient = row.cells[0].innerHTML;

    var selectedRecipient = "";

    if (recipients.length == 1) {
        selectedRecipient = recipients[0];
    } else {
        for (var i = 0; i < recipients.length; i++) {
            if (recipients[i].indexOf(recipient) != -1) {
                selectedRecipient = recipients[i] + "...";
                break;
            }
        }
    }
    return selectedRecipient;
}

function addTooltip(tableClass, rowNumber, data, colType, delay) {
    $("table."+tableClass+" tr td span."+tableClass+"-"+colType+".tooltip" + rowNumber).tooltip({
        content: data,
        track: true,
        tooltipClass: colType+"-"+TOOLTIP,
        show: {
            delay: delay
        }
    });
}

function padToTwo(number) {
    if (number <= 9) {
        number = ("0" + number).slice(-2);
    }
    return number;
}

function formatSpamScore(number) {
    var score;
    if (number == null) {
        score = "";
    } else if (!isNaN(number)) {
        score = parseFloat(number).toFixed(2);
    }
    return score;
}

function getCanItScoreClass(incidentId, score, warning_level_spam_score, auto_reject_spam_score) {
    var scoreClass = (incidentId ? "has-incident" : "");
    if (!score) {
        scoreClass += " spam-score-empty";
    } else if (score < warning_level_spam_score) {
        scoreClass += " spam-score-good";
    } else if (score < auto_reject_spam_score) {
        scoreClass += " spam-score-quarantined";
    } else {
        scoreClass += " spam-score-rejected";
    }
    return scoreClass;
}
