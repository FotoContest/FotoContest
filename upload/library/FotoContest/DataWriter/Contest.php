<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_DataWriter_Contest extends XenForo_DataWriter
{
  /**
   * Holds the temporary hash used to pull attachments and associate them with this contest.
   *
   * @var string
   */
  const DATA_ATTACHMENT_HASH = 'attachmentHash';

  /**
   * Option that controls whether an IP address should be recorded for this contest.
   * Defaults to true.
   *
   * @var string
   */
  const OPTION_SET_IP_ADDRESS = 'setIpAddress';

  /**
   * Title of the phrase that will be created when a call to set the
   * existing data fails (when the data doesn't exist).
   *
   * @var string
   */
  protected $_existingDataErrorPhrase = 'lfc_no_record_found';

  /**
   * Gets the fields that are defined for the table. See parent for explanation.
   *
   * @return array
   */
  protected function _getFields()
  {
    return array(
      'xf_lfc_photo_contest' => array(
        'photo_contest_id' => array(
          'type' => self::TYPE_UINT,
          'autoIncrement' => true
        ),
        'title' => array(
          'type' => self::TYPE_STRING,
          'required' => true,
          'maxLength' => 255,
        ),
        'description' => array(
          'type' => self::TYPE_STRING,
          'default' => ''
        ),
        'user_id' => array(
          'type' => self::TYPE_UINT,
          'required' => true
        ),
        'username' => array(
          'type' => self::TYPE_STRING,
          'required' => true,
          'maxLength' => 50,
          'requiredError' => 'please_enter_valid_name'
        ),
        'ip_id' => array(
          'type' => self::TYPE_UINT,
          'default' => 0
        ),
        'posting_opens_on' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'verification' => array('$this', '_verifyDate'),
          'default' => XenForo_Application::$time
        ),
        'posting_closes_on' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'verification' => array('$this', '_verifyDate')
        ),
        'voting_opens_on' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'verification' => array('$this', '_verifyDate')
        ),
        'voting_closes_on' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'verification' => array('$this', '_verifyDate')
        ),
        'contest_state' => array(
          'type' => self::TYPE_STRING,
          'default' => 'visible',
          'allowedValues' => array('visible', 'moderated', 'deleted')
        ),
        'entry_count' => array(
          'type' => self::TYPE_UINT,
        ),
        'thread_id' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'default' => 0
        ),
        'max_winners_count' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'default' => 1
        ),
        'entry_order' => array(
          'type' => self::TYPE_STRING,
          'default' => 'latest',
          'allowedValues' => array('latest', 'oldest', 'most_likes', 'least_likes')
        ),
        'contest_closed' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 0
        ),
        'moderate_entries' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 0
        ),
        'hide_authors' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 0
        ),
        'hide_entries' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 1
        ),
        'is_featured' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 0
        ),
        'max_entry_count' => array(
          'type' => self::TYPE_UINT,
          'default' => 1
        ),
        'allow_tied_winners' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 1
        ),
        'max_images_per_entry' => array(
          'type' => self::TYPE_UINT,
          'default' => 1
        ),
        'max_votes_count' => array(
          'type' => self::TYPE_UINT,
          'default' => 1
        ),
        'post_user_group_ids' => array(
          'type' => self::TYPE_UNKNOWN,
          'default' => '',
          'verification' => array('$this', '_verifyUserGroupIds')
        ),
        'vote_user_group_ids' => array(
          'type' => self::TYPE_UNKNOWN,
          'default' => '',
          'verification' => array('$this', '_verifyUserGroupIds')
        ),
        'created_at' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'default' => XenForo_Application::$time
        ),
        'updated_at' => array(
          'type' => self::TYPE_UINT,
          'required' => true,
          'default' => XenForo_Application::$time
        )
      )
    );
  }

  /**
   * Gets the actual existing data out of data that was passed in. See parent for explanation.
   *
   * @param mixed
   *
   * @return array|false
   */
  protected function _getExistingData($data)
  {
    if (!$id = $this->_getExistingPrimaryKey($data))
    {
      return false;
    }

    return array('xf_lfc_photo_contest' => $this->_getModel()->getContestById($id));
  }

  /**
   * Gets SQL condition to update the existing record.
   *
   * @return string
   */
  protected function _getUpdateCondition($tableName)
  {
    return 'photo_contest_id = ' . $this->_db->quote($this->getExisting('photo_contest_id'));
  }

  /**
   * Gets the current value of the contest ID for this contest.
   *
   * @return integer
   */
  public function getContestId()
  {
    return $this->get('photo_contest_id');
  }

  public function getStateField()
  {
    return 'contest_state';
  }

  public function getContentId()
  {
    return $this->getContestId();
  }

  /**
  * Gets the default set of options for this data writer.
  *
  * @return array
  */
  protected function _getDefaultOptions()
  {
    return array(
      self::OPTION_SET_IP_ADDRESS => true
    );
  }

  /**
   * Gets the content type for tables that contain multiple data types together.
   *
   * @return string
   */
  public function getContentType()
  {
    return 'lfc';
  }

  /**
   * The name of the table that holds this contest.
   *
   * @return string
   */
  public function getContestTableName()
  {
    return 'xf_lfc_photo_contest';
  }

  protected function _verifyDate($value, $dw, $field)
  {
    if (empty($value))
    {
      $this->error(new XenForo_Phrase(
        'please_enter_value_for_required_field_x',
        array('field' => $field), $field, false
        ), $field
      );
      return false;
    }

    return true;
  }

  /**
  * Upates the IP data.
  */
  protected function _updateIpData()
  {
    if (!empty($this->_extraData['ipAddress']))
    {
      $ipAddress = $this->_extraData['ipAddress'];
    }
    else
    {
      $ipAddress = null;
    }

    $ipId = XenForo_Model_Ip::log(
      $this->get('user_id'), $this->getContentType(), $this->getContestId(), 'insert', $ipAddress
    );
    $this->set('ip_id', $ipId, '', array('setAfterPreSave' => true));

    $this->_db->update($this->getContestTableName(), array(
      'ip_id' => $ipId
    ), 'photo_contest_id = ' .  $this->_db->quote($this->getContestId()));
  }

  /**
  * Generic pre-save handler.
  */
  protected final function _preSave()
  {
    if ($this->isInsert() && !$this->isChanged('contest_state'))
    {
      $this->set('contest_state', 'visible');
    }

    if ($this->isInsert() && !$this->get('posting_opens_on'))
    {
      $this->set('posting_opens_on', XenForo_Application::$time);
    }

    if ($this->isUpdate() && $this->hasChanges())
    {
      $this->set('updated_at', XenForo_Application::$time);
    }

    if ($this->get('posting_opens_on') >= $this->get('posting_closes_on'))
    {
      $this->mergeErrors(
        array('posting_closes_on' => new XenForo_Phrase(
            'lfc_error_contest_closing_date_after_opening_date'
          )
        )
      );
    }

    if ($this->get('voting_opens_on') >= $this->get('voting_closes_on'))
    {
      $this->mergeErrors(
        array('voting_closes_on' => new XenForo_Phrase(
            'lfc_error_voting_close_date_after_voting_open_date'
          )
        )
      );
    }
  }

  /**
  * Generic post-save handler.
  */
  protected function _postSave()
  {
    if ($this->isInsert() && $this->getOption(self::OPTION_SET_IP_ADDRESS) && !$this->get('ip_id'))
    {
      $this->_updateIpData();
    }

    $attachmentHash = $this->getExtraData(self::DATA_ATTACHMENT_HASH);

    if ($attachmentHash)
    {
      $this->_associateAttachment($attachmentHash);
    }
  }

  protected function _postDelete()
  {
    XenForo_Application::defer(
      'FotoContest_Deferred_Contest',
      $this->getMergedData()
    );
  }

  /**
   * Associates attachment with this contest.
   *
   * @param string $attachmentHash
   */
  protected function _associateAttachment($attachmentHash)
  {
    $this->_db->update('xf_attachment', array(
      'content_type' => $this->getContentType(),
      'content_id' => $this->getContestId(),
      'temp_hash' => '',
      'unassociated' => 0
    ), 'temp_hash = ' . $this->_db->quote($attachmentHash));
  }

  /**
   * Verifies the user group IDs.
   *
   * @param array|string $userGroupIds Array or comma-delimited list
   *
   * @return boolean
   */
  protected function _verifyUserGroupIds(&$userGroupIds)
  {
    if (!is_array($userGroupIds))
    {
      $userGroupIds = preg_split('#,\s*#', $userGroupIds);
    }

    $userGroupIds = array_map('intval', $userGroupIds);
    $userGroupIds = array_unique($userGroupIds);
    sort($userGroupIds, SORT_NUMERIC);
    $userGroupIds = implode(',', $userGroupIds);

    return true;
  }

  /**
   * @return FotoContest_Model_Contest
   */
  protected function _getModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Contest');
  }

}
