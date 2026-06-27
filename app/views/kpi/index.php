<div class="section-header">
    <div>
        <div class="section-title">Báo cáo KPI</div>
        <p class="section-desc">
            Theo dõi hiệu suất theo số việc, tiến độ hoàn thành và điểm đánh giá.
        </p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get"
              action="<?= e(route_url('kpi/index')) ?>"
              class="row g-3 align-items-end">

            <input type="hidden" name="route" value="kpi/index">

            <div class="col-md-3">
                <label class="form-label">Dự án</label>

                <select class="form-select" name="project_id">
                    <option value="">Tất cả dự án</option>

                    <?php foreach ($projects as $project): ?>
                        <option value="<?= e((string) $project['id']) ?>"
                            <?= (($filters['project_id'] ?? '') === (string) $project['id']) ? 'selected' : '' ?>>

                            <?= e($project['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!empty($employees)): ?>
                <div class="col-md-3">
                    <label class="form-label">Nhân viên</label>

                    <select class="form-select" name="employee_id">
                        <option value="">Tất cả nhân viên</option>

                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= e((string) $employee['id']) ?>"
                                <?= (($filters['employee_id'] ?? '') === (string) $employee['id']) ? 'selected' : '' ?>>

                                <?= e($employee['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>

                <input type="date"
                       class="form-control"
                       name="from_date"
                       value="<?= e($filters['from_date'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>

                <input type="date"
                       class="form-control"
                       name="to_date"
                       value="<?= e($filters['to_date'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    Áp dụng
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">

        <?php $rows = isset($rows) && is_array($rows) ? $rows : []; ?>

        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th class="text-center">Tổng việc</th>
                    <th class="text-center">Đã duyệt</th>
                    <th class="text-center">Đúng hạn</th>
                    <th class="text-center">Duyệt trễ</th>
                    <th class="text-center">Quá hạn</th>
                    <th class="text-center">Làm lại</th>
                    <th class="text-center">Điểm đánh giá</th>
                    <th class="text-center">% Hiệu suất KPI</th>
                    <th class="text-center">Trạng thái khen thưởng</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <div class="fw-semibold">
                            <?= e($row['name']) ?>
                        </div>

                        <div class="small text-secondary">
                            <?= e($row['email']) ?>
                        </div>
                    </td>

                    <td class="text-center"><?= e((string) $row['total_tasks']) ?></td>
                    <td class="text-center"><?= e((string) $row['approved_tasks']) ?></td>
                    <td class="text-center"><?= e((string) $row['on_time_tasks']) ?></td>
                    <td class="text-center"><?= e((string) $row['approved_late_tasks']) ?></td>
                    <td class="text-center"><?= e((string) $row['overdue_tasks']) ?></td>
                    <td class="text-center"><?= e((string) $row['redo_tasks']) ?></td>
                    <td class="text-center"><?= e((string) $row['review_points']) ?></td>

                    <?php
                        $raw = (int) $row['kpi_score'];
                        $score = max(0, min(100, $raw));

                        if ($score >= 80) {
                            $badge = 'text-bg-success';
                        } elseif ($score >= 50) {
                            $badge = 'text-bg-warning';
                        } else {
                            $badge = 'border bg-light text-dark';
                        }

                        if ($score >= 80) {
                            $rewardLabel = 'Đạt chỉ tiêu';
                            $rewardClass = 'text-bg-success';
                        } else {
                            $rewardLabel = 'Cần cải thiện';
                            $rewardClass = 'text-bg-warning';
                        }
                    ?>

                    <td class="text-center">
                        <span class="badge <?= $badge ?> p-2"
                              style="min-width:54px">

                            <?= e((string) $score) ?>%
                        </span>
                    </td>

                    <td class="text-center">
                        <span class="badge <?= $rewardClass ?> p-2">
                            <?= e($rewardLabel) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="10" class="text-muted">
                        Chưa có dữ liệu KPI.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">

        <h5 class="card-title">Biểu đồ KPI</h5>

        <p class="card-text small text-secondary">
            Biểu đồ hiển thị điểm KPI (%) theo nhân viên và các chỉ số công việc.
        </p>

        <div id="kpiChartsContainer">
            <div class="alert alert-secondary mb-0">
                Đang tải dữ liệu biểu đồ...
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(() => {

    // =========================
    // STYLE
    // =========================

    const style = document.createElement('style');

    style.textContent = `
        #kpiChartsContainer {
            width: 100%;
        }

        #kpiChartsContainer canvas.kpi-chart {
            max-height: 320px;
            height: 320px !important;
        }

        #kpiChartsContainer canvas.kpi-metrics-chart {
            max-height: 420px;
            height: 420px !important;
        }

        @media (max-width: 768px) {

            #kpiChartsContainer canvas.kpi-metrics-chart {
                height: 360px !important;
            }

            #kpiChartsContainer canvas.kpi-chart {
                height: 220px !important;
            }
        }
    `;

    document.head.appendChild(style);

    // =========================
    // API URL
    // =========================

    function dataUrl() {

        const params = new URLSearchParams(window.location.search);

        params.set('route', 'kpi/data');

        return 'index.php?' + params.toString();
    }

    // =========================
    // LOAD DATA
    // =========================

    async function loadChartData() {

        try {

            const res = await fetch(dataUrl(), {
                credentials: 'same-origin'
            });

            if (!res.ok) {
                throw new Error('Network error: ' + res.status);
            }

            const json = await res.json();

            return json.rows || [];

        } catch (err) {

            console.error('KPI data load failed', err);

            return null;
        }
    }

    // =========================
    // RENDER CHARTS
    // =========================

    function renderCharts(rows) {

        const container = document.getElementById('kpiChartsContainer');

        if (!rows || rows.length === 0) {

            container.innerHTML = `
                <div class="alert alert-secondary mb-0">
                    Chưa có dữ liệu để hiển thị biểu đồ.
                </div>
            `;

            return;
        }

        // =========================
        // HTML
        // =========================

        container.innerHTML = `
            <div style="max-width:100%; overflow-x:auto">

                <div style="display:flex; gap:1.5rem; flex-direction:column">

                    <!-- KPI CHART -->
                    <div>

                        <h6 class="mb-2">KPI (%)</h6>

                        <canvas id="kpiChart"
                                class="kpi-chart"></canvas>
                    </div>

                    <!-- METRICS CHART -->
                    <div>

                        <h6 class="mb-2">
                            Chỉ số công việc
                        </h6>

                        <!-- FILTER -->
                        <div class="mb-3 p-3 border rounded bg-light">

                            <label class="form-label fw-semibold mb-2">
                                Lọc chỉ số công việc:
                            </label>

                            <div style="
                                display:flex;
                                flex-wrap:wrap;
                                gap:1rem;
                            ">

                                <div class="form-check">
                                    <input class="form-check-input metric-filter"
                                           type="checkbox"
                                           id="metric0"
                                           value="0"
                                           checked>

                                    <label class="form-check-label"
                                           for="metric0">

                                        Tổng việc
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input metric-filter"
                                           type="checkbox"
                                           id="metric1"
                                           value="1"
                                           checked>

                                    <label class="form-check-label"
                                           for="metric1">

                                        Đã duyệt
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input metric-filter"
                                           type="checkbox"
                                           id="metric2"
                                           value="2"
                                           checked>

                                    <label class="form-check-label"
                                           for="metric2">

                                        Đúng hạn
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input metric-filter"
                                           type="checkbox"
                                           id="metric3"
                                           value="3"
                                           checked>

                                    <label class="form-check-label"
                                           for="metric3">

                                        Duyệt trễ
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input metric-filter"
                                           type="checkbox"
                                           id="metric4"
                                           value="4"
                                           checked>

                                    <label class="form-check-label"
                                           for="metric4">

                                        Quá hạn
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input metric-filter"
                                           type="checkbox"
                                           id="metric5"
                                           value="5"
                                           checked>

                                    <label class="form-check-label"
                                           for="metric5">

                                        Làm lại
                                    </label>
                                </div>
                            </div>
                        </div>

                        <canvas id="kpiMetricsChart"
                                class="kpi-metrics-chart"></canvas>
                    </div>
                </div>
            </div>
        `;

        // =========================
        // DATA
        // =========================

        const labels = rows.map(r => r.name);

        const kpiData = rows.map(r =>
            Number(r.kpi_score || 0)
        );

        const totalTasks = rows.map(r =>
            Number(r.total_tasks || 0)
        );

        const approvedTasks = rows.map(r =>
            Number(r.approved_tasks || 0)
        );

        const onTimeTasks = rows.map(r =>
            Number(r.on_time_tasks || 0)
        );

        const approvedLateTasks = rows.map(r =>
            Number(r.approved_late_tasks || 0)
        );

        const overdueTasks = rows.map(r =>
            Number(r.overdue_tasks || 0)
        );

        const redoTasks = rows.map(r =>
            Number(r.redo_tasks || 0)
        );

        // =========================
        // KPI COLORS
        // =========================

        const kpiColors = kpiData.map(v => {

            if (v >= 90) return '#0d6efd';
            if (v >= 80) return '#198754';
            if (v >= 70) return '#0dcaf0';
            if (v >= 60) return '#ffc107';
            if (v >= 50) return '#fd7e14';

            return '#6c757d';
        });

        // =========================
        // KPI CHART
        // =========================

        const ctxKpi = document
            .getElementById('kpiChart')
            .getContext('2d');

        new Chart(ctxKpi, {

            type: 'bar',

            data: {
                labels,
                datasets: [{
                    label: 'KPI %',
                    data: kpiData,
                    backgroundColor: kpiColors,
                    borderColor: kpiColors,
                    borderWidth: 2
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,

                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10
                        }
                    }
                },

                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // =========================
        // METRICS CHART
        // =========================

        const ctxM = document
            .getElementById('kpiMetricsChart')
            .getContext('2d');

        const metricsChart = new Chart(ctxM, {

            type: 'bar',

            data: {
                labels,

                datasets: [
                    {
                        label: 'Tổng việc',
                        data: totalTasks,
                        backgroundColor: '#0d6efd'
                    },
                    {
                        label: 'Đã duyệt',
                        data: approvedTasks,
                        backgroundColor: '#198754'
                    },
                    {
                        label: 'Đúng hạn',
                        data: onTimeTasks,
                        backgroundColor: '#0dcaf0'
                    },
                    {
                        label: 'Duyệt trễ',
                        data: approvedLateTasks,
                        backgroundColor: '#ffc107'
                    },
                    {
                        label: 'Quá hạn',
                        data: overdueTasks,
                        backgroundColor: '#fd7e14'
                    },
                    {
                        label: 'Làm lại',
                        data: redoTasks,
                        backgroundColor: '#6c757d'
                    }
                ]
            },

            options: {

                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 3,

                scales: {
                    x: {
                        stacked: false
                    },

                    y: {
                        beginAtZero: true,

                        ticks: {
                            stepSize: 1
                        }
                    }
                },

                plugins: {

                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },

                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // =========================
        // FILTER EVENTS
        // =========================

        document
            .querySelectorAll('.metric-filter')
            .forEach(checkbox => {

                checkbox.addEventListener('change', (e) => {

                    const idx = parseInt(e.target.value, 10);

                    metricsChart.getDatasetMeta(idx).hidden =
                        !e.target.checked;

                    metricsChart.update();
                });
            });
    }

    // =========================
    // INIT
    // =========================

    (async () => {

        const rows = await loadChartData();

        const container = document.getElementById(
            'kpiChartsContainer'
        );

        if (rows === null) {

            container.innerHTML = `
                <div class="alert alert-danger mb-0">
                    Không thể tải dữ liệu biểu đồ.
                </div>
            `;

            return;
        }

        renderCharts(rows);

    })();

})();
</script>