<?php
/**
 * @file
 * Tests for the CodeKitCompiler class
 *
 * @ingroup codekit_php
 * @{
 */
require_once '../vendor/autoload.php';
require_once 'CodeKitTestCase.php';
require_once '../classes/CodeKit.php';
require_once '../classes/Compiler.php';
use aklump\codekit_php\Compiler;

class CompilerTest extends CodeKitTestCaseTest {

  public function testDirectories() {

    $this->paths[] = $source = $this->getTempDir() . '/ck_source';
    $this->paths[] = $output = $this->getTempDir() . '/ck_output';
    $obj = new Compiler($source, $output);
    $this->assertFileExists($source);
    $this->assertFileExists($output);
    $this->assertEquals($source, $obj->getSourceDirectory());
    $this->assertEquals($output, $obj->getOutputDirectory());

    $obj = new Compiler;
    $dir = $this->getTempDir();
    $return = $obj->setSourceDirectory($dir);
    $this->assertInstanceOf('aklump\codekit_php\Compiler', $return);
    $this->assertEquals($dir, $obj->getSourceDirectory());

    // Assert bad directory, no create returns empty
    $obj = new Compiler;
    $dir = '/some/dorky/directory/that/does/not/exist';
    $obj->setSourceDirectory($dir, FALSE);
    $this->assertEmpty($obj->getSourceDirectory());

    // Assert no directory, create true, creates and returns it.
    $obj = new Compiler;
    $this->paths[] = $dir = $this->getTempDir() . '/kit';
    $obj->setSourceDirectory($dir);
    $this->assertEquals($dir, $obj->getSourceDirectory());
    $this->assertFileExists($dir);

    $obj = new Compiler;
    $dir = $this->getTempDir();
    $return = $obj->setOutputDirectory($dir);
    $this->assertInstanceOf('aklump\codekit_php\Compiler', $return);
    $this->assertEquals($dir, $obj->getOutputDirectory());

    // Assert bad directory, no create returns empty
    $obj = new Compiler;
    $dir = '/some/dorky/directory/that/does/not/exist';
    $obj->setOutputDirectory($dir, FALSE);
    $this->assertEmpty($obj->getOutputDirectory());

    // Assert no directory, create true, creates and returns it.
    $obj = new Compiler;
    $this->paths[] = $dir = $this->getTempDir() . '/public_html';
    $obj->setOutputDirectory($dir);
    $this->assertEquals($dir, $obj->getOutputDirectory());
    $this->assertFileExists($dir);

  }

  public function testApply() {
    $this->paths['source'] = $this->getTempDir() . '/apply_source';
    $this->paths['output'] = $this->getTempDir() . '/apply_output';
    $obj = new Compiler($this->paths['source'], $this->paths['output']);

    // Set up three nested source .kit files
    $contents = <<<EOD
<!--\$header = 'Header'-->
<!--\$footer = 'Footer'-->
<!--\$preface = 'Four score and seven...'-->
<!--\$conclusion = 'Amen.'-->
<!--\$noun = 'donkey'-->
<!--\$place = 'Jerusalem'-->
<!--\$header-->
<!--@include body.kit-->
<!--\$footer-->
EOD;
    $this->writeFile($contents, 'page.kit', $this->paths['source']);
    $contents = <<<EOD
<!--\$preface-->
<!--@include content.kit-->
<!--\$conclusion-->
EOD;
    $this->writeFile($contents, 'body.kit', $this->paths['source']);
    $contents = <<<EOD
There was a <!--@noun-->, who lived in <!--@place-->.
EOD;
    $this->writeFile($contents, 'content.kit', $this->paths['source']);

    // Set up a non kit file to make sure it's ignored
    $this->writeFile('', 'bogus.html', $this->paths['source']);

    // Extract the files
    $control = array('body.kit', 'content.kit', 'page.kit');
    $this->assertEquals($control, $obj->getKitFiles());

    // Apply and check result
    $control = <<<EOD
Header
Four score and seven...
There was a donkey, who lived in Jerusalem.
Amen.
Footer
EOD;
    $this->assertEquals($control, $obj->apply());

    // Make sure we've not left any .kit.orig files behind
    $obj->__destruct();
    $files = scandir($this->paths['source']);
    $orphans = array();
    foreach ($files as $file) {
      if (preg_match('/(.*?\.kit)\.orig$/', $file, $matches)) {
        $orphans[] = $file;
      }
    }
    $this->assertEmpty($orphans);


  }
}

/** @} */ //end of group: codekit_php
