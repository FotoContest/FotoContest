<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_ViewPublic_Helper_Like
{
  public static function getLikeViewParams($liked)
  {
    $output = array();

    if ($liked)
    {
      $output['term'] = new XenForo_Phrase('lfc_contest_entry_unlike');

      $output['cssClasses'] = array(
        'like' => '-',
        'unlike' => '+'
      );
    }
    else
    {
      $output['term'] = new XenForo_Phrase('lfc_contest_entry_like');

      $output['cssClasses'] = array(
        'like' => '+',
        'unlike' => '-'
      );
    }

    return $output;
  }

}
