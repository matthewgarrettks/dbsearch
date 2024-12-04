<?php
/**
 * Generated largely from ChatGPT with some help from myself
 *
 * @author Matt Garrett
 */

$shortopts = "h:u:p:n:s:P:r::"; // "::" for optional -P and -r argument
$options = getopt($shortopts);

// Validate required arguments
$requiredArgs = ['h', 'u', 'p', 'n', 's'];
foreach ($requiredArgs as $arg) {
    if (!isset($options[$arg])) {
        die("Missing required argument: -$arg\n");
    }
}

// Assign arguments to variables
$host = $options['h'];
$username = $options['u'];
$password = $options['p'];
$port = $options['P'] ?? 3306;
$dbname = $options['n'];
$search = $options['s'];
$replace = $options['r']?? null;

try {
    // Establish a MySQL connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve all tables in the database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Retrieve column details for the table
        $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            $columnName = $column['Field'];
            $columnType = $column['Type'];

            // Check if column type is text-based or numeric
            if (preg_match('/(char|text|blob)/i', $columnType) || preg_match('/(int|float|double|decimal)/i', $columnType)) {
                if ($replace !== null) {
                    // Perform replacement
                    $stmt = $pdo->prepare("UPDATE `$table` SET `$columnName` = REPLACE(`$columnName`, :search, :replace) WHERE `$columnName` LIKE :like");
                    $stmt->execute([':search' => $search, ':replace' => $replace, ':like' => "%$search%"]);
                    $affectedRows = $stmt->rowCount();
                    if ($affectedRows > 0) {
                        echo "Updated $affectedRows rows in $table.$columnName\n";
                    }
                } else {
                    // Search for occurrences
                    $stmt = $pdo->prepare("SELECT `$columnName` FROM `$table` WHERE `$columnName` LIKE :like");
                    $stmt->execute([':like' => "%$search%"]);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $row) {
                        $output = strlen($row[$columnName]) > 30?"...".substr($row[$columnName], stripos($row[$columnName], $search) -4, 30)."...":$row[$columnName];
                        echo "Found in $table.$columnName: " . $output . "\n";
                    }
                }
            }
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
