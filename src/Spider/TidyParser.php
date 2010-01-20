<?php
class Spider_TidyParser extends Spider_Parser
{
    public function parse($content)
    {
        $content = $this->tidy($content);
        return $this->getXPath($content);
    }

    protected function tidy($content)
    {
        $config = array(
            'output-xhtml' => true,
        );

        $tidy = tidy_parse_string($content, $config, 'utf8');
        $tidy->cleanRepair();

        return (string)$tidy;
    }
}