<?php
/**
 * @file
 * Defines the Compiler class
 *
 * @ingroup codekit_php
 * @{
 */
namespace aklump\codekit_php;

/**
 * Interface CompilerInterface
 */
interface CompilerInterface extends CodeKitInterface {
  public function setSourceDirectory($directory);
  public function getSourceDirectory();
  public function setOutputDirectory($directory);
  public function getOutputDirectory();

  /**
   * Return all .kit files in the source dir
   *
   * @return array
   */
  public function getKitFiles();
}

/**
 * Class Compiler
 */
class Compiler extends CodeKit implements CompilerInterface {

  private $source_dir, $output_dir;

  /**
   * Constructor
   *
   * @param string $source_dir
   * @param string $output_dir
   */
  public function __construct($source_dir = NULL, $output_dir = NULL) {
    $this->setSourceDirectory($source_dir);
    $this->setOutputDirectory($output_dir);
  }

  public function __destruct() {
    //@todo remove
    //return;
    // Replace all the kit files with their originals
    if ($this->source_dir) {
      $files = scandir($this->source_dir);
      foreach ($files as $key => $file) {
        if (preg_match('/(.*?\.kit)\.orig$/', $file, $matches)) {
          unlink($this->source_dir . '/' . $matches[1]);
          rename($this->source_dir . '/' . $matches[0], $this->source_dir . '/' . $matches[1]);
        }
      }
    }
  }

  /**
   * Check and/or recursively create a directory
   */
  protected function checkDir($dir, $create) {
    if ($dir && $create && !is_dir($dir)) {
      mkdir($dir, 0700, TRUE);
    }

    return is_dir($dir);
  }

  /**
   * Write a file to the source or output directory
   *
   * @param string $contents
   * @param string $file
   * @param string $destination
   *   (Optional) Defaults to 'output'. The other option is 'source'.
   */
  protected function writeFile($contents, $file, $destination = 'output') {
    $dir = $destination === 'source' ? $this->source_dir : $this->output_dir;

    // Make an orig backup
    if ($destination === 'source' && !file_exists($dir . '/' . $file . '.orig')) {
      copy($dir . '/' . $file, $dir . '/' . $file . '.orig');
    }

    $fp = fopen($dir . '/' . $file, 'w');
    fwrite($fp, $contents);
    fclose($fp);
  }

  public function setSourceDirectory($directory, $create = TRUE){
    if ($this->checkDir($directory, $create)) {
      $this->source_dir = $directory;
    }

    return $this;
  }

  public function getSourceDirectory() {
    return $this->source_dir;
  }

  public function setOutputDirectory($directory, $create = TRUE){
    if ($this->checkDir($directory, $create)) {
      $this->output_dir = $directory;
    }

    return $this;
  }

  public function getOutputDirectory() {
    return $this->output_dir;
  }

  public function getKitFiles() {
    $files = scandir($this->source_dir);
    foreach ($files as $key => $file) {
      if (!preg_match('/\.kit$/', $file)) {
        unset($files[$key]);
      }
    }

    return array_values($files);
  }

  public function apply() {
    $imports = array();

    if ($files = $this->getKitFiles()) {
      foreach ($files as $file) {
        $import = new Imports($this->source_dir . '/' . $file, TRUE);
        $this->writeFile($import->getSource(), $file, 'source');
        $this->writeFile($import->apply(), $file, 'source');
        $imports += $import->getImports();
      }
    }

    $files = array_diff($files, $imports);
    foreach ($files as $file) {
      $variables = new Variables($this->source_dir . '/' . $file, TRUE);
      $variables->extract();
      $result = $variables->apply();
      $this->writeFile($result, $file);
    }

    return $result;
  }
}



/** @} */ //end of group: codekit_php
