<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Drone Damage & Incident Official Report</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.5; color: #000; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 15pt; color: #b91c1c; font-weight: bold; }
        .header p { margin: 5px 0 0 0; font-size: 9pt; font-weight: bold; color: #4b5563; }
        .ba-number { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 25px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-info td { padding: 7px 10px; vertical-align: top; border: 1px solid #d1d5db; }
        .table-info td.label { width: 28%; font-weight: bold; background-color: #f9fafb; }

        .section-title { font-size: 10pt; font-weight: bold; color: #b91c1c; margin-top: 25px; margin-bottom: 10px; border-left: 4px solid #b91c1c; padding-left: 8px; text-transform: uppercase;}
        
        .damage-card { border: 1px solid #111827; margin-bottom: 15px; page-break-inside: avoid; }
        .damage-card-header { background-color: #fee2e2; padding: 8px; font-weight: bold; border-bottom: 1px solid #111827; color: #991b1b; }
        .damage-card-body { padding: 10px; }

        .footer-sign { margin-top: 45px; width: 100%; page-break-inside: avoid; }
        .footer-sign td { text-align: center; width: 33.3%; vertical-align: top; font-size: 9pt; }
        .space-sign { height: 60px; }
        
        .badge-danger { font-weight: bold; color: #fff; padding: 2px 6px; background-color: #ef4444; border-radius: 3px; font-size: 8.5pt; text-transform: uppercase; }
    </style>
</head>
<body>

    <div class="header">
        <h1>OFFICIAL REPORT OF DRONE DAMAGE & INCIDENT</h1>
        <p>PT BUKIT BAIDURI ENERGI - LOGDRONE OPERATIONAL SYSTEM</p>
    </div>

    <div class="ba-number">Incident Ref Number: INC-{!! str_pad($record->id, 5, '0', STR_PAD_LEFT) !!}</div>

    <div class="section-title">A. Asset & Operator Information</div>
    <table class="table-info">
        <tr>
            <td class="label">Reported Date</td>
            <td class="value">{{ $record->created_at ? $record->created_at->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Main Drone Unit</td>
            <td class="value"><strong>{{ $record->asset->asset_name ?? '-' }}</strong> (S/N: {{ $record->asset->serial_number ?? '-' }})</td>
        </tr>
        <tr>
            <td class="label">Inspecting Technician</td>
            <td class="value">{{ $record->technician->name ?? $record->technician_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Log Maintenance Type</td>
            <td class="value" style="text-transform: uppercase;">{{ str_replace('_', ' ', $record->maintenance_type ?? 'Routine') }}</td>
        </tr>
    </table>

    <div class="section-title">B. Detailed Breakdown of Damaged Components</div>

    @php
        $items = $record->hardwareItems ?? $record->hardware_items ?? [];
        if (is_string($items)) { $items = json_decode($items, true); }
        
        // Filter hanya mengambil komponen yang rusak/out of service
        $damagedItems = collect($items)->filter(function($item) {
            return in_array(strtolower(data_get($item, 'condition', '')), ['damaged_replace', 'out_of_service']);
        });
    @endphp

    @foreach($damagedItems as $index => $item)
        @php
            $compVal = data_get($item, 'component') ?? data_get($item, 'component_id') ?? data_get($item, 'asset_id');
            $compName = null;
            if (!empty($compVal) && is_numeric($compVal)) {
                $assetObj = \App\Models\Asset::find($compVal);
                $compName = $assetObj ? ($assetObj->asset_name ?? $assetObj->name) : null;
            }
            $compName = $compName ?? $compVal ?? 'Component Item';

            $severity = data_get($item, 'damage_severity', 'N/A');
            $incidentDate = data_get($item, 'oos_incident_date') ? \Carbon\Carbon::parse(data_get($item, 'oos_incident_date'))->format('d F Y') : 'N/A';
            $location = data_get($item, 'oos_location', 'N/A');
            $chronology = data_get($item, 'oos_chronology', 'No chronology details provided.');
            $note = data_get($item, 'note');
        @endphp

        <div class="damage-card">
            <div class="damage-card-header">
                #{!! $index + 1 !!} - Component Name: {!! strtoupper($compName) !!}
            </div>
            <div class="damage-card-body">
                <table style="width:100%; margin-bottom:0; border:none;">
                    <tr style="border:none;">
                        <td style="width: 25%; font-weight:bold; border:none; padding:3px 0;">Damage Severity:</td>
                        <td style="width: 75%; border:none; padding:3px 0;"><span class="badge-danger">{!! $severity !!}</span></td>
                    </tr>
                    <tr style="border:none;">
                        <td style="font-weight:bold; border:none; padding:3px 0;">Incident Date:</td>
                        <td style="border:none; padding:3px 0;">{!! $incidentDate !!}</td>
                    </tr>
                    <tr style="border:none;">
                        <td style="font-weight:bold; border:none; padding:3px 0;">Incident Location:</td>
                        <td style="border:none; padding:3px 0;">{!! $location !!}</td>
                    </tr>
                    <tr style="border:none;">
                        <td style="font-weight:bold; border:none; padding:3px 0; vertical-align:top;">Chronology & Details:</td>
                        <td style="border:none; padding:3px 0; text-align:justify; line-height:1.4;">{!! $chronology !!}</td>
                    </tr>
                    @if(!empty($note))
                    <tr style="border:none;">
                        <td style="font-weight:bold; border:none; padding:3px 0; color:#b45309;">Technician Note:</td>
                        <td style="border:none; padding:3px 0; color:#b45309; font-style:italic;">{!! $note !!}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    @endforeach

    <div class="section-title">C. Declaration & Signatures</div>
    <p style="font-size: 9.5pt; text-align: justify; line-height: 1.4;">
        By signing below, the field technician and operations team declare that the information regarding the aforementioned drone damage incident is true, accurate, and inspectable to the best of their field knowledge. Further action will be scheduled for repair or component replacements.
    </p>

    <table class="footer-sign">
        <tr>
            <td>Reported By,<br><strong>Field Technician</strong><div class="space-sign"></div><u>( {{ $record->technician->name ?? $record->technician_name ?? '..................' }} )</u></td>
            <td>Reviewed By,<br><strong>Safety / Operations</strong><div class="space-sign"></div><u>( ............................................ )</u></td>
            <td>Acknowledged By,<br><strong>PT Bukit Baiduri Energi</strong><div class="space-sign"></div><u>( ............................................ )</u></td>
        </tr>
    </table>

</body>
</html>