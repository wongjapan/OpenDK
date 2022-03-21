<?php

namespace App\Http\Controllers\Api;

use App\Models\DataDesa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PengesahanController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {
        $desa = DataDesa::where('desa_id', '=', $request->kode_desa)->first();
        if ($desa == null) {
            return response()->json(['status' => false, 'message' => 'Desa tidak terdaftar' ]);
        }
        
        $this->validate($request, [
            'surat' => 'file|mimes:rtf|max:51200',
        ]);

        try {
            // Upload file zip temporary.
            $file = $request->file('surat');
            $file->storeAs('public/sid', $name = $file->getClientOriginalName());
            
            dd($file);
            

            
        } catch (\Exception $e) {
            echo "eror";
            dd($e);
            return back()->with('error', 'Import data gagal.');
        }
    }

}
