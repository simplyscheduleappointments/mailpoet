<?php

namespace MailPoet\Config;

use MailPoet\WP\Functions as WPFunctions;

class Env {
  const NEWSLETTER_CONTENT_WIDTH = 1320;

  static $version;
  static $pluginName;
  static $pluginPath;
  static $baseUrl;
  static $file;
  static $path;
  static $viewsPath;
  static $assetsPath;
  static $assetsUrl;
  static $utilPath;
  static $tempPath;
  static $cachePath;
  static $tempUrl;
  static $languagesPath;
  static $libPath;
  static $pluginPrefix;
  static $dbPrefix;
  static $dbHost;
  static $dbIsIpv6;
  static $dbSocket;
  static $dbPort;
  static $dbName;
  static $dbUsername;
  static $dbPassword;
  static $dbCharset;
  static $dbCollation;
  static $dbCharsetCollate;
  static $dbTimezoneOffset;

  public static function init($file, $version, $dbHost, $dbUser, $dbPassword, $dbName) {
    self::$version = $version;
    self::$file = $file;
    self::$path = dirname(self::$file);
    self::$pluginName = 'mailpoet';
    self::$pluginPath = 'mailpoet/mailpoet.php';
    self::$baseUrl = WPFunctions::get()->pluginsUrl('', $file);
    self::$viewsPath = self::$path . '/views';
    self::$assetsPath = self::$path . '/assets';
    self::$assetsUrl = WPFunctions::get()->pluginsUrl('/assets', $file);
    self::$utilPath = self::$path . '/lib/Util';
    $wpUploadDir = WPFunctions::get()->wpUploadDir();
    self::$tempPath = $wpUploadDir['basedir'] . '/' . self::$pluginName;
    self::$cachePath = self::$tempPath . '/cache';
    self::$tempUrl = $wpUploadDir['baseurl'] . '/' . self::$pluginName;
    self::$languagesPath = self::$path . '/lang';
    self::$libPath = self::$path . '/lib';
    self::$pluginPrefix = 'mailpoet_';
    self::initDbParameters($dbHost, $dbUser, $dbPassword, $dbName);
  }

  /**
   * @see https://codex.wordpress.org/Editing_wp-config.php#Set_Database_Host for possible DB_HOSTS values
   */
  private static function initDbParameters($dbHost, $dbUser, $dbPassword, $dbName) {
    $parsedHost = WPFunctions::get()->parseDbHost($dbHost);
    if ($parsedHost === false) {
      throw new \InvalidArgumentException('Invalid db host configuration.');
    }
    list($host, $port, $socket, $isIpv6) = $parsedHost;

    global $wpdb;
    self::$dbPrefix = $wpdb->prefix . self::$pluginPrefix;
    self::$dbHost = $host;
    self::$dbIsIpv6 = $isIpv6;
    self::$dbPort = $port ?: 3306;
    self::$dbSocket = $socket;
    self::$dbName = $dbName;
    self::$dbUsername = $dbUser;
    self::$dbPassword = $dbPassword;
    self::$dbCharset = $wpdb->charset;
    self::$dbCollation = $wpdb->collate;
    self::$dbCharsetCollate = $wpdb->get_charset_collate();
    self::$dbTimezoneOffset = self::getDbTimezoneOffset();
  }

  public static function getDbTimezoneOffset($offset = false) {
    $offset = ($offset) ? $offset : WPFunctions::get()->getOption('gmt_offset');
    $mins = $offset * 60;
    $sgn = ($mins < 0 ? -1 : 1);
    $mins = abs($mins);
    $hrs = floor($mins / 60);
    $mins -= $hrs * 60;
    return sprintf('%+03d:%02d', $hrs * $sgn, $mins);
  }
}
