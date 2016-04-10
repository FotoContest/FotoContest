<?php

class FotoContest_Model_Permission_Entry_CanLike extends Xenforo_Model
{
  public function executeMulti($contest, &$entries, $viewingUser = null)
  {
    foreach ($entries as $key => &$entry)
    {
      $this->execute($contest, $entry, $viewingUser);
    }
  }

  public function execute($contest, &$entry, $viewingUser = null)
  {
    $entry['canLike'] = $this->_hasUserGroupPermission(
      $entry, $viewingUser
    ) && $this->_contestAllowsUsergroup(
      $contest, $entry, $viewingUser
    );
  }

  protected function _contestAllowsUsergroup($contest, $entry, $viewingUser = null)
  {
    $voteUserGroupIds = $contest['vote_user_group_ids'];
    $userGroupIdsList = explode(',', $voteUserGroupIds);
    $allGroupsAllowed = $voteUserGroupIds == -1;
    $userGroupAllowed = XenForo_Template_Helper_Core::helperIsMemberOf(
      $viewingUser, $userGroupIdsList
    );

    return $allGroupsAllowed || $userGroupAllowed;
  }

  protected function _hasUserGroupPermission($entry, $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    if (!$viewingUser['user_id'])
    {
      return false;
    }

    if ($entry['user_id'] == $viewingUser['user_id'])
    {
      return false;
    }

    return XenForo_Permission::hasPermission(
      $viewingUser['permissions'],
      'lfc',
      'like'
    );
  }
}
