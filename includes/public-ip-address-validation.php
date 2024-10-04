<?php
  function validate_public_ip_address($label) {
    $a = strstr($label, ".", true);
    $label = trim(strstr($label, "."), ".");
    $b = strstr($label, ".", true);
    $label = trim(strstr($label, "."), ".");
    $c = strstr($label, ".", true);
    $d = substr(strstr($label, "."), 1);
    $is_public = true;

    if (
      ctype_digit($a) == false ||
      ctype_digit($b) == false ||
      ctype_digit($c) == false ||
      ctype_digit($d) == false ||
      $d > 255 ||
      $d < 0 ||
      $a > 223 ||
      $a == 0 ||
      $a == 10 ||
      $a == 127 ||
      (
        $a == 100 &&
        $b > 63 &&
        $b < 128
      ) ||
      (
        $a == 172 &&
        $b > 15 &&
        $b < 32
      ) ||
      (
        $a == 198 &&
        (
          $b == 18 ||
          $b == 19 ||
          (
            $b == 51 &&
            $c == 100
          )
        )
      ) ||
      (
        $a == 192 &&
        (
          $b == 168 ||
          (
            $b == 0 &&
            (
              $c == 0 ||
              $c == 2
            )
          )
        )
      ) ||
      (
        $a == 169 &&
        $b == 254
      ) ||
      (
        $a == 203 &&
        $b == 0 &&
        $c == 113
      )
    ) {
      $is_public = false;
    }

    return $is_public;
  }
?>
