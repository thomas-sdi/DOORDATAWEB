<?
require_once 'Mail.php'; // PEAR extension

class Mailer {
    
    var $_smtp;
    
    public function __construct($config) {
        // create an SMTP session 
        $this->_smtp = Mail::factory('smtp', array (
            'host'     => $config->host,
            'port'     => $config->port,
            'auth'     => $config->user != null,
            'username' => $config->user,
            'password' => $config->password));
    }

    public function send($to, $subject, $body, $from) {
        // send an email
        $mailStatus = $this->_smtp->send(
            $to, array('From' => $from, 'To' => $to, 'Subject' => $subject), $body);
           
        // check if the mail was sent successfully
        if (PEAR::isError($mailStatus))
            throw new Exception($mailStatus->getMessage());
    }
}