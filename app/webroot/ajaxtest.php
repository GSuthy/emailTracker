<?php
	$ID = $_REQUEST["ID"];
	if ($ID == 5)
	{
		echo "Message id: 1 Filename: /opt/mail/quarantine/20140121/spam/qfs0LBT80R022330<br/> Transport layer information:<br/> -----------------------------------------------------------------------<br/> Sending host: mta.newsletter.rakuten.com [198.245.80.132]<br/> Envelope From: address: bounce-2883_HTML-12992025-106607-6196309-1814@bounce.newsletter.rakuten.com<br/> Final recipient address: brandonbrasmussen@gmail.com<br/> Envelope To: addresses: brasmussen@byu.edu<br/> Message header:<br/> ----------------------------------------------------------------------<br/> Received: from mta.newsletter.rakuten.com (mta.newsletter.rakuten.com [198.245.80.132])<br/> by gridley.byu.edu (8.14.3/8.13.8) with ESMTP id s0LBT80R022330<br/> Subject: 10% Back on TV & Home Theater! Samsung LED HDTV $497.99, Harman Kardon Receiver $369.99 + More!<br/> Date: Tue, 21 Jan 2014 05:29:07 -0600<br/> MIME-Version: 1.0<br/>";
	}
	else
	{
		echo "It's not a 5!";
	}

?>