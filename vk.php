<?php

class VK {
	private $token;
	private $version;
	private $endpoint;
	function __construct($token, $version) {
		$this->token = $token;
		$this->version = $version;
		$this->endpoint = "https://api.vk.com/method";
		//random_int(1, 9999999) = random_int(1, 9999999);
	}

	public function GetUserInfo($user_id) {
		$user = $this->HttpRequest("users.get", array("user_ids" => $user_id, "fields" => "sex"));
		return $user;
	}

	public function SendMessage($peer_id, $message, $attachment = null) {
		$this->Request("messages.send", array("peer_id" => $peer_id, "message" => $message, "attachment" => $attachment, "random_id" => random_int(1, 9999999)));
	}

	public function SendMessages($user_ids, $message, $attachment = null) {
		$this->Request("messages.send", array("user_ids" => $user_ids, "message" => $message, "attachment" => $attachment, "random_id" => random_int(1, 9999999)));
	}
    public function SendCarousel ($peer_id,$message,$elements,$attachment){
        $template = [
            "type"=>"carousel",
            "elements"=>$elements
        ];
        $template = json_encode($template, JSON_UNESCAPED_UNICODE);
        return $this->Request("messages.send", array("peer_id" => $peer_id, "message" => $message, "attachment" => $attachment, "random_id" => random_int(1, 9999999), "template"=>$template));
        
    }
	public function SendButton($peer_id, $message, $gl_massiv=array(), $inline, $attachment = null, $carousel= []) {
        $buttons = [];
        $i = 0;
        foreach ($gl_massiv as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                if ($button[0] == 'text') {
                    $color = $this->replaceColor($button[3]);
                    $buttons[$i][$j]["action"]["type"] = "text";
                    if ($button[1] != null)
                        $buttons[$i][$j]["action"]["payload"] = json_encode($button[1], JSON_UNESCAPED_UNICODE);
                    $buttons[$i][$j]["action"]["label"] = $button[2];
                    $buttons[$i][$j]["color"] = $color;
                } if ($button[0] == 'link') {
                    $buttons[$i][$j]["action"]["type"] = "open_link";
                    $buttons[$i][$j]["action"]["label"] = $button[1];
                    $buttons[$i][$j]["action"]["link"] = $button[2];
                } if ($button[0] == 'location') {
                	$buttons[$i][$j]["action"]["type"] = "location";
                } if ($button[0] == 'callback') {
                	$buttons[$i][$j]["action"]["type"] = "callback";
					if ($button[1] != null)
                        $buttons[$i][$j]["action"]["payload"] = json_encode($button[1], JSON_UNESCAPED_UNICODE);
                	$buttons[$i][$j]["action"]["label"] = $button[2];
                	$color = $this->replaceColor($button[3]);
                	$buttons[$i][$j]["color"] = $color;
                }
                $j++;
            }
            $i++;
        }
        if ($inline == true) {
        	$one_time = false;
        } else {
        	$one_time = true;
        }
        $is_addbutton = false;
        if($buttons){
            $is_addbutton = true;
        }
        $buttons = array(
        	"one_time" => $one_time,
            "inline" => $inline,
            "buttons" => $buttons);
        
        $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);
        
        return $this->Request("messages.send", array("peer_id" => $peer_id, "message" => $message, "attachment" => $attachment, "random_id" => random_int(1, 9999999), "keyboard" => $buttons));
		
	}

    public function SendUsersButton($user_ids, $message, $gl_massiv=array(), $inline, $attachment = null) {
        $buttons = [];
        $i = 0;
        foreach ($gl_massiv as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                if ($button[0] == 'text') {
                    $color = $this->replaceColor($button[3]);
                    $buttons[$i][$j]["action"]["type"] = "text";
                    if ($button[1] != null)
                        $buttons[$i][$j]["action"]["payload"] = json_encode($button[1], JSON_UNESCAPED_UNICODE);
                    $buttons[$i][$j]["action"]["label"] = $button[2];
                    $buttons[$i][$j]["color"] = $color;
                } if ($button[0] == 'link') {
                    $buttons[$i][$j]["action"]["type"] = "open_link";
                    $buttons[$i][$j]["action"]["label"] = $button[1];
                    $buttons[$i][$j]["action"]["link"] = $button[2];
                } if ($button[0] == 'location') {
                    $buttons[$i][$j]["action"]["type"] = "location";
                } if ($button[0] == 'callback') {
                    $buttons[$i][$j]["action"]["type"] = "callback";
                    if ($button[1] != null)
                        $buttons[$i][$j]["action"]["payload"] = json_encode($button[1], JSON_UNESCAPED_UNICODE);
                    $buttons[$i][$j]["action"]["label"] = $button[2];
                    $color = $this->replaceColor($button[3]);
                    $buttons[$i][$j]["color"] = $color;
                }
                $j++;
            }
            $i++;
        }
        if ($inline == true) {
            $one_time = false;
        } else {
            $one_time = true;
        }
        $buttons = array(
            "one_time" => $one_time,
            "inline" => $inline,
            "buttons" => $buttons);
        $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);
        $this->Request("messages.send", array("user_ids" => $user_ids, "message" => $message, "attachment" => $attachment, "random_id" => random_int(1, 9999999), "keyboard" => $buttons));
    }

	public function SendEvent($user_id, $peer_id, $event_id, $payload) {
		$this->Request("messages.sendMessageEventAnswer", array("user_id" => $user_id, "peer_id" => $peer_id, "event_id" => $event_id, "event_data" => json_encode($payload)));
	}

    private function replaceColor($color) {
        switch ($color) {
            case 'red':
                $color = 'negative';
                break;
            case 'green':
                $color = 'positive';
                break;
            case 'white':
                $color = 'secondary';
                break;
            case 'blue':
                $color = 'primary';
                break;
        }
        return $color;
    }

    private function Request($method, $params=array()) {
        $url = $this->endpoint."/$method?";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)."&access_token=".$this->token."&v=".$this->version);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

	private function HttpRequest($method, $params=array()) {
		return $request = json_decode(file_get_contents($this->endpoint."/$method?".http_build_query($params)."&access_token=".$this->token."&v=".$this->version));
	}

    public function sendOK(){
        echo 'ok';
        $response_length = ob_get_length();
        // check if fastcgi_finish_request is callable
        if (is_callable('fastcgi_finish_request')) {
            /*
             * This works in Nginx but the next approach not
             */
            session_write_close();
            fastcgi_finish_request();

            return;
        }

        ignore_user_abort(true);

        ob_start();
        $serverProtocole = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
        header($serverProtocole.' 200 OK');
        header('Content-Encoding: none');
        header('Content-Length: '. $response_length);
        header('Connection: close');

        ob_end_flush();
        ob_flush();
        flush();
    }

}