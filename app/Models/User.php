<?php
namespace App\Models;

use App\Models\Disposisi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username', 'password_hash', 'nama_lengkap', 'email', 'jabatan',
        'level', 'struktur', 'unit_kerja', 'status', 'signature',
        'nik', 'no_hp', 'foto_profile',
    ];

    protected $hidden = ['password_hash'];

    public function getAuthPassword() { 
        return $this->password_hash; 
    }

    public function disposisiMasuk() { 
        return $this->hasMany(Disposisi::class, 'ke_user_id'); 
    }

    public function disposisiKeluar() { 
        return $this->hasMany(Disposisi::class, 'dari_user_id'); 
    }

    // 🔹 HELPER LEVEL (UPDATED - terpisah, PHP 7.4 safe)
    public function isAdmin(): bool         { return $this->level === 'admin'; }
    public function isDirut(): bool         { return $this->level === 'dirut'; }
    public function isKabag(): bool         { return $this->level === 'kabag'; }
    public function isKacab(): bool         { return $this->level === 'kacab'; }
    public function isKasubag(): bool       { return $this->level === 'kasubag'; }
    public function isKasie(): bool         { return $this->level === 'kasie'; }
    public function isStaff(): bool         { return $this->level === 'staff'; }

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
        return $this->isKasubag() || $this->isKasie(); 
    }

    public function isPusat(): bool  { return $this->struktur === 'pusat'; }
    public function isCabang(): bool { return $this->struktur === 'cabang'; }

    // 🔹 LOGIKA ROUTING (PHP 7.4 safe - no arrow functions)
    public function canForwardTo(User $target): bool
    {
        if ($this->isAdmin() || $this->isDirut()) return true;

        if ($this->isPusat()) {
            if ($this->isStaff()) {
                return $this->unit_kerja === $target->unit_kerja
                    && in_array($target->level, array('kasubag', 'kabag'))
                    && $target->level_urutan > $this->level_urutan;
            }
            if ($this->isKasubag()) {
                return $this->unit_kerja === $target->unit_kerja
                    && in_array($target->level, array('kabag', 'staff'));
            }
            if ($this->isKabag()) {
                if ($target->isDirut() || $target->isKabag()) return true;
                return $this->unit_kerja === $target->unit_kerja && $target->isKasubag();
            }
        }

        elseif ($this->isCabang()) {
            if ($this->isStaff()) {
                return in_array($target->level, array('kasie', 'kacab'))
                    && $target->level_urutan > $this->level_urutan;
            }
            if ($this->isKasie()) {
                return in_array($target->level, array('kacab', 'staff'));
            }
            if ($this->isKacab()) {
                if ($target->isDirut() || $target->isKacab()) return true;
                return $target->isKasie();
            }
        }

        return false;
    }

    // 🔹 Get Available Forward Targets (PHP 7.4 safe)
    /** Ambil list user yang VALID untuk dipilih di dropdown forward */
public function getAvailableForwardTargets()
{
    $query = User::where('status', 'aktif')->where('id', '!=', $this->id);

    // ✅ ADMIN & DIRUT: Lihat semua (tanpa filter)
    if ($this->isAdmin() || $this->isDirut()) {
        return $query->orderByRaw("
            CASE level 
                WHEN 'admin' THEN 7 
                WHEN 'dirut' THEN 6 
                WHEN 'kabag' THEN 5 
                WHEN 'kacab' THEN 5 
                WHEN 'kasubag' THEN 3 
                WHEN 'kasie' THEN 3 
                WHEN 'staff' THEN 1 
                ELSE 0 
            END DESC
        ")->orderBy('nama_lengkap')->get();
    }

    // ✅ PUSAT
    if ($this->isPusat()) {
        if ($this->isStaff()) {
            $query->whereIn('level', array('kasubag', 'kabag'))
                  ->where('unit_kerja', $this->unit_kerja)
                  ->orderByRaw("
                    CASE level 
                        WHEN 'kabag' THEN 5 WHEN 'kasubag' THEN 3 ELSE 0 
                    END DESC
                  ");
        }
        elseif ($this->isKasubag()) {
            $query->where('level', 'kabag')
                  ->where('unit_kerja', $this->unit_kerja);
        }
        elseif ($this->isKabag()) {
            $query->where(function ($q) {
                $q->whereIn('level', array('dirut', 'kabag'))
                  ->orWhere(function ($sub) {
                      $sub->where('level', 'kasubag')
                          ->where('unit_kerja', $this->unit_kerja);
                  });
            });
        }
    }

    // ✅ CABANG
    elseif ($this->isCabang()) {
        if ($this->isStaff()) {
            $query->whereIn('level', array('kasie', 'kacab'))
                  ->orderByRaw("
                    CASE level 
                        WHEN 'kacab' THEN 5 WHEN 'kasie' THEN 3 ELSE 0 
                    END DESC
                  ");
        }
        elseif ($this->isKasie()) {
            $query->where('level', 'kacab');
        }
        elseif ($this->isKacab()) {
            $query->where(function ($q) {
                $q->whereIn('level', array('dirut', 'kacab'))
                  ->orWhere('level', 'kasie');
            });
        }
    }

    return $query->orderBy('nama_lengkap')->get();
}

    // 🔹 UI Helpers (PHP 7.4 compatible - NO match(), NO arrow functions)
    public function getStrukturLabel() {
        if ($this->struktur === 'pusat') {
            return 'Pusat';
        } elseif ($this->struktur === 'cabang') {
            return 'Cabang';
        } elseif ($this->struktur === 'unit') {
            return 'Unit';
        }
        return ucfirst($this->struktur);
    }

    public function getLevelLabel() {
        $labels = array(
            'admin'       => 'Administrator',
            'dirut'       => 'Direktur Utama',
            'kabag'       => 'Kepala Bagian',
            'kacab'       => 'Kepala Cabang',
            'kasubag'     => 'Kepala Sub Bagian',
            'kasie'       => 'Kepala Seksi',
            'staff'       => 'Staff',
        );
        if (isset($labels[$this->level])) {
            return $labels[$this->level];
        }
        return ucfirst(str_replace('_', ' ', $this->level));
    }
    
}