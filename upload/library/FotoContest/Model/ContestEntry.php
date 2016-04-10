<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_ContestEntry extends FotoContest_Shared_Model
{
  protected $_structureModel = 'FotoContest_Structure_ContestEntry';

  const FETCH_WINNER = 0x02;
  const FETCH_CONTEST = 0x03;
  const FETCH_CONTEST_VISIBLE = 0x04;
  const FETCH_WINNERS_ONLY = 0x08;

  /**
   * Get Contest Entry by ID
   *
   * @param integer $contestEntryId
   *
   * @return array|false
   */
  public function getContestEntryById($contestEntryId, array $fetchOptions = array())
  {
    $joinOptions = $this->prepareContestEntryJoinOptions($fetchOptions);

    return $this->_getDb()->fetchRow('
      SELECT
        photo_contest_entry.*
        ' . $joinOptions['selectFields'] . '
      FROM
        xf_lfc_photo_contest_entry AS photo_contest_entry
      ' . $joinOptions['joinTables'] . '
      WHERE
        photo_contest_entry.photo_contest_entry_id = ?', $contestEntryId
    );
  }

  // Alias for above
  public function getByid($contestEntryId, array $fetchOptions = array()){
    return $this->getContestEntryById($contestEntryId, $fetchOptions);
  }

  /**
   * Get Contest Entry by Thread ID
   *
   * @param integer $threadId
   *
   * @return array|false
   */
  public function getContestEntryByThreadId($threadId, array $fetchOptions = array())
  {
    $joinOptions = $this->prepareContestEntryJoinOptions($fetchOptions);

    return $this->_getDb()->fetchRow('
      SELECT
        photo_contest_entry.*
        ' . $joinOptions['selectFields'] . '
      FROM
        xf_lfc_photo_contest_entry AS photo_contest_entry
      ' . $joinOptions['joinTables'] . '
      WHERE
        photo_contest_entry.thread_id = ?', $threadId
    );
  }

  public function getAllContestEntries(array $conditions, array $fetchOptions)
  {
    $joinOptions  = $this->prepareContestEntryJoinOptions($fetchOptions);
    $orderClause  = $this->prepareContestEntryOrderOptions($fetchOptions, 'created_at');
    $whereClause  = $this->prepareContestEntryConditions($conditions, $fetchOptions);
    $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

    return $this->fetchAllKeyed($this->limitQueryResults('
        SELECT
          photo_contest_entry.*
          ' . $joinOptions['selectFields'] . '
        FROM
          xf_lfc_photo_contest_entry as photo_contest_entry' . $joinOptions['joinTables'] . '
        WHERE ' . $whereClause . '
        ' . $orderClause . '
      ', $limitOptions['limit'], $limitOptions['offset']
    ), 'photo_contest_entry_id');
  }

  public function countContestEntries(array $conditions)
  {
    $fetchOptions = array();
    $whereClause = $this->prepareContestEntryConditions($conditions, $fetchOptions);
    $joinOptions = $this->prepareLimitFetchOptions($fetchOptions);

    return $this->_getDb()->fetchOne('
      SELECT
        COUNT(*)
      FROM
        xf_lfc_photo_contest_entry
      WHERE '
      . $whereClause
    );
  }

  public function countContestEntriesForContestAndUser(array $contest, array $user)
  {
    $db = $this->_getDb();
    return $db->fetchOne('
      SELECT
        COUNT(*)
      FROM
        xf_lfc_photo_contest_entry
      WHERE
        entry_state IN ("visible", "moderated")
          AND
        photo_contest_id = ' . $db->quote($contest['photo_contest_id']) . '
          AND
        user_id = ' . $db->quote($user['user_id'])
    );
  }

  /**
   * Helper to delete the specified contest entry, via a soft or hard delete.
   *
   * @param array $contestEntry contest entry
   * @param string $deleteType Type of deletion (soft or hard)
   * @param array $options Deletion options. Currently unused.
   *
   * @return FotoContest_DataWriter_ContestEntry The DW used to delete the contest
   */
  public function deleteContestEntry($contestEntry, $deleteType, array $options = array())
  {
    $options = array_merge(array(
      'reason' => ''
    ), $options);

    $dw = XenForo_DataWriter::create('FotoContest_DataWriter_ContestEntry');
    $dw->setExistingData($contestEntry);
    if ($deleteType == 'hard')
    {
      $dw->delete();
    }
    else
    {
      $dw->setExtraData(FotoContest_Shared_XFCP_DataWriter_DeleteLog::DATA_DELETE_REASON, $options['reason']);
      $dw->set('entry_state', 'deleted');
      $dw->save();
    }

    return $dw;
  }

  /**
   * Gets contest entries by contest ID
   *
   * @param integer $contestId
   * @param array   $fetchOptions
   *
   * @return array|false
   */
  public function getContestEntriesInContest($contestId, array $conditions = array(), array $fetchOptions = array())
  {
    $conditions['photo_contest_id'] = $contestId;

    return $this->getAllContestEntries($conditions, $fetchOptions);
  }

  public function getAndMergeAttachmentsIntoContestEntry($contestEntry)
  {
    $attachmentModel = $this->_getAttachmentModel();
    $attachments     = $attachmentModel->getAttachmentsByContentId(
      'lfc_entry', $contestEntry['photo_contest_entry_id']
    );

    $contestEntry['attachments'] = array_reverse(
      $attachmentModel->prepareAttachments($attachments)
    );

    return $contestEntry;
  }

  /**
   * @param array $contestEntries
   *
   * @return array Contest Entries, with attachments added where necessary
   */
  public function getAndMergeAttachmentsIntoContestEntries(array $contestEntries)
  {
    $contestEntryIds = array_keys($contestEntries);

    if ($contestEntryIds)
    {
      $attachmentModel = $this->_getAttachmentModel();
      $attachments = $attachmentModel->getAttachmentsByContentIds(
        'lfc_entry', $contestEntryIds
      );

      foreach ($attachments AS $attachment)
      {
        $contestEntries[
          $attachment['content_id']
        ]['attachment'] = $attachmentModel->prepareAttachment($attachment);
      }
    }

    return $contestEntries;
  }

  public function prepareContestEntryConditions(array $conditions, array &$fetchOptions)
  {
    $db = $this->_getDb();
    $sqlConditions = array();

    if (!empty($conditions['photo_contest_id']))
    {
      $sqlConditions[] = 'photo_contest_entry.photo_contest_id = ' . $db->quote($conditions['photo_contest_id']);
    }

    if (!empty($conditions['entry_state']))
    {
      if (is_array($conditions['entry_state']))
      {
        $sqlConditions[] = 'photo_contest_entry.entry_state IN (' . $db->quote($conditions['entry_state']) . ')';
      }
      else
      {
        $sqlConditions[] = 'photo_contest_entry.entry_state = ' . $db->quote($conditions['entry_state']);
      }
    }

    if (isset($conditions['user_id']) && !empty($conditions['user_id']))
    {
      $sqlConditions[] = 'photo_contest_entry.user_id = ' . $db->quote($conditions['user_id']);
    }

    if (isset($conditions['deleted']) || isset($conditions['moderated']))
    {
      $sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'photo_contest_entry', 'entry_state');
    }

    return $this->getConditionsForClause($sqlConditions);
  }

  public function prepareContestEntryOrderOptions(array &$fetchOptions, $defaultOrderSql = 'title')
  {
    $choices = array(
      'title' => 'photo_title',
      'created_at' => 'created_at',
      'likes' => 'likes',
    );

    if (isset($fetchOptions['order']) == false)
    {
      $fetchOptions['order'] = 'latest';
    }

    if ($fetchOptions['order'] == 'latest')
    {
      $fetchOptions['order'] = 'created_at';
      $fetchOptions['direction'] = 'desc';
    }

    if ($fetchOptions['order'] == 'oldest')
    {
      $fetchOptions['order'] = 'created_at';
      $fetchOptions['direction'] = 'ASC';
    }

    if ($fetchOptions['order'] == 'most_likes')
    {
      $fetchOptions['order'] = 'likes';
      $fetchOptions['direction'] = 'desc';
    }

    if ($fetchOptions['order'] == 'least_likes')
    {
      $fetchOptions['order'] = 'likes';
      $fetchOptions['direction'] = 'ASC';
    }

    return $this->getOrderByClause($choices, $fetchOptions, $defaultOrderSql);
  }

  public function getContestEntriesByIds(array $contestEntryIds, array $fetchOptions = array())
  {
    $joinOptions = $this->prepareContestEntryJoinOptions($fetchOptions);

    return $this->fetchAllKeyed('
      SELECT photo_contest_entry.*
        ' . $joinOptions['selectFields'] . '
      FROM xf_lfc_photo_contest_entry as photo_contest_entry
        ' . $joinOptions['joinTables'] . '
      WHERE photo_contest_entry.photo_contest_entry_id IN (' . $this->_getDb()->quote($contestEntryIds) . ')
    ', 'photo_contest_entry_id');
  }

  public function canDeleteContestEntry(array $contestEntry, $deleteType = 'soft', &$errorPhraseKey = '', array $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    if (!$viewingUser['user_id'])
    {
      return false;
    }

    if ($deleteType != 'soft' && !XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'hardDeleteAnyEntry'))
    {
      return false;
    }

    if ($viewingUser['user_id'] == $contestEntry['user_id'])
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'deleteOwnEntry');
    }
    else
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'deleteAnyEntry');
    }
  }

  public function canUnDeleteContestEntry(array $contestEntry, &$errorPhraseKey = '', array $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);
    return $viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'unDelete');
  }

  public function canEditContestEntry(array $contestEntry, &$errorPhraseKey = '', array $viewingUser = null)
  {
    $this->standardizeViewingUserReference($viewingUser);

    if (!$viewingUser['user_id'])
    {
      return false;
    }

    if ($viewingUser['user_id'] == $contestEntry['user_id'])
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'editOwnEntry');
    }
    else
    {
      return XenForo_Permission::hasPermission($viewingUser['permissions'], 'lfc', 'editAnyEntry');
    }
  }

  public function canReportContestEntry(array $contestEntry, &$errorPhraseKey = '', array $viewingUser = null)
  {
    return $this->_getUserModel()->canReportContent($errorPhraseKey, $viewingUser);
  }

  public function prepareContestEntries($contestEntries, array $viewingUser = null)
  {
    foreach ($contestEntries as &$contestEntry)
    {
      $contestEntry = $this->prepareContestEntry($contestEntry, $viewingUser);
    }
    return $contestEntries;
  }

  public function prepareContestEntry(array $contestEntry, array $viewingUser = null)
  {
    $contestEntry['canEdit']   = $this->canEditContestEntry($contestEntry, $null, $viewingUser);
    $contestEntry['canDelete'] = $this->canDeleteContestEntry($contestEntry, 'soft', $null, $viewingUser);
    $contestEntry['canReport'] = $this->canReportContestEntry($contestEntry, $null, $viewingUser);
    $contestEntry['likeUsers'] = unserialize($contestEntry['like_users']);

    return $contestEntry;
  }

  /**
   * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
   * and returns SQL snippets to join the specified tables if required
   *
   * @param array $fetchOptions containing a 'join' integer key build from this class's FETCH_x bitfields
   *
   * @return array Containing 'selectFields' and 'joinTables' keys. Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '
   */
  public function prepareContestEntryJoinOptions(array $fetchOptions)
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
            ON (user.user_id = photo_contest_entry.user_id)
        ';
      }

      if ($fetchOptions['join'] & self::FETCH_WINNER)
      {
        $selectFields .= ', lfcw.position';

        $joinTables .= '
          LEFT JOIN xf_lfc_photo_contest_winner AS lfcw
            ON (lfcw.photo_contest_entry_id = photo_contest_entry.photo_contest_entry_id)
        ';
      }
    }

    if (isset($fetchOptions['likeUserId']))
    {
      if (empty($fetchOptions['likeUserId']))
      {
        $selectFields .= ',
          0 AS like_date';
      }
      else
      {
        $selectFields .= ',
          liked_content.like_date';
        $joinTables .= '
          LEFT JOIN xf_liked_content AS liked_content
            ON (liked_content.content_type = \'lfc_entry\'
              AND liked_content.content_id = photo_contest_entry.photo_contest_entry_id
              AND liked_content.like_user_id = ' .$db->quote($fetchOptions['likeUserId']) . ')';
      }
    }

    return array(
      'selectFields' => $selectFields,
      'joinTables'   => $joinTables
    );
  }

  public function prepareJoinOptions(array $fetchOptions)
  {
    $joinOptions = parent::prepareJoinOptions($fetchOptions);

    if (!empty($fetchOptions['join']))
    {
      if ($fetchOptions['join'] & self::FETCH_CONTEST)
      {
        $joinOptions['selectFields'] .= ',
          contest.title as photo_contest_title,
          contest.contest_state as photo_contest_state,
          contest.hide_authors as photo_contest_hide_authors,
          contest.hide_entries as photo_contest_hide_entries,
          contest.voting_opens_on as photo_contest_voting_opens_on
        ';

        $joinOptions['joinTables'] .= '
          LEFT JOIN xf_lfc_photo_contest AS contest
            ON (contest.photo_contest_id = xf_lfc_photo_contest_entry.photo_contest_id)
        ';
      }

      //TEMP: join to only fetch visible contest entries
      if ($fetchOptions['join'] & self::FETCH_CONTEST_VISIBLE)
      {
        $joinOptions['joinTables'] .= '
          JOIN xf_lfc_photo_contest
            ON (
              xf_lfc_photo_contest.photo_contest_id = xf_lfc_photo_contest_entry.photo_contest_id
                AND
              xf_lfc_photo_contest.contest_state = "visible"
            )
        ';
      }

      if ($fetchOptions['join'] & self::FETCH_WINNERS_ONLY)
      {
        $joinOptions['joinTables'] .= '
          RIGHT JOIN xf_lfc_photo_contest_winner
            ON (
              xf_lfc_photo_contest_winner.photo_contest_entry_id = xf_lfc_photo_contest_entry.photo_contest_entry_id
            )
        ';
      }
    }

    return $joinOptions;
  }

  /**
   * @return XenForo_Model_Attachment
   */
  protected function _getAttachmentModel()
  {
    return $this->getModelFromCache('XenForo_Model_Attachment');
  }

  /**
   * @return XenForo_Model_User
   */
  protected function _getUserModel()
  {
    return $this->getModelFromCache('XenForo_Model_User');
  }
}
