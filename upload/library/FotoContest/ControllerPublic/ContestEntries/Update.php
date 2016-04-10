<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ContestEntries_Update extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();
    $this->_assertPostOnly();

    $viewingUser       = XenForo_Visitor::getInstance()->toArray();
    $contestEntry      = $this->_findContestEntry();
    $contestEntryModel = $this->_getContestEntryModel();
    $contestEntry      = $contestEntryModel->prepareContestEntry($contestEntry);
    $contest           = $this->_findContest($contestEntry['photo_contest_id']);
    $contest           = $this->_getContestModel()->prepareContest($contest);

    $this->_assertCanEditContestEntry($contestEntry, $contest, $viewingUser);

    $input = $this->_getContestEntryInput();

    $writer = XenForo_DataWriter::create('FotoContest_DataWriter_ContestEntry');
    $writer->setExistingData($contestEntry, true);
    $writer->set('title', $input['title']);
    $writer->set('description', $input['description']);
    $writer->setExtraData(
      FotoContest_DataWriter_ContestEntry::DATA_ATTACHMENT_HASH,
      $input['attachment_hash']
    );
    $writer->preSave();

    if (!$writer->hasErrors())
    {
      $this->assertNotFlooding('lfc');
    }

    $writer->save();
    $contestEntry = $writer->getMergedData();

    return $this->responseRedirect(
      XenForo_ControllerResponse_Redirect::SUCCESS,
      XenForo_Link::buildPublicLink('photo-contest-entries', $contestEntry),
      new XenForo_Phrase('lfc_your_contest_entry_has_been_updated')
    );
  }
}
