<?php
/**
 * @file
 * Tests for the CodeKitTestCase class
 *
 * @ingroup codekit_php
 * @{
 */
class CodeKitTestCaseTest extends PHPUnit_Framework_TestCase {
  protected $paths;

  protected function getTempDir() {
    return sys_get_temp_dir() . '/com.aklump.codekit_php/';
  }

  protected function writeFile($contents, $file, $dir = NULL) {
    if ($dir === NULL) {
      $dir = $this->getTempDir();
    }
    if (is_writable($dir) /*&& !is_file($dir . '/' . $file)*/) {
      $fp = fopen($dir . '/' . $file, 'w');
      fwrite($fp, $contents);
      fclose($fp);
      $this->paths[] = $dir . '/' . $file;
    }

    return is_file($dir . '/' . $file) ? $dir . '/' . $file : FALSE;
  }

  function __destruct() {
    // Delete all of our temporary files
    if (is_dir($this->getTempDir())) {
      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($this->getTempDir(), RecursiveDirectoryIterator::SKIP_DOTS),
          RecursiveIteratorIterator::CHILD_FIRST
      );

      foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
      }

      rmdir($this->getTempDir());
    }
  }
}

/** @} */ //end of group: codekit_php
