<?php

$files = [
    'database/migrations/2018_01_09_111005_modify_payment_status_in_transactions_table.php',
    'database/migrations/2018_02_09_124945_modify_transaction_payments_table_for_contact_payments.php',
    'database/migrations/2018_02_13_183323_alter_decimal_fields_size.php',
    'database/migrations/2018_02_26_130519_modify_users_table_for_sales_cmmsn_agnt.php',
    'database/migrations/2018_03_29_110138_change_tax_field_to_nullable_in_business_table.php',
    'database/migrations/2018_03_31_140921_update_transactions_table_exchange_rate.php',
    'database/migrations/2018_04_09_135320_change_exchage_rate_size_in_business_table.php',
    'database/migrations/2018_06_05_111905_modify_products_table_for_modifiers.php',
    'database/migrations/2018_07_26_124720_change_design_column_type_in_invoice_layouts_table.php',
    'database/migrations/2018_08_08_110755_add_new_payment_methods_to_transaction_payments_table.php',
    'database/migrations/2018_08_08_122225_modify_cash_register_transactions_table_for_new_payment_methods.php',
    'database/migrations/2018_08_14_104036_add_opening_balance_type_to_transactions_table.php',
    'database/migrations/2018_09_27_111609_modify_transactions_table_for_purchase_return.php',
    'database/migrations/2018_11_02_171949_change_card_type_column_to_varchar_in_transaction_payments_table.php',
    'database/migrations/2018_12_06_114937_modify_system_table_and_users_table.php',
    'database/migrations/2018_12_14_103307_modify_system_table.php',
    'database/migrations/2019_06_28_133732_change_type_column_to_string_in_transactions_table.php',
    'database/migrations/2019_08_26_133419_update_price_fields_decimal_point.php',
    'database/migrations/2019_11_21_162913_change_quantity_field_types_to_decimal.php',
    'database/migrations/2019_12_19_181412_make_alert_quantity_field_nullable_on_products_table.php',
    'database/migrations/2020_07_09_174621_add_balance_field_to_contacts_table.php',
    'database/migrations/2020_07_23_104933_change_status_column_to_varchar_in_transaction_table.php',
    'database/migrations/2020_09_21_123224_modify_booking_status_column_in_bookings_table.php',
    'database/migrations/2020_11_17_164041_modify_type_column_to_varchar_in_contacts_table.php',
    'database/migrations/2021_03_24_183132_add_shipping_export_custom_field_details_to_contacts_table.php',
    'database/migrations/2021_08_25_114932_add_payment_link_fields_to_transaction_payments_table.php',
    'database/migrations/2021_09_03_061528_modify_cash_register_transactions_table.php',
    'database/migrations/2024_10_03_151459_modify_transaction_sell_lines_purchase_lines_table.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Extract the up() method content
    if (preg_match('/public function up\(\)\s*\{(.*?)\n    \}/s', $content, $match)) {
        $upContent = $match[1];
        
        // Check if it already has getDriverName
        if (strpos($upContent, 'getDriverName') !== false) {
            echo "Skipping (already fixed): " . basename($file) . "\n";
            continue;
        }
        
        // Wrap everything in MySQL check
        $newUpContent = "\n        if (DB::getDriverName() === 'mysql') {" . $upContent . "\n        }\n    ";
        
        $content = str_replace("public function up()\n    {" . $upContent . "\n    }", "public function up()\n    {" . $newUpContent . "}", $content);
        
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}

echo "All done!\n";
