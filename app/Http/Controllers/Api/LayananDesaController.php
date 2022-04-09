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
use App\Http\Requests\SuratVerifRequest;
use App\Models\LayananSuratDesa;
use Exception;

class LayananDesaController extends Controller
{
    public const PATHUPLOAD = "public/sid";
    public const PATHUPLOADSYARAT = "public/syarat";

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
        $desa = verif_desa($request->kode_desa);
        if (!$desa) {
            return response()->json(['status' => false, 'message' => 'Desa tidak terdaftar' ]);
        }

        try {
            // Upload file zip temporary.
            $file = $request->file('surat');
            $upload = $file->storeAs(self::PATHUPLOAD, $name = $file->getClientOriginalName());

            // Upload file syarata
            $file_syarat = [];
            $daftar_syarat = json_decode($request->daftar_syarat);
            foreach ($request->syarat as $key => $filesyarat) {
                $upload_syarat = $filesyarat->storeAs(self::PATHUPLOADSYARAT, $name = $filesyarat->getClientOriginalName());
                array_push(
                    $file_syarat,
                    [
                        'path' => $filesyarat->getClientOriginalName(),
                        'nama' => $daftar_syarat[$key]->syarat_nama
                    ]
                );
            }

            // load file lampiran jika ada
            if (isset($request->lampiran)) {
                $lampiran = $request->file('lampiran');
                $upload_lampiran = $lampiran->storeAs(self::PATHUPLOAD, $name = $lampiran->getClientOriginalName());
            }

            LayananSuratDesa::insert([
                'id_sid' => $request->id_sid,
                'data_desa_id' => $desa->id,
                'path' => $upload,
                'nama_surat' => $request->nama_surat,
                'nik' => $request->nik,
                'nama_penduduk' => $request->nama_penduduk,
                'syarat' => json_encode($file_syarat),
                'lampiran' => $upload_lampiran ?? null
            ]);
            return response()->json(['status' => true, 'message' => 'berhasil kirim dokumen' ]);
        } catch (Exception $e) {
            report($e);
            response()->json(['status' => false, 'message' => 'error' ]);
        }
    }

    public function listSuratVerif(SuratVerifRequest $request)
    {
        $desa = verif_desa($request->kode_desa);

        if (!$desa) {
            return response()->json(['status' => false, 'message' => 'Desa tidak terdaftar' ]);
        }

        try {
            $layanan = LayananSuratDesa::where([
                'data_desa_id' => $desa->id,
                'setujui' => 1
                ])
                ->get();

            return response()->json(['status' => true, 'data' => $layanan ]);
        } catch (Exception $e) {
            report($e);
            return response()->json(['status' => false, 'message' => 'error' ]);
        }
    }
}
