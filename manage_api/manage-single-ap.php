<?php

session_start();
require "../db.php";

/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| GET API ID
|--------------------------------------------------------------------------
*/

$api_id = isset($_GET["id"])
    ? $_GET["id"]
    : '';

$user_id = isset($_SESSION["user_id"])
    ? intval($_SESSION["user_id"])
    : 0;





/*
|--------------------------------------------------------------------------
| CHECK API BELONGS TO USER
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT api_id, api_name, api_url
    FROM api_log
    WHERE api_id = ?
    AND user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $api_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {

    die("API not found.");

}

$api = $result->fetch_assoc();

$stmt->close();

print_r($api);


/*
|--------------------------------------------------------------------------
| API TABLE NAME
|--------------------------------------------------------------------------
|
| Example:
| API ID = 15
| Table  = api_data_15
|
*/

//$api_table = "api_data_" . $api_name;


/*
|--------------------------------------------------------------------------
| FETCH API FIELDS
|--------------------------------------------------------------------------
*/
$api_name = $api["api_name"];
$sql = "
    SELECT *
    FROM $api_name
    ORDER BY id ASC
";

$stmt = $conn->prepare($sql);



$stmt->execute();

$field_result = $stmt->get_result();

$fields = [];

while ($field = $field_result->fetch_assoc()) {

    $fields[] = $field;
    

}

echo "<br> ";

print_r($fields);
$stmt->close();




/*fetch api colume information */

$table_name = "x__7wdn";

$sql = "
    SELECT COLUMN_NAME, DATA_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = ?
    ORDER BY ORDINAL_POSITION
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $table_name);
$stmt->execute();

$result = $stmt->get_result();


$columns = [];

while ($row = $result->fetch_assoc()) {
    $columns[$row["COLUMN_NAME"]] = $row["DATA_TYPE"];
}
echo "<br> ";
print_r($columns);

$stmt->close();


/*
|--------------------------------------------------------------------------
| CREATE API TABLE IF IT DOES NOT EXIST
|--------------------------------------------------------------------------
*/
/*
if (count($fields) > 0) {

    $column_sql = "";

    foreach ($fields as $field) {

        $column_name = preg_replace(
            "/[^a-zA-Z0-9_]/",
            "_",
            $field["key_name"]
        );

        /*
        | Map application types to MySQL types
        

        switch ($field["key_type"]) {

            case "integer":
                $mysql_type = "INT";
                break;

            case "float":
                $mysql_type = "DECIMAL(15,4)";
                break;

            case "boolean":
                $mysql_type = "TINYINT(1)";
                break;

            case "date":
                $mysql_type = "DATE";
                break;

            case "email":
            case "url":
            case "string":
            default:
                $mysql_type = "TEXT";
                break;
        }

        $column_sql .=
            "`" . $column_name . "` " .
            $mysql_type .
            " NULL,";
    }


    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS `$api_table` (

            `id` INT AUTO_INCREMENT PRIMARY KEY,

            $column_sql

            `created_at`
            TIMESTAMP DEFAULT CURRENT_TIMESTAMP

        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
    ";

    if (!$conn->query($create_table_sql)) {

        die(
            "Unable to create API table: " .
            $conn->error
        );

    }
}
*/

/*
|--------------------------------------------------------------------------
| MESSAGES
|--------------------------------------------------------------------------
*/

$success = "";
$error = "";
$query_result = null;

$show_modal = false;


/*
|--------------------------------------------------------------------------
| ADD DATA
|--------------------------------------------------------------------------
*/

if (isset($_POST["add_data"])) {

    $values = [];

    $valid = true;


    foreach ($fields as $field) {

        $field_name = $field["key_name"];

        $clean_field_name = preg_replace(
            "/[^a-zA-Z0-9_]/",
            "_",
            $field_name
        );

        $value = $_POST["field_" . $field["id"]] ?? "";

        /*
        | Validate based on type
        */

        if (
            $field["key_type"] === "integer" &&
            $value !== "" &&
            !filter_var($value, FILTER_VALIDATE_INT)
        ) {

            $error =
                $field_name .
                " must be an integer.";

            $valid = false;

            break;
        }


        if (
            $field["key_type"] === "float" &&
            $value !== "" &&
            !is_numeric($value)
        ) {

            $error =
                $field_name .
                " must be a number.";

            $valid = false;

            break;
        }


        if (
            $field["key_type"] === "email" &&
            $value !== "" &&
            !filter_var($value, FILTER_VALIDATE_EMAIL)
        ) {

            $error =
                $field_name .
                " must be a valid email.";

            $valid = false;

            break;
        }


        /*
        | Boolean conversion
        */

        if ($field["key_type"] === "boolean") {

            $value = ($value === "1") ? 1 : 0;
        }


        $values[$clean_field_name] = $value;
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT DATA
    |--------------------------------------------------------------------------
    */

    if ($valid) {

        $column_names = [];
        $placeholders = [];
        $bind_values = [];
        $types = "";


        foreach ($values as $column => $value) {

            $column_names[] = "`$column`";

            $placeholders[] = "?";

            $bind_values[] = $value;

            /*
            | Everything is inserted as string safely.
            | MySQL converts numeric values where necessary.
            */

            $types .= "s";
        }


        $insert_sql = "
            INSERT INTO `$api_name`
            (" . implode(",", $column_names) . ")
            VALUES
            (" . implode(",", $placeholders) . ")
        ";


        $stmt = $conn->prepare($insert_sql);


        if ($stmt) {

            $stmt->bind_param(
                $types,
                ...$bind_values
            );


            if ($stmt->execute()) {

                $success =
                    "Data added successfully.";

            } else {

                $error =
                    "Unable to add data: " .
                    $stmt->error;
            }


            $stmt->close();

        } else {

            $error =
                "Database error: " .
                $conn->error;
        }
    }


    $show_modal = true;
}


/*
|--------------------------------------------------------------------------
| RUN SQL QUERY
|--------------------------------------------------------------------------
*/

if (isset($_POST["run_query"])) {

    $user_query = trim(
        $_POST["sql_query"] ?? ""
    );


    if ($user_query === "") {

        $error =
            "Please enter a SQL query.";

    } else {

        /*
        | Remove trailing ;
        */

        $user_query = rtrim(
            $user_query,
            " \t\n\r\0\x0B;"
        );


        /*
        |--------------------------------------------------------------------------
        | ONLY SELECT QUERIES
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                "/^SELECT\s/i",
                $user_query
            )
        ) {

            $error =
                "Only SELECT queries are allowed.";

        }

        /*
        |--------------------------------------------------------------------------
        | REQUIRE {API_TABLE}
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos(
                $user_query,
                "{API_TABLE}"
            ) === false
        ) {

            $error =
                "Use {API_TABLE} in your query.";
        }

        /*
        |--------------------------------------------------------------------------
        | BLOCK DANGEROUS SQL
        |--------------------------------------------------------------------------
        */

        elseif (
            preg_match(
                "/\b(INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE|RENAME|GRANT|REVOKE|UNION|INTO\s+OUTFILE|LOAD_FILE)\b/i",
                $user_query
            )
        ) {

            $error =
                "This SQL operation is not allowed.";

        } else {

            /*
            | Replace API table placeholder
            */

            $safe_query = str_replace(
                "{API_TABLE}",
                "`$api_table`",
                $user_query
            );


            /*
            | Execute SELECT
            */

            $query_result =
                $conn->query($safe_query);


            if (!$query_result) {

                $error =
                    "SQL Error: " .
                    $conn->error;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<link rel="stylesheet" href="css/m_s_a.css">

<title>
    Manage <?php echo htmlspecialchars($api["api_name"]); ?>
</title>



</head>


<body>


<!-- =========================================
     NAVBAR
========================================= -->

<nav class="navbar">

    <div class="logo">
        S<span>•</span>P
    </div>

    <a
        href="manage-api.php"
        class="back"
    >
        ← Manage APIs
    </a>

</nav>



<!-- =========================================
     MAIN
========================================= -->

<main class="container">


    <!-- API HEADER -->

    <div class="api-header">

        <div class="small-title">
            API MANAGEMENT
        </div>

        <h1>
            <?php
            echo htmlspecialchars(
                $api["api_name"]
            );
            ?>
        </h1>

        <div class="api-url">

            <?php
            echo htmlspecialchars(
                $api["api_url"]
            );
            ?>

        </div>

    </div>



    <!-- =========================================
         MESSAGES
    ========================================= -->

    <?php if ($error != ""): ?>

        <div class="message error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <?php if ($success != ""): ?>

        <div class="message success">

            ✓

            <?php
            echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>



    <!-- =========================================
         API DATA
    ========================================= -->

    <section class="card">

        <div class="card-header">

            <div>

                <h2>
                    API Data
                </h2>

                <p>
                    Add a new record to this API.
                </p>

            </div>


            <button
                type="button"
                class="btn"
                onclick="openModal()"
            >
                + Add Data
            </button>

        </div>


        <!-- FIELD LIST -->

        <div class="field-list">

            <?php foreach ($fields as $field): ?>

                <div class="field">

                    <div class="field-name">

                        <?php
                        echo htmlspecialchars(
                            $field["key_name"]
                        );
                        ?>

                    </div>


                    <div class="field-type">

                        <?php
                        echo htmlspecialchars(
                            $field["key_type"]
                        );
                        ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </section>



    <!-- =========================================
         SQL QUERY
    ========================================= -->

    <section class="card">

        <div class="card-header">

            <div>

                <h2>
                    Run SQL Query
                </h2>

                <p>
                    Query the data stored in this API.
                </p>

            </div>

        </div>


        <form method="POST">

            <textarea
                name="sql_query"
                class="sql-box"
                placeholder="SELECT * FROM {API_TABLE}"
            ><?php
                echo isset($_POST["sql_query"])
                    ? htmlspecialchars(
                        $_POST["sql_query"]
                    )
                    : "";
            ?></textarea>


            <div class="sql-help">

                Use

                <code>{API_TABLE}</code>

                as the table name.

                Example:

                <code>
                    SELECT * FROM {API_TABLE}
                </code>

            </div>


            <button
                type="submit"
                name="run_query"
                class="btn"
            >
                ▶ Run Query
            </button>

        </form>



        <!-- =====================================
             QUERY RESULT
        ====================================== -->

        <?php if ($query_result instanceof mysqli_result): ?>

            <div class="result-wrapper">

                <table class="result-table">

                    <thead>

                        <tr>

                            <?php

                            $columns =
                                $query_result->fetch_fields();

                            foreach ($columns as $column):

                            ?>

                                <th>

                                    <?php
                                    echo htmlspecialchars(
                                        $column->name
                                    );
                                    ?>

                                </th>

                            <?php endforeach; ?>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($query_result->num_rows > 0): ?>

                            <?php while (
                                $row =
                                $query_result->fetch_assoc()
                            ): ?>

                                <tr>

                                    <?php foreach (
                                        $row as $value
                                    ): ?>

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                (string)$value
                                            );
                                            ?>

                                        </td>

                                    <?php endforeach; ?>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="<?php
                                        echo count($columns);
                                    ?>"
                                    style="text-align:center;"
                                >

                                    No data found.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


</main>



<!-- =========================================
     ADD DATA MODAL
========================================= -->

<div
    class="modal <?php
        echo $show_modal ? "show" : "";
    ?>"
    id="addDataModal"
>

    <div class="modal-content">


        <div class="modal-title">

            <h2>
                Add Data
            </h2>

            <button
                type="button"
                class="close"
                onclick="closeModal()"
            >
                ×
            </button>

        </div>


        <form method="POST">


            <?php foreach ($fields as $field): ?>


                <div class="form-group">

                    <label>

                        <?php
                        echo htmlspecialchars(
                            $field["key_name"]
                        );
                        ?>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $field["key_type"]
                            );
                            ?>
                        </span>

                    </label>


                    <?php

                    /*
                    |--------------------------------------------------------------------------
                    | INPUT TYPE
                    |--------------------------------------------------------------------------
                    */

                    $input_type = "text";

                    switch ($field["key_type"]) {

                        case "email":
                            $input_type = "email";
                            break;

                        case "integer":
                            $input_type = "number";
                            break;

                        case "float":
                            $input_type = "number";
                            break;

                        case "date":
                            $input_type = "date";
                            break;

                        case "url":
                            $input_type = "url";
                            break;

                        case "boolean":
                            $input_type = "select";
                            break;
                    }

                    ?>


                    <?php if ($input_type === "select"): ?>

                        <select
                            name="field_<?php
                                echo $field["id"];
                            ?>"
                            class="form-input"
                        >

                            <option value="1">
                                True
                            </option>

                            <option value="0">
                                False
                            </option>

                        </select>


                    <?php else: ?>

                        <input

                            type="<?php
                                echo $input_type;
                            ?>"

                            name="field_<?php
                                echo $field["id"];
                            ?>"

                            class="form-input"

                            <?php
                            if (
                                $field["key_type"]
                                === "float"
                            ) {
                                echo 'step="any"';
                            }
                            ?>

                            placeholder="Enter <?php
                                echo htmlspecialchars(
                                    $field["key_name"]
                                );
                            ?>"

                        >

                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


            <button
                type="submit"
                name="add_data"
                class="btn"
                style="width:100%;"
            >
                Add Data
            </button>


        </form>

    </div>

</div>



<script>

/*
|--------------------------------------------------------------------------
| MODAL
|--------------------------------------------------------------------------
*/

function openModal() {

    document
        .getElementById("addDataModal")
        .classList.add("show");

}


function closeModal() {

    document
        .getElementById("addDataModal")
        .classList.remove("show");

}


/*
|--------------------------------------------------------------------------
| CLOSE WHEN CLICK OUTSIDE
|--------------------------------------------------------------------------
*/

document
    .getElementById("addDataModal")
    .addEventListener(
        "click",
        function(event) {

            if (
                event.target === this
            ) {

                closeModal();

            }

        }
    );


/*
|--------------------------------------------------------------------------
| ESCAPE KEY
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            closeModal();

        }

    }
);

</script>


</body>

</html>

<?php

$conn->close();

?>