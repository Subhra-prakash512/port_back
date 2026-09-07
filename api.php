<?php

header("Content-Type: application/json; charset=UTF-8");

require "db.php";


/*
|--------------------------------------------------------------------------
| JSON RESPONSE FUNCTION
|--------------------------------------------------------------------------
*/

function response($status, $message, $data = null, $code = 200)
{
    http_response_code($code);

    $output = [
        "success" => $status,
        "message" => $message
    ];

    if ($data !== null) {
        $output["data"] = $data;
    }

    echo json_encode(
        $output,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES |
        JSON_PRETTY_PRINT
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ONLY GET REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    response(
        false,
        "Only GET requests are allowed.",
        null,
        405
    );
}


/*
|--------------------------------------------------------------------------
| GET API KEY
|--------------------------------------------------------------------------
|
| Example:
|
| ?key=Z1t$A123456
|
*/

$api_key = trim($_GET["key"] ?? "");


if ($api_key === "") {

    response(
        false,
        "API key is required.",
        null,
        401
    );
}


/*
|--------------------------------------------------------------------------
| API KEY LENGTH
|--------------------------------------------------------------------------
*/

if (strlen($api_key) < 5) {

    response(
        false,
        "Invalid API key.",
        null,
        401
    );
}


/*
|--------------------------------------------------------------------------
| FIRST 5 CHARACTERS = API ID
|--------------------------------------------------------------------------
*/

$api_id = substr($api_key, 0, 5);


/*
|--------------------------------------------------------------------------
| VALIDATE API ID + API KEY
|--------------------------------------------------------------------------
|
| Change "api_key" below if your key column has another name.
|
*/

$sql = "
    SELECT
        api_id,
        api_name
    FROM api_log
    WHERE api_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    response(
        false,
        "Database error.",
        null,
        500
    );
}


$stmt->bind_param(
    "s",
    $api_id
);

$stmt->execute();

$result = $stmt->get_result();

$api = $result->fetch_assoc();

$stmt->close();

print_r($api); // Debugging line to check the fetched API data

/*
|--------------------------------------------------------------------------
| API NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$api) {

    response(
        false,
        "Invalid API ID.",
        null,
        401
    );
}


/*
|--------------------------------------------------------------------------
| VALIDATE API KEY
|--------------------------------------------------------------------------
|
| If api_key contains the complete key directly.
|
*/

if (!hash_equals(
    (string)$api["api_id"],
    (string)$api_id
)) {

    response(
        false,
        "Invalid API key.",
        null,
        401
    );
}


/*
|--------------------------------------------------------------------------
| GET TABLE NAME
|--------------------------------------------------------------------------
*/

$table_name = $api["api_name"];


/*
|--------------------------------------------------------------------------
| VALIDATE TABLE NAME
|--------------------------------------------------------------------------
*/

if (
    !preg_match(
        '/^[a-zA-Z0-9_]+$/',
        $table_name
    )
) {

    response(
        false,
        "Invalid API table.",
        null,
        500
    );
}


/*
|--------------------------------------------------------------------------
| CHECK TABLE EXISTS
|--------------------------------------------------------------------------
*/

$check_sql = "
    SELECT COUNT(*) AS total
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = ?
";

$stmt = $conn->prepare($check_sql);

if (!$stmt) {

    response(
        false,
        "Database error.",
        null,
        500
    );
}

$stmt->bind_param(
    "s",
    $table_name
);

$stmt->execute();

$check_result = $stmt->get_result();

$table_exists = $check_result->fetch_assoc();

$stmt->close();


if (
    !$table_exists ||
    intval($table_exists["total"]) !== 1
) {

    response(
        false,
        "API data table not found.",
        null,
        404
    );
}


/*
|--------------------------------------------------------------------------
| FETCH API DATA
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM `$table_name`
";

$result = $conn->query($sql);


if (!$result) {

    response(
        false,
        "Unable to fetch API data.",
        null,
        500
    );
}


/*
|--------------------------------------------------------------------------
| CONVERT MYSQL RESULT TO ARRAY
|--------------------------------------------------------------------------
*/

$data = [];

while ($row = $result->fetch_assoc()) {

    $data[] = $row;
}


/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

response(
    true,
    "API data fetched successfully.",
    $data,
    200
);

?>