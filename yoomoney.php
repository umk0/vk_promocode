<?php
include "bootstrap.php";
switch ($_GET['type']) {
    case "redir":
        $id_service = $_GET['id_service'];
        if ($id_service == NULL) {
            exit('Dont access');
        }
        $services = $setting["VK"]["services"][$id_service];
        $id_day = $_GET['id_day'];
        if ($id_day == NULL) {
            exit('Dont access');
        }
        $peer_id = $_GET['peer_id'];
        if ($peer_id == NULL) {
            exit('Dont access');
        }
        $order_id = genUniqPromo("orders", "id_order");
        $DB->insert("orders", [
            'id_order' => $order_id,
            'id_service' => $id_service,
            'id_day' => $id_day,
            'status' => 0,
            'last_upd' => time(),
            'peer_id' => $peer_id,
        ]);
        $mode = 0; // оплата через yoomoney
        if ($_GET['mode'] == 1) {
            $mode = 1; //оплата через карту
        }
        $urlYoomoney = getYoomoneyUrl(
            $order_id,
            "https://vk.com/im?sel=-" . $setting["VK"]["group_id"],
            $services["days_and_price"][$id_day],
            $setting["YOOMONEY"]["id"],
            $mode,
            $setting["YOOMONEY"]["commission_type"]
        );
        header('Location: ' . $urlYoomoney);
?>
        <meta http-equiv="refresh" content="0; url=<?php echo $urlYoomoney; ?>" />
<?
        break;
    case "pay":
        $order_id = $_POST['label'];
        if ($order_id) {
            $order = $DB->select("orders", "*", [
                "id_order" => $order_id,
                "status" => 0
            ])[0];
            if ($order) {
                $promocode = genUniqPromo();
                $DB->update("orders", [
                    'status' => 1,
                ], [
                    'id' => $order['id']
                ]);

                $service = $setting["VK"]["services"][$order['id_service']];
                $DB->insert("promo", [
                    "promocode" => $promocode,
                    "time_pay" => time(),
                    "time_activated" => 0,
                    "time_end" => 0,
                    "days" => (int)$order['id_day'],
                    "steamid" => "",
                    "flags" => $service['flags'],
                    "service_name" => $service['name'],
                    "id_vk" => $order['peer_id']
                ]);
                $button[][] = ["text", ["command" => "start"], $lang['main_menu_btn'], $setting["VK"]["main_menu_btn_color"]];
                $button[][] = ["text", ["command" => "pay_menu"], $lang['buy_more_btn'], $setting["VK"]["buy_more_btn_color"]];
                $success_text = $lang["pay_success_message"];
                $success_text = str_replace("%service_name%", $service['name'], $success_text);
                $success_text = str_replace("%service_desc%", (($service["desc"]) ? $service["desc"] : $lang["service_no_desc"]), $success_text);
                $success_text = str_replace("%promocode%", $promocode, $success_text);
                $success_text = str_replace("%flags%", $service['flags'], $success_text);
                $vk->SendButton($order["peer_id"], $success_text, $button, true);
            }
        }
        echo "OK";
        break;
}
