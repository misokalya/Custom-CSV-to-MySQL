<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Student Result</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h2>Add Student Result</h2>

        <form action="insert.php" method="POST">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="text" name="module_code" placeholder="Module Code" required>
            <input type="number" name="CA" placeholder="CA Marks" required>
            <input type="number" name="SE" placeholder="SE Marks" required>

            <button type="submit">Save Result</button>
        </form>

        <a href="view.php" class="link">View Results</a>
    </div>

</body>

</html>