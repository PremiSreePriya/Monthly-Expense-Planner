<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "expense_planner");

if (isset($_POST['save'])) {
    $year = $_POST['year'];
    $month = $_POST['month'];
    $fields = ['rent', 'petrol', 'provision', 'gas', 'milk', 'cable_wifi', 'eb', 'servant', 'others'];
    $values = [];
    $total = 0;

    // Collect values and calculate total
    foreach ($fields as $f) {
        $val = isset($_POST[$f]) ? floatval($_POST[$f]) : 0;
        $values[$f] = $val;
        $total += $val;
    }

    // Check if a row already exists for this month/year
    $check = $conn->query("SELECT * FROM expenses WHERE year='$year' AND month='$month'");

    if ($check === false) {
        die("Check query failed: " . $conn->error);
    }

    if ($check->num_rows > 0) {
        // Update existing record
        $updateParts = [];
        foreach ($values as $k => $v) {
            $updateParts[] = "$k = $v";
        }
        $updateParts[] = "total = $total";
        $sql = "UPDATE expenses SET " . implode(", ", $updateParts) . " WHERE year='$year' AND month='$month'";
    } else {
        // Insert new record — include all fields in correct order
        $columns = "year, month, " . implode(", ", array_keys($values)) . ", total";
        $allValues = array_map("floatval", array_values($values));
        $valuesString = "'$year', '$month', " . implode(", ", $allValues) . ", $total";

        $sql = "INSERT INTO expenses ($columns) VALUES ($valuesString)";
    }

    // Run query and report errors if any
    if (!$conn->query($sql)) {
        die("Query failed: " . $conn->error . "<br><pre>SQL: $sql</pre>");
    }
}

// Load current data (default to today)
$selectedYear = $_POST['year'] ?? date('Y');
$selectedMonth = $_POST['month'] ?? date('F');
$currentData = $conn->query("SELECT * FROM expenses WHERE year='$selectedYear' AND month='$selectedMonth'")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Monthly Expense Planner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c, #4facfe, #00f2fe);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            padding: 20px;
            margin: 0;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating Background Elements */
        .floating-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .floating-emoji {
            position: absolute;
            font-size: 4rem;
            opacity: 0.15;
            animation: float 20s infinite linear;
            user-select: none;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0px) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.15;
            }
            90% {
                opacity: 0.15;
            }
            100% {
                transform: translateY(-100px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 1400px;
            margin: auto;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1), 
                        0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        h3 {
            color: #34495e;
            font-size: 1.4rem;
            margin-bottom: 20px;
            text-align: center;
            background: linear-gradient(45deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Main layout container */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }

        /* Form styles */
        input, select {
            padding: 12px;
            width: 100%;
            margin-bottom: 12px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            outline: none;
        }

        input:focus, select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        label {
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
            color: #2c3e50;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 25px;
            position: relative;
        }

        .side-sections {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section {
            padding: 20px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .section:hover {
            transform: translateY(-5px);
        }

        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn.clear-btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .btn.clear-btn:hover {
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            border: 1px solid rgba(102, 126, 234, 0.2);
            text-align: center;
            font-size: 12px;
            transition: background 0.3s ease;
        }

        th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        td:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        #totalAmount {
            background: linear-gradient(45deg, #f093fb, #f5576c) !important;
            color: white !important;
            font-weight: bold !important;
            border: 2px solid rgba(240, 147, 251, 0.5) !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2) !important;
        }

        .compare-container {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .compare-section {
            flex: 1;
            background: rgba(255, 255, 255, 0.4);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        #comparisonResult, #viewResult {
            margin-top: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            border-left: 4px solid #667eea;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Form container glow effect */
        .form-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
            border-radius: 22px;
            z-index: -1;
            opacity: 0.1;
            animation: glow 3s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { opacity: 0.1; }
            to { opacity: 0.3; }
        }

        /* Loading animation for buttons */
        .btn.loading {
            position: relative;
            color: transparent;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success animation */
        .success-pulse {
            animation: successPulse 0.6s ease-out;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .compare-container {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
                padding: 10px;
                font-size: 13px;
            }

            h2 {
                font-size: 2rem;
            }

            h3 {
                font-size: 1.2rem;
            }

            .container {
                padding: 15px;
                margin: 10px;
            }

            .floating-emoji {
                font-size: 3rem;
            }

            input, select {
                padding: 10px;
                font-size: 13px;
                margin-bottom: 10px;
            }

            label {
                font-size: 13px;
                margin-bottom: 4px;
            }

            .form-container, .section {
                padding: 15px;
            }

            th, td {
                padding: 8px;
                font-size: 11px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
                margin: 5px;
            }

            h2 {
                font-size: 1.8rem;
            }

            input, select {
                padding: 8px;
                font-size: 12px;
            }

            .btn {
                padding: 8px 15px;
                font-size: 12px;
            }

            .floating-emoji {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-bg" id="floatingBg"></div>

    <div class="container">
        <h2>💰 Monthly Expense Planner</h2>
        
        <div class="main-layout">
            <!-- Main Form Section -->
            <div class="form-container">
                <form method="POST" id="expenseForm">
                    <label>📅 Year</label>
                    <input type="number" name="year" value="<?= $selectedYear ?>" required>
                    
                    <label>🗓️ Month</label>
                    <select name="month" id="monthSelect">
                        <?php foreach (range(1, 12) as $m): $mName = date("F", mktime(0, 0, 0, $m)); ?>
                            <option value="<?= $mName ?>" <?= ($mName == $selectedMonth) ? "selected" : "" ?>><?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>

                    <?php
                    $fields = [
                        'rent' => '🏠 Rent',
                        'petrol' => '⛽ Petrol',
                        'provision' => '🛒 Provision',
                        'gas' => '🔥 Gas',
                        'milk' => '🥛 Milk',
                        'cable_wifi' => '📶 Cable/WiFi',
                        'eb' => '💡 EB',
                        'servant' => '🧹 Servant',
                        'others' => '📦 Others'
                    ];
                    foreach ($fields as $key => $label): ?>
                        <label><?= $label ?></label>
                        <input type="number" step="0.01" name="<?= $key ?>" value="<?= $currentData[$key] ?? 0 ?>" class="expense-field">
                    <?php endforeach; ?>

                    <label><strong>💸 Total</strong></label>
                    <input type="text" id="totalAmount" readonly value="<?= $currentData['total'] ?? 0 ?>">

                    <button type="submit" name="save" class="btn" id="saveBtn">💾 Save Expense</button>
                    <button type="button" onclick="clearForm()" class="btn clear-btn">🧹 Clear Form</button>
                </form>
            </div>

            <!-- Side Sections -->
            <div class="side-sections">
                <!-- Compare Section -->
                <div class="section">
                    <h3>📊 Compare Any Two Months</h3>
                    <div class="compare-container">
                        <div class="compare-section">
                            <label>🔍 First Month</label>
                            <input type="number" id="compareYear1" placeholder="Year">
                            <select id="compareMonth1">
                                <?php foreach (range(1, 12) as $m): $mName = date("F", mktime(0, 0, 0, $m)); ?>
                                    <option value="<?= $mName ?>"><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="compare-section">
                            <label>🔍 Second Month</label>
                            <input type="number" id="compareYear2" placeholder="Year">
                            <select id="compareMonth2">
                                <?php foreach (range(1, 12) as $m): $mName = date("F", mktime(0, 0, 0, $m)); ?>
                                    <option value="<?= $mName ?>"><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button onclick="compareMonths()" class="btn">📈 Compare Months</button>
                    <div id="comparisonResult"></div>
                </div>

                <!-- View Previous Data Section -->
                <div class="section">
                    <h3>📅 View Previous Month's Data</h3>
                    <input type="number" id="viewYear" placeholder="Enter Year">
                    <select id="viewMonth">
                        <?php foreach (range(1, 12) as $m): $mName = date("F", mktime(0, 0, 0, $m)); ?>
                            <option value="<?= $mName ?>"><?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="viewPreviousData()" class="btn">👁️ View Data</button>
                    <div id="viewResult"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Create floating background emojis
        function createFloatingEmojis() {
            const emojis = ['💰', '💸', '🏠', '⛽', '🛒', '🔥', '🥛', '📶', '💡', '🧹', '📦', '💳', '📊', '📈', '💲', '🏦', '💼', '📱', '🖥️', '⚡', '💎', '🎯', '📋', '💵', '🔢'];
            const container = document.getElementById('floatingBg');
            
            function addEmoji() {
                const emoji = document.createElement('div');
                emoji.className = 'floating-emoji';
                emoji.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                emoji.style.left = Math.random() * 100 + 'vw';
                emoji.style.animationDuration = (Math.random() * 10 + 15) + 's';
                emoji.style.animationDelay = Math.random() * 5 + 's';
                container.appendChild(emoji);
                
                // Remove emoji after animation
                setTimeout(() => {
                    if (emoji.parentNode) {
                        emoji.parentNode.removeChild(emoji);
                    }
                }, 25000);
            }
            
            // Add initial emojis
            for (let i = 0; i < 20; i++) {
                setTimeout(addEmoji, i * 800);
            }
            
            // Continue adding emojis
            setInterval(addEmoji, 1500);
        }

        document.addEventListener('DOMContentLoaded', function () {
            createFloatingEmojis();
            
            // Total Auto Calculation with enhanced UI
            const expenseFields = document.querySelectorAll('.expense-field');
            const totalInput = document.getElementById('totalAmount');

            function updateTotal() {
                let total = 0;
                expenseFields.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                totalInput.value = total.toFixed(2);
                
                // Add pulse effect to total when it changes
                totalInput.classList.add('success-pulse');
                setTimeout(() => {
                    totalInput.classList.remove('success-pulse');
                }, 600);
            }

            expenseFields.forEach(input => {
                input.addEventListener('input', updateTotal);
                input.addEventListener('focus', function() {
                    this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.2)';
                });
                input.addEventListener('blur', function() {
                    this.style.boxShadow = '';
                });
            });

            updateTotal(); // Initial calculation on load

            // Enhanced form submission
            document.getElementById('expenseForm').addEventListener('submit', function(e) {
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.classList.add('loading');
                saveBtn.textContent = 'Saving...';
            });

            // Compare Function with enhanced UI
            window.compareMonths = function () {
                const button = event.target;
                button.classList.add('loading');
                
                const y1 = document.getElementById("compareYear1").value;
                const m1 = document.getElementById("compareMonth1").value;
                const y2 = document.getElementById("compareYear2").value;
                const m2 = document.getElementById("compareMonth2").value;

                if (!y1 || !m1 || !y2 || !m2) {
                    alert("Please select both months and years to compare.");
                    button.classList.remove('loading');
                    return;
                }

                fetch(`compare.php?year1=${y1}&month1=${m1}&year2=${y2}&month2=${m2}`)
                    .then(res => res.json())
                    .then(data => {
                        button.classList.remove('loading');
                        
                        if (!data.first || !data.second) {
                            document.getElementById("comparisonResult").innerHTML = 
                                '<div style="padding: 15px; background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; border-radius: 8px; text-align: center;">' +
                                '❌ One or both records not found' +
                                '</div>';
                            return;
                        }

                        let html = "<table><tr><th>Field</th><th>First</th><th>Second</th><th>Change</th></tr>";
                        const fields = ['rent', 'petrol', 'provision', 'gas', 'milk', 'cable_wifi', 'eb', 'servant', 'others', 'total'];

                        fields.forEach(f => {
                            const v1 = parseFloat(data.first[f] ?? 0);
                            const v2 = parseFloat(data.second[f] ?? 0);
                            const diff = v2 - v1;
                            const sign = diff === 0 ? '' : (diff > 0 ? '↑' : '↓');
                            const changeColor = diff > 0 ? '#e74c3c' : diff < 0 ? '#27ae60' : '#34495e';
                            html += `<tr><td>${f}</td><td>${v1}</td><td>${v2}</td><td style="color: ${changeColor}; font-weight: bold;">${sign} ${Math.abs(diff).toFixed(2)}</td></tr>`;
                        });

                        html += "</table>";
                        document.getElementById("comparisonResult").innerHTML = html;
                    })
                    .catch(error => {
                        button.classList.remove('loading');
                        document.getElementById("comparisonResult").innerHTML = 
                            '<div style="padding: 15px; background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; border-radius: 8px; text-align: center;">' +
                            '❌ Error fetching comparison data' +
                            '</div>';
                    });
            }

            // View Function with enhanced UI
            window.viewPreviousData = function () {
                const button = event.target;
                button.classList.add('loading');
                
                const year = document.getElementById("viewYear").value;
                const month = document.getElementById("viewMonth").value;

                if (!year || !month) {
                    alert("Select year and month.");
                    button.classList.remove('loading');
                    return;
                }

                fetch(`compare.php?viewYear=${year}&viewMonth=${month}`)
                    .then(res => res.json())
                    .then(data => {
                        button.classList.remove('loading');
                        
                        if (!data || Object.keys(data).length === 0) {
                            document.getElementById("viewResult").innerHTML = 
                                '<div style="padding: 15px; background: linear-gradient(45deg, #f39c12, #e67e22); color: white; border-radius: 8px; text-align: center;">' +
                                '📭 No data found for selected month' +
                                '</div>';
                            return;
                        }

                        let html = "<table><tr><th>Field</th><th>Value</th></tr>";
                        for (let key in data) {
                            if (['id', 'year', 'month', 'created_at'].includes(key)) continue;
                            html += `<tr><td>${key}</td><td>${data[key]}</td></tr>`;
                        }
                        html += "</table>";
                        document.getElementById("viewResult").innerHTML = html;
                    })
                    .catch(error => {
                        button.classList.remove('loading');
                        document.getElementById("viewResult").innerHTML = 
                            '<div style="padding: 15px; background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; border-radius: 8px; text-align: center;">' +
                            '❌ Error fetching data' +
                            '</div>';
                    });
            }
        });

        function clearForm() {
            const button = event.target;
            button.classList.add('loading');
            
            setTimeout(() => {
                document.querySelectorAll('.expense-field').forEach(input => {
                    input.style.transition = 'all 0.3s ease';
                    input.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        input.value = '';
                        input.style.transform = 'scale(1)';
                    }, 150);
                });
                
                document.getElementById('totalAmount').value = '0.00';
                button.classList.remove('loading');
            }, 500);
        }
    </script>
</body>
</html>