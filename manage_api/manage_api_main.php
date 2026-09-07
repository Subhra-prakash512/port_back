<?php
session_start();
require"../db.php";
/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION["user_id"];


/*

|--------------------------------------------------------------------------
| Fetch APIs
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT api_id, api_name, unique_add_on, api_url
    FROM api_log
    WHERE user_id = ?
    ORDER BY api_id DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

?> 

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <link rel="stylesheet" href="css/m_a_m.css">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage API</title>



    

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
        href="dashboard.php"
        class="back"
    >
        ← Dashboard
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


        <div class="header-row">

            <div>

                <h1>
                    Manage APIs
                </h1>

                <p>
                    View and manage all your APIs.
                </p>

            </div>


            <a
                href="create-api.php"
                class="create-btn"
            >
                + Create New API
            </a>

        </div>

    </div>



    <!-- =========================================
         API LIST
    ========================================= -->

    <div class="api-list">


        <?php if ($result->num_rows > 0): ?>


            <!-- TABLE HEADER -->

            <div class="api-header">

                <div>
                    API Name
                </div>

                <div>
                    API URL
                </div>

                <div>
                    Copy
                </div>

                <div>
                    Manage
                </div>

            </div>



            <!-- API ROWS -->

            <?php while ($api = $result->fetch_assoc()): ?>


                <div class="api-row">


                    <!-- API NAME -->

                    <div class="api-name">

                        <div class="api-icon">
                            ⚡
                        </div>

                        <div class="api-name-text">

                            <h3>
                                <?php
                                echo htmlspecialchars(
                                    $api["api_name"]
                                );
                                ?>
                            </h3>

                            <span>
                                API #<?php echo $api["api_id"]; ?>
                            </span>

                        </div>

                    </div>



                    <!-- API URL -->

                    <div class="api-url">

                        <code
                            id="url-<?php echo $api["api_id"]; ?>"
                        >
                            <?php
                            echo htmlspecialchars(
                                $api["api_url"]
                            );
                            ?>
                        </code>

                    </div>



                    <!-- ACTIONS -->

                    <div class="row-actions">

                       <button
    type="button"
    class="copy-btn"
    onclick="copyAPI(
        '<?php echo htmlspecialchars($api["api_id"], ENT_QUOTES, "UTF-8"); ?>',
        this
    )"
>
    Copy
</button>


                        <a
                            href="manage-single-api.php?id=<?php echo $api["api_id"]; ?>"
                            class="manage-btn"
                        >
                            Manage
                        </a>

                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- EMPTY -->

            <div class="empty">

                <div class="empty-icon">
                    +
                </div>

                <h2>
                    No APIs Yet
                </h2>

                <p>
                    Create your first API to get started.
                </p>

                <a
                    href="create-api.php"
                    class="create-btn"
                >
                    + Create New API
                </a>

            </div>


        <?php endif; ?>


    </div>


</main>



<!-- =========================================
     COPY JAVASCRIPT
========================================= -->

<script>
function copyAPI(id, button) {

    const urlElement = document.getElementById("url-" + id);

    if (!urlElement) {
        alert("URL not found.");
        return;
    }

    const url = urlElement.textContent.trim();

    // Modern clipboard API
    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(url)
            .then(function () {
                showCopied(button);
            })
            .catch(function () {
                fallbackCopy(url, button);
            });

    } else {

        // Fallback for HTTP / older browsers
        fallbackCopy(url, button);

    }
}


function fallbackCopy(url, button) {

    const textarea = document.createElement("textarea");

    textarea.value = url;

    textarea.style.position = "fixed";
    textarea.style.left = "-9999px";

    document.body.appendChild(textarea);

    textarea.focus();
    textarea.select();

    try {

        document.execCommand("copy");

        showCopied(button);

    } catch (error) {

        alert("Unable to copy URL.");

    }

    document.body.removeChild(textarea);
}


function showCopied(button) {

    button.innerText = "Copied ✓";

    button.classList.add("copied");

    setTimeout(function () {

        button.innerText = "Copy";

        button.classList.remove("copied");

    }, 2000);
}
</script>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>