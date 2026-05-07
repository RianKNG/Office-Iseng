<?php
    // ✅ DEBUG & AUTO-CORRECT PATH UNTUK SIGNATURE
    $signatureImageBase64 = null;
    
    if ($letter->creator && $letter->creator->signature_path) {
        // Hapus slash di depan jika ada (common issue)
        $cleanPath = ltrim($letter->creator->signature_path, '/');
        
        // Path fisik sebenarnya di Laravel
        $path = storage_path('app/public/' . $cleanPath);
        
        // Debug log (cek storage/logs/laravel.log)
        \Log::info('=== SIGNATURE DEBUG ===');
        \Log::info('DB Value: ' . $letter->creator->signature_path);
        \Log::info('Clean Path: ' . $cleanPath);
        \Log::info('Full Physical Path: ' . $path);
        \Log::info('File Exists: ' . (file_exists($path) ? 'YES ✅' : 'NO ❌'));
        
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $signatureImageBase64 = 'image/' . $type . ';base64,' . base64_encode($data);
            \Log::info('Base64 Generated: YES ✅');
        } else {
            // Fallback: coba path alternatif (jika symbolic link confusion)
            $altPath = public_path('storage/' . $cleanPath);
            \Log::info('Trying Alt Path: ' . $altPath);
            \Log::info('Alt File Exists: ' . (file_exists($altPath) ? 'YES ✅' : 'NO ❌'));
            
            if (file_exists($altPath)) {
                $type = pathinfo($altPath, PATHINFO_EXTENSION);
                $data = file_get_contents($altPath);
                $signatureImageBase64 = 'image/' . $type . ';base64,' . base64_encode($data);
            }
        }
    }
?>

<div style="font-family: 'Times New Roman', Times, serif; line-height: 1.6; max-width: 800px; margin: 0 auto;">
    <!-- Kop Surat -->
    <div style="text-align: center; margin-bottom: 25px; border-bottom: 4px double #000; padding-bottom: 15px;">
        <h2 style="margin: 0; font-size: 22px; font-weight: bold;">PEMERINTAH KOTA CONTOH</h2>
        <h3 style="margin: 8px 0 5px; font-size: 18px; font-weight: bold;">DINAS KOMUNIKASI DAN INFORMATIKA</h3>
        <p style="margin: 3px 0; font-size: 13px;">Jl. Teknologi No. 123, Kota Contoh - Telp: (021) 123-4567</p>
    </div>

    <!-- Judul Surat -->
    <div style="text-align: center; margin-bottom: 25px;">
        <h4 style="margin: 0; font-size: 16px; text-decoration: underline; text-underline-offset: 5px;">
            {{ strtoupper($letter->template->nama_template) }}
        </h4>
        <p style="margin: 5px 0 0; font-size: 14px;">Nomor: {{ $letter->nomor_surat }}</p>
    </div>

    <!-- Tabel Info -->
    <div style="margin-bottom: 25px;">
        <table style="width: 100%; font-size: 14px;">
            <tr><td style="width: 140px;"><strong>Kode</strong></td><td>: {{ $letter->template->kode_template }}</td></tr>
            <tr><td><strong>Nomor</strong></td><td>: {{ $letter->nomor_surat }}</td></tr>
            <tr><td><strong>Tanggal</strong></td><td>: {{ $letter->tanggal->format('d F Y') }}</td></tr>
            <tr><td><strong>Perihal</strong></td><td>: {{ $letter->perihal }}</td></tr>
        </table>
    </div>

    <!-- Detail Field -->
    @if($letter->values->count() > 0)
    <div style="margin-bottom: 30px;">
        <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
            @foreach($letter->values as $val)
            <tr>
                <td width="35%" style="vertical-align: top; padding: 5px 0;"><strong>{{ $val->field->nama_field }}:</strong></td>
                <td>{{ $val->nilai }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @endif

    <!-- ✅ TANDA TANGAN (POSISI BENAR: DI BAWAH JABATAN, DI ATAS NAMA) -->
    <div style="margin-top: 60px; text-align: right; padding-right: 40px;">
        <p style="margin: 0 0 5px 0;">Kota Contoh, {{ $letter->tanggal->format('d F Y') }}</p>
        <p style="margin: 0 0 45px 0;">{{ $letter->creator->jabatan ?? 'Pejabat Berwenang' }}</p>
        
        <div style="height: 65px; display: flex; align-items: center; justify-content: flex-end; margin-bottom: 5px;">
            @if(!empty($signatureBase64))
                        <img src="{{ $signatureBase64 }}" class="signature-img">
                    @else
                        <p style="color:red; font-size: 9pt; border: 1px dashed red; padding: 10px;">
                            (TTD Belum Diunggah)
                        </p>
                    @endif
        </div>
        
        <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $letter->creator->nama_lengkap ?? $letter->creator->name }}</p>
        <p style="margin: 0;">NgggIP. {{ $letter->creator->nip ?? '___________________' }}</p>
    </div>
</div>