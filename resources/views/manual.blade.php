<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bread Cravers ERP System - User Manual</title>
    <style>
        :root {
            --primary: #C97C5D;
            --primary-light: #DA9B7A;
            --dark: #3E2C2C;
            --light: #FFF9F6;
            --accent: #8B4513;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
            background: var(--light);
        }
        
        .manual-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, var(--dark), var(--accent));
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .header .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .role-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid var(--primary);
        }
        
        .role-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }
        
        .role-icon {
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .role-title {
            font-size: 1.8rem;
            color: var(--dark);
            font-weight: 600;
        }
        
        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .module-card {
            background: var(--light);
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .module-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .module-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .steps-list {
            list-style: none;
            margin-top: 1rem;
        }
        
        .steps-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .steps-list li:before {
            content: "✓";
            color: var(--primary);
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 4px solid #fdcb6e;
        }
        
        .success-box {
            background: #d1f2eb;
            border: 1px solid #a3e4d7;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 4px solid #58d68d;
        }
        
        .nav-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .nav-tab {
            padding: 1rem 2rem;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .nav-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .role-content {
            display: none;
        }
        
        .role-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .module-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-tab {
                padding: 0.8rem 1.2rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="manual-container">
        <div class="header">
            <h1>🥐 Bread Cravers ERP System</h1>
            <div class="subtitle">Complete User Manual & Operational Guide</div>
        </div>

        <div class="nav-tabs">
            <div class="nav-tab active" onclick="showRole('admin')">Administrator</div>
            <div class="nav-tab" onclick="showRole('manager')">Manager</div>
            <div class="nav-tab" onclick="showRole('finance')">Finance</div>
            <div class="nav-tab" onclick="showRole('chef')">Chef</div>
            <div class="nav-tab" onclick="showRole('sales')">Sales Staff</div>
            <div class="nav-tab" onclick="showRole('driver')">Drivers</div>
        </div>

        <!-- ADMINISTRATOR SECTION -->
        <div id="admin" class="role-content active">
            <div class="role-section">
                <div class="role-header">
                    <div class="role-icon">👑</div>
                    <div class="role-title">Administrator</div>
                </div>
                <p><strong>System Overview & Full Control Access</strong></p>
                
                <div class="module-grid">
                    <div class="module-card">
                        <div class="module-icon">👥</div>
                        <div class="module-title">User Management</div>
                        <p>Manage all system users and their permissions</p>
                        <ul class="steps-list">
                            <li>Create new user accounts</li>
                            <li>Assign roles (Admin, Manager, Chef, etc.)</li>
                            <li>Reset passwords when needed</li>
                            <li>Deactivate inactive users</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📊</div>
                        <div class="module-title">Financial Reports</div>
                        <p>Monitor overall business performance</p>
                        <ul class="steps-list">
                            <li>View sales vs deposits reports</li>
                            <li>Analyze driver financial performance</li>
                            <li>Generate profit/loss statements</li>
                            <li>Export reports to Excel/PDF</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">🍞</div>
                        <div class="module-title">Production Oversight</div>
                        <p>Monitor and approve all production activities</p>
                        <ul class="steps-list">
                            <li>Review chef production entries</li>
                            <li>Approve/reject production records</li>
                            <li>Set production targets</li>
                            <li>Monitor ingredient usage</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">🚚</div>
                        <div class="module-title">Dispatch Management</div>
                        <p>Oversee all dispatch operations</p>
                        <ul class="steps-list">
                            <li>View all driver dispatches</li>
                            <li>Monitor driver performance</li>
                            <li>Review back debt tracking</li>
                            <li>Approve shop dispatches</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">💰</div>
                        <div class="module-title">Expense Analytics</div>
                        <p>Track and analyze all business expenses</p>
                        <ul class="steps-list">
                            <li>View expense dashboard</li>
                            <li>Analyze driver expenses</li>
                            <li>Generate expense reports</li>
                            <li>Monitor budget compliance</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📈</div>
                        <div class="module-title">System Reports</div>
                        <p>Comprehensive business intelligence</p>
                        <ul class="steps-list">
                            <li>Daily production reports</li>
                            <li>Product profit analysis</li>
                            <li>Sales performance tracking</li>
                            <li>Inventory turnover reports</li>
                        </ul>
                    </div>
                </div>

                <div class="warning-box">
                    <strong>⚠️ Administrator Responsibilities:</strong>
                    <ul class="steps-list">
                        <li>Ensure data integrity across all modules</li>
                        <li>Monitor system performance and user activity</li>
                        <li>Resolve escalated issues from other roles</li>
                        <li>Backup critical data regularly</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- MANAGER SECTION -->
        <div id="manager" class="role-content">
            <div class="role-section">
                <div class="role-header">
                    <div class="role-icon">📋</div>
                    <div class="role-title">Manager</div>
                </div>
                <p><strong>Daily Operations & Team Management</strong></p>
                
                <div class="module-grid">
                    <div class="module-card">
                        <div class="module-icon">🚚</div>
                        <div class="module-title">Driver Dispatches</div>
                        <p>Manage daily driver operations and stock</p>
                        <ul class="steps-list">
                            <li>Create new driver dispatches</li>
                            <li>Record sold quantities and cash received</li>
                            <li>Track driver expenses and commission</li>
                            <li>Monitor driver back debt</li>
                        </ul>
                        <div class="success-box">
                            <strong>💡 Pro Tip:</strong> Always verify driver signatures and cash amounts before saving dispatches.
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">💰</div>
                        <div class="module-title">Sales vs Deposits Tracking</div>
                        <p>Monitor driver cash handling and banking</p>
                        <ul class="steps-list">
                            <li>Generate financial reports by driver</li>
                            <li>Identify cash shortages immediately</li>
                            <li>Track actual bank deposits vs expected</li>
                            <li>Address cash discrepancies promptly</li>
                        </ul>
                        <div class="warning-box">
                            <strong>🚨 Critical:</strong> Drivers should deposit cash daily. Any shortages must be addressed immediately.
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">✅</div>
                        <div class="module-title">Production Approvals</div>
                        <p>Review and approve chef production entries</p>
                        <ul class="steps-list">
                            <li>Check production quantities</li>
                            <li>Verify ingredient usage</li>
                            <li>Approve or request corrections</li>
                            <li>Monitor production targets</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📦</div>
                        <div class="module-title">Ingredients Management</div>
                        <p>Monitor stock levels and usage</p>
                        <ul class="steps-list">
                            <li>Check current ingredient stock</li>
                            <li>Review stock history and usage</li>
                            <li>Monitor ingredient costs</li>
                            <li>Plan for restocking</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">🏪</div>
                        <div class="module-title">Shop & Kampala Dispatches</div>
                        <p>Manage internal shop operations</p>
                        <ul class="steps-list">
                            <li>Create shop dispatches</li>
                            <li>Manage Kampala branch dispatches</li>
                            <li>Track shop stock levels</li>
                            <li>Monitor shop sales performance</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📊</div>
                        <div class="module-title">Production Reports</div>
                        <p>Analyze production efficiency</p>
                        <ul class="steps-list">
                            <li>Generate production reports</li>
                            <li>Track chef performance</li>
                            <li>Monitor product yield</li>
                            <li>Identify production issues</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- FINANCE SECTION -->
        <div id="finance" class="role-content">
            <div class="role-section">
                <div class="role-header">
                    <div class="role-icon">💰</div>
                    <div class="role-title">Finance Department</div>
                </div>
                <p><strong>Financial Operations & Cash Management</strong></p>
                
                <div class="module-grid">
                    <div class="module-card">
                        <div class="module-icon">🏦</div>
                        <div class="module-title">Bank Deposits</div>
                        <p>Record and verify all bank transactions</p>
                        <ul class="steps-list">
                            <li>Record driver bank deposits</li>
                            <li>Upload deposit receipts</li>
                            <li>Verify deposit amounts match records</li>
                            <li>Track deposit dates and amounts</li>
                        </ul>
                        <div class="warning-box">
                            <strong>📝 Important:</strong> Always record deposits on the same day they are made to maintain accurate financial records.
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">💸</div>
                        <div class="module-title">Expense Management</div>
                        <p>Track and categorize all business expenses</p>
                        <ul class="steps-list">
                            <li>Record business expenses</li>
                            <li>Categorize expenses properly</li>
                            <li>Attach expense receipts</li>
                            <li>Monitor expense budgets</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">👨‍💼</div>
                        <div class="module-title">Payroll Management</div>
                        <p>Process employee salaries and payments</p>
                        <ul class="steps-list">
                            <li>Generate payroll records</li>
                            <li>Calculate salaries and deductions</li>
                            <li>Print payslips for employees</li>
                            <li>Maintain payroll history</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📈</div>
                        <div class="module-title">Financial Overview</div>
                        <p>Monitor overall financial health</p>
                        <ul class="steps-list">
                            <li>View financial dashboard</li>
                            <li>Track income vs expenses</li>
                            <li>Monitor cash flow</li>
                            <li>Generate financial statements</li>
                        </ul>
                    </div>
                </div>

                <div class="success-box">
                    <strong>✅ Best Practices for Finance Team:</strong>
                    <ul class="steps-list">
                        <li>Reconcile bank deposits daily with driver records</li>
                        <li>Verify all expenses have proper documentation</li>
                        <li>Process payroll on time every period</li>
                        <li>Maintain accurate financial records for auditing</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CHEF SECTION -->
        <div id="chef" class="role-content">
            <div class="role-section">
                <div class="role-header">
                    <div class="role-icon">👨‍🍳</div>
                    <div class="role-title">Chef</div>
                </div>
                <p><strong>Production Management & Quality Control</strong></p>
                
                <div class="module-grid">
                    <div class="module-card">
                        <div class="module-icon">🍞</div>
                        <div class="module-title">Daily Production</div>
                        <p>Record daily baking production</p>
                        <ul class="steps-list">
                            <li>Enter quantities of each product made</li>
                            <li>Record flour and ingredient usage</li>
                            <li>Note any production issues</li>
                            <li>Submit for manager approval</li>
                        </ul>
                        <div class="success-box">
                            <strong>👨‍🍳 Chef's Tip:</strong> Record production immediately after baking while quantities are fresh in memory.
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📊</div>
                        <div class="module-title">Production History</div>
                        <p>Track your production performance</p>
                        <ul class="steps-list">
                            <li>View past production records</li>
                            <li>Monitor production trends</li>
                            <li>Track your progress against targets</li>
                            <li>Identify areas for improvement</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">🎯</div>
                        <div class="module-title">Target Monitoring</div>
                        <p>Stay on track with production goals</p>
                        <ul class="steps-list">
                            <li>View daily/weekly production targets</li>
                            <li>Monitor your performance against goals</li>
                            <li>Identify products needing attention</li>
                            <li>Plan production based on targets</li>
                        </ul>
                    </div>
                </div>

                <div class="warning-box">
                    <strong>⚠️ Quality Control Checklist:</strong>
                    <ul class="steps-list">
                        <li>Verify all quantities before submitting</li>
                        <li>Report any ingredient quality issues immediately</li>
                        <li>Note any production delays or problems</li>
                        <li>Maintain consistent product quality standards</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- SALES SECTION -->
        <div id="sales" class="role-content">
            <div class="role-section">
                <div class="role-header">
                    <div class="role-icon">🛒</div>
                    <div class="role-title">Sales Staff</div>
                </div>
                <p><strong>Shop Operations & Customer Service</strong></p>
                
                <div class="module-grid">
                    <div class="module-card">
                        <div class="module-icon">💳</div>
                        <div class="module-title">Point of Sale (POS)</div>
                        <p>Process customer sales efficiently</p>
                        <ul class="steps-list">
                            <li>Select products and quantities</li>
                            <li>Calculate totals automatically</li>
                            <li>Process cash and card payments</li>
                            <li>Print receipts for customers</li>
                        </ul>
                        <div class="success-box">
                            <strong>💡 Quick Tip:</strong> Use the search function to quickly find products during busy periods.
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📦</div>
                        <div class="module-title">Shop Stock Management</div>
                        <p>Monitor and manage shop inventory</p>
                        <ul class="steps-list">
                            <li>Check current stock levels</li>
                            <li>Identify low stock items</li>
                            <li>Request restocks from bakery</li>
                            <li>Monitor product movement</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">🏦</div>
                        <div class="module-title">Daily Banking</div>
                        <p>Record and deposit daily sales</p>
                        <ul class="steps-list">
                            <li>Record daily sales totals</li>
                            <li>Prepare bank deposit slips</li>
                            <li>Record banking transactions</li>
                            <li>Maintain cash drawer accuracy</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📊</div>
                        <div class="module-title">Sales Reports</div>
                        <p>Track shop performance</p>
                        <ul class="steps-list">
                            <li>View daily sales summaries</li>
                            <li>Monitor best-selling products</li>
                            <li>Track sales trends</li>
                            <li>Identify peak sales periods</li>
                        </ul>
                    </div>
                </div>

                <div class="warning-box">
                    <strong>🎯 Customer Service Excellence:</strong>
                    <ul class="steps-list">
                        <li>Always greet customers warmly</li>
                        <li>Maintain clean and organized shop</li>
                        <li>Handle customer complaints professionally</li>
                        <li>Keep product displays fresh and appealing</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- DRIVER SECTION -->
        <div id="driver" class="role-content">
            <div class="role-section">
                <div class="role-header">
                    <div class="role-icon">🚚</div>
                    <div class="role-title">Drivers</div>
                </div>
                <p><strong>Route Operations & Cash Management</strong></p>
                
                <div class="module-grid">
                    <div class="module-card">
                        <div class="module-icon">📦</div>
                        <div class="module-title">Daily Dispatch Process</div>
                        <p>Follow proper procedures for daily operations</p>
                        <ul class="steps-list">
                            <li>Receive assigned products from bakery</li>
                            <li>Verify quantities against dispatch form</li>
                            <li>Provide signature acknowledging receipt</li>
                            <li>Plan efficient delivery routes</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">💵</div>
                        <div class="module-title">Cash Handling Procedures</div>
                        <p>Proper management of sales collections</p>
                        <ul class="steps-list">
                            <li>Collect cash from sales throughout day</li>
                            <li>Keep cash secure at all times</li>
                            <li>Record all sales accurately</li>
                            <li>Deposit collections daily with finance</li>
                        </ul>
                        <div class="warning-box">
                            <strong>🚨 CRITICAL:</strong> You must deposit ALL cash collections (minus your commission and approved expenses) to finance EVERY DAY.
                        </div>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📝</div>
                        <div class="module-title">Expense Recording</div>
                        <p>Document all business expenses properly</p>
                        <ul class="steps-list">
                            <li>Record fuel and transport expenses</li>
                            <li>Document any other business expenses</li>
                            <li>Collect and submit receipts</li>
                            <li>Get manager approval for expenses</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">💰</div>
                        <div class="module-title">Commission Calculation</div>
                        <p>Understand your earnings structure</p>
                        <ul class="steps-list">
                            <li>Commission based on products sold</li>
                            <li>Higher commission for meeting sales targets</li>
                            <li>Automatic calculation in system</li>
                            <li>Paid from daily cash collections</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">⚡</div>
                        <div class="module-title">Daily Routine Checklist</div>
                        <p>Follow this sequence every working day</p>
                        <ul class="steps-list">
                            <li>Morning: Collect products from bakery</li>
                            <li>During day: Sell products, record sales</li>
                            <li>End of day: Return unsold stock</li>
                            <li>Before leaving: Deposit cash to finance</li>
                        </ul>
                    </div>

                    <div class="module-card">
                        <div class="module-icon">📊</div>
                        <div class="module-title">Performance Tracking</div>
                        <p>Monitor your sales and financial performance</p>
                        <ul class="steps-list">
                            <li>Check your back debt status regularly</li>
                            <li>Monitor your sales vs deposits report</li>
                            <li>Track your commission earnings</li>
                            <li>Identify areas for improvement</li>
                        </ul>
                    </div>
                </div>

                <div class="success-box">
                    <strong>✅ Driver Success Formula:</strong>
                    <ul class="steps-list">
                        <li><strong>Be Punctual:</strong> Start early, finish strong</li>
                        <li><strong>Be Honest:</strong> Accurate records build trust</li>
                        <li><strong>Be Professional:</strong> Represent Bread Cravers well</li>
                        <li><strong>Be Consistent:</strong> Daily deposits prevent problems</li>
                        <li><strong>Be Efficient:</strong> Plan routes to maximize sales</li>
                    </ul>
                </div>

                <div class="warning-box">
                    <strong>🚫 AVOID THESE COMMON MISTAKES:</strong>
                    <ul class="steps-list">
                        <li>❌ Holding cash instead of daily deposits</li>
                        <li>❌ Inaccurate sales recording</li>
                        <li>❌ Missing or incomplete expense documentation</li>
                        <li>❌ Late returns of unsold products</li>
                        <li>❌ Poor customer service</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showRole(role) {
            // Hide all role contents
            document.querySelectorAll('.role-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected role content
            document.getElementById(role).classList.add('active');
            
            // Activate selected tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>