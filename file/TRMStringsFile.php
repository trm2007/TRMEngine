<?php

namespace TRMEngine\File;

/**
 * расширяет работу с файлом TRMFile, 
 * позволяя оперировать с отдельными строками как с элементами массива,
 * в частности, к полченному содержимому, 
 * можно будет прменить foreach( TRMStringsFile->getArrayBuffer() ) для перебора всех полученных строк
 */
class TRMStringsFile extends TRMFile
{
  /**
   * @var array - если вызван метод 
   */
  protected $ArrayBuffer = array();


  /**
   * @return array - возвращает строки, полученные из файла в виде массива,
   * массив будет не пустым, только после вызова getEveryStringToArrayFrom(...),
   * либо заполнения вручную
   */
  public function getArrayBuffer()
  {
    return $this->ArrayBuffer;
  }
  /**
   * @param array $ArrayBuffer - устанавливает массив со строками, строковый буфер не затрагивает!!!
   */
  public function setArrayBuffer($ArrayBuffer)
  {
    $this->ArrayBuffer = $ArrayBuffer;
  }

  /**
   * @param string $str - строка, которая будет добавлена в массив ArrayBuffer, но не в строковый буфер!!!
   */
  public function addStringToArray($str)
  {
    $this->ArrayBuffer[] = (string)$str;
  }

  /**
   * @return int - возвращает количество строк, хранящихся в массиве,
   * для получения размера буыера используйте - getBufferSize
   */
  public function getArraySize()
  {
    return count($this->ArrayBuffer);
  }

  /**
   * очищает массив со строками
   */
  public function clearArray()
  {
    $this->ArrayBuffer = array();
  }

  /**
   * заполняет массив ArrayBuffer содержимым файла, 
   * каждая строка записывается в новый элемент массива
   * 
   * @param string $filename - имя файла, которое нужно прочитать
   * @param int $option - опции чтения файла для функции file(..., $option), 
   * поумолчанию включена опция пропускать пустые строки FILE_SKIP_EMPTY_LINES
   * 
   * @return bool
   */
  public function getEveryStringToArrayFrom($filename = "", $option = FILE_SKIP_EMPTY_LINES)
  {
    if (empty($filename)) {
      $filename = $this->getFullPath();
    }
    if (!is_file($filename)) {
      $this->StateString = "имя файла задано не верно {$filename}";
      return false;
    }
    $this->ArrayBuffer = array();
    $this->ArrayBuffer = file($filename, $option);
    if ($this->ArrayBuffer === false) {
      $this->StateString = "не удалось прочитать данные из файла {$filename}";
      $this->ArrayBuffer = array();
      return false;
    }
    $this->StateString = "";
    return true;
  }

  /**
   * получает очередную строку из открытого файла и помещает ее в массив
   * можно указать количество считываемых байт, 
   * по умолчанию читает всю строку, 
   * либо 4096 байта если строка окажется длиннее, 
   * либо до конца файла, если он окажется короче 4096 байт
   * 
   * @param int $length
   * @return bool
   */
  public function getStringToArrayFrom($length = 4096)
  {
    if (!$this->Handle) {
      $this->StateString = "файл для чтения не открыт";
      return false;
    }

    $str = fgets($this->Handle, $length);
    if ($str === false) {
      $this->StateString = "не удалось прочитать данные из файла {$this->FullPath}";
      return false;
    }

    $this->addToBuffer($str);
    $this->StateString = "";
    return true;
  }

  /**
   * записывает каждую строку из массива в файл, 
   * каждая строка будет заканчиваться EOL.
   * если файл не открыт, то пытаестя открыть его в режиме $mod - поумолчанию перезапись всего содержимого...
   * возвращается количество записанных байт, 0 - если буфер пуст и false в случае ошибки
   * 
   * @param string $mod - режим: "a" добавления в файл, или "w" перезаписи всего содержимого
   * @return bool|int
   */
  public function putStringsArrayTo($mod = "w", $AddEolflag = true)
  {
    if (empty($this->ArrayBuffer)) {
      $this->StateString = "";
      return 0;
    }
    if (!$this->Handle) {
      if (!$this->FullPath) {
        $this->StateString = "файл для записи не открыт, имя не задано";
        return false;
      }
      if (!$this->openFile($this->FullPath, $mod)) {
        return false;
      }
    }
    $count = 0;
    $length = 0;
    $this->StateString = "";
    foreach ($this->ArrayBuffer as $str) {
      if ($AddEolflag) {
        $str .= PHP_EOL;
      }
      $length = strlen($str);
      $count += $length;

      $writen = self::fwrite_stream($this->Handle, $str); // fwrite( $this->Handle, $str ); }
      if (strlen($str) > $writen) {
        $this->StateString .= "строка [{$str}] не полностью записана ({$writen} байт из {$length});";
      }
    }

    return $count;
  }
} // TRMStringsFile