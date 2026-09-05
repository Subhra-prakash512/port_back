<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <style>
        /* =========================================
           GLOBAL
        ========================================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", Arial, sans-serif;
            background: #07070d;
            color: #ffffff;

            min-height: 100vh;
        }


        /* =========================================
           NAVBAR
        ========================================= */

        .navbar {
            height: 78px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 7%;

            border-bottom: 1px solid rgba(255, 255, 255, 0.08);

            background: rgba(7, 7, 13, 0.9);
        }

        .logo {
            font-size: 32px;
            font-weight: 800;

            letter-spacing: -2px;
        }

        .logo span {
            color: #b45cff;

            text-shadow:
                0 0 15px rgba(180, 92, 255, 0.7);
        }

        .logout {
            padding: 10px 22px;

            border: 1px solid #8f3ee8;

            border-radius: 30px;

            color: #c77cff;

            text-decoration: none;

            font-size: 14px;
            font-weight: 600;

            transition: 0.3s;
        }

        .logout:hover {
            background: #9b42ed;
            color: white;

            box-shadow:
                0 0 25px rgba(155, 66, 237, 0.35);
        }


        /* =========================================
           BACKGROUND GLOW
        ========================================= */

        .dashboard {
            position: relative;

            min-height: calc(100vh - 78px);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 50px 20px;

            overflow: hidden;
        }

        .dashboard::before {
            content: "";

            position: absolute;

            width: 650px;
            height: 650px;

            top: 50%;
            left: 50%;

            transform: translate(-50%, -50%);

            background: radial-gradient(
                circle,
                rgba(157, 55, 255, 0.15),
                transparent 70%
            );

            filter: blur(20px);

            pointer-events: none;
        }


        /* =========================================
           CONTENT
        ========================================= */

        .dashboard-content {
            position: relative;
            z-index: 2;

            width: 100%;
            max-width: 900px;

            text-align: center;
        }

        .welcome {
            color: #b45cff;

            font-size: 18px;

            margin-bottom: 10px;
        }

        h1 {
            font-size: 48px;

            letter-spacing: -1.5px;

            margin-bottom: 12px;
        }

        .description {
            color: #9999a8;

            font-size: 16px;

            margin-bottom: 45px;
        }


        /* =========================================
           BUTTON CARDS
        ========================================= */

        .dashboard-buttons {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 25px;

            max-width: 700px;

            margin: auto;
        }

        .dashboard-card {
            position: relative;

            min-height: 220px;

            padding: 35px 30px;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;

            background: rgba(16, 16, 24, 0.9);

            border: 1px solid rgba(255, 255, 255, 0.1);

            border-radius: 18px;

            text-decoration: none;

            color: white;

            overflow: hidden;

            transition: 0.35s ease;
        }

        .dashboard-card::before {
            content: "";

            position: absolute;

            width: 180px;
            height: 180px;

            background: rgba(168, 64, 255, 0.12);

            border-radius: 50%;

            filter: blur(60px);

            opacity: 0;

            transition: 0.35s;
        }

        .dashboard-card:hover::before {
            opacity: 1;
        }

        .dashboard-card:hover {
            transform: translateY(-7px);

            border-color: rgba(180, 92, 255, 0.6);

            box-shadow:
                0 15px 50px rgba(139, 50, 237, 0.18);
        }


        /* =========================================
           ICON
        ========================================= */

        .card-icon {
            width: 60px;
            height: 60px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 20px;

            border-radius: 14px;

            background: rgba(164, 65, 255, 0.12);

            border: 1px solid rgba(180, 92, 255, 0.25);

            color: #bd65ff;

            font-size: 28px;
        }


        /* =========================================
           CARD TEXT
        ========================================= */

        .dashboard-card h2 {
            font-size: 22px;

            margin-bottom: 10px;
        }

        .dashboard-card p {
            color: #8f8f9d;

            font-size: 14px;

            line-height: 1.5;

            max-width: 260px;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 650px) {

            .navbar {
                padding: 0 20px;
            }

            .dashboard {
                align-items: flex-start;

                padding-top: 70px;
            }

            h1 {
                font-size: 36px;
            }

            .dashboard-buttons {
                grid-template-columns: 1fr;

                max-width: 400px;
            }

            .dashboard-card {
                min-height: 190px;
            }
        }
    </style>
</head>

<body>


    <!-- NAVBAR -->

    <nav class="navbar">

        <div class="logo">
            A<span>•</span>K
        </div>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </nav>


    <!-- DASHBOARD -->

    <main class="dashboard">

        <div class="dashboard-content">

            <div class="welcome">
                Welcome back, <?php echo htmlspecialchars($username); ?>
            </div>

            <h1>
                API Dashboard
            </h1>

            <p class="description">
                Create and manage your APIs from one place.
            </p>


            <!-- BUTTONS -->

            <div class="dashboard-buttons">


                <!-- CREATE API -->

                <a href="create_api/create-api.php" class="dashboard-card">

                    <div class="card-icon">
                        +
                    </div>

                    <h2>
                        Create New API
                    </h2>

                    <p>
                        Create a new API and configure its settings.
                    </p>

                </a>


                <!-- MANAGE API -->

                <a href="manage_api/manage_api_main.php" class="dashboard-card">

                    <div class="card-icon">
                        ⚙
                    </div>

                    <h2>
                        Manage API
                    </h2>

                    <p>
                        View, edit, delete and manage your existing APIs.
                    </p>

                </a>


            </div>

        </div>

    </main>

</body>

</html>