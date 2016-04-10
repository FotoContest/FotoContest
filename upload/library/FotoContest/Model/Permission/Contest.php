<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Model_Permission_Contest extends Xenforo_Model
{
  public function canLikeContestEntries(array $contest, array $viewingUser)
  {
    $db     = $this->_getDb();
    $result = $db->fetchRow('
      SELECT
        count(*) < contest.max_votes_count can_vote
      FROM
        xf_liked_content
      JOIN
        xf_lfc_photo_contest_entry AS entry
          ON (entry.photo_contest_entry_id = content_id)
      JOIN
        xf_lfc_photo_contest AS contest
          ON (contest.photo_contest_id = entry.photo_contest_id)
        WHERE
          content_type = "lfc_entry"
            AND
          contest.photo_contest_id = ' . $db->quote($contest['photo_contest_id']) . '
            AND
          like_user_id = ' . $db->quote($viewingUser['user_id'])
    );

    return $result['can_vote'];
  }
}
