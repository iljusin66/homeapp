<?php
namespace Latecka\HomeApp\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class mailer {
    
    private static $mailer;
    
    public function __construct() {
        ;
    }
    
    public function sendPHPMail(
        $fromName
        , $fromMail
        , $toName
        , $toMail
        , $subject
        , $html_message
        , $aAttachFiles = []
        , $iCal = ''
        , $aCidImages = []
        , $aCC = [] //prijemci kopie mailu. Key = e-mail, value = jmeno prijemce
        , $aBCC = [] //prijemci skryte kopie mailu. Key = e-mail, value = jmeno prijemce
        ) {


        //Create a new PHPMailer instance
        $this->setPHPMailer();

        //Set who the message is to be sent from
        self::$mailer->setFrom($fromMail, $fromName);
        //Set an alternative reply-to address
        self::$mailer->addReplyTo($fromMail, $fromName);
        //Set who the message is to be sent to
        self::$mailer->addAddress($toMail, $toName);
        
        if (is_array($aCC)) :
            foreach ($aCC AS $mailCC => $nameCC) :
                self::$mailer->addCC($mailCC, $nameCC);
            endforeach;
        endif;
        
        if (is_array($aBCC)) :
            foreach ($aBCC AS $mailBCC => $nameBCC) :
                self::$mailer->addBCC($mailBCC, $nameBCC);
            endforeach;
        endif;
        
        //Set the subject line
        self::$mailer->Subject = $subject;
        self::$mailer->isHTML(true);

        self::$mailer->Body = $html_message;

        //Replace the plain text body with one created manually
        //$mailer->AltBody = 'This is a plain-text message body v češtině';
        self::$mailer->clearAttachments();

        //Attach an file
        $this->addAttachFiles($aAttachFiles);

        if (!empty($iCal)) { self::$mailer->addStringAttachment($iCal,'ical.ics','base64','text/calendar'); }
        $this->adCidImages($aCidImages);

        //send the message, check for errors
        if (!self::$mailer->send()) {
            echo "Mailer Error: " . self::$mailer->ErrorInfo;
        } else {
            //echo "Message sent!";
        }
        self::$mailer->ClearAllRecipients();


    }
    
    
    private function setPHPMailer() {
    
        if (gettype(self::$mailer) == 'object') { return; }

        self::$mailer = new PHPMailer();

        //Character set
        self::$mailer->CharSet = "UTF-8";
        //Tell PHPMailer to use SMTP
        self::$mailer->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        // 3 = As 2, but also show details about the initial connection; only use this if you're having trouble connecting (e.g. connection timing out)
        // 4 = As 3, but also shows detailed low-level traffic. Only really useful for analyzing protocol-level bugs, very verbose, probably not what you need.
        self::$mailer->SMTPDebug = 0;
        //Ask for HTML-friendly debug output
        self::$mailer->Debugoutput = 'html';
        //Set the hostname of the mail server
        self::$mailer->Host = "mail2.bonicom.cz";
        //self::$mailer->Host = "email.active24.com";
        //Set the SMTP port number - likely to be 25, 465 or 587
        self::$mailer->Port = 465;
        //self::$mailer->Port = 25;
        //Whether to use SMTP authentication
        self::$mailer->SMTPAuth = true;
        self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //ssl
        //self::$mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //tls
        //Username to use for SMTP authentication
        self::$mailer->Username = 'statistiky@clinterap.cz';
        //Password to use for SMTP authentication
        self::$mailer->Password = "5BdhTjD_b";

    }
    
    private function addAttachFiles($aAttachFiles) {

        //Attach an CID images
        if (!is_array($aAttachFiles)) {
            if ($aAttachFiles=="") { return; }

            $a[] =  $aAttachFiles;
            $aAttachFiles = $a;
            unset($a);
        }

        if (count($aAttachFiles)==0) { return; }

        foreach ($aAttachFiles AS $attach_name) {
            if (!file_exists($attach_name)) { continue; }
            self::$mailer->addAttachment($attach_name);
        }
    }


    function adCidImages($aCidImages) {

        //Attach an CID images
        if (!is_array($aCidImages)) {
            if ($aCidImages=="") { return; }

            $a[] =  $aCidImages;
            $aCidImages = $a;
            unset($a);
        }

        if (count($aCidImages)==0) { return; }

        foreach ($aCidImages AS $cid => $file) {
            if (!file_exists($file)) { continue; }
            selef::$mailer->AddEmbeddedImage($file, $cid);
        } 
    }

}





