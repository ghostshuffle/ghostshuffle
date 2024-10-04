<?php
  if (empty($_POST) == false) {
    $encoded_parameters = file_get_contents('php://input');

    if (empty($encoded_parameters) == false) {
      $decoded_paypal_transaction_data = array();
      $decoded_parameters = explode("&", $encoded_parameters);
      $i = count($decoded_parameters);

      while ($i != 0) {
        $i--;
        list($decoded_parameters_key, $decoded_parameters_value) = explode("=", $decoded_parameters[$i]);

        if (empty($decoded_parameters_key) == false) {
          $decoded_paypal_transaction_data[$decoded_parameters_key] = urldecode(utf8_encode($decoded_parameters_value));
        }
      }

      $decoded_paypal_transaction_data = array(
        "amount" => $decoded_paypal_transaction_data["mc_gross"] . " " . $decoded_paypal_transaction_data["mc_currency"],
        "created_timestamp" => time(),
        "description" => $decoded_paypal_transaction_data["item_name"],
        "id" => $decoded_paypal_transaction_data["txn_id"],
        "status" => strtolower($decoded_paypal_transaction_data["payment_status"]),
        "type" => $decoded_paypal_transaction_data["txn_type"],
        "user_id" => $decoded_paypal_transaction_data["item_number"]
      );
      $encoded_paypal_transaction_data = json_encode($decoded_paypal_transaction_data);

      if ($encoded_paypal_transaction_data != false) {
        $paypal_transaction_directory_path = "/var/www/ghostproxies.com/database/paypal-transactions/" . $decoded_paypal_transaction_data["id"] . "/";

        if (is_dir($paypal_transaction_directory_path) == false) {
          if (
            mkdir($paypal_transaction_directory_path) == false ||
            chmod($paypal_transaction_directory_path, 0777) == false ||
            file_put_contents($paypal_transaction_directory_path . "data.json", $encoded_paypal_transaction_data) == false
          ) {
            rmdir($paypal_transaction_directory_path);
          }
        }
      }
    }
  }

  exit;
?>
