<?php
class Spider_Downloader
{
    public function download($uri, $options = array())
    {
        //Make sure that the curl_options exists.
        if (!isset($options['curl_options'])) {
            $options['curl_options'] = array();
        }
        
        if (isset($options['user_agent'])) {
            $options['curl_options'][CURLOPT_USERAGENT] = $options['user_agent'];
        }
        
        //Make sure that the content is returned.
        $options['curl_options'][CURLOPT_RETURNTRANSFER] = true;
        $options['curl_options'][CURLOPT_NOBODY]         = false;

        $info = Spider::getURIInfo($uri, $options['curl_options']);
        
        if (!$info['content']) {
            throw new Exception('Error downloading ' . $uri . ' ' . $info['content']);
        }

        if (in_array($info['http_code'], array(0, 404)) && isset($options['crawl_404_pages']) && !$options['crawl_404_pages']) {
            throw new Exception('404 page ' . $uri . ' ' . $info['http_code']);
        }

        return $info['content'];
    }
}
