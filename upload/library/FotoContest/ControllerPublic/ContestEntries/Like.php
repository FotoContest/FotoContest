<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ControllerPublic_ContestEntries_Like extends FotoContest_ControllerPublic_Base
{
  public function actionRun()
  {
    $this->_assertRegistrationRequired();

    $viewingUser  = XenForo_Visitor::getInstance()->toArray();
    $contestEntry = $this->_findContestEntry();
    $contest      = $this->_getContestModel()->getContestById(
      $contestEntry['photo_contest_id']
    );
    $contest      = $this->_getContestModel()->prepareContest($contest);

    if ($contestEntry['user_id'] == $viewingUser['user_id'])
    {
      throw $this->getErrorOrNoPermissionResponseException('liking_own_content_cheating');
    }

    $this->getModelFromCache(
      'FotoContest_Model_Permission_Entry_CanLike'
    )->execute($contest, $contestEntry, $viewingUser);

    $likeModel    = $this->_getLikeModel();
    $existingLike = $likeModel->getContentLikeByLikeUser(
      'lfc_entry', $contestEntry['photo_contest_entry_id'], $viewingUser['user_id']
    );

    if ($this->_request->isPost())
    {
      if ($existingLike)
      {
        $latestUsers = $likeModel->unlikeContent($existingLike);
      }
      else
      {
        if (!$contestEntry['canLike'])
        {
          return $this->responseError(new XenForo_Phrase(
            'lfc_flash_not_allowed_to_vote'
          ));
        }

        if (!$contest['isOpenToVotes'])
        {
          throw $this->responseException($this->responseError(new XenForo_Phrase(
            'lfc_contest_currently_closed'
          ), 403));
        }

        if (!$this->_getPermissionModel()->canLikeContestEntries($contest, $viewingUser))
        {
          throw $this->responseException($this->responseError(new XenForo_Phrase(
            'lfc_contest_max_votes_reached',
            array('limit' => $contest['max_votes_count'])
          ), 403));
        }

        $latestUsers = $likeModel->likeContent(
          'lfc_entry', $contestEntry['photo_contest_entry_id'], $contestEntry['user_id']
        );
      }

      $liked = ($existingLike ? false : true);

      if ($this->_noRedirect() && $latestUsers !== false)
      {
        $contestEntry['likeUsers'] = $latestUsers;
        $contestEntry['likes'] += ($liked ? 1 : -1);
        $contestEntry['like_date'] = ($liked ? XenForo_Application::$time : 0);

        $this->_getContestEntryPermissionModel()->canViewLikesCountForEntry(
          $contestEntry,
          $contest,
          $viewingUser
        );

        $viewParams = array(
          'contestEntry' => $contestEntry,
          'liked'   => $liked
        );

        return $this->responseView(
          'FotoContest_ViewPublic_ContestEntry_LikeConfirmed', '',
          $viewParams
        );
      }
      else
      {
        return $this->responseRedirect(
          XenForo_ControllerResponse_Redirect::SUCCESS,
          XenForo_Link::buildPublicLink('photo-contest-entries', $contestEntry)
        );
      }
    }
    else
    {
      $viewParams = array(
        'contest' => $contest,
        'contestEntry' => $contestEntry,
        'like' => $existingLike
      );

      return $this->responseView(
        'FotoContest_ViewPublic_ContestEntry_Like',
        'lfc_contest_entry_like', $viewParams
      );
    }
  }

  protected function _getPermissionModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Permission_Contest');
  }
}
