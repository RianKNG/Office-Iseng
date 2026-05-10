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
    'catatan_respon', // WAJIB ADA
    'balasan',        // WAJIB ADA
    'prioritas', 
    'status', 
    'deadline',
    'is_locked',
    'urutan_berjenjang'
    ];

    protected $casts = [
        'deadline' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = ['tanggal_disposisi']; 

    /**
     * Get the letter that owns this disposisi
     */
    public function letter()
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }

    /**
     * Get the user who sent this disposisi
     */
    public function dari()
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    /**
     * Get the user who received this disposisi
     */
    public function ke()
    {
        return $this->belongsTo(User::class, 'ke_user_id');
    }

    /**
     * Get the parent disposisi (if this is a reply/forward)
     */
    public function parent()
    {
        return $this->belongsTo(Disposisi::class, 'parent_id');
    }

    /**
     * Get all child disposisis (replies/forwards)
     */
    public function children()
    {
        return $this->hasMany(Disposisi::class, 'parent_id');
    }

    /**
     * Get all disposisi chain for this letter
     */
    public function disposisiChain()
    {
        return $this->hasMany(Disposisi::class, 'parent_id')
            ->with('dari', 'ke');
    }
}