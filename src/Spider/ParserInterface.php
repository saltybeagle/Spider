<?php
interface Spider_ParserInterface
{
    /**
     * parse the content and return xpath object
     * 
     * @param string $content The content
     * 
     * @return DOMXPAth
     */
    public function parse($content);

}