<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_LoadClassDataWriter
{
  public static function init($class, array &$extend)
  {
    if ($class == 'FotoContest_DataWriter_Contest')
    {
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_DeleteLog';
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_ModerationLog';
    }

    if ($class == 'FotoContest_DataWriter_ContestEntry')
    {
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_DeleteLog';
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_ModerationLog';
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_ModerationQueue';
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_Thumbnail';
      $extend[] = 'FotoContest_Shared_XFCP_DataWriter_Search';
    }
  }
}
