<?php

/**
 *
 */
class Google_Photos_Importer
{

  function register_ajax_importer(){
    add_action( 'wp_ajax_google_photos_file_import',  array($this, 'ajax_importer_callback') );
  }

  /**
   * Callback for media import Ajax request
   */
  function ajax_importer_callback() {

      // if user is allowed to import media
      if( ! current_user_can( Google_Photos_Importer_Page::$capability ) ){
        echo json_encode(array(
          'error' => 'permissions_denied'
        ));
        die();
      }

      $id = sanitize_text_field( $_POST["fileId"] );
      $image_id = $this->importMedia($id);

      $responseData = array(
        'image_id' => $image_id
      );
      echo json_encode($responseData);
      die();
  }



  function importMedia( $id ){

    $image = $this->get_media( $id );

    $image_url = $this->get_media_download_url($image);
    $image_name = $image->getFilename();
    $image_description = $image->getDescription();
    $upload_dir       = wp_upload_dir(); // Set upload folder
    $image_data       = file_get_contents($image_url); // Get image data
    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
    $filename         = basename( $unique_file_name ); // Create image file name


    // Check folder permission and define file location
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    // Create the image  file on the server
    file_put_contents( $file, $image_data );

    // Check image file type
    $wp_filetype = wp_check_filetype( $filename, null );

    // Set attachment data
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => $image_description,
        'post_status'    => 'inherit'
    );

    // Create the attachment
    $attach_id = wp_insert_attachment( $attachment, $file, 0 );

    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

    // Assign metadata to attachment
    wp_update_attachment_metadata( $attach_id, $attach_data );


    return $attach_id;


  }

  function get_media( $id ){

    $connector = new Google_Photos_Connector();
    $item = $connector->getMedia($id);

    return $item;

  }

  /**
   * helper method to get the origintal file url
   * filters url based on mimetype
   *
   * @see https://developers.google.com/photos/library/guides/access-media-items#base-urls
   */
  function get_media_download_url( $image ){
    // get media mimetype
    $mime_type = $image->getMimeType();

    // get url for images
    if(strpos($mime_type, 'image') !== false){
      return $image->getBaseUrl() . '=d';
    }

    // get url for videos
    if(strpos($mime_type, 'video') !== false){
      return $image->getBaseUrl() . '=dv';
    }

    // fallback
    return $image->getBaseUrl();

  }


}
