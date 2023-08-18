<?php
include "bootstrap.php";
$payload = json_decode($data['object']['message']['payload'], true);
$peer_id = $data["object"]["message"]["peer_id"];

if ($data["type"] == 'confirmation') {
    exit($setting["VK"]["confirmation"]);
}
if ($setting["DEBUG"]['status']) {
    loggetWebhook($setting["DEBUG"]['url_webhook'], $data);
}
switch ($payload['command']) {
    case "pay_menu":
        $button = [];
        foreach ($setting["VK"]["services"] as $key => $services) {
            $button[][] = ["text", ["command" => "pay_service", "id" => $key], $services['name'], ($services['color']) ? $services['color'] : 'blue'];
        }
        $button[][] = ["text", ["command" => "start"], $lang["back_btn"], $setting["VK"]["back_btn_color"]];
        $result = $vk->SendButton($peer_id, $lang["service_message"], $button, true);
        if ($setting["DEBUG"]['status']) {
            loggetWebhook($setting["DEBUG"]['url_webhook'], $result);
        }
        exit('ok');
        break;
    case "status":
        try {
            $Query->Connect($setting['SERVER']["IP"], $setting['SERVER']["PORT"], 5, 0);

            $info = $Query->GetInfo();
            if (!$info) { //не доступен
                throw new Exception('No avaible server');
            } else {
                $players = $Query->GetPlayers();
                $path = __DIR__ . "/maps/";

                if ($info['Map']) {
                    if (file_exists($path . $info['Map'] . ".jpg")) {
                        $url_image = $site . "maps/" . $info['Map'] . ".jpg";
                    } else {
                        $url_image = $site . "maps/noimage.jpg";
                    }
                } else { //не доступен
                    $url_image = $site . "maps/noimage.jpg";
                }
                $vk->SendButton($peer_id, $lang["map"] . " " . $info['Map'], [[["text", ["command" => "start"], $lang["back_btn"], $setting["VK"]["back_btn_color"]]]], true, $url_image);
                $i = 1;
                $msg_players = "";
                foreach ($players as $player) {
                    $msg_players .= $i . ") " . $player['Name'] . " (Фраги: " . $player["Frags"] . ")\n";
                    $i++;
                }
                $vk->SendButton($peer_id, $lang["players"] . " " . count($players) . "/" . $info['MaxPlayers'] . "\n" . $msg_players, [[["text", ["command" => "start"], $lang["back_btn"], $setting["VK"]["back_btn_color"]]]], true);
            }
        } catch (Exception $e) {
            $vk->SendButton($peer_id, $lang["server_off"], [[["text", ["command" => "start"], $lang["back_btn"], $setting["VK"]["back_btn_color"]]]], true, $site . "maps/noresponse.jpg");
            if ($setting["DEBUG"]['status']) {
                loggetWebhook($setting["DEBUG"]['url_webhook'], $e->getMessage());
            }
        } finally {
            $Query->Disconnect();
        }
        exit('ok');
        break;
    case "pay_service":
        $service = $setting["VK"]["services"][$payload['id']];
        $carousel = [];
        foreach ($service["days_and_price"] as $day => $price) {
            $button = [];

            if ($setting["YOOMONEY"]["enabled"]) {
                $button[] = [
                    'action' => [
                        'type' => "open_link",
                        "link" => $site . "yoomoney.php?type=redir&id_service={$payload['id']}&id_day={$day}&mode=0&peer_id={$peer_id}",
                        "label" => $lang["pay_yoomoney_btn"]
                    ]
                ];
                $button[] = [
                    'action' => [
                        'type' => "open_link",
                        "link" => $site . "yoomoney.php?type=redir&id_service={$payload['id']}&id_day={$day}&mode=1&peer_id={$peer_id}",
                        "label" => $lang["pay_bank_card_btn"]
                    ]
                ];
            }
            if ($setting["VK_PAY"]["enabled"]) {
                $data = [
                    'id_services' => $payload['id'],
                    'id_day' => $day,
                    'peer_id' => $peer_id
                ];
                $button[] = [
                    'action' => [
                        'type' => "vkpay",
                        "payload" => json_encode($data, JSON_UNESCAPED_UNICODE),
                        "hash" => http_build_query([
                            "action" => "pay-to-group",
                            "group_id" => $setting["VK"]["group_id"],
                            "amount" => $price,
                            "data" => $data
                        ])
                    ]
                ];
            }
            $title = $lang["service_title"];
            $title = str_replace("%service_name%", $service['name'], $title);
            $title = str_replace("%days_text%", num_word($day, [$lang["day_1"], $lang["day_234"], $lang["day_n"]]), $title);
            $title = str_replace("%days%", $day, $title);
            $title = str_replace("%price_text%", num_word($price, [$lang["currency_1"], $lang["currency_234"], $lang["currency_n"]]), $title);
            $title = str_replace("%price%", $price, $title);
            $title = str_replace("%flags%", $service['flags'], $title);
            $desc = $lang["service_desc"];
            $desc = str_replace("%service_name%", $service['name'], $desc);
            $desc = str_replace("%service_desc%", (($service["desc"]) ? $service["desc"] : $lang["service_no_desc"]), $desc);
            $desc = str_replace("%days_text%", num_word($day, [$lang["day_1"], $lang["day_234"], $lang["day_n"]]), $desc);
            $desc = str_replace("%days%", $day, $desc);
            $desc = str_replace("%price_text%", num_word($price, [$lang["currency_1"], $lang["currency_234"], $lang["currency_n"]]), $desc);
            $desc = str_replace("%price%", $price, $desc);
            $desc = str_replace("%flags%", $service['flags'], $desc);
            $carousel[] = [
                "title" => $title,
                "description" => $desc,
                "photo_id" => $service["image"],
                "buttons" => $button
            ];
        }
        $result = $vk->SendCarousel($peer_id, $lang["service_term"], $carousel, null);
        $button[][] = ["text", ["command" => "pay_menu"], $lang["back_btn"], $setting["VK"]["back_btn_color"]];
        $vk->SendButton($peer_id, $lang["service_back"], $button, true);
        exit('ok');
        break;
    case "start":
        $buttons = [];

        foreach ($setting["VK"]['main_menu'] as $button) {
            if (!$setting["SERVER"]["STATUS_SERVER"] && $button["type"] == "status") {
                continue;
            }
            $buttons[] = [
                "text",
                ["command" => $button["type"]],
                $lang[$button["text"]],
                $button["color"]
            ];
        }
        $vk->SendButton($peer_id, $lang["welcome_message"], [$buttons], true);
        exit('ok');
        break;
}
