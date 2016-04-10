<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_DataWriter_ContestWinner extends FotoContest_Shared_DataWriter
{

  protected function _getFields()
  {
    return array(
      'xf_lfc_photo_contest_winner' => array(
        'photo_contest_winner_id' => array(
          'type' => self::TYPE_UINT,
          'autoIncrement' => true
        ),
        'photo_contest_id' => array(
          'type' => self::TYPE_UINT,
          'required' => true
        ),
        'photo_contest_entry_id' => array(
          'type' => self::TYPE_UINT,
          'required' => true
        ),
        'likes' => array(
          'type' => self::TYPE_UINT,
          'required' => true
        ),
        'position' => array(
          'type' => self::TYPE_UINT,
          'required' => true
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
        'alert_sent' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 0
        ),
        'email_sent' => array(
          'type' => self::TYPE_BOOLEAN,
          'default' => 0
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
   * @return FotoContest_Model_ContestWinner
   */
  protected function _getModel()
  {
    return $this->getModelFromCache('FotoContest_Model_ContestWinner');
  }

}
