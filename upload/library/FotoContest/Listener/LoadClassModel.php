<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_LoadClassModel
{
  public static function init($class, array &$extend)
  {
    if ($class == 'XenForo_Model_Moderator')
    {
      $extend[] = 'FotoContest_XFCP_Model_Moderator';
    }
  }
}
