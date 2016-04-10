<?php
/**
 * @package Luqman_FotoContest
 * @author Luqman
 */
class FotoContest_Shared_XFCP_DataWriter_ModerationQueue extends XFCP_FotoContest_Shared_XFCP_DataWriter_ModerationQueue
{
  protected function _postSave()
  {
    parent::_postSave();

    $this->_updateModerationQueue();
  }

  protected function _updateModerationQueue()
  {
    $stateField = $this->getStateField();

    if (!$this->isChanged($stateField))
    {
      return;
    }

    if ($this->get($stateField) == 'moderated')
    {
      $this->getModelFromCache('XenForo_Model_ModerationQueue')->insertIntoModerationQueue(
        $this->getContentType(), $this->getContentId(), $this->get('created_at')
      );
    }
    else if ($this->getExisting($stateField) == 'moderated')
    {
      $this->getModelFromCache('XenForo_Model_ModerationQueue')->deleteFromModerationQueue(
        $this->getContentType(), $this->getContentId()
      );
    }
  }
}
