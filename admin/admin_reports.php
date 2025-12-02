<?php
/**
 * Advanced Reports & Analytics Page  
 * Additional Tools Module
 * Generate comprehensive reports across all platform modules
 */

// Start session and include necessary files
session_start();
require_once 'includes/db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Advanced Reports";

// ==================== VIEW LAYER ====================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Smart Laptop Advisor Admin</title>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="source/assets/css/bootstrap.css">
    <link rel="stylesheet" href="source/assets/vendors/iconly/bold.css">
    <link rel="stylesheet" href="source/assets/vendors/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="source/assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="source/assets/vendors/apexcharts/apexcharts.css">
    <link rel="stylesheet" href="source/assets/css/app.css">
    <link rel="shortcut icon" href="source/assets/images/favicon.svg" type="image/x-icon">
</head>

<body>
    <div id="app">
        <?php include 'includes/admin_header.php'; ?>

        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <div class="page-title">
                    <div class="row">
                        <div class="col-12 col-md-6 order-md-1 order-last">
                            <h3>Comprehensive Reports & Analytics</h3>
                            <p class="text-subtitle text-muted">Generate detailed reports and analytics for all platform modules</p>
                        </div>
                        <div class="col-12 col-md-6 order-md-2 order-first">
                            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                
                <!-- Report Filters -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Report Filters & Export</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="reportType">Report Type</label>
                                            <select class="form-select" id="reportType">
                                                <option value="all">All Reports</option>
                                                <option value="sales">Sales & Revenue</option>
                                                <option value="products">Product Performance</option>
                                                <option value="customers">Customer Analytics</option>
                                                <option value="ai">AI Recommendations</option>
                                                <option value="chatbot">Chatbot Performance</option>
                                                <option value="inventory">Inventory Reports</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="dateFrom">From Date</label>
                                            <input type="date" class="form-control" id="dateFrom" value="<?= date('Y-01-01') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="dateTo">To Date</label>
                                            <input type="date" class="form-control" id="dateTo" value="<?= date('Y-12-31') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="btn-group w-100">
                                                <button type="button" class="btn btn-primary" id="generateBtn">
                                                    <i class="bi bi-search"></i> Generate
                                                </button>
                                                <button type="button" class="btn btn-success" id="exportBtn">
                                                    <i class="bi bi-download"></i> Export CSV
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon purple">
                                            <i class="iconly-boldShow"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold" id="card1-title">Total Orders</h6>
                                        <h6 class="font-extrabold mb-0" id="card1-value">-</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon blue">
                                            <i class="iconly-boldProfile"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold" id="card2-title">Total Revenue</h6>
                                        <h6 class="font-extrabold mb-0" id="card2-value">-</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon green">
                                            <i class="iconly-boldAdd-User"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold" id="card3-title">New Users</h6>
                                        <h6 class="font-extrabold mb-0" id="card3-value">-</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon red">
                                            <i class="iconly-boldBookmark"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Report Exports</h6>
                                        <h6 class="font-extrabold mb-0">567</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and detailed views -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 id="chart-title">Loading...</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-sales-revenue"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'includes/admin_footer.php'; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="source/assets/js/bootstrap.js"></script>
    <script src="source/assets/js/app.js"></script>
    <script src="source/assets/vendors/apexcharts/apexcharts.js"></script>

    <script>
        // Global chart variable
        let salesRevenueChart = null;

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize event listeners
            document.getElementById('generateBtn').addEventListener('click', generateReport);
            document.getElementById('exportBtn').addEventListener('click', exportReport);
            
            // Load initial report
            generateReport();
        });

        function generateReport() {
            const reportType = document.getElementById('reportType').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            // Show loading state
            const btn = document.getElementById('generateBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
            btn.disabled = true;

            // Update Chart Title
            const reportTypeText = document.getElementById('reportType').options[document.getElementById('reportType').selectedIndex].text;
            document.getElementById('chart-title').textContent = reportTypeText + ' Analytics';

            // Fetch report data
            fetch(`ajax/report_handler.php?action=generate&reportType=${reportType}&dateFrom=${dateFrom}&dateTo=${dateTo}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateDashboard(data);
                    } else {
                        throw new Error(data.error || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while generating the report: ' + error.message);
                    
                    // Show error in chart area
                    const chartContainer = document.querySelector("#chart-sales-revenue");
                    chartContainer.innerHTML = '<div class="alert alert-danger">Failed to load report data. Please try again.</div>';
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function updateDashboard(data) {
            console.log('Updating dashboard with data:', data);
            
            // Update Summary Cards using specific IDs
            if (data.summary) {
                document.getElementById('card1-title').textContent = data.summary.card1.title;
                document.getElementById('card1-value').textContent = data.summary.card1.value;
                
                document.getElementById('card2-title').textContent = data.summary.card2.title;
                document.getElementById('card2-value').textContent = data.summary.card2.value;
                
                document.getElementById('card3-title').textContent = data.summary.card3.title;
                document.getElementById('card3-value').textContent = data.summary.card3.value;
            }

            // Validate chart data
            if (!data.chart || !data.chart.type) {
                console.error('Invalid chart data received');
                return;
            }

            // Build chart options based on chart type
            let newChartOptions = {};
            
            if (data.chart.type === 'donut' || data.chart.type === 'pie') {
                // Donut/Pie chart configuration
                newChartOptions = {
                    series: data.chart.series || [],
                    chart: {
                        type: data.chart.type,
                        height: 350,
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    labels: data.chart.categories || [],
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return val.toFixed(1) + "%";
                        }
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center'
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 300
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
                    noData: {
                        text: 'No Data Available',
                        align: 'center',
                        verticalAlign: 'middle'
                    }
                };
            } else if (data.chart.type === 'bar') {
                // Bar chart configuration
                newChartOptions = {
                    series: data.chart.series || [],
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: false,
                        toolbar: {
                            show: true
                        },
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: data.chart.categories || [],
                        type: 'category'
                    },
                    yaxis: {
                        title: {
                            text: 'Values'
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left'
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val;
                            }
                        }
                    },
                    noData: {
                        text: 'No Data Available',
                        align: 'center',
                        verticalAlign: 'middle'
                    }
                };
            } else {
                // Line/Area chart configuration (default)
                newChartOptions = {
                    series: data.chart.series || [],
                    chart: {
                        type: data.chart.type || 'line',
                        height: 350,
                        zoom: { 
                            enabled: true,
                            type: 'x'
                        },
                        toolbar: {
                            show: true
                        },
                        animations: {
                            enabled: true,
                            speed: 800
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: data.chart.categories || [],
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false,
                            format: 'dd MMM'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Values'
                        },
                        labels: {
                            formatter: function (val) {
                                return val.toFixed(0);
                            }
                        }
                    },
                    tooltip: {
                        x: {
                            format: 'dd MMM yyyy'
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left'
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    },
                    markers: {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    },
                    noData: {
                        text: 'No Data Available',
                        align: 'center',
                        verticalAlign: 'middle'
                    }
                };
            }
            
            // Render chart
            renderChart(newChartOptions);
        }

        function renderChart(options) {
            try {
                // Destroy existing chart if it exists
                if (salesRevenueChart !== null) {
                    salesRevenueChart.destroy();
                    salesRevenueChart = null;
                }
                
                // Clear the container
                const chartContainer = document.querySelector("#chart-sales-revenue");
                chartContainer.innerHTML = '';
                
                // Small delay to ensure DOM is ready for new chart
                setTimeout(() => {
                    try {
                        salesRevenueChart = new ApexCharts(chartContainer, options);
                        salesRevenueChart.render();
                        console.log('Chart rendered successfully');
                    } catch (renderError) {
                        console.error("Chart rendering error:", renderError);
                        chartContainer.innerHTML = 
                            '<div class="alert alert-danger">Error rendering chart: ' + renderError.message + '</div>';
                    }
                }, 150);
                
            } catch (e) {
                console.error("Chart preparation error:", e);
                document.querySelector("#chart-sales-revenue").innerHTML = 
                    '<div class="alert alert-danger">Error preparing chart: ' + e.message + '</div>';
            }
        }

        function exportReport() {
            const reportType = document.getElementById('reportType').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            // Show loading state
            const btn = document.getElementById('exportBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Exporting...';
            btn.disabled = true;
            
            // Redirect to export handler
            window.location.href = `ajax/report_handler.php?action=export&reportType=${reportType}&dateFrom=${dateFrom}&dateTo=${dateTo}`;
            
            // Reset button after a delay (since page might not actually redirect if there's an error)
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
    </script>
</body>
</html>

<?php
// Close database connection
if (isset($conn)) {
    mysqli_close($conn);
}
?>