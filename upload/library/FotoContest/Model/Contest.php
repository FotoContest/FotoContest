<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_Contest extends XenForo_Model
{

  const FETCH_USER = 0x01;

  /**
   * Get Contest by ID
   *
   * @param integer $contestId
   *
   * @return array|false
   */
  public function getContestById($contestId, array $fetchOptions = array())
  {
    $joinOptions = $this->prepareContestJoinOptions($fetchOptions);

    return $this->_getDb()->fetchRow('
      SELECT
        photo_contest.*
        ' . $joinOptions['selectFields'] . '
      FROM
        xf_lfc_photo_contest AS photo_contest
      ' . $joinOptions['joinTables'] . '
      WHERE
        photo_contest.photo_contest_id = ?', $contestId
    );
  }

  public function getContests(array $conditions, array $fetchOptions)
  {
    $joinOptions  = $this->prepareContestJoinOptions($fetchOptions);
    $orderClause  = $this->prepareContestOrderOptions($fetchOptions, 'created_at');
    $whereClause  = $this->prepareContestConditions($conditions, $fetchOptions);
    $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

    return $this->fetchAllKeyed($this->limitQueryResults('
        SELECT
          photo_contest.*
          ' . $joinOptions['selectFields'] . '
        FROM
          xf_lfc_photo_contest as photo_contest' . $joinOptions['joinTables'] . '
        WHERE ' . $whereClause . '
        ' . $orderClause . '
      ', $limitOptions['limit'], $limitOptions['offset']
    ), 'photo_contest_id');
  }

  public function countContests(array $conditions)
  {
    $fetchOptions = array();
    $whereClause = $this->prepareContestConditions($conditions, $fetchOptions);
    $joinOptions = $this->prepareLimitFetchOptions($fetchOptions);

    return $this->_getDb()->fetchOne('
      SELECT
        COUNT(*)
      FROM
        xf_lfc_photo_contest
      WHERE '
      . $whereClause
    );
  }

  /**
   * Helper to delete the specified contest, via a soft or hard delete.
   *
   * @param integer $contest the contest to delete
   * @param string $deleteType Type of deletion (soft or hard)
   * @param array $options Deletion options. Currently unused.
   *
   * @return FotoContest_DataWriter_Contest The DW used to delete the contest
   */
  public function deleteContest($contest, $deleteType, array $options = array())
  {
    $options = array_merge(array(
      'reason' => ''
    ), $options);

    $dw = XenForo_DataWriter::create('FotoContest_DataWriter_Contest');
    $dw->setExistingData($contest);
    if ($deleteType == 'hard')
    {
      $dw->delete();
    }
    else
    {
      $dw->setExtraData(FotoContest_Shared_XFCP_DataWriter_DeleteLog::DATA_DELETE_REASON, $options['reason']);
      $dw->set('contest_state', 'deleted');
      $dw->save();
    }

    return $dw;
  }

  public function prepareContestConditions(array $conditions, array &$fetchOptions)
  {
    $db = $this->_getDb();
    $sqlConditions = array();

    if (isset($conditions['contest_closed']))
    {
      if ($conditions['contest_closed'])
      {
        $sqlConditions[] = 'photo_contest.contest_closed = 1';
      }
      else
      {
        $sqlConditions[] = 'photo_contest.contest_closed = 0';
      }
    }

    if (isset($conditions['is_featured']))
    {
      if ($conditions['is_featured'])
      {
        $sqlConditions[] = 'photo_contest.is_featured = 1';
      }
      else
      {
        $sqlConditions[] = 'photo_contest.is_featured = 0';
      }
    }

    if (isset($conditions['contest_state']))
    {
      $sqlConditions[] = 'photo_contest.contest_state = ' . $this->_getDb()->quote($conditions['contest_state']);
    }

    return $this->getConditionsForClause($sqlConditions);
  }

  public function prepareContestOrderOptions(array &$fetchOptions, $defaultOrderSql = 'title')
  {
    $choices = array(
      'title'             => 'title',
      'created_at'        => 'created_at',
      'posting_opens_on'  => 'posting_opens_on',
      'posting_closes_on' => 'posting_closes_on',
      'voting_opens_on'   => 'voting_opens_on',
      'voting_closes_on'  => 'voting_closes_on'
    );

    return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
  }

  public function getContestIdsInRange($contestId, $limit)
  {
    return $this->_getDb()->fetchCol($this->_getDb()->limit('
      SELECT
        photo_contest_id
      FROM
        xf_lfc_photo_contest
      WHERE
        photo_contest_id > ?
      ORDER BY
        photo_contest_id
    ', $limit), $contestId);
  }

  public function getContestsByIds(array $contestIds, array $fetchOptions = array())
  {
    $joinOptions = $this->prepareContestJoinOptions($fetchOptions);

    if (empty($contestIds))
    {
      return;
    }

    return $this->fetchAllKeyed('
      SELECT photo_contest.*
        ' . $joinOptions['selectFields'] . '
      FROM xf_lfc_photo_contest as photo_contest
        ' . $joinOptions['joinTables'] . '
      WHERE photo_contest.photo_contest_id IN (' . $this->_getDb()->quote($contestIds) . ')
    ', 'photo_contest_id');
  }

  public function canDeleteContest(array $contest, $deleteType = 'soft', &$errorPhraseKey = '', array $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    if (!$viewingUser['user_id'])
    {
      return false;
    }

    if ($deleteType != 'soft' && !XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'hardDeleteAny'))
    {
      return false;
    }

    if ($viewingUser['user_id'] == $contest['user_id'])
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'deleteOwn');
    }
    else
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'deleteAny');
    }
  }

  public function canUnDeleteContest(array $contest, &$errorPhraseKey = '', array $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);
    return $viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'unDelete');
  }

  public function canEditContest(array $contest, &$errorPhraseKey = '', array $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    if (!$viewingUser['user_id'])
    {
      return false;
    }

    if ($viewingUser['user_id'] == $contest['user_id'])
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'editOwn');
    }
    else
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'editAny');
    }
  }

  public function prepareContests($contests, array $viewingUser = null)
  {
    foreach ($contests as &$contest)
    {
      $contest = $this->prepareContest($contest, $viewingUser);
    }
    return $contests;
  }

  public function prepareContest(array $contest, array $viewingUser = null)
  {
    $contest['canEdit']     = $this->canEditContest($contest, $null, $viewingUser);
    $contest['canDelete']   = $this->canDeleteContest($contest, 'soft', $null, $viewingUser);
    $contest['canUnDelete'] = $this->canUnDeleteContest($contest, $null, $viewingUser);

    // entries

    $contest['isOpenToEntries']  = (
      XenForo_Application::$time >= $contest['posting_opens_on']
        &&
      XenForo_Application::$time <= $contest['posting_closes_on']
    );

    $contest['willOpenToEntries']  = (
      XenForo_Application::$time < $contest['posting_opens_on']
        &&
      XenForo_Application::$time < $contest['posting_closes_on']
    );

    $contest['isClosedToEntries']  = (
      XenForo_Application::$time > $contest['posting_opens_on']
        &&
      XenForo_Application::$time > $contest['posting_closes_on']
    );

    // votes

    $contest['isOpenToVotes']  = (
      XenForo_Application::$time >= $contest['voting_opens_on']
        &&
      XenForo_Application::$time <= $contest['voting_closes_on']
    );

    $contest['willOpenToVotes']  = (
      XenForo_Application::$time < $contest['voting_opens_on']
        &&
      XenForo_Application::$time < $contest['voting_closes_on']
    );

    $contest['isClosedToVotes']  = (
      XenForo_Application::$time > $contest['voting_opens_on']
        &&
      XenForo_Application::$time > $contest['voting_closes_on']
    );

    return $contest;
  }

  public function getAndMergeAttachmentIntoContest($contest)
  {
    $attachmentModel = $this->_getAttachmentModel();
    $attachments     = $attachmentModel->getAttachmentsByContentId(
      'lfc', $contest['photo_contest_id']
    );

    if ($attachments)
    {
      $contest['attachment'] = $attachmentModel->prepareAttachment(current($attachments));
    }
    return $contest;
  }

  /**
   * @param array $contestEntries
   *
   * @return array Contests, with attachments added where necessary
   */
  public function getAndMergeAttachmentsIntoContests(array $contests)
  {
    $contestIds = array_keys($contests);

    if ($contestIds)
    {
      $attachmentModel = $this->_getAttachmentModel();
      $attachments = $attachmentModel->getAttachmentsByContentIds(
        'lfc', $contestIds
      );

      foreach ($attachments AS $attachment)
      {
        $contests[
          $attachment['content_id']
        ]['attachment'] = $attachmentModel->prepareAttachment($attachment);
      }
    }

    return $contests;
  }

  /**
   * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
   * and returns SQL snippets to join the specified tables if required
   *
   * @param array $fetchOptions containing a 'join' integer key build from this class's FETCH_x bitfields
   *
   * @return array Containing 'selectFields' and 'joinTables' keys. Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '
   */
  public function prepareContestJoinOptions(array $fetchOptions)
  {
    $selectFields = '';
    $joinTables = '';

    $db = $this->_getDb();

    if (!empty($fetchOptions['join']))
    {
      if ($fetchOptions['join'] & self::FETCH_USER)
      {
        $selectFields .= ', user.username, user.avatar_date, user.avatar_width, user.avatar_height, user.gravatar';

        $joinTables .= '
          LEFT JOIN xf_user AS user
            ON (user.user_id = contest.user_id)
        ';
      }
    }

    return array(
      'selectFields' => $selectFields,
      'joinTables'   => $joinTables
    );
  }

  public function updateEntryCount($contestId)
  {
    $entryCount = $this->_getDb()->fetchOne('
      SELECT
        COUNT(*)
      FROM
        xf_lfc_photo_contest_entry
      WHERE
        entry_state = "visible"
          AND
      ' . 'photo_contest_id = ' . $this->_getDb()->quote($contestId)
    );

    $this->_getDb()->update('xf_lfc_photo_contest',
      array('entry_count' => $entryCount),
      'photo_contest_id = ' . $this->_getDb()->quote($contestId)
    );
  }

  /**
   * @return XenForo_Model_Attachment
   */
  protected function _getAttachmentModel()
  {
    return $this->getModelFromCache('XenForo_Model_Attachment');
  }
}
