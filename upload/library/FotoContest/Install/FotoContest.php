<?php
/**
 * @package FotoContest
 * @author Luqman
 */
class FotoContest_Install_FotoContest
{
  public static function install($existingAddOn, $addOnData)
  {
    $db = XenForo_Application::get('db');
    XenForo_Db::beginTransaction();

    // Version 1

    $db->query("
      CREATE TABLE IF NOT EXISTS `xf_lfc_photo_contest` (
        `photo_contest_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL DEFAULT '',
        `description` mediumtext NOT NULL,
        `user_id` int(10) NOT NULL,
        `username` varchar(50) NOT NULL,
        `contest_state` enum('visible','moderated','deleted') NOT NULL DEFAULT 'visible',
        `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
        `ip_id` int(10) unsigned NOT NULL DEFAULT '0',
        `moderate_entries` tinyint(3) unsigned NOT NULL DEFAULT '0',
        `hide_authors` tinyint(3) NOT NULL DEFAULT '0',
        `contest_closed` tinyint(3) NOT NULL DEFAULT '0',
        `entry_count` int(10) unsigned NOT NULL DEFAULT '0',
        `max_winners_count` int(10) NOT NULL DEFAULT '1',
        `max_entry_count` int(10) unsigned NOT NULL DEFAULT '1',
        `max_votes_count` int(10) unsigned NOT NULL DEFAULT '1',
        `posting_opens_on` int(10) unsigned NOT NULL,
        `posting_closes_on` int(10) unsigned NOT NULL,
        `voting_opens_on` int(10) unsigned NOT NULL,
        `voting_closes_on` int(10) unsigned NOT NULL,
        `created_at` int(10) unsigned NOT NULL,
        `updated_at` int(10) unsigned NOT NULL,
        PRIMARY KEY (`photo_contest_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $db->query("
      CREATE TABLE IF NOT EXISTS `xf_lfc_photo_contest_entry` (
        `photo_contest_entry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `user_id` int(10) unsigned NOT NULL,
        `username` varchar(50) NOT NULL DEFAULT '',
        `likes` int(10) unsigned NOT NULL DEFAULT '0',
        `like_users` blob NOT NULL,
        `ip_id` int(10) unsigned NOT NULL DEFAULT '0',
        `entry_state` enum('visible','moderated','deleted') NOT NULL DEFAULT 'visible',
        `photo_contest_id` int(10) unsigned NOT NULL,
        `created_at` int(10) unsigned NOT NULL,
        `updated_at` int(10) unsigned NOT NULL,
        PRIMARY KEY (`photo_contest_entry_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $db->query("
      INSERT IGNORE INTO xf_content_type
        (content_type, addon_id, fields)
      VALUES
        ('lfc', 'FotoContest', ''),
        ('lfc_entry', 'FotoContest', '');
    ");

    $db->query("
      INSERT IGNORE INTO xf_content_type_field
        (content_type, field_name, field_value)
      VALUES
        ('lfc', 'moderator_log_handler_class', 'FotoContest_ModeratorLogHandler_Contest'),
        ('lfc', 'attachment_handler_class', 'FotoContest_AttachmentHandler_Contest'),
        ('lfc_entry', 'alert_handler_class', 'FotoContest_AlertHandler_ContestEntry'),
        ('lfc_entry', 'attachment_handler_class', 'FotoContest_AttachmentHandler_ContestEntry'),
        ('lfc_entry', 'like_handler_class', 'FotoContest_LikeHandler_ContestEntry'),
        ('lfc_entry', 'moderator_log_handler_class', 'FotoContest_ModeratorLogHandler_ContestEntry'),
        ('lfc_entry', 'news_feed_handler_class', 'FotoContest_NewsFeedHandler_ContestEntry'),
        ('lfc_entry', 'report_handler_class', 'FotoContest_ReportHandler_ContestEntry');
    ");

    // Version 3

    $db->query("
      CREATE TABLE IF NOT EXISTS `xf_lfc_photo_contest_winner` (
        `photo_contest_winner_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `photo_contest_id` int(10) unsigned NOT NULL,
        `photo_contest_entry_id` int(10) unsigned NOT NULL,
        `likes` int(10) unsigned NOT NULL,
        `position` int(10) unsigned NOT NULL,
        `user_id` int(10) unsigned NOT NULL,
        `username` varchar(50) NOT NULL DEFAULT '',
        `alert_sent` tinyint(3) unsigned NOT NULL DEFAULT '0',
        `email_sent` tinyint(3) unsigned NOT NULL DEFAULT '0',
        `created_at` int(10) unsigned NOT NULL,
        `updated_at` int(10) unsigned NOT NULL,
        PRIMARY KEY (`photo_contest_winner_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // Version 4

    self::addFieldTo(
      'xf_lfc_photo_contest',
      'max_images_per_entry',
      "INT(10) UNSIGNED NOT NULL DEFAULT '1'"
    );

    self::addFieldTo(
      'xf_lfc_photo_contest_entry',
      'description',
      'MEDIUMTEXT NOT NULL'
    );

    self::addFieldTo(
      'xf_lfc_photo_contest_entry',
      'attach_count',
      "INT(10)  UNSIGNED NOT NULL DEFAULT '0'"
    );

    // Version 8

    self::addFieldTo(
      'xf_lfc_photo_contest',
      'entry_order',
      "VARCHAR(25) NOT NULL DEFAULT 'latest'"
    );

    // Version 10

    self::addFieldTo(
      'xf_lfc_photo_contest',
      'allow_tied_winners',
      "TINYINT(3) UNSIGNED NOT NULL DEFAULT '1'"
    );

    // Version 11

    self::addFieldTo(
      'xf_lfc_photo_contest_entry',
      'thread_id',
      "INT(10) UNSIGNED NOT NULL DEFAULT '0'"
    );

    // Version 13

    self::addFieldTo(
      'xf_lfc_photo_contest',
      'post_user_group_ids',
      'BLOB NOT NULL'
    );

    // Version 14

    self::addFieldTo(
      'xf_lfc_photo_contest',
      'hide_entries',
      "INT(3) UNSIGNED NOT NULL DEFAULT '1'"
    );

    // Version 15

    $db->query("
      INSERT IGNORE INTO
        xf_content_type_field
      SET
        content_type = 'lfc_entry',
        field_name = 'search_handler_class',
        field_value = 'FotoContest_Search_DataHandler_ContestEntry';
    ");

    // Version 16

    self::addFieldTo(
      'xf_user',
      'lfc_contests_won_count',
      "int(10) UNSIGNED NOT NULL DEFAULT '0'"
      );

    self::addFieldTo(
      'xf_user',
      'lfc_contest_entries_count',
      "int(10) UNSIGNED NOT NULL DEFAULT '0'"
    );

    // Version 17

    self::addFieldTo('xf_lfc_photo_contest', 'post_user_group_ids', 'BLOB NOT NULL');
    self::addFieldTo('xf_lfc_photo_contest', 'vote_user_group_ids', 'BLOB NOT NULL');

    // Version 18

    self::addFieldTo(
      'xf_lfc_photo_contest',
      'is_featured',
      "INT(3) UNSIGNED NOT NULL DEFAULT '0'"
    );

    XenForo_Db::commit();
  }

  public static function uninstall($addOnData)
  {
    $db = XenForo_Application::get('db');
    XenForo_Db::beginTransaction();

    $db->query("DROP TABLE xf_lfc_photo_contest;");
    $db->query("DROP TABLE xf_lfc_photo_contest_entry;");
    $db->query("DROP TABLE xf_lfc_photo_contest_winner;");
    $db->query("DELETE FROM xf_content_type WHERE addon_id = 'FotoContest';");
    $db->query("DELETE FROM xf_content_type_field WHERE content_type = 'lfc';");
    $db->query("DELETE FROM xf_content_type_field WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_news_feed WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_moderator_log WHERE content_type = 'lfc';");
    $db->query("DELETE FROM xf_moderator_log WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_bb_code_parse_cache WHERE content_type = 'lfc';");
    $db->query("DELETE FROM xf_deletion_log WHERE content_type = 'lfc';");
    $db->query("DELETE FROM xf_deletion_log WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_ip WHERE content_type = 'lfc';");
    $db->query("DELETE FROM xf_ip WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_liked_content WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_report WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_user_alert WHERE content_type = 'lfc_entry';");
    $db->query("DELETE FROM xf_data_registry WHERE data_key = 'lfcModerationCounts';");

    self::dropFieldFrom(
      'xf_user',
      'lfc_contest_entries_count'
    );

    self::dropFieldFrom(
      'xf_user',
      'lfc_contests_won_count'
    );

    XenForo_Db::commit();

    XenForo_Model::create('XenForo_Model_ContentType')->rebuildContentTypeCache();
  }

  public static function addFieldTo($table, $field, $type)
  {
    $db = XenForo_Application::get('db');
    $query = 'SHOW columns FROM `' . $table . '` WHERE Field = ?';

    if (!$db->fetchRow($query, $field))
    {
      return $db->query("ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $type);
    }

    return false;
  }

  public static function dropFieldFrom($table, $field)
  {
    $db = XenForo_Application::get('db');
    $query = 'SHOW columns FROM `' . $table . '` WHERE Field = ?';

    if ($db->fetchRow($query, $field))
    {
      return $db->query("ALTER TABLE `" . $table . "` DROP `" . $field . "` ");
    }

    return false;
  }
}
