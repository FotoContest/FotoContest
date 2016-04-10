<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_LoadClassController
{
  public static function init($class, array &$extend)
  {
    if (!class_exists('XFCP_FotoContest_XFCP_Controller', false))
    {
      $extend[] = 'FotoContest_XFCP_Controller';
    }

    if ($class == 'XenForo_ControllerPublic_Attachment')
    {
      $extend[] = 'FotoContest_XFCP_ControllerPublic_Attachment';
    }
  }
}
