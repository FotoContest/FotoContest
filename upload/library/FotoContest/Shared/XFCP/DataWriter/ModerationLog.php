<?php
/**
 * @package Luqman_FotoContest
 * @author Luqman
 */
class FotoContest_Shared_XFCP_DataWriter_ModerationLog extends XFCP_FotoContest_Shared_XFCP_DataWriter_ModerationLog
{
  protected function _postSave()
  {
    parent::_postSave();

    $stateField = $this->getStateField();

    if ($this->isUpdate() && $this->isChanged($stateField))
    {
      $newState  = $this->getNew($stateField);
      $oldState  = $this->getExisting($stateField);
      $action    = $this->getActionFromState($newState, $oldState);

      if ($action)
      {
        XenForo_Model_Log::logModeratorAction(
          $this->getContentType(),
          $this->getMergedData(),
          $action,
          array('reason' => $this->getExtraData(self::DATA_DELETE_REASON))
        );
      }
    }
  }

  protected function _postDelete()
  {
    parent::_postDelete();

    XenForo_Model_Log::logModeratorAction(
      $this->getContentType(),
      $this->getMergedData(),
      'delete_hard',
      array('reason' => $this->getExtraData(self::DATA_DELETE_REASON))
    );
  }

  protected function getActionFromState($newState, $oldState)
  {
    switch ($newState)
    {
      case 'visible':
        switch (strval($oldState))
        {
          case 'visible': return;
          case 'moderated': $logAction = 'approve'; break;
          case 'deleted': $logAction = 'undelete'; break;
          default: $logAction = 'undelete'; break;
        }
        break;

      case 'moderated':
        switch (strval($oldState))
        {
          case 'visible': $logAction = 'unapprove'; break;
          case 'moderated': return;
          case 'deleted': $logAction = 'unapprove'; break;
          default: $logAction = 'unapprove'; break;
        }
        break;

      case 'deleted':
        switch (strval($oldState))
        {
          case 'visible': $logAction = 'delete_soft'; break;
          case 'moderated': $logAction = 'delete_soft'; break;
          case 'deleted': return;
          default: $logAction = 'delete_soft'; break;
        }
        break;

      default: return;
    }

    return $logAction;
  }

}
