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
        $check_surat = 'route("layanan/suratdesa")';
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

    public function sisipkan_qrcode(&$qrcode, $buffer)
    {
        $awalan_qr = '89504e470d0a1a0a0000000d4948445200000084000000840802000000de';
        $akhiran_qr        = '04c5cd360000000049454e44ae426082';
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
            $placeholder_qr = '/' . $this->awalan_qr . '.*' . $akhiran_sementara . '/s';
            $buffer         = preg_replace($placeholder_qr, $qr_hex, $buffer);
        }
    }

    public function setuju(Request $request)
    {
        $id_sid = $request->id;
        $id_desa = $request->iddesa;
        $surat = LayananSuratDesa::where([
            'id_sid' => $id_sid,
            'data_desa_id' => $id_desa
        ])->first();
        $qrcode = $this->createQrcode($surat);
        // dd(storage_path($surat->path));
        // open file rtf
        $handle = fopen(storage_path('app/'.$surat->path), 'rb');
        $buffer = stream_get_contents($handle);
        $this->sisipkan_qrcode($qrcode, $buffer);
        dd($buffer);

        $handle = fopen($surat, 'w+b');
        fwrite($handle, $buffer);
        fclose($handle);
    }

    public function downloadSurat($file_name)
    {
        $file = Storage::disk('public/sid')->get($file_name);
        return (new Response($file, 200));
    }
}
