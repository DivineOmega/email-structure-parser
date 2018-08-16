<?php

use DivineOmega\EmailStructureParser\EmailStructureParser;

require_once('vendor/autoload.php');

// Connect to mailbox
$imapStream = imap_open('{outlook.office365.com:993/ssl/novalidate-cert}INBOX', getenv('USERNAME'), getenv('PASSWORD'));

// Get first message number
$msgNums = imap_search($imapStream, 'ALL');
$msgNum = $msgNums[0];

// Load message into parser
$parser = new EmailStructureParser($imapStream, $msgNum);

// Get parsed multipart email parts - including plain text and/or HTML content, and any attachments
$parts = $parser->getParts();

// Output HTML email content
var_dump($parts['TEXT/HTML']);

// Save attached PNG images
foreach($parts['IMAGE/PNG'] as $image) {
    file_put_contents($image->name, $image->content);
}
