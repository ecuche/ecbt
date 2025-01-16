<?php
declare(strict_types=1);
namespace Framework\Helpers;
use Framework\Helpers\Data;
class Mail
{
    protected array $errors = [];
    protected string $to = "";
    protected string $from = "";
    protected string $replyto = "";
    protected string $cc = '';
    protected string $bcc = "";
    protected string $subject = "";
    protected string $message = "";
    protected array $headers = [];
    protected string $attachments = "";
    protected string $boundary = "";
    protected bool $is_html = false;
    protected string $text = "";
    protected string $body = "";

    public function __construct(){
        $date = date('r', time());
        $this->boundary = md5($date);
        $this->headers['From'] = $this->from = $_ENV["EMAIL_FROM"];
        $this->headers['Reply-To'] =$this->replyto =  $_ENV["EMAIL_REPLYTO"];
        $this->headers['Date'] = $date;
        $this->headers['Content-Type'] = "multipart/mixed; boundary={$this->boundary}";
    }

    private function addError(string $field, string $message): string
    {
        $this->errors[$field] = $message;
        return $this->errors[$field];
    }
   
    private function address(string $type, string|object|array $email, string $name = ""): string
    {
        $emails = $type;
        if (is_array($email) || is_object($email)) {
            $email = (array) $email;
            $count = count($email);
            $i = 1;
            foreach ($email as $value){
                if(Data::is_multi_dim($email)){
                    if(empty($value['name']) || empty( $value['email'] ) || !filter_var($value['email'], FILTER_VALIDATE_EMAIL)){
                        return $this->addError(strtok($type, ':'), "Invalid email format");
                    }
                    $emails .= $i !== $count ? " {$value['name']} <{$value['email']}>," : " {$value['name']} <{$value['email']}>";
                }else{
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return $this->addError(strtok($type, ':'), "Invalid email format");
                    }
                    $emails .= $i !== $count ? "{$value}," : " {$value}";
                }
                $i++;
            }
            return ucwords($emails);
        }else{
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $string = empty($name) ? $email : "{$name} <{$email}>";
                return ucwords($string); 
            }
            return $this->addError(strtok($type, ':'), "Invalid email format");
        }
    }

    public function to(string $email, string $name = ""): string
    {
        $to = $this->address("To: ", $email,  $name);
        if(empty($this->to)){
            return $this->to = $to;
        }
        return  $this->to .= ", {$to}";
    }

    public function from(string $email , string $name = ""): string|bool
    {
        $from = $this->address('From: ', $email, $name);
        return $this->headers['From'] = $this->from = $from;
    }

    public function replyTo(string $email , string $name = ""): string|bool
    {
        $replyto = $this->address('Reply-To: ', $email, $name);
        return $this->headers['Reply-To'] = $this->replyto = $replyto;
    }

    public function cc(string $email , string $name = ""): string
    {
        $cc = $this->address('Cc: ', $email, $name);
        if(empty($this->cc)){
            return $this->headers['Cc'] = $this->cc = $cc;
        }
        $this->cc .= ", {$cc}";
        return  $this->headers['Cc'] =  $this->cc;
    }
    
    public function bcc(string $email , string $name = ""): string
    {
        $bcc = $this->address('Bcc: ', $email, $name);
        if(empty($this->bcc)){
            return $this->headers['Bcc'] = $this->bcc = $bcc;
        }
        $this->bcc .= ", {$bcc}";
        return  $this->headers['Bcc'] = $this->bcc;
    }

    public function subject(string $subject): string
    {
        return $this->subject = ucwords($subject);
    }

    public function is_html(): bool
    {        
        return $this->is_html = true;
    }

    public function text(string $plain, string $type = 'plain'): string{
        $message = "";
        $message .= "--alt-{$this->boundary}\r\n";
        $message .= "Content-Type: text/{$type}; charset=iso-8859-1\r\n"; 
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "{$plain} \r\n\r\n";
        $this->message .= $message;
        return $this->text = $plain;
    }

    public function message(string $message): string
    {   
        $this->body = $message;    
        $this->message .= "--{$this->boundary}\r\n";
        $this->message .= "Content-Type: multipart/alternative; boundary=alt-{$this->boundary}\r\n\r\n";
        $this->text($this->body);
        return  $this->message;
    }

    public function attachment(string $fullpath): string
    {
        if(!empty($fullpath) && file_exists($fullpath)){
            $content = file_get_contents( $fullpath);
            $content = chunk_split(base64_encode($content));
            $file_name = basename($fullpath);

            $this->attachments .= "--{$this->boundary}\r\n";
            $this->attachments .= "Content-Type: application/octet-stream; name=\"{$file_name}\"\r\n";
            $this->attachments .= "Content-Transfer-Encoding: base64 \r\n";
            $this->attachments .= "Content-Disposition: attachment; filename=\"{$file_name}\" \r\n\r\n";
            $this->attachments .= "{$content}  \r\n\r\n";
        }elseif(!empty($fullpath) && !file_exists($fullpath)){
            return $this->addError('attachment', "Invalid file path");
        }
        return $this->attachments;
    }   

    private function errors(): bool|string
    {
        if(empty($this->to)){
            return $this->addError('to', "Kindly add a recipeint address");
        }
        if(empty($this->subject)){
            return $this->addError('subject', "Kindly add a subject");
        }
        if(empty($this->body)){
            return $this->addError('message', "Kindly add a message");
        }
       if(empty($this->from)){
            return $this->addError('from', "Kindly add a sender address");
        }
        return true;
    }

    private function prepare(): array|string
    {   
        $this->errors();
        if(!empty($this->errors)){
            return $this->errors;
        }
        $this->is_html ? $this->text($this->body, 'html') : null ;
        
        $this->message .= "--alt-{$this->boundary}--\r\n\r\n";
        $message = "";
        $message .= $this->message;

        $message .= $this->attachments;
        $message .= "--{$this->boundary}--"; 
        $this->message = $message;
        return $message;
    }

    public function send(): bool|array|string
    {
        $this->prepare();
        $mail = @mail($this->to, $this->subject, $this->message,  $this->headers) ? true : false;
        if($mail){
            return true;
        }
        return $this->errors;
    }
}


