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

use App\Http\Controllers\Controller;
use App\Models\LayananSuratDesa;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LayananSuratDesaController extends Controller
{
    public function index()
    {
        $page_title       = 'Lyanan Surat Desa';
        $page_description = 'Daftar Layanan Surat Desa';

        $surat         = LayananSuratDesa::with(['dataDesa'])->latest()->paginate(10);
        return view('layanan.layanansuratdesa.index', compact('page_title', 'page_description', 'surat'));
    }

    public function update()
    {
        // return redirect()->route('informasi.regulasi.show', $regulasi->id)->with('success', 'Regulasi berhasil disimpan!');
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

        // dd('app/'.$data->path);
        $surat         = storage_path('app/'.$data->path);
        // dd($surat);
        $input         = $data['input'];
        $config        = $data['config'];
        $foreqr        = '#000000';
        $nama_surat_qr = pathinfo($surat, PATHINFO_FILENAME);
        $check_surat = route("layanan/suratdesa");
        $logoqr = is_logo($this->profil->file_logo);

        $qrcode = [
            'pathqr' => storage_path(),
            'namaqr' => $nama_surat_qr,
            'isiqr'  => $check_surat,
            'logoqr' => $logoqr,
            'sizeqr' => 6,
            'foreqr' => $foreqr,
         ];
        $this->session->qrcode = $qrcode;
        qrcode_generate($qrcode['pathqr'], $qrcode['namaqr'], $qrcode['isiqr'], $qrcode['logoqr'], $qrcode['sizeqr'], $qrcode['foreqr']);

        // ambil logo
        // if ($this->profil->file_logo == '') {
        //     $logo = public_path('img/no-image.png');
        // } else {
        //     $logo          = explode('/',$this->profil->file_logo);
        //     $logo          = end($logo);
        //     $logo          = storage_path('app/public/profil/file_logo/'.$logo);
        // }

        QrCode::eyeColor(0, 255, 100, 255, 0, 0, 0)
        ->errorCorrection('H')
        ->generate($nama_surat_qr, storage_path('app/public/qrcode/'.$nama_surat_qr.'.svg'));
    }

    public function setuju(Request $request)
    {
        $id_sid = $request->id;
        $id_desa = $request->iddesa;
        $surat = LayananSuratDesa::where([
            'id_sid' => $id_sid,
            'data_desa_id' => $id_desa
        ])->first();
        $this->createQrcode($surat);
    }

    public function downloadSurat($file_name)
    {
        $file = Storage::disk('public/sid')->get($file_name);
        return (new Response($file, 200));
    }
}
