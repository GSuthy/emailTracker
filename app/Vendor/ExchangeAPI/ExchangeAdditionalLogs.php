<?php

require "ExchangeClient.php";

$maxResults = filter_input(INPUT_GET, "maxResults", FILTER_VALIDATE_FLOAT);
$internalMessageId = filter_input(INPUT_GET, "internalMessageId", FILTER_VALIDATE_FLOAT);

echo ExchangeClient::getAdditionalLogs($internalMessageId, $maxResults);