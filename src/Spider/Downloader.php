<?php
class Spider_Downloader
{
    private $curl = null;

    public function __construct()
    {
        $this->curl = curl_init();

        curl_setopt_array(
            $this->curl,
            array(
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'silverorange-spider',
            )
        );
    }

    public function download($uri)
    {
        curl_setopt($this->curl, CURLOPT_URL, $uri);
        return curl_exec($this->curl);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
}
