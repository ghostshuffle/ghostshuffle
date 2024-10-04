<?php
  if (empty($_POST["encoded_data"]) == false):
    header("Content-type: application/json");
    require("/var/www/ghostproxies.com/includes/requests.php");
    $decoded_data = json_decode($_POST["encoded_data"], true);

    if (empty($decoded_data) == false) {
      $decoded_data["messages"] = array();

      if (
        empty($decoded_data["session_id"]) == false &&
        strpos($decoded_data["session_id"], "/") === false
      ) {
        if (empty($decoded_data["input"]["old_password"]) == false) {
          if (empty($decoded_data["input"]["new_password"]) == false) {
            if (isset($decoded_data["input"]["new_password"][100]) == false) {
              if (empty($decoded_data["input"]["new_password_confirmation"]) == false) {
                if ($decoded_data["input"]["new_password"] == $decoded_data["input"]["new_password_confirmation"]) {
                  $user_directory_path = readlink("/var/www/ghostproxies.com/database/sessions/" . $decoded_data["session_id"]);

                  if (is_dir($user_directory_path) == true) {
                    $password_hash_salt = file_get_contents("/var/www/ghostproxies.com/password-hash-salt");

                    if ($password_hash_salt != false) {
                      $password_hash = file_get_contents($user_directory_path . "password-hash");

                      if ($password_hash != false) {
                        if ($password_hash == hash("sha256", $decoded_data["input"]["old_password"] . $password_hash_salt)) {
                          if (file_put_contents($user_directory_path . "password-hash", hash("sha256", $decoded_data["input"]["new_password"] . $password_hash_salt)) == true) {
                            $decoded_data["redirect"] = "/account/?password_changed";
                          } else {
                            $decoded_data["messages"]["global"] = "There was an error processing the request.";
                          }
                        } else {
                          $decoded_data["messages"]["global"] = "Old password is invalid.";
                        }
                      } else {
  					        		$decoded_data["messages"]["global"] = "There was an error processing the request.";
                      }
                    } else {
                      $decoded_data["messages"]["global"] = "There was an error processing the request.";
                    }
                  } else {
                    $decoded_data["redirect"] = "/sign-out/?invalid_session";
                  }
                } else {
                  $decoded_data["messages"]["new_password_confirmation"] = "The new password confirmation must match the new password.";
                }
              } else {
                $decoded_data["messages"]["new_password_confirmation"] = "The new password confirmation is required.";
              }
            } else {
              $decoded_data["messages"]["new_password"] = "The new password must be less than or equal to 100 characters.";
            }
					} else {
						$decode_data["messages"]["new_password"] = "The new password is required.";
					}
				} else {
					$decoded_data["messages"]["global"] = "The old password is required.";
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
      "no_index" => true,
      "title" => "Change Password"
    );
    require_once("/var/www/ghostproxies.com/includes/header.php");

    if (
      empty($_SESSION["id"]) == true ||
      file_exists("/var/www/ghostproxies.com/database/sessions/" . $_SESSION["id"]) == false ||
      file_exists("/var/www/ghostproxies.com/database/users/" . $_SESSION["user_id"] . "/name") == false
    ) {
      session_destroy();
      $data["redirect"] = "/sign-in/?invalid_session";
    }
?>
                        <main class="form interior">
                          <h1>Change Password</h1>
<?php if (empty($data["messages"]["global"])): ?>
                          <p class="hidden message" name="global"></p>
<?php else: ?>
                          <p class="message" name="global"><?php echo $data["messages"]["global"]; ?></p>
<?php endif; ?>
                          <div style="margin-top: 35px;">
                            <p class="hidden message" name="old_password"></p>
                            <label for="old-password">Old Password</label>
                            <div class="text-input">
                              <input autocomplete="current-password" autofocus id="old-password" name="old_password" type="password">
                            </div>
                          </div>
                          <div>
                            <p class="hidden message" name="new_password"></p>
                            <label for="new-password">New Password</label>
                            <div class="text-input">
                              <input autocomplete="new-password" id="new-password" name="new_password" type="password">
                            </div>
                          </div>
                          <div>
                            <p class="hidden message" name="new_password_confirmation"></p>
                            <label for="new-password-confirmation">New Password Confirmation</label>
                            <div class="text-input">
                              <input autocomplete="new-password" id="new-password-confirmation" name="new_password_confirmation" type="password">
                            </div>
                          </div>
                          <button>Change Password</button>
                        </main>
  <?php
  require("/var/www/ghostproxies.com/includes/javascript.php");
  require("/var/www/ghostproxies.com/includes/footer.php");
  endif;
?>
