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

function rowExpander(currentRowClass)
{
	$.ajax
	({
		type: "GET",
	  	url: "../ajaxtest.php",
	  	data: {ID: "5"}
	})
	.done(function(data)
	{
		var insertionText = '<tr class="log ' + currentRowClass + '"><td colspan="7"><p>' + data + '</p></td></tr>';
		$(insertionText).insertAfter('tr.tr-hover-state');
	});
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

            //alert ("FIRE11!!!");
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
	    		rowExpander(currentRowClass);
	    		$(this).text("Close Log");
	    	}
	    });
};

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

    	if ($(this).parents('table').hasClass('canit'))
    	{
    		overlayIDtoPass = "#canitOverlay";
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
        $('table.results tr.tr-hover-state').removeClass('tr-hover-state');
    });


});


///helllloooo




//hello world!



//hahahaha