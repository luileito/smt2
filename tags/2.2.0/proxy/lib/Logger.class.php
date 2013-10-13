<?php

/**
 * Logger class. This will write out input to a log file
 */
class Logger {
	const SEVERITY_DEBUG = 0;
	const SEVERITY_INFO  = 1;
	const SEVERITY_WARN  = 2;
	const SEVERITY_ERROR = 3;
	const SEVERITY_FATAL = 4;
	
	var $severities = array(
		Logger::SEVERITY_DEBUG => 'DEBUG',
		Logger::SEVERITY_INFO => 'INFO',
		Logger::SEVERITY_WARN => 'WARN',
		Logger::SEVERITY_ERROR => 'ERROR',
		Logger::SEVERITY_FATAL => 'FATAL'
	);
	
	function debug($message) {
		$this->log(Logger::SEVERITY_DEBUG, $message);
	}
	
	function info($message) {
		$this->log(Logger::SEVERITY_INFO, $message);
	}
	
	function warn($message) {
		$this->log(Logger::SEVERITY_WARN, $message);
	}
	
	function error($message) {
		$this->log(Logger::SEVERITY_ERROR, $message);
	}
	
	function fatal($message) {
		$this->log(Logger::SEVERITY_FATAL, $message);
	}
	
	private function log($severity, $message) {
		if (LOG_LEVEL > $severity) {
			return;
		}
		
		$this->openLog();
		
		$date = date(DATE_FORMAT);
		$fn = $this->getCallingFunction();
		
		$toWrite = sprintf('%1$s - %2$5s - %3$s - %4$s%5$s', $date, $this->severities[$severity], $fn, $message, LINE_BREAK);
		
		fwrite($this->file, $toWrite);
		$this->closeLog();
		$this->rotateLog();
	}
	
	private function openLog() {
		$this->file = fopen(LOG_FILE, 'ab');
	}
	
	private function closeLog() {
		fclose($this->file);
	}
	
	private function rotateLog() {
		if (LOG_MAX_SIZE > 0) {
			$bytes = LOG_MAX_SIZE * 1024; // convert KB to bytes
			$size = filesize(LOG_FILE);
			
			if ($size > $bytes) {
				if (MAX_LOG_BACKUPS > 0) {
					if (file_exists(LOG_FILE . '.' . MAX_LOG_BACKUPS)) {
						unlink(LOG_FILE . '.' . MAX_LOG_BACKUPS);
					}
					
					for ($i = MAX_LOG_BACKUPS - 1; $i > 0; $i--) {
						$filename = LOG_FILE . '.' . $i;
						if (file_exists($filename)) {
							rename($filename, LOG_FILE . '.' . ($i + 1));
						}
					}
					
					rename(LOG_FILE, LOG_FILE . '.1');
				}
				else {
					unlink(LOG_FILE);
				}
			}
		}
	}
	
	private function getCallingFunction() {
		$trace = debug_backtrace(FALSE);
		
		// $trace[0] will be this function
		// $trace[1] will be log()
		// $trace[2] will be (e.g.) debug()
		// $trace[3] will be the actual function
		$fn = $trace[3];
		
		return $fn['class'] . $fn['type'] . $fn['function'] . '()';
	}
}

?>