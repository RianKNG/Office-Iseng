<?php
namespace App\Models;

use App\Models\Disposisi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ✅ $fillable: level_urutan TIDAK dimasukkan (generated column)
    protected $fillable = [
    'username', 'password_hash', 'nama_lengkap', 'email', 'jabatan',
    'level', 'struktur', 'unit_kerja', 'status', 'signature',
    'nik', 'no_hp', 'foto_profile', 'level_urutan',
];
    protected $hidden = ['password_hash'];

    public function getAuthPassword() { 
        return $this->password_hash; 
    }

    // Relasi
    public function disposisiMasuk() { 
        return $this->hasMany(Disposisi::class, 'ke_user_id'); 
    }

    public function disposisiKeluar() { 
        return $this->hasMany(Disposisi::class, 'dari_user_id'); 
    }

    // ==========================================
    // 🟢 1. HELPER CEK LEVEL
    // ==========================================
    public function isAdmin(): bool { 
        return $this->level === 'admin'; 
    }

    public function isDirut(): bool { 
        return $this->level === 'dirut'; 
    }

    public function isKabagKacab(): bool { 
        return $this->level === 'kabag_kacab'; 
    }

    public function isKasubagKasie(): bool { 
        return $this->level === 'kasubag_kasie'; 
    }

    public function isStaff(): bool { 
        return $this->level === 'staff'; 
    }

    public function isLevelAtLeast(int $minLevel): bool {
        return ($this->level_urutan ?? 1) >= $minLevel;
    }

    public function canVerify(): bool { 
        return $this->isAdmin() || $this->isLevelAtLeast(2); 
    }

    public function canDispose(): bool { 
        return $this->isAdmin() || $this->isLevelAtLeast(3); 
    }

    public function canReturnToStaff(): bool { 
        return $this->isKasubagKasie(); 
    }

    // ==========================================
    // 🟢 2. LOGIKA ROUTING
    // ==========================================
    
    /** Cek apakah user ini boleh meneruskan ke $target */
    public function canForwardTo(User $target): bool
    {
        // ✅ ADMIN & DIRUT: Bisa forward ke SIAPA SAJA
        if ($this->isAdmin() || $this->isDirut()) {
            return true;
        }

        // ✅ STAFF: Ke atasan langsung (level_urutan + 1) di kantor & divisi sama
        if ($this->isStaff()) {
            return $this->struktur === $target->struktur
                && $this->unit_kerja === $target->unit_kerja
                && $target->level_urutan === ($this->level_urutan + 1);
        }

        // ✅ KASUBAG/KASIE: Ke Kabag (atasan) atau Staff (bawahan) di divisi/kantor sama
        if ($this->isKasubagKasie()) {
            return $this->struktur === $target->struktur
                && $this->unit_kerja === $target->unit_kerja
                && in_array($target->level, ['kabag_kacab', 'staff']);
        }

        // ✅ KABAG/KACAB: Ke Dirut, sesama Kabag, atau Kasubag (bawahan satu divisi)
        if ($this->isKabagKacab()) {
            if ($target->isDirut() || $target->isKabagKacab()) {
                return true;
            }
            return $this->struktur === $target->struktur
                && $this->unit_kerja === $target->unit_kerja
                && $target->isKasubagKasie();
        }

        return false;
    }

    /** Ambil list user yang VALID untuk dipilih di dropdown forward */
    public function getAvailableForwardTargets()
    {
        $query = User::where('status', 'aktif')->where('id', '!=', $this->id);

        // ✅ STAFF: Hanya ke atasan langsung (Kasubag/Kasie) satu divisi
        if ($this->isStaff()) {
            $query->where('struktur', $this->struktur)
                  ->where('unit_kerja', $this->unit_kerja)
                  ->where('level_urutan', $this->level_urutan + 1);
        }
        // ✅ KASUBAG/KASIE: Ke Kabag (atasan) satu divisi
        elseif ($this->isKasubagKasie()) {
            $query->where('struktur', $this->struktur)
                  ->where('unit_kerja', $this->unit_kerja)
                  ->where('level', 'kabag_kacab');
        }
        // ✅ KABAG/KACAB: Ke Dirut, sesama Kabag, & Kasubag (bawahan)
        elseif ($this->isKabagKacab()) {
            $query->where(function ($q) {
                $q->whereIn('level', ['dirut', 'kabag_kacab'])
                  ->orWhere(function ($sub) {
                      $sub->where('level', 'kasubag_kasie')
                          ->where('struktur', $this->struktur)
                          ->where('unit_kerja', $this->unit_kerja);
                  });
            });
        }
        // ✅ DIRUT & ADMIN: Tidak difilter (lihat semua)
        // else: biarkan query tanpa where tambahan

        return $query->orderBy('level_urutan', 'desc')
                     ->orderBy('nama_lengkap')
                     ->get();
    }

    // ==========================================
    // 🟢 3. HELPER LABEL UI
    // ==========================================
    
    public function getStrukturLabel(): string {
        return $this->struktur === 'pusat' ? 'Pusat' : 'Cabang';
    }

    public function getLevelLabel(): string {
        $labels = [
            'admin' => 'Administrator',
            'dirut' => 'Direktur',
            'kabag_kacab' => $this->struktur === 'pusat' ? 'Kabag' : 'Kacab',
            'kasubag_kasie' => $this->struktur === 'pusat' ? 'Kasubag' : 'Kasie',
            'staff' => 'Staff',
        ];
        return $labels[$this->level] ?? ucfirst(str_replace('_', ' ', $this->level));
    }
}