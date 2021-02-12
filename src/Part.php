<?php

namespace DivineOmega\EmailStructureParser;

class Part
{
    public $name;
    public $content;
    
    public function __construct($name, $content, $contentId = null)
    {
        $this->name = $name;
        $this->content = $content;
        $this->contentId = $contentId;
    }
}