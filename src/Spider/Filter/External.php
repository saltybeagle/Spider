<?php
class Spider_Filter_External extends Spider_UriFilterInterface
{
    protected $agnostic_baseuri;

    public function __construct(Iterator $iterator, $baseuri)
    {
        $this->agnostic_baseuri = $this->makeAgnostic($baseuri);

        parent::__construct($iterator);
    }

    public function accept()
    {
        //Only get sub-pages of the baseuri
        $agnostic_uri = $this->makeAgnostic(($this->current()));

        if (strncmp($this->agnostic_baseuri, $agnostic_uri, strlen($this->agnostic_baseuri)) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Strip the http or https from a URL to make it agnostic
     *
     * @param $absolute_uri
     * @return mixed
     */
    public function makeAgnostic($absolute_uri)
    {
        return preg_replace('/^https?:\/\//', '//', $absolute_uri);
    }
}
