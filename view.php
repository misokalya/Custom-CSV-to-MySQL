<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>View Results</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <h2>Student Results</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Module</th>
                <th>CA</th>
                <th>SE</th>
                <th>Total</th>
                <th>Grade</th>
            </tr>

            <?php
            $sql = "SELECT students.id, first_name, last_name, module_code, CA, SE, TOT, GRD
                FROM students
                JOIN results ON students.id = results.student_id";

            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['first_name']} {$row['last_name']}</td>
                    <td>{$row['module_code']}</td>
                    <td>{$row['CA']}</td>
                    <td>{$row['SE']}</td>
                    <td>{$row['TOT']}</td>
                    <td>{$row['GRD']}</td>
                  </tr>";
            }
            ?>
        </table>

        <a href="index.php" class="link">Add New Result</a>
    </div>

</body>

</html>