<?php
class Spider_Filter_RobotsTxt extends Spider_UriFilterInterface
{
    public static $robotstxt = array();

    function accept()
    {
        return $this->robots_allowed($this->current());
    }

    /**
     * @param string $url
     * @param string $useragent
     *
     * @return bool
     *
     * Original PHP code by Chirp Internet: www.chirp.com.au
     * Please acknowledge use of this code by including this header.
     */
    function robots_allowed($url, $useragent = false)
    {
        $parsed = parse_url($url);

        $agents = array(preg_quote('*'));
        if ($useragent) {
            $agents[] = preg_quote($useragent);
        }
        $agents = implode('|', $agents);

        // Get robots.txt if it is not statically cached
        if (empty(self::$robotstxt) && self::$robotstxt !== false) {
            self::$robotstxt = file("http://{$parsed['host']}/robots.txt");
        }

        // If there isn't a robots.txt, then we're allowed in
        if (empty(self::$robotstxt)) {
            return true;
        }

        $rules = array();
        $ruleApplies = false;
        foreach (self::$robotstxt as $line) {
            // Skip blank lines
            if (!$line = trim($line)) {
                continue;
            }

            // Following rules only apply if User-agent matches $useragent or '*'
            if (preg_match('/^\s*User-agent: (.*)/i', $line, $match)) {
                $ruleApplies = preg_match("/($agents)/i", $match[1]);
            }

            if ($ruleApplies && preg_match('/^\s*Disallow:(.*)/i', $line, $regs)) {
                // An empty rule implies full access - no further tests required
                if (!$regs[1]) {
                    return true;
                }
                // Add rules that apply to array for testing
                $rules[] = preg_quote(trim($regs[1]), '/');
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
