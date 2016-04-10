<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_XFCP_Model_Moderator extends XFCP_FotoContest_XFCP_Model_Moderator
{
  public function getGeneralModeratorInterfaceGroupIds()
  {
    $ids = parent::getGeneralModeratorInterfaceGroupIds();
    $ids[] = 'lfcModeratorPermissions';
    return $ids;
  }
}
