<?php
  if (
    empty($_GET["token"] == false) &&
    is_string($_GET["token"]) &&
    strlen($_GET["token"]) < 100
  ) {
    echo $_SERVER["REMOTE_ADDR"] . "-" . hash("sha1", $_GET["token"] . $_SERVER["REMOTE_ADDR"] . "!!6h057pr0x135!!");
  }
?>
