<?php
// Include database connection
require_once '../../backend/config.php';

try {
    // Get report ID from query parameter
    $report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;
    
    if ($report_id <= 0) {
        http_response_code(400);
        die('Invalid report ID');
    }
    
    // Query to get report details
    $query = "
        SELECT 
            r.report_id,
            r.child_id,
            r.generated_by,
            r.report_type,
            r.report_date,
            c.first_name,
            c.last_name,
            c.birthdate,
            c.gender,
            c.zone_id,
            z.zone_name,
            ud.full_name as generated_by_name,
            ud.contact,
            ud.address
        FROM tbl_report r
        LEFT JOIN tbl_child c ON r.child_id = c.child_id
        LEFT JOIN tbl_barangay z ON c.zone_id = z.zone_id
        LEFT JOIN tbl_user_details ud ON r.generated_by = ud.user_id
        WHERE r.report_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $report_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $report = $result->fetch_assoc();

    if (!$report) {
        http_response_code(404);
        die('Report not found');
    }
    
    // Calculate age
    $birthDate = new DateTime($report['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    // Create HTML content for the report with auto-print functionality
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Report #RPT-' . str_pad($report_id, 4, '0', STR_PAD_LEFT) . '</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                background-color: #f5f5f5;
                line-height: 1.6;
            }
            
            .print-container {
                background-color: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 800px;
                margin: 0 auto;
            }
            
            .header { 
                text-align: center; 
                border-bottom: 2px solid #333; 
                padding-bottom: 15px; 
                margin-bottom: 30px;
            }
            
            .header h1 {
                color: #2c5aa0;
                margin: 0 0 10px 0;
                font-size: 28px;
            }
            
            .header h2 {
                color: #666;
                margin: 0;
                font-size: 20px;
            }
            
            .section { 
                margin: 25px 0; 
                padding: 15px;
                border-left: 4px solid #2c5aa0;
                background-color: #f9f9f9;
            }
            
            .section h3 {
                color: #2c5aa0;
                margin-top: 0;
                margin-bottom: 15px;
            }
            
            .info-row {
                display: flex;
                margin-bottom: 8px;
                align-items: flex-start;
            }
            
            .label { 
                font-weight: bold; 
                min-width: 150px;
                color: #333;
            }
            
            .value { 
                color: #555;
                flex: 1;
            }
            
            .print-button {
                background-color: #2c5aa0;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                margin: 20px auto;
                display: block;
            }
            
            .print-button:hover {
                background-color: #1e3a5f;
            }
            
            .footer {
                margin-top: 40px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            
            /* Print styles */
            @media print {
                body {
                    background-color: white;
                    margin: 0;
                }
                
                .print-container {
                    box-shadow: none;
                    background-color: white;
                    padding: 0;
                    margin: 0;
                    max-width: none;
                }
                
                .print-button {
                    display: none !important;
                }
                
                .section {
                    background-color: white !important;
                    border-left: 2px solid #333;
                    break-inside: avoid;
                }
                
                .header {
                    break-inside: avoid;
                }
                
                h1, h2, h3 {
                    break-after: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-container">
            <div class="header">
                <h1>NutriTrack Health Report</h1>
                <h2>Report #RPT-' . str_pad($report_id, 4, '0', STR_PAD_LEFT) . '</h2>
            </div>
            
            <div class="section">
                <h3>Child Information</h3>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">' . htmlspecialchars($report['first_name'] . ' ' . $report['last_name']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Age:</span>
                    <span class="value">' . $age . ' years</span>
                </div>
                <div class="info-row">
                    <span class="label">Gender:</span>
                    <span class="value">' . htmlspecialchars($report['gender']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Zone:</span>
                    <span class="value">' . htmlspecialchars($report['zone_name'] ?: 'N/A') . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Birth Date:</span>
                    <span class="value">' . date('F j, Y', strtotime($report['birthdate'])) . '</span>
                </div>
            </div>
            
            <div class="section">
                <h3>Report Details</h3>
                <div class="info-row">
                    <span class="label">Report Type:</span>
                    <span class="value">' . htmlspecialchars($report['report_type']) . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Generated By:</span>
                    <span class="value">' . htmlspecialchars($report['generated_by_name'] ?: 'System') . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Date Generated:</span>
                    <span class="value">' . date('F j, Y g:i A', strtotime($report['report_date'])) . '</span>
                </div>
            </div>
            
            <div class="section">
                <h3>Report Summary</h3>
                <p>This ' . htmlspecialchars($report['report_type']) . ' has been generated for ' . htmlspecialchars($report['first_name'] . ' ' . $report['last_name']) . ' on ' . date('F j, Y', strtotime($report['report_date'])) . '.</p>
                <p>For detailed analysis and recommendations, please refer to the complete health assessment records.</p>
            </div>
            
            <div class="section">
                <h3>Contact Information</h3>
                <div class="info-row">
                    <span class="label">Generated By:</span>
                    <span class="value">' . htmlspecialchars($report['generated_by_name'] ?: 'System') . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Contact:</span>
                    <span class="value">' . htmlspecialchars($report['contact'] ?: 'N/A') . '</span>
                </div>
                <div class="info-row">
                    <span class="label">Address:</span>
                    <span class="value">' . htmlspecialchars($report['address'] ?: 'N/A') . '</span>
                </div>
            </div>
            
            <div class="footer">
                <p>Generated by NutriTrack System - ' . date('Y-m-d H:i:s') . '</p>
            </div>
            
            <button class="print-button" onclick="printReport()" id="printBtn">
                🖨️ Print Report
            </button>
        </div>

        <script>
            // Print function
            function printReport() {
                window.print();
            }
            
            // Auto print when page loads
            function autoPrint() {
                // Wait for page to fully load, then auto-print
                setTimeout(function() {
                    window.print();
                }, 500);
            }
            
            // Initialize auto-print when DOM is ready
            document.addEventListener("DOMContentLoaded", function() {
                autoPrint();
            });
            
            // Handle keyboard shortcuts
            document.addEventListener("keydown", function(e) {
                // Ctrl+P or Cmd+P to print
                if ((e.ctrlKey || e.metaKey) && e.key === "p") {
                    e.preventDefault();
                    printReport();
                }
            });
            
            // Optional: Auto-close window after printing (uncomment if needed)
            // window.addEventListener("afterprint", function() {
            //     setTimeout(function() {
            //         window.close();
            //     }, 1000);
            // });
        </script>
    </body>
    </html>
    ';
    
    // Output the HTML with auto-print functionality
    echo $html;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Error generating report: ' . $e->getMessage());
}
?>