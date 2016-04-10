<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ModeratorLogHandler_ContestEntry extends XenForo_ModeratorLogHandler_Abstract
{
  protected function _log(array $logUser, array $content, $action, array $actionParams = array(), $parentContent = null)
  {
    $dw = XenForo_DataWriter::create('XenForo_DataWriter_ModeratorLog');
    $dw->bulkSet(array(
      'user_id' => $logUser['user_id'],
      'content_type' => 'lfc_entry',
      'content_id' => $content['photo_contest_entry_id'],
      'content_user_id' => $content['user_id'],
      'content_username' => $content['username'],
      'content_title' => $content['title'],
      'content_url' => XenForo_Link::buildPublicLink('photo-contest-entries', $content),
      'action' => $action,
      'action_params' => $actionParams
    ));
    $dw->save();

    return $dw->get('moderator_log_id');
  }
}
