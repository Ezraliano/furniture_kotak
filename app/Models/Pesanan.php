<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Pesanan extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'kode_pesanan', 'pelanggan_id', 'user_id', 'tanggal_pesanan', 'status', 'total_harga', 'catatan'
    ];

    public function pelanggan() {
        return $this->belongsTo(Pelanggan::class);
    }

    public function detailPesanan() {
        return $this->hasMany(DetailPesanan::class);
    }

    public function lampiran() {
        return $this->hasMany(LampiranPesanan::class);
    }
}
