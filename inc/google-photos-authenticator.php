<?php
use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Auth\OAuth2;

/**
 *  Class for all authrentication stuff
 */
class Google_Photos_Authenticator
{

  public static $api_credentials = null;

  function __construct()
  {

  }

  public function get_api_credentials(){
    if( ! Google_Photos_Authenticator::$api_credentials ){
      Google_Photos_Authenticator::$api_credentials = array(
        'client_id' => get_option('google_photos_client_id'),
        'client_secret' => get_option('google_photos_client_secret'),
      );
    }

    return Google_Photos_Authenticator::$api_credentials;

  }

  public function api_credentials_available(){
    $credentials = $this->get_api_credentials();
    if(
      $credentials
      && isset( $credentials['client_id'] ) && !empty($credentials['client_id'])
      && isset( $credentials['client_secret'] ) && !empty($credentials['client_secret'])
      ) return true;
  }

  public function get_oauth2_context(){
    $credentials = $this->get_api_credentials();
    if( ! $this->api_credentials_available() ) return false;

    $oauth2 = new OAuth2([
        'clientId' => $credentials["client_id"],
        'clientSecret' => $credentials["client_secret"],
        'authorizationUri' => 'https://accounts.google.com/o/oauth2/v2/auth',
        // Where to return the user to if they accept your request to access their account.
        // You must authorize this URI in the Google API Console.
        'redirectUri' => $this->get_redirect_uri(),
        'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
        'scope' => $this->get_scopes(),
    ]);

    return $oauth2;

  }

  /**
   *
   */
  public function get_redirect_uri(){
    return menu_page_url( Google_Photos_Settings_Page::$menu_slug, false );
  }

  /**
   * Return required scope for OAuth2 authentication;
   */
  public function get_scopes(){
    return apply_filters( 'google-photos-import-authentication-scope', ['https://www.googleapis.com/auth/photoslibrary'] );
  }

  /**
   * Get Authorization URI
   */
  public function get_authorization_uri(){
    $oauth2 = $this->get_oauth2_context();
    // var_dump($oauth2);
    return $oauth2->buildFullAuthorizationUri(['access_type' => 'offline']);
  }

  /*
   * Handles the OAuth2 Redirect Callback
   * Creates Auth-Token from code
   */
  public function handle_authentication_redirect($code){
    $credentials = $this->get_api_credentials();
    $oauth2 = $this->get_oauth2_context();

    // With the code returned by the OAuth flow, we can retrieve the refresh token.
    $oauth2->setCode($_GET['code']);

    $authToken = $oauth2->fetchAuthToken();
    $refreshToken = $authToken['access_token'];

    $credentials_object = new UserRefreshCredentials(
        $this->get_scopes(),
        [
            'client_id' => $credentials["client_id"],
            'client_secret' => $credentials["client_secret"],
            'refresh_token' => $refreshToken
        ]
    );

    $this->save_credentials_object_to_database($credentials_object);

    // delte cached albumns
    delete_transient( 'googe-photos-importer-page-albumns-list' );

    return $credentials_object;

  }


  /**
   * Save UserRefreshCredentials object to database
   */
  public function save_credentials_object_to_database( $credentials_object ){

    update_option( 'google-photos-authentication-credentials-object', $credentials_object );

  }

  /**
   * Get UserRefreshCredentials object from database
   */
  public function get_credentials_object_from_database(){
    return get_option( 'google-photos-authentication-credentials-object', false );
  }

}
