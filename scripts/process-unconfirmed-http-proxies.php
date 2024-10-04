<?php
  error_reporting(0);
  usleep((hrtime(true) & 255) << 11);
  exec("cd /var/www/ghostproxies.com/database/unconfirmed-proxies/http/ && ls -r -t | head -1", $response);
  $proxies = array();
  $proxies_count = 0;

  if (empty($response) == false) {
    $proxy = $response[0];
    $proxy_ip_address = strstr($proxy, ":", true);

    if (file_put_contents("/var/www/ghostproxies.com/database/unconfirmed-proxies/http/" . $proxy, "1", FILE_APPEND) != false) {
      $proxy_connection_attempts_count = file_get_contents("/var/www/ghostproxies.com/database/unconfirmed-proxies/http/" . $proxy);

      if ($proxy_connection_attempts_count !== false) {
        $proxy_connection_attempts_count = strlen($proxy_connection_attempts_count);

        if ($proxy_connection_attempts_count < 4) {
          require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");
          $token = uniqid() . hrtime(true);
          $process_repetitions_count = 4;

          while ($process_repetitions_count != 0) {
            $copied_proxies_count = $proxies_count;
            usleep(1000);
            $response = array();
            exec("sudo curl --insecure --max-time 3 -s -x " . $proxy . " \"http://parsonsbots.com/?token=" . $token . "\" 2>&1", $response);

            if (empty($response[0]) == false) {
              $response_proxy_ip_address = strstr($response[0], "-", true);
              $response_hash = hash("sha1", $token . $response_proxy_ip_address . "!!6h057pr0x135!!");

              if ($response_proxy_ip_address == $proxy_ip_address) {
                if (
                  $copied_proxies_count == 0 &&
                  $response_hash == trim(substr(strstr($response[0], "-"), 1))
                ) {
                  $proxies[$proxies_count] = $proxy;
                  $proxies_count++;
                  $process_repetitions_count = 4;
                }
              } elseif (
                validate_public_ip_address($response_proxy_ip_address) == true &&
                in_array($response_proxy_ip_address . strstr($proxy, ":"), $proxies) == false &&
                $response_hash == trim(substr(strstr($response[0], "-"), 1))
              ) {
                if ($copied_proxies_count == 0) {
                  $proxies[$proxies_count] = $proxy;
                  $proxies[$proxies_count + 1] = $response_proxy_ip_address . strstr($proxy, ":");
                  $proxies_count += 2;
                } else {
                  $proxies[$proxies_count] = $response_proxy_ip_address . strstr($proxy, ":");
                  $proxies_count++;
                }

                $process_repetitions_count = 6;
              }

              if ($copied_proxies_count == $proxies_count) {
                $process_repetitions_count--;
              }
            } else {
              $process_repetitions_count--;
            }
          }
        } elseif (file_exists("/var/www/ghostproxies.com/database/unconfirmed-proxies/http/" . $proxy) == true) {
          unlink("/var/www/ghostproxies.com/database/unconfirmed-proxies/http/" . $proxy);
        }
      }
    }
  }

  if ($proxies_count != 0) {
    while ($proxies_count != 0) {
      $proxies_count--;
      $proxy_ip_address = strstr($proxies[$proxies_count], ":", true);
      $proxy_port = substr(strstr($proxies[$proxies_count], ":"), 1);
      $ip_address_proxy_history_directory_path = "/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . str_replace(".", "/", $proxy_ip_address) . "/";
      $ip_address_proxy_history = time() . "-" . $proxy_port . "-http";

      if (is_dir($ip_address_proxy_history_directory_path) == false) {
        mkdir($ip_address_proxy_history_directory_path, 0777, true);
        file_put_contents($ip_address_proxy_history_directory_path . "f", $ip_address_proxy_history);
      }

      file_put_contents($ip_address_proxy_history_directory_path . "l", $ip_address_proxy_history);
    }

    file_put_contents("/var/www/ghostproxies.com/database/ip-addresses-proxies-history/s", $proxy_ip_address . "-" . $ip_address_proxy_history);
  }
?>
