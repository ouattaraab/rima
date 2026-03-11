<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste Véhicules - PRIMA</title>
    <style>
        @page {
            margin: 18mm 12mm 18mm 12mm;
            size: landscape;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        /* ── Header ── */
        .header {
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 2px solid #2DB56B;
        }

        .header-top {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .header h1 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .header h1 span {
            color: #2DB56B;
        }

        .header-subtitle {
            font-size: 8px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 1px;
        }

        .header-meta {
            font-size: 8px;
            color: #94a3b8;
        }

        .header-meta strong {
            color: #64748b;
        }

        /* ── Summary bar ── */
        .summary {
            margin-bottom: 12px;
            padding: 6px 0;
            border-bottom: 1px dashed #cbd5e1;
            font-size: 9px;
            color: #64748b;
        }

        .summary strong {
            color: #0f172a;
            font-size: 10px;
        }

        .summary .filter-tag {
            display: inline-block;
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            color: #475569;
            margin-left: 2px;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f1f5f9;
            color: #475569;
            padding: 6px 5px;
            font-size: 7px;
            font-weight: 600;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1.5px solid #cbd5e1;
        }

        /* Bordures verticales entre colonnes (sauf premiere et derniere) */
        thead th + th {
            border-left: 1px solid #e2e8f0;
        }

        thead th:first-child,
        thead th:last-child {
            border-left: none;
        }

        tbody td {
            padding: 5px 5px;
            font-size: 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        /* Bordures verticales entre colonnes du body (sauf premiere et derniere) */
        tbody td + td {
            border-left: 1px solid #f1f5f9;
        }

        tbody td:first-child,
        tbody td:last-child {
            border-left: none;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        /* ── Status cells ── */
        .st-validated { color: #166534; font-weight: 600; }
        .st-synchronized { color: #92400e; font-weight: 600; }
        .st-rejected { color: #991b1b; font-weight: 600; }
        .st-draft { color: #64748b; }

        .vs-en-service { color: #166534; }
        .vs-en-reparation { color: #92400e; }
        .vs-reforme { color: #64748b; }
        .vs-cede { color: #991b1b; }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        .footer-bar {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
        }

        .footer-center {
            display: table-cell;
            text-align: center;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <h1><span>PRIMA</span> &middot; SODECI</h1>
                <div class="header-subtitle">Référentiel et Inventaire de la Mobilité et des Autos</div>
            </div>
            <div class="header-right">
                <div class="header-meta">
                    <strong>Document généré le</strong><br>
                    {{ $generatedAt }}
                </div>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        Total : <strong>{{ $total }}</strong> véhicule(s)
        @if(!empty($filters))
            &nbsp;&nbsp;|&nbsp;&nbsp;Filtres actifs :
            @foreach($filters as $key => $value)
                @if($value)
                    <span class="filter-tag">{{ $key }}: {{ is_array($value) ? implode(', ', $value) : $value }}</span>
                @endif
            @endforeach
        @endif
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Marque</th>
                <th>Modèle</th>
                <th>Immatriculation</th>
                <th>Statut véhicule</th>
                <th>Statut fiche</th>
                <th>Carburant</th>
                <th>Km</th>
                <th>Assuré</th>
                <th>Direction</th>
                <th>Collecté par</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $v)
            @php
                $fClass = match($v->form_status) {
                    'validated' => 'st-validated',
                    'synchronized' => 'st-synchronized',
                    'rejected' => 'st-rejected',
                    default => 'st-draft',
                };
                $fLabel = match($v->form_status) {
                    'validated' => 'Validé',
                    'synchronized' => 'Synchronisé',
                    'rejected' => 'Rejeté',
                    'draft' => 'Brouillon',
                    default => $v->form_status,
                };
                $vClass = match($v->status) {
                    'En service' => 'vs-en-service',
                    'En reparation' => 'vs-en-reparation',
                    'Reforme' => 'vs-reforme',
                    'Cede' => 'vs-cede',
                    default => '',
                };
            @endphp
            <tr>
                <td>{{ $v->vehicle_type }}</td>
                <td>{{ $v->category }}</td>
                <td>{{ $v->brand }}</td>
                <td>{{ $v->model }}</td>
                <td style="font-weight: 600; color: #0f172a;">{{ $v->registration_number ?? $v->temporary_registration ?? '-' }}</td>
                <td class="{{ $vClass }}">{{ $v->status }}</td>
                <td class="{{ $fClass }}">{{ $fLabel }}</td>
                <td>{{ $v->fuel_type }}</td>
                <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ number_format($v->mileage ?? 0, 0, ',', ' ') }}</td>
                <td>{{ $v->is_insured ? 'Oui' : 'Non' }}</td>
                <td>{{ $v->user_direction ?? '-' }}</td>
                <td>{{ $v->collector?->full_name ?? '-' }}</td>
                <td>{{ $v->collected_at?->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-bar">
            <div class="footer-left">PRIMA — SODECI</div>
            <div class="footer-center">Document confidentiel</div>
            <div class="footer-right">{{ $generatedAt }} &nbsp;|&nbsp; Page {PAGE_NUM} / {PAGE_COUNT}</div>
        </div>
    </div>

</body>
</html>
