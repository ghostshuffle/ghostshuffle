<?php
  session_set_cookie_params(11111111, "/", "ghostproxies.com", true, true);
  session_start();
  $visit_id = hrtime(true);
  $ip_address_file_path = str_replace(".", "/", $_SERVER["REMOTE_ADDR"]);
  $ip_address_directory_path = substr($ip_address_file_path, 0, strrpos($ip_address_file_path, "/")) . "/";

  if (
    file_exists("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) == true &&
    filesize("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) >= 30
  ) {
    http_response_code(429);
    echo "Limit of 30 visits per IP address per minute is exceeded.";
    exit;
  }

  if (
    file_exists("/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . $ip_address_file_path . "/l") == true &&
    (filemtime("/var/www/ghostproxies.com/database/ip-addresses-proxies-history/" . $ip_address_file_path . "/l") > (time() - 1209600)) == true
  ) {
    http_response_code(403);
    echo "GhostProxies.com confirmed " . $_SERVER["REMOTE_ADDR"] . " is a high-risk IP address originating from a recently-opened proxy server. It was blocked automatically to mitigate malicious activity.";
    exit;
  }

  if (is_dir("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_directory_path) == false) {
    mkdir("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_directory_path, 0777, true);
    file_put_contents("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path, "1", FILE_APPEND);
  } elseif (
    file_exists("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) == false ||
    filesize("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path) < 30
  ) {
    file_put_contents("/var/www/ghostproxies.com/database/limits/60/" . $ip_address_file_path, "1", FILE_APPEND);
  }

  $data = array(
    "messages" => array()
  );
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?php echo $parameters["title"]; ?></title>
    <link href="https://ghostproxies.com<?php echo str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]); ?>" rel="canonical">
    <link href="/favicon.ico?<?php echo $visit_id; ?>" rel="icon" type="image/x-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;500;600;700;800&display=swap" rel="stylesheet">
<?php if (empty($parameters["no_index"]) == false): ?>
    <meta content="noindex" name="robots">
<?php endif; ?>
    <meta content="initial-scale=1, width=device-width" name="viewport">
    <meta charset="utf-8">
    <style type="text/css">
      a {
        color: #000;
        font-weight: 800;
        text-decoration: none;
        transition: all 0.3s ease;
      }
        a:hover {
          color: #111;
        }
      body,
      html {
        background: #fafafa;
        color: #000;
        font-size: 14px;
        font-weight: 500;
        line-height: 24px;
        overflow-x: hidden;
        margin: 0;
        width: 100%;
      }
      body,
      html,
      input,
      label,
      textarea {
        font-family: "Manrope";
      }
      code {
        background: #f3f3f3 !important;
        border: 2px solid #bcbcbc !important;
        border-radius: 4px;
        box-sizing: border-box !important;
        color: #333;
        display: inline-block !important;
        font-family: "DM Mono" !important;
        font-size: 12px;
        font-weight: 400;
        height: auto;
        line-height: 19px !important;
        margin: 3px 0 20px;
        overflow-x: scroll;
        padding: 22px 26px 21px !important;
        white-space: pre;
        width: 100% !important;
      }
      code,
      input,
      label,
      textarea {
        box-sizing: border-box;
        display: block;
        max-width: 100%;
        min-width: 100%;
        width: 100%;
      }
      footer {
        display: inline-block;
        margin: 100px 0;
        width: 100%;
      }
        footer ul {
          display: inline-block;
        }
        footer ul {
          margin: 55px 0 0;
          padding: 0;
          width: 100%;
        }
          footer ul li {
            display: inline-block;
            float: left;
            line-height: 18px;
            margin: 0 15px 5px 0;
          }
            footer ul li a {
              font-size: 12px;
              float: left;
            }
              footer ul li a svg {
                fill: #000;
                float: left;
                height: 14px;
              }
      h1,
      h2,
      h3 {
        display: inline-block;
        width: 100%;
      }
      h1 {
        font-size: 46px;
        font-weight: 200;
        line-height: 64px;
        margin: -6px 0 40px;
      }
      h2 {
        font-size: 28px;
        font-weight: 300;
        line-height: 42px;
        margin: 0 0 30px;
        padding-top: 18px;
      }
      h3 {
        font-size: 16px;
        font-weight: 600;
        margin: 4px 0 16px;
      }
      header {
        display: inline-block;
        padding: 105px 0 70px;
        width: 100%;
      }
        header span {
          margin: 0 5px 0 10px;
        }
      input {
        line-height: 39px;
      }
      input,
      textarea {
        background: #fff;
        border: 2px solid #000;
        border-radius: 4px;
        box-sizing: border-box !important;
        color: #000;
        font-weight: 600;
        padding: 10px 20px;
      }
        input:active,
        input:focus,
        textarea:active,
        textarea:focus {
          outline: none;
        }
      label {
        color: #444;
        display: block;
        line-height: 16px;
        word-wrap: break-word;
      }
      main {
        min-height: 280px;
        width: 100%;
      }
        main p a:hover {
          color: #333;
        }
      p {
        box-sizing: border-box;
        color: #444;
        display: inline-block;
        margin: 0 0 20px;
        width: 100%;
      }
        p.button-container {
          display: block;
          font-weight: 700;
          margin: 28px 0 0;
          max-width: 430px;
          position: relative;
        }
          p.button-container input {
            padding-right: 140px;
          }
          p.button-container .button {
            position: absolute;
            right: 0;
            top: 0;
          }
          p.button-container .column {
            cursor: default;
            display: block;
            font-weight: 800;
            user-select: none;
          }
          p.button-container .column,
          p.button-container .row {
            position: absolute;
          }
            p.button-container .column.column-2:before,
            p.button-container .column.column-3:before,
            p.button-container .column.column-4:before {
              content: "."
            }
            p.button-container .column.column-1 {
              color: #999;
              left: 2px;
            }
            p.button-container .column.column-2 {
              color: #bbb;
              left: 30px;
            }
            p.button-container .column.column-3 {
              color: #ccc;
              left: 63px;
            }
            p.button-container .column.column-4 {
              color: #ddd;
              left: 96px;
            }
          p.button-container .row {
            right: -12px;
          }
          p.button-container .row.row-1 {
            top: -1px;
          }
          p.button-container .row.row-2 {
            top: 19px;
          }
          p.button-container .row.row-3 {
            top: 39px;
          }
        p .code {
          background: #f3f3f3;
          border: 2px solid #bcbcbc;
          border-radius: 4px;
          color: #333;
          display: inline-block;
          font-family: "DM Mono";
          font-size: 12px;
          font-weight: 500;
          letter-spacing: -0.2px;
          line-height: 20px;
          margin: 2px;
          padding: 1px 6px 0;
        }
      strong {
        font-weight: 700;
      }
      textarea {
        height: 150px;
        line-height: 24px;
        padding: 15px 20px;
      }
      ul {
        line-height: 24px;
        list-style: none;
        margin: 0;
        padding: 0;
      }
        ul li {
          color: #444;
          margin-bottom: 15px;
        }
      .button {
        background: #000;
        border: none;
        border-radius: 4px;
        box-sizing: border-box;
        color: #fff;
        cursor: pointer;
        display: inline-block;
        font-weight: 800;
        line-height: 38px;
        padding: 12px 36px;
        text-align: center;
        transition: all 0.3s ease;
        user-select: none;
      }
        .button:hover {
          background: #111;
          color: #fff;
        }
      .checkbox {
        background: #fff;
        border: 2px solid #000;
        border-radius: 4px;
        cursor: pointer;
        display: block;
        float: left;
        height: 20px;
        margin-right: 10px;
        width: 20px;
      }
        .checkbox.active {
          background: url("/check.png") center 7px no-repeat #000;
          background-size: 10px;
        }
      .container {
        box-sizing: border-box;
        margin: 0 auto;
        max-width: 720px;
        padding: 0 25px;
        width: 100%;
      }
      .form button {
        background: #000;
        border: none;
        border-radius: 3px;
        color: #fff;
        cursor: pointer;
        display: inline-block;
        font-family: "Manrope";
        font-size: 14px;
        font-weight: 800;
        line-height: 38px;
        letter-spacing: 0 !important;
        margin: 23px 0 0 !important;
        padding: 12px 36px;
      }
      .form p {
        margin-bottom: 27px;
      }
      .ghostproxies-icon {
        float: left;
        margin-bottom: 9px;
      }
        .ghostproxies-icon img {
          float: left;
          height: 32px !important;
          margin: -4px 20px 0 0;
        }
      .hidden {
        display: none;
      }
      .homepage .introduction {
        margin: 0 0 45px;
      }
      .homepage .introduction h1 {
        margin-bottom: 20px;
        max-width: 530px;
        width: 100%;
      }
      .message {
        margin: 0 0 15px !important;
      }
      .no-margin-bottom {
        margin-bottom: 0 !important;
      }
      .no-margin-left {
        margin-left: 0 !important;
      }
      .no-margin-right {
        margin-right: 0 !important;
      }
      .no-margin-top {
        margin-top: 0 !important;
      }
      .section {
        margin-bottom: 40px;
      }
      .social-icons {
        display: inline-block;
        margin: 20px 0 0;
        width: 100%;
      }
        .social-icons a {
          display: inline-block;
          height: 16px;
          width: 16px;
        }
        .social-icons a,
        .social-icons a svg,
        .social-icons li {
          float: left;
        }
        .social-icons li {
          margin: 0 15px 0 0;
          width: auto;
        }
      .spotlight {
        background: #000;
        border-radius: 4px;
        box-sizing: border-box;
        padding: 23px 32px 24px;
        position: relative;
        transition: all 0.3s ease;
      }
        .spotlight:hover {
          background: #111;
        }
        .spotlight a {
          border-radius: 3px;
          height: 100%;
          left: 0;
          position: absolute;
          text-indent: -9999px;
          top: 0;
          width: 100%;
        }
        .spotlight h3 {
          display: block;
          font-size: 16px;
          font-weight: 700;
          line-height: 23px;
          margin: 18px 0 10px;
        }
        .spotlight h3,
        .spotlight .seconds-ago {
          color: #fff;
        }
        .spotlight p {
          margin-bottom: 13px;
        }
        .spotlight p,
        .spotlight .arrow {
          color: #999;
        }
        .spotlight .arrow {
          color: #fff;
          font-size: 21px;
          font-weight: 200;
        }
        .spotlight .seconds-ago {
          display: inline-block;
          font-size: 11px;
          font-weight: 500;
          margin-top: 3px;
          width: 100%;
        }
      .text-input {
        margin: 7px 0 25px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <header>
        <a class="ghostproxies-icon" href="/"><img alt="GhostProxies Icon" src="/ghostproxies.png?<?php echo $visit_id; ?>"> GhostProxies</a>
      </header>
