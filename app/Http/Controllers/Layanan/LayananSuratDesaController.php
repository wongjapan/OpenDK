<?php

namespace App\Http\Controllers\Layanan;

use Illuminate\Http\Request;
use App\Models\LayananSuratDesa;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class LayananSuratDesaController extends Controller
{
    public function index()
    {
        $page_title       = 'Dokumen SID';
        $page_description = 'Daftar Dokumen SID yang Masuk';

        $surat         = LayananSuratDesa::with(['dataDesa'])->latest()->paginate(10); // TODO : Gunakan datatable
        
        return view('layanan.layanansuratdesa.index', compact('page_title', 'page_description', 'surat'));
    }
    
    public function update()
    {
        // return redirect()->route('informasi.regulasi.show', $regulasi->id)->with('success', 'Regulasi berhasil disimpan!');
    }
}
