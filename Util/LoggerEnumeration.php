<?php
class LoggerEnumeration
{
    private $handle;

    private $dateFormat;


    public function __construct($file, $mode = 'a') {

        $this->handle = fopen($file, $mode);

        $this->dateFormat = 'd/M/Y H:i:s';
    }

    public function dateFormat($format) {
        $this->dateFormat = $format;
    }
    public function getDateFormat() {
        return $this->dateFormat;
    }
    /**
     * Writes info to the log
     * @param mixed, string or an array to write to log
     * @access public
     */
    public function log($entries) {

        date_default_timezone_set('Europe/Madrid');

        if(is_string($entries)) {
            fwrite($this->handle, "Error: [" . date($this->dateFormat) . "] " . $entries);
            $this->addIpClient();

            return;
        }

        if(is_array($entries)) {
            $firstLine = true;
            foreach($entries as $value) {
                if($firstLine) {
                    fwrite($this->handle, "Error: [" . date($this->dateFormat) . "] " . $value);
                    $firstLine = false;
                    continue;
                }
                fwrite($this->handle, $value);
            }

            $this->addIpClient();
        }
    }


    private function addIpClient()
    {
        $ipClient = ClientUtil::getIp();
        $ipClient = '- '.$ipClient.'-';

        fwrite($this->handle, $ipClient."\n");
    }
}
