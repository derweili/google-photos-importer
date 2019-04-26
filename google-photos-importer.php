<?php
/**
 * Plugin Name:     Google Photos Importer
 * Description:     Import Media from Google Photos
 * Author:          derweili
 * Author URI:      https://www.derweili.de
 * Text Domain:     google-photos-import
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Google_Photos_Importer
 */


/**
 *
 */
class Google_Photos_Importer_Plugin
{

  public static $plugin_dir;
  public static $plugin_url;

  function __construct()
  {
    Google_Photos_Importer_Plugin::$plugin_dir = __DIR__;
  }

  function run(){
    $this->load_dependencies();
    $this->register_actions();
  }

  private function register_actions(){
    $this->register_settings_page();
  }


  private function load_dependencies(){
    include_once Google_Photos_Importer_Plugin::$plugin_dir . '/vendor/autoload.php';
    include_once Google_Photos_Importer_Plugin::$plugin_dir . '/inc/google-photos-authenticator.php';
    include_once Google_Photos_Importer_Plugin::$plugin_dir . '/inc/google-photos-connector.php';
    include_once Google_Photos_Importer_Plugin::$plugin_dir . '/inc/google-photos-importer.php';
    include_once Google_Photos_Importer_Plugin::$plugin_dir . '/admin/google-photos-settings-page.php';
    include_once Google_Photos_Importer_Plugin::$plugin_dir . '/admin/google-photos-importer-page.php';
  }

  function register_settings_page(){

    $settings_page = new Google_Photos_Settings_Page();
    add_action('admin_menu', array( $settings_page, 'add_menu_page') );
    add_action('admin_init', array( $settings_page, 'settings_init') );

    $importer_page = new Google_Photos_Importer_Page();
    add_action('admin_menu', array( $importer_page, 'add_menu_page') );
    add_action('admin_head', array( $importer_page, 'styles') );
    add_action('admin_footer', array( $importer_page, 'custom_admin_script') );

    $importer = new Google_Photos_Importer();
    add_action('init', array( $importer, 'register_ajax_importer') );
  }


}

$Google_Photos_Importer = new Google_Photos_Importer_Plugin();
$Google_Photos_Importer->run();
