<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $letter->template->nama_template }}</title>
    <style>
        @page { margin: 20mm; size: A4; }
        body {
            font-family: 'serif'; /* DomPDF lebih stabil dengan nama generic serif */
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 4px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 { margin: 0; font-size: 16pt; }
        .header h3 { margin: 5px 0; font-size: 14pt; }
        .header p { margin: 0; font-size: 10pt; }
        
        .title { text-align: center; margin-bottom: 20px; }
        .title h4 { margin: 0; font-size: 13pt; text-decoration: underline; }
        
        table.info { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        table.info td { padding: 2px 0; vertical-align: top; }
        .label { width: 120px; font-weight: bold; }
        .separator { width: 10px; }

        .content { margin-bottom: 30px; }
        
        .signature-wrapper {
            margin-top: 30px;
            float: right; /* Menggunakan float agar posisi di kanan lebih stabil */
            width: 250px;
            text-align: center;
        }
        .signature-img {
            height: 70px; /* Atur tinggi pasti */
            width: auto;
            margin: 10px 0;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PEMERINTAH KOTA CONTOH</h2>
        <h3>DINAS KOMUNIKASI DAN INFORMATIKA</h3>
        <p>Jl. Teknologi No. 123, Kota Contoh - Telp: (021) 123-4567</p>
    </div>

    <div class="title">
        <h4>{{ strtoupper($letter->template->nama_template) }}</h4>
        <p>Nomor: {{ $letter->nomor_surat }}</p>
    </div>

    <table class="info">
        <tr>
            <td class="label">Tanggal</td>
            <td class="separator">:</td>
            <td>{{ $letter->tanggal->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Perihal</td>
            <td class="separator">:</td>
            <td>{{ $letter->perihal }}</td>
        </tr>
    </table>
    
    <div class="content">
        <table class="info">
            @foreach($letter->values as $val)
            <tr>
                <td class="label">{{ $val->field->nama_field }}</td>
                <td class="separator">:</td>
                <td>{{ $val->nilai }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Bagian Tanda Tangan -->
    <div class="signature-wrapper">
        <p>Kota Contoh, {{ $letter->tanggal->format('d F Y') }}</p>
        <p>{{ $letter->creator->jabatan ?? 'Pejabat Berwenang' }}</p>
        
        @if($signatureBase64)
            <img src="{{ $signatureBase64 }}" class="signature-img">
        @else
            <div style="height: 70px;"></div> <!-- Spacer jika TTD kosong -->
        @endif
        
        <p><strong><u>{{ $letter->creator->nama_lengkap ?? $letter->creator->name }}</u></strong></p>
        <p>NIP. {{ $letter->creator->nip ?? '___________________' }}</p>
    </div>
    <div class="clear"></div>
</body>
</html>