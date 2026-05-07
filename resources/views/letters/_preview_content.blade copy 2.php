<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Digital</title>
    <style>
        body {
            font-family: 'serif';
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 4px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .content { margin-bottom: 30px; min-height: 300px; }
        
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .sig-space {
            height: 90px;
            vertical-align: middle;
        }
        .signature-img {
            max-height: 80px;
            max-width: 180px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin-bottom: 5px;">KOP SURAT INSTANSI</h2>
        <p style="margin:0;">Jl. Teknologi No. 123, Kota Contoh, Indonesia</p>
    </div>

    <div class="content">
        <p><strong>Nomor:</strong> {{ $letter->nomor_surat }}</p>
        <p><strong>Perihal:</strong> {{ $letter->perihal }}</p>
        <br>
        @foreach($letter->values as $val)
            <p><strong>{{ $val->field->nama_field }}:</strong> {{ $val->nilai }}</p>
        @endforeach
    </div>

    <table class="sig-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: center;">
                <p>Kota Contoh, {{ $letter->tanggal->format('d F Y') }}</p>
                <p>{{ $letter->creator->jabatan ?? 'Administrator' }}</p>
                
                <div class="sig-space">
                    @if($signatureBase64)
    <img src="{{ $signatureBase64 }}" style="max-height: 100px; width: auto;">
@else
    <p class="text-danger">(TTD Belum Diunggah)</p>
@endif
                </div>
                
                <p><strong><u>{{ $letter->creator->nama_lengkap ?? $letter->creator->name }}</u></strong></p>
                <p>NIP. {{ $letter->creator->nip ?? '___________________' }}</p>
            </td>
        </tr>
    </table>
</body>
</html>