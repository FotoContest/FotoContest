<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Cron_Counters extends Xenforo_Model
{
  public static function init()
  {
    XenForo_Model::create('FotoContest_Cron_Counters')->run();
  }

  public function run()
  {
    $this->_getDb()->query('
      UPDATE
        xf_user
      INNER JOIN
        (
          SELECT
            user_id, COUNT(*) AS contest_won
          FROM
            xf_lfc_photo_contest_winner lfcw
          GROUP BY lfcw.user_id
        ) won ON xf_user.user_id = won.user_id
      SET lfc_contests_won_count = won.contest_won
    ');

    $this->_getDb()->query('
      UPDATE
        xf_user
      INNER JOIN
        (
          SELECT
            user_id, COUNT(*) AS contest_entries
          FROM
            xf_lfc_photo_contest_entry lfce
          GROUP BY lfce.user_id
        ) entries ON xf_user.user_id = entries.user_id
      SET lfc_contest_entries_count = entries.contest_entries
    ');
  }
}
