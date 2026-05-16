@extends('layouts.admin')

@section('title', 'Admin Dashboard | EasyTax')

@section('css')
<style>
    /* ── PAGE HEADER ── */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .page-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--slate-dark);
        margin: 0;
        letter-spacing: -0.02em;
    }
    .header-date {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-muted);
        background: var(--surface);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* ── DASHBOARD PANELS (Charts & Tables) ── */
    .dash-panel {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .dash-panel-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--ink-100);
        display: flex;
        align-items: center;
    }
    .dash-panel-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--slate-dark);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dash-panel-body {
        padding: 1.5rem;
        flex: 1;
    }
    .dash-panel-body.no-padding {
        padding: 0;
    }

    /* ── NEW FUNNEL CARDS (3x2 Layout) ── */
    .funnel-container {
        background: var(--green-light, #f0fdf4); 
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(30,156,93,0.1);
    }
    .funnel-container-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--green-dark, #166534);
        margin-bottom: 1.5rem;
    }
    .funnel-card {
        background: var(--surface, #ffffff);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        border: 1px solid var(--border, #e2e8f0);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        height: 100%;
        transition: transform 0.2s;
    }
    .funnel-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .funnel-icon {
        background: var(--green-light, #f0fdf4);
        color: var(--green, #16a34a);
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1.25rem;
    }
    .funnel-icon.orange {
        background: #fff7ed;
        color: #ea580c;
    }
    .funnel-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted, #64748b);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.3rem;
    }
    .funnel-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--slate-dark, #0f172a);
        line-height: 1;
    }

    /* ── MINI TABLES (Top Agents/Services/Recent) ── */
    .mini-table { width: 100%; border-collapse: collapse; }
    .mini-table thead th {
        background: #f8fafc; color: var(--text-muted); font-size: 0.7rem;
        text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem;
        font-weight: 700; border-bottom: 1px solid var(--border);
    }
    .mini-table tbody td {
        padding: 0.85rem 1rem; vertical-align: middle; color: var(--text);
        font-size: 0.85rem; border-bottom: 1px solid var(--ink-100);
    }
    .mini-table tbody tr:last-child td { border-bottom: none; }
    .mini-table tbody tr:hover { background: #f8fafc; }

    /* ── BADGES & TAGS ── */
    .custom-badge {
        display: inline-flex; align-items: center; padding: 0.3rem 0.75rem;
        border-radius: 50px; font-size: 0.7rem; font-weight: 700;
        letter-spacing: 0.05em; text-transform: uppercase;
    }
    .badge-success-soft   { background: #dcfce7; color: #166534; }
    .badge-info-soft      { background: #dbeafe; color: #1e40af; }
    .badge-warning-soft   { background: #fef3c7; color: #92400e; }
    .badge-danger-soft    { background: #fee2e2; color: #991b1b; }
    .badge-secondary-soft { background: #f1f5f9; color: #475569; }

    .code-tag {
        background: var(--ink-100); color: var(--slate); padding: 0.2rem 0.5rem;
        border-radius: 4px; font-family: 'Courier New', Courier, monospace;
        font-size: 0.75rem; font-weight: 700; border: 1px solid var(--border);
    }
</style>
@endsection

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Analytics Dashboard</h1>
        </div>
        <div class="d-flex align-items-center" style="gap: 1rem;">
            <div>
                <span class="custom-badge badge-info-soft" style="font-size: 0.8rem; text-transform: none; letter-spacing: normal; padding: 0.5rem 1rem;">
                    <i class="fas fa-headset mr-1"></i> Support Helpline: +91 77259 81022
                </span>
            </div>
            <div class="header-date m-0">
                <i class="far fa-calendar-alt text-primary"></i>
                {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    {{-- ═══════ KPI FUNNEL CARDS (4 Top, 3 Bottom) ═══════ --}}
    <div class="funnel-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="funnel-container-title mb-0">Application Funnels</h2>
            
            @if(in_array(request()->getHost(), ['b2b.easytax.live', 'uat.easytax.live']))
            <div class="form-group mb-0" style="width: 250px;">
                <select id="serverSelector" class="form-control font-weight-bold border-primary shadow-sm">
                    <option value="local" selected>📍 Local Data (This Server)</option>
                    <option value="b2b">☁️ B2B Server</option>
                    <option value="upwest">☁️ Upwest Server</option>
                    <option value="marketing">☁️ Marketing Server</option>
                </select>
            </div>
            @endif
        </div>
        
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="funnel-label">Total Applications</div>
                    <div class="funnel-value" id="kpi-total_applications">{{ $kpis['total_applications'] }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="funnel-label">Completed Apps</div>
                    <div class="funnel-value" id="kpi-completed_applications">{{ $kpis['completed_applications'] }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-clock"></i></div>
                    <div class="funnel-label">Pending Apps</div>
                    <div class="funnel-value" id="kpi-pending_applications">{{ $kpis['pending_applications'] }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon orange"><i class="fas fa-archive"></i></div>
                    <div class="funnel-label">Processed (Draft/Fail)</div>
                    <div class="funnel-value" id="kpi-processed_applications">{{ $kpis['processed_applications'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-users"></i></div>
                    <div class="funnel-label">Total Agents</div>
                    <div class="funnel-value" id="kpi-total_agents">{{ $kpis['total_agents'] }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-rupee-sign"></i></div>
                    <div class="funnel-label">Total Revenue</div>
                    <div class="funnel-value" id="kpi-total_revenue">₹{{ number_format($kpis['total_revenue'], 2) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-coins"></i></div>
                    <div class="funnel-label">Commission Generated</div>
                    <div class="funnel-value" id="kpi-total_commission">₹{{ number_format($kpis['total_commission'], 2) }}</div>
                </div>
            </div>

            

           <div class="col-xl-3 col-lg-6 col-md-6 mb-3 mb-xl-0">
                <div class="funnel-card">
                    <div class="funnel-icon"><i class="fas fa-users"></i></div>
                    <div class="funnel-label">Total Marketers</div>
                    <div class="funnel-value" id="kpi-total_marketers">{{ $kpis['total_marketers'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ CHARTS ═══════ --}}
    <div class="row mb-4">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h3 class="dash-panel-title"><i class="fas fa-chart-bar text-primary"></i> Applications Overview</h3>
                </div>
                <div class="dash-panel-body" style="height: 300px;">
                    <canvas id="applicationsChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h3 class="dash-panel-title"><i class="fas fa-chart-line text-success"></i> Revenue Growth</h3>
                </div>
                <div class="dash-panel-body" style="height: 300px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ Top Agents & Top Services ═══════ --}}
    <div class="row mb-4">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h3 class="dash-panel-title"><i class="fas fa-trophy text-warning"></i> Top 10 Agents</h3>
                </div>
                <div class="dash-panel-body no-padding">
                    <div class="table-responsive">
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Agent</th>
                                    <th class="text-center">Apps</th>
                                    <th class="text-right">Revenue</th>
                                    <th class="text-right">Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topAgents as $i => $agent)
                                <tr>
                                    <td class="font-weight-bold text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="font-weight-bold" style="color: var(--slate-dark);">{{ $agent->name }}</div>
                                        <div class="code-tag mt-1" style="display:inline-block;">{{ $agent->agent_code }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="custom-badge badge-info-soft">{{ $agent->applications_count }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: var(--green);">₹{{ number_format($agent->total_revenue, 2) }}</td>
                                    <td class="text-right font-weight-bold" style="color: var(--slate);">₹{{ number_format($agent->commission_earned, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h3 class="dash-panel-title"><i class="fas fa-concierge-bell text-info"></i> Top 10 Services</h3>
                </div>
                <div class="dash-panel-body no-padding">
                    <div class="table-responsive">
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Service</th>
                                    <th class="text-center">Apps</th>
                                    <th class="text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topServices as $i => $svc)
                                <tr>
                                    <td class="font-weight-bold text-muted">{{ $i + 1 }}</td>
                                    <td class="font-weight-bold" style="color: var(--slate-dark);">{{ $svc->name }}</td>
                                    <td class="text-center">
                                        <span class="custom-badge badge-info-soft">{{ $svc->applications_count }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: var(--green);">₹{{ number_format($svc->revenue, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No data available</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ Recent Applications ═══════ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <h3 class="dash-panel-title"><i class="fas fa-history text-secondary"></i> Recent Applications</h3>
                </div>
                <div class="dash-panel-body no-padding">
                    <div class="table-responsive">
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th class="pl-4">ID</th>
                                    <th>Agent</th>
                                    <th>Service</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right pr-4">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApplications as $app)
                                <tr>
                                    <td class="font-weight-bold pl-4" style="color: var(--slate-dark);">#{{ $app->id }}</td>
                                    <td class="font-weight-bold text-dark">{{ $app->agent->name ?? '—' }}</td>
                                    <td>{{ $app->service->name ?? '—' }}</td>
                                    <td class="text-right font-weight-bold" style="color: var(--green);">₹{{ number_format($app->amount, 2) }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = [
                                                'DRAFT'      => 'badge-secondary-soft',
                                                'SUBMITTED'  => 'badge-info-soft',
                                                'PROCESSING' => 'badge-warning-soft',
                                                'COMPLETED'  => 'badge-success-soft',
                                                'REJECTED'   => 'badge-danger-soft',
                                            ];
                                            $statusValue = $app->status->value ?? $app->status;
                                            $badgeClass = $statusColors[$statusValue] ?? 'badge-secondary-soft';
                                        @endphp
                                        <span class="custom-badge {{ $badgeClass }}">{{ $statusValue }}</span>
                                    </td>
                                    <td class="text-right text-muted pr-4">{{ $app->submitted_at?->format('d M Y') ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No applications yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const labels = @json($chartLabels);
    const appData = @json($chartApplications);
    const revData = @json($chartRevenue);

    const gridColor = 'rgba(226,232,240,0.6)';
    const fontFamily = "'Plus Jakarta Sans', sans-serif";

    // ── Applications Chart ──
    new Chart(document.getElementById('applicationsChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Applications',
                data: appData,
                backgroundColor: 'rgba(37,99,235,0.85)',
                borderColor: 'rgba(37,99,235,1)',
                borderWidth: 0,
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1f2a36', titleFont: { family: fontFamily }, bodyFont: { family: fontFamily } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: fontFamily, size: 11 }, color: '#7a8799' } },
                y: { beginAtZero: true, border: {display: false}, grid: { color: gridColor }, ticks: { font: { family: fontFamily, size: 11 }, color: '#7a8799', precision: 0 } }
            }
        }
    });

    // ── Revenue Chart ──
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (₹)',
                data: revData,
                borderColor: '#1E9C5D',
                backgroundColor: 'rgba(30, 156, 93, 0.1)',
                fill: true, tension: 0.4,
                pointBackgroundColor: '#1E9C5D', pointBorderColor: '#fff', pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6, borderWidth: 3,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1f2a36', titleFont: { family: fontFamily }, bodyFont: { family: fontFamily }, callbacks: { label: ctx => '₹' + ctx.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 }) } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: fontFamily, size: 11 }, color: '#7a8799' } },
                y: { beginAtZero: true, border: {display: false}, grid: { color: gridColor }, ticks: { font: { family: fontFamily, size: 11 }, color: '#7a8799', callback: v => '₹' + v.toLocaleString() } }
            }
        }
    });
</script>
<script>
let serverSelector = document.getElementById('serverSelector');
if (serverSelector) {
    serverSelector.addEventListener('change', function() {
        let server = this.value;
        let funnelCards = document.querySelectorAll('.funnel-value');
        
        if (server === 'local') {
            window.location.reload(); // Quickest way to reset to local data
            return;
        }
    
        // Add a fast loading spinner visually!
        funnelCards.forEach(card => {
            card.innerHTML = '<i class="fas fa-spinner fa-spin text-muted" style="font-size: 1.2rem;"></i>';
        });
    
        // Fetch the remote data via our proxy
        fetch(`/admin/fetch-remote-kpis?server=${server}`)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    alert('Could not load data: ' + data.error);
                    window.location.reload(); 
                    return;
                }
                
                document.getElementById('kpi-total_applications').innerText = data.total_applications;
                document.getElementById('kpi-completed_applications').innerText = data.completed_applications;
                document.getElementById('kpi-pending_applications').innerText = data.pending_applications;
                document.getElementById('kpi-processed_applications').innerText = data.processed_applications;
                document.getElementById('kpi-total_agents').innerText = data.total_agents;
                document.getElementById('kpi-total_marketers').innerText = data.total_marketers;
                
                // Auto-format Indian Rupee currency with commas and 2 decimals
                document.getElementById('kpi-total_revenue').innerText = '₹' + parseFloat(data.total_revenue).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('kpi-total_commission').innerText = '₹' + parseFloat(data.total_commission).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            })
            .catch(error => {
                alert('Failed to connect to the server.');
                console.error(error);
                window.location.reload();
            });
    });
}
</script>
@endsection

