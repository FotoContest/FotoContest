<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_InsertContestEntry extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertPostOnly();
    $this->_assertRegistrationRequired();

    $contest = $this->_findContest();
    $contest = $this->_getContestModel()->prepareContest($contest);

    $this->_assertContestEntriesAllowed($contest);

    $viewingUser = XenForo_Visitor::getInstance()->toArray();
    $this->_assertCanPostContestEntry($contest, $viewingUser);

    $input   = $this->_getContestEntryInput();
    $visitor = XenForo_Visitor::getInstance();

    $writer = XenForo_DataWriter::create('FotoContest_DataWriter_ContestEntry');
    $writer->set('user_id', $visitor['user_id']);
    $writer->set('username', $visitor['username']);
    $writer->set('title', $input['title']);
    $writer->set('description', $input['description']);
    $writer->set('photo_contest_id', $contest['photo_contest_id']);
    $writer->setExtraData(
      FotoContest_DataWriter_ContestEntry::DATA_ATTACHMENT_HASH,
      $input['attachment_hash']
    );

    if ($contest['moderate_entries'])
    {
      $writer->set('entry_state', 'moderated');
    }

    $writer->preSave();

    if (!$writer->hasErrors())
    {
      $this->assertNotFlooding('lfc_entry');
    }

    $writer->save();
    $contestEntry = $writer->getMergedData();

    return $this->responseRedirect(
      XenForo_ControllerResponse_Redirect::SUCCESS,
      XenForo_Link::buildPublicLink('photo-contest-entries', $contestEntry),
      new XenForo_Phrase('lfc_your_contest_entry_has_been_posted')
    );
  }
}
