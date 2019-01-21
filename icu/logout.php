<?php 
    session_start();
    if($_SESSION["auth"] == null){
        header("Location: index.php");
    }
    session_destroy();
    header("Location: index.php");
    exit;
?>



