<?php

require_once('simple_html_dom.php');

// Movie title to search for
$title = 'titanic';

// Construct the search URL
$url = 'https://hexdownload.co/?s=' . urlencode($title);

// Retrieve the search results page
$html = file_get_contents($url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));

// Load the search results page into a DOM object
$dom = str_get_html($html);

// Find the first search result link
$link = $dom->find('.post-title a', 0)->href;

// Retrieve the download page
$html = file_get_contents($link, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));

// Load the download page into a DOM object
$dom = str_get_html($html);

// Find the download link
$download_link = $dom->find('.download-box a', 0)->href;

// Print the download link
echo $download_link;

?>
