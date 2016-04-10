<?php
/**
 * Helper for file-related functions.
 *
 * @package FotoContest_Shared_Helper_File
 */

abstract class FotoContest_Shared_Helper_File
{
  public static function rmdir($dir)
  {
    $root = XenForo_Application::getInstance()->getRootDir();
    require($root . '/library/Sabre/Sabre.autoload.php');

    $directory = new Sabre_DAV_FSExt_Directory($dir);
    return $directory->delete();
  }
}
