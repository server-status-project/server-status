<?php

/**
 * Class that encapsulates everything that can be done with email
 */

use PHPMailer\PHPMailer\PHPMailer;  // Inclusion of namespace will not cause any issue even if PHPMailer is not used
use PHPMailer\PHPMailer\Exception;

// TODO Any other way to handle this include? Should we just do the include form each index.php?
if (file_exists("libs/php_idn/idna.php")) {
    require_once("libs/php_idn/idna.php");   
} else {
    // Include for Admin section
    require_once("../libs/php_idn/idna.php");    
}

class Mailer {
    
    public function __construct(){
        
    }
    
    /**
     * Generic function to submit mail messages via mail og PHPmailer()
     * @param String $to Email address to which mail should be sent
     * @param String $message Body of email
     * @param boolean $html Set to true if we are sending HTML Mailer
     * @return boolean True if success
     */
    public function send_mail($to, $subject, $message, $html = true) {
        // TODO -Handle $to as an array in order to send to muliple recipients without having 
        //       to call the entire send_mail function over and over.. 

        $content_type  = ($html) ? 'text/html' : 'text/plain';
        
        // Convert IDN/punycode domain to ascii
        // TODO Handle IDN in left hand side of email address
        if ( $this->is_utf8($to) ) {
            $elements  = explode('@', $to);
            $domainpart = EncodePunycodeIDN(array_pop($elements));  // Convert domain part to ascii
            $to = $elements[0] . '@' . $domainpart;  // Reassemble tge full email address
            syslog(1,"email: " .$to);
        }
                
        // Send using PHP mailer if it is enabled
        if ( PHP_MAILER ) {
            require_once(PHP_MAILER_PATH .'/Exception.php'); /* Exception class. */
            require_once(PHP_MAILER_PATH .'/PHPMailer.php'); /* The main PHPMailer class. */
            
            if ( PHP_MAILER_SMTP ) {
                require_once(PHP_MAILER_PATH .'/SMTP.php');  /* SMTP class, needed if you want to use SMTP. */
            }
            
            $phpmail = new PHPMailer(false);

            $phpmail->setFrom(MAILER_ADDRESS, MAILER_NAME);
            $phpmail->addReplyTo(MAILER_ADDRESS, MAILER_NAME);
            //$phpmail->Debugoutput = error_log;
            
            // Define SMTP parameters if enabled 
            if ( PHP_MAILER_SMTP ) {
                
                $phpmail->isSMTP(); 
                $phpmail->Host = PHP_MAILER_HOST;
                $phpmail->Port = PHP_MAILER_PORT;
                $phpmail->SMTPSecure = PHP_MAILER_SECURE;
                //$phpmail->SMTPDebug = 2; // Enable for debugging
                
                // Handle authentication for SMTP if enabled
                if ( !empty(PHP_MAILER_USER) ) {
                    $phpmail->SMTPAuth = true;
                    $phpmail->Username = PHP_MAILER_USER;
                    $phpmail->Password = PHP_MAILER_PASS;
                }
            }
            
            $phpmail->addAddress($to);
            $phpmail->Subject = $subject;
            // Send HMTL mail 
            if ( $html ) {
                $phpmail->msgHtml($message);
                $phpmail->AltBody = $this->convert_html_to_plain_txt($message, false);                
            } else {
                $phpmail->Body = $message;  // Send plain text
            }
            
            $phpmail->isHtml($html);    // use htmlmail if enabled
            if ( ! $phpmail->send() ) {
                // TODO Log error message $phpmail->ErrorInfo;
                return false;                    
            }
            return true;
            
        } else {
            // Use standard PHP mail() function
            $headers = "Content-Type: $content_type; \"charset=utf-8\" ".PHP_EOL;
            $headers .= "MIME-Version: 1.0 ".PHP_EOL;
            $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
            $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
            
            mail($to, $subject, $message, $headers);
            // TODO log error message if mail fails
            return true;
        }
        
    }
    /**
     * Tries to verify the domain using dns request against an MX record of the domain part
     * of the passed email address. The code also handles IDN/Punycode formatted addresses which 
     * contains utf8 characters. 
     * Original code from https://stackoverflow.com/questions/19261987/how-to-check-if-an-email-address-is-real-or-valid-using-php/19262381
     * @param String $email Email address to check
     * @return boolean True if MX record exits, false if otherwise
     */
    public function verify_domain($email){
        // TODO - Handle idn/punycode domain names without being dependent on PHP native libs.
        $domain = explode('@', $email);
        $domain = EncodePunycodeIDN(array_pop($domain).'.');  // Add dot at end of domain to avoid local domain lookups
        syslog(1,$domain);
        return checkdnsrr($domain, 'MX');
    }
    
   
    /**
     * Check if string contains non-english characters (detect IDN/Punycode enabled domains)
     * Original code from: https://stackoverflow.com/questions/13120475/detect-non-english-chars-in-a-string
     * @param String $str String to check for extended characters
     * @return boolean True if extended characters, false otherwise
     */
    public function is_utf8($str)
    {
        if (strlen($str) == strlen(utf8_decode($str))) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Takes the input from an HTML email and convert it to plain text
     * This is commonly used when sending HTML emails as a backup for email clients who can only view, or who choose to only view,
     * Original code from https://github.com/DukeOfMarshall/PHP---JSON-Email-Verification/blob/master/EmailVerify.class.php
     * plain text emails
     * @param string $content The body part of the email to convert to plain text.
     * @param boolean $remove_links Set to true if links should be removed from email
     * @return String pain text version 
     */
    public function convert_html_to_plain_txt($content, $remove_links=false){
        // TODO does not handle unsubscribe/manage subscription text very well.
        // Replace HTML line breaks with text line breaks
        $plain_text = str_ireplace(array("<br>","<br />"), "\n\r", $content);
        
        // Remove the content between the tags that wouldn't normally get removed with the strip_tags function
        $plain_text = preg_replace(array('@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
        ), "", $plain_text); // Remove everything from between the tags that doesn't get removed with strip_tags function
        
        // If the user has chosen to preserve the addresses from links
        if(!$remove_links){
            $plain_text = strip_tags(preg_replace('/<a href="(.*)">/', ' $1 ', $plain_text));
        }
        
        // Remove HTML spaces
        $plain_text = str_replace("&nbsp;", "", $plain_text);
        
        // Replace multiple line breaks with a single line break
        $plain_text = preg_replace("/(\s){3,}/","\r\n\r\n",trim($plain_text));
        
        return $plain_text;
    }
    
}