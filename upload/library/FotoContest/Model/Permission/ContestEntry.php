<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_Permission_ContestEntry extends Xenforo_Model
{
  public function getContestEntryInsertEntryState(array $contest, $viewingUser = null)
  {
    $permissions = $viewingUser['permissions']['lfc'];

    if ($viewingUser['user_id'] && $permissions['approveUnapprove'])
    {
      return 'visible';
    }
    else if (XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'followModerationRules'))
    {
      return (empty($contest['moderate_entries']) ? 'visible' : 'moderated');
    }
    else
    {
      return 'moderated';
    }
  }

  public function inlineModPermissions(array &$contestEntry, $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    $canInlineMod = (
      $viewingUser['user_id']
        &&
      (
        XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'deleteAnyEntry')
          ||
        XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'undelete')
      )
    );

    $modOptions = array();

    if ($canInlineMod)
    {
      $contestEntryModel = $this->getModelFromCache('FotoContest_Model_ContestEntry');
      if ($contestEntryModel->canDeleteContestEntry($contestEntry, 'soft', $null, $viewingUser))
      {
        $modOptions['delete'] = true;
      }
      if ($contestEntryModel->canUndeleteContestEntry($contestEntry, $null, $viewingUser))
      {
        $modOptions['undelete'] = true;
      }
    }

    $contestEntry['canInlineMod'] = (count($modOptions) > 0);

    return $modOptions;
  }

  public function canViewResult(array $result, $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    $editAnyEntry = XenForo_Permission::hasPermission(
      $viewingUser['permissions'],
      'lfc',
      'editAnyEntry'
    );

    if ($editAnyEntry || $viewingUser['user_id'] == $result['user_id'])
    {
      return true;
    }

    $contest = $result['contest'];

    if ($contest['contest_state'] == 'visible' && $result['entry_state'] == 'visible')
    {
      if ($contest['hide_entries'])
      {
        return XenForo_Application::$time >= $contest['voting_opens_on'];
      }

      return true;
    }

    return false;
  }

  public function canViewLikesCountForEntries(array &$entries, $contest, $viewingUser = null)
  {
    foreach ($entries as &$entry)
    {
      $this->canViewLikesCountForEntry($entry, $contest, $viewingUser);
    }
  }

  public function canViewLikesCountForEntry(&$entry, $contest, $viewingUser = null)
  {
    if ($contest['isClosedToVotes'] == false)
    {
      $showOwnLikesCountOnly = XenForo_Permission::hasPermission(
        $viewingUser['permissions'],
        'lfc',
        'viewLikeCount'
      ) == false;

      if ($showOwnLikesCountOnly && $contest['user_id'] != $viewingUser['user_id'])
      {
        if ($entry['like_date'])
        {
          $entry['likes'] = 1;
        }
        else
        {
          $entry['likes'] = 0;
        }
      }
    }
  }
}
