<?php
/**
 *
 */
class Google_Photos_Settings_Page
{

  public static $page_title = 'Google Photos Importer';
  public static $menu_title = 'Google Photos';
  public static $capability = 'manage_options';
  public static $menu_slug = 'google-photos-importer-settings';
  public static $options_group = 'google-photos-importer-settings';
  public static $settings_section = 'google-photos-importer-settings';

  public function add_menu_page(){
    add_options_page(
      Google_Photos_Settings_Page::$page_title,
      Google_Photos_Settings_Page::$menu_title,
      Google_Photos_Settings_Page::$capability,
      Google_Photos_Settings_Page::$menu_slug,
      array($this, 'settings_page')
    );
  }

  public function settings_page(){
    global $select_options, $radio_options;

    ?>
      <div class="wrap">
        <?php screen_icon(); ?>
        <h2>My Plugin Page Title</h2>
        <?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
          <!-- <div class="updated fade">
          	<p><strong>Settings Saved</strong></p>
          </div> -->
        <?php endif; ?>
        <form method="post" action="options.php">

          <?php

              //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
              settings_fields(Google_Photos_Settings_Page::$settings_section);

              // handle OAuth2 redirect callback and render auth buttons
              $this->authentication();

              // all the add_settings_field callbacks is displayed here
              do_settings_sections(Google_Photos_Settings_Page::$menu_slug);

              // Add the submit button to serialize the options
              submit_button();

          ?>
        </form>
      </div>
    <?php
  }


  public function settings_init(){

    //section name, display name, callback to print description of section, page to which section is attached.
    add_settings_section(
      Google_Photos_Settings_Page::$settings_section, // id
      "OAuth Credentials", // Title of the section.
      array( $this, "display_header_options_content" ), // Callback Function that fills the section with the desired content. The function should echo its output.
      Google_Photos_Settings_Page::$menu_slug // The menu page on which to display this section. Should match $menu_slug from Function Reference/add options page if you are adding a section to a 'Settings' page.
    );


    //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
    add_settings_field(
      "google_photos_client_id", //setting name
      "Client ID", // display name | Title of the field.
      array( $this, "display_client_id_form_element" ), // callback | Function that fills the field with the desired inputs as part of the larger form.
      Google_Photos_Settings_Page::$menu_slug, // page | The menu page on which to display this field. Should match $menu_slug from add_theme_page() or from do_settings_sections().
      Google_Photos_Settings_Page::$settings_section // settings section
    );

    add_settings_field(
      "google_photos_client_secret", //setting name
      "Client Secret", // display name | Title of the field.
      array( $this, "display_client_secret_form_element" ), // callback | Function that fills the field with the desired inputs as part of the larger form.
      Google_Photos_Settings_Page::$menu_slug, // page | The menu page on which to display this field. Should match $menu_slug from add_theme_page() or from do_settings_sections().
      Google_Photos_Settings_Page::$settings_section // settings section
    );

    //section name, form element name, callback for sanitization
    register_setting(Google_Photos_Settings_Page::$settings_section, "google_photos_client_id");
    register_setting(Google_Photos_Settings_Page::$settings_section, "google_photos_client_secret");

  }

  public function display_client_id_form_element(){
    //id and name of form element should be same as the setting name.
    ?>
        <input type="text" name="google_photos_client_id" id="google_photos_client_id" value="<?php echo get_option('google_photos_client_id'); ?>" />
    <?php
  }

  public function display_client_secret_form_element(){
    //id and name of form element should be same as the setting name.
    ?>
        <input type="text" name="google_photos_client_secret" id="google_photos_client_secret" value="<?php echo get_option('google_photos_client_secret'); ?>" />
    <?php
  }

  /*
   * Handle authentication
   *
   * Render Authentication Button
   * Handle Redirect Callback
   */
  public function authentication(){

    $authenticator = new Google_Photos_Authenticator();
    if( ! $authenticator->api_credentials_available() ) return;

    // check if buttons should be rendered or callback must be handled
    if( $this->is_redirect_callback() ){ // check if is redirect callback
      $this->handle_redirect_callback($authenticator); // handle redirect callback
    }else{
      $auth_uri = $authenticator->get_authorization_uri(); // get auth uri
      if($auth_uri)
      echo '<a href="' . $auth_uri . '" class="button button-primary">Authenticate with Google</a>'; // render button
    }

  }

  public function handle_redirect_callback($authenticator){
    $code = $_GET['code'];
    $credentials_object = $authenticator->handle_authentication_redirect($code);
    if($credentials_object)
      echo '<div class="updated fade">
        <p><strong>Successfully Authenticated with Google Photos</strong></p>
      </div>';


  }


  public function is_redirect_callback(){
    return isset($_GET['code']);
  }


}
