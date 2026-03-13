<?php
require_once '../config/config.php';

// Require member access
requireMember();

// Page variables
$pageTitle = 'My Savings';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'My Savings', 'url' => '']
];
$includeCharts = true;

// Get current member
$db = Database::getInstance();
$member = getCurrentMember();

// Get all savings for current member
$savings = $db->select(
    "SELECT * FROM savings 
    WHERE member_id = ? 
    ORDER BY deposit_date DESC, created_at DESC",
    [$member['member_id']]
);
if (!is_array($savings)) $savings = [];

// Get monthly savings summary
$monthlySummary = $db->select(
    "SELECT 
        month,
        SUM(amount) as total_amount,
        COUNT(*) as transaction_count
    FROM savings 
    WHERE member_id = ?
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12",
    [$member['member_id']]
);
if (!is_array($monthlySummary)) $monthlySummary = [];

// Get savings by type
$savingsByType = $db->select(
    "SELECT 
        transaction_type,
        SUM(amount) as total_amount,
        COUNT(*) as transaction_count
    FROM savings 
    WHERE member_id = ?
    GROUP BY transaction_type",
    [$member['member_id']]
);
if (!is_array($savingsByType)) $savingsByType = [];

// Prepare data for charts
$monthLabels = array_reverse(array_column($monthlySummary, 'month'));
$monthAmounts = array_reverse(array_column($monthlySummary, 'total_amount'));

$typeLabels = [];
$typeAmounts = [];
$typeColors = [
    'deposit'    => '#6366f1',
    'withdrawal' => '#ef4444',
];
$chartColors = [];

foreach ($savingsByType as $type) {
    $typeLabels[] = ucwords(str_replace('_', ' ', $type['transaction_type']));
    $typeAmounts[] = $type['total_amount'];
    $chartColors[] = $typeColors[$type['transaction_type']] ?? '#6b7280';
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0"><?= $pageTitle ?></h2>
            <p class="text-muted">Track your savings contributions</p>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Savings</h6>
                            <h4 class="mb-0"><?= formatCurrency($member['total_savings']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-success bg-opacity-10 text-success">
                                <i class="bi bi-arrow-repeat fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Deposits</h6>
                            <h4 class="mb-0"><?= count($savings) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-info bg-opacity-10 text-info">
                                <i class="bi bi-calendar-month fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">This Month</h6>
                            <?php
                            $currentMonth = date('Y-m');
                            $thisMonthSavings = 0;
                            foreach ($monthlySummary as $summary) {
                                if ($summary['month'] === $currentMonth) {
                                    $thisMonthSavings = $summary['total_amount'];
                                    break;
                                }
                            }
                            ?>
                            <h4 class="mb-0"><?= formatCurrency($thisMonthSavings) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-graph-up fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Avg. per Month</h6>
                            <?php
                            $avgPerMonth = !empty($monthlySummary) ? 
                                array_sum(array_column($monthlySummary, 'total_amount')) / count($monthlySummary) : 0;
                            ?>
                            <h4 class="mb-0"><?= formatCurrency($avgPerMonth) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Savings Trend (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="savingsTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Savings by Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="savingsByTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Savings History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Savings History</h5>
        </div>
        <div class="card-body">
            <?php if (empty($savings)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-wallet2 display-1 text-muted"></i>
                    <h4 class="mt-3">No Savings Yet</h4>
                    <p class="text-muted">Your savings history will appear here once you make deposits.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Payment Method</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($savings as $saving): ?>
                            <tr>
                                <td><?= formatDate($saving['deposit_date']) ?></td>
                                <td>
                                    <strong class="<?= $saving['transaction_type'] === 'deposit' ? 'text-success' : 'text-danger' ?>">
                                        <?= $saving['transaction_type'] === 'deposit' ? '+' : '-' ?><?= formatCurrency($saving['amount']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php
                                    $typeColors = [
                                        'deposit'    => 'success',
                                        'withdrawal' => 'danger',
                                    ];
                                    $typeColor = $typeColors[$saving['transaction_type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $typeColor ?>"><?= ucwords(str_replace('_', ' ', $saving['transaction_type'])) ?></span>
                                </td>
                                <td><?= ucfirst(str_replace('_', ' ', $saving['transaction_mode'] ?? '-')) ?></td>
                                <td><?= htmlspecialchars($saving['remarks'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$monthLabelsJson  = json_encode($monthLabels);
$monthAmountsJson = json_encode($monthAmounts);
$typeLabelsJson   = json_encode($typeLabels);
$typeAmountsJson  = json_encode($typeAmounts);
$chartColorsJson  = json_encode($chartColors);

$customJS = <<<JS
<script>
    // Savings Trend Chart
    const savingsTrendCtx = document.getElementById('savingsTrendChart').getContext('2d');
    new Chart(savingsTrendCtx, {
        type: 'line',
        data: {
            labels: {$monthLabelsJson},
            datasets: [{
                label: 'Savings Amount',
                data: {$monthAmountsJson},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Amount: ₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString('en-IN');
                        }
                    }
                }
            }
        }
    });
    
    // Savings by Type Chart
    const savingsByTypeCtx = document.getElementById('savingsByTypeChart').getContext('2d');
    new Chart(savingsByTypeCtx, {
        type: 'doughnut',
        data: {
            labels: {$typeLabelsJson},
            datasets: [{
                data: {$typeAmountsJson},
                backgroundColor: {$chartColorsJson}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₹' + context.parsed.toLocaleString('en-IN');
                        }
                    }
                }
            }
        }
    });
</script>
JS;

include '../includes/footer.php';
?>
