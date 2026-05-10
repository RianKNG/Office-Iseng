<!DOCTYPE html>
<html>
<head>
    <title>Surat - {{ $letter->nomor_surat }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; color: black; line-height: 1.3; }
        .kop-surat { text-align: center; border-bottom: 3px solid black; padding-bottom: 5px; }
        .kop-surat h2 { margin: 0; font-size: 16pt; }
        .garis-tipis { border-bottom: 1px solid black; margin-top: 2px; margin-bottom: 20px; }
        
        .tabel-info { width: 100%; margin-bottom: 20px; }
        .tabel-info td { vertical-align: top; }

        .isi-surat { text-align: justify; margin-bottom: 30px; text-indent: 40px; }

        /* Area Tanda Tangan */
        .ttd-wrapper {
            float: right;
            width: 250px;
            text-align: center;
            position: relative;
        }
        .img-signature {
            width: 150px;
            height: auto;
            position: absolute;
            top: 20px; /* Menyesuaikan posisi agar terlihat menimpa nama sedikit */
            left: 50%;
            transform: translateX(-50%);
            z-index: -1; /* Di belakang teks nama */
        }
        .nama-pejabat {
            margin-top: 80px; /* Memberi ruang untuk gambar TTD */
            font-weight: bold;
            text-decoration: underline;
        }

        /* Logic Halaman Baru untuk Lampiran */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- Halaman 1: Surat Utama -->
    <div class="kop-surat">
        <h3>PEMERINTAH KABUPATEN SUMEDANG</h3>
        <h2>PERUSAHAAN DAERAH AIR MINUM</h2>
        <p>JALAN RAYA SUMEDANG - CIREBON KM. 4,5 SUMEDANG 45353</p>
    </div>
    <div class="garis-tipis"></div>

    <table class="tabel-info">
        <tr>
            <td width="15%">Nomor</td><td>: {{ $letter->nomor_surat }}</td>
            <td width="10%">Kepada Yth:</td>
        </tr>
        <tr>
            <td>Perihal</td><td>: <strong>{{ $letter->perihal }}</strong></td>
            <td>Penerima di Tempat</td>
        </tr>
    </table>

    <div class="isi-surat">
        <p>Dengan hormat, sehubungan dengan {{ $letter->perihal }}, kami mengundang saudara untuk hadir pada:</p>
        
        <table style="margin-left: 50px;">
            @foreach($letter->values as $val)
            <tr>
                <td width="150">{{ $val->field->nama_field }}</td>
                <td>: {{ $val->nilai }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Blok Tanda Tangan -->
    <div class="ttd-wrapper">
        <p>DIREKTUR PDAM,<br>Kabupaten Sumedang</p>
        
        @if($signatureBase64)
            <img src="{{ $signatureBase64 }}" class="img-signature">
        @else
            <div style="height: 60px;"></div> <!-- Spasi jika tidak ada TTD -->
        @endif

        <p class="nama-pejabat">{{ $letter->creator->nama_lengkap }}</p>
    </div>

    <div style="clear: both;"></div>

    <!-- Halaman 2: Lampiran (Hanya muncul jika ada file_path) -->
    @if($letter->file_path)
    <div class="page-break"></div>
    <div style="text-align: center;">
        <h3>LAMPIRAN SURAT</h3>
        <p>Nomor: {{ $letter->nomor_surat }}</p>
        <hr>
        <div style="margin-top: 50px; border: 1px dashed #000; padding: 20px;">
            <p>Dokumen Lampiran Asli: <strong>{{ basename($letter->file_path) }}</strong></p>
            <p>Silahkan download file lampiran melalui sistem aplikasi.</p>
        </div>
    </div>
    @endif

</body>
</html>