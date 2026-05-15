<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    protected $table = 'disposisi';
    
    protected $fillable = [
        'letter_id', 'parent_id', 'dari_user_id', 'ke_user_id',
        'instruksi', 'catatan_respon', 'balasan', 'prioritas',
        'status', 'deadline', 'is_locked', 'urutan_berjenjang',
    ];

    protected $casts = [
        'deadline' => 'date', 
        'is_locked' => 'boolean',
        'created_at' => 'datetime', 
        'updated_at' => 'datetime',
    ];

    // 🔗 RELASI
    public function letter() { return $this->belongsTo(Letter::class, 'letter_id'); }
    public function dari()   { return $this->belongsTo(User::class, 'dari_user_id'); }
    public function ke()     { return $this->belongsTo(User::class, 'ke_user_id'); }
    public function parent() { return $this->belongsTo(Disposisi::class, 'parent_id'); }
    public function children(){ return $this->hasMany(Disposisi::class, 'parent_id'); }

    // 🔹 SCOPES (Pakai relasi cabang->tipe)
    public function scopeToKabag($query) {
        return $query->whereHas('ke', function($q) {
            $q->where('level', 'kabag')
              ->whereHas('cabang', function($c) { $c->where('tipe', 'pusat'); });
        });
    }

    public function scopeToKacab($query) {
        return $query->whereHas('ke', function($q) {
            $q->where('level', 'kacab')
              ->whereHas('cabang', function($c) { $c->where('tipe', 'cabang'); });
        });
    }

    public function scopeInternalPusat($query) {
        return $query->whereHas('dari', function($q1) {
            $q1->whereHas('cabang', function($c) { $c->where('tipe', 'pusat'); });
        })->whereHas('ke', function($q2) {
            $q2->whereHas('cabang', function($c) { $c->where('tipe', 'pusat'); });
        });
    }

    public function scopeInternalCabang($query, $cabangId = null) {
        $query->whereHas('dari', function($q1) {
            $q1->whereHas('cabang', function($c) { $c->where('tipe', 'cabang'); });
        })->whereHas('ke', function($q2) {
            $q2->whereHas('cabang', function($c) { $c->where('tipe', 'cabang'); });
        });

        if ($cabangId) {
            $query->where('dari_user_id', function($q3) use ($cabangId) {
                $q3->select('id')->from('users')->where('cabang_id', $cabangId);
            });
        }
        return $query;
    }

    public function scopeCrossStructure($query) {
        return $query->whereHas('dari', function($q1) {
            $q1->whereHas('cabang', function($c) { $c->where('tipe', 'pusat'); });
        })->whereHas('ke', function($q2) {
            $q2->whereHas('cabang', function($c) { $c->where('tipe', '!=', 'pusat'); });
        })->orWhereHas('dari', function($q3) {
            $q3->whereHas('cabang', function($c) { $c->where('tipe', '!=', 'pusat'); });
        })->whereHas('ke', function($q4) {
            $q4->whereHas('cabang', function($c) { $c->where('tipe', 'pusat'); });
        });
    }

    // 🔹 HELPER METHODS
    public function isToKabag() {
        return $this->ke && $this->ke->level === 'kabag' && $this->ke->isPusat();
    }

    public function isToKacab() {
        return $this->ke && $this->ke->level === 'kacab' && $this->ke->isCabang();
    }

    // ✅ TAMBAHKAN INI (yang sebelumnya hilang):
    public function isFromKabag() {
        return $this->dari && $this->dari->level === 'kabag' && $this->dari->isPusat();
    }

    public function isFromKacab() {
        return $this->dari && $this->dari->level === 'kacab' && $this->dari->isCabang();
    }

    public function isCrossStructure() {
        if (!$this->dari || !$this->ke) return false;
        return $this->dari->isPusat() !== $this->ke->isPusat();
    }

    public function getStrukturLabel() {
        if (!$this->dari || !$this->ke) return '-';
        if ($this->dari->isPusat() && $this->ke->isPusat()) return 'Internal Pusat';
        if ($this->dari->isCabang() && $this->ke->isCabang()) return 'Internal Cabang';
        return $this->dari->isPusat() ? 'Pusat → Cabang/Unit' : 'Cabang/Unit → Pusat';
    }

    // ✅ Get badge class berdasarkan tipe disposisi
    public function getTipeBadgeClass()
    {
        if ($this->isCrossStructure()) {
            return 'bg-purple text-white';
        }
        if ($this->isToKabag() || $this->isFromKabag()) {
            return 'bg-indigo text-white';
        }
        if ($this->isToKacab() || $this->isFromKacab()) {
            return 'bg-teal text-white';
        }
        return 'bg-secondary';
    }

    // 🔹 METHOD LAIN
    public function isProcessed() { 
        return in_array($this->status, array('processed', 'completed', 'rejected')); 
    }
    
    public function isActive() { 
        return $this->status === 'pending' && (!$this->deadline || $this->deadline->isFuture()); 
    }
    
    public function canReplyBy(User $user) { 
        return $user->isAdmin() || $user->isDirut() || $this->ke_user_id === $user->id; 
    }
    
    public function canForwardBy(User $user) { 
        return $this->ke_user_id === $user->id && in_array($this->status, array('processed', 'completed')); 
    }
    
    public function getNextValidTargets() { 
        return $this->ke ? $this->ke->getAvailableForwardTargets() : collect(); 
    }
    
    public function getDeadlineLabel() {
        if (!$this->deadline) return '-';
        $diff = $this->deadline->diffInDays(now(), false);
        if ($diff < 0) return '<span class="text-danger fw-bold">OVERDUE '.abs($diff).' hari</span>';
        if ($diff === 0) return '<span class="text-warning fw-bold">HARI INI</span>';
        return $diff.' hari lagi';
    }

    public function getPriorityBadgeClass() {
        $map = array(
            'tinggi'=>'bg-danger',
            'sedang'=>'bg-warning text-dark',
            'biasa'=>'bg-secondary',
            'rendah'=>'bg-info text-dark'
        );
        return $map[$this->prioritas] ?? 'bg-secondary';
    }

    public function getStatusBadgeClass() {
        $map = array(
            'pending'=>'bg-warning text-dark',
            'processed'=>'bg-primary',
            'completed'=>'bg-success',
            'rejected'=>'bg-danger',
            'cancelled'=>'bg-secondary'
        );
        return $map[$this->status] ?? 'bg-secondary';
    }

    public function getLevelBadgeWithStructure() {
        if (!$this->ke) return '-';
        $levelMap = array(
            'kabag'=>'Kabag','kacab'=>'Kacab','kasubag'=>'Kasubag',
            'kasie'=>'Kasie','kanit'=>'Kanit','staff'=>'Staff',
            'dirut'=>'Dirut','admin'=>'Admin'
        );
        $label = $levelMap[$this->ke->level] ?? ucfirst($this->ke->level);
        $struktur = $this->ke->isPusat() ? 'Pusat' : ($this->ke->isCabang() ? 'Cabang' : 'Unit');
        return $label . ' <small class="text-muted">(' . $struktur . ')</small>';
    }
}