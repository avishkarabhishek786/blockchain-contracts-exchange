<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07-Jul-16
 * Time: 9:23 PM
 */

class SendMail extends Orders {

    private $reciever_email = array();
    public $errors = array();
    private $from = null;
    private $sender = null;
    private $subject = null;
    private $body = null;
//add a field to dynamically add subject and to be sent as a parameter in do_email fn

    public function do_email($reciever_email=array(), $email_from=null, $email_sender=null, $email_subject=null, $email_body=null, $attachments=array()) {
        $this->reciever_email = $reciever_email;
        $this->from = $email_from;
        $this->sender = $email_sender;
        $this->subject = $email_subject;
        $this->body = $email_body;
        $this->attachments = $attachments;

        $mail = new PHPMailer;

        // please look into the config/config.php for much more info on how to use this!
        // use SMTP or use mail()
        if (EMAIL_USE_SMTP) {
            // Set mailer to use SMTP
            $mail->IsSMTP();
            //useful for debugging, shows full SMTP errors
            $mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
            // Enable SMTP authentication
            $mail->SMTPAuth = EMAIL_SMTP_AUTH;
            // Enable encryption, usually SSL/TLS
            if (defined(EMAIL_SMTP_ENCRYPTION)) {
                $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
            }
            $mail->SMTPSecure = "tls";
            // Specify host server
            $mail->Host = EMAIL_SMTP_HOST;
            $mail->Username = EMAIL_SMTP_USERNAME;
            $mail->Password = EMAIL_SMTP_PASSWORD;
            $mail->Port = EMAIL_SMTP_PORT;
        } else {
            $mail->IsMail();
        }

        if(trim($email_from) != "" && !empty($reciever_email)) {
            $mail->From = $email_from;
            $mail->FromName = $email_sender;

            $this->reciever_email[] = $reciever_email;
            $mail->IsHTML(true);
        foreach ($reciever_email as $rec) {
            $mail->AddAddress($rec);
        }
            $mail->AddCC(RT);
            $mail->AddCC(AB);
            $mail->Subject = $email_subject;
            $mail->Body = $email_body;
      
            if ($attachments!==null && is_array($attachments) && !empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $path_to_file = $attachment;
                    $arr = explode('/',$attachment);
                    $att_file = end($arr);
                    $mail->AddAttachment($path_to_file, $att_file);
                }
            }

            if(!$mail->Send()) {
                $this->errors[] = "Mail could not be sent" . $mail->ErrorInfo;
                return $this->errors;
            } else {
                return true;
            }
        }
        return false;
    }
}