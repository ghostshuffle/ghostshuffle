<?php
  $paypal_transactions_directory_path = "/var/www/ghostproxies.com/database/paypal-transactions/";
  $paypal_transactions = array_values(array_diff(scandir($paypal_transactions_directory_path), array("..", ".")));
  $i = count($paypal_transactions);

  while ($i != 0) {
    $i--;
    $paypal_transaction_id = $paypal_transactions[$i];

    if (file_exists($paypal_transactions_directory_path . $paypal_transaction_id . "/data.json") == true) {
      $encoded_paypal_transaction_data = file_get_contents($paypal_transactions_directory_path . $paypal_transaction_id . "/data.json");

      if ($encoded_paypal_transaction_data != false) {
        $decoded_paypal_transaction_data = json_decode($encoded_paypal_transaction_data, true);

        if ($decoded_paypal_transaction_data != false) {
          if (
            $decoded_paypal_transaction_data["status"] == "refunded" ||
            $decoded_paypal_transaction_data["status"] == "reversed"
          ) {
            $decoded_paypal_transaction_data["description"] = $decoded_paypal_transaction_data["amount"] . " Refund for " . $decoded_paypal_transaction_data["description"];
          } else if ($decoded_paypal_transaction_data["type"] == "web_accept") {
            $decoded_paypal_transaction_data["description"] = "+" . $decoded_paypal_transaction_data["amount"] . " Payment for " . $decoded_paypal_transaction_data["description"];

            if (
              $decoded_paypal_transaction_data["status"] == "completed" ||
              $decoded_paypal_transaction_data["status"] == "processed"
            ) {
              $decoded_paypal_transaction_data["description"] .= " Completed";
            } else {
              $decoded_paypal_transaction_data["description"] .= " Pending";
            }
          }

          if (empty($decoded_paypal_transaction_data["description"]) == false) {
            $encoded_user_data_file_path = "/var/www/ghostproxies.com/database/users/" . $decoded_paypal_transaction_data["user_id"] . "/data.json";

            if (file_exists($encoded_user_data_file_path) == true) {
              $encoded_user_data = file_get_contents($encoded_user_data_file_path);

              if ($encoded_user_data != false) {
                $decoded_user_data = json_decode($encoded_user_data, true);

                if ($decoded_user_data != false) {
                  if ($decoded_paypal_transaction_data["description"][0] == "+") {
                    $decoded_user_data["credits_count"] = bcadd($decoded_user_data["credits_count"], ceil($decoded_paypal_transaction_data["amount"] / 0.0001));
                    touch("/var/www/ghostproxies.com/database/users/" . $decoded_paypal_transaction_data["user_id"] . "/has-credits");
                    touch("/var/www/ghostproxies.com/database/users/" . $decoded_paypal_transaction_data["user_id"] . "/has-transactions");

                    if (file_exists("/var/www/ghostproxies.com/database/users/new/" . $decoded_paypal_transaction_data["user_id"]) == true) {
                      unlink("/var/www/ghostproxies.com/database/users/new/" . $decoded_paypal_transaction_data["user_id"]);
                    }
                  } else {
                    $decoded_user_data["credits_count"] = bcsub($decoded_user_data["credits_count"], ceil($decoded_paypal_transaction_data["amount"] / 0.0001));

                    if ($decoded_user_data["credits_count"] <= 0) {
                      $decoded_user_data["credits_count"] = 0;

                      if (file_exists("/var/www/ghostproxies.com/database/users/" . $decoded_paypal_transaction_data["user_id"] . "/has-credits")) {
                        unlink("/var/www/ghostproxies.com/database/users/" . $decoded_paypal_transaction_data["user_id"] . "/has-credits");
                      }
                    }
                  }

                  $encoded_user_data = json_encode($decoded_user_data);

                  if ($encoded_user_data != false) {
                    file_put_contents($encoded_user_data_file_path, $encoded_user_data);
                  }
                }
              }
            }
          }

          unlink($paypal_transactions_directory_path . $paypal_transaction_id . "/data.json");
        }
      }
    }
  }
?>
