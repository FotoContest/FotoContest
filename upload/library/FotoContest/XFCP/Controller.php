<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_XFCP_Controller extends XFCP_FotoContest_XFCP_Controller
{
  protected function _updateModeratorSessionCaches()
  {
    parent::_updateModeratorSessionCaches();
    if (XenForo_Application::isRegistered('session'))
    {
      $this->_updateContestEntrySessionModerationCounts();
    }
  }

  protected function _updateContestEntrySessionModerationCounts()
  {
    if (XenForo_Application::isRegistered('lfcModerationCounts'))
    {
      $counts = XenForo_Application::get('lfcModerationCounts');
    }
    else
    {
      $counts = Xenforo_Model::create(
        'FotoContest_Model_ModerationQueue'
      )->rebuildModerationQueueCountCache();
    }

    $session = XenForo_Application::get('session');
    $sessionCounts = $session->get('lfcModerationCounts');

    if (!is_array($sessionCounts) || $sessionCounts['lastBuildDate'] < $counts['lastModifiedDate'])
    {
      if (!$counts['total'])
      {
        $sessionCounts = array('total' => 0);
      }
      else
      {
        $sessionCounts = array(
          'total' => Xenforo_Model::create(
            'FotoContest_Model_ModerationQueue'
          )->getModerationQueueCountForUser()
        );
      }

      $sessionCounts['lastBuildDate'] = XenForo_Application::$time;
      $session->set('lfcModerationCounts', $sessionCounts);
    }
  }
}
