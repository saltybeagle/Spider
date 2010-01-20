<?php
abstract class Spider_DbLogger extends Spider_Logger
{
    protected $db = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}