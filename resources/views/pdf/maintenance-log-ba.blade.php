<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Drone Maintenance Official Report</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.5; color: #000; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 15pt; color: #1e3a8a; font-weight: bold; }
        .header p { margin: 5px 0 0 0; font-size: 9pt; font-weight: bold; color: #4b5563; }
        .ba-number { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 25px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-info td { padding: 7px 10px; vertical-align: top; border: 1px solid #d1d5db; }
        .table-info td.label { width: 28%; font-weight: bold; background-color: #f9fafb; }

        .section-title { font-size: 10pt; font-weight: bold; color: #1e3a8a; margin-top: 25px; margin-bottom: 10px; border-left: 4px solid #1e3a8a; padding-left: 8px; text-transform: uppercase;}
        
        .table-grid th, .table-grid td { border: 1px solid #111827; padding: 8px; text-align: left; }
        .table-grid th { background-color: #f3f4f6; text-align: center; font-weight: bold; }

        .footer-sign { margin-top: 45px; width: 100%; page-break-inside: avoid; }
        .footer-sign td { text-align: center; width: 33.3%; vertical-align: top; font-size: 9pt; }
        .space-sign { height: 60px; }
        .page-break { page-break-after: always; }
        .badge { font-weight: bold; color: #fff; padding: 3px 8px; background-color: #10b981; border-radius: 3px; }
        .badge-danger { background-color: #ef4444; }
        
        .text-success { color: #047857; font-weight: bold; }
        .text-danger { color: #b91c1c; font-weight: bold; }
        .text-warning { color: #d97706; font-weight: bold; }
    </style>
</head>
<body>

@foreach($records as $record)
    <div class="header">
        <h1>DRONE MAINTENANCE & REPAIR OFFICIAL REPORT</h1>
        <p>PT BUKIT BAIDURI ENERGI - LOGDRONE OPERATIONAL SYSTEM</p>
    </div>

    <div class="ba-number">Document Number: {{ $record->maintenance_number ?? 'MAIN-'.str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</div>

    <div class="section-title">A. Asset Information & Maintenance Schedule</div>
    <table class="table-info">
        <tr>
            <td class="label">Maintenance Date</td>
            <td class="value">{{ $record->created_at ? $record->created_at->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Drone Unit / Asset</td>
            <td class="value"><strong>{{ $record->asset->asset_name ?? '-' }}</strong> (S/N: {{ $record->asset->serial_number ?? '-' }})</td>
        </tr>
        <tr>
            <td class="label">Technician / PIC</td>
            <td class="value">{{ $record->technician->name ?? $record->technician_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Maintenance Type</td>
            <td class="value" style="text-transform: uppercase; font-weight: bold; color: #1e3a8a;">{{ str_replace('_', ' ', $record->maintenance_type ?? 'Routine') }}</td>
        </tr>
    </table>

    <div class="section-title">B. Component Inspection & Spare Parts Results</div>
    <table class="table-grid">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">No</th>
                <th style="width: 34%;">Component Item Name</th>
                <th style="width: 25%; text-align: center;">Inspection Condition</th>
                <th style="width: 35%;">Chronology / Spare Part Recommendation</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = $record->hardwareItems ?? $record->hardware_items ?? [];
                if (is_string($items)) { $items = json_decode($items, true); }
            @endphp

            @if(!empty($items) && (is_array($items) || is_object($items)))
                @foreach($items as $itemIndex => $item)
                    @php
                        $compVal = data_get($item, 'component') ?? data_get($item, 'component_id') ?? data_get($item, 'asset_id');
                        $componentName = null;

                        if (!empty($compVal)) {
                            if (is_numeric($compVal)) {
                                $assetObj = \App\Models\Asset::find($compVal);
                                $componentName = $assetObj ? ($assetObj->asset_name ?? $assetObj->name) : null;
                            } else {
                                $componentName = $compVal;
                            }
                        }
                        $componentName = $componentName ?? 'Component Item #' . ($itemIndex + 1);

                        $replaceVal = data_get($item, 'replaceWithPart') ?? data_get($item, 'replace_with_part') ?? data_get($item, 'replace_part');
                        $replaceWithName = '';
                        if (!empty($replaceVal)) {
                            if (is_numeric($replaceVal)) {
                                $repAsset = \App\Models\Asset::find($replaceVal);
                                $replaceWithName = $repAsset ? ($repAsset->asset_name ?? $repAsset->name) : '';
                            } else {
                                $replaceWithName = $replaceVal;
                            }
                        }

                        $condition  = strtolower(data_get($item, 'condition', 'good'));
                        $chronology = data_get($item, 'chronologyDetails') ?? data_get($item, 'chronology_details') ?? data_get($item, 'chronology') ?? '';
                        $note       = data_get($item, 'note') ?? data_get($item, 'notes') ?? '';
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $itemIndex + 1 }}</td>
                        <td style="font-weight: bold;">{{ strtoupper(str_replace('_', ' ', $componentName)) }}</td>
                        <td style="text-align: center;">
                            @if(str_contains($condition, 'damage') || str_contains($condition, 'replace'))
                                <span class="text-danger">[X] DAMAGED / REPLACE</span>
                            @elseif($condition === 'out of service')
                                <span class="text-warning">[!] OUT OF SERVICE</span>
                            @else
                                <span class="text-success">[V] GOOD / OK</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($chronology)) <div><strong>Chronology:</strong> {{ $chronology }}</div> @endif
                            @if(!empty($note)) <div><strong>Note:</strong> {{ $note }}</div> @endif
                            @if(str_contains($condition, 'damage') || str_contains($condition, 'replace') || $condition === 'out of service')
                                <div style="color:#b91c1c; margin-top:5px; font-size:8.5pt; font-weight:bold;">
                                    @if(!empty($replaceWithName) && !str_contains(strtolower($replaceWithName), 'select'))
                                        &raquo; Replaced with: <span style="background-color: #fee2e2; padding: 1px 4px; border: 1px solid #fca5a5;">{{ strtoupper(str_replace('_', ' ', $replaceWithName)) }}</span>
                                    @else
                                        &raquo; Action Required: <span style="background-color: #fee2e2; padding: 1px 4px; border: 1px solid #fca5a5;">NEEDS REPLACEMENT</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color:#6b7280; font-style:italic;">No hardware items found.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="table-info">
        <tr>
            <td class="label" style="width: 28%;">Final Airworthiness Status</td>
            <td class="value" style="width: 72%;">
                @php
                    $status = strtolower($record->status ?? 'completed');
                    $isReady = in_array($status, ['ready', 'completed', 'safe', 'safe_to_fly']);
                @endphp
                <span class="badge {{ $isReady ? '' : 'badge-danger' }}">
                    {{ $isReady ? 'READY TO FLY' : 'GROUNDED / FURTHER REPAIR REQUIRED' }}
                </span>
            </td>
        </tr>
    </table>

    <table class="footer-sign">
        <tr>
            <td>Prepared By,<br><strong>Field Technician</strong><div class="space-sign"></div><u>( {{ $record->technician->name ?? $record->technician_name ?? '..................' }} )</u></td>
            <td>Reviewed By,<br><strong>Operations Supervisor</strong><div class="space-sign"></div><u>( ............................................ )</u></td>
            <td>Approved By,<br><strong>PT Bukit Baiduri Energi</strong><div class="space-sign"></div><u>( ............................................ )</u></td>
        </tr>
    </table>

    @if(!$loop->last) <div class="page-break"></div> @endif
@endforeach
</body>
</html>