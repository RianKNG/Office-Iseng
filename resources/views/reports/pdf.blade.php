<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Surat Digital</title>
    <style>
        @page { margin: 1cm; }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 { margin: 5px 0; }
        .header p { margin: 2px 0; }
        
        .stats {
            margin-bottom: 20px;
        }
        .stats table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .stats th {
            background-color: #f0f0f0;
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .table-laporan {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .table-laporan th {
            background-color: #333;
            color: white;
            padding: 8px;
            border: 1px solid #000;
            text-align: left;
        }
        .table-laporan td {
            padding: 6px;
            border: 1px solid #000;
        }
        .table-laporan tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
        }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN SURAT DIGITAL</h2>
        <p>PDAM TIRTA MEDAL KABUPATEN SUMEDANG</p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
        @if($search)
        <p>Pencarian: "{{ $search }}"</p>
        @endif
    </div>

    <div class="stats">
        <table>
            <tr>
                <th>Total Surat</th>
                <th>Menunggu Verifikasi</th>
                <th>Diproses</th>
                <th>Disetujui</th>
                <th>Selesai</th>
            </tr>
            <tr>
                <td><strong>{{ $stats['total'] }}</strong></td>
                <td>{{ $stats['menunggu_verifikasi'] }}</td>
                <td>{{ $stats['diproses'] }}</td>
                <td>{{ $stats['disetujui'] }}</td>
                <td>{{ $stats['selesai'] }}</td>
            </tr>
        </table>
    </div>

    <table class="table-laporan">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Nomor Surat</th>
                <th width="25%">Perihal</th>
                <th width="15%">Template</th>
                <th width="15%">Pembuat</th>
                <th width="10%">Status</th>
                <th width="10%">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($letters as $index => $letter)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $letter->nomor_surat }}</td>
                <td>{{ Str::limit($letter->perihal, 40) }}</td>
                <td>{{ $letter->template->nama_template ?? '-' }}</td>
                <td>{{ $letter->creator->nama_lengkap ?? '-' }}</td>
                <td>
                    @php
                        $badgeClass = [
                            'menunggu_verifikasi' => 'warning',
                            'diproses' => 'info',
                            'disetujui' => 'success',
                            'selesai' => 'secondary',
                            'ditolak' => 'danger'
                        ][$letter->status] ?? 'secondary';
                    @endphp
                    <span class="badge badge-{{ $badgeClass }}">
                        {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                    </span>
                </td>
                <td>{{ $letter->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>