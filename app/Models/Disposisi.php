<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    protected $table = 'disposisi';
    
    protected $fillable = [
        'letter_id',
        'parent_id',
        'dari_user_id',
        'ke_user_id',
        'instruksi',
        'catatan_respon',
        'balasan',
        'prioritas',
        'status',
        'deadline',
        'is_locked',
        'urutan_berjenjang',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_locked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==========================================
    // 🔹 RELATIONS
    // ==========================================
    public function letter() { 
        return $this->belongsTo(Letter::class, 'letter_id'); 
    }
    
    public function dari() { 
        return $this->belongsTo(User::class, 'dari_user_id'); 
    }
    
    public function ke() { 
        return $this->belongsTo(User::class, 'ke_user_id'); 
    }
    
    public function parent() { 
        return $this->belongsTo(Disposisi::class, 'parent_id'); 
    }
    
    public function children() { 
        return $this->hasMany(Disposisi::class, 'parent_id'); 
    }

    // ==========================================
    // 🔹 SCOPES (PHP 7.4 compatible)
    // ==========================================
    public function scopePending($query) { 
        return $query->where('status', 'pending'); 
    }
    
    public function scopeForUser($query, $userId) { 
        return $query->where('ke_user_id', $userId); 
    }
    
    public function scopeActive($query) { 
        return $query->where('status', '!=', 'cancelled'); 
    }
    
    public function scopeOverdue($query) { 
        return $query->where('deadline', '<', now())->where('status', 'pending'); 
    }
    
    // ✅ Scope untuk level baru (kasubag/kasie/kanit)
    public function scopeForLevel($query, $levels) {
        if (!is_array($levels)) {
            $levels = array($levels);
        }
        return $query->whereHas('ke', function($q) use ($levels) {
            $q->whereIn('level', $levels);
        });
    }

    // ==========================================
    // 🟢 SCOPES KHUSUS: KABAG vs KACAB (PUSAT vs CABANG)
    // ==========================================
    
    /** Scope: Disposisi yang ditujukan ke KABAG (Pusat only) */
    public function scopeToKabag($query) {
        return $query->whereHas('ke', function($q) {
            $q->where('level', 'kabag')->where('struktur', 'pusat');
        });
    }
    
    /** Scope: Disposisi yang ditujukan ke KACAB (Cabang only) */
    public function scopeToKacab($query) {
        return $query->whereHas('ke', function($q) {
            $q->where('level', 'kacab')->where('struktur', 'cabang');
        });
    }
    
    /** Scope: Disposisi dari KABAG (Pusat) */
    public function scopeFromKabag($query) {
        return $query->whereHas('dari', function($q) {
            $q->where('level', 'kabag')->where('struktur', 'pusat');
        });
    }
    
    /** Scope: Disposisi dari KACAB (Cabang) */
    public function scopeFromKacab($query) {
        return $query->whereHas('dari', function($q) {
            $q->where('level', 'kacab')->where('struktur', 'cabang');
        });
    }
    
    /** Scope: Disposisi lintas struktur (Pusat ↔ Cabang) */
    public function scopeCrossStructure($query) {
        return $query->whereHas('dari', function($q1) {
            $q1->where('struktur', 'pusat');
        })->whereHas('ke', function($q2) {
            $q2->where('struktur', 'cabang');
        })->orWhereHas('dari', function($q3) {
            $q3->where('struktur', 'cabang');
        })->whereHas('ke', function($q4) {
            $q4->where('struktur', 'pusat');
        });
    }
    
    /** Scope: Disposisi internal Pusat */
    public function scopeInternalPusat($query) {
        return $query->whereHas('dari', function($q1) {
            $q1->where('struktur', 'pusat');
        })->whereHas('ke', function($q2) {
            $q2->where('struktur', 'pusat');
        });
    }
    
    /** Scope: Disposisi internal Cabang */
    public function scopeInternalCabang($query, $cabangId = null) {
        $query->whereHas('dari', function($q1) {
            $q1->where('struktur', 'cabang');
        })->whereHas('ke', function($q2) {
            $q2->where('struktur', 'cabang');
        });
        
        if ($cabangId) {
            $query->whereHas('dari', function($q3) use ($cabangId) {
                $q3->where('cabang_id', $cabangId);
            });
        }
        
        return $query;
    }

    // ==========================================
    // 🟢 HELPER METHODS: KABAG vs KACAB
    // ==========================================
    
    /** ✅ Cek apakah disposisi ini ditujukan ke KABAG (Pusat) */
    public function isToKabag() {
        return $this->ke && $this->ke->level === 'kabag' && $this->ke->struktur === 'pusat';
    }
    
    /** ✅ Cek apakah disposisi ini ditujukan ke KACAB (Cabang) */
    public function isToKacab() {
        return $this->ke && $this->ke->level === 'kacab' && $this->ke->struktur === 'cabang';
    }
    
    /** ✅ Cek apakah disposisi ini dari KABAG (Pusat) */
    public function isFromKabag() {
        return $this->dari && $this->dari->level === 'kabag' && $this->dari->struktur === 'pusat';
    }
    
    /** ✅ Cek apakah disposisi ini dari KACAB (Cabang) */
    public function isFromKacab() {
        return $this->dari && $this->dari->level === 'kacab' && $this->dari->struktur === 'cabang';
    }
    
    /** ✅ Cek apakah disposisi ini lintas struktur (Pusat ↔ Cabang) */
    public function isCrossStructure() {
        if (!$this->dari || !$this->ke) return false;
        return $this->dari->struktur !== $this->ke->struktur;
    }
    
    /** ✅ Get label struktur untuk display */
    public function getStrukturLabel() {
        if (!$this->dari || !$this->ke) return '-';
        
        if ($this->dari->struktur === $this->ke->struktur) {
            return $this->dari->struktur === 'pusat' ? 'Internal Pusat' : 'Internal Cabang';
        }
        
        return $this->dari->struktur === 'pusat' 
            ? 'Pusat → Cabang' 
            : 'Cabang → Pusat';
    }
    
    /** ✅ Get badge class berdasarkan tipe disposisi */
    public function getTipeBadgeClass() {
        if ($this->isCrossStructure()) {
            return 'bg-purple text-white'; // Lintas struktur
        }
        if ($this->isToKabag() || $this->isFromKabag()) {
            return 'bg-indigo text-white'; // Terkait Kabag/Pusat
        }
        if ($this->isToKacab() || $this->isFromKacab()) {
            return 'bg-teal text-white'; // Terkait Kacab/Cabang
        }
        return 'bg-secondary'; // Default
    }

    // ==========================================
    // 🔹 HELPER METHODS (Existing + Updated)
    // ==========================================
    
    public function isProcessed() {
        return in_array($this->status, array('processed', 'completed', 'rejected'));
    }
    
    public function isActive() {
        return $this->status === 'pending' && (!$this->deadline || $this->deadline->isFuture());
    }
    
    public function canReplyBy(User $user) {
        if ($user->isAdmin() || $user->isDirut()) {
            return true;
        }
        return $this->ke_user_id === $user->id;
    }
    
    public function canForwardBy(User $user) {
        if ($this->ke_user_id !== $user->id) {
            return false;
        }
        if ($this->status !== 'processed' && $this->status !== 'completed') {
            return false;
        }
        return true;
    }
    
    public function getNextValidTargets() {
        $currentReceiver = $this->ke;
        if (!$currentReceiver) {
            return collect();
        }
        return $currentReceiver->getAvailableForwardTargets();
    }
    
    public function getDeadlineLabel() {
        if (!$this->deadline) {
            return '-';
        }
        
        $now = now();
        $diff = $this->deadline->diffInDays($now, false);
        
        if ($diff < 0) {
            return '<span class="text-danger fw-bold">OVERDUE ' . abs($diff) . ' hari</span>';
        } elseif ($diff === 0) {
            return '<span class="text-warning fw-bold">HARI INI</span>';
        } elseif ($diff === 1) {
            return '<span class="text-info">Besok</span>';
        } else {
            return $diff . ' hari lagi';
        }
    }
    
    public function getPriorityBadgeClass() {
        $classes = array(
            'tinggi' => 'bg-danger',
            'sedang' => 'bg-warning text-dark',
            'biasa'  => 'bg-secondary',
            'rendah' => 'bg-info text-dark',
        );
        return isset($classes[$this->prioritas]) ? $classes[$this->prioritas] : 'bg-secondary';
    }
    
    public function getStatusBadgeClass() {
        $classes = array(
            'pending'    => 'bg-warning text-dark',
            'processed'  => 'bg-primary',
            'completed'  => 'bg-success',
            'rejected'   => 'bg-danger',
            'cancelled'  => 'bg-secondary',
        );
        return isset($classes[$this->status]) ? $classes[$this->status] : 'bg-secondary';
    }
    
    // ✅ NEW: Get level badge dengan konteks struktur
    public function getLevelBadgeWithStructure() {
        if (!$this->ke) return '-';
        
        $level = $this->ke->level;
        $struktur = $this->ke->struktur;
        
        $labels = array(
            'kabag' => 'Kabag',
            'kacab' => 'Kacab',
            'kasubag' => 'Kasubag',
            'kasie' => 'Kasie',
            'kanit' => 'Kanit',
            'staff' => 'Staff',
            'dirut' => 'Dirut',
            'admin' => 'Admin',
        );
        
        $label = isset($labels[$level]) ? $labels[$level] : ucfirst($level);
        $strukturLabel = $struktur === 'pusat' ? 'Pusat' : 'Cabang';
        
        return $label . ' <small class="text-muted">(' . $strukturLabel . ')</small>';
    }
}