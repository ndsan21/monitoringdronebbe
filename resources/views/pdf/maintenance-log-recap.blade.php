<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Maintenance Log Summary Report</title>
    <style>
        @page { margin: 10px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7pt; line-height: 1.3; color: #000; margin: 0; padding: 0; }
        
        .header-title { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #111827; padding-bottom: 6px; }
        .header-title h1 { margin: 0; font-size: 12pt; color: #1e3a8a; font-weight: bold; }
        .header-title p { margin: 3px 0 0 0; font-size: 8pt; font-weight: bold; color: #374151; }
        .header-title .period { font-size: 7pt; color: #4b5563; font-weight: normal; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 5px 4px; vertical-align: top; word-wrap: break-word; }
        th { background-color: #f3f4f6; font-weight: bold; text-align: center; }
        
        .col-no { width: 3%; text-align: center; }
        .col-id { width: 8%; text-align: center; font-weight: bold; }
        .col-date { width: 7.5%; text-align: center; }
        .col-drone { width: 13%; }
        .col-tech { width: 10%; }
        .col-type { width: 9%; text-align: center; }
        .col-issue { width: 18%; }
        .col-action { width: 18%; }
        .col-parts { width: 13%; }
        .col-status { width: 10.5%; text-align: center; }

        .status-ready { color: #047857; font-weight: bold; }
        .status-grounded { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header-title">
        <h1>DRONE MAINTENANCE & REPAIR REPORT RECAPITULATION</h1>
        <p>PT BUKIT BAIDURI ENERGI - LOGDRONE OPERATIONAL SYSTEM</p>
        <div class="period">Report Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-id">Doc No / ID</th>
                <th class="col-date">Maint. Date</th>
                <th class="col-drone">Drone Asset Unit</th>
                <th class="col-tech">Technician / PIC</th>
                <th class="col-type">Maint. Type</th>
                <th class="col-issue">Issue Description / Chronology</th>
                <th class="col-action">Corrective Action & Calibration</th>
                <th class="col-parts">Replaced Part</th>
                <th class="col-status">Airworthiness Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $record)
                @php
                    $items = $record->hardwareItems ?? $record->hardware_items ?? [];
                    if (is_string($items)) { $items = json_decode($items, true); }

                    $replacedParts = [];
                    $chronologies = [];
                    $notes = [];

                    if (!empty($items) && (is_array($items) || is_object($items))) {
                        foreach ($items as $item) {
                            $compVal = data_get($item, 'component') ?? data_get($item, 'component_id') ?? data_get($item, 'asset_id');
                            $compName = null;
                            if (!empty($compVal)) {
                                if (is_numeric($compVal)) {
                                    $fetchedAsset = \App\Models\Asset::find($compVal);
                                    $compName = $fetchedAsset ? ($fetchedAsset->asset_name ?? $fetchedAsset->name) : null;
                                } else {
                                    $compName = $compVal;
                                }
                            }
                            $compName = $compName ?? 'Item';

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

                            $cond = strtolower(data_get($item, 'condition', ''));
                            $chrono = data_get($item, 'chronologyDetails') ?? data_get($item, 'chronology_details') ?? data_get($item, 'chronology') ?? '';
                            if (!empty($chrono)) { $chronologies[] = $chrono; }
                            
                            $nt = data_get($item, 'note') ?? data_get($item, 'notes') ?? '';
                            if (!empty($nt)) { $notes[] = $nt; }

                            if (str_contains($cond, 'damage') || str_contains($cond, 'replace') || $cond === 'out of service') {
                                $compStr = strtoupper(str_replace('_', ' ', $compName));
                                if (!empty($replaceWithName) && !str_contains(strtolower($replaceWithName), 'select')) {
                                    $partStr = strtoupper(str_replace('_', ' ', $replaceWithName));
                                    $replacedParts[] = $compStr . ' &rarr; ' . $partStr;
                                } else {
                                    $replacedParts[] = $compStr . ' (DAMAGED)';
                                }
                            }
                        }
                    }

                    $issueText = !empty($chronologies) ? implode('; ', $chronologies) : ($record->issue_description ?? $record->description ?? '-');
                    $actionText = !empty($notes) ? implode('; ', $notes) : ($record->action_taken ?? $record->notes ?? '-');
                    $partText = !empty($replacedParts) ? implode('<br>', $replacedParts) : '-';

                    $dbStatus = strtolower($record->status ?? 'completed');
                    $isReady = in_array($dbStatus, ['ready', 'completed', 'safe', 'safe_to_fly']);
                @endphp
                <tr>
                    <td class="col-no" style="text-align: center;">{{ $index + 1 }}</td>
                    <td class="col-id">{{ $record->maintenance_number ?? 'MAIN-'.$record->id }}</td>
                    <td class="col-date">{{ $record->created_at ? $record->created_at->format('d/m/Y') : '-' }}</td>
                    <td class="col-drone">
                        <strong>{{ $record->asset->asset_name ?? '-' }}</strong><br>
                        <span style="color: #4b5563; font-size:6.5pt;">S/N: {{ $record->asset->serial_number ?? '-' }}</span>
                    </td>
                    <td class="col-tech">{{ $record->technician->name ?? $record->technician_name ?? '-' }}</td>
                    <td class="col-type" style="text-transform: uppercase; font-weight: bold;">{{ str_replace('_', ' ', $record->maintenance_type ?? 'Routine') }}</td>
                    <td class="col-issue">{{ $issueText }}</td>
                    <td class="col-action">{{ $actionText }}</td>
                    <td class="col-parts" style="color: #b45309; font-weight: bold; font-size: 6.5pt;">{!! $partText !!}</td>
                    <td class="col-status {{ $isReady ? 'status-ready' : 'status-grounded' }}">
                        {{ $isReady ? 'READY TO FLY' : 'GROUNDED' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>