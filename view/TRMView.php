<?php

namespace TRMEngine\View;

use TRMEngine\PathFinder\TRMPathFinder;

/**
 * ������� ����� ����
 */
class TRMView
{
/**
 * DefaultLayoutName string - ��� ������� �� ���������
 */
const DefaultLayoutName = "default";
/**
 * DefaultViewName string - ��� ���� �� ���������
 */
const DefaultViewName = "default";

/**
 * @var string - ���� � ����� � �����
 */
protected $PathToViews = "";
/**
 * @var string - ���� � ����� � ��������
 */
protected $PathToLayouts = "";
/**
 * @var string  ������ ��� ���� ��� ����������� �������� ������, ��� ������ ����� � ��� ����������
 */
protected $ViewName = "";
/**
 * @var string ��� ������ �������� ��� �� ��� ����������� � ��� ������ �����
 */
protected $LayoutName = "";
/**
 * @var array ����-���� Title, Description, keywords....
 */
protected $MetaTags = array();
/**
 * @var string - ���������
 */
protected $Title = "";
/**
 * @var string - ��� �������� ����� ��� ����
 */
protected $Lang = "ru";
/**
 * @var string 
 */
protected $Canonical = "";
/**
 * @var string - ����� favicon
 */
protected $Favicon = "";
/**
 * @var array - ������ � �������� CSS,
 * ����� ����� �� ������� ��������� � ������ �������, � � �������� �������� true - ����� ������������ � head, ��� false - ����������� � ����� ����
 */
protected $CSSArray = array();
/**
 * @var array - ������ � �������� JS, 
 * ����� ����� �� �������� ��������� � ������ �������, � � �������� �������� true - ������ ��� ����� head, ��� false - ����������� � ����� ����
 */
protected $JSArray = array();
/**
 * @var array - ������ ����������, ������� ����������� ������� setVars();
 */
protected $Vars = array();
/**
 * @var array - ������ �����-������ � ���������, ����� �������������� � ���� ��� ���������� ��������, � ������� render �� ����������
 */
protected $Contents = array();
/**
 * @var sting - ���������������� ������� � ���� ������
 */
protected $FullContent = "";

/**
 * @var sting ������� ��������� � ������ � ����� <script.*>...</script>, 
 * ������������ � ���� ����, ����������� �������� cutScripts,
 * ��� ������� ������������ ��� ���������� �������� � �����
 */
protected $Scripts = "";

/**
 *
 * @var boolean - ���� ���������� � true, 
 * ����� ��� ������� �� ������ �������� ����� ������������ ����������
 * � ������������ ���� ����� ������������� </body>,
 * ����������� ������� = true�����, ��� �� �������� ����� ������� - ���� = false
 */
public $ScriptsToEndFlag = true; // false; // 

/**
 * ��� ����� ������-�����/�������� � �����-����� ��� ��� ������ � ������ �������� !!!
 * 
 * @param string $view - ��� ����, ��� ����, ��� ����������, ������ ���, ���� � ���������� ".php" ������������� �������������
 * @param string $layout - ��� ������-�������, ��� �� ��� ���� � ����������, ���� ���� ���������� � === null - �� �� ������������, 
 * ������������ ��� AJAX-��������, ���� ������ ������, �� ������������ ������ �� ��������� == default
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
 * �������� ������� ������ ����������� ���� � ����� ��� �������� ������� ����������� echo...
 */
public function render()
{
    // ������������� __toString
    echo $this;
}
/**
 * ���������� ���� ������� � ���� ������
 * 
 * @return string
 */
public function __toString()
{
	$viewfilepath = $this->PathToViews . "/" . $this->ViewName.".php";
        if( !is_file($viewfilepath) )
        {
            return "";
            
            // __toString �� ����� ����������� ����������, ������ ���������������� ������, ������ � ������ ""
            // throw new \Exception( "�� ������ ��� {$viewfilepath}!", 500 );
        }

	if(!empty($this->Vars) ) // ����� ������� Html ������� ��� ���������� �� �������
        {
            extract($this->Vars);
        }

        // ����� ������� Html ������� ��� ����-���� � ���������� �� �������
//        if(!empty($this->MetaTags) ) { extract($this->MetaTags); }
	
        // ����� ������� Html ������� ��� ����� �������� � ���������� �� �������
//        if( is_array($this->Contents) ) { extract($this->Contents); }

        
	// � ���� ��������� �������� ����������, ��� ������� (��� ������� ��� ��������� � ��� ������)
        if( null === $this->LayoutName )
        {
            ob_start();
            require $viewfilepath;
            $this->FullContent = ob_get_clean();
        }
        else
        {
            // ���� ����� �����, �� ���������� ����� TRMContent � ���� Html-���, ���������� �� ��������� ����
            $LayoutFile = $this->PathToLayouts . "/" . $this->LayoutName.".php";
            if( !is_file($LayoutFile) ) { return ""; } // throw new \Exception( "�� ������ ����� {$LayoutFile}!" ); }
            ob_start();
            require $viewfilepath;
            // ��������� �����
            // $TRMContent - ������������ ��� �������� ������� ������ ��������
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
 * ������� �������� ��� <script...>...</script> �� ���� ��������� ���� 
 * ($Contents ���� ������ ��������� ���� ������ ������) 
 * � ��������� �� � Scripts � ���� ������
 */
protected function cutScripts()
{
    $matches = null;
    // ����������� s ����������, ����� �� ���������� ������������� ������
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
 * ������� ��������� ����� ���� (������ ��� ��� �������� � ����������!!!)
 */
function setViewName($name)
{
    $this->ViewName = $name;
}
/**
 * ���������� ��� ����
 * @return string
 */
function getViewName()
{
    return $this->ViewName;
}

/**
 * ������� ��������� ����� ������� ����������� (������ ��� ��� �������� � ����������!!!)
 */
function setLayoutName($name)
{
    $this->LayoutName = $name;
}
/**
 * ���������� ��� �������
 * @return string
 */
function getLayoutName()
{
    return $this->LayoutName;
}

/**
 * ������ ���� ��� ����
 */
function setPathToViews($path)
{
    $this->PathToViews = rtrim($path, "/");
}
/**
 * @return string - ���������� ���� ��� ����
 */
function getPathToViews()
{
    return $this->PathToViews;
}

/**
 * ������ ���� ��� �������
 */
function setPathToLayouts($path)
{
    $this->PathToLayouts = rtrim($path, "/");
}
/**
 * @return string - ���������� ���� ��� �������
 */
function getPathToLayouts()
{
    return $this->PathToLayouts;
}

/**
 * ������������� � ������� vars �������� �� ����� $name, ����� ������ ��������������� �������� extract � ���������� � ������� $name
 * 
 * @param string $name - ��� ���������� ��� ����
 * @param mixed $value - �� ��������
 */
function setVar($name, $value)
{
    if(!is_string($name) )
    {
//        TRMLib::debugPrint("��� ����������� ���������� ������ ���� �������!");
        return false;
    }

    $this->Vars[$name] = $value;
}
/**
 * ���������� �������� ���������� �� ������� Vars
 * 
 * @param string $name - ��� ���������� ��� ����
 * @return mixed|null
 */
function getVar($name)
{
    if( !isset($this->Vars[$name]) ) { return null; }
    return $this->Vars[$name];
}
/**
 * ��������� � ������� vars ����� �������� �� ������� VarsArray, 
 * ���� � ��������� ���������� �����, 
 * �.�. ����� ������ ��� ���� � ������ vars, �� ��������������� ����� �������� �� VarsArray
 * 
 * @param array $VarsArray - ������ � "������� ����������" => "� �� ����������", 
 * ���� �����-����� �� ����������� ��� ���������, �� ����� ������������ �������� �������������
 */
function setVarsArray( array $VarsArray )
{
    $this->Vars = array_replace($this->Vars, $VarsArray);
}
/**
 * ������� ������ � �����������
 */
function clearVars()
{
    $this->Vars = array();
}

/**
 * @return  - ����� ��� ����������� � rel='canonical'
 */
function getCanonical()
{
    return $this->Canonical;
}
/**
 * @param type $Canonical - ����� ��� ����������� � rel='canonical'
 */
function setCanonical( $Canonical )
{
    $this->Canonical = $Canonical;
}
/**
 * ������� link rel='canonical' href='{$this->Canonical' ������� echo
 */
function printCanonical()
{
    if(!empty($this->Canonical))
    {
        echo "<link rel='canonical' href='{$this->Canonical}' />" . PHP_EOL;
    }
}

/**
 * ������������� ��������� - ������� Title;
 */
function setTitle($title)
{
    $this->Title = $title;
}
/**
 * @return string - ������� ��������� ����
 */
function getTitle()
{
    return $this->Title;
}
/**
 * ������� ������� �������� Title � ��� <title> ������ echo
 */
function printTitle()
{
    if(!empty($this->Title))
    {
        echo "<title>{$this->Title}</title>" . PHP_EOL;
    }
}

/**
 * @return string - ���������� ��� �����, �������������� ��� ����, �������� ru ��� en
 */
function getLang()
{
    return $this->Lang;
}
/**
 * @param string $Lang - ��� ����� ��� ����, �������� ru ��� en
 */
function setLang($Lang)
{
    $this->Lang = $Lang;
}

/**
 * @return string - ���������� ����� favicon
 */
function getFavicon()
{
    return $this->Favicon;
}
/**
 * @param string $Favicon - ������������� ����� ��� favicon
 */
function setFavicon($Favicon)
{
    $this->Favicon = $Favicon; // filter_var($Favicon, FILTER_SANITIZE_URL);
}
/**
 * ������� (����������) favicon link rel="shortcut icon" href="favicon.ico" type="image/x-icon"
 */
function printFavicon()
{
    if( !empty($this->Favicon) )
    {
        echo "<link rel='shortcut icon' href='{$this->Favicon}' type='image/x-icon' />" . PHP_EOL;
    }
}

/**
 * ������������� �������� - ������� Description;
 * 
 * @param string $description
 */
function setDescription($description)
{
    $this->MetaTags["Description"] = $description;
}
/**
 * ������������� �������� ����� - ������� keywords;
 * 
 * @param string $keywords
 */
function setKeyWords($keywords)
{
    $this->MetaTags["KeyWords"] = $keywords;
}

/**
 * ������������� ��� �������� $tagname �������� $tagvalue
 * 
 * @param sring $tagname
 * @param sring $tagvalue
 */
function setMeta($tagname, $tagvalue)
{
    $this->MetaTags[strtolower($tagname)] = $tagvalue;
}
/**
 * ���������� �������� �������� $name
 * 
 * @param sring $name - ��� META-����
 * @return string|null
 */
function getMeta($name)
{
    $name = strtolower($name);
    if( !isset($this->MetaTags[$name]) ) { return null; }
    return $this->MetaTags[$name];
}
/**
 * ������� ������ META NAME ������� echo
 */
function printMeta()
{
    foreach( $this->MetaTags as $TagName => $TagValue )
    {
        echo "<META NAME='{$TagName}' CONTENT='{$TagValue}'/>" . PHP_EOL;
    }
}

/**
 *  ��������� ���������� �������� $content � ������ �� ����� $name
 * 
 * @param sring $name - ��� �������� � �������
 * @param sring $content - ���������� ��� ������, ������ ������� ����� ���� ������������ � ������
 * @return boolean
 */
function setContent($name, $content)
{
    if( !is_string($name) )
    {
//        TRMLib::debugPrint("��� ������������ �������� ������ ���� �������!");
        return false;
    }

    $this->Contents[$name] = (string)$content;
    return true;
}
/**
 * @param sring $name - ��� �������������� �������� � ������� $name
 * @return sring - ���������� �������� ��� ������ $name
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
 * ������� ���������� �������� ��� ������ $name ������� echo
 * 
 * @param sring $name - ��� �������� �� ������� Contents
 */
function printContent($name)
{
    if( isset($this->$name[$name]) )
    {
        echo $this->Contents[$name];
    }
}
/**
 * ������� ���������� ������� �������� �� ������� Contents ����������� echo
 */
function printAllContents()
{
    foreach( $this->Contents as $Content )
    {
        echo $Content;
    }
}
/**
 * @return string - ���������� ���������� ������� �������� �� ������� Contents ������������ � ���� ������,
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
 * ��������� ����� ����� ������ ��� ����������� � ������� ��� ����
 * ����������� � ������ �� ����� [$link] �������� $top , ��� "link" = ����� ����� .CSS
 * � ������� �� ��������� = $top ��������� �� ������������� ���������� ������� CSS � ������ ������� ��� ����, 
 * ���� ���� ������� �� true, �� ��� ������� ������� ������� CSS ������ ���������
 */
function addCss($link, $top = false)
{
    $this->CSSArray[$link] = $top;
}
/**
 * ��������� ������ � ����������� CSS-������ ����� link rel='stylesheet' ������� echo,
 * ��� ������� ������� ������ CSS � ������������� ������ $top,
 * ��� ������� ������������ ��� ����������� ������ � ������ ���������
 */
function printCSS( $topflag = true )
{
    foreach($this->CSSArray as $link => $top)
    {
        if( $top == $topflag ) { echo "<link rel='stylesheet' type='text/css' href='{$link}' />" . PHP_EOL; }
    }
}
/**
 * ��������� ������ � ����������� CSS-������ ����� link rel='stylesheet' ������� echo,
 * ��� ������� ������� ������ CSS � �� ������������� ������ $top,
 * ��� ������� ������������ ��� ����������� ������ � ����� ���������
 */
function printEndCSS()
{
    $this->printCSS(false);
}

/**
 * ��������� ����� ����� javascript ��� ����������� � ������� ��� ����
 * ����������� � ������ �� ����� [$link] �������� $top , ��� "link" = ����� ����� .js
 * � ������� �� ��������� = $top ��������� �� ������������� ���������� ������� javascript � ������ ������� ��� ����, 
 * ���� ���� ������� �� true, �� ��� ������� ������� ������� ������� ������ ���������
 * 
 * @param string $link - ������ �� ������
 * @param boolean $top - ���� true, �� ����������� � ������ head - � ������ ��������, ����� ����� ����������� ����� ��������� ���� /body
 */
function addJS($link, $top = false)
{
    $this->JSArray[$link] = $top;
}
/**
 * ���� $topflag == true, �� ��������� ������ � ����������� JS-������ ����� script ������� echo,
 * ��� ������� ������� ������ JS �� ������� JSArray �� ������� true,
 * ��� ������� ������������ ��� ����������� �������� � ������ ���������.
 * ���� $topflag == false, �� ��������� ������ � ����������� JS-������ ����� script ������� echo,
 * ��� ������� ������� ������ JS �� ������� JS0Array �� �������� false,
 * ��� ������� ������������ ��� ����������� �������� � ����� ���������
 * 
 * @param boolean $topflag = ���� true, �� ��������� ��� ������� � ������������� ������ top
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