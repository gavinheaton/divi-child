<?php

/*
Description: File uploader for TheAir.Works
Author: TheAir.Works
Version: 1.1
*/

// Create the shortcode
add_shortcode( 'taw_upload_shortcode', 'taw_upload_shortcode' );

function fileZipper ($folderZip){
  echo "<p>Working with {$folderZip}";

  // Iterate throught the directory
  $fileIterator = new DirectoryIterator($folderZip);
  # create a temp file & open it
  $tmp_file = $folderZip . 'downloader.zip';
  //echo "<p>Temp file is {$tmp_file}";
  # create new zip opbject
  $zip = new ZipArchive();
  if ( $zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
    exit("There was an error creating the zip.");
  }
  // adds files to the file list
  foreach($fileIterator as $file){
      # add each file
      if ($file->isFile()){
        $fileN = $file->getFilename();
        //echo "<p>The file is {$fileN}</p>";
        // file get contents requires full path to file
        $download_file = file_get_contents($folderZip.'/'.$file);
        #add it to the zip
        $zip->addFromString($file,$download_file);
    }
  }
  # close zip
  $zip->close();

  # send the file to the browser as a download
 header('Content-Description: File Transfer');
 header('Content-disposition: attachment; filename="TAW-Download.zip"');
 header('Content-type: application/zip');
 header("Content-length: " . filesize($tmp_file));
 header("Pragma: public");
 header('Cache-Control: must-revalidate');
 header("Expires: 0");

 // Clear headers
 ob_clean();
 ob_end_flush();

 readfile($tmp_file);
 unlink($tmp_file);
}
/* ---------------------- */
// Because we are working on the front end we use the short code
function taw_upload_shortcode(){

  // Get the upload directory
  $TAWupload_dir = wp_upload_dir();
  // Get the referring page
  $TAWreferralSlug = basename(wp_get_referer());
  // Set the slug of the referring page to cache
  wp_cache_set( 'TAWslugCache', $TAWreferralSlug );

  $siteUploads = get_site_url() . '/wp-content/uploads/uploader/' . $TAWreferralSlug.'/';
  wp_cache_set( 'TAWsiteUploads', $siteUploads );

  echo "<h3>Upload a File to " . wp_cache_get( 'TAWslugCache') . "</h3>";
  echo "<p>Site uploads: {$siteUploads}</p>";

  //global $TAWtheDir;
  $TAWfullPath = $TAWupload_dir['basedir'] . '/uploader/'. $TAWreferralSlug;
  echo "<p>TAW full path is: {$TAWfullPath}</p>";
  // Cache the full path
  set_transient ('TAWdir',$TAWfullPath);
  //wp_cache_set('TAWtheDir', $TAWfullPath);
  $TAWtheDir = $TAWfullPath;

  if ($TAWtheDir == $TAWupload_dir['basedir'].'/uploader/') {
  echo '<div class="TAWalert"><span class="TAWclosebtn" onclick="this.parentElement.style.display=\'none\';">&times;</span>
  No upload folder was found.</div>';
} else {
  if ( ! file_exists( $TAWtheDir ) ) {
    echo "<script>console.log('Making a new directory');</script>";
    mkdir($TAWtheDir,0777,true);
  }
  listFiles($TAWtheDir);
  }

    $TAWreferralSlug = wp_cache_get('TAWslugCache');
    set_transient("TAWslug", $TAWreferralSlug, 120);
    $TAWtemp = get_transient('TAWslug');
    echo "<p>TAW temp: {$TAWtemp}</p>";
    $TAWuploaderDir = get_theme_root_uri().'/divi-child/uploader/uploaded.php';
    echo '<form  method="post" action="'.$TAWuploaderDir.'" enctype="multipart/form-data">';
    ?>
        <input type='file' id='taw_upload_file' name='taw_upload_file'></input>
        <br />
        <!--?php submit_button('Upload') ?> -->
        <input type='submit'>
    </form>
    <br />
  <?php
}

function listFiles($TAWtheDir){
  // Get the referring page for the uploader
  // Extract only the last part of the url

  $TAWreferralSlug = wp_cache_get( 'TAWslugCache' );
  echo "<p>The slug is: {$TAWreferralSlug}";
  $TAWtheDir = wp_cache_get('TAWtheDir');
  $TAWsiteUploads = wp_cache_get( 'TAWsiteUploads');
  echo "<p>The path is: {$TAWtheDir}";
  echo "<table><tr><th>File name</th><th>Actions</th></tr>";

  $files = glob($TAWtheDir . "/*.*");
  //echo "<p>Setting up</p>";
  for ($i = 0; $i < count($files); $i++) {
    $image = $files[$i];
    $supported_file = array(
      'gif',
      'jpg',
      'jpeg',
      'png',
      'pdf',
      'ppt',
      'pptx',
      'doc',
      'docx'
    );

    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    if (in_array($ext, $supported_file)) {
      echo "<tr><td>".basename($image)."</td>";
      echo '<td><a target="_blank" href="' . $TAWsiteUploads . basename($image) . '">Download</a></td></tr>';
    } else {
      continue;
    }
  }
  echo "</table>";
}

?>
