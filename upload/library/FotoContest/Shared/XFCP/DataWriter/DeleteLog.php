<?php
/**
 * @package Luqman_FotoContest
 * @author Luqman
 */
class FotoContest_Shared_XFCP_DataWriter_DeleteLog extends XFCP_FotoContest_Shared_XFCP_DataWriter_DeleteLog
{
  /**
   * Holds the reason for soft deletion.
   *
   * @var string
   */
  const DATA_DELETE_REASON = 'deleteReason';

  protected function _postSave()
  {
    parent::_postSave();

    $this->_updateDeletionLog();
  }

  protected function _updateDeletionLog()
  {
    $stateField = $this->getStateField();

    if (!$this->isChanged($stateField))
    {
      return;
    }

    if ($this->get($stateField) == 'deleted')
    {
      $reason = $this->getExtraData(self::DATA_DELETE_REASON);
      $this->getModelFromCache('XenForo_Model_DeletionLog')->logDeletion(
        $this->getContentType(), $this->getContentId(), $reason
      );
    }
    else if ($this->getExisting($stateField) == 'deleted')
    {
      $this->getModelFromCache('XenForo_Model_DeletionLog')->removeDeletionLog(
        $this->getContentType(), $this->getContentId()
      );
    }
  }

}
