<?php
include "../../config.php";
session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION["role_id"] != 2) {
    http_response_code(401);
    exit('Unauthorized access');
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="medicine_inventory_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: max-age=0');

try {
    // Base query
    $query = "SELECT 
                medicine_id,
                medicine_name,
                brand,
                generic_name,
                dosage_form,
                strength,
                unit,
                stock_quantity,
                minimum_stock,
                unit_cost,
                expiry_date,
                batch_number,
                supplier,
                description,
                status,
                created_at,
                updated_at
              FROM tbl_medicine
              WHERE 1=1";

    $params = [];
    $types  = "";
    $values = [];

    // Apply filters based on URL parameters
    if (!empty($_GET['search'])) {
        $query .= " AND (medicine_name LIKE ? OR brand LIKE ? OR generic_name LIKE ?)";
        $types  .= "sss";
        $searchTerm = "%" . $_GET['search'] . "%";
        $values[] = $searchTerm;
        $values[] = $searchTerm;
        $values[] = $searchTerm;
    }

    if (!empty($_GET['status'])) {
        $query .= " AND status = ?";
        $types  .= "s";
        $values[] = $_GET['status'];
    }

    if (!empty($_GET['stock'])) {
        if ($_GET['stock'] === 'low') {
            $query .= " AND stock_quantity <= minimum_stock";
        } elseif ($_GET['stock'] === 'out') {
            $query .= " AND stock_quantity = 0";
        } elseif ($_GET['stock'] === 'normal') {
            $query .= " AND stock_quantity > minimum_stock";
        }
    }

    if (!empty($_GET['expiry_filter'])) {
        if ($_GET['expiry_filter'] === 'expired') {
            $query .= " AND expiry_date < CURDATE()";
        } elseif ($_GET['expiry_filter'] === 'expiring_soon') {
            $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($_GET['expiry_filter'] === 'expiring_3months') {
            $query .= " AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
        }
    }

    $query .= " ORDER BY medicine_name ASC";

    // Prepare & execute
    $stmt = $conn->prepare($query);
    if ($types && $values) {
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $medicines = $result->fetch_all(MYSQLI_ASSOC);

    // Helper functions
    function getStockStatus($currentStock, $minimumStock) {
        $stock = floatval($currentStock);
        $minimum = floatval($minimumStock);
        
        if ($stock <= 0) {
            return 'Out of Stock';
        } elseif ($stock <= $minimum) {
            return 'Low Stock';
        } elseif ($stock <= $minimum * 1.5) {
            return 'Normal';
        } else {
            return 'Good Stock';
        }
    }

    function getExpiryStatus($expiryDate) {
        if (!$expiryDate) return 'No Expiry';
        
        $today = new DateTime();
        $expiry = new DateTime($expiryDate);
        $daysUntilExpiry = $today->diff($expiry)->days;
        $isExpired = $expiry < $today;
        
        if ($isExpired) {
            return 'Expired';
        } elseif ($daysUntilExpiry <= 30) {
            return 'Expiring Soon';
        } elseif ($daysUntilExpiry <= 90) {
            return 'Check Expiry';
        } else {
            return 'Good';
        }
    }

    function calculateDaysUntilExpiry($expiryDate) {
        if (!$expiryDate) return 'N/A';
        
        $today = new DateTime();
        $expiry = new DateTime($expiryDate);
        $diff = $today->diff($expiry);
        
        if ($expiry < $today) {
            return -$diff->days . ' (Expired)';
        } else {
            return $diff->days;
        }
    }

    function formatCurrency($amount) {
        return '₱' . number_format(floatval($amount), 2);
    }

    // Start Excel output
    echo '<table border="1">';

    // Headers
    echo '<tr style="background-color: #f8f9fa; font-weight: bold;">';
    echo '<th>Medicine ID</th>';
    echo '<th>Medicine Name</th>';
    echo '<th>Brand</th>';
    echo '<th>Generic Name</th>';
    echo '<th>Dosage Form</th>';
    echo '<th>Strength</th>';
    echo '<th>Unit</th>';
    echo '<th>Current Stock</th>';
    echo '<th>Minimum Stock</th>';
    echo '<th>Stock Status</th>';
    echo '<th>Unit Cost</th>';
    echo '<th>Total Value</th>';
    echo '<th>Expiry Date</th>';
    echo '<th>Days Until Expiry</th>';
    echo '<th>Expiry Status</th>';
    echo '<th>Batch Number</th>';
    echo '<th>Supplier</th>';
    echo '<th>Status</th>';
    echo '<th>Description</th>';
    echo '<th>Date Added</th>';
    echo '<th>Last Updated</th>';
    echo '</tr>';

    // Data rows
    $totalValue = 0;
    $totalMedicines = count($medicines);
    $lowStockCount = 0;
    $expiredCount = 0;
    $expiringSoonCount = 0;

    foreach ($medicines as $medicine) {
        $stockStatus = getStockStatus($medicine['stock_quantity'], $medicine['minimum_stock']);
        $expiryStatus = getExpiryStatus($medicine['expiry_date']);
        $daysUntilExpiry = calculateDaysUntilExpiry($medicine['expiry_date']);
        $medicineValue = floatval($medicine['stock_quantity']) * floatval($medicine['unit_cost'] ?? 0);
        $totalValue += $medicineValue;

        // Count statistics
        if ($stockStatus === 'Low Stock' || $stockStatus === 'Out of Stock') {
            $lowStockCount++;
        }
        if ($expiryStatus === 'Expired') {
            $expiredCount++;
        }
        if ($expiryStatus === 'Expiring Soon') {
            $expiringSoonCount++;
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($medicine['medicine_id']) . '</td>';
        echo '<td>' . htmlspecialchars($medicine['medicine_name']) . '</td>';
        echo '<td>' . htmlspecialchars($medicine['brand'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['generic_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['dosage_form'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['strength'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['unit'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['stock_quantity']) . '</td>';
        echo '<td>' . htmlspecialchars($medicine['minimum_stock']) . '</td>';
        echo '<td>' . htmlspecialchars($stockStatus) . '</td>';
        echo '<td>' . formatCurrency($medicine['unit_cost'] ?? 0) . '</td>';
        echo '<td>' . formatCurrency($medicineValue) . '</td>';
        echo '<td>' . htmlspecialchars($medicine['expiry_date'] ? date('M d, Y', strtotime($medicine['expiry_date'])) : 'No Expiry') . '</td>';
        echo '<td>' . htmlspecialchars($daysUntilExpiry) . '</td>';
        echo '<td>' . htmlspecialchars($expiryStatus) . '</td>';
        echo '<td>' . htmlspecialchars($medicine['batch_number'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['supplier'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($medicine['status']) . '</td>';
        echo '<td>' . htmlspecialchars($medicine['description'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($medicine['created_at']))) . '</td>';
        echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($medicine['updated_at']))) . '</td>';
        echo '</tr>';
    }

    echo '</table>';

    // Summary information
    echo '<br><br>';
    echo '<table border="1">';
    echo '<tr style="background-color: #e9ecef; font-weight: bold;"><th colspan="2">Inventory Summary</th></tr>';
    echo '<tr><td><strong>Total Medicines</strong></td><td>' . $totalMedicines . '</td></tr>';
    echo '<tr><td><strong>Total Inventory Value</strong></td><td>' . formatCurrency($totalValue) . '</td></tr>';
    echo '<tr><td><strong>Low/Out of Stock Items</strong></td><td>' . $lowStockCount . '</td></tr>';
    echo '<tr><td><strong>Expired Items</strong></td><td>' . $expiredCount . '</td></tr>';
    echo '<tr><td><strong>Expiring Soon (30 days)</strong></td><td>' . $expiringSoonCount . '</td></tr>';
    echo '<tr><td><strong>Export Date</strong></td><td>' . date('Y-m-d H:i:s') . '</td></tr>';
    echo '</table>';

    // Filter information
    if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['stock']) || !empty($_GET['expiry_filter'])) {
        echo '<br>';
        echo '<table border="1">';
        echo '<tr style="background-color: #fff3cd; font-weight: bold;"><th colspan="2">Applied Filters</th></tr>';
        
        if (!empty($_GET['search'])) {
            echo '<tr><td><strong>Search Term</strong></td><td>' . htmlspecialchars($_GET['search']) . '</td></tr>';
        }
        if (!empty($_GET['status'])) {
            echo '<tr><td><strong>Status Filter</strong></td><td>' . htmlspecialchars($_GET['status']) . '</td></tr>';
        }
        if (!empty($_GET['stock'])) {
            echo '<tr><td><strong>Stock Filter</strong></td><td>' . htmlspecialchars($_GET['stock']) . '</td></tr>';
        }
        if (!empty($_GET['expiry_filter'])) {
            echo '<tr><td><strong>Expiry Filter</strong></td><td>' . htmlspecialchars($_GET['expiry_filter']) . '</td></tr>';
        }
        
        echo '</table>';
    }

    // Legend
    echo '<br>';
    echo '<table border="1">';
    echo '<tr style="background-color: #d1ecf1; font-weight: bold;"><th colspan="2">Stock Status Legend</th></tr>';
    echo '<tr><td><strong>Good Stock</strong></td><td>Stock > 1.5 × Minimum Stock</td></tr>';
    echo '<tr><td><strong>Normal</strong></td><td>Stock between Minimum and 1.5 × Minimum</td></tr>';
    echo '<tr><td><strong>Low Stock</strong></td><td>Stock ≤ Minimum Stock</td></tr>';
    echo '<tr><td><strong>Out of Stock</strong></td><td>Stock = 0</td></tr>';
    echo '</table>';

    echo '<br>';
    echo '<table border="1">';
    echo '<tr style="background-color: #f8d7da; font-weight: bold;"><th colspan="2">Expiry Status Legend</th></tr>';
    echo '<tr><td><strong>Good</strong></td><td>More than 90 days until expiry</td></tr>';
    echo '<tr><td><strong>Check Expiry</strong></td><td>31-90 days until expiry</td></tr>';
    echo '<tr><td><strong>Expiring Soon</strong></td><td>≤ 30 days until expiry</td></tr>';
    echo '<tr><td><strong>Expired</strong></td><td>Past expiry date</td></tr>';
    echo '</table>';

} catch (Exception $e) {
    echo '<h2>Export Error</h2>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Please contact the system administrator if this problem persists.</p>';
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>