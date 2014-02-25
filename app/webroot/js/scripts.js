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

function rowExpander(currentHoveredRow)
{
    if(currentHoveredRow.hasClass("exchange")) {
        var date = currentHoveredRow[0]['cells'][0].innerHTML;
        var time = currentHoveredRow[0]['cells'][1].innerHTML;
        
        var timestamp = new Date(date + " " + time);
        
        var internalMessageId = currentHoveredRow[0]['cells'][5].innerHTML;
        var maxResults = 1000;
        
        $.ajax
	({
            type: "GET",
            url: "exchange/getAdditionalLogs",
            data: {
                internal_message_id: internalMessageId,
                max_results: maxResults,
                utc_milliseconds: timestamp.getTime()
            },
            dataType: "json"
	})
	.done(function(data)
	{
            var insertionText = '<tr class="log ' + currentHoveredRow.attr("class") + '"><td colspan="7"><div class="indent">';
            
            if(data.hasOwnProperty('error')) {
                insertionText += '<p>error: ' + data['error'] + '</p>';
                insertionText += '</td></tr>';
                
                $(insertionText).insertAfter('tr.tr-clicked-state');
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
                return;
            }
            
            

            for(var rowIndex in data) {
                var row = data[rowIndex];
                insertionText += '<div class="indentLine">';
                insertionText += row["date_time"] + ', ';
                insertionText += row["event_id"] + ', ';
                insertionText += row["recipient_address"] + ', ';
                insertionText += 'client: ' + row["client_hostname"] + ', ';
                insertionText += 'server: ' + row["server_hostname"];
                insertionText += '</div>';
            }
            insertionText += '</div></td></tr>';
            $(insertionText).insertAfter('tr.tr-clicked-state');
            $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
	})
        .fail(function(data) {
            console.log(document.URL);
            var insertionText = '<tr class="log ' + currentHoveredRow.attr("class") + '"><td colspan="7"><div class="indent"><p>An error occurred</p></td></tr>';
            $(insertionText).insertAfter('tr.tr-clicked-state');
            $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
        });
    } else if (currentHoveredRow.hasClass("canit")) {
        var queueId = currentHoveredRow[0]['cells'][7].innerHTML;
        var reportingHost = currentHoveredRow[0]['cells'][8].innerHTML;

        $.ajax
        ({
            type: "POST",
            url: "Search/canitlogs",
            data: {queue_id: queueId, reporting_host: reportingHost},
            dataType: "json"
        })
            .done(function(data) {
                var logs = "";

                /*for (var i = 0; i < data.length; i++) {
                    logs += "<div class='indentLine'>" + data[i] + "</div>";
                }

                var insertionText = '<tr class="log ' + currentHoveredRow.attr("class") + '"><td colspan="7"><div class="indent">' + logs + '</div></td></tr>';*/

                var logLines = new Array();
                for (var i = 0; i < data.length; i++) {
                    var lines = new Array();
                    var lineLength = 100;

                    var logDelimited = data[i].split(/[\s;]+/g);
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
                var insertionText = '<tr class="log ' + currentHoveredRow.attr("class") + '"><td colspan="7"><p>' + logs + '</p></td></tr>';

                $(insertionText).insertAfter('tr.tr-clicked-state');
                $('table.results tr.tr-clicked-state').removeClass('tr-clicked-state');
            });

    } else {
	$.ajax
	({
            type: "GET",
            url: "../ajaxtest.php",
            data: {ID: "5"}
	})
	.done(function(data)
	{
            var insertionText = '<tr class="log ' + currentHoveredRow.attr("class") + '"><td colspan="7"><p>' + data + '</p></td></tr>';
            $(insertionText).insertAfter('tr.tr-hover-state');
	});
    }
};

function rowHover(currentHoveredRow, rowOverlayChoice, currentRowClass)
{
        // define useful variables
        var $rowOverlay = $(rowOverlayChoice);
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
        $(currentHoveredRow).addClass('tr-hover-state');

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

        $(document).find("a.view-in-canit").off("click");

        $rowOverlay.find("a.view-in-canit").on("click", function()
        {
            var rlm = currentHoveredRow[0]['cells'][9].innerHTML;
            var id = currentHoveredRow[0]['cells'][10].innerHTML;
            var stream = currentHoveredRow[0]['cells'][11].innerHTML;
            var url = "https://emailfilter.byu.edu/canit/showincident.php?&id=" + id + "&rlm=" + rlm + "&s=" + stream;
            window.open(url, '_blank');
        });
};

$(document).ready(function(realm, stream) {

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

	
    $('table.results tr').not('.table-information').mouseover(function() {
    	// alert($(this).next().attr("class"));
    	if($(this).next().hasClass('log'))
    	{
    		$("#canitOverlay a.view-logs").text("Close Log");
    	}
    	else
    	{
    		$("#canitOverlay a.view-logs").text("View Log");
    	}
    	var overlayIDtoPass = "#nonCanitOverlay";

        // this determines if it's the canit or non canit overlay to use
    	if ($(this).parents('table').hasClass('canit'))
    	{
    		overlayIDtoPass = "#canitOverlay";
    	}

        // This hides the "open in canit" if it's either red, green, or empty
        if ($(this).find("span").hasClass("spam-score-quarantined") == true)
        {
            $("#canitOverlay a.view-in-canit").show();

        }
        else
        {
            $("#canitOverlay a.view-in-canit").hide();
        }

    	rowHover($(this), overlayIDtoPass, $(this).attr('class'));

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
        $('table.results tr.tr-hover-state').removeClass('tr-hover-state');
    });


});
