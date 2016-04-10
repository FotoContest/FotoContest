<?php
/**
 * @package FotoContest
 * @author Luqman
 */

class FotoContest_Cron_Winner extends Xenforo_Model
{
  public static function init()
  {
    XenForo_Model::create('FotoContest_Cron_Winner')->run();
  }

  public function run()
  {
    $options            = XenForo_Application::get('options');
    $userModel          = XenForo_Model::create('XenForo_Model_User');
    $contestEntryModel  = XenForo_Model::create('FotoContest_Model_ContestEntry');
    $threadStarter      = $userModel->getUserByName($options->lfcThreadUsername);
    $activeContests     = $this->_getActiveContests();

    if ($activeContests && (!$threadStarter || !$options->lfcThreadForumId))
    {
      XenForo_Error::debug('Photo Contest: No thread starter or target forum set');
      return;
    }

    foreach ($activeContests as $contest)
    {
      $winningEntries = $this->_getWinningContestEntries($contest);

      if ($winningEntries)
      {
        $tempHash = md5(uniqid('', true));
        $maxWinnersCount = $contest['max_winners_count'];

        if ($contest['allow_tied_winners'])
        {
          $tiedEntries = $this->_groupTiedEntries($winningEntries);
          $tiedEntries = array_slice($tiedEntries, 0, $maxWinnersCount, true);


          $this->_assignTiedEntryPositions($tiedEntries);
          $this->_insertTiedContestWinners($tiedEntries);
          $this->_cloneAttachmentsForTiedContestEntries(
            $tempHash,
            $tiedEntries,
            $threadStarter
          );

          list($title, $message) = $this->_getParsedTitleAndMessage(
            $contest, $tiedEntries
          );
        }
        else
        {
          $winningEntries = array_slice($winningEntries, 0, $maxWinnersCount, true);

          $this->_assignPositions($winningEntries);
          $this->_insertContestWinners($winningEntries);
          $this->_cloneAttachmentsForContestEntries(
            $tempHash,
            $winningEntries,
            $threadStarter
          );

          list($title, $message) = $this->_getParsedTitleAndMessage(
            $contest, $winningEntries
          );
        }

        $this->_createThread(
          $contest,
          $threadStarter,
          $title,
          $message,
          $tempHash
        );
      }
    }
  }

  protected function _createThread($contest, $threadStarter, $title, $message, $tempHash)
  {
    $writer = $this->_getThreadDataWriter($threadStarter);
    $writer->set('title', $title);

    $postWriter = $writer->getFirstMessageDw();
    $postWriter->set('message', $message);
    $postWriter->setExtraData(
      XenForo_DataWriter_DiscussionMessage::DATA_ATTACHMENT_HASH,
      $tempHash
    );

    $writer->save();

    $contestDw = XenForo_DataWriter::create(
      'FotoContest_DataWriter_Contest',
      XenForo_DataWriter::ERROR_SILENT
    );

    $contestDw->setExistingData($contest);
    $contestDw->set('contest_closed', true);
    $contestDw->set('thread_id', $writer->getNew('thread_id'));
    $contestDw->save();
  }

  protected function _groupTiedEntries($winningEntries)
  {
    $tiedEntries = array();
    foreach ($winningEntries as $contestEntry)
    {
      $groupField = $contestEntry['likes'];
      $tiedEntries[$groupField][] = $contestEntry;
    }

    return $tiedEntries;
  }

  protected function _insertContestWinners($winners)
  {
    foreach ($winners as $winner)
    {
      $this->_insertContestWinner($winner);
    }
  }

  protected function _insertTiedContestWinners($tiedWinners)
  {
    foreach ($tiedWinners as $group)
    {
      foreach ($group as $winner)
      {
        $this->_insertContestWinner($winner);
      }
    }
  }

  protected function _insertContestWinner($winner)
  {
    $dw = XenForo_DataWriter::create('FotoContest_DataWriter_ContestWinner');
    $dw->bulkSet(
      array(
        'photo_contest_id'       => $winner['photo_contest_id'],
        'photo_contest_entry_id' => $winner['photo_contest_entry_id'],
        'likes'                  => $winner['likes'],
        'user_id'                => $winner['user_id'],
        'username'               => $winner['username'],
        'position'               => $winner['position']
      )
    );
    $dw->save();
  }

  protected function _assignPositions(&$winners)
  {
    $position = 0;
    foreach ($winners as &$winner)
    {
      $position++;
      $winner['position'] = $position;
    }
  }

  protected function _assignTiedEntryPositions(&$groupWinners)
  {
    $position = 0;
    foreach ($groupWinners as &$group)
    {
      $position++;
      foreach ($group as &$winner)
      {
        $winner['position'] = $position;
      }
    }
  }

  protected function _getActiveContests()
  {
    return $contests = $this->fetchAllKeyed('
      SELECT
        *
      FROM
        xf_lfc_photo_contest
      WHERE
        voting_closes_on < ' . XenForo_Application::$time . '
          AND
        contest_state = "visible"
          AND
        contest_closed = 0
      ',
      'contest_id'
    );
  }

  protected function _getWinningContestEntries($contest)
  {
    return $this->fetchAllKeyed('
      SELECT
        *
      FROM
        xf_lfc_photo_contest_entry
      WHERE
        entry_state = "visible"
          AND
        photo_contest_id = ' . $contest['photo_contest_id'] . '
      ORDER BY
        likes DESC,
        created_at ASC
      LIMIT 999',
      'photo_contest_entry_id'
    );
  }

  protected function _getParsedTitleAndMessage($contest, $winningEntries)
  {
    $options         = XenForo_Application::get('options');
    $replace         = array();
    $titleTemplate   = $options->lfcThreadTitle;
    $messageTemplate = $options->lfcThreadMessage;

    $replace['title']        = $contest['title'];
    $replace['description']  = $contest['description'];
    $replace['contestUrl']   = XenForo_Link::buildPublicLink(
      'canonical:photo-contests',
      $contest
    );

    $replace['winnersHtml']  = $this->_getThreadWinnersListHtml(
      $contest,
      $winningEntries
    );

    $title    = $this->_replaceTokens($titleTemplate,   $replace);
    $message  = $this->_replaceTokens($messageTemplate, $replace);

    return array($title, $message);
  }

  protected function _getThreadWinnersListHtml($contest, $winningEntries)
  {
    $options  = XenForo_Application::get('options');
    $template = 'lfc_thread_winners_list';
    $params   = array('winningEntries' => $winningEntries);

    if ($contest['allow_tied_winners']){
      $template = 'lfc_thread_winners_list_tied';
    }

    $template = new XenForo_Template_Public($template);
    $template->setLanguageId($options->defaultLanguageId);
    $template->setStyleId($options->defaultStyleId);
    $template->setParams($params);

    return XenForo_Html_Renderer_BbCode::renderFromHtml($template->render());
  }

  protected function _cloneAttachmentsForTiedContestEntries($tempHash, $tiedEntries, $threadStarter)
  {
    $winningEntries = array();
    foreach ($tiedEntries as $key => $entries){
      foreach ($entries as $entry)
      {
        $winningEntries[$entry['photo_contest_entry_id']] = $entry;
      }
    }

    $this->_cloneAttachmentsForContestEntries($tempHash, $winningEntries, $threadStarter);
  }

  protected function _cloneAttachmentsForContestEntries($tempHash, $winningEntries, $threadStarter)
  {
    $attachmentModel  = XenForo_Model::create('XenForo_Model_Attachment');
    $attachments      = $attachmentModel->getAttachmentsByContentIds(
      'lfc_entry', array_keys($winningEntries)
    );

    foreach ($attachments as $attachment)
    {
      $contestEntry   = $winningEntries[$attachment['content_id']];
      $attachmentPath = $attachmentModel->getAttachmentDataFilePath($attachment);
      $tempFile       = tempnam(XenForo_Helper_File::getTempDir(), 'xf');

      copy($attachmentPath, $tempFile);

      $newFileName = $contestEntry['position'] . '-' .
        $contestEntry['username'] . '-' . $attachment['filename'];

      $upload           = new XenForo_Upload($newFileName, $tempFile);
      $attachmentDataId = $attachmentModel->insertUploadedAttachmentData(
        $upload, $threadStarter['user_id']
      );

      $attachmentModel->insertTemporaryAttachment($attachmentDataId, $tempHash);
    }
  }

  protected function _getThreadDataWriter($threadStarter)
  {
    $options = XenForo_Application::get('options');

    $writer = XenForo_DataWriter::create(
      'XenForo_DataWriter_Discussion_Thread',
      XenForo_DataWriter::ERROR_SILENT
    );

    $state = $options->lfcThreadOptions['state'] ? 'visible' : 'moderated';

    $writer->bulkSet(array(
      'node_id'          => $options->lfcThreadForumId,
      'discussion_state' => $state,
      'discussion_open'  => $options->lfcThreadOptions['open'],
      'sticky'           => $options->lfcThreadOptions['sticky'],
      'user_id'          => $threadStarter['user_id'],
      'username'         => $threadStarter['username']
    ));

    return $writer;
  }

  protected function _replaceTokens($template, array $entry)
  {
    if (preg_match_all('/\{([a-z0-9_]+)\}/i', $template, $matches))
    {
      foreach ($matches[1] AS $token)
      {
        if (isset($entry[$token]))
        {
          $template = str_replace('{' . $token . '}', $entry[$token], $template);
        }
      }
    }

    return $template;
  }
}
