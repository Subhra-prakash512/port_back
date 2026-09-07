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


$user_id = intval($_SESSION["user_id"]);


/*
|--------------------------------------------------------------------------
| GET API ID
|--------------------------------------------------------------------------
*/

$api_id = isset($_GET["id"])
    ? trim($_GET["id"])
    : '';

if ($api_id === '') {
    die("Invalid API ID.");
}



/*
|--------------------------------------------------------------------------
| GET API INFORMATION
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT  api_name, api_url
    FROM api_log
    WHERE api_id = ?
    AND user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ss",
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


/*
|--------------------------------------------------------------------------
| API TABLE NAME
|--------------------------------------------------------------------------
|
| Example:
|
| api_name = x__7wdn
|
| Table:
|
| x__7wdn
|
*/

$table_name = $api["api_name"];


/*
|--------------------------------------------------------------------------
| VALIDATE TABLE NAME
|--------------------------------------------------------------------------
|
| Since table name comes from your database, still validate it
| before using it in SQL.
|
*/




/*
|--------------------------------------------------------------------------
| FETCH API COLUMN INFORMATION
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        COLUMN_NAME,
        DATA_TYPE,
        IS_NULLABLE,
        COLUMN_KEY,
        EXTRA,
        COLUMN_DEFAULT
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = ?
    ORDER BY ORDINAL_POSITION
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $table_name
);

$stmt->execute();

$result = $stmt->get_result();


$columns = [];


while ($row = $result->fetch_assoc()) {

    $columns[] = $row;
}


$stmt->close();


/*
|--------------------------------------------------------------------------
| MESSAGES
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";

$show_modal = false;


/*
|--------------------------------------------------------------------------
| ADD DATA
|--------------------------------------------------------------------------
*/

if (isset($_POST["add_data"])) {

    $insert_columns = [];
    $insert_values = [];
    $types = "";

    $valid = true;


    /*
    |--------------------------------------------------------------------------
    | LOOP THROUGH REAL TABLE COLUMNS
    |--------------------------------------------------------------------------
    */

    foreach ($columns as $column) {

        $column_name = $column["COLUMN_NAME"];
        $data_type   = strtolower($column["DATA_TYPE"]);
        $extra       = strtolower($column["EXTRA"]);


        /*
        |--------------------------------------------------------------------------
        | DON'T ASK USER FOR AUTO INCREMENT COLUMNS
        |--------------------------------------------------------------------------
        */

        if (
            strpos($extra, "auto_increment") !== false
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | GET INPUT VALUE
        |--------------------------------------------------------------------------
        */

        $post_name = "field_" . $column_name;

        $value = $_POST[$post_name] ?? "";


        /*
        |--------------------------------------------------------------------------
        | EMPTY VALUE
        |--------------------------------------------------------------------------
        */

        if (
            $value === "" &&
            $column["IS_NULLABLE"] === "YES"
        ) {

            $insert_columns[] =
                "`" . $column_name . "`";

            $insert_values[] = null;

            $types .= "s";

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | INTEGER
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $data_type,
                [
                    "tinyint",
                    "smallint",
                    "mediumint",
                    "int",
                    "integer",
                    "bigint"
                ]
            )
        ) {

            if (
                $value !== "" &&
                filter_var(
                    $value,
                    FILTER_VALIDATE_INT
                ) === false
            ) {

                $error =
                    $column_name .
                    " must be an integer.";

                $valid = false;

                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FLOAT / DOUBLE / DECIMAL
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $data_type,
                [
                    "decimal",
                    "numeric",
                    "float",
                    "double",
                    "real"
                ]
            )
        ) {

            if (
                $value !== "" &&
                !is_numeric($value)
            ) {

                $error =
                    $column_name .
                    " must be a number.";

                $valid = false;

                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | EMAIL
        |--------------------------------------------------------------------------
        |
        | MySQL usually stores email as VARCHAR/TEXT.
        | We only validate email if column name contains "email".
        |
        */

        if (
            stripos(
                $column_name,
                "email"
            ) !== false &&
            $value !== ""
        ) {

            if (
                !filter_var(
                    $value,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                $error =
                    $column_name .
                    " must contain a valid email.";

                $valid = false;

                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        if (
            $data_type === "date" &&
            $value !== ""
        ) {

            $date = DateTime::createFromFormat(
                "Y-m-d",
                $value
            );

            if (
                !$date ||
                $date->format("Y-m-d") !== $value
            ) {

                $error =
                    $column_name .
                    " must be a valid date.";

                $valid = false;

                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | DATETIME
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $data_type,
                [
                    "datetime",
                    "timestamp"
                ]
            ) &&
            $value !== ""
        ) {

            $date = DateTime::createFromFormat(
                "Y-m-d\TH:i",
                $value
            );

            if (!$date) {

                $error =
                    $column_name .
                    " must be a valid date/time.";

                $valid = false;

                break;
            }

            $value =
                $date->format(
                    "Y-m-d H:i:s"
                );
        }


        /*
        |--------------------------------------------------------------------------
        | BOOLEAN
        |--------------------------------------------------------------------------
        |
        | MySQL BOOLEAN is normally TINYINT(1).
        |
        */

        if (
            $data_type === "tinyint" &&
            $column["COLUMN_NAME"] !== "id"
        ) {

            /*
            | Don't force boolean here because
            | TINYINT can also be a normal integer.
            */
        }


        /*
        |--------------------------------------------------------------------------
        | STORE COLUMN + VALUE
        |--------------------------------------------------------------------------
        */

        $insert_columns[] =
            "`" . $column_name . "`";

        $insert_values[] = $value;

        $types .= "s";
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT INTO TABLE
    |--------------------------------------------------------------------------
    */

    if ($valid) {

        if (count($insert_columns) === 0) {

            $error =
                "No columns available for inserting data.";

        } else {

            $placeholders = array_fill(
                0,
                count($insert_values),
                "?"
            );


            $insert_sql = "
                INSERT INTO `$table_name`
                (
                    " .
                    implode(
                        ",",
                        $insert_columns
                    ) .
                "
                )
                VALUES
                (
                    " .
                    implode(
                        ",",
                        $placeholders
                    ) .
                "
                )
            ";


            $stmt =
                $conn->prepare($insert_sql);


            if (!$stmt) {

                $error =
                    "Database error: " .
                    $conn->error;

            } else {

                /*
                |--------------------------------------------------------------------------
                | bind_param requires references
                |--------------------------------------------------------------------------
                */

                $bind_params = [];

                $bind_params[] = $types;


                foreach (
                    $insert_values
                    as $key => $value
                ) {

                    $bind_params[] =
                        &$insert_values[$key];
                }


                call_user_func_array(
                    [
                        $stmt,
                        "bind_param"
                    ],
                    $bind_params
                );


                if ($stmt->execute()) {

                    $success =
                        "Data added successfully.";

                    $show_modal = false;

                } else {

                    $error =
                        "Unable to add data: " .
                        $stmt->error;

                    $show_modal = true;
                }


                $stmt->close();
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW MODAL IF ERROR
    |--------------------------------------------------------------------------
    */

    if ($error !== "") {
        $show_modal = true;
    }
}
/*
|--------------------------------------------------------------------------
| SQL QUERY
|--------------------------------------------------------------------------
*/

$query_result = null;
$query_type   = null;

if (isset($_POST["run_query"])) {

    $user_query = trim($_POST["sql_query"] ?? "");

    if ($user_query === "") {

        $error = "Please enter a SQL query.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Remove ending ;
        |--------------------------------------------------------------------------
        */

        $user_query = rtrim(
            $user_query,
            " \t\n\r\0\x0B;"
        );

        /*
        |--------------------------------------------------------------------------
        | Only allow SELECT / INSERT / UPDATE / DELETE
        |--------------------------------------------------------------------------
        */

        if (!preg_match(
            "/^(SELECT|INSERT|UPDATE|DELETE)\b/i",
            $user_query
        )) {

            $error =
                "Only SELECT, INSERT, UPDATE and DELETE queries are allowed.";

        }

        /*
        |--------------------------------------------------------------------------
        | QUERY MUST USE API TABLE
        |--------------------------------------------------------------------------
        */

        elseif (
            stripos($user_query, "{API_TABLE}") === false
        ) {

            $error =
                "Use {API_TABLE} in your query.";

        }

        /*
        |--------------------------------------------------------------------------
        | BLOCK DANGEROUS COMMANDS
        |--------------------------------------------------------------------------
        */

        elseif (
            preg_match(
                "/\b(
                    DROP|
                    ALTER|
                    CREATE|
                    TRUNCATE|
                    RENAME|
                    GRANT|
                    REVOKE|
                    LOAD\s+DATA|
                    LOAD_FILE|
                    INTO\s+OUTFILE|
                    INTO\s+DUMPFILE|
                    EXEC|
                    EXECUTE|
                    CALL|
                    PROCEDURE|
                    FUNCTION|
                    EVENT|
                    TRIGGER|
                    SHUTDOWN|
                    FLUSH
                )\b/ix",
                $user_query
            )
        ) {

            $error =
                "This SQL operation is not allowed.";

        }

        /*
        |--------------------------------------------------------------------------
        | Prevent multiple SQL statements
        |--------------------------------------------------------------------------
        */

        elseif (
            strpos($user_query, ";") !== false
        ) {

            $error =
                "Multiple SQL statements are not allowed.";

        }

        else {

            /*
            |--------------------------------------------------------------------------
            | Detect Query Type
            |--------------------------------------------------------------------------
            */

            if (preg_match("/^SELECT\b/i", $user_query)) {

                $query_type = "SELECT";

            } elseif (preg_match("/^INSERT\b/i", $user_query)) {

                $query_type = "INSERT";

            } elseif (preg_match("/^UPDATE\b/i", $user_query)) {

                $query_type = "UPDATE";

            } elseif (preg_match("/^DELETE\b/i", $user_query)) {

                $query_type = "DELETE";
            }


            /*
            |--------------------------------------------------------------------------
            | Replace {API_TABLE}
            |--------------------------------------------------------------------------
            */

            $safe_query = str_replace(
                "{API_TABLE}",
                "`" . $table_name . "`",
                $user_query
            );


            /*
            |--------------------------------------------------------------------------
            | Execute Query
            |--------------------------------------------------------------------------
            */

            $query_result = $conn->query($safe_query);


            /*
            |--------------------------------------------------------------------------
            | Query Error
            |--------------------------------------------------------------------------
            */

            if (!$query_result) {

                $error =
                    "SQL Error: " .
                    $conn->error;

            } else {

                /*
                |--------------------------------------------------------------------------
                | SELECT
                |--------------------------------------------------------------------------
                */

                if ($query_type === "SELECT") {

                    $success =
                        "SELECT query executed successfully.";

                }

                /*
                |--------------------------------------------------------------------------
                | INSERT
                |--------------------------------------------------------------------------
                */

                elseif ($query_type === "INSERT") {

                    $success =
                        $conn->affected_rows .
                        " row(s) inserted successfully.";

                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE
                |--------------------------------------------------------------------------
                */

                elseif ($query_type === "UPDATE") {

                    $success =
                        $conn->affected_rows .
                        " row(s) updated successfully.";

                }

                /*
                |--------------------------------------------------------------------------
                | DELETE
                |--------------------------------------------------------------------------
                */

                elseif ($query_type === "DELETE") {

                    $success =
                        $conn->affected_rows .
                        " row(s) deleted successfully.";

                }
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

<link
    rel="stylesheet"
    href="css/m_s_a.css"
>

<title>
    Manage
    <?php
    echo htmlspecialchars(
        $api["api_name"]
    );
    ?>
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
         MESSAGE
    ========================================= -->

    <?php if ($error !== ""): ?>

        <div class="message error">

            <?php
            echo htmlspecialchars(
                $error
            );
            ?>

        </div>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <div class="message success">

            ✓

            <?php
            echo htmlspecialchars(
                $success
            );
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
                     Database Table Column Names and Data Types
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



        <!-- =====================================
             SHOW TABLE COLUMNS
        ====================================== -->

        <div class="field-list">

            <?php foreach (
                $columns as $column
            ): ?>

                <div class="field">

                    <div>

                        <div class="field-name">

                            <?php
                            echo htmlspecialchars(
                                $column["COLUMN_NAME"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="field-type">

                        <?php
                        echo htmlspecialchars(
                            $column["DATA_TYPE"]
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
                    Run Queries (Data-Related) – Just Like MySQL
                </p>

            </div>

        </div>


        <form method="POST">

            <textarea
                name="sql_query"
                class="sql-box"
                placeholder="SELECT * FROM {API_TABLE}"
            ><?php

            echo isset(
                $_POST["sql_query"]
            )
                ? htmlspecialchars(
                    $_POST["sql_query"]
                )
                : "";

            ?></textarea>


            <div class="sql-help">

                Use

                <code>
                    {API_TABLE}
                </code>

                as the table name. don't change it.. Otherwise, a Fucking error will occur.

                <br><br>

                Example:

                <code>
                    SELECT * FROM {API_TABLE}
                </code>

            </div>

<!-- 
            <button
                type="submit"
                name="run_query"
                class="btn"
            >
                ▶ Run Query
            </button>

        </form> -->

        <div style="display: flex; gap: 10px; align-items: center;">

    <button
        type="submit"
        name="run_query"
        class="btn"
    >
        ▶ Run Query
    </button>

    <button
        type="button"
        class="btn"
       onclick="window.open('sql_help.php', '_blank')"
    >
        SQL Help
    </button>

</div>



        <!-- =====================================
             RESULT TABLE
        ====================================== -->

        <?php

        if (
            $query_result
            instanceof mysqli_result
        ):

        ?>

            <div class="result-wrapper">

                <table class="result-table">

                    <thead>

                        <tr>

                            <?php

                            $result_columns =
                                $query_result
                                ->fetch_fields();

                            foreach (
                                $result_columns
                                as $column
                            ):

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

                        <?php

                        if (
                            $query_result->num_rows
                            > 0
                        ):

                        ?>

                            <?php

                            while (
                                $row =
                                $query_result
                                ->fetch_assoc()
                            ):

                            ?>

                                <tr>

                                    <?php

                                    foreach (
                                        $row
                                        as $value
                                    ):

                                    ?>

                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                (string)
                                                $value
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
                                        echo count(
                                            $result_columns
                                        );
                                    ?>"
                                    style="
                                        text-align:center;
                                    "
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
     ADD DATA POPUP
========================================= -->

<div
    class="modal <?php
        echo $show_modal
            ? "show"
            : "";
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


            <?php foreach (
                $columns as $column
            ): ?>


                <?php

                $column_name =
                    $column["COLUMN_NAME"];

                $data_type =
                    strtolower(
                        $column["DATA_TYPE"]
                    );

                $extra =
                    strtolower(
                        $column["EXTRA"]
                    );


                /*
                |--------------------------------------------------------------------------
                | SKIP AUTO INCREMENT
                |--------------------------------------------------------------------------
                */

                if (
                    strpos(
                        $extra,
                        "auto_increment"
                    ) !== false
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | DEFAULT HTML INPUT
                |--------------------------------------------------------------------------
                */

                $input_type = "text";


                /*
                |--------------------------------------------------------------------------
                | MYSQL -> HTML INPUT TYPE
                |--------------------------------------------------------------------------
                */

                switch ($data_type) {

                    case "tinyint":

                        /*
                        | TINYINT(1) can be boolean.
                        | Otherwise number.
                        */

                        $input_type =
                            "number";

                        break;


                    case "smallint":
                    case "mediumint":
                    case "int":
                    case "integer":
                    case "bigint":

                        $input_type =
                            "number";

                        break;


                    case "decimal":
                    case "numeric":
                    case "float":
                    case "double":
                    case "real":

                        $input_type =
                            "number";

                        break;


                    case "date":

                        $input_type =
                            "date";

                        break;


                    case "datetime":
                    case "timestamp":

                        $input_type =
                            "datetime-local";

                        break;


                    case "time":

                        $input_type =
                            "time";

                        break;


                    case "year":

                        $input_type =
                            "number";

                        break;


                    case "text":
                    case "tinytext":
                    case "mediumtext":
                    case "longtext":

                        $input_type =
                            "textarea";

                        break;


                    case "json":

                        $input_type =
                            "textarea";

                        break;


                    default:

                        $input_type =
                            "text";

                        break;
                }

                ?>


                <div class="form-group">


                    <label>

                        <?php
                        echo htmlspecialchars(
                            $column_name
                        );
                        ?>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $data_type
                            );
                            ?>
                        </span>

                    </label>



                    <?php if (
                        $input_type ===
                        "textarea"
                    ): ?>


                        <textarea
                            name="field_<?php
                                echo htmlspecialchars(
                                    $column_name
                                );
                            ?>"
                            class="form-input"
                            rows="4"
                            placeholder="Enter <?php
                                echo htmlspecialchars(
                                    $column_name
                                );
                            ?>"
                        ></textarea>


                    <?php else: ?>


                        <input

                            type="<?php
                                echo $input_type;
                            ?>"

                            name="field_<?php
                                echo htmlspecialchars(
                                    $column_name
                                );
                            ?>"

                            class="form-input"

                            placeholder="Enter <?php
                                echo htmlspecialchars(
                                    $column_name
                                );
                            ?>"

                            <?php

                            if (
                                in_array(
                                    $data_type,
                                    [
                                        "decimal",
                                        "numeric",
                                        "float",
                                        "double",
                                        "real"
                                    ]
                                )
                            ) {

                                echo 'step="any"';
                            }

                            ?>

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
| OPEN MODAL
|--------------------------------------------------------------------------
*/

function openModal() {

    document
        .getElementById("addDataModal")
        .classList
        .add("show");

}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeModal() {

    document
        .getElementById("addDataModal")
        .classList
        .remove("show");

}


/*
|--------------------------------------------------------------------------
| CLICK OUTSIDE MODAL
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
| ESC KEY
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Escape"
        ) {

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