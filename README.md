# Email Structure Parser
Given an IMAP stream and a message number, this library will parse the 
structure of multipart emails.

## Installation

You can easily install the Email Structure Parser package using the following 
Composer command.

```bash
composer require divineomega/email-structure-parser
```

## Usage

To use this parser, you must have first connect to a mail server using PHP's built-in 
`imap_open` function. Once connected, you then need to retrieve a message number, 
via the `imap_search` function.

Once you have a message number, you can pass it, along with the IMAP stream object, 
into the `EmailStructureParser`. You can then call the `getParts()` method to 
retrieve an array of parsed email parts split up by mime type.

See the example usage code below.

```php
use DivineOmega\EmailStructureParser\EmailStructureParser;

// Connect to mailbox
$imapStream = imap_open('{outlook.office365.com:993/ssl/novalidate-cert}INBOX', getenv('USERNAME'), getenv('PASSWORD'));

// Get a message number (in this example, just get the first)
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
```

## Content IDs

Some emails embed images into the html content using "cid:" urls; these URLs link to the content ID of another part within the email rather than being a URL that can be resolved normally.

If one exists, this content ID will be exposed via the `contentId` property of the `Part`:

```php
foreach($parts['IMAGE/PNG'] as $image) {
    // Store the file as in the above example:
    file_put_contents($image->name, $image->content);
    // You would then want to store this relationship in your database:    
    echo "{$image->contentId} => {$image->name}\n";
}
```