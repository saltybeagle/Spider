<?php
abstract class Spider_DbLogger extends Spider_LoggerAbstract
{
    protected $db = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}