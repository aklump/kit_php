<?php
/**
 * @file
 * Defines the abstract CodeKit class
 *
 * @ingroup codekit_php
 * @{
 */
namespace aklump\codekit_php;

/**
 * Interface CodeKitInterface
 */
interface CodeKitInterface {
  /**
   * Set the source code
   *
   * @param string $source
   *
   * @return object $this
   */
  public function setSource($source);

  /**
   * Return the source code
   *
   * @return string
   */
  public function getSource();

  /**
   * Apply any transformations and return the compiled code
   *
   * @return string
   */
  public function apply();
}

/**
 * Class CodeKit
 */
abstract class CodeKit implements CodeKitInterface {

  protected $source;

  /**
   * Constructor
   *
   * @param type $source
   *   (Optional) Defaults to NULL.
   */
  public function __construct($source = NULL, $is_file = FALSE) {
    if ($source !== NULL) {
      if ($is_file && file_exists($source)) {
        $this->setSource(file_get_contents($source));
      }
      else {
        $this->setSource($source);
      }
    }
  }

  public function setSource($source) {
    if (is_string($source)) {
      $this->source = $source;
    }

    return $this;
  }

  public function getSource() {
    return $this->source;
  }

  abstract function apply();
}

/** @} */ //end of group: codekit_php
