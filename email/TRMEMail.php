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
 * все письма посылаются в кодировке "UTF-8"
 */
const SendCoding = "UTF-8";
/**
 * @var string - E-mail отправителя
 */
protected $emailfrom = "";
/**
 * @var string - имя отправителя
 */
protected $namefrom = "";
/**
 * @var string - E-mail получателя
 */
protected $emailto = "";
/**
 * @var string - имя получателя
 */
protected $nameto = "";
/**
 * @var string - E-mail получателя 2, копия письма
 */
protected $emailCc = "";
/**
 * @var string - имя получателя 2, копия письма
 */
protected $nameCc = "";
/**
 * @var string - Reply-To адрес, на который отправлять ответ
 */
protected $emailreplyto = "";
/**
 * @var string - Reply-To имя
 */
protected $namereplyto = "";
/**
 * @var string - тема сообщения
 */
protected $subject = "";

/**
 * @var string - текст письма без форматирования, будет отображаться как есть
 */
protected $textplain = "";
/**
 * @var string - текст письма в HTML - теги будут преобразованы в разметку
 */
protected $texthtml = "";
/**
 * @var string - кодировка, в которой формируется письмо, как правило на рускоязычных ресурсах - Windows-1251
 * оно потом преобразовывается в SendCoding = UTF-8 (если не менять)
 */
protected $coding;
/**
 * @var string - тип содержимого письма, вычисляется автоматически на основании отрправляемых блоков
 */
protected $ContentType;

/**
 * @var array массив полных имен файлов (с путем на сервере) для прикрепления к письму
 */
protected $filestosend = array();

/**
 * @var array картинки, которые будут показаны в теле письма
 */
protected $inlineimages = array();

/**
 * @var string  уникальная строка для разделения разделов письма
 */
protected $un;

/**
 *
 * @var array - массив с настройками почтового сервера
 */
protected $config = array();


/**
 * в конструкторе по умолчанию устанавливается кодировка входных данных в "Windows-1251"
 * так как мой сайт изначально работал в "Windows-1251"
 * затем эти данные (сообщение и заголовок) преобразуются в "UTF-8" для отправки
 * 
 * так же генерируется уникальная строка для разделения разделов
 */
public function __construct()
{
    $this->setCoding(); //"Windows-1251");
    $this->un = $this->getUniq();
}

/**
 * устанавливает кодировКу, 
 * в которой задаются сообщение и тема, 
 * само письмо всегда отправляется в UTF-8
 * 
 * @param string $coding кодировака, поумолчанию = SendCoding = UTF-8
 */
public function setCoding($coding = self::SendCoding)
{
    $this->coding = $coding;
}

/**
 * генерирует уникальную строку для добавления в начало раздела
 * @return string
 */
protected function getUniq()
{
    return strtoupper(uniqid(time()));
}

/**
 * отправляет сообщение в HTML-формате с вложениями
 * 
 * @return boolean
 */
public function sendEmail()
{
    $this->validate();
    
    //определяем какой тип в зависимости от установленных частей письма (наличие прикрепленных файлов, картинок, HTML или просто текст)
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
 * @param string $str - строка , которую нужно оформить в теме письма или в имени файда
 * оформляется в правильном формате base64 для кирилицы
 * 
 * @return string
 */
protected function setStrToCoding($str)
{
    if( !empty($str) )
    {
        // только UTF-8
        return ('=?'.strtolower(TRMEMail::SendCoding).'?B?'.base64_encode($str).'?=');
    }
    return '';
}

/**
 * преобразует содировку строки из заданной по умолчанию для работы с этим объектом письма,
 * в обязательную для отправки UTF-8,
 * если в настройках кодировки совпадают, то строка не изменится,
 * Внимание! начальная кодировка строки не проверяется.
 */
public function setStrCharset($str)
{
    $newstr = $str;
    // если кодировки не совпадают, то преобразуем 
    if($this->coding != TRMEMail::SendCoding )
    {
        $newstr = iconv($this->coding, TRMEMail::SendCoding, $str); // TRMLib::conv($str, $currentdatacharset, GlobalConfig::$ConfigArray["Charset"]);
    }
    return $newstr;
}

/**
 * формирует тему письма
 * 
 * @return string
 */
protected function generateSubject()
{
    return $this->setStrToCoding($this->subject);
}

/**
 * формирует заголовок письма и возвращаем в виде строки!
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
        $head .= "Content-Type: multipart/mixed; boundary=\"".$this->un."\"\r\n"; //multipart/mixed;\r\n"  // письмо состоит из нескольких разных частей - multipart/mixed;
    }
    elseif( $this->ContentType == "multipart/alternative" )
    {
        $head .= "Content-Type: multipart/alternative; boundary=\"ALT_".$this->un."\"\r\n"; //multipart/mixed;\r\n"  // письмо состоит из нескольких разных частей - multipart/mixed;
    }
    elseif( $this->ContentType == "multipart/related" )
    {
        $head .= "Content-Type: multipart/related; boundary=\"REL_".$this->un."\"\r\n"; //multipart/mixed;\r\n"  // письмо состоит из нескольких разных частей - multipart/mixed;
    }
    elseif( $this->ContentType == "text/html" || $this->ContentType == "text/plain" )
    {
        $head .= "Content-Type: {$this->ContentType}; charset=".TRMEMail::SendCoding." \r\n"."Content-Transfer-Encoding: base64 \r\n"; //multipart/mixed;\r\n"  // письмо состоит из нескольких разных частей - multipart/mixed;
    }

    return $head;
}

/**
 * определяет тип контента письма по содержанию в нем прикрепленных файлов, Html или простого текста...
 * 
 * @return string|boolean - если удалось определить тип содержимого письма, 
 * то вернется строка в формате типа - text/html, иначе false
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
 * формирует тело сообщения из двух часте - text/plain и text/html для отображения, соответственно как обычного текста и в виде HTML
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
            $content .= "Content-Type: multipart/alternative; boundary=\"ALT_".$this->un."\"\r\n\r\n"; // текст в формате HTML и альтернативном просто plain/text - multipart/mixed;
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


    return $content; // разбиваем сообщение на строки по 76 символов , в конце каждой добавляется \r\n
}

/**
 * генерирует часть письма multipart/related,
 * оно появляется, если тело представляет HTML и внутри есть ссылки на локальные (прикрепленные) картинки
 * 
 * @return string
 */
protected function generateRelated()
{
    if( empty($this->inlineimages) )
    {
        return $this->generateHtml();
    }
    // этот код выполнится, если массив картинок не пустой
    // но картинки в теле письма могут появится только если HTML тело не пустое, иначе на них просто никто не ссылается
    
    if( isset($this->texthtml) && strlen($this->texthtml)>0 )
    {
        $content = "";
        if( $this->ContentType != "multipart/related" )
        {
            $content = "Content-Type: multipart/related; boundary=\"REL_".$this->un."\"\r\n\r\n"; // текст в формате HTML и альтернативном просто plain/text - multipart/mixed;
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
 * @param string $boundary - разделитель разделов в multipart/alternative
 * @return string
 */
protected function generateTextPlain()
{
    if( !isset($this->textplain) || !strlen($this->textplain)>0 )
    {
        return "";
    }
    
    //$text = strip_tags($text); //убираем в обычном тексте все HTML-теги
//    $text = $this->setStrToCoding($text);
/*
    if($this->coding != TRMEMail::SendCoding)
        $text = iconv($this->coding, TRMEMail::SendCoding, $text); // преобразуем из Windows-1251 в UTF-8
 * 
 */
    $text = chunk_split( base64_encode( $this->replaceCRLF( $this->textplain ) ) );
//    $text = wordwrap($text,70,"\r\n"); // каждая строка в собщении должна быть не длиннее 70 символов
//    $text = self::quotedPrintableEncode($text);
    
    if( $this->ContentType != "text/plain" )
    {
        $content = "Content-Type: text/plain; charset=".TRMEMail::SendCoding." \r\n";
    //    $content .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit потому что уже преобразован в UTF-8 и там только латиница
        $content .= "Content-Transfer-Encoding: base64\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit потому что уже преобразован в UTF-8 и там только латиница
        $content .= $text."\r\n";
        return $content;
    }
    return $text;
}

/**
 * 
 * @param string $boundary - разделитель разделов в multipart/alternative
 * @return string
 */
protected function generateHtml()
{
    if( !isset($this->texthtml) || !strlen($this->texthtml)>0 )
    {
        return "";
    }

    $texthtml = $this->replaceCRLF( $this->texthtml, "<br />");
    
    // Ищем все ссылки на картинки в теле письма <img src="cid:XXXXX"> и меняем идентификаторы XXXXX на sha1(XXXXX)
    $matches = "";
    // ищем в кавычках одиарных или двойных строку cid:любые символы , 
    // перебор любых символов закончится после первого нахождения " или ' жадность отключена - модификатор U
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
    //    $content .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit потому что уже преобразован в UTF-8 и там только латиница
        $content .= "Content-Transfer-Encoding: base64\r\n\r\n"; // 7bit\r\n\r\n"; // 7bit потому что уже преобразован в UTF-8 и там только латиница
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
 * @param string $str - строка в которой удаляются все переводы строк, новая строка - \r \n
 * @param string $substr - строка на которую заменяются, по умолчанию пробел
 * @return string
 */
protected function replaceCRLF($str, $substr = ' ')
{
    $eol=array("\r\n", "\n", "\r");
    return str_replace($eol, $substr, $str); // для обычного текста все переводы строк удаляются и заменяются на пробел
}

/**
 * формирует изображения, которые будут отображаться в HTML-письме
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
        $image_type = image_type_to_mime_type( exif_imagetype($filename) ); // image/gif или...
        $f = fopen($filename,"rb");

        $basefilename = basename($filename); // оставляем только имя файла , без пути

        $content .= $boundary."\r\n";
        $content .= "Content-Type: {$image_type};\r\n"; // это для картинки
        $content .= " name=\"". $this->setStrToCoding($basefilename) ."\"\r\n";
        $content .= "Content-Transfer-Encoding: base64\r\n";
        $content .= "Content-ID: <". sha1( trim($basefilename) ) .">\r\n";
        $content .= "Content-Disposition: inline;\r\n"; // для картинок внутри тела письма
        $content .= " filename=\"".$this->setStrToCoding($basefilename)."\"\r\n\r\n";
        // переводим содержимое файла в формат base64 и разбиваем на строки по 76 байт в соответствие с требованиями RFC 2045
        $content .= chunk_split( base64_encode( fread($f,filesize($filename)) )  )."\r\n";

        fclose ($f); 
    }

    return $content;
}

/**
 * формируем прикрепленные файлы
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

        $basefilename = basename($filename); // оставляем только имя файла , без пути

        $content .= $boundary."\r\n";
	$content .= "Content-Type: application/octet-stream;\r\n"; // для присоединенных-вложенных файлов
        $content .= " name=\"".$basefilename."\"\r\n";
        $content .= "Content-Transfer-Encoding: base64\r\n";
	$content .= "Content-Disposition: attachment;\r\n"; // для присоединенных-вложенных файлов
        $content .= " filename=\"".$basefilename."\"\r\n\r\n";
        // переводим содержимое файла в формат base64 и разбиваем на строки по 76 байт в соответствие с требованиями RFC 2045
        $content .= chunk_split( base64_encode( fread($f,filesize($filename)) )  )."\r\n";

        fclose ($f); 
    }
    return $content;
}

/**
 * проверяет заполнены ли необходимые поля для отправки письма
 * 
 * @param boolean $strength - указывает строгая проверуа, или же нет и тело письма вместе с темой можно оставлять пустывми
 * @return boolean
 */
protected function validate($strength = false)
{
    //в первую очередь необходимо проверить получателя
    if ( !isset($this->emailto) )
    {
        throw new TRMEMailWrongRecepientExceptions("Не указан");
    }
    if( !filter_var($this->emailto,FILTER_VALIDATE_EMAIL) )
    {
        throw new TRMEMailWrongRecepientExceptions($this->emailto);
    }
    //если установлена строгая проверка, то проверяем дополнительно заполненность тела письма и темы
    if($strength)
    {
        // так же необходимо, что бы тема письма была заполнена
        if ( !isset($this->subject) )
        {
            throw new TRMEMailWrongThemeExceptions( __METHOD__ . " пустая");
        }
        // проверяем заполненность текста письма, должен быть задан хотя бы один из texthtml или textplain
        if ( (!isset($this->texthtml) || strlen($this->texthtml)==0) && (!isset($this->textplain) || strlen($this->textplain)==0) )
        {
            throw new TRMEMailWrongBodyExceptions( __METHOD__ . " сообщение пустое");
        }
    }
    return true;
}

/**
 * устанавливает e-mail отправителя
 * 
 * @param type $emailfrom
 * @return string
 */
public function setEmailFrom($emailfrom)
{
    return $this->emailfrom = filter_var(trim($emailfrom),FILTER_VALIDATE_EMAIL );
}

/**
 * устанавливает имя отправителя
 * 
 * @param string $name - имя отправителя
 */
public function setNameFrom($name)
{
    if(is_string($name) ) { $this->namefrom = $this->setStrCharset( strip_tags($name) ); }
}

/**
 * устанавливает e-mail адрес получателя
 * 
 * @param string $emailto
 * @return string
 */
public function setEmailTo($emailto)
{
    return $this->emailto = filter_var(trim($emailto),FILTER_VALIDATE_EMAIL );
}

/**
 * устанавливает имя получаиеля
 * 
 * @param string $name - имя отправителя
 */
public function setNameTo($name)
{
    if(is_string($name) ) { $this->nameto = $this->setStrCharset( strip_tags($name) ); }
}

/**
 * устанавливает e-mail адрес получателя 2 (копия письма)
 * 
 * @param string $emailсс
 * @return string
 */
public function setEmailCc($emailсс)
{
    return $this->emailCc = filter_var(trim($emailсс),FILTER_VALIDATE_EMAIL );
}

/**
 * устанавливает Reply-To - кому отвечать в сообщении
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
 * устанавливает форматированное сообщение с возможными тегами HTML
 * 
 * @param string $msg - текст сообщения, возможно в формате HTML
 */
public function setMessage($msg)
{
    if( is_array($msg) ) { $this->texthtml = $this->setStrCharset( implode (' ', $msg) ); }
    else if( is_string($msg) ) { $this->texthtml = $this->setStrCharset( $msg ); }
}
/**
 * добавляет форматированное сообщение с возможными тегами HTML
 * 
 * @param string $msg - текст сообщения, возможно в формате HTML
 */
public function addMessage($msg)
{
    if( is_array($msg) ) { $this->texthtml .= $this->setStrCharset( implode (' ', $msg) ); }
    else if( is_string($msg) ) { $this->texthtml .= $this->setStrCharset( $msg ); }
}

/**
 * устанавливает простой текст, все  теги HTML убираются из текста!
 * 
 * @param string $msg - простой текст сообщения без HTML-тегов
 */
public function setTextPlain($msg)
{
    if( is_array($msg) ) { $this->textplain = $this->setStrCharset( strip_tags( implode (' ', $msg) ) ); }
    else if( is_string($msg) ) { $this->textplain = $this->setStrCharset( strip_tags($msg) ); }
}
/**
 * добавляет простой текст, все  теги HTML убираются из текста!
 * 
 * @param string $msg - простой текст сообщения без HTML-тегов
 */
public function addTextPlain($msg)
{
    if( is_array($msg) ) { $this->textplain .= $this->setStrCharset( strip_tags( implode (' ', $msg) ) ); }
    else if( is_string($msg) ) { $this->textplain .= $this->setStrCharset( strip_tags($msg) ); }
}

/**
 * устанавливаем тему сообщения
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
 * добавляем файл для отправки в присоединенных, проверяется существование файла на сервере
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
 * добавляем картинку для вставки в тело HTML-письма, проверяется существование файла на сервере
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
 * устанавливает настройки E-Mail
 * 
 * @param string $filename - файл, который фозвращает массив с настройками
 * @return boolean
 * @throws TRMEMailExceptions
 */
public function setConfig( $filename )
{
    if( !is_file($filename) )
    {
        throw new TRMEMailExceptions( __METHOD__ . " Файл с настройками получить на удалось [{$filename}]!" );
            //TRMLib::dp( __METHOD__ . " Файл с настройками получить на удалось [{$filename}]!" );
            //return false;
    }
    $this->config = include($filename);

    if( !is_array($this->config) || empty($this->config) )
    {
        throw new TRMEMailExceptions( __METHOD__ . " Файл конфигурации вернул неверный формат данных [{$filename}]!" );
            //TRMLib::dp( __METHOD__ . " Файл конфигурации вернул неверный формат данных [{$filename}]!" );
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
 * Отправка письма через SMTP
 * 
 * @param string $mailTo - получатель письма - только почта вида - name$mail.com
 * @param string $subject - тема письма - в правильной кодировке для отправления
 * @param string $message - тело письма - в правильной кодировке для отправления
 * @param string $headers - заголовки письма - в правильной кодировке для отправления
 *
 * @return boolean - в случае отправки вернет true, иначе
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
        throw new TRMEMailSendingExceptions("SMTP: Не все настройки установлены");
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
 * читает из сокета данные, строка за строкой или по 256 символов,
 * пока не встретится строка, удовлетворяющая условию:
 * первые 3 символа должны быть 3-х цифровым кодм $response,
 * а за ними должен идти пробел
 * 
 * @param type $socket - сокет из которого читаются данные
 * @param type $response - кодк. который должен быть прочитан
 * @return boolean - если в сокете найдена строка с первыми символами кода $response, а затем пробел, то возвращается true, иначе false
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