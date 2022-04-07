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

namespace App\Http\Controllers\Layanan;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\LayananSuratDesa;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage; 

class LayananSuratDesaController extends Controller
{
    public const PATHUPLOADSYARAT = "public/syarat/";

    public function index()
    {
        $page_title       = 'Lyanan Surat Desa';
        $page_description = 'Daftar Layanan Surat Desa';
        $surat         = LayananSuratDesa::with(['dataDesa'])->latest()->paginate(10);
        return view('layanan.layanansuratdesa.index', compact('page_title', 'page_description', 'surat'));
    }

     
    public function downloadSurat($idLayanan, $id_desa)
    {
        $getFile = LayananSuratDesa::where([
            'id_sid' => $idLayanan,
            'data_desa_id' => $id_desa
        ] )->firstOrFail();
         
        return response()->download(storage_path('app/'.$getFile->path));
    }

    /**
     * Return view count for all pages on the site.
     * You may supply an integer if you would like to
     * restrict counted views to a certain amount of days.
     *
     * Example: Show total views for the last 30 days
     * Counter::allHits(30)
     *
     * @param object $data
     * @return string
     */

    public function createQrcode($data)
    {
        $surat         = storage_path('app/'.$data->path);
        $input         = $data['input'];
        $config        = $data['config'];
        $foreqr        = '#000000';
        $nama_surat_qr = pathinfo($surat, PATHINFO_FILENAME);
        $check_surat = route("layanan.suratdesa.index");
        $logoqr = public_path('img/logo.png');

        $qrcode = [
            'pathqr' => public_path('img/qrcode/'),
            'namaqr' => $nama_surat_qr,
            'isiqr'  => $check_surat,
            'logoqr' => $logoqr,
            'sizeqr' => 6,
            'foreqr' => $foreqr,
         ];
        // $this->session->qrcode = $qrcode;
        return qrcode_generate($qrcode['pathqr'], $qrcode['namaqr'], $qrcode['isiqr'], $qrcode['logoqr'], $qrcode['sizeqr'], $qrcode['foreqr']);
    }

    public function sisipkan_qrcode($qrcode, &$buffer)
    {
        $awalan_qr = '89504e470d0a1a0a0000000d4948445200000082000000820803000000bddde';
                      
        $akhiran_qr        = 'f010600145f226d416367500000000049454e44ae426082';
        $akhiran_sementara = 'akhiran_qr';
        $jml_qr            = substr_count($buffer, $akhiran_qr);
        if ($jml_qr <= 0) {
            return $buffer;
        }

        $qr_bytes = file_get_contents($qrcode);
        $qr_hex   = implode('', unpack('H*', $qr_bytes));
        for ($i = 0; $i < $jml_qr; $i++) {
            $pos            = strpos($buffer, $akhiran_qr);
            $buffer         = substr_replace($buffer, $akhiran_sementara, $pos, strlen($akhiran_qr));
            $placeholder_qr = '/' . $awalan_qr . '.*' . $akhiran_sementara . '/s';
            $buffer         = preg_replace($placeholder_qr, $qr_hex, $buffer);
        }
    }

    public function setuju(Request $request)
    {
        try {
            $id_sid = $request->id;
            $id_desa = $request->iddesa;
            $layanan = LayananSuratDesa::where([
                'id_sid' => $id_sid,
                'data_desa_id' => $id_desa
            ]);
            $surat = $layanan->first();
            $qrcode = $this->createQrcode($surat);
            $handle = fopen(storage_path('app/'.$surat->path), 'rb');
            $buffer = stream_get_contents($handle);
            $file_qrcode = $this->sisipkan_qrcode($qrcode, $buffer);
            $this->sisipkan_qrcode($file_qrcode, $buffer);
            $rtf = $buffer;
            $path_arsip = storage_path('app/setujui/');
            $berkas_arsip = $path_arsip . $surat->nama_surat;
            $tulis       = fopen(storage_path('app/'.$surat->path), 'w+b');
            fwrite($tulis, $rtf);
            fclose($tulis);

            // update tabel
            $layanan->update([
                'setujui' => 1
            ]);

            return redirect()->route('layanan.suratdesa.index')->with('success', 'dokumen berhasil di setujui');

        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', 'generate file gagal');
        }
    }

    public function dokumenAjax(Request $request)
    {
        if (!$request->ajax()) {
            response()->json(['status' => false, 'message' => 'tidak mempunyai hak akses' ]);
        }
        try {
            $id_sid = (int) $request->id;
            $id_desa = (int) $request->id_desa;
            $layanan = LayananSuratDesa::where([
                'id_sid' => $id_sid,
                'data_desa_id' => $id_desa
            ])->first();
          
            $daftar_Syarat = json_decode($layanan->syarat);
            return response()->json(['status' => true, 'data' => $daftar_Syarat]);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['status' => false, 'message' => $e ]);
        }
    }

    public function downloadSyarat(Request $request)
    {
        $file = $request->file;
        return response()->download(storage_path('app/'.self::PATHUPLOADSYARAT.$file));
    }
 
}
