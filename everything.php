@extends('layouts.agent')

@section('title', 'Application #' . $application->id . ' | EasyTax')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2 pt-2">
        <div>
            <a href="{{ route('agent.applications.index') }}"
                class="text-muted text-sm font-weight-bold mb-2 d-inline-block transition-hover">
                <i class="fas fa-arrow-left mr-1"></i> Back to Applications
            </a>

            @php
                $status = strtolower($application->status->value ?? 'unknown');

                $statusClass = match ($status) {
                    'completed' => 'badge-success-soft',
                    'pending' => 'badge-warning-soft',
                    'rejected' => 'badge-danger-soft',
                    'cancelled' => 'badge-secondary-soft',
                    default => 'badge-primary-soft',
                };
            @endphp

            <div class="d-flex align-items-center mt-1">
                <h1 class="h3 font-weight-bold mb-0 text-dark">
                    Application #{{ $application->id }}
                </h1>
                <span class="badge {{ $statusClass }} ml-3 px-3 py-2 text-uppercase"
                    style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    {{ $application->status->value ?? 'UNKNOWN' }}
                </span>
            </div>

            <p class="text-muted mt-2 mb-0 text-sm">
                <i class="far fa-calendar-alt mr-1"></i>
                Submitted on <span
                    class="font-weight-bold">{{ optional($application->created_at)->format('F d, Y \a\t h:i A') }}</span>
            </p>
        </div>
    </div>
@stop

@section('content')
    <div class="row">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            {{-- QUICK STATS CARDS --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-success-soft text-success mr-3">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase text-xs font-weight-bold mb-1">Total Amount</h6>
                                <h4 class="mb-0 font-weight-bold text-dark">
                                    ₹{{ number_format($application->amount ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-primary-soft text-primary mr-3">
                                <i class="fas fa-hand-holding-usd fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase text-xs font-weight-bold mb-1">Commission</h6>
                                <h4 class="mb-0 font-weight-bold text-dark">
                                    ₹{{ number_format($application->commission_amount ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    @php $paymentStatus = strtolower($application->payment_status->value ?? 'pending'); @endphp
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div
                                class="icon-box {{ $paymentStatus === 'paid' || $paymentStatus === 'success' ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning' }} mr-3">
                                <i
                                    class="fas {{ $paymentStatus === 'paid' || $paymentStatus === 'success' ? 'fa-check-double' : 'fa-hourglass-half' }} fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted text-uppercase text-xs font-weight-bold mb-1">Payment Status</h6>
                                <h4 class="mb-0 font-weight-bold text-dark text-capitalize">{{ $paymentStatus }}</h4>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>

            {{-- MAIN DETAILS CARD --}}
            <div class="card border-0 shadow-sm mb-4 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-file-invoice text-primary mr-2"></i>
                        Core Details
                    </h3>

                    {{-- THE BUTTONS --}}
                    <div class="d-flex gap-2">
                        
                       @php 
                      
                            $currentStatus = $application->status instanceof \App\Enums\ApplicationStatus 
                                ? $application->status->value 
                                : (is_string($application->status) ? $application->status : '');
                                
                            $paymentStr = $application->payment_status instanceof \App\Enums\PaymentStatus
                                ? $application->payment_status->value
                                : (is_string($application->payment_status) ? $application->payment_status : '');

                            $isPaid = in_array(strtoupper($paymentStr), ['SUCCESS', 'PAID']);
                            $confirmMsg = $isPaid 
                                ? 'Are you sure you want to cancel? Since payment is already completed, you must contact the Admin for a refund. Do you want to proceed?' 
                                : 'Are you sure you want to cancel this application?';
                        @endphp

                       {{-- 💳 RAZORPAY RETRY BUTTON --}}
                       @if(in_array(strtoupper($paymentStr), ['FAILED', 'PENDING']) && strtoupper($currentStatus) !== 'CANCELLED')
                            <form action="{{ route('applications.retryPayment', $application) }}" method="POST" class="mr-2">
                                @csrf
                                <button type="submit" class="btn btn-sm shadow-sm font-weight-bold text-white pulse-green" style="background-color: #1E9C5D; border-color: #1E9C5D;">
                                    <i class="fas fa-credit-card mr-1"></i> Pay Now to Complete
                                </button>
                            </form>
                        @endif
                        @if (strtoupper($currentStatus) !== 'CANCELLED')
                            <form id="cancelApplicationForm" action="{{ route('agent.applications.cancel', $application) }}" method="POST" class="mr-2">
                                @csrf
                                @method('PATCH')
                                <button type="button" onclick="openCancelModal('{{ addslashes($confirmMsg) }}')" class="btn btn-sm btn-outline-danger shadow-sm font-weight-bold">
                                    <i class="fas fa-times-circle mr-1"></i> Cancel Application
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-sm btn-light text-muted border shadow-sm font-weight-bold" disabled>
                                <i class="fas fa-ban mr-1"></i> Cancelled
                            </button>
                        @endif

                        <button onclick="window.print()" class="btn btn-sm btn-primary shadow-sm font-weight-bold">
                            <i class="fas fa-print mr-1"></i> Print Summary
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover mb-0 detail-table">
                        <tbody>
                            <tr>
                                <td class="text-muted text-uppercase text-xs font-weight-bold w-30 align-middle pl-4 border-top-0">
                                    Service Required</td>
                                <td class="font-weight-bold text-dark border-top-0">
                                    {{ $application->service->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted text-uppercase text-xs font-weight-bold w-30 align-middle pl-4">
                                    Payment Reference</td>
                                <td><code class="px-2 py-1 bg-light text-dark rounded border font-weight-bold">{{ $application->payment_reference ?? 'N/A' }}</code>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted text-uppercase text-xs font-weight-bold w-30 align-middle pl-4">
                                    Application ID</td>
                                <td><span class="text-muted font-weight-bold">#{{ $application->id }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- DYNAMIC FORM DATA (SMART REPEATER GRID VIEW) --}}
            <div class="card border-0 shadow-sm mb-4 rounded-lg elegant-border">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-clipboard-list text-primary mr-2"></i>
                        Client Information
                    </h3>
                    
                    {{-- EXPORT BUTTON (For Admin View) --}}
                    @if(isset($application) && !empty($application->form_data) && Route::has('admin.applications.exportSingle'))
                        <a href="{{ route('admin.applications.exportSingle', $application->id) }}" class="btn btn-sm btn-outline-success font-weight-bold shadow-sm transition-hover">
                            <i class="fas fa-file-excel mr-1"></i> Export to Excel
                        </a>
                    @endif
                </div>

                @php 
                    $rawFormData = $application->form_data ?? [];
                    // Remove sensitive internal fields
                    $formData = array_filter($rawFormData, fn($key) => !in_array($key, ['admin_username', 'admin_password']), ARRAY_FILTER_USE_KEY); 
                    
                    // 🧠 SMART REPEATER ENGINE: Handles BOTH Arrays and Claude's Flat Numbered Format
                    $regularData = [];
                    $repeaterGroups = [];
                    
                    foreach($formData as $key => $value) {
                        if (str_starts_with($key, 'director_') || str_starts_with($key, 'member_') || str_starts_with($key, 'partner_')) {
                            
                            if (is_array($value)) {
                                // Format 1: Arrays (member_name => ['Alice', 'Bob'])
                                $parts = explode('_', $key, 2);
                                $prefix = $parts[0]; 
                                $subField = $parts[1] ?? $key; 
                                
                                foreach($value as $index => $val) {
                                    $repeaterGroups[$prefix][$index][$subField] = $val;
                                }
                            } else {
                                // Format 2: Claude's Flat Numbered Format (member_1_name => 'Alice')
                                // We use regex to automatically group '1' into Box 1, '2' into Box 2
                                if (preg_match('/^([a-zA-Z]+)_(\d+)_(.+)$/', $key, $matches)) {
                                    $prefix = $matches[1]; 
                                    $index = (int)$matches[2] - 1; // Convert to 0-based array index
                                    $subField = $matches[3]; 
                                    
                                    $repeaterGroups[$prefix][$index][$subField] = $value;
                                } else {
                                    $regularData[$key] = $value; // Fallback
                                }
                            }
                        } else {
                            $regularData[$key] = $value;
                        }
                    }
                @endphp

                <div class="card-body p-4 bg-light rounded-bottom">
                    @if (count($formData))
                        
                        {{-- 1. NORMAL FLAT FIELDS --}}
                        <div class="row">
                            @foreach ($regularData as $field => $value)
                                <div class="col-md-6 mb-3">
                                    <div class="bg-white p-3 rounded-lg border shadow-sm h-100 data-box transition-hover">
                                        <span class="d-block text-muted text-uppercase text-xs font-weight-bold mb-1">
                                            {{ Str::title(str_replace('_', ' ', $field)) }}
                                        </span>
                                        <span class="text-dark font-weight-normal" style="word-break: break-word;">
                                            @if (is_array($value))
                                                {{ implode(', ', $value) }}
                                            @elseif(is_bool($value))
                                                <span class="badge {{ $value ? 'badge-success-soft' : 'badge-secondary-soft' }} px-2 py-1">
                                                    {{ $value ? 'Yes' : 'No' }}
                                                </span>
                                            @elseif(empty($value))
                                                <span class="text-muted font-italic">Not provided</span>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- 2. THE MAGIC REPEATER BLOCKS (Directors, Members, etc.) --}}
                        @if(!empty($repeaterGroups))
                            @foreach($repeaterGroups as $groupName => $items)
                                <div class="w-100 mt-4 mb-3 px-2">
                                    <h5 class="font-weight-bold text-dark border-bottom pb-2">
                                        <i class="fas fa-users text-primary mr-2"></i> {{ Str::title($groupName) }} Details
                                    </h5>
                                </div>
                                
                                <div class="row">
                                    @foreach($items as $index => $itemFields)
                                        <div class="col-12 mb-3">
                                            <div class="bg-white p-3 rounded-lg shadow-sm" style="border: 1px solid #c8eadb; border-left: 4px solid var(--brand-green, #1E9C5D);">
                                                <h6 class="font-weight-bold mb-3 text-uppercase" style="color: var(--brand-green, #1E9C5D); letter-spacing: 0.5px;">
                                                    {{ Str::title($groupName) }} {{ $index + 1 }}
                                                </h6>
                                                <div class="row">
                                                    @foreach($itemFields as $subField => $subVal)
                                                        <div class="col-md-4 col-sm-6 mb-2">
                                                            <span class="d-block text-muted text-uppercase text-xs font-weight-bold mb-1">
                                                                {{ Str::title(str_replace('_', ' ', $subField)) }}
                                                            </span>
                                                            <span class="text-dark font-weight-bold" style="font-size: 0.95rem;">
                                                                {{ $subVal ?: '—' }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif

                    @else
                        <div class="text-center text-muted py-4">
                            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm border" style="width: 70px; height: 70px;">
                                <i class="fas fa-inbox fa-2x text-secondary opacity-50"></i>
                            </div>
                            <h6 class="font-weight-bold">No Client Data</h6>
                            <p class="text-sm mb-0">No form data was captured for this application.</p>
                        </div>
                    @endif
                </div>
            </div>

          {{-- DELIVERABLES & CREDENTIALS CARD (AGENT DISPLAY) --}}
            @if(isset($application->form_data['admin_username']) || isset($application->form_data['admin_password']) || $application->getMedia('final_deliverables')->count())
            <div class="card border-0 shadow-sm mb-4 rounded-lg">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-key text-primary mr-2"></i> Deliverables & Credentials
                    </h3>
                </div>
                <div class="card-body p-4 bg-primary-soft rounded-bottom">
                    <div class="row">
                        @if(!empty($application->form_data['admin_username']))
                        <div class="col-md-6 mb-3">
                            <div class="bg-white p-3 rounded-lg border shadow-sm h-100 data-box transition-hover">
                                <span class="d-block text-muted text-uppercase text-xs font-weight-bold mb-1">GST Username</span>
                                <span class="text-dark font-weight-bold" style="font-size: 1.1rem; letter-spacing: 1px;">{{ $application->form_data['admin_username'] }}</span>
                            </div>
                        </div>
                        @endif
                        
                        @if(!empty($application->form_data['admin_password']))
                        <div class="col-md-6 mb-3">
                            <div class="bg-white p-3 rounded-lg border shadow-sm h-100 data-box transition-hover">
                                <span class="d-block text-muted text-uppercase text-xs font-weight-bold mb-1">GST Password</span>
                                <span class="text-dark font-weight-bold" style="font-size: 1.1rem;">{{ $application->form_data['admin_password'] }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if ($application->getMedia('final_deliverables')->count())
                        <h6 class="font-weight-bold text-dark mt-2 mb-3">GST Certificate</h6>
                        <div class="document-list">
                            @foreach ($application->getMedia('final_deliverables') as $doc)
                                <div class="document-item d-flex align-items-center p-3 mb-2 bg-white rounded-lg border shadow-sm">
                                    <div class="document-icon bg-light rounded d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-file-pdf text-danger fa-lg"></i>
                                    </div>
                                    <div class="document-info flex-grow-1">
                                        <div class="text-dark font-weight-bold text-sm">{{ $doc->name }}</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('agent.documents.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-light border text-primary"><i class="fas fa-eye"></i> View</a>
                                        <a href="{{ route('agent.documents.download', $doc->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Download</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif

        </div>


        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            {{-- DOCUMENTS --}}
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 border-bottom text-center">
                    <h3 class="card-title font-weight-bold text-dark w-100 float-none mb-0">
                        <i class="fas fa-paperclip text-orange mr-2"></i>
                        Attached Documents
                    </h3>
                </div>

                <div class="card-body p-4">
                    @php
                        // Filter out 'final_deliverables' so GST Certificates don't show up twice
                        $generalDocs = $application->media->where('collection_name', '!==', 'final_deliverables');
                    @endphp

                    @if ($generalDocs->count() > 0)
                        <div class="document-list">
                            @foreach ($generalDocs as $doc)
                                <div class="document-item d-flex align-items-center p-3 mb-3 bg-white rounded-lg border shadow-sm transition-hover">

                                    <div class="document-icon bg-light rounded d-flex align-items-center justify-content-center mr-3"
                                        style="width: 45px; height: 45px; flex-shrink: 0;">
                                        @php
                                            $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
                                            $icon = match ($ext) {
                                                'pdf' => 'fa-file-pdf text-danger',
                                                'jpg', 'jpeg', 'png' => 'fa-file-image text-primary',
                                                'doc', 'docx' => 'fa-file-word text-info',
                                                default => 'fa-file-alt text-secondary',
                                            };
                                        @endphp
                                        <i class="fas {{ $icon }} fa-lg"></i>
                                    </div>

                                    <div class="document-info flex-grow-1 overflow-hidden pr-2">
                                        <div class="text-dark font-weight-bold text-truncate text-sm mb-1" title="{{ $doc->name }}">
                                            {{ $doc->custom_properties['label'] ?? $doc->name }}
                                        </div>
                                        <div class="text-muted text-xs text-uppercase font-weight-bold">
                                            {{ strtoupper($ext) ?: 'FILE' }} • {{ number_format($doc->size / 1024, 1) }} KB
                                            
                                            {{-- Show which bucket it came from (e.g., partner_pan_1) --}}
                                            @if($doc->collection_name !== 'documents' && $doc->collection_name !== 'default')
                                                 • <span class="text-primary">{{ ucwords(str_replace('_', ' ', $doc->collection_name)) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- SECURE VIEW AND DOWNLOAD BUTTONS --}}
                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <a href="{{ route('agent.documents.view', $doc->id) }}" target="_blank"
                                            class="btn btn-sm btn-light border text-primary shadow-sm px-2" title="View Document">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('agent.documents.download', $doc->id) }}"
                                            class="btn btn-sm btn-primary shadow-sm px-2" title="Download Document">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                style="width: 60px; height: 60px;">
                                <i class="fas fa-folder-open fa-2x text-secondary"></i>
                            </div>
                            <h6 class="font-weight-bold">No Documents</h6>
                            <p class="text-sm mb-0">No files have been attached.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- NOTICE --}}
            <div class="card border-0 bg-primary-soft mt-4 rounded-lg">
                <div class="card-body">
                    <h5 class="font-weight-bold text-primary mb-2 text-sm text-uppercase">
                        <i class="fas fa-info-circle mr-1"></i> Agent Note
                    </h5>
                    <p class="text-primary-dark mb-0 text-sm">
                        Please verify all uploaded documents and submitted data carefully before marking this application as
                        completed.
                    </p>
                </div>
            </div>

        </div>

    </div>

    {{-- CUSTOM CANCELLATION MODAL --}}
    <div id="customCancelModal" class="custom-modal-backdrop" style="display: none;">
        <div class="custom-modal-dialog shadow-lg">
            <div class="custom-modal-content">
                <div class="custom-modal-header bg-danger-soft">
                    <h5 class="mb-0 text-danger font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Cancellation
                    </h5>
                </div>
                <div class="custom-modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="fas fa-times-circle text-danger" style="font-size: 3rem; opacity: 0.8;"></i>
                    </div>
                    <p id="cancelModalMessage" class="mb-0 font-weight-bold text-dark" style="font-size: 1.1rem;"></p>
                    <p class="text-muted text-sm mt-2">This action cannot be undone.</p>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-light border font-weight-bold" onclick="closeCancelModal()">No, Go Back</button>
                    <button type="button" class="btn btn-danger font-weight-bold shadow-sm" onclick="submitCancelForm()">Yes, Cancel It</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        :root {
            --brand-green: #1E9C5D;
            --brand-green-soft: #EDF7F4;
            --brand-slate: #2E3D4E;
            --brand-slate-soft: #f8f9fa;
        }

        /* ── TYPOGRAPHY & UTILITIES ── */
        .w-30 { width: 30%; }
        .text-xs { font-size: 0.75rem; }
        
        .text-primary { color: var(--brand-green) !important; }
        .text-primary-dark { color: #157a48 !important; } 

        /* ── PREMIUM SOFT BACKGROUNDS ── */
        .bg-primary-soft { background-color: var(--brand-green-soft) !important; }
        .bg-success-soft { background-color: var(--brand-green-soft) !important; } 
        .bg-warning-soft { background-color: #FEF3C7 !important; }
        .bg-danger-soft  { background-color: #FEE2E2 !important; }
        .bg-secondary-soft { background-color: #f1f3f4 !important; }

        /* ── PREMIUM STATUS BADGES ── */
        .badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.4rem 0.85rem !important;
            border-radius: 6px;
            border: none !important;
        }
        .badge-primary-soft, .badge-success-soft { 
            background-color: var(--brand-green-soft); 
            color: var(--brand-green); 
        }
        .badge-warning-soft { background-color: #FEF3C7; color: #D97706; }
        .badge-danger-soft  { background-color: #FEE2E2; color: #DC2626; }
        .badge-secondary-soft { background-color: #f1f3f4; color: #5f6368; }

        /* ── PREMIUM CARDS & ICONS ── */
        .card {
            border-radius: 16px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important;
            border: 1px solid #e8ecf0 !important;
        }
        .card-header {
            border-bottom: 1px solid #e8ecf0 !important;
            border-radius: 16px 16px 0 0 !important;
        }
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── DETAIL TABLES ── */
        .detail-table td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e8ecf0;
            color: #4a5568;
        }
        .detail-table tr:last-child td { border-bottom: none; }

        /* ── DATA BOXES ── */
        .data-box {
            border-left: 3px solid var(--brand-green) !important;
            border-radius: 12px !important;
        }

        /* ── DOCUMENTS ── */
        .document-item {
            border-radius: 12px !important;
            border: 1px solid #e8ecf0 !important;
        }

        /* ── HOVER TRANSITIONS ── */
        .transition-hover { transition: all 0.2s ease-in-out; }
        
        .data-box:hover, .document-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06) !important;
            border-color: #d1d5db !important;
        }
        .document-item:hover .document-action i { color: var(--brand-green); }
        .transition-color { transition: color 0.2s ease-in-out; }

        /* ── BUTTONS ── */
        .btn-primary {
            background-color: var(--brand-green);
            border-color: var(--brand-green);
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #157a48;
            border-color: #157a48;
        }

        /* ── CUSTOM MODAL ── */
        .custom-modal-backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); z-index: 1050;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(3px); 
        }
        .custom-modal-dialog {
            background: #fff; border-radius: 16px; width: 100%; max-width: 450px;
            overflow: hidden; animation: popIn 0.3s ease-out forwards;
            transform: scale(0.9); opacity: 0;
        }
        .custom-modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f3f4; }
        .custom-modal-footer { padding: 1rem 1.5rem; background: #f8f9fa; display: flex; justify-content: center; gap: 12px; }
        
        @keyframes popIn {
            to { transform: scale(1); opacity: 1; }
        }

        /* ── PRINT OPTIMIZATION ── */
        @media print {
            .header-actions, .btn { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
            .bg-primary-soft, .bg-light { background-color: transparent !important; }
            body { background-color: #fff !important; }
            .data-box { border: 1px solid #eee !important; page-break-inside: avoid; }
        }

        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .pulse-green { animation: pulse-green 2s infinite; }
    </style>
@stop

@section('js')
    <script>
        // --- Custom Cancellation Modal Logic ---
        function openCancelModal(message) {
            document.getElementById('cancelModalMessage').innerText = message;
            document.getElementById('customCancelModal').style.display = 'flex';
        }

        function closeCancelModal() {
            document.getElementById('customCancelModal').style.display = 'none';
        }

        function submitCancelForm() {
            event.target.disabled = true;
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cancelling...';
            document.getElementById('cancelApplicationForm').submit();
        }

        window.onclick = function(event) {
            var modal = document.getElementById('customCancelModal');
            if (event.target == modal) {
                closeCancelModal();
            }
        }

       
    </script>
    
    {{-- --- RAZORPAY POPUP LOGIC --- --}}
    @if(session('razorpay_order'))
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            var options = {
                "key": "{{ session('razorpay_order.key_id') }}",
                "amount": "{{ session('razorpay_order.amount') }}",
                "currency": "{{ session('razorpay_order.currency') }}",
                "name": "EasyTax",
                "description": "Application Payment Retry",
                "order_id": "{{ session('razorpay_order.order_id') }}",
                "handler": function (response) {
                    // 1. Create a hidden form when payment succeeds
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('payment.success') }}';

                    // 2. Add CSRF Token
                    var csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // 3. Add Razorpay Verification Data
                    var inputs = ['razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature'];
                    inputs.forEach(function(name) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = response[name];
                        form.appendChild(input);
                    });

                    // 4. Add Application ID
                    var appId = document.createElement('input');
                    appId.type = 'hidden';
                    appId.name = 'application_id';
                    appId.value = '{{ session('razorpay_order.application_id') }}';
                    form.appendChild(appId);

                    // 5. Submit the form to your backend
                    document.body.appendChild(form);
                    form.submit();
                },
                "prefill": {
                    "name": "{{ auth()->user()->name ?? 'Agent' }}",
                    "email": "{{ auth()->user()->email ?? '' }}",
                    "contact": "{{ auth()->user()->phone ?? '' }}"
                },
                "theme": {
                    "color": "#1E9C5D"
                }
            };
            
            // Launch the Razorpay Window
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response){
                alert("Payment Failed: " + response.error.description);
            });
            rzp1.open();
        </script>
    @endif
@stop