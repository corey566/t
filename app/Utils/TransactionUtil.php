--- a/app/Utils/TransactionUtil.php
+++ b/app/Utils/TransactionUtil.php
@@ -778,6 +778,14 @@
             $lines[$key] = $formated_sell_line;
         }
 
+        // yazeed sales person table updated begining
+        // Add service staff information for the line
+        if (!empty($line->res_service_staff_id) && !empty($line->service_staff)) {
+            $output_line['service_staff_name'] = $line->service_staff->first_name . ' ' . $line->service_staff->last_name;
+        } else {
+            $output_line['service_staff_name'] = '';
+        }
+        // yazeed sales person table updated end
             $output['item_discount_label'] = $il->common_settings['item_discount_label'] ?? '';
 
             $output['discounted_unit_price_label'] = $il->common_settings['discounted_unit_price_label'] ?? '';
@@ -1227,6 +1235,14 @@
                 'modifiers' => $modifiers,
                 'base_unit_price_exc_tax' => $base_unit_price_exc_tax,
                 'base_unit_price_inc_tax' => $base_unit_price_inc_tax,
+                'product_custom_fields' => $product_custom_fields,
+                'mfg_date' => $mfg_date,
+                'service_staff_name' => $service_staff_name,
+            ];
+
+            $lines[] = [
+                'name' => $product_name,
+                'product_variation' => $product_variation,
                 'product_custom_fields' => $product_custom_fields,
                 'mfg_date' => $mfg_date,
             ];