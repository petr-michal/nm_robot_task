<?php

const API_URL = "https://area51.serverzone.dev/robot/";

function request(string $method, string $url, array $data = []): array
{
    $context = stream_context_create([
        'http' => [
            'method'        => $method,
            'header'        => "Content-Type: application/json\r\n",
            'content'       => json_encode($data),
            'ignore_errors' => true,
            'timeout'       => 10,
        ],
    ]);

    unset($http_response_header);
    $body = @file_get_contents($url, false, $context);

    $status = 0;
    if (isset($http_response_header[0]) &&
        preg_match('#HTTP/\S+\s+(\d{3})#', $http_response_header[0], $m)) {
        $status = (int) $m[1];
    }

    if ($status === 410) {
        throw new RuntimeException('Robotovi došla energie (410).');
    }

    if ($status >= 200 && $status < 300 && $body !== false) {
        $json = json_decode($body, true);
        if (is_array($json)) {
            return $json;
        }
    }

    return ["error" => "Request failed with status $status"];
}

/**
 * @throws Exception
 */
function createRobot($email): string
{
    $data = [
        'email' => $email,
    ];

    $data = request('POST', API_URL, $data);
    if (isset($data['id'])) {
        return $data['id'];
    } else {
        throw new Exception('Error creating robot: ' . json_encode($data));
    }
}

function moveRobot($id, $direction, $distance = 5): ?int
{

    $url = API_URL . "$id/move";

    $data = [
        'direction' => $direction,
        'distance' => $distance,
    ];

    $result = request('PUT', $url, $data);

    if(isset($result['distance'])) {
        if(is_integer($result['distance'])) {
            return $result['distance'];
        }
        throw new Exception('Invalid distance returned: ' . json_encode($result['distance']));
    }

    return null;

}

function escapeRobot($id, $salary=1) {

    $url = API_URL . "$id/escape";

    $data = [
        'salary' => $salary,
    ];

    $result = request('PUT', $url, $data);
    echo "Escape result: " . json_encode($result) . "\n";

    if (isset($result['success']) && $result['success']) {
        return $result;
    }

    throw new Exception('Error escaping robot');
}
