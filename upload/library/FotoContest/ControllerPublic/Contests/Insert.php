<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Contests_Insert extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();
    $this->_assertPostOnly();
    $this->_assertCanPostContest();

    $visitor = XenForo_Visitor::getInstance();
    $input   = $this->_getContestInput();

    $writer = XenForo_DataWriter::create('FotoContest_DataWriter_Contest');
    $writer->set('user_id', $visitor['user_id']);
    $writer->set('username', $visitor['username']);
    $writer->set('title', $input['title']);
    $writer->set('description', $input['description']);

    $writer->set('posting_opens_on', $input['posting_opens_on']);
    $writer->set('posting_closes_on', $input['posting_closes_on']);

    $writer->set('voting_opens_on', $input['voting_opens_on']);
    $writer->set('voting_closes_on', $input['voting_closes_on']);

    $writer->set('moderate_entries', $input['moderate_entries']);
    $writer->set('max_entry_count', $input['max_entry_count']);
    $writer->set('max_votes_count', $input['max_votes_count']);
    $writer->set('max_winners_count', $input['max_winners_count']);
    $writer->set('max_images_per_entry', $input['max_images_per_entry']);
    $writer->set('hide_authors', $input['hide_authors']);
    $writer->set('entry_order', $input['entry_order']);
    $writer->set('allow_tied_winners', $input['allow_tied_winners']);
    $writer->set('hide_entries', $input['hide_entries']);
    $writer->set('is_featured', $input['is_featured']);

    $writer->set('post_user_group_ids', $this->_getInputUserGroupIds(
      $input, 'post_user_group_type', 'post_user_group_ids'
    ));

    $writer->set('vote_user_group_ids', $this->_getInputUserGroupIds(
      $input, 'vote_user_group_type', 'vote_user_group_ids'
    ));

    $writer->setExtraData(
      FotoContest_DataWriter_Contest::DATA_ATTACHMENT_HASH,
      $input['attachment_hash']
    );
    $writer->preSave();

    if (!$writer->hasErrors())
    {
      $this->assertNotFlooding('lfc');
    }

    $writer->save();

    $contest = $writer->getMergedData();

    return $this->responseRedirect(
      XenForo_ControllerResponse_Redirect::SUCCESS,
      XenForo_Link::buildPublicLink('photo-contests', $contest),
      new XenForo_Phrase('lfc_your_contest_has_been_posted')
    );
  }
}
