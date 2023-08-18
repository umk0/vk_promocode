<?php
function loggetWebhook($url, $data)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($curl);
}
function genUniqPromo($base = "promo", $row = 'promocode', $mask = null, $characters = null)
{
    global $setting, $DB;
    if ($mask == null) {
        $mask = $setting["PROMOCODE"]["mask"];
    }
    if ($characters == null) {
        $characters = $setting["PROMOCODE"]["characters"];
    }
    $promocode = new Rilog\CodeGenerator();
    $promocode->setMask($mask);
    $promocode->setCharacters($characters);
    $promocode->setAmount(1);
    $promo = $promocode->getCodes()[0];
    $check = $DB->count($base, [
        $row => $promo
    ]);
    if ($check == 0) {
        return $promo;
    } else {
        return genUniqPromo();
    }
}
function num_word($value, $words, $show = true)
{
    $num = $value % 100;
    if ($num > 19) {
        $num = $num % 10;
    }

    $out = ($show) ?  $value . ' ' : '';
    switch ($num) {
        case 1:
            $out .= $words[0];
            break;
        case 2:
        case 3:
        case 4:
            $out .= $words[1];
            break;
        default:
            $out .= $words[2];
            break;
    }

    return $out;
}
function getYoomoneyUrl($order_id, $successUrl, $sum, $receiver, $mode = 0, $commission_type = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://yoomoney.ru/quickpay/confirm");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        http_build_query([
            "receiver" => $receiver,
            "label" => $order_id,
            "quickpay-form" => "button",
            "sum" => ($commission_type == 0) ? ($sum + ($sum * ($mode == 0) ? 0.01012 : 0.03012)) : $sum,// бля плюс минус формула рабочая в случае когда комиссию платит покупатель то продавец получит больше чем должен на сущщие копейки XD
            "paymentType" => ($mode == 0) ? "PC" : "AC",
            "successURL" => $successUrl
        ])
    );
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    return $finalUrl;
}
