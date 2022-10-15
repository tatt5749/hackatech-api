<?php

namespace App\Logging;
// use Illuminate\Log\Logger;
use DB;
use Illuminate\Support\Facades\Auth;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use App\Models\ErrorLog;

class MySQLLoggingHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        $this->table = 'error_logs';
        parent::__construct($level, $bubble);
    }
    
    protected function write(array $record):void
    {
        
       $data = array(
            'instance'    => gethostname(),
            'category'       => $record['message'],
            'context'       => json_encode($record['context']),
            'level'         => $record['level'],
            'level_name'    => $record['level_name'],
            'channel'       => $record['channel'],
            'created_at'    => getCurentTime(),
       );
       //dd($data);  
       ErrorLog::create($data);     
    }
}