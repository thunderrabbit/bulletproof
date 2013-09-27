<?php

    $conn = new PDO('mysql:host=localhost; dbname=seowrapper', 'root', ''); //don't mind me, i'm just an example

    require_once('src/seoWrapper.php');

	$SeoWrapper = new SeoWrapper();

    $result = ($SeoWrapper->hasErrors()) ? die('page not found') :  $SeoWrapper->currentPage($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']);

    if($SeoWrapper->currentPage($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF']) === 'dynamic'){
        $result = ($SeoWrapper->hasErrors()) ? die('page not found') :  $SeoWrapper->getContents($conn);
    }

	var_dump($result); // you can see the result on the page, or Crtl+U
?>





<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8" />
        <meta name="robots" content="index, follow" />
        <meta name="description" content="  <?php echo $result['title'] ; ?> " />
        <meta name="keywords" content="  <?php  echo $result['keywords']; ?>  " />
        <meta name="REVISIT-AFTER" content="15 DAYS" />

        <title>  <?php  echo $result['description']; ?>  </title>
    </head>

// only three rows are fetched in this example, but in your case, you can bombard your meta with more if you want.