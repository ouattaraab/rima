<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Véhicule - RIMA</title>
    <style>
        @page {
            margin: 25mm 20mm 20mm 20mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* ── Header ── */
        .header {
            padding-bottom: 14px;
            margin-bottom: 20px;
            border-bottom: 2px solid #2DB56B;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 6px;
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
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .header h1 span {
            color: #2DB56B;
        }

        .header-subtitle {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        .header-meta {
            font-size: 9px;
            color: #94a3b8;
        }

        .header-meta strong {
            color: #64748b;
        }

        /* ── Document title ── */
        .doc-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-registration {
            font-size: 12px;
            font-weight: 600;
            color: #2DB56B;
            margin-bottom: 18px;
        }

        /* ── Section ── */
        .section {
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 5px;
            margin-bottom: 8px;
            border-bottom: 1px dashed #cbd5e1;
        }

        .section-title span {
            color: #2DB56B;
            margin-right: 6px;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10px;
        }

        td.label {
            font-weight: 600;
            width: 22%;
            color: #64748b;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #f8fafc;
        }

        td.value {
            width: 28%;
            color: #0f172a;
        }

        tr {
            border-bottom: 1px solid #f1f5f9;
        }

        /* Bordure verticale entre les deux paires label/value (colonne du milieu) */
        td:nth-child(3) {
            border-left: 1px solid #e2e8f0;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
            border-radius: 10px;
        }

        .status-validated { background: #dcfce7; color: #166534; }
        .status-synchronized { background: #fef3c7; color: #92400e; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-draft { background: #f1f5f9; color: #475569; }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
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

        /* ── Accent line ── */
        .accent-line {
            width: 40px;
            height: 3px;
            background: #2DB56B;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <h1><span>RIMA</span> &middot; SODECI</h1>
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

    {{-- Document title --}}
    <div class="doc-title">Fiche véhicule</div>
    <div class="doc-registration">{{ $vehicle->registration_number ?? $vehicle->temporary_registration ?? 'N/A' }}</div>
    <div class="accent-line"></div>

    {{-- 1. Identification --}}
    <div class="section">
        <div class="section-title"><span>01</span> Identification du véhicule</div>
        <table>
            <tr>
                <td class="label">Type</td>
                <td class="value">{{ $vehicle->vehicle_type }}</td>
                <td class="label">Catégorie</td>
                <td class="value">{{ $vehicle->category }}</td>
            </tr>
            <tr>
                <td class="label">Marque</td>
                <td class="value">{{ $vehicle->brand }}</td>
                <td class="label">Modèle</td>
                <td class="value">{{ $vehicle->model }}</td>
            </tr>
            <tr>
                <td class="label">Version</td>
                <td class="value">{{ $vehicle->version ?? '-' }}</td>
                <td class="label">Couleur</td>
                <td class="value">{{ $vehicle->color }}</td>
            </tr>
            <tr>
                <td class="label">Mise en circulation</td>
                <td class="value">{{ $vehicle->commissioning_date?->format('d/m/Y') }}</td>
                <td class="label">Type de contrat</td>
                <td class="value">{{ $vehicle->contract_type }}</td>
            </tr>
        </table>
    </div>

    {{-- 2. Immatriculation --}}
    <div class="section">
        <div class="section-title"><span>02</span> Immatriculation et chassis</div>
        <table>
            <tr>
                <td class="label">Immat. définitive</td>
                <td class="value">{{ $vehicle->registration_number ?? '-' }}</td>
                <td class="label">Immat. provisoire</td>
                <td class="value">{{ $vehicle->temporary_registration ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">N. chassis</td>
                <td class="value">{{ $vehicle->chassis_number ?? '-' }}</td>
                <td class="label">Chassis lisible</td>
                <td class="value">{{ $vehicle->chassis_readable ? 'Oui' : 'Non' }}</td>
            </tr>
        </table>
    </div>

    {{-- 3. Technique --}}
    <div class="section">
        <div class="section-title"><span>03</span> Caractéristiques techniques</div>
        <table>
            <tr>
                <td class="label">Carburant</td>
                <td class="value">{{ $vehicle->fuel_type }}</td>
                <td class="label">Transmission</td>
                <td class="value">{{ $vehicle->transmission ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Cylindrée</td>
                <td class="value">{{ $vehicle->engine_displacement ?? '-' }}</td>
                <td class="label">Nombre de places</td>
                <td class="value">{{ $vehicle->seats_count }}</td>
            </tr>
            <tr>
                <td class="label">Charge utile</td>
                <td class="value">{{ $vehicle->load_capacity ? $vehicle->load_capacity . ' kg' : '-' }}</td>
                <td class="label">Kilométrage</td>
                <td class="value">{{ number_format($vehicle->mileage ?? 0, 0, ',', ' ') }} km</td>
            </tr>
        </table>
    </div>

    {{-- 4. Statut --}}
    <div class="section">
        <div class="section-title"><span>04</span> Statut et équipements</div>
        <table>
            <tr>
                <td class="label">Statut véhicule</td>
                <td class="value">{{ $vehicle->status }}</td>
                <td class="label">Structure / CI</td>
                <td class="value">{{ $vehicle->structure_ci ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Arceaux</td>
                <td class="value">{{ $vehicle->has_roll_bars === null ? '-' : ($vehicle->has_roll_bars ? 'Oui' : 'Non') }}</td>
                <td class="label">Équip. spéciaux</td>
                <td class="value">{{ $vehicle->special_equipment ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- 5. Reglementaire --}}
    <div class="section">
        <div class="section-title"><span>05</span> Données réglementaires</div>
        <table>
            <tr>
                <td class="label">Visite technique</td>
                <td class="value">{{ $vehicle->technical_inspection_date?->format('d/m/Y') }}</td>
                <td class="label">Assuré</td>
                <td class="value">{{ $vehicle->is_insured ? 'Oui' : 'Non' }}</td>
            </tr>
            @if($vehicle->is_insured)
            <tr>
                <td class="label">Compagnie</td>
                <td class="value">{{ $vehicle->insurance_company }}</td>
                <td class="label">N. police</td>
                <td class="value">{{ $vehicle->policy_number }}</td>
            </tr>
            <tr>
                <td class="label">Couverture</td>
                <td class="value">{{ $vehicle->coverage_type ?? '-' }}</td>
                <td class="label">Période</td>
                <td class="value">{{ $vehicle->insurance_start_date?->format('d/m/Y') }} — {{ $vehicle->insurance_end_date?->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- 6. Utilisateur --}}
    <div class="section">
        <div class="section-title"><span>06</span> Identification utilisateur</div>
        <table>
            <tr>
                <td class="label">Direction</td>
                <td class="value">{{ $vehicle->user_direction ?? '-' }}</td>
                <td class="label">Matricule</td>
                <td class="value">{{ $vehicle->user_matricule ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Permis de conduire</td>
                <td class="value" colspan="3">{{ $vehicle->user_driver_license ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- 7. Données financières --}}
    @if($vehicle->financing_mode)
    <div class="section">
        <div class="section-title"><span>07</span> Données financières</div>
        <table>
            <tr>
                <td class="label">Mode financement</td>
                <td class="value">{{ $vehicle->financing_mode }}</td>
                <td class="label">Banque</td>
                <td class="value">{{ $vehicle->bank_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">N. contrat</td>
                <td class="value">{{ $vehicle->contract_number ?? '-' }}</td>
                <td class="label">Mise à disposition</td>
                <td class="value">{{ $vehicle->provision_date?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            @if($vehicle->financing_mode === 'Leasing')
            <tr>
                <td class="label">Début prélèvement</td>
                <td class="value">{{ $vehicle->withdrawal_start_date?->format('d/m/Y') }}</td>
                <td class="label">Fin prélèvement</td>
                <td class="value">{{ $vehicle->withdrawal_end_date?->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- 8. Métadonnées --}}
    <div class="section">
        <div class="section-title"><span>{{ $vehicle->financing_mode ? '08' : '07' }}</span> Métadonnées de collecte</div>
        <table>
            <tr>
                <td class="label">Statut fiche</td>
                <td class="value">
                    @php
                        $fLabel = match($vehicle->form_status) {
                            'validated' => 'Validé',
                            'synchronized' => 'Synchronisé',
                            'rejected' => 'Rejeté',
                            'draft' => 'Brouillon',
                            default => $vehicle->form_status,
                        };
                    @endphp
                    <span class="status-badge status-{{ $vehicle->form_status }}">{{ $fLabel }}</span>
                </td>
                <td class="label">Revision</td>
                <td class="value">{{ $vehicle->revision }}</td>
            </tr>
            <tr>
                <td class="label">Collecté par</td>
                <td class="value">{{ $vehicle->collector?->full_name ?? '-' }}</td>
                <td class="label">Date collecte</td>
                <td class="value">{{ $vehicle->collected_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Coordonnées GPS</td>
                <td class="value">{{ $vehicle->gps_latitude }}, {{ $vehicle->gps_longitude }}</td>
                <td class="label">Précision GPS</td>
                <td class="value">{{ $vehicle->gps_accuracy ? $vehicle->gps_accuracy . ' m' : '-' }}</td>
            </tr>
            @if($vehicle->validated_by)
            <tr>
                <td class="label">Validé / Rejeté par</td>
                <td class="value">{{ $vehicle->validator?->full_name ?? '-' }}</td>
                <td class="label">Date validation</td>
                <td class="value">{{ $vehicle->validated_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @endif
            @if($vehicle->rejection_reason)
            <tr>
                <td class="label">Motif rejet</td>
                <td class="value" colspan="3" style="color: #991b1b;">{{ $vehicle->rejection_reason }} — {{ $vehicle->rejection_comment }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-bar">
            <div class="footer-left">RIMA — SODECI</div>
            <div class="footer-center">Document confidentiel</div>
            <div class="footer-right">{{ $generatedAt }}</div>
        </div>
    </div>

</body>
</html>
