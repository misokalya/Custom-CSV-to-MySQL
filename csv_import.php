<!-- Written by miso -->

<?php
// ============================================================
// CONFIG - Update these settings for your environment
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'csv-to-mysql');
define('TABLE_NAME', 'csv_data');

// ============================================================
// DATABASE CONNECTION
// ============================================================
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$message = '';
$error   = '';

// ============================================================
// HANDLE CSV UPLOAD & IMPORT
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {

    $file = $_FILES['csv_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload failed.';
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
        $error = 'Please upload a valid .csv file.';
    } else {
        $handle = fopen($file['tmp_name'], 'r');

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            $error = 'CSV file is empty or unreadable.';
        } else {
            // Sanitize column names
            $cols = array_map(fn($h) => preg_replace('/[^a-zA-Z0-9_]/', '_', trim($h)), $headers);

            // Drop & recreate table dynamically based on CSV headers
            $pdo->exec("DROP TABLE IF EXISTS `" . TABLE_NAME . "`");
            $colDefs = implode(', ', array_map(fn($c) => "`$c` TEXT", $cols));
            $pdo->exec("CREATE TABLE `" . TABLE_NAME . "` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                $colDefs
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

            // Prepare insert statement
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $colNames     = implode(', ', array_map(fn($c) => "`$c`", $cols));
            $stmt         = $pdo->prepare("INSERT INTO `" . TABLE_NAME . "` ($colNames) VALUES ($placeholders)");

            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== false) {
                // Pad/trim row to match column count
                $row = array_slice(array_pad($row, count($cols), null), 0, count($cols));
                $stmt->execute($row);
                $rowCount++;
            }

            fclose($handle);
            $message = "✅ Successfully imported <strong>$rowCount rows</strong> from <em>{$file['name']}</em>.";
        }
    }
}

// ============================================================
// FETCH DATA FOR DISPLAY
// ============================================================
$tableExists = $pdo->query("SHOW TABLES LIKE '" . TABLE_NAME . "'")->rowCount() > 0;
$rows        = [];
$columns     = [];

if ($tableExists) {
    $stmt    = $pdo->query("SELECT * FROM `" . TABLE_NAME . "` LIMIT 1000");
    $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columns = $rows ? array_keys($rows[0]) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CSV → MySQL Importer</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: #f0f4f8;
    color: #1a202c;
    min-height: 100vh;
    padding: 2rem;
  }

  h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1.5rem;
  }

  .card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .card h2 {
    font-size: 1rem;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: .05em;
  }

  .upload-area {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }

  input[type="file"] {
    border: 2px dashed #cbd5e0;
    border-radius: 8px;
    padding: .6rem 1rem;
    cursor: pointer;
    flex: 1;
    min-width: 200px;
    background: #f7fafc;
    font-size: .9rem;
    color: #4a5568;
  }

  button {
    background: #4f46e5;
    color: #fff;
    border: none;
    padding: .65rem 1.4rem;
    border-radius: 8px;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
  }

  button:hover { background: #4338ca; }

  .alert {
    padding: .75rem 1rem;
    border-radius: 8px;
    font-size: .9rem;
    margin-top: 1rem;
  }

  .alert-success { background: #f0fff4; border: 1px solid #9ae6b4; color: #276749; }
  .alert-error   { background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; }

  /* Table */
  .table-wrap {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: .88rem;
  }

  thead {
    background: #4f46e5;
    color: #fff;
    position: sticky;
    top: 0;
  }

  th {
    padding: .7rem 1rem;
    text-align: left;
    font-weight: 600;
    white-space: nowrap;
  }

  td {
    padding: .6rem 1rem;
    border-bottom: 1px solid #edf2f7;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  tr:last-child td { border-bottom: none; }
  tr:nth-child(even) td { background: #f7fafc; }
  tr:hover td { background: #ebf4ff; }

  .row-count {
    font-size: .85rem;
    color: #718096;
    margin-bottom: .75rem;
  }

  .empty { text-align: center; padding: 2rem; color: #a0aec0; font-size: .95rem; }
</style>
</head>
<body>

<h1>📂 CSV → MySQL Importer</h1>

<!-- Upload Form -->
<div class="card">
  <h2>Upload CSV File</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="upload-area">
      <input type="file" name="csv_file" accept=".csv" required>
      <button type="submit">Import CSV</button>
    </div>
  </form>

  <?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
</div>

<!-- Data Table -->
<div class="card">
  <h2>Data Table — <?= TABLE_NAME ?></h2>

  <?php if ($tableExists && $rows): ?>
    <p class="row-count">Showing <?= count($rows) ?> row<?= count($rows) !== 1 ? 's' : '' ?> (max 1,000)</p>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <?php foreach ($columns as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($row as $cell): ?>
                <td title="<?= htmlspecialchars($cell ?? '') ?>">
                  <?= htmlspecialchars($cell ?? '') ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php else: ?>
    <div class="empty">No data yet. Upload a CSV file to get started.</div>
  <?php endif; ?>
</div>

</body>
</html>
