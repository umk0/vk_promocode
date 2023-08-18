<?
error_reporting(E_ERROR | E_PARSE);
require_once 'setting.dev.php';
if (file_exists("lang/" . $setting["LANG"] . ".php")) {
    require_once "lang/" . $setting["LANG"] . ".php";
} else {
    require_once "lang/ru.php";
}
require_once "vendor/autoload.php";
require_once 'vk.php';
require_once 'CodeGenerator.php';
require_once 'function.php';

use Medoo\Medoo;
use xPaw\SourceQuery\SourceQuery;


$vk = new VK($setting["VK"]["access_token"], '5.131');
$DB = new Medoo([
    'database_type' => 'mysql',
    'server' => $setting["MYSQL"]["host"],
    'database_name' => $setting["MYSQL"]["database"],
    'username' => $setting["MYSQL"]["username"],
    'password' => $setting["MYSQL"]["password"],
    'prefix' => $setting["MYSQL"]["prefix"] . "_"
]);
$Query = new SourceQuery();
$data = json_decode(file_get_contents('php://input'), true);
$site = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
