<?php
class Spider_Filter_External extends Spider_UriFilterInterface
{
    protected $baseuri = '';

    function __construct(Iterator $iterator, $baseuri)
    {
        $this->baseuri = $baseuri;
        
        parent::__construct($iterator);
    }
    
    function accept()
    {
        //Only get sub-pages of the baseuri
        if (strncmp($this->baseuri, $this->current(), strlen($this->baseuri)) !== 0) {
            return false;
        }
        
        return true;
    }
}