<?php


function APIExecute($Method, $URL, $fields = null)
{
    $curl = curl_init();

    if ($Method = 'GET') {
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST =>  $Method,
            CURLOPT_HTTPHEADER => array("cache-control: no-cache"),
        ));
    } else {
        curl_setopt_array($curl, array(
            CURLOPT_URL => $URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $Method,
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                "accept: */*",
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));
    }

    $response = curl_exec($curl);
    $err = curl_error($curl);

    $returnArray = array("error" => false, "data" => array());

    curl_close($curl);

    if ($err) {
        $returnArray["error"] = true;
        $returnArray["data"] = array("error" => "cURL Error #:" . $err);
    } else {
        $returnArray["error"] = false;
        $returnArray["data"] = array("response" => $response);
    }
    return $returnArray;
}
