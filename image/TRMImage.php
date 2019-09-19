<?php

namespace TRMEngine\Image;

use TRMEngine\Helpers\TRMLib;
use TRMEngine\Image\Exceptions\TRMImageNoDestImageException;
use TRMEngine\Image\Exceptions\TRMImageWrongBMPException;
use TRMEngine\Image\Exceptions\TRMImageWrongGIFException;
use TRMEngine\Image\Exceptions\TRMImageWrongJPEGException;
use TRMEngine\Image\Exceptions\TRMImageWrongPNGException;
use TRMEngine\Image\Exceptions\TRMImageWrongWBMPException;

/**
 * класс для работы с изображениями
 * 
 * @author TRM 2018
 */
class TRMImage
{
const DEFAULT_PNG_QUALITY = 7;
const DEFAULT_JPEG_QUALITY = 90;
/**
 * @var int - IMAGETYPE_GIF, IMAGETYPE_JPEG... - тип для файла с исходным изображением
 */
public $ImageType;
/**
 * @var int - IMAGETYPE_GIF, IMAGETYPE_JPEG... - тип для формируемого изображения 
 */
public $DestImageType;
/**
 * @var int - ширина исходной картинки
 */
public $Width;
/**
 * @var int - высота исходной картинки
 */
public $Height;
/**
 * @var int - максимально возможная ширина формируемого изображения, может быть меньше, если соотношение сторон будет отличаться от исходного
 */
public $MaxW;
/**
 * @var int - максимально возможная высота формируемого изображения, может быть меньше, если соотношение сторон будет отличаться от исходного
 */
public $MaxH;
/**
 * @var int - качество записываемого изображения для jpeg, Степень сжатия png
 */
public $DestImageQuality;
/**
 * @var string - полный путь к исходному файлу с изображением
 */
public $SrcFullPath;
/**
 * @var string - только имя нового файла без расширения и без каталога !!!
 */
public $DestFileName;
/**
 * @var string - каталог внутри web-сервера, куда сохраняются картинки
 */
public $DestCatalog;
/**
 * @var string - добавляется в конец имени файла для уникализации, как правило это идентификатор записи из БД, лидо рандомноое число в виде строки
 */
public $DestNamePostfix;
/**
 * @var string - префикс для формируемого имени файла
 */
public $DestNamePrefix;
/**
 * @var resource - ресурс исходного рисунка
 */
public $SrcImage;
/**
 * @var resource - ресурс для формируемого рисунка
 */
public $DestImage;
    
/**
 * получает изображение из файла $filename 
 * и сохраняет ресурс в $this->SrcImage, 
 * а так же параметры изображения - высоту, ширину, тип
 * 
 * @param string $filename
 * @return boolean
 */
public function getImageFromFile($filename)
{
    if(!file_exists($filename))
    {
        return false;
    }
    $ImageParam = getimagesize($filename);//определяем размер рисунка и тип файла

    if(!$ImageParam) { return false; }
    
    $this->Width = $ImageParam[0];
    $this->Height = $ImageParam[1];
    $this->ImageType = $ImageParam[2];
    
    $this->SrcImage = null;

    switch($this->ImageType)
    {
        case IMAGETYPE_BMP: $this->SrcImage = imagecreatefrombmp($filename); break;
        case IMAGETYPE_GIF: $this->SrcImage = imagecreatefromgif($filename); break;
        case IMAGETYPE_JPEG: $this->SrcImage = imagecreatefromjpeg($filename); break;
        case IMAGETYPE_PNG: $this->SrcImage = imagecreatefrompng($filename); break;
        case IMAGETYPE_WBMP: $this->SrcImage = imagecreatefromwbmp($filename); break;
        default: 
            throw new TRMImageExceptions("Тип изображения [{$this->ImageType}] не поддерживается, файл  [{$filename}]");
    }
    if( !$this->SrcImage ) { return false; }
    
    return true;
}

/**
 * создает ресурс новго изображения с новыми размерами и типом, 
 * учитывается прозрачность, 
 * если формируемое изображение имеет тип с поддержкой прозрачности GIF , PNG...
 * функция не учитывает масштаб, если отношение стороно будет не такое как в исходном рисунке,
 * поэтому ее лучше вызывать после расчета ширины и высоты относительно исходного изображения
 * 
 * @param int $Width - ширина нового изображения
 * @param int $Height - высота нового изображения
 * @param int $DestImageType - тип нового изображения - IMAGETYPE_GIF, IMAGETYPE_JPEG...
 * @return boolean - в случае упеха true, если сформировать не удалось - false
 */
private function generateDestImageFromSrc($Width=0, $Height=0, $DestImageType=IMAGETYPE_JPEG )
{
    $this->MaxW = $Width>0 ? $Width : $this->Width;
    $this->MaxH = $Height>0 ? $Height : $this->Height;
    $this->DestImageType = $DestImageType;
    if(!$this->SrcImage)
    {
        TRMLib::dp( __METHOD__ . " Не сформировано исходное изображение SrcImage" );
        return false;
    }
    
    $this->DestImage = null;
    
    $this->DestImage = imagecreatetruecolor($Width, $Height); // создаем пустой рисунок $Width x $Height
    $TransparentFlag = false; // указывает установлен или нет прозрачный цвет 

    if($DestImageType === IMAGETYPE_PNG)// для прозрачного PNG
    {
            imagealphablending($this->DestImage, false); //Отключаем режим сопряжения цветов
            imagesavealpha($this->DestImage, true); //Включаем сохранение альфа канала
            $TransparentFlag = true;
    }
    else if($DestImageType === IMAGETYPE_GIF)// для GIF
    {
        $transparent_source_index=imagecolortransparent($this->SrcImage);

        //Проверяем наличие прозрачности
        if($transparent_source_index!==-1)
        {
            //Получаем прозрачный цвет исходного изображения
            $transparent_color=imagecolorsforindex($this->SrcImage, $transparent_source_index);
            //Добавляем цвет в палитру нового изображения, и устанавливаем его как прозрачный
            $transparent_destination_index=imagecolorallocate($this->DestImage, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
            //На всякий случай заливаем фон этим цветом
            imagefill($this->DestImage, 0, 0, $transparent_destination_index);
            //устанавливаем полученный цвет как прозрачный
            imagecolortransparent($this->DestImage, $transparent_destination_index);
            $TransparentFlag = true;
        }            
    }

    // если прозрачный цвет не установлен заливаем все белым
    if( !$TransparentFlag )
    {
        //создаем белый цвет
        $white = imagecolorallocatealpha($this->DestImage, 0xff, 0xff, 0xff, 0); // создаем белый цвет с alph-каналом, 0 - совсем не прозрачный (127 - полностью прозрачный)!!!
        // $white = imagecolorallocate($this->DestImage, 0xff, 0xff, 0xff); // создаем белый цвет без альфа-слоя
        // заполняем пустое изображение белым
        imagefill($this->DestImage, 0, 0, $white); //заливка белым цветом

        imagecolortransparent($this->DestImage, $white); // добавляем в новое изображение белый как прозрачный
//        imagecopyresampled($this->DestImage, $this->SrcImage, 0, 0, 0, 0, $Width, $Height, $this->Width, $this->Height);// скопируем исходный с изменением размера
    }
    return imagecopyresampled($this->DestImage, $this->SrcImage, 0, 0, 0, 0, $Width, $Height, $this->Width, $this->Height);// скопируем исходный с изменением размера
}


/**
 * расчитывает правильное отношение сторон для нового рисунка 
 * и формирует новое изображение DestImage
 * 
 * @param int $MaxW - максимально возможная ширина нового изображения, если =0, то берется размер исходного
 * @param int $MaxH - максимально возможная высота нового изображения, если =0, то берется размер исходного
 * @param int $ImageType - тип нового изображения, по умолчанию IMAGETYPE_JPG...
 * 
 * @return boolean - в случае упеха true, если сформировать не удалось - false
 */
public function generateNewSizeImage( $MaxW=0, $MaxH=0, $ImageType = IMAGETYPE_JPEG )
{
    if( !$this->Width || !$this->Height )
    {
        return false;
    }
    $sizecoeffx=1;
    $sizecoeffy=1;
    $newsizecoeffx = 1;
    $newsizecoeffy = 1;
    
    // если $MaxW не задана, 
    // или запрашивается формирование рисунка большей, чем исходная ширина
    // сохраняем исходну, растянуть изображение не получится
    if( $MaxW == 0 || $MaxW > $this->Width ) { $MaxW = $this->Width; }
    // иначер рассчитываем соотошение - 
    // отношение формируемой и исходной ширины
    else { $newsizecoeffx = ( $MaxW / $this->Width ); }
    // если запрашивается формирование рисунка большей, чем исходная высота
    if( $MaxH == 0 || $MaxH > $this->Height ) { $MaxH = $this->Height; }
    //  иначер рассчитываем соотошение - 
    // отношение формируемой и исходной высоты
    else { $newsizecoeffy = ( $MaxH / $this->Height ); }
    
    // берем максимальный размер из ширины или высоты исходного
    $Max = max($this->Width, $this->Height);
    // умножаем его на минимальное отношение формируемой и исходной сторон
    $MaxReSized = $Max * min ($newsizecoeffx, $newsizecoeffy); 
    
    // вычисляем коэффициент масштабирования для каждой стороны рисунка
    // и рассчитываем новые размеры
    // 
    // если высота исходного изображения больше ширины исходного, 
    // тогда используем отношение ширины к высоте
    // для вычисления коэффицента маштабирования ширины результирующего изображения
    if($this->Height > $this->Width) { $sizecoeffx = $this->Width / $this->Height; }
    // иначе, если ширина исходного изображения больше высоты исходного, 
    // тогда используем отношение высоты к ширине 
    // для вычисления коэффицента маштабирования высоты результирующего изображения
    else { $sizecoeffy = $this->Height / $this->Width; }

    // получаем корректное значение ширины и высоты результирующего изображения,
    // при этом значения не превышают $MaxW и $MaxH, соответсвенно
    $x1 = round($MaxReSized*$sizecoeffx);
    $y1 = round($MaxReSized*$sizecoeffy);

    return $this->generateDestImageFromSrc($x1, $y1, $ImageType);
}

/**
 * сохраняет сформированное изображение в файл, 
 * каталог для файла, префикс и постфикс к имени должны задаваться заранее !!!
 * расширение формируется в зависимости от типа сформированного изображения
 * 
 * @param string $filename
 */
public function saveDestImageToFile($filename)
{
    if( !$this->DestImage )
    {
        throw new TRMImageNoDestImageException( __METHOD__ );
    }
    
    $this->DestFileName = $filename;
    
    $FullPath = rtrim($this->DestCatalog, "/") . "/" . $this->DestNamePrefix . $filename . $this->DestNamePostfix;
    
    switch($this->DestImageType)
    {
        case IMAGETYPE_BMP:  
            if( !imagebmp($this->DestImage, $FullPath.".bmp") ) 
            { throw new TRMImageWrongBMPException( __METHOD__ . " - " . $FullPath ); } 
            break;
        case IMAGETYPE_GIF:  
            if( !imagegif($this->DestImage, $FullPath.".gif") ) 
            { throw new TRMImageWrongGIFException( __METHOD__ . " - " . $FullPath ); } 
            break;
        case IMAGETYPE_PNG:  
            if( !$this->DestImageQuality ) { $this->DestImageQuality = 7; }
            if( !imagepng($this->DestImage, $FullPath.".png", $this->DestImageQuality) ) 
            { throw new TRMImageWrongPNGException( __METHOD__ . " - " . $FullPath ); } 
            break;
        case IMAGETYPE_WBMP: 
            if( !imagewbmp($this->DestImage, $FullPath.".wbmp") )
            { throw new TRMImageWrongWBMPException( __METHOD__ . " - " . $FullPath ); } 
            break;
//        case IMAGETYPE_JPEG: if(!imagejpeg($this->DestImage, $FullPath.".jpg")) TRMLib::dp( __METHOD__ . " не могу сформировать JPEG-картинку {$FullPath}."); break;
        default : 
            if( !$this->DestImageQuality ) { $this->DestImageQuality = 90; }
            if( !imagejpeg($this->DestImage, $FullPath.".jpg", $this->DestImageQuality) ) 
            { throw new TRMImageWrongJPEGException( __METHOD__ . " - " . $FullPath ); } 
    }
    return true;
}

/**
 * сохраняет исходное изображение в файл,
 * таким образом можно поменять тип файла изображения без изменения размера.
 * каталог для файла, префикс и постфикс к имени должны задаваться заранее !!!
 * расширение формируется в зависимости от заданного типа изображения
 * 
 * @param string $filename
 */
function saveSrcImageToFile($filename)
{
    if( !$this->SrcImage )
    {
        TRMLib::dp( __METHOD__ . " Не сформировано изображение SrcImage" );
        return false;
    }
    
    $FullPath = "/" . trim($this->DestCatalog, "/") 
            . "/" . $this->DestNamePrefix . $filename . $this->DestNamePostfix;
    
    switch($this->ImageType)
    {
        case IMAGETYPE_BMP:  
            if( !imagebmp($this->SrcImage, $FullPath.".bmp")) 
            {
                throw new TRMImageWrongBMPException( __METHOD__ . " не могу сформировать  BMP-картинку {$FullPath}.");
            } 
            break;
        case IMAGETYPE_GIF:  
            if( !imagegif($this->SrcImage, $FullPath.".gif")) 
            {
                throw new TRMImageWrongGIFException( __METHOD__ . " не могу сформировать  GIF-картинку {$FullPath}.");
            } 
            break;
        case IMAGETYPE_PNG: 
            if( !$this->DestImageQuality) { $this->DestImageQuality = self::DEFAULT_PNG_QUALITY; } 
            if( !imagepng($this->SrcImage, $FullPath.".png", $this->DestImageQuality) ) 
            {
                throw new TRMImageWrongPNGException( __METHOD__ . " не могу сформировать  PNG-картинку {$FullPath}.");
            }
            break;
        case IMAGETYPE_WBMP: 
            if( !imagewbmp($this->SrcImage, $FullPath.".wbmp")) 
            {
                throw new TRMImageWrongWBMPException( __METHOD__ . " не могу сформировать WBMP-картинку {$FullPath}.");
            }
            break;
//        case IMAGETYPE_JPEG: if(!imagejpeg($this->DestImage, $FullPath.".jpg")) TRMLib::dp( __METHOD__ . " не могу сформировать JPEG-картинку {$FullPath}."); break;
        default : 
            if( !$this->DestImageQuality ) { $this->DestImageQuality = self::DEFAULT_JPEG_QUALITY; } 
            if( !imagejpeg($this->SrcImage, $FullPath.".jpg", $this->DestImageQuality) ) 
            {
                throw new TRMImageWrongJPEGException( __METHOD__ . " не могу сформировать JPEG-картинку {$FullPath}.");
            }
    }
    return true;
}

/**
 * @return string - возвращает расширение 
 * для сформированного файла изображения без точки , например "jpg"
 */
public function getDestExt()
{
    switch($this->DestImageType)
    {
        case IMAGETYPE_BMP:  return "bmp";
        case IMAGETYPE_GIF:  return "gif";
        case IMAGETYPE_PNG:  return "png";
        case IMAGETYPE_WBMP: return "wbmp";
    }
    return "jpg";
}


} // class TRMImage