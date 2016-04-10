<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_DataWriter_ContestEntry extends FotoContest_Shared_DataWriter
{
  /**
   * Holds the temporary hash used to pull attachments and associate them with this contest entry.
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
  protected $_existingDataErrorPhrase = 'lfc_entry_no_record_found';

  /**
   * Gets the fields that are defined for the table. See parent for explanation.
   *
   * @return array
   */
  protected function _getFields()
  {
    return array(
      'xf_lfc_photo_contest_entry' => array(
        'photo_contest_entry_id' => array(
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
        'likes' => array(
          'type' => self::TYPE_UINT_FORCED,
          'default' => 0
        ),
        'like_users' => array(
          'type' => self::TYPE_SERIALIZED,
          'default' => 'a:0:{}'
        ),
        'ip_id' => array(
          'type' => self::TYPE_UINT,
          'default' => 0
        ),
        'thread_id' => array(
          'type' => self::TYPE_UINT,
          'default' => 0
        ),
        'entry_state' => array(
          'type' => self::TYPE_STRING,
          'default' => 'visible',
          'allowedValues' => array('visible', 'moderated', 'deleted')
        ),
        'attach_count' => array(
          'type' => self::TYPE_UINT_FORCED,
          'default' => 0,
          'max' => 65535
        ),
        'photo_contest_id' => array(
          'type' => self::TYPE_UINT,
          'required' => true
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
   * Gets the current value of the contest ID for this contest.
   *
   * @return integer
   */
  public function getContestEntryId()
  {
    return $this->get('photo_contest_entry_id');
  }

  public function getContentId()
  {
    return $this->getContestEntryId();
  }

  public function getStateField()
  {
    return 'entry_state';
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
    return 'lfc_entry';
  }

  /**
   * The name of the table that holds this contest.
   *
   * @return string
   */
  public function getContestTableName()
  {
    return 'xf_lfc_photo_contest_entry';
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
      $this->get('user_id'), $this->getContentType(), $this->getContestEntryId(), 'insert', $ipAddress
    );
    $this->set('ip_id', $ipId, '', array('setAfterPreSave' => true));

    $this->_db->update($this->getContestTableName(), array(
      'ip_id' => $ipId
    ), 'photo_contest_entry_id = ' .  $this->_db->quote($this->getContestEntryId()));
  }

  /**
  * Generic pre-save handler.
  */
  protected final function _preSave()
  {
    if ($this->isInsert() && !$this->isChanged('entry_state'))
    {
      $this->set('entry_state', 'visible');
    }

    if ($this->isInsert())
    {
      $attachmentModel = $this->_getAttachmentModel();
      $attachmentHash  = $this->getExtraData(self::DATA_ATTACHMENT_HASH);
      $attachments     = $attachmentModel->getAttachmentsByTempHash($attachmentHash);

      if (empty($attachments))
      {
        $this->error(new XenForo_Phrase('lfc_please_upload_your_contest_entry'), 'attachment_hash');
      }
    }

    if ($this->isUpdate() && $this->hasChanges())
    {
      $this->set('updated_at', XenForo_Application::$time);
    }
  }

  /**
  *  Post-save handler.
  */
  protected function _postSave()
  {
    if ($this->isInsert() && $this->getOption(self::OPTION_SET_IP_ADDRESS) && !$this->get('ip_id'))
    {
      $this->_updateIpData();
    }

    $this->_updateEntryCount();

    if ($this->isInsert())
    {
      $this->_insertDiscussionThread();
    }

  }

  protected function _getThreadTitle()
  {
    $title = str_replace(
      '{title}',
      $this->get('title'),
      XenForo_Application::get('options')->lfcEntryThreadTitle
    );
    return $title;
  }

  protected function _getThreadUserInfo()
  {
    $contest = $this->_getContestModel()->getContestById($this->get('photo_contest_id'));
    if ($contest['hide_authors'])
    {
      return array(
        'user_id'  => $contest['user_id'],
        'username' => $contest['username']
      );
    }
    else
    {
      return array(
        'user_id'  => $this->get('user_id'),
        'username' => $this->get('username')
      );
    }
  }

  protected function _insertDiscussionThread()
  {
    $nodeId = XenForo_Application::get('options')->lfcEntryForumId;

    if (!$nodeId) return false;

    $forum = $this->getModelFromCache('XenForo_Model_Forum')->getForumById($nodeId);
    if (!$forum) return false;

    $userInfo = $this->_getThreadUserInfo();
    $threadDw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
    $threadDw->setExtraData(XenForo_DataWriter_Discussion_Thread::DATA_FORUM, $forum);
    $threadDw->bulkSet(array_merge(
      array(
      'node_id' => $nodeId,
      'title' => $this->_getThreadTitle(),
      'discussion_type' => 'lfc_entry'
      ),
      $userInfo
    ));

    $threadDw->set('discussion_state', $this->getModelFromCache('XenForo_Model_Post')->getPostInsertMessageState(array(), $forum));
    $threadDw->setOption(XenForo_DataWriter_Discussion::OPTION_PUBLISH_FEED, false);

    if ($this->get('description'))
    {
      $messageText = $this->get('description');

      // note: this doesn't actually strip the BB code - it will fix the BB code in the snippet though
      $parser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('XenForo_BbCode_Formatter_BbCode_AutoLink', false));
      $snippet = $parser->render(XenForo_Helper_String::wholeWordTrim($messageText, 500));
    }
    else
    {
      $snippet = '';
    }

    $phraseKey = 'lfc_contest_entry_thread';
    if ($userInfo['user_id'] != $this->get('user_id'))
    {
      $phraseKey = 'lfc_contest_entry_thread_someone';
    }

    $message = new XenForo_Phrase($phraseKey, array_merge(
      array(
      'title' => $this->get('title'),
      'snippet' => $snippet,
      'contestEntryLink' => XenForo_Link::buildPublicLink(
        'canonical:photo-contest-entries', $this->getMergedData()
      )),
      $userInfo),
    false);

    $postWriter = $threadDw->getFirstMessageDw();
    $postWriter->set('message', $message->render());
    $postWriter->setExtraData(XenForo_DataWriter_DiscussionMessage_Post::DATA_FORUM, $forum);
    $postWriter->setOption(XenForo_DataWriter_DiscussionMessage::OPTION_PUBLISH_FEED, false);

    if (!$threadDw->save())
    {
      return false;
    }

    $this->set('discussion_thread_id',
      $threadDw->get('thread_id'), '', array('setAfterPreSave' => true)
    );

    $this->getModelFromCache('XenForo_Model_Thread')->markThreadRead(
      $threadDw->getMergedData(), $forum, XenForo_Application::$time
    );

    $this->getModelFromCache('XenForo_Model_ThreadWatch')->setThreadWatchStateWithUserDefault(
      $this->get('user_id'), $threadDw->get('thread_id'),
      $this->getExtraData('watchDefault')
    );

    $this->_db->update($this->getContestTableName(), array(
      'thread_id' => $threadDw->get('thread_id')
    ), 'photo_contest_entry_id = ' .  $this->_db->quote($this->getContestEntryId()));
  }

  /**
   *  Post-delete handler.
   */
  protected function _postDelete()
  {
    if ($this->get('likes'))
    {
      $this->_deleteLikes();
    }

    $this->_deleteFromNewsFeed();

    $this->_updateEntryCount();
  }


  /**
   * Delete all like entries for content.
   */
  protected function _deleteLikes()
  {
    $updateUserLikeCounter = ($this->get('entry_state') == 'visible');

    $this->getModelFromCache('XenForo_Model_Like')->deleteContentLikes(
      $this->getContentType(), $this->getContestEntryId(), $updateUserLikeCounter
    );
  }


  /**
   * Removes an already published news feed item
   */
  protected function _deleteFromNewsFeed()
  {
    $this->_getNewsFeedModel()->delete(
      $this->getContentType(),
      $this->getContestEntryId()
    );
  }

  protected function _updateEntryCount()
  {
    $this->_getContestModel()->updateEntryCount($this->get('photo_contest_id'));
  }

  /**
   * @return FotoContest_Model_ContestEntry
   */
  protected function _getModel()
  {
    return $this->getModelFromCache('FotoContest_Model_ContestEntry');
  }

  /**
   * @return FotoContest_Model_Contest
   */
  protected function _getContestModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Contest');
  }

  /**
   * @return FotoContest_Model_Attachment
   */
  protected function _getAttachmentModel()
  {
    return $this->getModelFromCache('FotoContest_Model_Attachment');
  }

}
