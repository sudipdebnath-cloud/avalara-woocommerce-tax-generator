# avalara-woocommerce-tax-generator
 A PHP script to extract and process tax rates from ZIP files downloaded from Avalara.com, generating WooCommerce-compatible CSV files for easy integration into e-commerce platforms.
 
 # Tax Rates Processor in PHP

## Overview
This PHP script processes tax rates from a ZIP file containing CSV files provided by Avalara. It extracts the ZIP file, reads the CSV files, and generates WooCommerce-compatible CSV files with the specified format.

## Requirements
- PHP 7.0 or higher
- ZipArchive extension enabled (typically included in standard PHP installations)

## File Structure
- `process_tax_rates.php` - The main PHP script that performs the extraction and processing of tax rates.
- `TAXRATES_ZIP5.zip` - The ZIP file containing the original CSV tax rates files (must be placed in the same directory as the script).
- `extracted_files/` - Directory where extracted files and generated CSVs will be stored.

## Usage
1. Place the `TAXRATES_ZIP5.zip` file in the same directory as the `process_tax_rates.php` script.
2. Run the script via command line:
   ```bash
   php process_tax_rates.php

