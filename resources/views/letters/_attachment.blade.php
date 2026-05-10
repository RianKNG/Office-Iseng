<style>
    /* Reset gaya untuk simulasi kertas A4 */
    .surat-wrapper {
        font-family: "Times New Roman", Times, serif;
        color: black;
        line-height: 1.2;
    }
    
    /* Kop Surat PDAM */
    .kop-surat {
        text-align: center;
        border-bottom: 3px solid black;
        padding-bottom: 5px;
        margin-bottom: 2px;
    }
    .kop-surat h2 { margin: 0; font-size: 16pt; font-weight: bold; }
    .kop-surat h3 { margin: 0; font-size: 14pt; font-weight: normal; }
    .kop-surat p { margin: 0; font-size: 10pt; }
    .garis-pembatas { border-bottom: 1px solid black; margin-bottom: 20px; }

    /* Layout Data Surat */
    .tgl-surat { text-align: right; margin-bottom: 15px; }
    .tabel-info { width: 100%; margin-bottom: 20px; }
    .tabel-info td { vertical-align: top; padding: 2px 0; }
    
    /* Isi Surat */
    .isi-surat { text-align: justify; text-indent: 45px; margin-bottom: 20px; }
    .detail-titik { margin-left: 80px; margin-bottom: 20px; }

    /* Area Tanda Tangan */
    .signature-wrapper {
        float: right;
        width: 300px;
        text-align: center;
        margin-top: 20px;
        position: relative;
    }
    .ttd-image {
        max-height: 80px;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1;
        margin-top: -10px;
    }
    .ttd-nama {
        margin-top: 80px;
        font-weight: bold;
        text-decoration: underline;
        position: relative;
        z-index: 2;
    }

    /* Pengaturan Cetak & Lampiran */
    @media screen {
        .lampiran-print-area { display: none; } /* Sembunyi di modal preview */
    }

    @media print {
        .lampiran-print-area {
            display: block !important;
            page-break-before: always; /* Pindah ke Halaman 2 */
        }
        .no-print { display: none !important; }
    }
</style>

<div class="surat-wrapper">
    <div class="kop-surat">
        <h3>PEMERINTAH KABUPATEN SUMEDANG</h3>
        <h2>PERUSAHAAN DAERAH AIR MINUM</h2>
        <p>JALAN RAYA SUMEDANG - CIREBON KM. 4,5 DS. SERANG CIMALAKA</p>
        <p>Telp. (0261) 202827 E-mail: pdamsumedang@gmail.com SUMEDANG 45353</p>
    </div>
    <div class="garis-pembatas"></div>

    <div class="tgl-surat">Sumedang, {{ $letter->tanggal->format('d F Y') }}</div>

    <table class="tabel-info">
        <tr>
            <td style="width: 15%;">Nomor</td>
            <td style="width: 45%;">: {{ $letter->nomor_surat }}</td>
            <td style="width: 10%;">Kepada</td>
            <td rowspan="4">Yth. <strong>{{ $letter->penerima ?? 'Penerima Surat' }}</strong><br>di -<br><span style="margin-left: 15px;">Tempat</span></td>
        </tr>
        <tr><td>Sifat</td><td>: Biasa</td></tr>
        <tr><td>Lampiran</td><td>: -</td></tr>
        <tr><td>Perihal</td><td>: <strong><u>{{ $letter->perihal }}</u></strong></td></tr>
    </table>

    <div class="isi-surat">
        <p>Dalam rangka Peningkatan Kinerja Sumber Daya Manusia bersama ini kami mengundang Saudara untuk hadir dalam <strong>{{ $letter->perihal }}</strong> pada:</p>
    </div>

    <table class="detail-titik">
        @foreach($letter->values as $val)
        <tr>
            <td style="width: 120px;">{{ $val->field->nama_field }}</td>
            <td>: {{ $val->nilai }}</td>
        </tr>
        @endforeach
    </table>

    <p>Demikian, agar hadir tepat waktu.</p>

    <!-- TANDA TANGAN DENGAN GAMBAR TERPANTAU -->
    <div class="signature-wrapper">
        <p>DIREKTUR PDAM TIRTA MEDAL<br>KABUPATEN SUMEDANG</p>
        
        {{-- Logika untuk menampilkan gambar tanda tangan jika ada --}}
        @if($letter->creator->signature_path)
            <img src="{{ asset('storage/' . $letter->creator->signature_path) }}" class="ttd-image" alt="Tanda Tangan">
        @endif

        <p class="ttd-nama">{{ $letter->creator->nama_lengkap ?? 'H. TATANG HIDAYAT, SE' }}</p>
    </div>
    <div style="clear: both;"></div>
</div>

<!-- LAMPIRAN OTOMATIS KE HALAMAN 2 SAAT DOWNLOAD/PRINT -->
@if($letter->file_path)
    <div class="lampiran-print-area">
        <div style="padding: 50px; border: 1px dashed #ccc; text-align: center;">
            <h4 style="text-decoration: underline;">LAMPIRAN SURAT</h4>
            <br>
            <p>Dokumen Terlampir untuk Nomor Surat: {{ $letter->nomor_surat }}</p>
            <div class="alert alert-light border">
                <i class="bi bi-file-earmark-pdf fs-1"></i><br>
                <strong>{{ basename($letter->file_path) }}</strong><br>
                <small>{{ strtoupper(pathinfo($letter->file_path, PATHINFO_EXTENSION)) }} - {{ number_format(filesize(storage_path('app/public/' . $letter->file_path))/1024, 2) }} KB</small>
            </div>
            
            <div class="no-print mt-3">
                <a href="{{ asset('storage/' . $letter->file_path) }}" class="btn btn-primary">
                    <i class="bi bi-download"></i> Download File Asli
                </a>
            </div>
        </div>
    </div>
@endif