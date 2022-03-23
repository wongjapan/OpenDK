<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananSuratDesa extends Model
{
    protected $table     = 'das_dokumen_sid';

    protected $fillable = ['data_desa_id','id_sid','path', 'nama_surat', 'nik', 'nama_penduduk'];

    public function dataDesa()
    {
        return $this->hasOne(DataDesa::class, "id", "data_desa_id");
    }

    public function getCustomDateAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y H:i');
    }
}
