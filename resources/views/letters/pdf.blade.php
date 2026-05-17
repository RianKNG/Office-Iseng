<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Digital - PDAM Sumedang</title>
    <style>
        @page { 
            margin: 1.5cm 2cm;
            margin-top: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        /* Header */
        .header {
            position: relative;
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .header-line-2 {
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
            height: 1px;
        }
        .logo-left { position: absolute; left: 0; top: 0; width: 60px; }
        .logo-right { position: absolute; right: 0; top: 0; width: 60px; }
        .header h3 { margin: 0; font-size: 12pt; font-weight: normal; line-height: 1.2; }
        .header h2 { margin: 0; font-size: 14pt; font-weight: bold; line-height: 1.2; }
        .header p { margin: 1px 0; font-size: 8pt; line-height: 1.2; }

        /* Content */
        .content { margin-top: 5px; padding: 0; }
        .date-place { text-align: right; margin-bottom: 15px; line-height: 1.8; }
        
        /* Info Table */
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { vertical-align: top; padding: 1px 0; line-height: 1.4; }
        
        /* Isi Surat */
        .invitation-text { 
            text-align: justify; 
            margin-bottom: 15px; 
            text-indent: 40px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        /* Detail Fields */
        .details-table { margin-left: 40px; margin-bottom: 15px; width: auto; }
        .details-table td { padding: 2px 5px; vertical-align: top; line-height: 1.4; }
        
        /* Penutup */
        .closing-text { margin-bottom: 20px; text-align: justify; }
        
        /* Tanda Tangan */
        .signature-table { 
            width: 100%; 
            margin-top: 20px; 
            border-collapse: collapse; 
            page-break-inside: avoid;
        }
        .signature-table td { 
            width: 50%; 
            vertical-align: top; 
            text-align: center; 
            padding: 5px; 
        }
        .signature-space { height: 70px; position: relative; margin: 10px 0; }
        .signature-img {
            max-height: 60px;
            max-width: 120px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .signature-label { font-weight: bold; margin-bottom: 5px; line-height: 1.3; font-size: 10pt; }
        .signature-name { font-weight: bold; text-decoration: underline; margin: 5px 0 2px 0; font-size: 11pt; }
        .signature-nip { font-size: 9pt; margin-top: 1px; }
        
        /* Tembusan - KIRI BAWAH */
        .tembusan-wrapper {
            margin-top: 25px;
            text-align: left !important;
            page-break-inside: avoid;
            width: 100%;
        }
        .tembusan-label {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
            font-size: 9pt;
        }
        .tembusan-list {
            margin: 0;
            padding-left: 20px;
            font-size: 9pt;
            line-height: 1.4;
            list-style-position: outside;
        }
        .tembusan-list li {
            margin-bottom: 2px;
        }
        
        /* Watermark */
        .watermark-pending {
            position: fixed;
            top: 40%;
            left: 15%;
            transform: rotate(-30deg);
            opacity: 0.08;
            font-size: 40pt;
            color: red;
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>

    {{-- Watermark --}}
    @if(!isset($is_approved) || !$is_approved)
        <div class="watermark-pending">MENUNGGU VERIFIKASI</div>
    @endif

    {{-- Header --}}
    <div class="header">
        {{-- <img src="{{ asset('storage/' . auth()->user()->foto_profile) }}"  --}}
        {{-- <img src="{{ public_path('foto/kabupaten.png') }}" class="logo-left" onerror="this.style.display='none'"> --}}
       <img src="{{ storage_path('app/public/signatures/kabupaten.png') }}" class="logo-left" onerror="this.style.display='none'">
        <h3>PEMERINTAH KABUPATEN SUMEDANG</h3>
        <h2>PERUSAHAAN DAERAH AIR MINUM</h2>
        <p>JALAN RAYA SUMEDANG - CIREBON KM. 4,5 DS. SERANG CIMALAKA</p>
        <p>Telp. (0261) 202827 E-mail: pdamsumedang@gmail.com SUMEDANG 45353</p>
        <img src="{{ storage_path('app/public/signatures/pdam.png') }}" class="logo-right" onerror="this.style.display='none'">


    </div>
    <div class="header-line-2"></div>

    {{-- Content --}}
    <div class="content">
        {{-- Tanggal --}}
        <div class="date-place">
            Sumedang, {{ $letter->tanggal ? $letter->tanggal->format('d F Y') : date('d F Y') }}
        </div>

        {{-- Info Surat --}}
        {{-- Info Surat --}}
<table class="info-table">
    <tr>
        <td style="width: 15%;"><strong>Nomor</strong></td>
        <td style="width: 35%;">: {{ $letter->nomor_surat }}</td>
        <td style="width: 15%;"><strong>Sifat</strong></td>
        <td style="width: 35%;">: {{ $letter->sifat ?? 'Biasa' }}</td>
    </tr>
    <tr>
        <td><strong>Lampiran</strong></td>
        <td>: {{ $letter->lampiran ?? '-' }}</td>
        <td><strong>Perihal</strong></td>
        <td>: <strong><u>{{ $letter->perihal }}</u></strong></td>
    </tr>
</table>

{{-- Kepada --}}
<div style="margin: 15px 0;">
    @php
        $fieldKepada = $letter->values->firstWhere('field.nama_field', 'kepada') ?? 
                       $letter->values->firstWhere('field.nama_field', 'kepada_nd');
        
        // Jika field kepada adalah user ID, ambil nama dari relasi
        $kepadaName = 'Penerima Surat';
        if ($fieldKepada) {
            if (is_numeric($fieldKepada->nilai)) {
                // Ini adalah user ID
                $user = \App\Models\User::find($fieldKepada->nilai);
                $kepadaName = $user ? $user->nama_lengkap : $fieldKepada->nilai;
            } else {
                $kepadaName = $fieldKepada->nilai;
            }
        }
    @endphp
    <p style="margin: 0;">
        <strong>Kepada Yth.</strong><br>
        {{ $kepadaName }}<br>
        di Tempat
    </p>
</div>

        {{-- Isi Surat --}}
        @php
            $fieldIsi = $letter->values->firstWhere('field.nama_field', 'isi_surat') ?? 
                        $letter->values->firstWhere('field.nama_field', 'isi_nota');
            $isiSurat = $fieldIsi ? $fieldIsi->nilai : ($letter->isi_surat ?? 'Demikian surat ini kami sampaikan.');
        @endphp
        <div class="invitation-text">{{ $isiSurat }}</div>

        {{-- Field Dinamis (EXCLUDE tembusan) --}}
        {{-- Field Dinamis --}}
@if($letter->values && count($letter->values) > 0)
    @php
        $excludeFields = ['isi_surat', 'isi_nota', 'kepada', 'kepada_nd', 'tujuan_instansi', 'penandatangan', 'tembusan', 'dari'];
        $displayValues = $letter->values->filter(function($val) use ($excludeFields) {
            return !in_array($val->field->nama_field ?? '', $excludeFields) && !empty($val->nilai);
        });
    @endphp
    @if($displayValues->count() > 0)
    <table class="details-table">
        @foreach($displayValues as $val)
        <tr>
            <td style="width: 120px; font-weight: bold;">
                {{ ucfirst(str_replace('_', ' ', $val->field->nama_field)) }}
            </td>
            <td style="width: 10px;">:</td>
            <td>
                @php
                    // Cek jika field adalah user ID (dari, kepada, dll)
                    $displayValue = $val->nilai;
                    if (is_numeric($val->nilai) && ($val->field->nama_field === 'dari' || strpos(strtolower($val->field->nama_field), 'user') !== false)) {
                        $user = \App\Models\User::find($val->nilai);
                        $displayValue = $user ? $user->nama_lengkap : $val->nilai;
                    }
                @endphp
                @if($val->field->tipe_field === 'date' && $val->nilai)
                    {{ \Carbon\Carbon::parse($val->nilai)->isoFormat('dddd, D MMMM Y') }}
                @else
                    {{ $displayValue }}
                @endif
            </td>
        </tr>
        @endforeach
    </table>
    @endif
@endif

        {{-- Penutup --}}
        <p class="closing-text">Demikian, agar menjadi perhatian dan dilaksanakan sebagaimana mestinya.</p>

        {{-- Tanda Tangan --}}
        @php
            $fieldPenandatangan = $letter->values->firstWhere('field.nama_field', 'penandatangan');
            $jabatanPenandatangan = $fieldPenandatangan ? $fieldPenandatangan->nilai : 'Kepala Bagian';
        @endphp

        <table class="signature-table">
            @if(isset($is_approved) && $is_approved)
                <tr>
                    {{-- Dirut --}}
                    <td>
                        <p class="signature-label">Menyetujui,<br>DIREKTUR UTAMA<br>PDAM TIRTA MEDAL</p>
                        <div class="signature-space">
                            @if($signatureDirut)
                                <img src="{{ $signatureDirut }}" class="signature-img" onerror="this.style.display='none'">
                            @else
                                <div style="padding-top: 30px; color: #666; font-size: 8pt;">[ TTD Digital ]</div>
                            @endif
                        </div>
                        <p class="signature-name">{{ $letter->approver ? $letter->approver->nama_lengkap : 'NAMA DIREKTUR' }}</p>
                        <p class="signature-nip">NIP. {{ $letter->approver ? $letter->approver->nip : '________________' }}</p>
                    </td>
                    {{-- Penandatangan --}}
                    <td>
                        <p class="signature-label">Sumedang, {{ $letter->tanggal ? $letter->tanggal->format('d F Y') : date('d F Y') }}<br>{{ $jabatanPenandatangan }},</p>
                        <div class="signature-space">
                            @if($signatureKabag)
                                <img src="{{ $signatureKabag }}" class="signature-img" onerror="this.style.display='none'">
                            @else
                                <div style="padding-top: 30px; color: #666; font-size: 8pt;">[ TTD ]</div>
                            @endif
                        </div>
                        <p class="signature-name">{{ $letter->creator ? $letter->creator->nama_lengkap : 'NAMA KABAG' }}</p>
                        <p class="signature-nip">NIP. {{ $letter->creator ? $letter->creator->nip : '________________' }}</p>
                    </td>
                </tr>
            @else
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="text-align: center; width: 50%;">
                        <p class="signature-label">Sumedang, {{ $letter->tanggal ? $letter->tanggal->format('d F Y') : date('d F Y') }}<br>{{ $jabatanPenandatangan }},</p>
                        <div class="signature-space">
                            @if($signatureKabag)
                                <img src="{{ $signatureKabag }}" class="signature-img" onerror="this.style.display='none'">
                            @else
                                <div style="padding-top: 30px; color: #666; font-size: 8pt;">[ TTD ]</div>
                            @endif
                        </div>
                        <p class="signature-name">{{ $letter->creator ? $letter->creator->nama_lengkap : 'NAMA KABAG' }}</p>
                        <p class="signature-nip">NIP. {{ $letter->creator ? $letter->creator->nip : '________________' }}</p>
                        <p style="margin-top: 10px; font-size: 8pt; color: #999; font-style: italic;">
                            Status: {{ strtoupper(str_replace('_', ' ', $letter->status)) }}
                        </p>
                    </td>
                </tr>
            @endif
        </table>

        {{-- ✅ TEMBUSAN - POSISI KIRI BAWAH --}}
       {{-- ✅ TEMBUSAN - Cek dari 2 Sumber --}}
@php
    // Prioritas 1: Cari dari letter_values (field template)
    $tembusanField = $letter->values->firstWhere('field.nama_field', 'tembusan');
    $tembusanText = null;
    
    if ($tembusanField && trim($tembusanField->nilai)) {
        $tembusanText = trim($tembusanField->nilai);
    } elseif ($letter->tembusan && trim($letter->tembusan)) {
        // Fallback: kolom letters.tembusan
        $tembusanText = trim($letter->tembusan);
    }
@endphp

@if($tembusanText)
<div class="tembusan-wrapper">
    <div class="tembusan-label">Tembusan:</div>
    <ol class="tembusan-list">
        @foreach(explode("\n", $tembusanText) as $item)
            @if(trim($item))
                <li>{{ trim($item) }}</li>
            @endif
        @endforeach
    </ol>
</div>
@endif

    </div>
</body>
</html>