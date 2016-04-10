<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_Base extends XenForo_ControllerPublic_Abstract
{
  public function _preDispatch($action)
  {
    $this->_routeMatch->setSections('lfc');
    if (!XenForo_Visitor::getInstance()->hasPermission('lfc', 'view')){
      throw $this->getNoPermissionResponseException();
    }
  }

  protected function _findContest($contestId = null, array $fetchOptions = array())
  {
    if (is_null($contestId))
    {
      $contestId = $this->_input->filterSingle('photo_contest_id', XenForo_Input::UINT);
    }

    $contest = $this->_getContestModel()->getContestById($contestId, $fetchOptions);

    if (!$contest)
    {
      throw $this->responseException(
        $this->responseError(new XenForo_Phrase('lfc_requested_contest_not_found'), 404)
      );
    }

    return $contest;
  }

  protected function _getContestInput()
  {
    $filter  = array(
      'title'                   => XenForo_Input::STRING,
      'description'             => XenForo_Input::STRING,
      'attachment_hash'         => XenForo_Input::STRING,
      'entry_order'             => XenForo_Input::STRING,
      'moderate_entries'        => XenForo_Input::BOOLEAN,
      'hide_authors'            => XenForo_Input::BOOLEAN,
      'hide_entries'            => XenForo_Input::BOOLEAN,
      'contest_closed'          => XenForo_Input::BOOLEAN,
      'is_featured'             => XenForo_Input::BOOLEAN,
      'max_entry_count'         => XenForo_Input::UINT,
      'max_images_per_entry'    => XenForo_Input::UINT,
      'max_votes_count'         => XenForo_Input::UINT,
      'max_winners_count'       => XenForo_Input::UINT,
      'allow_tied_winners'      => XenForo_Input::BOOLEAN,
      'posting_opens_on'        => XenForo_Input::DATE_TIME,
      'posting_closes_on'       => XenForo_Input::DATE_TIME,
      'voting_opens_on'         => XenForo_Input::DATE_TIME,
      'voting_closes_on'        => XenForo_Input::DATE_TIME,
      'post_user_group_type'    => XenForo_Input::STRING,
      'post_user_group_ids'     => array(XenForo_Input::UINT, 'array' => true),
      'vote_user_group_type'    => XenForo_Input::STRING,
      'vote_user_group_ids'     => array(XenForo_Input::UINT, 'array' => true),
    );

    $input = $this->_input->filter($filter);
    $input['description'] = $this->getHelper('Editor')->getMessageText('description', $this->_input);
    $input['description'] = XenForo_Helper_String::autoLinkBbCode($input['description']);

    return $input;
  }

  protected function _findContestEntry($contestEntryId = null, array $fetchOptions = array())
  {
    if (is_null($contestEntryId))
    {
      $contestEntryId = $this->_input->filterSingle('photo_contest_entry_id', XenForo_Input::UINT);
    }

    $contestEntry = $this->_getContestEntryModel()->getContestEntryById(
      $contestEntryId, $fetchOptions
    );

    if (!$contestEntry)
    {
      throw $this->responseException(
        $this->responseError(new XenForo_Phrase('lfc_requested_contest_entry_not_found'), 404)
      );
    }

    return $contestEntry;
  }

  protected function _getContestEntryInput()
  {
    $filter = array(
      'title'           => XenForo_Input::STRING,
      'description'     => XenForo_Input::STRING,
      'attachment_hash' => XenForo_Input::STRING
    );

    $input = $this->_input->filter($filter);
    $input['description'] = $this->getHelper('Editor')->getMessageText('description', $this->_input);
    $input['description'] = XenForo_Helper_String::autoLinkBbCode($input['description']);

    return $input;
  }

  protected function _getInputUserGroupIds($input, $type, $field)
  {
    $postUserGroupIds = array();

    if ($input[$type] == 'all')
    {
      $postUserGroupIds = array(-1); // -1 is a sentinel for all groups
    }
    else
    {
      $postUserGroupIds = $input[$field];
    }

    return $postUserGroupIds;
  }

  protected function _assertCanPostContest()
  {
    if (!XenForo_Visitor::getInstance()->hasPermission('lfc', 'postContest'))
    {
      throw $this->getNoPermissionResponseException();
    }
  }

  protected function _canTakePartInContest($contest, array $viewingUser)
  {
    $postUserGroupIds = $contest['post_user_group_ids'];
    $userGroupIds = explode(',', $postUserGroupIds);

    if ($postUserGroupIds == -1){
      return true;
    } else {
      return XenForo_Template_Helper_Core::helperIsMemberOf($viewingUser, $userGroupIds);
    }
  }

  protected function _assertCanPostContestEntry(array $contest, array $viewingUser)
  {
    if (!XenForo_Visitor::getInstance()->hasPermission('lfc', 'postEntry'))
    {
      throw $this->getNoPermissionResponseException();
    }


    if (!$this->_canTakePartInContest($contest, $viewingUser))
    {
      throw $this->getNoPermissionResponseException();
    }

    $entryCount = $this->_getContestEntryModel()->countContestEntriesForContestAndUser(
      $contest, $viewingUser
    );

    $limit = $contest['max_entry_count'];
    if ($entryCount >= $limit)
    {
      throw $this->responseException(
        $this->responseError(new XenForo_Phrase(
          'lfc_contest_entry_limit_reached',
          array('limit' => $limit)
        ))
      );
    }
  }

  protected function _assertContestEntriesAllowed($contest)
  {
    if ($contest['isOpenToEntries'] === false)
    {
      throw $this->responseException(
        $this->responseError(new XenForo_Phrase('lfc_contest_closed'))
      );
    }

    if ($contest['contest_closed'])
    {
      throw $this->responseException(
        $this->responseError(new XenForo_Phrase(
          'lfc_contest_entry_edit_sorry_contest_is_closed'
        ))
      );
    }
  }

  protected function _assertCanEditContest(array $contest)
  {
    if (!$this->_getContestModel()->canEditContest($contest, $errorPhraseKey))
    {
      throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
    }
  }

  protected function _assertCanDeleteContest(array $contest, $deleteType)
  {
    if (!$this->_getContestModel()->canDeleteContest($contest, $deleteType, $errorPhraseKey))
    {
      throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
    }
  }

  protected function _assertCanUnDeleteContest(array $contest)
  {
    if (!$this->_getContestModel()->canUnDeleteContest($contest, $errorPhraseKey))
    {
      throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
    }
  }

  protected function _assertCanEditContestEntry(array $contestEntry, array $contest, array $viewingUser)
  {
    if (!$this->_canTakePartInContest($contest, $viewingUser))
    {
      throw $this->getNoPermissionResponseException();
    }

    if ($contest['isOpenToVotes'])
    {
      throw $this->responseException(
        $this->responseError(new XenForo_Phrase(
          'lfc_contest_entry_edit_sorry_voting_is_open'
        ))
      );
    }

    if (!$this->_getContestEntryModel()->canEditContestEntry($contestEntry, $errorPhraseKey))
    {
      throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
    }
  }

  protected function _assertCanDeleteContestEntry(array $contestEntry, $deleteType)
  {
    if (!$this->_getContestEntryModel()->canDeleteContestEntry($contestEntry, $deleteType, $errorPhraseKey))
    {
      throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
    }
  }

  protected function _getAllUserGroups()
  {
    $userGroups = $this->_getUserGroupModel()->getAllUserGroups();
    unset($userGroups[1]); //remove unregistered / unconfirmed
    return $userGroups;
  }

  /**
   * @return FotoContest_Model_Contest
   */
  protected function _getContestModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Contest');
  }

  /**
   * @return FotoContest_Model_ContestEntry
   */
  protected function _getContestEntryModel()
  {
    return $this->getModelFromCache('FotoContest_Model_ContestEntry');
  }

  /**
   * @return FotoContest_Model_Prepare_ContestEntry
   */
  protected function _getContestEntryPrepareModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Prepare_ContestEntry');
  }

  /**
   * @return FotoContest_Model_Permission_ContestEntry
   */
  protected function _getContestEntryPermissionModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Permission_ContestEntry');
  }

  /**
   * @return FotoContest_Model_Attachment
   */
  protected function _getAttachmentModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Attachment');
  }

  /**
   * @return FotoContest_AttachmentHandler_Contest
   */
  protected function _getContestAttachmentHandler()
  {
    return $this->getModelFromCache('FotoContest_AttachmentHandler_Contest');
  }

  /**
   * @return FotoContest_AttachmentHandler_ContestEntry
   */
  protected function _getContestEntryAttachmentHandler()
  {
    return $this->getModelFromCache('FotoContest_AttachmentHandler_ContestEntry');
  }

  /**
   * @return  XenForo_Model_Like
   */
  protected function _getLikeModel()
  {
    return $this->getModelFromCache('XenForo_Model_Like');
  }

  /**
   * @return FotoContest_Model_ModerationQueue
   */
  protected function _getModerationQueueModel()
  {
    return $this->getModelFromCache('FotoContest_Model_ModerationQueue');
  }

  /**
   * @return XenForo_Model_UserGroup
   */
  protected function _getUserGroupModel()
  {
    return $this->getModelFromCache('XenForo_Model_UserGroup');
  }
}
