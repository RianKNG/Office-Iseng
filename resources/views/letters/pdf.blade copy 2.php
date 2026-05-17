<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Digital - PDAM Sumedang</title>
    <style>
        @page { margin: 2cm; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background: white;
        }
        /* Header / Kop Surat */
        .header {
            position: relative;
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 5px;
            margin-bottom: 2px;
        }
        .header-line-2 {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
            height: 2px;
        }
        .logo-left {
            position: absolute;
            left: 0; top: 0;
            width: 70px;
        }
        .logo-right {
            position: absolute;
            right: 0; top: 0;
            width: 70px;
        }
        .header h3 { margin: 0; font-size: 13pt; font-weight: normal; }
        .header h2 { margin: 0; font-size: 15pt; font-weight: bold; }
        .header p { margin: 0; font-size: 9pt; }

        /* Content Layout */
        .content { margin-top: 20px; }
        .date-place { text-align: right; margin-bottom: 20px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; }

        .invitation-text { text-align: justify; margin-bottom: 15px; text-indent: 40px; }

        /* Detail Undangan (Tabel Tengah) */
        .details-table { margin-left: 80px; margin-bottom: 20px; }
        .details-table td { padding: 2px 5px; }

        /* Tanda Tangan */
        .sig-container {
            float: right;
            width: 300px;
            text-align: center;
            margin-top: 20px;
        }
        .sig-space {
            height: 80px;
            position: relative;
            margin: 5px 0;
        }
        .signature-img {
            max-height: 80px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Tembusan */
        .tembusan {
            margin-top: 50px;
            clear: both;
            font-size: 10pt;
        }
        .tembusan p { margin-bottom: 2px; font-weight: bold; text-decoration: underline; }
        .tembusan ol { margin-top: 0; padding-left: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <!-- Pastikan path logo sesuai dengan folder public/ storage Anda -->
        <img src="https://path-ke-logo-kab-sumedang.png" class="logo-left">
        <h3>PEMERINTAH KABUPATEN SUMEDANG</h3>
        <h2>PERUSAHAAN DAERAH AIR MINUM</h2>
        <p>JALAN RAYA SUMEDANG - CIREBON KM. 4,5 DS. SERANG CIMALAKA</p>
        <p>Telp. (0261) 202827 E-mail: pdamsumedang@gmail.com SUMEDANG 45353</p>
        <img src="https://path-ke-logo-pdam.png" class="logo-right">
    </div>
    <div class="header-line-2"></div>

    <div class="content">
        <div class="date-place">
            Sumedang, {{ $letter->tanggal->format('d F Y') }}
        </div>

        <table class="info-table">
            <tr>
                <td style="width: 15%;">Nomor</td>
                <td style="width: 45%;">: {{ $letter->nomor_surat }}</td>
                <td style="width: 10%;">Kepada</td>
                <td rowspan="4">Yth. <strong>Penerima Surat</strong><br>di -<br><span style="margin-left: 15px;">Tempat</span></td>
            </tr>
            <tr>
                <td>Sifat</td>
                <td>: Biasa</td>
            </tr>
            <tr>
                <td>Lampiran</td>
                <td>: -</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>: <strong><u>{{ $letter->perihal }}</u></strong></td>
            </tr>
        </table>

        <p class="invitation-text">
            Dalam rangka Peningkatan Kinerja Sumber Daya Manusia bersama ini kami mengundang Saudara untuk hadir dalam <strong>{{ $letter->perihal }}</strong> pada:
        </p>

        <table class="details-table">
            @foreach($letter->values as $val)
            <tr>
                <td style="width: 100px;">{{ $val->field->nama_field }}</td>
                <td>:</td>
                <td>{{ $val->nilai }}</td>
            </tr>
            @endforeach
        </table>

        <p>Demikian, agar hadir tepat waktu.</p>

        <div class="sig-container">
            <p style="margin-bottom: 0;">DIREKTUR PDAM TIRTA MEDAL<br>KABUPATEN SUMEDANG</p>
            
            <div class="sig-space">
                @if(!empty($signatureBase64))
                    <img src="{{ $signatureBase64 }}" class="signature-img">
                @else
                    <div style="padding-top: 30px; color: #ccc; font-style: italic;">(Tanda Tangan Digital)</div>
                @endif
            </div>
            
            <p style="margin-bottom: 0;"><strong><u>{{ $letter->creator->nama_lengkap ?? 'H. TATANG HIDAYAT, SE' }}</u></strong></p>
            <p style="margin-top: 0;">NIP. {{ $letter->creator->nip ?? '___________________' }}</p>
        </div>

        <div class="tembusan">
            <p>Tembusan:</p>
            <ol>
                <li>Yth. Bapak Ketua Dewan Pengawas PDAM Tirta Medal (sebagai laporan)</li>
                <li>Ka. SPI / Fungsional / Ka. Bag / Ka. Bid / Ka. Cab PDAM Tirta Medal</li>
                <li>Arsip</li>
            </ol>
        </div>
    </div>
</body>
</html>