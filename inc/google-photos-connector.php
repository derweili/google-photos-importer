<?php

use Google\Photos\Library\V1\Album;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Library\V1\PhotosLibraryResourceFactory;
use Google\Rpc\Code;

/**
 *
 */
class Google_Photos_Connector
{

  private $photos_library_client = null;

  function __construct($credentials_object = null)
  {

    $this->get_photos_library_client($credentials_object);

  }

  function get_photos_library_client($credentials_object = null){
    if( ! $credentials_object ){
      $this->authenticator = new Google_Photos_Authenticator();
      $credentials_object = $this->authenticator->get_credentials_object_from_database();
    }

    $this->photos_library_client =  new PhotosLibraryClient(['credentials' => $credentials_object]);
  }


  function list_albums(){
    $response = $this->photos_library_client->listAlbums();
    return $response->iterateAllElements();
  }

  function list_album_media($album_id){
    $response = $this->photos_library_client->searchMediaItems(['albumId' => $album_id]);
    return $response->iterateAllElements();
  }

  function getMedia($media_id){
    $response = $this->photos_library_client->getMediaItem($media_id);
    return $response;
  }

}
