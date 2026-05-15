<?php
namespace App\Models;

use App\Models\Disposisi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ✅ Tambah cabang_id & jabatan_id agar bisa di-mass-assign
    protected $fillable = [
        'username', 'password_hash', 'nama_lengkap', 'email', 'jabatan',
        'level', 'struktur', 'unit_kerja', 'status', 'signature',
        'nik', 'no_hp', 'foto_profile', 'cabang_id', 'jabatan_id',
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

    // 🔗 RELASI: User "milik" satu Cabang & satu Jabatan
    public function cabang() { return $this->belongsTo(Cabang::class); }
    public function jabatan(){ return $this->belongsTo(Jabatan::class); }

    // 🔹 HELPER LEVEL (Tetap pakai enum `level` untuk performa)
    public function isAdmin(): bool   { return $this->level === 'admin'; }
    public function isDirut(): bool   { return $this->level === 'dirut'; }
    public function isKabag(): bool   { return $this->level === 'kabag'; }
    public function isKacab(): bool   { return $this->level === 'kacab'; }
    public function isKasubag(): bool { return $this->level === 'kasubag'; }
    public function isKasie(): bool   { return $this->level === 'kasie'; }
    public function isStaff(): bool   { return $this->level === 'staff'; }

    public function isLevelAtLeast(int $minLevel): bool {
        return ($this->level_urutan ?? 1) >= $minLevel;
    }

    public function canVerify(): bool  { return $this->isAdmin() || $this->isLevelAtLeast(2); }
    public function canDispose(): bool { return $this->isAdmin() || $this->isLevelAtLeast(3); }
    public function canReturnToStaff(): bool { return $this->isKasubag() || $this->isKasie(); }

    // 🔹 CEK STRUKTUR: Prioritaskan relasi, fallback ke kolom lama
    public function isPusat(): bool {
        if ($this->cabang && $this->cabang->tipe === 'pusat') return true;
        return $this->struktur === 'pusat';
    }

    public function isCabang(): bool {
        if ($this->cabang && $this->cabang->tipe === 'cabang') return true;
        return $this->struktur === 'cabang';
    }

    public function isUnit(): bool {
        if ($this->cabang && $this->cabang->tipe === 'unit') return true;
        return false;
    }

    // 🔹 ACCESSOR: Agar view/form lama TIDAK ERROR
    public function getStrukturAttribute() {
        return $this->cabang ? $this->cabang->tipe : ($this->attributes['struktur'] ?? 'pusat');
    }

    public function getUnitKerjaAttribute() {
        // Jika relasi cabang ada, ambil nama cabang sebagai unit kerja default
        if ($this->cabang && $this->cabang->nama_cabang) {
            return strtolower(preg_replace('/[^a-zA-Z]/', '', $this->cabang->nama_cabang));
        }
        return $this->attributes['unit_kerja'] ?? 'umum';
    }

    // 🔹 LOGIKA ROUTING: Ganti string comparison → ID comparison
    public function canForwardTo(User $target): bool
    {
        if ($this->isAdmin() || $this->isDirut()) return true;

        if ($this->isPusat()) {
            if ($this->isStaff()) {
                return $this->cabang_id === $target->cabang_id
                    && in_array($target->level, array('kasubag', 'kabag'))
                    && $target->level_urutan > $this->level_urutan;
            }
            if ($this->isKasubag()) {
                return $this->cabang_id === $target->cabang_id
                    && in_array($target->level, array('kabag', 'staff'));
            }
            if ($this->isKabag()) {
                if ($target->isDirut() || $target->isKabag()) return true;
                return $this->cabang_id === $target->cabang_id && $target->isKasubag();
            }
        } 
        elseif ($this->isCabang() || $this->isUnit()) {
            if ($this->isStaff()) {
                return $this->cabang_id === $target->cabang_id
                    && in_array($target->level, array('kasie', 'kacab'))
                    && $target->level_urutan > $this->level_urutan;
            }
            if ($this->isKasie()) {
                return $this->cabang_id === $target->cabang_id
                    && in_array($target->level, array('kacab', 'staff'));
            }
            if ($this->isKacab()) {
                if ($target->isDirut() || $target->isKacab()) return true;
                return $target->isKasie();
            }
        }

        return false;
    }

    // 🔹 GET AVAILABLE FORWARD TARGETS
    public function getAvailableForwardTargets()
    {
        $query = User::where('status', 'aktif')->where('id', '!=', $this->id);

        if ($this->isAdmin() || $this->isDirut()) {
            return $query->orderByRaw("
                CASE level WHEN 'admin' THEN 7 WHEN 'dirut' THEN 6 WHEN 'kabag' THEN 5 
                WHEN 'kacab' THEN 5 WHEN 'kasubag' THEN 3 WHEN 'kasie' THEN 3 WHEN 'staff' THEN 1 ELSE 0 END DESC
            ")->orderBy('nama_lengkap')->get();
        }

        if ($this->isPusat()) {
            if ($this->isStaff()) {
                $query->whereIn('level', array('kasubag', 'kabag'))
                      ->where('cabang_id', $this->cabang_id)
                      ->orderByRaw("CASE level WHEN 'kabag' THEN 5 WHEN 'kasubag' THEN 3 ELSE 0 END DESC");
            } elseif ($this->isKasubag()) {
                $query->where('level', 'kabag')->where('cabang_id', $this->cabang_id);
            } elseif ($this->isKabag()) {
                $query->where(function ($q) {
                    $q->whereIn('level', array('dirut', 'kabag'))
                      ->orWhere(function ($sub) {
                          $sub->where('level', 'kasubag')->where('cabang_id', $this->cabang_id);
                      });
                });
            }
        } 
        elseif ($this->isCabang() || $this->isUnit()) {
            if ($this->isStaff()) {
                $query->whereIn('level', array('kasie', 'kacab'))
                      ->where('cabang_id', $this->cabang_id)
                      ->orderByRaw("CASE level WHEN 'kacab' THEN 5 WHEN 'kasie' THEN 3 ELSE 0 END DESC");
            } elseif ($this->isKasie()) {
                $query->where('level', 'kacab')->where('cabang_id', $this->cabang_id);
            } elseif ($this->isKacab()) {
                $query->where(function ($q) {
                    $q->whereIn('level', array('dirut', 'kacab'))->orWhere('level', 'kasie');
                });
            }
        }

        return $query->orderBy('nama_lengkap')->get();
    }

    // 🔹 UI HELPERS
    public function getStrukturLabel() {
        if ($this->isPusat()) return 'Pusat';
        if ($this->isCabang()) return 'Cabang';
        if ($this->isUnit()) return 'Unit';
        return ucfirst($this->struktur ?? 'pusat');
    }

    public function getLevelLabel() {
        $labels = array(
            'admin' => 'Administrator', 'dirut' => 'Direktur Utama',
            'kabag' => 'Kepala Bagian', 'kacab' => 'Kepala Cabang',
            'kasubag' => 'Kepala Sub Bagian', 'kasie' => 'Kepala Seksi', 'staff' => 'Staff',
        );
        return isset($labels[$this->level]) ? $labels[$this->level] : ucfirst(str_replace('_', ' ', $this->level));
    }

    public function getUrutanAttribute() {
        $mapping = ['admin'=>7, 'dirut'=>6, 'kabag'=>5, 'kacab'=>5, 'kanit'=>4, 'kasubag'=>3, 'kasie'=>3, 'staff'=>1];
        return $mapping[$this->level] ?? 0;
    }

    public function getCabangLabelAttribute() {
        return $this->cabang ? $this->cabang->nama_cabang : ($this->isPusat() ? 'Kantor Pusat' : 'Cabang');
    }
}