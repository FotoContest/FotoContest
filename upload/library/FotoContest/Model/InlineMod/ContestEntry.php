<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_InlineMod_ContestEntry extends XenForo_Model
{
  public function deleteContestEntries(array $contestEntryIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
  {
    $options = array_merge(
      array(
        'deleteType' => '',
      ), $options
    );

    if (!$options['deleteType'])
    {
      throw new XenForo_Exception('No delete type specified.');
    }

    $this->standardizeViewingUserReference($viewingUser);

    $contestEntries = $this->_getContestEntryModel()->getContestEntriesByIds($contestEntryIds);

    if (!$this->canDeleteContestEntriesData($contestEntries, $options['deleteType'], $errorKey, $viewingUser))
    {
      return false;
    }

    foreach ($contestEntries AS $contestEntry)
    {
      $this->_getContestEntryModel()->deleteContestEntry(
        $contestEntry, $options['deleteType'], $options
      );
    }

    return true;
  }

  public function canDeleteContestEntries(array $contestEntryIds, $deleteType = 'soft', &$errorKey = '', array $viewingUser = null)
  {
    $contestEntries = $this->_getContestEntryModel()->getContestEntriesByIds($contestEntryIds);

    return $this->canDeleteContestEntriesData($contestEntries, $deleteType, $errorKey, $viewingUser);
  }

  public function canDeleteContestEntriesData(array $contestEntries, $deleteType = 'soft', &$errorKey = '', array $viewingUser = null)
  {
    if (!$contestEntries)
    {
      return true;
    }

    $this->standardizeViewingUserReference($viewingUser);

    $contestEntryModel = $this->_getContestEntryModel();

    foreach ($contestEntries AS $contestEntry)
    {
      if (!$contestEntryModel->canDeleteContestEntry($contestEntry, $deleteType, $errorKey, $viewingUser))
      {
        return false;
      }
    }

    return true;
  }

  public function undeleteContestEntries(array $contestEntryIds, array $options = array(), &$errorKey = '', array $viewingUser = null)
  {
    $contestEntries = $this->_getContestEntryModel()->getContestEntriesByIds($contestEntryIds);

    if (!$this->canUnDeleteContestEntriesData($contestEntries, $errorKey, $viewingUser))
    {
      return false;
    }

    $this->_updateContestEntriesState($contestEntries, 'visible', 'deleted');

    return true;
  }

  public function canUnDeleteContestEntriesData(array $contestEntries, &$errorKey = '', array $viewingUser = null)
  {
    if (!$contestEntries)
    {
      return true;
    }

    $this->standardizeViewingUserReference($viewingUser);

    $contestEntryModel = $this->_getContestEntryModel();

    foreach ($contestEntries AS $contestEntry)
    {
      if (!$contestEntryModel->canUnDeleteContestEntry($contestEntry, $errorKey, $viewingUser))
      {
        return false;
      }
    }

    return true;
  }

  protected function _updateContestEntriesState(array $contestEntries, $newState, $expectedOldState = false)
  {
    switch ($newState)
    {
      case 'visible':
        switch (strval($expectedOldState))
        {
          case 'visible': return;
          case 'deleted': $logAction = 'undelete'; break;
          default: $logAction = 'undelete'; break;
        }
        break;

      case 'deleted':
        switch (strval($expectedOldState))
        {
          case 'visible': $logAction = 'delete_soft'; break;
          case 'deleted': return;
          default: $logAction = 'delete_soft'; break;
        }
        break;

      default: return;
    }

    foreach ($contestEntries AS $contestEntry)
    {
      if ($expectedOldState && $contestEntry['entry_state'] != $expectedOldState)
      {
        continue;
      }

      $dw = XenForo_DataWriter::create('FotoContest_DataWriter_ContestEntry', XenForo_DataWriter::ERROR_SILENT);
      if (!$dw->setExistingData($contestEntry))
      {
        continue;
      }
      $dw->set('entry_state', $newState);

      $dw->save();
    }
  }

  /**
   * @return FotoContest_Model_ContestEntry
   */
  protected function _getContestEntryModel()
  {
    return $this->getModelFromCache('FotoContest_Model_ContestEntry');
  }
}
