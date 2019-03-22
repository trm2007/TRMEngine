<?php

namespace TRMEngine\View;

use TRMEngine\PathFinder\TRMPathFinder;

/**
 * Базовый класс вида
 */
class TRMView
{
/**
 * DefaultLayoutName string - имя шаблона по умолчанию
 */
const DefaultLayoutName = "default";
/**
 * DefaultViewName string - имя вида по умолчанию
 */
const DefaultViewName = "default";

/**
 * @var string - путь к файлу с видом
 */
protected $PathToViews = "";
/**
 * @var string - путь к файлу с шаблоном
 */
protected $PathToLayouts = "";
/**
 * @var string  только имя вида для отображения основных данных, без адреса папок и без расширения
 */
protected $ViewName = "";
/**
 * @var string имя макета страницы так же без расширениея и без адреса папок
 */
protected $LayoutName = "";
/**
 * @var array мета-теги Title, Description, keywords....
 */
protected $MetaTags = array();
/**
 * @var string - заголовок
 */
protected $Title = "";
/**
 * @var string - код текущего языка для вида
 */
protected $Lang = "ru";
/**
 * @var string 
 */
protected $Canonical = "";
/**
 * @var string - адрес favicon
 */
protected $Favicon = "";
/**
 * @var array - массив с адресами CSS,
 * адрес файла со стилями заносится в индекс массива, а в значении хранится true - стиль подключается в head, или false - добавляются в конце вида
 */
protected $CSSArray = array();
/**
 * @var array - массив с адресами JS, 
 * адрес файла со скриптов заносится в индекс массива, а в значении хранится true - скрипт для части head, или false - добавляются в конце вида
 */
protected $JSArray = array();
/**
 * @var array - массив переменных, которые заполняются методом setVars();
 */
protected $Vars = array();
/**
 * @var array - массив строк-текста с контентом, могут использоваться в виде как автономные жлементы, в функции render не учавствуют
 */
protected $Contents = array();
/**
 * @var sting - скомпилированный контент в виде строки
 */
protected $FullContent = "";

/**
 * @var sting скрипты найденные в тексте в тегах <script.*>...</script>, 
 * объединенные в один блок, заполняется функцией cutScripts,
 * как правило используется для размещения скриптов в конце
 */
protected $Scripts = "";

/**
 *
 * @var boolean - эсли установлен в true, 
 * тогда все скрипты из текста страницы перед отображением вырезаются
 * и перемещаются вниз перед закрывающимся </body>,
 * поумолчанию включен = trueэтого, что бы ускорить ответ сервера - флаг = false
 */
public $ScriptsToEndFlag = true; // false; // 

/**
 * все имена файлов-видов/шаблонов и папок-путей для ниъ только в нижнем регистре !!!
 * 
 * @param string $view - имя вида, без пути, без расширения, только имя, путь и расширение ".php" подставляется автоматически
 * @param string $layout - имя макета-шаблона, так же без пути и расширения, если явно установлен в === null - то не подключается, 
 * используется для AJAX-запросов, если пустая строка, то подключается шаблон по умолчанию == default
 */
public function __construct( $view="", $layout="" )
{
    if( empty($view) )
    {
        if( class_exists("TRMEngine\PathFinder\TRMPathFinder")  )
        {
            $this->ViewName = isset(TRMPathFinder::$CurrentPath["action"]) ? strtolower(TRMPathFinder::$CurrentPath["action"]) : TRMPathFinder::DefaultActionName;
        }
        else
        {
            $this->ViewName = self::DefaultViewName;
        }
    }
    else
    {
        $this->ViewName = strtolower( trim($view) );
    }
    
    if(null === $layout) { $this->LayoutName = null; }
    else { $this->LayoutName= strlen($layout)>0 ? strtolower(trim($layout)) : self::DefaultLayoutName; }
}
	
/**
 * основная функция вывода содержимого вида в поток для отправки клиенту посредством echo...
 */
public function render()
{
    // преобразуется __toString
    echo $this;
}
/**
 * возвращает весь контент в виде строки
 * 
 * @return string
 */
public function __toString()
{
	$viewfilepath = $this->PathToViews . "/" . $this->ViewName.".php";
        if( !is_file($viewfilepath) )
        {
            return "";
            
            // __toString не может выбрасывать исключения, должна возвращатьтолько строку, пустть и пустую ""
            // throw new \Exception( "Не найден Вид {$viewfilepath}!", 500 );
        }

	if(!empty($this->Vars) ) // перед выводом Html получим все переменные из массива
        {
            extract($this->Vars);
        }

        // перед выводом Html получим все мета-теги в переменные из массива
//        if(!empty($this->MetaTags) ) { extract($this->MetaTags); }
	
        // перед выводом Html получим все части контента в переменные из массива
//        if( is_array($this->Contents) ) { extract($this->Contents); }

        
	// в виде выводится основное содержимое, без шаблона (как правило без заголовка и без футера)
        if( null === $this->LayoutName )
        {
            ob_start();
            require $viewfilepath;
            $this->FullContent = ob_get_clean();
        }
        else
        {
            // если задан макет, то отправляем через TRMContent в него Html-код, полученный из основного вида
            $LayoutFile = $this->PathToLayouts . "/" . $this->LayoutName.".php";
            if( !is_file($LayoutFile) ) { return ""; } // throw new \Exception( "Не найден макет {$LayoutFile}!" ); }
            ob_start();
            require $viewfilepath;
            // сохраняем вывод
            // $TRMContent - используется как основной контент внутри шаблонов
            $TRMContent = ob_get_clean();

            ob_start();
            require $LayoutFile;
            $this->FullContent = ob_get_clean();

            if( $this->ScriptsToEndFlag )
            {
                $this->cutScripts();
                if( false === strpos($this->FullContent, "</body>") ) { $this->FullContent .= $this->Scripts;  }
                else { $this->FullContent = str_replace("</body>", $this->Scripts . "</body>", $this->FullContent); }
            }
	} // else = if layout
        return $this->FullContent;
} // function render

/**
 * функция вырезает все <script...>...</script> из всех контентов вида 
 * ($Contents либо массив контентов либо просто строка) 
 * и добавляет их в Scripts в виде строки
 */
protected function cutScripts()
{
    $matches = null;
    // модификатор s обязателен, иначе не собираются многострочные тексты
    $pattern = "#<script[^>]*>.*<\/script>#imsU";

    preg_match_all($pattern, $this->FullContent, $matches);

    if(is_array($matches[0]) )
    {
        foreach($matches[0] as $script)
        {
            $this->Scripts = $this->Scripts . $script;
            $this->FullContent = str_replace( $script, "", $this->FullContent );
        }
    }
}

/**
 * функция установки имени вида (только имя без каталога и расширения!!!)
 */
function setViewName($name)
{
    $this->ViewName = $name;
}
/**
 * возвращает имя вида
 * @return string
 */
function getViewName()
{
    return $this->ViewName;
}

/**
 * функция установки имени шаблона отображения (только имя без каталога и расширения!!!)
 */
function setLayoutName($name)
{
    $this->LayoutName = $name;
}
/**
 * возвращает имя шаблона
 * @return string
 */
function getLayoutName()
{
    return $this->LayoutName;
}

/**
 * задаем путь для вида
 */
function setPathToViews($path)
{
    $this->PathToViews = rtrim($path, "/");
}
/**
 * @return string - возвращает путь для вида
 */
function getPathToViews()
{
    return $this->PathToViews;
}

/**
 * задаем путь для шаблона
 */
function setPathToLayouts($path)
{
    $this->PathToLayouts = rtrim($path, "/");
}
/**
 * @return string - возвращает путь для шаблона
 */
function getPathToLayouts()
{
    return $this->PathToLayouts;
}

/**
 * устанавливает в массиве vars значение по ключу $name, затем массив распаковывается функцией extract в переменные с именами $name
 * 
 * @param string $name - имя переменной для вида
 * @param mixed $value - ее значение
 */
function setVar($name, $value)
{
    if(!is_string($name) )
    {
//        TRMLib::debugPrint("Имя добавляемой переменной должно быть строкой!");
        return false;
    }

    $this->Vars[$name] = $value;
}
/**
 * возвращает значение переменной из массива Vars
 * 
 * @param string $name - имя переменной для вида
 * @return mixed|null
 */
function getVar($name)
{
    if( !isset($this->Vars[$name]) ) { return null; }
    return $this->Vars[$name];
}
/**
 * добавляет к массиву vars новые элементы из массива VarsArray, 
 * если у элементов одинаковые ключи, 
 * т.е. такой индекс уже есть в старом vars, то устанавливается новое значение из VarsArray
 * 
 * @param array $VarsArray - массив с "именами переменных" => "и их значеничми", 
 * если ключи-имена не установлены или численные, то имена сформируются системой автоматически
 */
function setVarsArray( array $VarsArray )
{
    $this->Vars = array_replace($this->Vars, $VarsArray);
}
/**
 * очищает массив с переменными
 */
function clearVars()
{
    $this->Vars = array();
}

/**
 * @return  - адрес для подстановки в rel='canonical'
 */
function getCanonical()
{
    return $this->Canonical;
}
/**
 * @param type $Canonical - адрес для подстановки в rel='canonical'
 */
function setCanonical( $Canonical )
{
    $this->Canonical = $Canonical;
}
/**
 * выводит link rel='canonical' href='{$this->Canonical' методом echo
 */
function printCanonical()
{
    if(!empty($this->Canonical))
    {
        echo "<link rel='canonical' href='{$this->Canonical}' />" . PHP_EOL;
    }
}

/**
 * устанавливаем заголовок - метатег Title;
 */
function setTitle($title)
{
    $this->Title = $title;
}
/**
 * @return string - текущий заголовок вида
 */
function getTitle()
{
    return $this->Title;
}
/**
 * выводит текущее значение Title в тег <title> методо echo
 */
function printTitle()
{
    if(!empty($this->Title))
    {
        echo "<title>{$this->Title}</title>" . PHP_EOL;
    }
}

/**
 * @return string - возвращает код языка, установленного для вида, например ru или en
 */
function getLang()
{
    return $this->Lang;
}
/**
 * @param string $Lang - код языка для вида, например ru или en
 */
function setLang($Lang)
{
    $this->Lang = $Lang;
}

/**
 * @return string - возвращает адрес favicon
 */
function getFavicon()
{
    return $this->Favicon;
}
/**
 * @param string $Favicon - устанавливает адрес для favicon
 */
function setFavicon($Favicon)
{
    $this->Favicon = $Favicon; // filter_var($Favicon, FILTER_SANITIZE_URL);
}
/**
 * выводит (подключает) favicon link rel="shortcut icon" href="favicon.ico" type="image/x-icon"
 */
function printFavicon()
{
    if( !empty($this->Favicon) )
    {
        echo "<link rel='shortcut icon' href='{$this->Favicon}' type='image/x-icon' />" . PHP_EOL;
    }
}

/**
 * устанавливаем описание - метатег Description;
 * 
 * @param string $description
 */
function setDescription($description)
{
    $this->MetaTags["Description"] = $description;
}
/**
 * устанавливаем ключевые слова - метатег keywords;
 * 
 * @param string $keywords
 */
function setKeyWords($keywords)
{
    $this->MetaTags["KeyWords"] = $keywords;
}

/**
 * устанавливает для метатега $tagname значение $tagvalue
 * 
 * @param sring $tagname
 * @param sring $tagvalue
 */
function setMeta($tagname, $tagvalue)
{
    $this->MetaTags[strtolower($tagname)] = $tagvalue;
}
/**
 * возвращает значение метатега $name
 * 
 * @param sring $name - имя META-тега
 * @return string|null
 */
function getMeta($name)
{
    $name = strtolower($name);
    if( !isset($this->MetaTags[$name]) ) { return null; }
    return $this->MetaTags[$name];
}
/**
 * выводит список META NAME методом echo
 */
function printMeta()
{
    foreach( $this->MetaTags as $TagName => $TagValue )
    {
        echo "<META NAME='{$TagName}' CONTENT='{$TagValue}'/>" . PHP_EOL;
    }
}

/**
 *  добавляет содержимое контента $content в массив по ключу $name
 * 
 * @param sring $name - имя контента в массиве
 * @param sring $content - содержимое для вывода, объект который может быть преобразован к строке
 * @return boolean
 */
function setContent($name, $content)
{
    if( !is_string($name) )
    {
//        TRMLib::debugPrint("Имя добавляемого контента должно быть строкой!");
        return false;
    }

    $this->Contents[$name] = (string)$content;
    return true;
}
/**
 * @param sring $name - имя запрашиваемого контента в массиве $name
 * @return sring - содержимое контента под именем $name
 */
function getContent($name)
{
    if( !isset($this->$name[$name]) )
    {
        return null;
    }
    return $this->Contents[$name];
}
/**
 * выводит содержимое контента под именем $name методом echo
 * 
 * @param sring $name - имя контента из массива Contents
 */
function printContent($name)
{
    if( isset($this->$name[$name]) )
    {
        echo $this->Contents[$name];
    }
}
/**
 * выводит содержимое каждого элемента из массива Contents посредством echo
 */
function printAllContents()
{
    foreach( $this->Contents as $Content )
    {
        echo $Content;
    }
}
/**
 * @return string - возвращает содержимое каждого элемента из массива Contents объединенные в одну строку,
 */
function getAllContentsString()
{
    $ResStr = "";
    foreach( $this->Contents as $Content )
    {
        $ResStr .= $Content;
    }
    return $ResStr;
}

/**
 * добавляет адрес файла стилей для подключения в шаблоне или виде
 * добавляется в массив по ключу [$link] значение $top , где "link" = адрес файла .CSS
 * а элемент со значением = $top указывает на необходимость подключать данного CSS в начале шаблона или вида, 
 * если этот элемент не true, то как правило шаблоны выводят CSS вконце документа
 */
function addCss($link, $top = false)
{
    $this->CSSArray[$link] = $top;
}
/**
 * выводится список с подключений CSS-Файлов через link rel='stylesheet' методом echo,
 * эта функция выводит только CSS с установленным флагом $top,
 * как правило используется для подключения стилей в начале документа
 */
function printCSS( $topflag = true )
{
    foreach($this->CSSArray as $link => $top)
    {
        if( $top == $topflag ) { echo "<link rel='stylesheet' type='text/css' href='{$link}' />" . PHP_EOL; }
    }
}
/**
 * выводится список с подключений CSS-Файлов через link rel='stylesheet' методом echo,
 * эта функция выводит только CSS с НЕ установленным флагом $top,
 * как правило используется для подключения стилей в конце документа
 */
function printEndCSS()
{
    $this->printCSS(false);
}

/**
 * добавляет адрес файла javascript для подключения в шаблоне или виде
 * добавляется в массив по ключу [$link] значение $top , где "link" = адрес файла .js
 * а элемент со значением = $top указывает на необходимость подключать данного javascript в начале шаблона или вида, 
 * если этот элемент не true, то как правило шаблоны выводят скрипты вконце документа
 * 
 * @param string $link - ссылка на скрипт
 * @param boolean $top - если true, то добавляется в раздел head - в начало страницы, иначе будет добавляться перед закрытием тега /body
 */
function addJS($link, $top = false)
{
    $this->JSArray[$link] = $top;
}
/**
 * Если $topflag == true, то выводится список с подключений JS-Файлов через script методом echo,
 * эта функция выводит только JS из массива JSArray со значени true,
 * как правило используется для подключения скриптов в начале документа.
 * Если $topflag == false, то выводится список с подключений JS-Файлов через script методом echo,
 * эта функция выводит только JS из массива JS0Array со значение false,
 * как правило используется для подключения скриптов в конце документа
 * 
 * @param boolean $topflag = если true, то выводятся все скрипты с установленным влагом top
 */
function printJS( $topflag = false )
{
    foreach( $this->JSArray as $link => $top )
    {
        if( $top == $topflag ) { echo "<script src='{$link}'></script>" . PHP_EOL; }
    }
}
/**
 */
function printEndJS()
{
    $this->printJS( false );
}


} // TRMView