<?php
session_start();
require "../db.php";

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];

$error = "";
$success = "";

$showKeyForm = false;

$api_name = "";
$key_count = 0;


/*
|--------------------------------------------------------------------------
| STEP 1 - API INFORMATION
|--------------------------------------------------------------------------
*/

if (isset($_POST["continue"])) {

    $api_name = trim($_POST["api_name"]);
    $key_count = intval($_POST["key_count"]);

    if (empty($api_name)) {

        $error = "Please enter API name.";

    } elseif ($key_count < 1) {

        $error = "Please enter at least 1 key.";

    } elseif ($key_count > 50) {

        $error = "Maximum 50 keys are allowed.";

    } else {

        $showKeyForm = true;
    }
}


/*
|--------------------------------------------------------------------------
| STEP 2 - PROCESS API
|--------------------------------------------------------------------------
*/

if (isset($_POST["process"])) {

    $api_name = trim($_POST["api_name"]);

    $key_names = $_POST["key_name"] ?? [];
    $key_types = $_POST["key_type"] ?? [];

    if (empty($api_name)) {

        $error = "API name is required.";

    } elseif (empty($key_names)) {

        $error = "Please add at least one key.";

    } else {

        $valid = true;

        foreach ($key_names as $index => $key_name) {

            if (empty(trim($key_name))) {
                $valid = false;
                break;
            }

            if (!isset($key_types[$index]) || empty($key_types[$index])) {
                $valid = false;
                break;
            }
        }

        if (!$valid) {

            $error = "Please complete all key fields.";

            $showKeyForm = true;

            $key_count = count($key_names);

        } else {

            /*
             * API processing will be added here.
             *
             * Example:
             * Save API information to MySQL database.
             */
            //code random genarate 
            $api_id_value = 5;

            $chars =
                "ABCDEFGHIJKLMNOPQRSTUVWXYZ" .
                "abcdefghijklmnopqrstuvwxyz" .
                "0123456789" .
                "@#$%&*!";

            $code = "";

            for ($i = 0; $i < $api_id_value; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }

            $api_id_gen = $code;

            echo $api_id_gen;
            // unique add on 

            $unique_addon_value = 3;

            $chars =
                "ABCDEFGHIJKLMNOPQRSTUVWXYZ" .
                "abcdefghijklmnopqrstuvwxyz" .
                "0123456789" .
                "@#$%&*!";

            $code = "";

            for ($i = 0; $i < $unique_addon_value; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }

            $unique_addon_gen = $code;

            echo $unique_addon_gen;




            print_r($_POST);
            echo count($_POST);
            echo $_POST["api_name"];

            $api_name = $_POST["api_name"];

            /*
             * Save API information to MySQL database.
             */

            $api_id = $api_id_gen;
            $user_id = $_SESSION["user_id"];
            $unique_add_on = $unique_addon_gen;


            $sql = "INSERT INTO api_log
        (api_id, user_id, api_name, unique_add_on)
        VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ssss",
                $api_id,
                $user_id,
                $api_name,
                $unique_add_on
            );

                if ($stmt->execute()) {
                    echo "<br>API information saved successfully.";
                } else {
                    echo "<br>Error: " . $stmt->error;
                }










            //ending 

            $success = "API created successfully!";

            $showKeyForm = false;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/create_api.css">

    <title>Create API</title>



</head>


<body>


    <!-- =========================================
     NAVBAR
========================================= -->

    <nav class="navbar">

        <div class="logo">
            A<span>•</span>K
        </div>

        <a href="dashboard.php" class="back">
            ← Back to Dashboard
        </a>

    </nav>



    <!-- =========================================
     MAIN
========================================= -->

    <main class="container">


        <!-- HEADER -->

        <div class="page-header">

            <div class="small-title">
                API MANAGEMENT
            </div>

            <h1>
                Create New API
            </h1>

            <p>
                Configure your API and define its data keys.
            </p>

        </div>



        <!-- STEP INDICATOR -->

        <div class="steps">

            <div class="step <?php echo !$showKeyForm ? 'active' : ''; ?>">

                <div class="step-number">
                    1
                </div>

                API Details

            </div>


            <div class="step-line"></div>


            <div class="step <?php echo $showKeyForm ? 'active' : ''; ?>">

                <div class="step-number">
                    2
                </div>

                API Keys

            </div>

        </div>



        <!-- =========================================
         STEP 1
    ========================================= -->

        <?php if (!$showKeyForm && empty($success)): ?>

            <div class="form-card">

                <?php if ($error != ""): ?>

                    <div class="error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <form method="POST">


                    <!-- API NAME -->

                    <div class="form-group">

                        <label>
                            API Name
                        </label>

                        <input type="text" name="api_name" placeholder="Enter API name"
                            value="<?php echo htmlspecialchars($api_name); ?>" required>

                    </div>



                    <!-- KEY COUNT -->

                    <div class="form-group">

                        <label>
                            How many keys?
                        </label>

                        <input type="number" name="key_count" placeholder="Example: 5" min="1" max="50"
                            value="<?php echo $key_count > 0 ? $key_count : ''; ?>" required>

                    </div>



                    <button type="submit" name="continue" class="btn">
                        Continue →
                    </button>


                </form>

            </div>


        <?php endif; ?>



        <!-- =========================================
         STEP 2
    ========================================= -->

        <?php if ($showKeyForm): ?>

            <div class="form-card">


                <?php if ($error != ""): ?>

                    <div class="error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <div class="key-header">

                    <h2>
                        Define API Keys
                    </h2>

                    <div class="key-count">
                        <?php echo $key_count; ?> Keys
                    </div>

                </div>



                <form method="POST">


                    <!-- Keep API name -->

                    <input type="hidden" name="api_name" value="<?php echo htmlspecialchars($api_name); ?>">



                    <!-- Generate keys -->

                    <?php for ($i = 0; $i < $key_count; $i++): ?>

                        <div class="key-item">

                            <div class="key-number">

                                KEY <?php echo $i + 1; ?>

                            </div>


                            <div class="key-row">


                                <!-- KEY NAME -->

                                <div class="form-group">

                                    <label>
                                        Key Name
                                    </label>

                                    <input type="text" name="key_name[]" placeholder="Example: username" required>

                                </div>



                                <!-- KEY TYPE -->

                                <div class="form-group">

                                    <label>
                                        Key Type
                                    </label>

                                    <select name="key_type[]" required>

                                        <option value="">
                                            Select type
                                        </option>

                                        <option value="string">
                                            String
                                        </option>

                                        <option value="integer">
                                            Integer
                                        </option>

                                        <option value="float">
                                            Float
                                        </option>

                                        <option value="boolean">
                                            Boolean
                                        </option>

                                        <option value="email">
                                            Email
                                        </option>

                                        <option value="date">
                                            Date
                                        </option>

                                        <option value="url">
                                            URL
                                        </option>

                                    </select>

                                </div>


                            </div>

                        </div>

                    <?php endfor; ?>



                    <!-- PROCESS -->

                    <button type="submit" name="process" class="btn">
                        Process API →
                    </button>


                </form>

            </div>

        <?php endif; ?>



        <!-- =========================================
         SUCCESS
    ========================================= -->

        <?php if ($success != ""):




            ?>

            <div class="form-card">

                <div class="success">

                    ✓
                    <?php echo htmlspecialchars($success); ?>

                </div>


                <a href="dashboard.php" class="btn" style="
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    text-decoration:none;
                ">
                    Back to Dashboard
                </a>




            </div>

        <?php endif; ?>


    </main>


</body>

</html>