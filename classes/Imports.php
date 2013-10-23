<?php
/**
 * @file
 * Defines the Imports Class
 *
 * @ingroup codekit_php
 * @{
 */
namespace aklump\codekit_php;

/**
 * Interface ImportsInterface
 */
interface ImportsInterface extends CodeKitInterface {

  /**
   * Return the working directory of the source file
   *
   * @return string
   */
  public function getDirname();

  /**
   * Set the base directory for include files
   *
   * @param string $dirname
   *
   * @return $this
   */
  public function setDirname($dirname);

  /**
   * Return a list of all included import files (not full paths).
   *
   * @return array
   */
  public function getImports();
}


/**
 * Class Imports
 */
class Imports extends CodeKit implements ImportsInterface {
  protected $dirname, $imports;

  /**
   * Constructor
   *
   * @param type $source
   *   (Optional) Defaults to NULL. Can be a string, in which case you need to
       set $is_file to FALSE.  Otherwise the string should be a path to a file.
     @param bool $is_file
   */
  public function __construct($source = NULL, $is_file = TRUE) {
    $this->dirname = '';
    if ($source && $is_file) {
      $info = pathinfo($source);
      $this->dirname = $info['dirname'];
    }
    parent::__construct($source, $is_file);
    $this->imports = array();
  }

  public function apply() {
    $result = $this->source;
    if (!preg_match_all('/<!--\s*(?:@import|@include) "?([^"]*?)"?\s*-->/', $this->source, $matches)) {
      return $result;
    }
    foreach (array_unique($matches[1]) as $key => $paths) {
      $paths = explode(',', $paths);
      $replace = '';
      foreach ($paths as $path) {
        $path = trim($path);
        $this->imports[$path] = $path;
        $path = $this->dirname . '/' . $path;
        if (is_readable($path) && ($contents = file_get_contents($path))) {
          $replace .= $contents;
        }
      }
      $result = str_replace($matches[0][$key], $replace, $result);
    }

    return $result;
  }

  public function getDirname() {
    return $this->dirname;
  }

  public function setDirname($dir) {
    if (is_dir($dir)) {
      $this->dirname = $dir;
    }

    return $this;
  }

  public function getImports() {
    return $this->imports;
  }
}

/** @} */ //end of group: codekit_php
