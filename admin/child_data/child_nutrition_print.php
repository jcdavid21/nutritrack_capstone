<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Nutrition Report</title>
    <style>
        /* A4 Print Styles */
        @page {
            size: A4;
            margin: 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .report-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 0;
        }

        /* Header */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2d5a3d;
            padding-bottom: 15px;
        }

        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #2d5a3d;
            margin-bottom: 8px;
        }

        .report-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .report-date {
            font-size: 11px;
            color: #888;
        }

        /* Child Information Section */
        .child-info-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2d5a3d;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        .child-summary {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #27ae60;
        }

        .child-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2d5a3d 0%, #4a7c59 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .child-details h2 {
            font-size: 18px;
            color: #2d5a3d;
            margin-bottom: 8px;
        }

        .child-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        /* Measurement History */
        .history-section {
            margin-top: 25px;
        }

        .measurements-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .measurements-table th {
            background: #2d5a3d;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        .measurements-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        .measurements-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .measurements-table tbody tr:hover {
            background: #e8f5e8;
        }

        /* Status badges */
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
        }

        .status-normal {
            background-color: #d4edda;
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .status-underweight {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .status-overweight,
        .status-severely-underweight {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        /* Summary Stats */
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2d5a3d;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #888;
        }

        /* Print specific */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .report-container {
                max-width: none;
            }

            /* Ensure status badges print with colors */
            .status-badge {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .status-normal {
                background-color: #d4edda !important;
                color: #27ae60 !important;
                border: 1px solid #27ae60 !important;
            }

            .status-underweight {
                background-color: #fff3cd !important;
                color: #856404 !important;
                border: 1px solid #ffc107 !important;
            }

            .status-overweight,
            .status-severely-underweight {
                background-color: #f8d7da !important;
                color: #721c24 !important;
                border: 1px solid #dc3545 !important;
            }

            .child-avatar {
                background: linear-gradient(135deg, #2d5a3d 0%, #4a7c59 100%) !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .child-summary {
                background: #f8f9fa !important;
                border-left: 4px solid #27ae60 !important;
            }

            .summary-stats {
                background: #f8f9fa !important;
            }

            .measurements-table th {
                background: #2d5a3d !important;
                color: white !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        /* No records message */
        .no-records {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        /* Loading state */
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 2px solid #f3f3f3;
            border-top: 2px solid #2d5a3d;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="report-container" id="reportContainer">
        <!-- Loading state -->
        <div class="loading" id="loadingState">
            <div class="spinner"></div>
            <p>Loading child nutrition report...</p>
        </div>

        <!-- Report content (hidden initially) -->
        <div id="reportContent" style="display: none;">
            <!-- Report Header -->
            <div class="report-header">
                <div class="report-title">Child Nutrition Report</div>
                <div class="report-subtitle">Growth Monitoring & Nutritional Assessment</div>
                <div class="report-date">Generated on: <span id="reportDate"></span></div>
            </div>

            <!-- Child Information Section -->
            <div class="child-info-section">
                <div class="section-title">Child Information</div>
                
                <div class="child-summary">
                    <div class="child-avatar" id="childAvatar">--</div>
                    <div class="child-details">
                        <h2 id="childName">Child Name</h2>
                        <div class="child-meta">
                            <div class="info-item">
                                <span class="info-label">Child ID:</span>
                                <span class="info-value" id="childId">--</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Age:</span>
                                <span class="info-value" id="childAge">-- years</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Gender:</span>
                                <span class="info-value" id="childGender">--</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Birthdate:</span>
                                <span class="info-value" id="childBirthdate">--</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Zone:</span>
                                <span class="info-value" id="childZone">--</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Registered:</span>
                                <span class="info-value" id="childRegistered">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="summary-stats" id="summaryStats">
                    <div class="stat-item">
                        <div class="stat-value" id="totalRecords">0</div>
                        <div class="stat-label">Total Records</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="latestWeight">-- kg</div>
                        <div class="stat-label">Latest Weight</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="latestHeight">-- cm</div>
                        <div class="stat-label">Latest Height</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="latestBMI">--</div>
                        <div class="stat-label">Latest BMI</div>
                    </div>
                </div>
            </div>

            <!-- Measurement History Section -->
            <div class="history-section">
                <div class="section-title">Measurement History</div>
                
                <table class="measurements-table" id="measurementsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Age (Years)</th>
                            <th>Weight (kg)</th>
                            <th>Height (cm)</th>
                            <th>BMI</th>
                            <th>Nutritional Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="measurementsBody">
                        <!-- Measurements will be populated here -->
                    </tbody>
                </table>

                <div class="no-records" id="noRecordsMessage" style="display: none;">
                    No measurement records available for this child.
                </div>
            </div>

            <!-- Report Footer -->
            <div class="report-footer">
                <p>This report was generated by the Child Nutrition Monitoring System</p>
                <p>For questions or concerns, please contact your healthcare provider</p>
            </div>
        </div>
    </div>

    <script>
        // Function to populate the report with child data
        function populateReport(childData, recordsData) {
            // Hide loading state and show content
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('reportContent').style.display = 'block';

            // Set report date
            document.getElementById('reportDate').textContent = new Date().toLocaleDateString();
            
            // Child basic information
            const child = childData;
            const records = recordsData || [];
            
            // Calculate age
            const birthDate = new Date(child.birthdate);
            const age = calculateAge(birthDate);
            const initials = `${child.first_name[0]}${child.last_name[0]}`.toUpperCase();
            
            // Populate child info
            document.getElementById('childAvatar').textContent = initials;
            document.getElementById('childName').textContent = `${child.first_name} ${child.last_name}`;
            document.getElementById('childId').textContent = `#${child.child_id}`;
            document.getElementById('childAge').textContent = `${age} years`;
            document.getElementById('childGender').textContent = child.gender;
            document.getElementById('childBirthdate').textContent = birthDate.toLocaleDateString();
            document.getElementById('childZone').textContent = child.zone_name || 'N/A';
            document.getElementById('childRegistered').textContent = new Date(child.created_at).toLocaleDateString();
            
            // Summary statistics
            if (records.length > 0) {
                // Sort records by date to get the latest one
                const sortedRecords = records.sort((a, b) => new Date(b.date_recorded) - new Date(a.date_recorded));
                const latestRecord = sortedRecords[0];
                
                document.getElementById('totalRecords').textContent = records.length;
                document.getElementById('latestWeight').textContent = `${latestRecord.weight} kg`;
                document.getElementById('latestHeight').textContent = `${latestRecord.height} cm`;
                document.getElementById('latestBMI').textContent = latestRecord.bmi;
            }
            
            // Populate measurements table
            const tbody = document.getElementById('measurementsBody');
            const noRecordsMsg = document.getElementById('noRecordsMessage');
            
            if (records.length === 0) {
                document.getElementById('measurementsTable').style.display = 'none';
                noRecordsMsg.style.display = 'block';
            } else {
                noRecordsMsg.style.display = 'none';
                
                // Sort records by date (newest first)
                records.sort((a, b) => new Date(b.date_recorded) - new Date(a.date_recorded));
                
                tbody.innerHTML = records.map(record => {
                    const recordDate = new Date(record.date_recorded);
                    const recordAge = calculateAgeAtDate(birthDate, recordDate);
                    const statusClass = getStatusClass(record.status_name);
                    
                    return `
                        <tr>
                            <td>${recordDate.toLocaleDateString()}</td>
                            <td>${recordAge.toFixed(1)}</td>
                            <td>${record.weight}</td>
                            <td>${record.height}</td>
                            <td>${record.bmi}</td>
                            <td><span class="status-badge ${statusClass}">${record.status_name}</span></td>
                            <td>${record.notes || '--'}</td>
                        </tr>
                    `;
                }).join('');
            }
        }
        
        function calculateAge(birthDate) {
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age;
        }
        
        function calculateAgeAtDate(birthDate, targetDate) {
            const years = (targetDate - birthDate) / (365.25 * 24 * 60 * 60 * 1000);
            return years;
        }
        
        function getStatusClass(statusName) {
            if (!statusName) return '';
            
            const status = statusName.toLowerCase();
            if (status.includes('normal')) return 'status-normal';
            if (status.includes('underweight')) return 'status-underweight';
            if (status.includes('overweight')) return 'status-overweight';
            if (status.includes('severely')) return 'status-severely-underweight';
            return '';
        }

        // Function to be called from parent window
        window.populateReport = populateReport;

        // Handle case where data is passed via URL parameters (optional fallback)
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we have URL parameters with child data
            const urlParams = new URLSearchParams(window.location.search);
            const childId = urlParams.get('child_id');
            
            if (childId) {
                // Load child data via AJAX if child_id is provided
                loadChildDataFromServer(childId);
            } else if (window.opener && window.opener.nutritionManager) {
                // Try to get data from parent window
                const parentManager = window.opener.nutritionManager;
                if (parentManager.currentChildData && parentManager.currentRecordsData) {
                    populateReport(parentManager.currentChildData, parentManager.currentRecordsData);
                }
            }
        });

        // Function to load child data from server (fallback method)
        async function loadChildDataFromServer(childId) {
            try {
                const response = await fetch(`./child_data/get_child_details.php?child_id=${childId}`);
                const data = await response.json();
                
                if (data.child) {
                    populateReport(data.child, data.records || []);
                } else {
                    showErrorMessage('Failed to load child data');
                }
            } catch (error) {
                console.error('Error loading child data:', error);
                showErrorMessage('Error loading child data');
            }
        }

        function showErrorMessage(message) {
            document.getElementById('loadingState').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #dc3545;">
                    <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>Error</h3>
                    <p>${message}</p>
                    <button onclick="window.close()" style="margin-top: 20px; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Close Window</button>
                </div>
            `;
        }

        // Auto-print functionality
        let printCalled = false;
        function autoPrint() {
            if (!printCalled && document.getElementById('reportContent').style.display !== 'none') {
                printCalled = true;
                setTimeout(() => {
                    window.print();
                }, 1000); // Give time for content to fully render
            }
        }

        // Listen for data population to trigger auto-print
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.id === 'reportContent' && mutation.target.style.display !== 'none') {
                    autoPrint();
                }
            });
        });

        observer.observe(document.getElementById('reportContent'), {
            attributes: true,
            attributeFilter: ['style']
        });
    </script>
</body>
</html>