<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ModerationQueue_List extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $moderationQueueModel = $this->_getModerationQueueModel();

    $allEntries = $moderationQueueModel->getModerationQueueEntries();

    if (XenForo_Application::isRegistered('lfcModerationCounts'))
    {
      $moderationCounts = XenForo_Application::get('lfcModerationCounts');
      if (count($allEntries) != $moderationCounts['total'])
      {
        $moderationQueueModel->rebuildModerationQueueCountCache();
      }
    }

    $entries = $moderationQueueModel->getVisibleModerationQueueEntriesForUser($allEntries);

    $session = XenForo_Application::get('session');
    $sessionQueueCounts = $session->get('lfcModerationCounts');

    if (!is_array($sessionQueueCounts) || $sessionQueueCounts['total'] != count($entries))
    {
      $sessionQueueCounts = array(
        'total' => $moderationQueueModel->getModerationQueueCountForUser(),
        'lastBuildDate' => XenForo_Application::$time
      );
      $session->set('lfcModerationCounts', $sessionQueueCounts);
    }

    $viewParams = array(
      'queue' => array_slice($entries, 0, 100, true)
    );

    return $this->responseView(
      'FotoContest_ViewPublic_ModerationQueue_List',
      'lfc_moderation_queue_list', $viewParams
    );
  }
}
