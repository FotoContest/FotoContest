<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Listener_CriteriaUser
{
  public static function init($rule, array $data, array $user, &$returnValue)
  {
    switch ($rule)
    {
      case 'lfc_contests_won':
      if ($user['lfc_contests_won_count'] >= $data['contests_won'])
      {
        $returnValue = true;
      }
      break;

      case 'lfc_contest_entries':
      if ($user['lfc_contest_entries_count'] == $data['contest_entries'])
      {
        $returnValue = true;
      }
      break;
    }
  }
}
