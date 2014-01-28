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

function rowExpander(rowClass)
{
	var boxText = "Message id: 1 Filename: /opt/mail/quarantine/20140121/spam/qfs0LBT80R022330<br/> Transport layer information:<br/> -----------------------------------------------------------------------<br/> Sending host: mta.newsletter.rakuten.com [198.245.80.132]<br/> Envelope From: address: bounce-2883_HTML-12992025-106607-6196309-1814@bounce.newsletter.rakuten.com<br/> Final recipient address: brandonbrasmussen@gmail.com<br/> Envelope To: addresses: brasmussen@byu.edu<br/> Message header:<br/> ----------------------------------------------------------------------<br/> Received: from mta.newsletter.rakuten.com (mta.newsletter.rakuten.com [198.245.80.132])<br/> by gridley.byu.edu (8.14.3/8.13.8) with ESMTP id s0LBT80R022330<br/> Subject: 10% Back on TV & Home Theater! Samsung LED HDTV $497.99, Harman Kardon Receiver $369.99 + More!<br/> Date: Tue, 21 Jan 2014 05:29:07 -0600<br/> MIME-Version: 1.0<br/>";
	var insertionText = '<tr class="log"><td colspan="7"><p>' + boxText + '</p></td></tr>';

	var insertionText = '<tr class="log"><td colspan="7"><p>hello!</p></td></tr>';
	// var insertOnce = true;

		$(insertionText).insertAfter('tr.' + rowClass + '');

}

$(document).ready(function() {

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

	// Put today's date as the default dates for Start & End
	$("#datepickerStart").val($.datepicker.formatDate('mm/dd/yy', new Date()));
	$("#datepickerEnd").val($.datepicker.formatDate('mm/dd/yy', new Date()));

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
        var $rowOverlay = $('#rowOverlay');
        var currentTable = $(this).parents('table');
    	var rowIndex = $(this).index();
    	var currentRow = $(this);

        var rowWidth = $(this).width() + 2;
        var rowHeight = $(this).height() + 2;
        var rowPos = $(this).position();
       	var rowTop = rowPos.top - 1;
        var rowLeft = rowPos.left;
        $rowOverlay.css({
        	display: 'block',
            position: 'absolute',
            top: rowTop,
            left: rowLeft,
            width: rowWidth,
            height: rowHeight
        });
        $rowOverlay.children('.external-link-wrap').css({
        	height: rowHeight - 2
        });

        var externalLinkWrapHeight = $rowOverlay.children('.external-link-wrap').height();
        var externalLinkWrapAnchorHeight = $rowOverlay.children('.external-link-wrap').children('a').innerHeight();
        var anchorTagMarginTop = (externalLinkWrapHeight - externalLinkWrapAnchorHeight) / 2;

        // alert(externalLinkWrapHeight + " " + externalLinkWrapAnchorHeight + " " + anchorTagMarginTop);
        // alert(alertString);

        $rowOverlay.children('.external-link-wrap').children('a').css({
        	'margin-top': anchorTagMarginTop
        });

        // This adds the class so you can change the color of the entire row	
        $(this).addClass('tr-hover-state');

    	// unbinds the click function so it doesn't fire on multiple rows
    	$("a#viewLogs").off("click");    	

		// Binds the click function to the "view logs"    	
	    $("a#viewLogs").on("click", function()
	    {
	    	// Closes the log if it's currently open
	    	if($(currentRow).next().hasClass('log'))
	    	{
	    		$(currentRow).next().remove();
	 		}
	 		// Opens the log if it's not open
	 		else
	 		{
	    		rowExpander('tr-hover-state');
	    	}
	    });

    });

	// This prevents the click/hover effect from happening when you mouseover the table header
	$('table.results tr').children('th').mouseover(function(e)
	{
		e.stopPropagation();
	});

	// This takes off the hover effect when you move off of the row
    $('#rowOverlay').mouseleave(function() {
        $('#rowOverlay').hide();
        $('table.results tr.tr-hover-state').removeClass('tr-hover-state');
    });


});
