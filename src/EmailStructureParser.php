<?php

namespace DivineOmega\EmailStructureParser;

/**
 * Class EmailStructureParser
 * @package DivineOmega\EmailStructureParser
 */
class EmailStructureParser
{
    /**
     * @var
     */
    private $imapStream;

    /**
     * @var
     */
    private $msgNumber;

    /**
     * @var object
     */
    private $structure;

    private $parts;

    /**
     * EmailStructureParser constructor.
     * @param $imapStream
     * @param $msgNumber
     */
    public function __construct($imapStream, $msgNumber)
    {
        $this->imapStream = $imapStream;
        $this->msgNumber = $msgNumber;

        $structure = imap_fetchstructure($imapStream, $msgNumber);
        $this->parts = $this->parseParts($structure);
    }

    /**
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @return array
     */
    private function parseParts($structure)
    {
        $parts = [];
        $this->getPart($parts, $structure);
        return $parts;
    }

    /**
     * @param $returnParts
     * @param $structure
     * @param bool $partNumber
     */
    private function getPart(&$returnParts, $structure, $partNumber = false)
    {
        if (!is_array($structure) && isset($structure->parts))
        {
            $structure = $structure->parts;
            foreach($structure as $key => $substructure)
            {
                $key++;

                if ($partNumber) $partToGet = $partNumber .".". $key;
                else $partToGet = $key;

                if (isset($substructure->parts)) // multipart
                {
                    $this->getPart($returnParts, $substructure, $partToGet);

                }
                else
                {
                    $mimeType = $this->getMimeType($substructure);
                    $name = $this->getParameterValue($substructure, 'name');
                    $content = $this->getPartByPartNumber($substructure, $partToGet);
                    $returnParts[$mimeType][] = new Part($name, $content);
                }
            }
        }
        else
        {
            $mimeType = $this->getMimeType($structure);
            $name = $this->getParameterValue($structure, 'name');
            $content = $this->getPartByPartNumber($structure, 1);
            $returnParts[$mimeType][] = new Part($name, $content);
        }

    }

    private function getParameterValue($structure, $parameterName)
    {
        if (!isset($structure->parameters) || !is_array($structure->parameters)) {
            return null;
        }

        foreach($structure->parameters as $parameter) {
            if (strtolower($parameter->attribute) === strtolower($parameterName)) {
                return $parameter->value;
            }
        }

        return null;
    }

    /**
     * @param $structure
     * @param $partToGet
     * @return string
     */
    private function getPartByPartNumber($structure, $partToGet)
    {
        $text = imap_fetchbody($this->imapStream, $this->msgNumber, $partToGet);

        switch($structure->encoding) {
            case 3:
                return imap_base64($text);
                break;

            case 4:
                return imap_qprint($text);
                break;

            default:
                return $text;
                break;
        }
    }

    /**
     * @param $structure
     * @return string
     */
    private function getMimeType($structure)
    {
        if (!$structure) {
            return 'UNKNOWN';
        }

        $primaryMimeTypes = ["TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER"];

        if($structure->subtype)
        {
            return $primaryMimeTypes[(int) $structure->type] . '/' . $structure->subtype;
        }

        return "TEXT/PLAIN";
    }


}