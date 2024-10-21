<?php
// Increase max execution time for large files
ini_set('max_execution_time', 0);  // 0 = unlimited execution time
ini_set('memory_limit', '-1');     // Remove memory limit

// Define directories and file paths dynamically based on the current directory
$zipFilePath = __DIR__ . '/TAXRATES_ZIP5.zip'; // Path to your ZIP file in the current directory
$outputDir = __DIR__ . '/extracted_files';      // Path to save extracted files in the current directory

// Function to extract the zip and process CSV files
function extractAndProcessZip($zipFilePath, $outputDir) {
    // Create output directory if it doesn't exist
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // Open the zip file
    $zip = new ZipArchive;
    if ($zip->open($zipFilePath) === TRUE) {
        // Extract all files to the output directory
        if ($zip->extractTo($outputDir)) {
            echo "ZIP file extracted successfully.\n";
        } else {
            echo "Failed to extract ZIP file.\n";
        }
        $zip->close();
    } else {
        echo "Failed to open ZIP file.\n";
    }
}

// Function to process each CSV file
function processCSVFiles($outputDir) {
    // Construct the inner directory path
    $innerDir = $outputDir . '/TAXRATES_ZIP5'; // Path to the inner directory created by the ZIP extraction

    // Check if the inner directory exists
    if (!is_dir($innerDir)) {
        echo "Inner directory does not exist: $innerDir\n";
        return;
    }

    // Loop through extracted CSV files in the inner directory
    $csvFiles = glob($innerDir . '/*.csv'); // Get all CSV files from the inner directory
    if (empty($csvFiles)) {
        echo "No CSV files found in the inner directory: $innerDir\n";
        // List the contents of the inner directory for debugging
        $innerFiles = scandir($innerDir);
        echo "Contents of the inner directory:\n";
        foreach ($innerFiles as $file) {
            if ($file !== '.' && $file !== '..') {
                echo " - $file\n";
            }
        }
        return;
    }

    foreach ($csvFiles as $csvFile) {
        processCSVFile($csvFile, $outputDir);
    }
}

// Function to process each CSV file
function processCSVFile($csvFilePath, $outputDir) {
    if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
        // Skip the first line if it is a header row
        $firstRow = true;

        // Array to hold data for each state
        $dataByState = [];

        // Read each row of the CSV file
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($firstRow) {
                $firstRow = false;
                continue; // Skip header row
            }

            // Check if expected columns exist
            if (count($row) < 4) {
                echo "Unexpected CSV format in file: $csvFilePath\n";
                fclose($handle);
                return;
            }

            // Extract required fields from the CSV row
            $stateCode = $row[0];  // State Code
            $postcode = $row[1];   // Zip Code
            $rate = $row[3] * 100; // EstimatedCombinedRate multiplied by 100 for WooCommerce format

            // Prepare WooCommerce format row with city name left blank
            $woocommerceRow = [
                'US',           // Country Code
                $stateCode,     // State Code
                $postcode,      // Postcode
                '',             // City (leave blank)
                $rate,          // Tax Rate (Estimated Combined Rate * 100)
                'Tax',          // Tax Name (Fixed as "Tax")
                '1',            // Priority
                '0',            // Compound (0 for no, 1 for yes)
                '1',            // Shipping (1 for yes, 0 for no)
                ''              // Tax Class (leave blank)
            ];

            // Group data by state code
            if (!isset($dataByState[$stateCode])) {
                $dataByState[$stateCode] = [];
            }
            $dataByState[$stateCode][] = $woocommerceRow;
        }

        // Close the input file handler
        fclose($handle);

        // Create output CSV files for each state
        foreach ($dataByState as $state => $rows) {
            $outputFilePath = $outputDir . '/' . 'US_' . $state . '_taxrates.csv';
            $outputFile = fopen($outputFilePath, 'w');

            // WooCommerce tax CSV columns
            $headers = ['Country code', 'State code', 'Postcode / ZIP', 'City', 'Rate %', 'Tax name', 'Priority', 'Compound', 'Shipping', 'Tax class'];
            fputcsv($outputFile, $headers); // Write headers to the CSV

            // Write all rows for the current state
            foreach ($rows as $row) {
                fputcsv($outputFile, $row);
            }

            // Close the output file handler
            fclose($outputFile);
            echo "Created: $outputFilePath\n";
        }

    } else {
        echo "Failed to open CSV file: $csvFilePath\n";
    }
}

// Run the extraction
extractAndProcessZip($zipFilePath, $outputDir);

// Process CSV files in the extracted inner directory
processCSVFiles($outputDir);
?>