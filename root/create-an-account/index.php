<?php
  if (empty($_POST["encoded_data"]) == false):
    header("Content-type: application/json");
    require("/var/www/ghostproxies.com/includes/requests.php");

    if (
      file_exists("/var/www/ghostproxies.com/database/limits/86400/" . $ip_address_file_path) == true &&
      filesize("/var/www/ghostproxies.com/database/limits/86400/" . $ip_address_file_path) >= 3
    ) {
      echo "{\"messages\": {\"global\": \"Limit of 3 account creations per IP address per day is exceeded, <a href='/contact/'>send a message</a> to request a limit increase.\"}}";
      exit;
    }

    if (
      file_exists("/var/www/ghostproxies.com/database/limits/86400/create-an-account") == true &&
      filesize("/var/www/ghostproxies.com/database/limits/86400/create-an-account") >= 1000
    ) {
      echo "{\"messages\": {\"global\": \"There's a large amount of accounts being created at the moment, <a href='/contact/'>send a message</a> to request a private account creation link.\"}}";
      exit;
    }

    $decoded_data = json_decode($_POST["encoded_data"], true);

    if (empty($decoded_data) == false) {
      $decoded_data["messages"] = array();

      if (
        empty($decoded_data["session_id"]) == false &&
        strpos($decoded_data["session_id"], "/") === false
      ) {
        if (empty($decoded_data["input"]["username"]) == false) {
          if (
            isset($decoded_data["input"]["username"][4]) == true &&
            isset($decoded_data["input"]["username"][100]) == false
          ) {
            if (empty($decoded_data["input"]["password"]) == false) {
              if (
                isset($decoded_data["input"]["password"][9]) == true &&
                isset($decoded_data["input"]["password"][100]) == false
              ) {
                if (empty($decoded_data["input"]["password_confirmation"]) == false) {
                  if ($decoded_data["input"]["password"] == $decoded_data["input"]["password_confirmation"]) {
                    if (empty($decoded_data["input"]["agreement"]) == false) {
                      $password_hash_salt = file_get_contents("/var/www/ghostproxies.com/password-hash-salt");

                      if ($password_hash_salt != false) {
                        $user_id = hash("sha256", $decoded_data["input"]["username"] . $password_hash_salt);
                        $password_hash = hash("sha256", $decoded_data["input"]["password"] . $password_hash_salt);

                        if (is_dir("/var/www/ghostproxies.com/database/users/" . $user_id . "/") == false) {
                    			$user_directory_path = "/var/www/ghostproxies.com/database/users/" . $user_id . "/";
                          mkdir($user_directory_path);
                          chmod($user_directory_path, 0777);

                          if (is_dir($user_directory_path) == true) {
                            if (
                              file_put_contents($user_directory_path . "credits-count", "0") !== false &&
                              file_put_contents($user_directory_path . "id", $user_id) != false &&
                              file_put_contents($user_directory_path . "name", $decoded_data["input"]["username"]) != false &&
                              file_put_contents($user_directory_path . "password-hash", $password_hash) != false &&
                              file_put_contents($user_directory_path . "session-ids", "") !== false &&
                              touch("/var/www/ghostproxies.com/database/users/new/" . $user_id) != false
                            ) {
                              $session_id_file_path = "/var/www/ghostproxies.com/database/sessions/" . $decoded_data["session_id"];

                              if (file_exists($session_id_file_path) == true) {
                                unlink($session_id_file_path);
                              }

                              $temporary_session_id = hash("sha256", $user_id . time());
                              $temporary_session_id_file_path = "/var/www/ghostproxies.com/database/sessions/" . $temporary_session_id;

                              if (is_link($temporary_session_id_file_path) == true) {
                                unlink($temporary_session_id_file_path);
                              }

                              if (
                                link($user_directory_path . "id", $session_id_file_path) == true &&
                                symlink($session_id_file_path, $temporary_session_id_file_path) == true
                              ) {
                                $decoded_data["redirect"] = "/create-an-account/?session_id=" . $temporary_session_id;

                                if (is_dir("/var/www/ghostproxies.com/database/limits/86400/" . $ip_address_directory_path) == false) {
                                  mkdir("/var/www/ghostproxies.com/database/limits/86400/" . $ip_address_directory_path, 0777, true);
                                }

                                file_put_contents("/var/www/ghostproxies.com/database/limits/86400/" . $ip_address_file_path, "1", FILE_APPEND);
                                file_put_contents("/var/www/ghostproxies.com/database/limits/86400/create-an-account", "1", FILE_APPEND);
                              } else {
                                $decoded_data["messages"]["global"] = "There was an error processing the request.";
                                rmdir($user_directory_path);

                                if (file_exists($session_id_file_path) == true) {
                                  unlink($session_id_file_path);
                                }

                                if (is_link($temporary_session_id_file_path) == true) {
                                  unlink($temporary_session_id_file_path);
                                }
                              }
                            } else {
                              $decoded_data["messages"]["global"] = "There was an error processing the request.";
                              rmdir($user_directory_path);
                            }
                          } else {
                            $decoded_data["messages"]["global"] = "There was an error processing the request.";
                          }
                        } else {
                          $decoded_data["messages"]["global"] = "The username is already in use.";
                        }
                      } else {
                        $decoded_data["messages"]["global"] = "There was an error processing the request.";
                      }
                    } else {
                      $decoded_data["messages"]["agreement"] = "The agreement is required.";
                    }
                  } else {
                    $decoded_data["messages"]["password_confirmation"] = "The password confirmation must match the password.";
                  }
                } else {
                  $decoded_data["messages"]["password_confirmation"] = "The password confirmation is required.";
                }
              } else {
                $decoded_data["messages"]["password"] = "The password must be greater than or equal to 10 characters and less than or equal to 100 characters.";
              }
	        	} else {
			        $decoded_data["messages"]["password"] = "The password is required.";
		        }
          } else {
            $decoded_data["messages"]["username"] = "The username must be greater than or equal to 5 characters and less than or equal to 100 characters.";
          }
        } else {
          $decoded_data["messages"]["username"] = "The username is required.";
        }
      } else {
        $decoded_data["messages"]["global"] = "The user session is invalid.";
      }
    } else {
      $decoded_data = array(
        "messages" => array(
          "global" => "There was an error processing the request."
        )
      );
    }

    unset($decoded_data["input"]);
    $encoded_data = json_encode($decoded_data);

    if ($encoded_data == false) {
      $encoded_data = "{\"messages\": {\"global\": \"There was an error processing the request.\"}}";
    }

    echo $encoded_data;
    exit;
  else:
    $parameters = array(
      "title" => "Create an Account"
    );
    require("/var/www/ghostproxies.com/includes/header.php");

    if (empty($_SESSION["id"]) == false) {
      $data["redirect"] = "/account/";
    }

    if (
      empty($_GET["session_id"]) == false &&
      strpos($_GET["session_id"], "/") === false &&
      is_link("/var/www/ghostproxies.com/database/sessions/" . $_GET["session_id"]) == true
    ) {
      $temporary_session_id_file_path = "/var/www/ghostproxies.com/database/sessions/" . $_GET["session_id"];
      $session_id_file_path = readlink($temporary_session_id_file_path);

      if (
        $session_id_file_path != false &&
        unlink($temporary_session_id_file_path) != false
      ) {
        $session_id = basename($session_id_file_path);
        $user_id = file_get_contents($session_id_file_path);

        if (
          $user_id != false &&
          unlink($session_id_file_path) != false
        ) {
          $user_directory_path = "/var/www/ghostproxies.com/database/users/" . $user_id . "/";

          if (
            file_exists($user_directory_path . "session-ids") == true &&
            file_put_contents($user_directory_path . "session-ids", $session_id . " ") != false &&
            symlink($user_directory_path, $session_id_file_path) == true
          ) {
            $_SESSION["id"] = $session_id;
            $_SESSION["user_id"] = $user_id;
            $data["redirect"] = "/account/";
          } else {
            $data["messages"]["global"] = "There was an error processing the request.";
          }
        } else {
          $data["messages"]["global"] = "There was an error processing the request.";
        }
      } else {
        $data["messages"]["global"] = "There was an error processing the request.";
      }
    }
?>
        <main class="form interior">
          <h1>Create an Account</h1>
<?php if (empty($data["messages"]["global"])): ?>
          <p class="hidden message" name="global"></p>
<?php else: ?>
          <p class="message" name="global"><?php echo $data["messages"]["global"]; ?></p>
<?php endif; ?>
          <div style="margin-top: 35px;">
            <p class="hidden message" name="username"></p>
            <label for="username">Username</label>
            <div class="text-input">
              <input autocomplete="username" autofocus id="username" name="username" type="text">
            </div>
          </div>
          <div>
            <p class="hidden message" name="password"></p>
            <label for="password">Password</label>
            <div class="text-input">
              <input autocomplete="new-password" id="password" name="password" type="password">
            </div>
          </div>
          <div>
            <p class="hidden message" name="password_confirmation"></p>
            <label for="password-confirmation">Password Confirmation</label>
            <div class="text-input">
              <input autocomplete="new-password" id="password-confirmation" name="password_confirmation" type="password">
            </div>
          </div>
          <div class="no-margin-bottom">
            <p class="hidden message" name="agreement"></p>
            <div class="checkbox-input">
              <p><span class="checkbox" name="agreement"></span> I agree to the <a href="/privacy-policy/" target="_blank">privacy policy</a> and <a href="/terms/" target="_blank">terms</a>.</p>
            </div>
          </div>
          <button>Create an Account</button>
        </main>
  <?php
  require("/var/www/ghostproxies.com/includes/javascript.php");
  require("/var/www/ghostproxies.com/includes/footer.php");
  endif;
?>
