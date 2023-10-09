<?php
include 'dbConfig.php';

$rowsPerPage = 100; // Set the number of rows per page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
$startRow = ($currentPage - 1) * $rowsPerPage;

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['name'];
    $file = $_FILES['file'];

    $handle = fopen($file['tmp_name'], 'r');

    // Read the first row of the CSV to determine column names
    $header = fgetcsv($handle);

    // Construct the SQL query to create a new table with dynamic columns
    $createTableSQL = "CREATE TABLE IF NOT EXISTS $table (";
    foreach ($header as $columnName) {
        $createTableSQL .= "$columnName VARCHAR(255), ";
    }
    $createTableSQL = rtrim($createTableSQL, ', '); // Remove the trailing comma and space
    $createTableSQL .= ")";

    // Create the table
    if ($conn->query($createTableSQL) === TRUE) {
        echo "<h2>" . $table . "</h2>";
        // echo "<div class='table-container'>";
        // echo "<table class='display' border='1' cellspacing='0' width='100%'><thead><tr>";

        // Fetch and display column names from the table
        $headerResult = $conn->query("SHOW COLUMNS FROM $table");

        // while ($row = $headerResult->fetch_assoc()) {
        //     echo "<th>" . $row["Field"] . "</th>";
        // }
        // echo "</tr></thead><tbody>";

        $columns = implode(", ", $header);
        $placeholders = implode(", ", array_fill(0, count($header), "?"));
        $insertSQL = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        // Prepare the statement
        $stmt = $conn->prepare($insertSQL);

        while (($row = fgetcsv($handle)) !== FALSE) {
            // Bind parameters dynamically based on the number of columns
            $paramTypes = str_repeat("s", count($header));
            $stmt->bind_param($paramTypes, ...$row);

            // Execute the insert statement
            if ($stmt->execute()) {
                // Insert successful
            } else {
                echo '<script>alert("Error inserting data: ' . $stmt->error . '")</script>';
            }
        }

        echo "</tbody></table>";
        echo "</div>";

        // Close the file handle
        fclose($handle);

    } else {
        echo "Error creating table: " . $conn->error;
    }
} else {
    // Form was not submitted
    echo "Form not submitted.";
}

$columnNames = array();

// Fetch and display data rows with pagination
$getDataSQL = "SELECT * FROM $table LIMIT $startRow, $rowsPerPage";
$result = $conn->query($getDataSQL);

if ($result->num_rows > 0) {
    echo "<div class='table-container'>";
    echo "<table id='myDataTable' class='display' border='1' cellspacing='0' width='100%'><thead><tr>";

    // Fetch and display column names from the table
    $headerResult = $conn->query("SHOW COLUMNS FROM $table");

    while ($row = $headerResult->fetch_assoc()) {
        echo "<th>" . $row["Field"] . "</th>";
    }
    echo "</tr></thead><tbody>";

    while ($rowData = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($rowData as $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table>";
    echo "</div>";

    // Your pagination code here (if needed)

    echo "<br><br><form id='generateForm'>";
    echo "<label for='column'>Select a Column: </label>";
    echo "<br><br><select name='column' id='column'>";

    $selectionFormResult = $conn->query("SHOW COLUMNS FROM $table");
    while ($row = $selectionFormResult->fetch_assoc()) {
        $columnName = $row["Field"];
        echo "<option value='$columnName'>$columnName</option>";
    }

    echo "</select>";

    echo "<input type='hidden' name='table' value='$table' id='hiddentable'>";

    echo "<input type='submit' id='Generate' value='Generate' class='btn' onclick='generateWordCloud(event)'>";
    echo "</form><br><br>";

} else {
    echo '<script>alert("No data found in the table")</script>';
}

?>
