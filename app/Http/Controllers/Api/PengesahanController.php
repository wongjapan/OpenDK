<?php

/*
 * File ini bagian dari:
 *
 * OpenDK
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2017 - 2022 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package    OpenDK
 * @author     Tim Pengembang OpenDesa
 * @copyright  Hak Cipta 2017 - 2022 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license    http://www.gnu.org/licenses/gpl.html    GPL V3
 * @link       https://github.com/OpenSID/opendk
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LayananSuratRequest;
use App\Models\DataDesa;
use App\Models\LayananSuratDesa;
use Throwable;

class PengesahanController extends Controller
{
    public const PATHUPLOAD = "public/sid";

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(LayananSuratRequest $request)
    {
        $desa = DataDesa::where('desa_id', '=', $request->kode_desa)->first();
        if ($desa == null) {
            return response()->json(['status' => false, 'message' => 'Desa tidak terdaftar' ]);
        }

        try {
            // Upload file zip temporary.
            $file = $request->file('surat');
            $upload = $file->storeAs(self::PATHUPLOAD, $name = $file->getClientOriginalName());
            LayananSuratDesa::insert([
                'id_sid' => $request->id_sid,
                'data_desa_id' => $desa->id,
                'path' => $upload,
                'nama_surat' => $request->nama_surat,
                'nik' => $request->nik,
                'nama_penduduk' => $request->nama_penduduk,

            ]);
            return response()->json(['status' => true, 'message' => 'berhasil kirim dokumen' ]);
        } catch (Throwable $e) {
            response()->json(['status' => false, 'message' => 'error' ]);
        }
    }
}
