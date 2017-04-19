<?php
class Spider_Filter_RobotsTxt extends Spider_UriFilterInterface
{
    public static $robotstxt = array();

    private $downloader = null;

    /**
     * Array of options
     * 
     * @var array
     */
    protected $options = array();

    public function __construct(Iterator $iterator, $options = array())
    {
        $this->downloader = new Spider_Downloader();
        
        $this->options = $options;

        //Don't throw exceptions on 404 robots.txt
        $this->options['crawl_404_pages'] = true;

        parent::__construct($iterator);
    }

    public function accept()
    {
        return $this->robots_allowed($this->current(), $this->options['user_agent']);
    }

    /**
     * @param string $url
     * @param bool|string $useragent
     *
     * @return bool
     *
     * Original PHP code by Chirp Internet: www.chirp.com.au
     * Please acknowledge use of this code by including this header.
     */
    public function robots_allowed($url, $useragent = false)
    {
        $parsed = parse_url($url);

        $agents = array(preg_quote('*', '/'));
        if ($useragent) {
            $agents[] = preg_quote($useragent, '/');
        }
        $agents = implode('|', $agents);

        $root = $parsed['scheme'] . '://' . $parsed['host'] . '/';
        
        // Get robots.txt if it is not statically cached
        if (!isset(self::$robotstxt[$root])) {
            try {
                self::$robotstxt[$root] = $this->downloader->download($root . "robots.txt", $this->options);
            } catch (Exception $e) {
                //Failed to get the robots.txt file
                self::$robotstxt[$root] = '';
            }
        }

        $robotstxt = explode("\n", self::$robotstxt[$root]);

        // If there isn't a robots.txt, then we're allowed in
        if (empty($robotstxt)) {
            return true;
        }

        $rules = array();
        $ruleApplies = false;
        foreach ($robotstxt as $line) {
            // Skip blank lines
            if (!$line = trim($line)) {
                continue;
            }

            // Following rules only apply if User-agent matches $useragent or '*'
            if (preg_match('/^\s*User-agent: (.*)/i', $line, $match)) {
                $ruleApplies = preg_match("/^($agents)/i", $match[1]);
            }

            if ($ruleApplies && preg_match('/^\s*Disallow:(.*)/i', $line, $regs)) {
                // An empty rule implies full access - no further tests required
                if (!$regs[1]) {
                    return true;
                }
                // Add rules that apply to array for testing
                
                $rule = preg_quote(trim($regs[1]), '/');

                //Convert wildcards to regex wildcards
                $rule = str_replace('\*', '.*', $rule);
                
                $rules[] = $rule;
            }
        }

        foreach ($rules as $rule) {
            // Check if page is disallowed to us
            if (preg_match("/^$rule/", $parsed['path'])) {
                return false;
            }
        }

        return true;
    }
}
