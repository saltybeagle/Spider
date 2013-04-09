<?php
abstract class Spider_Logger_Db extends Spider_LoggerAbstract
{
    protected $db = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}