<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SQL Help</title>

    <link rel="stylesheet" href="css/sql-help.css">
</head>

<body>

<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">

    <div class="logo">
        S<span>•</span>P
    </div>

    <div class="nav-title">
        SQL Help
    </div>

    <a href="manage-api.php" class="back-btn">
        ← Back
    </a>

</nav>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="container">

    <section class="hero">

        <div class="hero-icon">
            SQL
        </div>

        <h1>SQL Query Help</h1>

        <p>
            Use SQL queries to view, insert, update and delete
            data from your API table.
        </p>

    </section>


    <!-- =====================================================
         IMPORTANT
    ====================================================== -->

    <div class="info-box">

        <div class="info-title">
            ⚡ API Table Placeholder
        </div>

        <p>
            Always use
            <code>{API_TABLE}</code>
            instead of writing your actual table name.
        </p>

        <div class="example">

            <span>Example:</span>

            <code>
                SELECT * FROM {API_TABLE}
            </code>

        </div>

    </div>


    <!-- =====================================================
         SELECT
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method select">
                SELECT
            </div>

            <div>
                <h2>Read Data</h2>

                <p>
                    Use SELECT to retrieve data from your API table.
                </p>
            </div>

        </div>


        <h3>Select all data</h3>

        <div class="code-box">
            <code>SELECT * FROM {API_TABLE}</code>
            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Select specific columns</h3>

        <div class="code-box">
            <code>SELECT id, username, age FROM {API_TABLE}</code>
            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Use WHERE</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE id = 5
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Find text</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE username = 'John'
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Greater than</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE age > 18
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Less than</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE age < 18
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Sort data</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
ORDER BY id ASC
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Newest first</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
ORDER BY id DESC
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Limit results</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
LIMIT 10
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Search using LIKE</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE username LIKE '%john%'
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Multiple conditions</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE age > 18 AND active = 1
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         INSERT
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method insert">
                INSERT
            </div>

            <div>
                <h2>Add Data</h2>

                <p>
                    Use INSERT to add new rows to your API table.
                </p>
            </div>

        </div>


        <h3>Insert one row</h3>

        <div class="code-box">
            <code>
INSERT INTO {API_TABLE}
(username, age, email)
VALUES
('John', 25, 'john@gmail.com')
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Insert multiple rows</h3>

        <div class="code-box">
            <code>
INSERT INTO {API_TABLE}
(username, age, email)
VALUES
('John', 25, 'john@gmail.com'),
('David', 30, 'david@gmail.com'),
('Alex', 22, 'alex@gmail.com')
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Insert only selected columns</h3>

        <div class="code-box">
            <code>
INSERT INTO {API_TABLE}
(username)
VALUES
('John')
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         UPDATE
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method update">
                UPDATE
            </div>

            <div>
                <h2>Change Data</h2>

                <p>
                    Use UPDATE to modify existing rows.
                </p>
            </div>

        </div>


        <h3>Update one value</h3>

        <div class="code-box">
            <code>
UPDATE {API_TABLE}
SET age = 30
WHERE id = 5
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Update multiple columns</h3>

        <div class="code-box">
            <code>
UPDATE {API_TABLE}
SET
username = 'David',
age = 35,
email = 'david@gmail.com'
WHERE id = 5
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Update multiple rows</h3>

        <div class="code-box">
            <code>
UPDATE {API_TABLE}
SET active = 0
WHERE age < 18
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         DELETE
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method delete">
                DELETE
            </div>

            <div>
                <h2>Delete Data</h2>

                <p>
                    Use DELETE to remove rows from your API table.
                </p>
            </div>

        </div>


        <h3>Delete one row</h3>

        <div class="code-box">
            <code>
DELETE FROM {API_TABLE}
WHERE id = 5
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Delete multiple rows</h3>

        <div class="code-box">
            <code>
DELETE FROM {API_TABLE}
WHERE age < 18
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <div class="warning">

            ⚠️
            <strong>Important:</strong>

            Always use a
            <code>WHERE</code>
            condition with DELETE.

        </div>

    </section>


    <!-- =====================================================
         OPERATORS
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method operators">
                WHERE
            </div>

            <div>
                <h2>Useful Operators</h2>

                <p>
                    Operators can be used with WHERE conditions.
                </p>
            </div>

        </div>


        <div class="operator-grid">

            <div>
                <code>=</code>
                <span>Equal</span>
            </div>

            <div>
                <code>!=</code>
                <span>Not equal</span>
            </div>

            <div>
                <code>&gt;</code>
                <span>Greater than</span>
            </div>

            <div>
                <code>&lt;</code>
                <span>Less than</span>
            </div>

            <div>
                <code>&gt;=</code>
                <span>Greater or equal</span>
            </div>

            <div>
                <code>&lt;=</code>
                <span>Less or equal</span>
            </div>

            <div>
                <code>LIKE</code>
                <span>Search text</span>
            </div>

            <div>
                <code>IN</code>
                <span>Match values</span>
            </div>

        </div>

    </section>


    <!-- =====================================================
         AND / OR
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method operators">
                AND / OR
            </div>

            <div>
                <h2>Combine Conditions</h2>

                <p>
                    Combine multiple conditions using AND or OR.
                </p>
            </div>

        </div>


        <h3>AND</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE age > 18
AND active = 1
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>OR</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE age < 18
OR active = 0
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         AGGREGATE
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method aggregate">
                COUNT
            </div>

            <div>
                <h2>Aggregate Functions</h2>

                <p>
                    Calculate values from multiple rows.
                </p>
            </div>

        </div>


        <h3>Count rows</h3>

        <div class="code-box">
            <code>
SELECT COUNT(*) AS total
FROM {API_TABLE}
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Average</h3>

        <div class="code-box">
            <code>
SELECT AVG(age) AS average_age
FROM {API_TABLE}
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Maximum</h3>

        <div class="code-box">
            <code>
SELECT MAX(age) AS maximum_age
FROM {API_TABLE}
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Minimum</h3>

        <div class="code-box">
            <code>
SELECT MIN(age) AS minimum_age
FROM {API_TABLE}
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Total</h3>

        <div class="code-box">
            <code>
SELECT SUM(age) AS total_age
FROM {API_TABLE}
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         GROUP BY
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method group">
                GROUP BY
            </div>

            <div>
                <h2>Group Data</h2>

                <p>
                    Group rows based on a column.
                </p>
            </div>

        </div>


        <div class="code-box">
            <code>
SELECT age, COUNT(*) AS total
FROM {API_TABLE}
GROUP BY age
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Group with HAVING</h3>

        <div class="code-box">
            <code>
SELECT age, COUNT(*) AS total
FROM {API_TABLE}
GROUP BY age
HAVING COUNT(*) > 1
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         NULL
    ====================================================== -->

    <section class="sql-card">

        <div class="card-header">

            <div class="method null">
                NULL
            </div>

            <div>
                <h2>Check Empty Values</h2>

                <p>
                    Find rows where a column is NULL.
                </p>
            </div>

        </div>


        <h3>Find NULL values</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE email IS NULL
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>


        <h3>Find non-NULL values</h3>

        <div class="code-box">
            <code>
SELECT * FROM {API_TABLE}
WHERE email IS NOT NULL
            </code>

            <button onclick="copyCode(this)">Copy</button>
        </div>

    </section>


    <!-- =====================================================
         SECURITY
    ====================================================== -->

    <section class="security">

        <h2>⚠️ Allowed SQL Operations</h2>

        <div class="allowed">

            <span>✓ SELECT</span>
            <span>✓ INSERT</span>
            <span>✓ UPDATE</span>
            <span>✓ DELETE</span>

        </div>


        <h3>Blocked Operations</h3>

        <div class="blocked">

            <span>✕ DROP</span>
            <span>✕ ALTER</span>
            <span>✕ CREATE</span>
            <span>✕ TRUNCATE</span>
            <span>✕ RENAME</span>
            <span>✕ GRANT</span>
            <span>✕ REVOKE</span>

        </div>

        <p>
            Your query runner only allows operations intended
            for managing data inside your API table.
        </p>

    </section>

</main>


<script>

function copyCode(button) {

    const code =
        button.parentElement
        .querySelector("code")
        .innerText
        .trim();

    navigator.clipboard.writeText(code);

    const oldText = button.innerText;

    button.innerText = "Copied!";

    setTimeout(() => {

        button.innerText = oldText;

    }, 1200);
}

</script>

</body>
</html>