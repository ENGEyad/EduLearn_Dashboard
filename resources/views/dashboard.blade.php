{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')

{{-- ===== STAT CARDS ===== --}}
<div class="row g-3 mb-4">

  {{-- Teachers --}}
  <div class="col-xl-2 col-md-4 col-sm-6">
    <div class="stat-card" style="--card-accent:#4f46e5;">
      <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;">
        <i class="bi bi-person-badge-fill"></i>
      </div>
      <div class="stat-label">Total Teachers</div>
      <div class="stat-value" id="dashTeachers">{{ $stats['teachers'] ?? 0 }}</div>
      <div class="stat-delta delta-up">
        <i class="bi bi-arrow-up-short"></i> +2 this month
      </div>
    </div>
  </div>

  {{-- Students --}}
  <div class="col-xl-2 col-md-4 col-sm-6">
    <div class="stat-card" style="--card-accent:#10b981;">
      <div class="stat-icon" style="background:#d1fae5; color:#059669;">
        <i class="bi bi-person-lines-fill"></i>
      </div>
      <div class="stat-label">Total Students</div>
      <div class="stat-value" id="dashStudents">{{ $stats['students'] ?? 0 }}</div>
      <div class="stat-delta delta-up">
        <i class="bi bi-arrow-up-short"></i> +15 this month
      </div>
    </div>
  </div>

  {{-- Classes --}}
  <div class="col-xl-2 col-md-4 col-sm-6">
    <div class="stat-card" style="--card-accent:#8b5cf6;">
      <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;">
        <i class="bi bi-journal-text"></i>
      </div>
      <div class="stat-label">Total Classes</div>
      <div class="stat-value" id="dashClasses">{{ $stats['classes'] ?? 0 }}</div>
      <div class="stat-delta delta-up">
        <i class="bi bi-arrow-up-short"></i> +1 this month
      </div>
    </div>
  </div>

  {{-- Subjects --}}
  <div class="col-xl-2 col-md-4 col-sm-6">
    <div class="stat-card" style="--card-accent:#f59e0b;">
      <div class="stat-icon" style="background:#fef3c7; color:#d97706;">
        <i class="bi bi-journal-bookmark-fill"></i>
      </div>
      <div class="stat-label">Total Subjects</div>
      <div class="stat-value" id="dashSubjects">{{ $stats['subjects'] ?? 0 }}</div>
      <div class="stat-delta delta-neu">
        <i class="bi bi-dash"></i> No change
      </div>
    </div>
  </div>

  {{-- Attendance --}}
  <div class="col-xl-4 col-md-8 col-sm-12">
    <div class="stat-card d-flex align-items-center justify-content-between gap-3" style="--card-accent:#06b6d4;">
      <div>
        <div class="stat-icon" style="background:#cffafe; color:#0891b2;">
          <i class="bi bi-activity"></i>
        </div>
        <div class="stat-label">Daily Attendance</div>
        <div class="stat-value" id="dashAttendance">{{ $stats['attendance'] ?? 0 }}%</div>
        <div class="stat-delta delta-down">
          <i class="bi bi-arrow-down-short"></i> -1.5% from yesterday
        </div>
      </div>
      <div style="flex-shrink:0; width:90px; height:90px; position:relative;">
        <canvas id="attendanceDonut"></canvas>
        <div style="position:absolute;inset:0;display:grid;place-items:center;font-family:'Outfit',sans-serif;font-weight:800;font-size:.9rem;color:#0891b2;">
          {{ $stats['attendance'] ?? 0 }}%
        </div>
      </div>
    </div>
  </div>

</div>

{{-- ===== CHARTS ===== --}}
<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card-panel h-100">
      <div class="panel-header">
        <div>
          <div class="panel-title">Grade Distribution</div>
          <small style="color:var(--muted); font-size:.75rem;">Overall student performance</small>
        </div>
        <span style="font-size:.75rem; font-weight:600; color:var(--success); background:#d1fae5; padding:.2rem .55rem; border-radius:99px;">
          B+ Avg <i class="bi bi-arrow-up-short"></i>
        </span>
      </div>
      <canvas id="gradeChart" height="190"></canvas>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card-panel h-100">
      <div class="panel-header">
        <div>
          <div class="panel-title">Weekly Attendance Trend</div>
          <small style="color:var(--muted); font-size:.75rem;">Average across all classes</small>
        </div>
        <span style="font-size:.75rem; font-weight:600; color:var(--danger); background:#fee2e2; padding:.2rem .55rem; border-radius:99px;">
          -0.5% <i class="bi bi-arrow-down-short"></i>
        </span>
      </div>
      <canvas id="attendanceChart" height="100"></canvas>
    </div>
  </div>
</div>

{{-- ===== ACTIVITY + ALERTS ===== --}}
<div class="row g-3">

  {{-- Recent Activities --}}
  <div class="col-lg-6">
    <div class="card-panel">
      <div class="panel-header">
        <div class="panel-title">Recent Activities</div>
        <span style="font-size:.72rem; color:var(--muted-lt);">Today</span>
      </div>
      <ul class="timeline">
        <li>
          <div class="timeline-icon" style="background:#eef2ff; color:#4f46e5;"><i class="bi bi-person-plus-fill"></i></div>
          <div>
            <div class="timeline-text">3 new students enrolled</div>
            <div class="timeline-time">2 hours ago</div>
          </div>
        </li>
        <li>
          <div class="timeline-icon" style="background:#ede9fe; color:#7c3aed;"><i class="bi bi-journal-plus"></i></div>
          <div>
            <div class="timeline-text">1 class created — <strong>Grade 8 – A</strong></div>
            <div class="timeline-time">4 hours ago</div>
          </div>
        </li>
        <li>
          <div class="timeline-icon" style="background:#d1fae5; color:#059669;"><i class="bi bi-bar-chart-fill"></i></div>
          <div>
            <div class="timeline-text">Attendance report generated</div>
            <div class="timeline-time">Yesterday</div>
          </div>
        </li>
        <li>
          <div class="timeline-icon" style="background:#fef3c7; color:#d97706;"><i class="bi bi-person-badge-fill"></i></div>
          <div>
            <div class="timeline-text">New teacher <strong>Ahmed Saleh</strong> added</div>
            <div class="timeline-time">Yesterday</div>
          </div>
        </li>
      </ul>
    </div>
  </div>

  {{-- Alerts --}}
  <div class="col-lg-6">
    <div class="card-panel">
      <div class="panel-header">
        <div class="panel-title">Alerts</div>
        <span style="font-size:.72rem; font-weight:600; background:#fee2e2; color:#b91c1c; padding:.2rem .55rem; border-radius:99px;">3 active</span>
      </div>
      <div class="alert-row warn">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>2 classes have attendance below 85%</span>
      </div>
      <div class="alert-row danger">
        <i class="bi bi-person-x-fill"></i>
        <span>Teacher <strong>Jane Smith</strong> marked absent today</span>
      </div>
      <div class="alert-row info">
        <i class="bi bi-cloud-arrow-up-fill"></i>
        <span>System backup is due in 2 days</span>
      </div>
    </div>
  </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
  if (typeof window.Chart === 'undefined') return;

  // --- Grade Distribution Bar Chart ---
  const gradeCanvas = document.getElementById('gradeChart');
  if (gradeCanvas) {
    const old = Chart.getChart(gradeCanvas);
    if (old) old.destroy();
    const gradients = ['#4f46e5','#6d28d9','#0891b2','#059669','#dc2626'];
    new Chart(gradeCanvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['A', 'B', 'C', 'D', 'F'],
        datasets: [{
          data: [12, 35, 25, 9, 2],
          backgroundColor: gradients.map(c => c + '33'),
          borderColor: gradients,
          borderWidth: 2,
          borderRadius: 8,
          maxBarThickness: 40
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { font: { weight: '600' } } },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(148,163,184,.12)' },
            ticks: { stepSize: 10, color: '#94a3b8', font: { size: 11 } }
          }
        }
      }
    });
  }

  // --- Attendance Line Chart ---
  const attCanvas = document.getElementById('attendanceChart');
  if (attCanvas) {
    const old = Chart.getChart(attCanvas);
    if (old) old.destroy();
    const ctx = attCanvas.getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 200);
    grad.addColorStop(0,  'rgba(79,70,229,.22)');
    grad.addColorStop(1,  'rgba(79,70,229,.0)');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
          data: [92, 93, 91, 90, 95, 97, 94],
          borderColor: '#4f46e5',
          backgroundColor: grad,
          tension: .45,
          fill: true,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#4f46e5',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
          y: {
            min: 80, max: 100,
            grid: { color: 'rgba(148,163,184,.12)' },
            ticks: { callback: v => v + '%', color: '#94a3b8', font: { size: 11 } }
          }
        }
      }
    });
  }

  // --- Attendance Donut ---
  const donutCanvas = document.getElementById('attendanceDonut');
  if (donutCanvas) {
    const old = Chart.getChart(donutCanvas);
    if (old) old.destroy();
    const val = parseInt(donutCanvas.closest('.stat-card').querySelector('.stat-value')?.textContent) || 93;
    new Chart(donutCanvas.getContext('2d'), {
      type: 'doughnut',
      data: {
        datasets: [{
          data: [val, 100 - val],
          backgroundColor: ['#06b6d4', '#e2e8f0'],
          borderWidth: 0,
          circumference: 360,
        }]
      },
      options: {
        cutout: '75%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        animation: { animateRotate: true }
      }
    });
  }
})();
</script>
@endpush
