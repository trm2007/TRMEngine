<?php

namespace TRMEngine\EMail;

use TRMEngine\EMail\Exceptions\TRMEMailExceptions;
use TRMEngine\EMail\Exceptions\TRMEMailSendingExceptions;
use TRMEngine\EMail\Exceptions\TRMEMailWrongBodyExceptions;
use TRMEngine\EMail\Exceptions\TRMEMailWrongRecepientExceptions;
use TRMEngine\EMail\Exceptions\TRMEMailWrongThemeExceptions;

class TRMEMail
{
/**
 * ��� ������ ���������� � ��������� "UTF-8"
 */
const SendCoding = "UTF-8";
/**
 * @var string - E-mail �����������
 */
protected $emailfrom = "";
/**
 * @var string - ��� �����������
 */
protected $namefrom = "";
/**
 * @var string - E-mail ����������
 */
protected $emailto = "";
/**
 * @var string - ��� ����������
 */
protected $nameto = "";
/**
 * @var string - E-mail ���������� 2, ����� ������
 */
protected $emailCc = "";
/**
 * @var string - ��� ���������� 2, ����� ������
 */
protected $nameCc = "";
/**
 * @var string - Reply-To �����, �� ������� ���������� �����
 */
protected $emailreplyto = "";
/**
 * @var string - Reply-To ���
 */
protected $namereplyto = "";
/**
 * @var string - ���� ���������
 */
protected $subject = "";

/**
 * @var string - ����� ������ ��� ��������������, ����� ������������ ��� ����
 */
protected $textplain = "";
/**
 * @var string - ����� ������ � HTML - ���� ����� ������������� � ��������
 */
protected $texthtml = "";
/**
 * @var string - ���������, � ������� ����������� ������, ��� ������� �� ������������ �������� - Windows-1251
 * ��� ����� ����������������� � SendCoding = UTF-8 (���� �� ������)
 */
protected $coding;
/**
 * @var string - ��� ����������� ������, ����������� ������������� �� ��������� ������������� ������
 */
protected $ContentType;

/**
 * @var array ������ ������ ���� ������ (� ����� �� �������) ��� ������������ � ������
 */
protected $filestosend = array();

/**
 * @var array ��������, ������� ����� �������� � ���� ������
 */
protected $inlineimages = array();

/**
 * @var string  ���������� ������ ��� ���������� �������� ������
 */
protected $un;

/**
 *
 * @var array - ������ � ����������� ��������� �������
 */
protected $config = array();


/**
 * � ������������ �� ��������� ��������������� ��������� ������� ������ � "Windows-1251"
 * ��� ��� ��� ���� ���������� ������� � "Windows-1251"
 * ����� ��� ������ (��������� � ���������) ������������� � "UTF-8" ��� ��������
 * 
 * ��� �� ������������ ���������� ������ ��� ���������� ��������
 */
public function __construct()
{
    $this->setCoding(); //"Windows-1251");
    $this->un = $this->getUniq();
}

/**
 * ������������� ���������, 
 * � ������� �������� ��������� � ����, 
 * ���� ������ ������ ������������ � UTF-8
 * 
 * @param string $coding ����������, ����������� = SendCoding = UTF-8
 */
public function setCoding($coding = self::SendCoding)
{
    $this->coding = $coding;
}

/**
 * ���������� ���������� ������ ��� ���������� � ������ �������
 * @return string
 */
protected function getUniq()
{
    return strtoupper(uniqid(time()));
}

/**
 * ���������� ��������� � HTML-������� � ����������
 * 
 * @return boolean
 */
public function sendEmail()
{
    $this->validate();
    
    //���������� ����� ��� � ����������� �� ������������� ������ ������ (������� ������������� ������, ��������, HTML ��� ������ �����)
    $this->ContentType = $this->calculateContentType();
    
    $subj = $this->generateSubject();

    $head = $this->generateHeader();

    $msg = $this->generateMessageBody();
    if(strlen($msg)>0 && $this->ContentType == "multipart/mixed" ) { $msg = "--".$this->un . "\r\n" . $msg; }

    $msg .= $this->generateAttach();
    if( $this->ContentType == "multipart/mixed" ) { $msg .= "--".$this->un; }

    $old_from = ini_get('sendmail_from');
    ini_set('sendmail_from', $this->emailfrom);
    if( isset($this->config["smtphost"]) && isset($this->config["smtpport"]) )
    {
        $this->sendSMTP($this->emailto, $subj, $msg, $head);
    }
    else if( !mail($this->emailto, $subj, $msg, $head) )
    {
        throw new TRMEMailSendingExceptions($this->emailto);
    }

    ini_set('sendmail_from', $old_from);

    return true;
}

/**
 * 
 * @param string $str - ������ , ������� ����� �������� � ���� ������ ��� � ����� �����
 * ����������� � ���������� ������� base64 ��� ��������
 * 
 * @return string
 */
protected function setStrToCoding($str)
{
    if( !empty($str) )
    {
        // ������ UTF-8
        return ('=?'.strtolower(TRMEMail::SendCoding).'?B?'.base64_encode($str).'?=');
    }
    return '';
}

/**
 * ����������� ��������� ������ �� �������� �� ��������� ��� ������ � ���� �������� ������,
 * � ������������ ��� �������� UTF-8,
 * ���� � ���������� ��������� ���������, �� ������ �� ���������,
 * ��������! ��������� ��������� ������ �� �����������.
 */
public function setStrCharset($str)
{
    $newstr = $str;
    // ���� ��������� �� ���������, �� ����������� 
    if($this->coding != TRMEMail::SendCoding )
    {
        $newstr = iconv($this->coding, TRMEMail::SendCoding, $str); // TRMLib::conv($str, $currentdatacharset, GlobalConfig::$ConfigArray["Charset"]);
    }
    return $newstr;
}

/**
 * ��������� ���� ������
 * 
 * @return string
 */
protected function generateSubject()
{
    return $this->setStrToCoding($this->subject);
}

/**
 * ��������� ��������� ������ � ���������� � ���� ������!
 * 
 * @return string
 */
protected function generateHeader()
{
    if( !$this->emailreplyto || !strlen($this->emailreplyto) )
    {
        $this->emailreplyto = $this->emailfrom;
        $this->namereplyto = $this->namefrom;
    }
    $head = //"From: ".$this->emailfrom."\r\n"
            "Return-Path: ".$this->emailreplyto."\r\n"
            ."From: ".$this->setStrToCoding($this->namefrom)." <".$this->emailfrom.">\r\n"
            ."To: {$this->emailto}\r\n";
    if(!empty($this->emailCc))
    {
        if( empty($this->nameCc) ) { $this->nameCc = $this->emailCc; }
        $head .= "Cc: ".$this->setStrToCoding($this->nameCc)." <{$this->emailCc}>\r\n";
    }
    $head .= "X-Mailer: TRMEmail sender\r\n"
            ."Reply-To: ".$this->setStrToCoding($this->namereplyto)." <".$this->emailreplyto.">\n"
            ."Sender: ".$this->setStrToCoding($this->namefrom)." <".$this->emailfrom.">\r\n"
            ."X-Priority: 3 (Normal)\r\n"
            ."MIME-Version: 1.0\r\n";
    
    if( $this->ContentType == "multipart/mixed" )
    {
        $head .= "Content-Type: multipart/mixed; boundary=\"".$this->un."\"\r\n"; //multipart/mixed;\r\n"  // ������ ������� �� ���������� ������ ������ - multipart/mixed;
    }
    elseif( $this->ContentType == "multipart/alternative" )
    {
        $head .= "Content-Type: multipart/alternative; boundary=\"ALT_".$this->un."\"\r\n"; //multipart/mixed;\r\n"  // ������ ������� �� ���������� ������ ������ - multipart/mixed;
    }
    elseif( $this->ContentType == "multipart/related" )
    {
        $head .= "Content-Type: multipart/related; boundary=\"REL_".$this->un."\"\r\n"; //multipart/mixed;\r\n"  // ������ ������� �� ���������� ������ ������ - multipart/mixed;
    }
    elseif( $this->ContentType == "text/html" || $this->ContentType == "text/plain" )
    {
        $head .= "Content-Type: {$this->ContentType}; charset=".TRMEMail::SendCoding." \r\n"."Content-Transfer-Encoding: base64 \r\n"; //multipart/mixed;\r\n"  // ������ ������� �� ���������� ������ ������ - multipart/mixed;
    }

    return $head;
}

/**
 * ���������� ��� �������� ������ �� ���������� � ��� ������������� ������, Html ��� �������� ������...
 * 
 * @return string|boolean - ���� ������� ���������� ��� ����������� ������, 
 * �� �������� ������ � ������� ���� - text/html, ����� false
 */
protected function calculateContentType()
{
    if( !empty($this->filestosend) ) { return "multipart/mixed"; }
    if( strlen($this->texthtml) && strlen($this->textplain) ) { return "multipart/alternative"; }
    if( strlen($this->texthtml) &&  !empty($this->inlineimages) ) { return "multipart/related"; }
    if( strlen($this->texthtml) ) { return "text/html"; }
    if( strlen($this->textplain) ) { return "text/plain"; }
    return false;
}

/**
 * ��������� ���� ��������� �� ���� ����� - text/plain � text/html ��� �����������, �������������� ��� �������� ������ � � ���� HTML
 * 
 * @return string
 */
protected function generateMessageBody()
{
    if( !strlen($this->textplain) )
    {
        return $this->generateRelated();
    }
    
    $content = "";
    if( isset($this->texthtml) && strlen($this->texthtml) )
    {
        if( $this->ContentType != "multipart/alternative" )
        {
            $content .= "Content-Type: multipart/alternative; boundary=\"ALT_".$this->un."\"\r\n\r\n"; // ����� � ������� HTML � �������������� ������ plain/text - multipart/mixed;
        }
        $content .= "--ALT_".$this->un."\r\n";
        $content .= $this->generateTextPlain();
        $content .= "--ALT_".$this->un."\r\n";
        $content .= $this->generateRelated();
        $content .= "--ALT_".$this->un."\r\n\r\n";
    }
    else
    {
        $content .= $this->generateTextPlain();
    }


    return $content; // ��������� ��������� �� ������ �� 76 �������� , � ����� ������ ����������� \r\n
}

/**
 * ���������� ����� ������ multipart/related,
 * ��� ����������, ���� ���� ������������ HTML � ������ ���� ������ �� ��������� (�������������) ��������
 * 
 * @return string
 */
protected function generateRelated()
{
    if( empty($this->inlineimages) )
    {
        return $this->generateHtml();
    }
    // ���� ��� ����������, ���� ������ �������� �� ������
    // �� �������� � ���� ������ ����� �������� ������ ���� HTML ���� �� ������, ����� �� ��� ������ ����� �� ���������
    
    if( isset($this->texthtml) && strlen($this->texthtml)>0 )
    {
        $content = "";
        if( $this->ContentType != "multipart/related" )
        {
            $content = "Content-Type: multipart/related; boundary=\"REL_".$this->un."\"\r\n\r\n"; // ����� � ������� HTML � �������������� ������ plain/text - multipart/mixed;
        }
        $content .= "--REL_".$this->un."\r\n";
        $content .= $this->generateHtml();
        $content .= "--REL_".$this->un."\r\n\r\n";
        return $content;
    }
    return "";
}

/**
 * 
 * @param string $boundary - ����������� �������� � multipart/alternative
 * @return string
 */
protected function generateTextPlain()
{
    if( !isset($this->textplain) || !strlen($this->textplain)>0 )
    {
        return "";
    }
    
    //$text = strip_tags($text); //������� � ������� ������ ��� HTML-����
//    $text = $this->setStrToCoding($text);
/*
    if($this->coding != TRMEMail::SendCoding)
        $text = iconv($this->coding, TRMEMail::SendCoding, $text); // ����������� �� Windows-1251 � UTF-8
 * 
 */
    $text = chunk_split( base64_encode( $this->replaceCRLF( $this->textplain ) ) );
//    $text = wordwrap($text,70,"\r\n"); // ������ ������ � �������� ������ ���� �� ������� 70 ��������
//    $text = self::quotedPrintableEncode($text);
    
    if( $this->ContentType != "text/plain" )
    {
        $content = "Content-Type: text/plain; charset=".TRMEMail::SendCoding." \r\n";
    //    $content .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit ������ ��� ��� ������������ � UTF-8 � ��� ������ ��������
        $content .= "Content-Transfer-Encoding: base64\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit ������ ��� ��� ������������ � UTF-8 � ��� ������ ��������
        $content .= $text."\r\n";
        return $content;
    }
    return $text;
}

/**
 * 
 * @param string $boundary - ����������� �������� � multipart/alternative
 * @return string
 */
protected function generateHtml()
{
    if( !isset($this->texthtml) || !strlen($this->texthtml)>0 )
    {
        return "";
    }

    $texthtml = $this->replaceCRLF( $this->texthtml, "<br />");
    
    // ���� ��� ������ �� �������� � ���� ������ <img src="cid:XXXXX"> � ������ �������������� XXXXX �� sha1(XXXXX)
    $matches = "";
    // ���� � �������� �������� ��� ������� ������ cid:����� ������� , 
    // ������� ����� �������� ���������� ����� ������� ���������� " ��� ' �������� ��������� - ����������� U
    preg_match_all("#['\"]cid:(.+)['\"]#U", $texthtml, $matches) ;
    foreach ($matches[1] as $findstr)
    {
        $texthtml = str_replace($findstr, sha1(trim($findstr) ), $texthtml);
    }

    $texthtml = chunk_split( base64_encode($texthtml) );

    $content = "";
    if( $this->ContentType != "text/html" )
    {
        $content = "Content-Type: text/html; charset=".TRMEMail::SendCoding." \r\n";
    //    $content .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit ������ ��� ��� ������������ � UTF-8 � ��� ������ ��������
        $content .= "Content-Transfer-Encoding: base64\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit ������ ��� ��� ������������ � UTF-8 � ��� ������ ��������
        $content .= $texthtml."\r\n";

        if( isset($this->inlineimages) && !empty($this->inlineimages))
        {
            $content .= $this->generateInlineImages();
        }
        return $content;
    }
    
    return $texthtml;
}

/**
 * 
 * @param string $str - ������ � ������� ��������� ��� �������� �����, ����� ������ - \r \n
 * @param string $substr - ������ �� ������� ����������, �� ��������� ������
 * @return string
 */
protected function replaceCRLF($str, $substr = ' ')
{
    $eol=array("\r\n", "\n", "\r");
    return str_replace($eol, $substr, $str); // ��� �������� ������ ��� �������� ����� ��������� � ���������� �� ������
}

/**
 * ��������� �����������, ������� ����� ������������ � HTML-������
 * 
 * @return string
 */
protected function generateInlineImages()
{
    if( empty($this->inlineimages) ) { return ''; }

    $boundary = "--REL_".$this->un;

    $content = "";
    foreach ($this->inlineimages as $filename)
    {
        $image_type = image_type_to_mime_type( exif_imagetype($filename) ); // image/gif ���...
        $f = fopen($filename,"rb");

        $basefilename = basename($filename); // ��������� ������ ��� ����� , ��� ����

        $content .= $boundary."\r\n";
        $content .= "Content-Type: {$image_type};\r\n"; // ��� ��� ��������
        $content .= " name=\"". $this->setStrToCoding($basefilename) ."\"\r\n";
        $content .= "Content-Transfer-Encoding: base64\r\n";
        $content .= "Content-ID: <". sha1( trim($basefilename) ) .">\r\n";
        $content .= "Content-Disposition: inline;\r\n"; // ��� �������� ������ ���� ������
        $content .= " filename=\"".$this->setStrToCoding($basefilename)."\"\r\n\r\n";
        // ��������� ���������� ����� � ������ base64 � ��������� �� ������ �� 76 ���� � ������������ � ������������ RFC 2045
        $content .= chunk_split( base64_encode( fread($f,filesize($filename)) )  )."\r\n";

        fclose ($f); 
    }

    return $content;
}

/**
 * ��������� ������������� �����
 * 
 * @return string
 */
protected function generateAttach()
{
    if( empty($this->filestosend) ) { return ''; }

    $boundary = "--".$this->un;

    $content = "";
    foreach ($this->filestosend as $filename)
    {
        $f = fopen($filename,"rb");

        $basefilename = basename($filename); // ��������� ������ ��� ����� , ��� ����

        $content .= $boundary."\r\n";
	$content .= "Content-Type: application/octet-stream;\r\n"; // ��� ��������������-��������� ������
        $content .= " name=\"".$basefilename."\"\r\n";
        $content .= "Content-Transfer-Encoding: base64\r\n";
	$content .= "Content-Disposition: attachment;\r\n"; // ��� ��������������-��������� ������
        $content .= " filename=\"".$basefilename."\"\r\n\r\n";
        // ��������� ���������� ����� � ������ base64 � ��������� �� ������ �� 76 ���� � ������������ � ������������ RFC 2045
        $content .= chunk_split( base64_encode( fread($f,filesize($filename)) )  )."\r\n";

        fclose ($f); 
    }
    return $content;
}

/**
 * ��������� ��������� �� ����������� ���� ��� �������� ������
 * 
 * @param boolean $strength - ��������� ������� ��������, ��� �� ��� � ���� ������ ������ � ����� ����� ��������� ��������
 * @return boolean
 */
protected function validate($strength = false)
{
    //� ������ ������� ���������� ��������� ����������
    if ( !isset($this->emailto) )
    {
        throw new TRMEMailWrongRecepientExceptions("�� ������");
    }
    if( !filter_var($this->emailto,FILTER_VALIDATE_EMAIL) )
    {
        throw new TRMEMailWrongRecepientExceptions($this->emailto);
    }
    //���� ����������� ������� ��������, �� ��������� ������������� ������������� ���� ������ � ����
    if($strength)
    {
        // ��� �� ����������, ��� �� ���� ������ ���� ���������
        if ( !isset($this->subject) )
        {
            throw new TRMEMailWrongThemeExceptions( __METHOD__ . " ������");
        }
        // ��������� ������������� ������ ������, ������ ���� ����� ���� �� ���� �� texthtml ��� textplain
        if ( (!isset($this->texthtml) || strlen($this->texthtml)==0) && (!isset($this->textplain) || strlen($this->textplain)==0) )
        {
            throw new TRMEMailWrongBodyExceptions( __METHOD__ . " ��������� ������");
        }
    }
    return true;
}

/**
 * ������������� e-mail �����������
 * 
 * @param type $emailfrom
 * @return string
 */
public function setEmailFrom($emailfrom)
{
    return $this->emailfrom = filter_var(trim($emailfrom),FILTER_VALIDATE_EMAIL );
}

/**
 * ������������� ��� �����������
 * 
 * @param string $name - ��� �����������
 */
public function setNameFrom($name)
{
    if(is_string($name) ) { $this->namefrom = $this->setStrCharset( strip_tags($name) ); }
}

/**
 * ������������� e-mail ����� ����������
 * 
 * @param string $emailto
 * @return string
 */
public function setEmailTo($emailto)
{
    return $this->emailto = filter_var(trim($emailto),FILTER_VALIDATE_EMAIL );
}

/**
 * ������������� ��� ����������
 * 
 * @param string $name - ��� �����������
 */
public function setNameTo($name)
{
    if(is_string($name) ) { $this->nameto = $this->setStrCharset( strip_tags($name) ); }
}

/**
 * ������������� e-mail ����� ���������� 2 (����� ������)
 * 
 * @param string $email��
 * @return string
 */
public function setEmailCc($email��)
{
    return $this->emailCc = filter_var(trim($email��),FILTER_VALIDATE_EMAIL );
}

/**
 * ������������� Reply-To - ���� �������� � ���������
 * 
 * @param type $email
 * @param type $name
 */
public function setReplyTo($email, $name)
{
    $this->emailreplyto = filter_var(trim($email),FILTER_VALIDATE_EMAIL );
    if(is_string($name) ) { $this->namereplyto = $this->setStrCharset( strip_tags($name) ); }
}

/**
 * ������������� ��������������� ��������� � ���������� ������ HTML
 * 
 * @param string $msg - ����� ���������, �������� � ������� HTML
 */
public function setMessage($msg)
{
    if( is_array($msg) ) { $this->texthtml = $this->setStrCharset( implode (' ', $msg) ); }
    else if( is_string($msg) ) { $this->texthtml = $this->setStrCharset( $msg ); }
}
/**
 * ��������� ��������������� ��������� � ���������� ������ HTML
 * 
 * @param string $msg - ����� ���������, �������� � ������� HTML
 */
public function addMessage($msg)
{
    if( is_array($msg) ) { $this->texthtml .= $this->setStrCharset( implode (' ', $msg) ); }
    else if( is_string($msg) ) { $this->texthtml .= $this->setStrCharset( $msg ); }
}

/**
 * ������������� ������� �����, ���  ���� HTML ��������� �� ������!
 * 
 * @param string $msg - ������� ����� ��������� ��� HTML-�����
 */
public function setTextPlain($msg)
{
    if( is_array($msg) ) { $this->textplain = $this->setStrCharset( strip_tags( implode (' ', $msg) ) ); }
    else if( is_string($msg) ) { $this->textplain = $this->setStrCharset( strip_tags($msg) ); }
}
/**
 * ��������� ������� �����, ���  ���� HTML ��������� �� ������!
 * 
 * @param string $msg - ������� ����� ��������� ��� HTML-�����
 */
public function addTextPlain($msg)
{
    if( is_array($msg) ) { $this->textplain .= $this->setStrCharset( strip_tags( implode (' ', $msg) ) ); }
    else if( is_string($msg) ) { $this->textplain .= $this->setStrCharset( strip_tags($msg) ); }
}

/**
 * ������������� ���� ���������
 * 
 * @param string $subj
 */
public function setSubject($subj)
{
    if( is_string($subj) )
    {
        $this->subject = $this->setStrCharset( $subj );
    }
}

/**
 * ��������� ���� ��� �������� � ��������������, ����������� ������������� ����� �� �������
 * 
 * @param string $filename
 * @return string|false
 */
public function addAttachment($filename)
{
    if(is_file($filename) ) { return $this->filestosend[] = $filename; }
    return false;
}

/**
 * ��������� �������� ��� ������� � ���� HTML-������, ����������� ������������� ����� �� �������
 * 
 * @param string $filename
 * @return string|false
 */
public function addInlineImages($filename)
{
    if( is_file($filename) ) { return $this->inlineimages[] = $filename; }
    return false;
}


/**
 * Encodes data to quoted-printable standard.
 *
 * @param string $input    The data to encode
 * @param int    $line_max Optional max line length. Should
 *                         not be more than 76 chars
 * @param string $eol      End-of-line sequence. Default: "\r\n"
 *
 * @return string Encoded data
 */
public static function quotedPrintableEncode($input , $line_max = 76, $eol = "\r\n")
{
    $lines  = preg_split("/\r?\n/", $input);
    $escape = '=';
    $output = '';

    while (list($idx, $line) = each($lines)) {
        $newline = '';
        $i = 0;

        while (isset($line[$i])) {
            $char = $line[$i];
            $dec  = ord($char);
            $i++;

            if (($dec == 32) && (!isset($line[$i]))) {
                // convert space at eol only
                $char = '=20';
            } 
            /*elseif ($dec == 9 && isset($line[$i]))
            {
                ; // Do nothing if a TAB is not on eol
            }*/ 
            elseif (($dec == 61) || ($dec < 32) || ($dec > 126)) {
                $char = $escape . sprintf('%02X', $dec);
            } elseif (($dec == 46) && (($newline == '')
                || ((strlen($newline) + strlen("=2E")) >= $line_max))
            ) {
                // Bug #9722: convert full-stop at bol,
                // some Windows servers need this, won't break anything (cipri)
                // Bug #11731: full-stop at bol also needs to be encoded
                // if this line would push us over the line_max limit.
                $char = '=2E';
            }

            // Note, when changing this line, also change the ($dec == 46)
            // check line, as it mimics this line due to Bug #11731
            // EOL is not counted
            if ((strlen($newline) + strlen($char)) >= $line_max) {
                // soft line break; " =\r\n" is okay
                $output  .= $newline . $escape . $eol;
                $newline  = '';
            }
            $newline .= $char;
        } // end of for
        $output .= $newline . $eol;
        unset($lines[$idx]);
    }
    // Don't want last crlf
    $output = substr($output, 0, -1 * strlen($eol));
    return $output;
} 

/**
 * ������������� ��������� E-Mail
 * 
 * @param string $filename - ����, ������� ���������� ������ � �����������
 * @return boolean
 * @throws TRMEMailExceptions
 */
public function setConfig( $filename )
{
    if( !is_file($filename) )
    {
        throw new TRMEMailExceptions( __METHOD__ . " ���� � ����������� �������� �� ������� [{$filename}]!" );
            //TRMLib::dp( __METHOD__ . " ���� � ����������� �������� �� ������� [{$filename}]!" );
            //return false;
    }
    $this->config = include($filename);

    if( !is_array($this->config) || empty($this->config) )
    {
        throw new TRMEMailExceptions( __METHOD__ . " ���� ������������ ������ �������� ������ ������ [{$filename}]!" );
            //TRMLib::dp( __METHOD__ . " ���� ������������ ������ �������� ������ ������ [{$filename}]!" );
            //return false;
    }
   
    if( isset($this->config["emailto"]) ) { $this->setEmailTo($this->config["emailto"]); }
    if( isset($this->config["nameto"]) ) { $this->setNameTo($this->config["nameto"]); }
    if( isset($this->config["cc"]) ) { $this->setEmailCc($this->config["cc"]); }

    if( isset($this->config["smtpemailfrom"]) ) { $this->setEmailFrom($this->config["smtpemailfrom"]); }
    if( isset($this->config["smtpnamefrom"]) ) { $this->setNameFrom($this->config["smtpnamefrom"]); }
    if( isset($this->config["charset"]) ) { $this->setCoding($this->config["charset"]); }
    if( isset($this->config["subject"]) ) { $this->setSubject($this->config["subject"]); }

    return true;
}


/**
 * �������� ������ ����� SMTP
 * 
 * @param string $mailTo - ���������� ������ - ������ ����� ���� - name$mail.com
 * @param string $subject - ���� ������ - � ���������� ��������� ��� �����������
 * @param string $message - ���� ������ - � ���������� ��������� ��� �����������
 * @param string $headers - ��������� ������ - � ���������� ��������� ��� �����������
 *
 * @return boolean - � ������ �������� ������ true, �����
 * @throws TRMEMailSendingExceptions
 */
protected function sendSMTP($mailTo, $subject, $message, $headers)
{
    $contentMail = "Date: " . date("D, d M Y H:i:s") . " UT\r\n"
                    ."Subject: {$subject}\r\n"
                    .$headers . "\r\n"
                    .$message . "\r\n.\r\n";

    $errorNumber = null;
    $errorDescription = "";

    if( !isset($this->config["smtphost"]) ||
        !isset($this->config["smtpport"]) ||
        !isset($this->config["login"]) ||
        !isset($this->config["password"]) )
    {
        throw new TRMEMailSendingExceptions("SMTP: �� ��� ��������� �����������");
    }


    if( !($socket = fsockopen($this->config["smtphost"], $this->config["smtpport"], $errorNumber, $errorDescription, 30)) )
    {
        throw new TRMEMailSendingExceptions($errorNumber.".".$errorDescription);
    }
    if (!$this->_parseServer($socket, "220"))
    {
        throw new TRMEMailSendingExceptions('Connection error');
    }

    $server_name = filter_input(INPUT_SERVER, "SERVER_NAME", FILTER_SANITIZE_STRING); //$_SERVER["SERVER_NAME"];
    fputs($socket, "HELO $server_name\r\n");
    if (!$this->_parseServer($socket, "250"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Error of command sending: HELO');
    }

    fputs($socket, "AUTH LOGIN\r\n");
    if (!$this->_parseServer($socket, "334"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Autorization error');
    }

    fputs($socket, base64_encode($this->config["login"]) . "\r\n");
    if (!$this->_parseServer($socket, "334"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Autorization error');
    }

    fputs($socket, base64_encode($this->config["password"]) . "\r\n");
    if (!$this->_parseServer($socket, "235"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Autorization error');
    }

    fputs($socket, "MAIL FROM: <".$this->config["login"].">\r\n");
    if (!$this->_parseServer($socket, "250"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Error of command sending: MAIL FROM');
    }

    fputs($socket, "RCPT TO: <" . $mailTo . ">\r\n");     
    if (!$this->_parseServer($socket, "250"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Error of command sending: RCPT TO');
    }

    fputs($socket, "DATA\r\n");     
    if (!$this->_parseServer($socket, "354"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions('Error of command sending: DATA');
    }

    fputs($socket, $contentMail);
    if (!$this->_parseServer($socket, "250"))
    {
        fclose($socket);
        throw new TRMEMailSendingExceptions("E-mail didn't sent");
    }

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}

/**
 * ������ �� ������ ������, ������ �� ������� ��� �� 256 ��������,
 * ���� �� ���������� ������, ��������������� �������:
 * ������ 3 ������� ������ ���� 3-� �������� ���� $response,
 * � �� ���� ������ ���� ������
 * 
 * @param type $socket - ����� �� �������� �������� ������
 * @param type $response - ����. ������� ������ ���� ��������
 * @return boolean - ���� � ������ ������� ������ � ������� ��������� ���� $response, � ����� ������, �� ������������ true, ����� false
 */
private function _parseServer($socket, $response)
{
    $responseServer = "";
    while( substr($responseServer, 3, 1) != ' ' )
    {
        if( !($responseServer = fgets($socket, 256)) ) { return false; }
    }
    if( !(substr($responseServer, 0, 3) == $response) ) { return false; }

    return true;
}


} // TRMEMail