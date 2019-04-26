<?php
/**
 *
 */
class Google_Photos_Importer_Page
{
  public static $page_title = 'Google Photos';
  public static $menu_title = 'Google Photos';
  public static $capability = 'upload_files';
  public static $menu_slug = 'google-photos-importer-page';

  private $google_photos_connector = null;

  public function add_menu_page(){
    add_media_page(
      Google_Photos_Importer_Page::$page_title,
      Google_Photos_Importer_Page::$menu_title,
      Google_Photos_Importer_Page::$capability,
      Google_Photos_Importer_Page::$menu_slug,
      array($this, 'importer_page')
    );
  }

  public function importer_page(){
      $this->setup_api_connection();
    ?>

    <div class="wrap">
      <?php if( ! isset( $_GET[ "album_id" ] ) || empty( $_GET[ "album_id" ]) ): ?>

        <?php $this->show_album_list(); ?>

      <?php else: ?>

        <?php $this->show_album( $_GET[ "album_id" ] ); ?>

      <?php endif; ?>

    </div>

    <?php
  }

  public function setup_api_connection(){
    $this->authenticator = new Google_Photos_Authenticator();
    $this->check_credentials();
    $this->google_photos_connector = new Google_Photos_Connector( $this->credentials_object );
  }


  private function check_credentials(){

    if( ! $this->authenticator ){
      echo '<div class="error fade">
        <p><strong>The Google Photos Plugin is not configured</strong></p>
        <p>Please go to plugin settings and add your credentials </p>
      </div>';
      return false;
    }
    $this->credentials_object = $this->authenticator->get_credentials_object_from_database();
    if( ! $this->credentials_object ){
      echo '<div class="error fade">
        <p><strong>You did not authenticate with Google Photos yet</strong></p>
        <p>Please go to plugin settings and authenticate your Google Photos Account </p>
      </div>';
      return false;
    }

    return true;
  }


  function show_album_list(){
    $albumns = $this->google_photos_connector->list_albums();
    echo '<h1>Select Album</h1>';
    echo '<ul class="google-photos-importer-album-list">';
      foreach ($albumns as $album ) {
        echo '<li>';
          echo '<a href="' . menu_page_url( Google_Photos_Importer_Page::$menu_slug, false ) . '&album_id=' . $album->getId() . '">';
            echo '<img src="' . $album->getCoverPhotoBaseUrl('=w2048-h1024') . '" " width="200px"/>';
            echo '<span>' . $album->getTitle() . '</span>';
          echo '</a>';
        echo '</li>';

      }
    echo '</ul>';
  }


  function show_album($album_id){
    $media = $this->google_photos_connector->list_album_media($album_id);
    echo '<h1>Import Image</h1>';
    echo '<ul class="google-photos-importer-album-list images">';
      foreach ($media as $element) {
        echo '<li>';
          echo '<a
                  href="#"
                  class="google-photos-media-file"
                  data-id="' . $element->getId() . '"
                  data-uri="' . $element->getBaseUrl('=w200-h200') . '"
                  >';
            echo '<img src="' . $element->getBaseUrl() . '=w200-h140-c"" width="200px"/>';
            echo '<span>' . $element->getFilename() . '</span>';
          echo '</a>';
        echo '</li>';
      }
    echo '</ul>';
  }



  function styles() {
    ?><style type="text/css">
    @keyframes rotation {
        from {transform: rotate(50deg)}
        to {transform: rotate(-670deg)}
    }
    /* The animation code */
@keyframes example {
  from {background-color: red;}
  to {background-color: yellow;}
}
    .google-photos-importer-album-list {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      margin-left: 0;
      list-style: none;
    }
    .google-photos-importer-album-list li {
      border: 1px solid lightgray;

      margin: 10px;
      width: 200px;
    }
    .google-photos-importer-album-list li a {
      display: flex;
      flex-direction: column;
      text-align: center;
      position: relative;
      word-wrap: break-word;
      padding-bottom: 10px;
    }
    .google-photos-importer-album-list li a.loading::after {
      font-family: dashicons;
      content: "\f531";
      position: absolute;
      top: 50%;
      left: 50%;
      top: 61px;
      left: 84px;
      /* background-color: red; */
      transform: rotate(50deg);
      animation-name: rotation;
      animation-duration: 4s;
      animation-iteration-count: infinite;
      animation-timing-function: linear;
      font-size: 30px;
      color: white;

    }
    .google-photos-importer-album-list li a.imported img, .google-photos-importer-album-list li a.imported span{
      opacity: 0.5;
    }
    .google-photos-importer-album-list li a.imported::after{
      font-family: dashicons;
      content: "\f147";
      position: absolute;
      top: 50%;
      left: 50%;
      top: 61px;
      left: 64px;
      font-size: 70px;
      color: #13da13;
      text-shadow: 0 0 17px rgba(0,0,0,0.26);
    }
    .google-photos-importer-album-list img {
      margin-bottom: 10px;
    }
    </style><?php
  }

  function custom_admin_script(){

    ?>
    <script>
      var $mediaFiles = jQuery('.google-photos-media-file');
      console.log('media files', $mediaFiles);
      $mediaFiles.on('click', function(e){
        e.preventDefault();
        var $linkElement = jQuery(this)
        var fileUri = jQuery(this).attr('data-uri');
        var fileId = jQuery(this).attr('data-id');
        console.log('download-file', fileUri);

        if($linkElement.hasClass( "loading" )) return;
        if($linkElement.hasClass( "imported" )) return;

        $linkElement.addClass('loading');

        jQuery.ajax({
            type: 'POST',
            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
            data: {
                action: 'google_photos_file_import',
                fileUri: fileUri,
                fileId: fileId
            },
            success: function (data, textStatus, XMLHttpRequest) {
              var response = jQuery.parseJSON( data );
              console.log(response);
                // alert('File Imported. File id: ' + response.image_id);
                $linkElement.removeClass('loading');
                $linkElement.addClass('imported');

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
                $linkElement.removeClass('loading');

            }
        });

      });


    </script>


    <?php

  }


}
