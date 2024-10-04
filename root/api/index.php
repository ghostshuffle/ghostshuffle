<?php
  error_reporting(0);
  header("Content-type: application/json", true, 200);

  if (
    empty($_SERVER["HTTP_TOKEN"]) == false &&
    ctype_xdigit($_SERVER["HTTP_TOKEN"]) == true &&
    strlen($_SERVER["HTTP_TOKEN"]) < 65 &&
    strlen($_SERVER["HTTP_TOKEN"]) > 56 &&
    file_exists("/var/www/ghostproxies.com/database/tokens/" . $_SERVER["HTTP_TOKEN"]) == true
  ) {
    $user_directory_path = readlink("/var/www/ghostproxies.com/database/tokens/" . $_SERVER["HTTP_TOKEN"]);
  } else if (
    empty($_POST["token"]) == false &&
    ctype_xdigit($_POST["token"]) == true &&
    strlen($_POST["token"]) < 65 &&
    strlen($_POST["token"]) > 56 &&
    file_exists("/var/www/ghostproxies.com/database/tokens/" . $_POST["token"]) == true
  ) {
    $user_directory_path = readlink("/var/www/ghostproxies.com/database/tokens/" . $_POST["token"]);
  } else if (
    empty($_GET["token"]) == false &&
    ctype_xdigit($_GET["token"]) == true &&
    strlen($_GET["token"]) < 65 &&
    strlen($_GET["token"]) > 56 &&
    file_exists("/var/www/ghostproxies.com/database/tokens/" . $_GET["token"]) == true
  ) {
    $user_directory_path = readlink("/var/www/ghostproxies.com/database/tokens/" . $_GET["token"]);
  } else {
    http_response_code(401);
    echo "{\"status_message\":\"Authentication with a token is required.\"}";
    exit;
  }

  $user_id = basename($user_directory_path);

  if ($user_id != false) {
    if (file_exists($user_directory_path . "has-credits") == true) {
      if (empty($_POST["input_ip_addresses"]) == false) {
        if (is_array($_POST["input_ip_addresses"]) == true) {
          require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");
          $input_ip_addresses_count = count($_POST["input_ip_addresses"]);
          $input_ip_addresses = array();
          $i = 0;
          $j = 0;

          while ($i != $input_ip_addresses_count) {
            if (validate_public_ip_address($_POST["input_ip_addresses"][$i]) == true) {
              if (in_array($_POST["input_ip_addresses"][$i], $input_ip_addresses) == false) {
                $input_ip_addresses[$j] = $_POST["input_ip_addresses"][$i];
                $j++;

                if ($j > 1024) {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                  exit;
                }
              }
            } else if (substr_count($_POST["input_ip_addresses"][$i], "/") == 1) {
              $input_ip_address = strstr($_POST["input_ip_addresses"][$i], "/", true);
              $input_ip_address_suffix = substr(strstr($_POST["input_ip_addresses"][$i], "/"), 1);

              if (validate_public_ip_address($input_ip_address) == true) {
                if (ctype_digit($input_ip_address_suffix) == true) {
                  if ($input_ip_address_suffix >= 24) {
                    if ($input_ip_address_suffix <= 32) {
                      $input_ip_address_range_count = 1 << (32 - $input_ip_address_suffix);
                      $input_ip_address_network = substr($input_ip_address, 0, strrpos($input_ip_address, "."));
                      $input_ip_address_host = substr($input_ip_address, strrpos($input_ip_address, ".") + 1);
                      $input_ip_address_host -= $input_ip_address_host & ($input_ip_address_range_count - 1);
                      $k = 0;

                      while ($k != $input_ip_address_range_count) {
                        $input_ip_address = $input_ip_address_network . "." . $input_ip_address_host;

                        if (in_array($input_ip_address, $input_ip_addresses) == false) {
                          $input_ip_addresses[$j] = $input_ip_address;
                          $j++;

                          if ($j > 1024) {
                            http_response_code(400);
                            echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                            exit;
                          }
                        }

                        $input_ip_address_host++;
                        $k++;
                      }
                    } else {
                      http_response_code(400);
                      echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be less than or equal to 32.\"}";
                      exit;
                    }
                  } else {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be greater than or equal to 24.\"}";
                    exit;
                  }
                } else {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be a number.\"}";
                  exit;
                }
              } else {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be a public IPv4 address.\"}";
                exit;
              }
            } else {
              http_response_code(400);
              echo "{\"status_message\":\"The input_ip_addresses value element " . $_POST["input_ip_addresses"][$i] . " must be either a public IPv4 address or an IPv4 CIDR block.\"}";
              exit;
            }

            $i++;
          }
        } else if (is_string($_POST["input_ip_addresses"]) == true) {
          require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");
          $input_ip_addresses = array();
          $input_ip_addresses_count = strlen($_POST["input_ip_addresses"]) - 1;
          $i = 0;

          while (
            ctype_alnum($_POST["input_ip_addresses"][$i]) == false &&
            $_POST["input_ip_addresses"][$i] != "." &&
            $_POST["input_ip_addresses"][$i] != "/"
          ) {
            $i++;
          }

          while (
            ctype_alnum($_POST["input_ip_addresses"][$input_ip_addresses_count]) == false &&
            $_POST["input_ip_addresses"][$input_ip_addresses_count] != "." &&
            $_POST["input_ip_addresses"][$input_ip_addresses_count] != "/"
          ) {
            $input_ip_addresses_count--;
          }

          $input_ip_addresses_count++;
          $j = 0;
          $k = $i;

          while ($i != $input_ip_addresses_count) {
            if (
              ctype_alnum($_POST["input_ip_addresses"][$i]) == false &&
              $_POST["input_ip_addresses"][$i] != "." &&
              $_POST["input_ip_addresses"][$i] != "/"
            ) {
              $input_ip_address = substr($_POST["input_ip_addresses"], $k, $i - $k);

              if (validate_public_ip_address($input_ip_address) == true) {
                if (in_array($input_ip_address, $input_ip_addresses) == false) {
                  $input_ip_addresses[$j] = $input_ip_address;
                  $j++;

                  if ($j > 1024) {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                    exit;
                  }
                }
              } else if (substr_count($input_ip_address, "/") == 1) {
                $input_ip_address_suffix = substr(strstr($input_ip_address, "/"), 1);
                $input_ip_address = strstr($input_ip_address, "/", true);

                if (validate_public_ip_address($input_ip_address) == true) {
                  if (ctype_digit($input_ip_address_suffix) == true) {
                    if ($input_ip_address_suffix >= 24) {
                      if ($input_ip_address_suffix <= 32) {
                        $input_ip_address_range_count = 1 << (32 - $input_ip_address_suffix);
                        $input_ip_address_network = substr($input_ip_address, 0, strrpos($input_ip_address, "."));
                        $input_ip_address_host = substr($input_ip_address, strrpos($input_ip_address, ".") + 1);
                        $input_ip_address_host -= $input_ip_address_host & ($input_ip_address_range_count - 1);
                        $l = 0;

                        while ($l != $input_ip_address_range_count) {
                          $input_ip_address = $input_ip_address_network . "." . $input_ip_address_host;

                          if (in_array($input_ip_address, $input_ip_addresses) == false) {
                            $input_ip_addresses[$j] = $input_ip_address;
                            $j++;

                            if ($j > 1024) {
                              http_response_code(400);
                              echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                              exit;
                            }
                          }

                          $input_ip_address_host++;
                          $l++;
                        }

                        while (
                          $i != $input_ip_addresses_count &&
                          ctype_alnum($_POST["input_ip_addresses"][$i]) == false
                        ) {
                          $i++;
                        }

                        $k = $i;
                      } else {
                        http_response_code(400);
                        echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be less than or equal to 32.\"}";
                        exit;
                      }
                    } else {
                      http_response_code(400);
                      echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be greater than or equal to 24.\"}";
                      exit;
                    }
                  } else {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be a number.\"}";
                    exit;
                  }
                } else {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be a public IPv4 address.\"}";
                  exit;
                }
              } else {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be either a public IPv4 address or an IPv4 CIDR block.\"}";
                exit;
              }

              while (
                $i != $input_ip_addresses_count &&
                ctype_alnum($_POST["input_ip_addresses"][$i]) == false
              ) {
                $i++;
              }

              $k = $i;
            }

            $i++;
          }

          $input_ip_address = substr($_POST["input_ip_addresses"], $k, $i - $k);

          if (validate_public_ip_address($input_ip_address) == true) {
            if (in_array($input_ip_address, $input_ip_addresses) == false) {
              $input_ip_addresses[$j] = $input_ip_address;
              $j++;

              if ($j > 1024) {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                exit;
              }
            }
          } else if (substr_count($input_ip_address, "/") == 1) {
            $input_ip_address_suffix = substr(strstr($input_ip_address, "/"), 1);
            $input_ip_address = strstr($input_ip_address, "/", true);

            if (validate_public_ip_address($input_ip_address) == true) {
              if (ctype_digit($input_ip_address_suffix) == true) {
                if ($input_ip_address_suffix >= 24) {
                  if ($input_ip_address_suffix <= 32) {
                    $input_ip_address_range_count = 1 << (32 - $input_ip_address_suffix);
                    $input_ip_address_network = substr($input_ip_address, 0, strrpos($input_ip_address, "."));
                    $input_ip_address_host = substr($input_ip_address, strrpos($input_ip_address, ".") + 1);
                    $input_ip_address_host -= $input_ip_address_host & ($input_ip_address_range_count - 1);
                    $l = 0;

                    while ($l != $input_ip_address_range_count) {
                      $input_ip_address = $input_ip_address_network . "." . $input_ip_address_host;

                      if (in_array($input_ip_address, $input_ip_addresses) == false) {
                        $input_ip_addresses[$j] = $input_ip_address;
                        $j++;

                        if ($j > 1024) {
                          http_response_code(400);
                          echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                          exit;
                        }
                      }

                      $input_ip_address_host++;
                      $l++;
                    }
                  } else {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be less than or equal to 32.\"}";
                    exit;
                  }
                } else {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be greater than or equal to 24.\"}";
                  exit;
                }
              } else {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be a number.\"}";
                exit;
              }
            } else {
              http_response_code(400);
              echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be a public IPv4 address.\"}";
              exit;
            }
          } else {
            http_response_code(400);
            echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be either a public IPv4 address or an IPv4 CIDR block.\"}";
            exit;
          }
        } else {
          http_response_code(400);
          echo "{\"status_message\":\"The input_ip_addresses value must be either an array or a string.\"}";
          exit;
        }
      } else if (empty($_GET["input_ip_addresses"]) == false) {
        if (is_array($_GET["input_ip_addresses"]) == true) {
          require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");
          $input_ip_addresses_count = count($_GET["input_ip_addresses"]);
          $input_ip_addresses = array();
          $i = 0;
          $j = 0;

          while ($i != $input_ip_addresses_count) {
            if (validate_public_ip_address($_GET["input_ip_addresses"][$i]) == true) {
              if (in_array($_GET["input_ip_addresses"][$i], $input_ip_addresses) == false) {
                $input_ip_addresses[$j] = $_GET["input_ip_addresses"][$i];
                $j++;

                if ($j > 1024) {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                  exit;
                }
              }
            } else if (substr_count($_GET["input_ip_addresses"][$i], "/") == 1) {
              $input_ip_address = strstr($_GET["input_ip_addresses"][$i], "/", true);
              $input_ip_address_suffix = substr(strstr($_GET["input_ip_addresses"][$i], "/"), 1);

              if (validate_public_ip_address($input_ip_address) == true) {
                if (ctype_digit($input_ip_address_suffix) == true) {
                  if ($input_ip_address_suffix >= 24) {
                    if ($input_ip_address_suffix <= 32) {
                      $input_ip_address_range_count = 1 << (32 - $input_ip_address_suffix);
                      $input_ip_address_network = substr($input_ip_address, 0, strrpos($input_ip_address, "."));
                      $input_ip_address_host = substr($input_ip_address, strrpos($input_ip_address, ".") + 1);
                      $input_ip_address_host -= $input_ip_address_host & ($input_ip_address_range_count - 1);
                      $k = 0;

                      while ($k != $input_ip_address_range_count) {
                        $input_ip_address = $input_ip_address_network . "." . $input_ip_address_host;

                        if (in_array($input_ip_address, $input_ip_addresses) == false) {
                          $input_ip_addresses[$j] = $input_ip_address;
                          $j++;

                          if ($j > 1024) {
                            http_response_code(400);
                            echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                            exit;
                          }
                        }

                        $input_ip_address_host++;
                        $k++;
                      }
                    } else {
                      http_response_code(400);
                      echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be less than or equal to 32.\"}";
                      exit;
                    }
                  } else {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be greater than or equal to 24.\"}";
                    exit;
                  }
                } else {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be a number.\"}";
                  exit;
                }
              } else {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be a public IPv4 address.\"}";
                exit;
              }
            } else {
              http_response_code(400);
              echo "{\"status_message\":\"The input_ip_addresses value element " . $_GET["input_ip_addresses"][$i] . " must be either a public IPv4 address or an IPv4 CIDR block.\"}";
              exit;
            }

            $i++;
          }
        } else if (is_string($_GET["input_ip_addresses"]) == true) {
          require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");
          $input_ip_addresses = array();
          $input_ip_addresses_count = strlen($_GET["input_ip_addresses"]) - 1;
          $i = 0;

          while (
            ctype_alnum($_GET["input_ip_addresses"][$i]) == false &&
            $_GET["input_ip_addresses"][$i] != "." &&
            $_GET["input_ip_addresses"][$i] != "/"
          ) {
            $i++;
          }

          while (
            ctype_alnum($_GET["input_ip_addresses"][$input_ip_addresses_count]) == false &&
            $_GET["input_ip_addresses"][$input_ip_addresses_count] != "." &&
            $_GET["input_ip_addresses"][$input_ip_addresses_count] != "/"
          ) {
            $input_ip_addresses_count--;
          }

          $input_ip_addresses_count++;
          $j = 0;
          $k = $i;

          while ($i != $input_ip_addresses_count) {
            if (
              ctype_alnum($_GET["input_ip_addresses"][$i]) == false &&
              $_GET["input_ip_addresses"][$i] != "." &&
              $_GET["input_ip_addresses"][$i] != "/"
            ) {
              $input_ip_address = substr($_GET["input_ip_addresses"], $k, $i - $k);

              if (validate_public_ip_address($input_ip_address) == true) {
                if (in_array($input_ip_address, $input_ip_addresses) == false) {
                  $input_ip_addresses[$j] = $input_ip_address;
                  $j++;

                  if ($j > 1024) {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                    exit;
                  }
                }
              } else if (substr_count($input_ip_address, "/") == 1) {
                $input_ip_address_suffix = substr(strstr($input_ip_address, "/"), 1);
                $input_ip_address = strstr($input_ip_address, "/", true);

                if (validate_public_ip_address($input_ip_address) == true) {
                  if (ctype_digit($input_ip_address_suffix) == true) {
                    if ($input_ip_address_suffix >= 24) {
                      if ($input_ip_address_suffix <= 32) {
                        $input_ip_address_range_count = 1 << (32 - $input_ip_address_suffix);
                        $input_ip_address_network = substr($input_ip_address, 0, strrpos($input_ip_address, "."));
                        $input_ip_address_host = substr($input_ip_address, strrpos($input_ip_address, ".") + 1);
                        $input_ip_address_host -= $input_ip_address_host & ($input_ip_address_range_count - 1);
                        $l = 0;

                        while ($l != $input_ip_address_range_count) {
                          $input_ip_address = $input_ip_address_network . "." . $input_ip_address_host;

                          if (in_array($input_ip_address, $input_ip_addresses) == false) {
                            $input_ip_addresses[$j] = $input_ip_address;
                            $j++;

                            if ($j > 1024) {
                              http_response_code(400);
                              echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                              exit;
                            }
                          }

                          $input_ip_address_host++;
                          $l++;
                        }

                        while (
                          $i != $input_ip_addresses_count &&
                          ctype_alnum($_GET["input_ip_addresses"][$i]) == false
                        ) {
                          $i++;
                        }

                        $k = $i;
                      } else {
                        http_response_code(400);
                        echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be less than or equal to 32.\"}";
                        exit;
                      }
                    } else {
                      http_response_code(400);
                      echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be greater than or equal to 24.\"}";
                      exit;
                    }
                  } else {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be a number.\"}";
                    exit;
                  }
                } else {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be a public IPv4 address.\"}";
                  exit;
                }
              } else {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be either a public IPv4 address or an IPv4 CIDR block.\"}";
                exit;
              }

              while (
                $i != $input_ip_addresses_count &&
                ctype_alnum($_GET["input_ip_addresses"][$i]) == false
              ) {
                $i++;
              }

              $k = $i;
            }

            $i++;
          }

          $input_ip_address = substr($_GET["input_ip_addresses"], $k, $i - $k);

          if (validate_public_ip_address($input_ip_address) == true) {
            if (in_array($input_ip_address, $input_ip_addresses) == false) {
              $input_ip_addresses[$j] = $input_ip_address;
              $j++;

              if ($j > 1024) {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                exit;
              }
            }
          } else if (substr_count($input_ip_address, "/") == 1) {
            $input_ip_address_suffix = substr(strstr($input_ip_address, "/"), 1);
            $input_ip_address = strstr($input_ip_address, "/", true);

            if (validate_public_ip_address($input_ip_address) == true) {
              if (ctype_digit($input_ip_address_suffix) == true) {
                if ($input_ip_address_suffix >= 24) {
                  if ($input_ip_address_suffix <= 32) {
                    $input_ip_address_range_count = 1 << (32 - $input_ip_address_suffix);
                    $input_ip_address_network = substr($input_ip_address, 0, strrpos($input_ip_address, "."));
                    $input_ip_address_host = substr($input_ip_address, strrpos($input_ip_address, ".") + 1);
                    $input_ip_address_host -= $input_ip_address_host & ($input_ip_address_range_count - 1);
                    $l = 0;

                    while ($l != $input_ip_address_range_count) {
                      $input_ip_address = $input_ip_address_network . "." . $input_ip_address_host;

                      if (in_array($input_ip_address, $input_ip_addresses) == false) {
                        $input_ip_addresses[$j] = $input_ip_address;
                        $j++;

                        if ($j > 1024) {
                          http_response_code(400);
                          echo "{\"status_message\":\"The input_ip_addresses value count of elements must be less than or equal to 1024.\"}";
                          exit;
                        }
                      }

                      $input_ip_address_host++;
                      $l++;
                    }
                  } else {
                    http_response_code(400);
                    echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be less than or equal to 32.\"}";
                    exit;
                  }
                } else {
                  http_response_code(400);
                  echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be greater than or equal to 24.\"}";
                  exit;
                }
              } else {
                http_response_code(400);
                echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " suffix " . $input_ip_address_suffix . " must be a number.\"}";
                exit;
              }
            } else {
              http_response_code(400);
              echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be a public IPv4 address.\"}";
              exit;
            }
          } else {
            http_response_code(400);
            echo "{\"status_message\":\"The input_ip_addresses value element " . $input_ip_address . " must be either a public IPv4 address or an IPv4 CIDR block.\"}";
            exit;
          }
        } else {
          http_response_code(400);
          echo "{\"status_message\":\"The input_ip_addresses value must be either an array or a string.\"}";
          exit;
        }
      } else {
        require("/var/www/ghostproxies.com/includes/public-ip-address-validation.php");

        if (validate_public_ip_address($_SERVER["REMOTE_ADDR"]) == true) {
          $input_ip_addresses_count = 1;
          $input_ip_addresses = new SplFixedArray(1);
          $input_ip_addresses[0] = $_SERVER["REMOTE_ADDR"];
        } else {
          http_response_code(400);
          echo "{\"status_message\":\"When the input_ip_addresses value is omitted, the client IP address must be an IPv4 address.\"}";
          exit;
        }
      }
    } else {
      http_response_code(402);
      echo "{\"status_message\":\"Not enough credits.\"}";
      exit;
    }
  } else {
    http_response_code(500);
    echo "{\"status_message\":\"Server error.\"}";
    exit;
  }

  echo "{\"output_ip_addresses\":[";
  $input_ip_addresses_count = count($input_ip_addresses);
  $output_ip_addresses_delimiter = "";
  $output_ip_addresses_first_confirmed_proxy_timestamp = time();
  $output_ip_addresses_last_confirmed_proxy_timestamp = 0;
  $i = 0;

  while ($i != $input_ip_addresses_count) {
    $output_ip_address_proxy_history_directory_path = "/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . str_replace(".", "/", $input_ip_addresses[$i]) . "/";

    if (
      is_dir($output_ip_address_proxy_history_directory_path) == true &&
      file_exists($output_ip_address_proxy_history_directory_path . "f") == true
    ) {
     $output_ip_address_first_confirmed_proxy = file_get_contents($output_ip_address_proxy_history_directory_path . "f");
     $output_ip_address_last_confirmed_proxy = file_get_contents($output_ip_address_proxy_history_directory_path . "l");

      if (
        $output_ip_address_first_confirmed_proxy !== false &&
        $output_ip_address_last_confirmed_proxy !== false &&
        substr_count($output_ip_address_first_confirmed_proxy, "-") == 2 &&
        substr_count($output_ip_address_last_confirmed_proxy, "-") == 2
      ) {
        echo $output_ip_addresses_delimiter;
        $output_ip_addresses_delimiter = ",";
        $output_ip_address_confirmed_proxy_timestamp = strstr($output_ip_address_first_confirmed_proxy, "-", true);

        if ($output_ip_address_confirmed_proxy_timestamp < $output_ip_addresses_first_confirmed_proxy_timestamp) {
          $output_ip_addresses_first_confirmed_proxy_timestamp = $output_ip_address_confirmed_proxy_timestamp;
        }

        $output_ip_address_proxy = substr(strstr($output_ip_address_first_confirmed_proxy, "-"), 1);

        if (strpos($output_ip_address_proxy, "h") > 0) {
          echo "{\"first_confirmed_proxy\":{\"port\":" . strstr($output_ip_address_proxy, "-", true) . ",\"protocol\":\"http\",\"timestamp\":\"" . $output_ip_address_confirmed_proxy_timestamp . "\"},\"label\":\"" . $input_ip_addresses[$i] . "\",";
        } else {
          echo "{\"first_confirmed_proxy\":{\"port\":" . strstr($output_ip_address_proxy, "-", true) . ",\"protocol\":\"socks\",\"timestamp\":\"" . $output_ip_address_confirmed_proxy_timestamp . "\"},\"label\":\"" . $input_ip_addresses[$i] . "\",";
        }

        if ($output_ip_address_first_confirmed_proxy == $output_ip_address_last_confirmed_proxy) {
          if ($output_ip_address_confirmed_proxy_timestamp > $output_ip_addresses_last_confirmed_proxy_timestamp) {
            $output_ip_addresses_last_confirmed_proxy_timestamp = $output_ip_address_confirmed_proxy_timestamp;
          }

          if (strpos($output_ip_address_proxy, "h") > 0) {
            echo "\"last_confirmed_proxy\":{\"port\":" . strstr($output_ip_address_proxy, "-", true) . ",\"protocol\":\"http\",\"timestamp\":\"" . $output_ip_address_confirmed_proxy_timestamp . "\"},";
          } else {
            echo "\"last_confirmed_proxy\":{\"port\":" . strstr($output_ip_address_proxy, "-", true) . ",\"protocol\":\"socks\",\"timestamp\":\"" . $output_ip_address_confirmed_proxy_timestamp . "\"},";
          }
        } else {
          $output_ip_address_confirmed_proxy_timestamp = strstr($output_ip_address_last_confirmed_proxy, "-", true);

          if ($output_ip_address_confirmed_proxy_timestamp > $output_ip_addresses_last_confirmed_proxy_timestamp) {
            $output_ip_addresses_last_confirmed_proxy_timestamp = $output_ip_address_confirmed_proxy_timestamp;
          }

          $output_ip_address_proxy = substr(strstr($output_ip_address_last_confirmed_proxy, "-"), 1);

          if (strpos($output_ip_address_proxy, "h") > 0) {
            echo "\"last_confirmed_proxy\":{\"port\":" . strstr($output_ip_address_proxy, "-", true) . ",\"protocol\":\"http\",\"timestamp\":\"" . $output_ip_address_confirmed_proxy_timestamp . "\"},";
          } else {
            echo "\"last_confirmed_proxy\":{\"port\":" . strstr($output_ip_address_proxy, "-", true) . ",\"protocol\":\"socks\",\"timestamp\":\"" . $output_ip_address_confirmed_proxy_timestamp . "\"},";
          }
        }

        if ($output_ip_address_confirmed_proxy_timestamp > (time() - 1209600)) {
          echo "\"risk_level\":2}";
        } else {
          echo "\"risk_level\":1}";
        }
      } else {
        http_response_code(500);
        echo "],\"status_message\":\"Server error.\"}";
        exit;
      }
    } else {
      echo $output_ip_addresses_delimiter . "{\"first_confirmed_proxy\":null,\"label\":\"" . $input_ip_addresses[$i] . "\",\"last_confirmed_proxy\":null,\"risk_level\":0}";
      $output_ip_addresses_delimiter = ",";
    }

    $i++;
  }

  echo "],\"output_ip_addresses_count\":" . $input_ip_addresses_count . ",\"status_message\":\"Success.\"}";
  usleep((hrtime(true) & 255) << 10);
  $credits_count = file_get_contents($user_directory_path . "credits-count");

  if ($credits_count !== false) {
    $credits_count = bcsub(trim($credits_count), $input_ip_addresses_count);

    if ($credits_count[0] == "-") {
      $credits_count = 0;
    }

    if (
      file_put_contents($user_directory_path . "credits-count", $credits_count) !== false &&
      $credits_count == 0 &&
      file_exists($user_directory_path . "has-credits") == true
    ) {
      unlink($user_directory_path . "has-credits");
    }
  }
?>
