<?php

//Android Push Functions
function pushToUser($user_id, $title, $message, $type, $typedata, $con) {
    //get api key from https://code.google.com/apis/console/
    if (checkSettings($user_id, $type, $con)) {
        $regIds = getDeviceIdByUserId($user_id, $con);
        foreach ($regIds as $i => $regId) {
            $pusher = new Pusher();
            $pusher->notify($regId, $message, $title, $type, $typedata);
            pushLog($pusher->getOutputAsArray(), $con, $title, $message, $type, $typedata, $user_id, $regId);
        }
    }
}

function getDeviceIdByUserId($user_id, $con) {
    $res = $con->query("SELECT * FROM user_deviceid WHERE user_id = '$user_id' AND status = 1");
    $devids = array();
    if ($res->num_rows > 0) {
        while ($data = $res->fetch_assoc()) {
            array_push($devids, $data["device_id"]);
        }
    }
    return $devids;
}

function pushLog($output, $con, $title, $message, $type, $typedata, $user_id, $regId) {
    $multicast_id = $output["multicast_id"];
    $isSuccess = 0;
    if ($output["success"] > 0) {
        $isSuccess = 1;
    }
    $canonical_ids = $output["canonical_ids"];
    $message_id = $output["results"][0]["message_id"];

    $con->query("INSERT INTO `log_push`(`logpush_id`, `multicast_id`, `isSuccess`, `canonical_ids`,"
            . " `message_id`, `user_id`, `regId`, `title`, `messagedata`, `type`,"
            . " `typedata`, `push_time`) VALUES "
            . "(null,'$multicast_id','$isSuccess','$canonical_ids'"
            . ",'$message_id','$user_id','$regId','$title','$message','$type'"
            . ",'$typedata',now())");
}

function registerDevice($user_id, $device_id, $con) {
    $findDevId = $con->query("SELECT * FROM user_deviceid WHERE device_id = '$device_id'");
    if ($findDevId->num_rows == 0) {
        $con->query("INSERT INTO `user_deviceid`(`id`, `user_id`, `device_id`, `last_update`, `created_at`, `status`) "
                . "VALUES (null,'$user_id','$device_id',now(),now(),1)");
    }
    if ($con->error == "") {
        return true;
    } else {
        return false;
    }
}

function unRegisterDevice($user_id, $device_id, $con) {
    $con->query("UPDATE user_deviceid SET status = 0 WHERE user_id = '$user_id' AND device_id = '$device_id'");
}

class Pusher {

    const GOOGLE_GCM_URL = 'https://android.googleapis.com/gcm/send';

    private $apiKey;
    private $proxy;
    private $output;

    public function __construct($apiKey = null, $proxy = null) {
        $this->apiKey = "AIzaSyBUyZGXH7HDm41X-IxREop0IR8fJNsO7-w";
        $this->proxy = $proxy;
    }

    public function notify($regIds, $data, $title, $type = null, $typedata = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::GOOGLE_GCM_URL);
        if (!is_null($this->proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getPostFields($regIds, $data, $title, $type, $typedata));

        $result = curl_exec($ch);
        if ($result === false) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        $this->output = $result;
    }

    public function getOutputAsArray() {
        return json_decode($this->output, true);
    }

    public function getOutputAsObject() {
        return json_decode($this->output);
    }

    private function getHeaders() {
        return [
            'Authorization: key=' . $this->apiKey,
            'Content-Type: application/json'
        ];
    }

    private function getPostFields($regIds, $data, $title, $type, $typedata) {
        $fields = [
            'registration_ids' => is_string($regIds) ? [$regIds] : $regIds,
            'data' => is_string($data) ? ['message' => $data, "title" => $title, "type" => $type, "typedata" => $typedata] : $data,
        ];
        return json_encode($fields, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }

}

function checkSettings($user_id, $type, $con) {
    $res = $con->query("SELECT * FROM user_settings WHERE user_id = '$user_id' AND type = '$type'");
    if ($res->num_rows == 0) {
        return true;
    } else {
        $data = $res->fetch_assoc();
        if ($data["value"] == "1") {
            return true;
        } else {
            return false;
        }
    }
}

?>