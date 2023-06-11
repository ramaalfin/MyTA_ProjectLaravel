<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\DosenPembimbing;
use App\Models\Mahasiswa;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasAkhirController extends Controller
{
    public function index()
    {
        if (Auth::user()->role->nama == 'mahasiswa') {
            $mahasiswaId = Auth::user()->mahasiswa->id; // get id mahasiswa yang login
            $tugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswaId)->get();
        }

        elseif (Auth::user()->role->nama == 'dosen' && Auth::user()->dosen->dosen_pembimbings->pluck('id')){
            $dosenPembimbingId = Auth::user()->dosen->dosen_pembimbings->pluck('id'); // get id dosen pembimbing yang login
            $tugasAkhir = TugasAkhir::where('dosen_pembimbing_id', $dosenPembimbingId)->get();
        }

        else {
            $tugasAkhir = TugasAkhir::all();
        }

        return view('pages.dashboard.tugas_akhir.index', [
            'tugasAkhirs' => $tugasAkhir,
        ]);
    }

    public function show(TugasAkhir $tugasAkhir)
    {
        if (Auth::user()->dosen) {
            $selectedDosenPembimbing = $tugasAkhir->dosen_pembimbing; // mendapatkan data dosen pembimbing terkait.
            $dosenPembimbingId = Auth::user()->dosen->dosen_pembimbings->pluck('id')->toArray();

            if (in_array($selectedDosenPembimbing->id, $dosenPembimbingId)) {
                return view('pages.dashboard.tugas_akhir.show', [
                    'tugasAkhir' => $tugasAkhir,
                    'selectedDosenPembimbing' => $selectedDosenPembimbing
                ]);
            }
        } elseif (Auth::user()->mahasiswa) {
            $selectedDosenPembimbing = $tugasAkhir->dosen_pembimbing; // mendapatkan data dosen pembimbing terkait.

            if ($tugasAkhir->mahasiswa_id === Auth::user()->mahasiswa->id) {
                return view('pages.dashboard.tugas_akhir.show', [
                    'tugasAkhir' => $tugasAkhir,
                    'selectedDosenPembimbing' => $selectedDosenPembimbing
                ]);
            }
        } elseif (Auth::user()->role->nama === 'admin') {
            $dosenPembimbings = DosenPembimbing::with('dosen.user')->get();

            $selectedDosenPembimbing = $tugasAkhir->dosen_pembimbing_id; // mendapatkan data dosen pembimbing di tabel tugas akhir.

            return view('pages.dashboard.tugas_akhir.show', [
                'tugasAkhir' => $tugasAkhir,
                'dosenPembimbings' => $dosenPembimbings,
                'selectedDosenPembimbing' => $selectedDosenPembimbing
            ]);
        }

        return redirect()->route('tugas-akhir.index')->with('error', 'Anda tidak memiliki izin untuk mengakses tugas akhir ini.');
    }


    public function create()
    {
        return view('pages.dashboard.tugas_akhir.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|min:3',
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        // Cek apakah mahasiswa sudah memiliki tugas akhir
        $mahasiswa = auth()->user()->mahasiswa;
        $existingTugasAkhir = TugasAkhir::where('mahasiswa_id', $mahasiswa->id)->first();

        if ($mahasiswa && $existingTugasAkhir) {
            return redirect()->route('tugas-akhir.index')->with('error', 'Anda sudah memiliki tugas akhir.');
        } else {

            // Simpan file tugas akhir
            // $path = $request->file('file')->store('tugas-akhir', 'public');
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('public/tugas-akhir/' . $mahasiswa->id, $fileName);

            // Buat data tugas akhir baru
            $tugasAkhir = TugasAkhir::create([
                'judul' => $request->judul,
                'file' => $filePath,
                'mahasiswa_id' => $mahasiswa->id,
            ]);
        }

        return redirect()->route('tugas-akhir.show', ['tugasAkhir' => $tugasAkhir->id])->with('success', 'Berhasil ditambahkan.');
    }

    public function update(Request $request, TugasAkhir $tugasAkhir)
    {
        $request->validate([
            'judul' => 'required|string|min:3',
            'file' => 'nullable|mimes:pdf|max:2048',
        ]);

        $user = auth()->user();
        if ($user->mahasiswa && $user->mahasiswa->id === $tugasAkhir->mahasiswa_id) {

            $tugasAkhir->judul = $request->judul;

            // Cek apakah ada file file yang diunggah
            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($tugasAkhir->file) {
                    Storage::delete($tugasAkhir->file);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('public/tugas-akhir/' . $user->mahasiswa->id, $fileName);

                $tugasAkhir->file = $filePath;
            }
            $tugasAkhir->save();
            return redirect()->route('tugas-akhir.show', ['tugasAkhir' => $tugasAkhir])->with('success', 'Berhasil mengubah data tugas akhir.');
        }
        elseif ($user->role->nama == 'admin') {
            // Ambil request form daftar dosen pembimbing
            $selectedDosenPembimbing = $request->input('dosen_pembimbing_', []);

            // Tambahkan dosen pembimbing ke data tugas akhir mahasiswa
            foreach ($selectedDosenPembimbing as $dosenPembimbing) {
                $dosenPembimbing = DosenPembimbing::find($dosenPembimbing);

                if ($dosenPembimbing) {
                    $tugasAkhir->update([
                        'dosen_pembimbing_id' => $dosenPembimbing->id
                    ]);
                }
            }
        }
        elseif ($user->dosen->dosen_pembimbings->pluck('id')->first() === $tugasAkhir->dosen_pembimbing_id) {
            $request->validate([
                'status_persetujuan' => 'required|in:Disetujui,Tidak Disetujui'
            ]);
            $tugasAkhir->status_persetujuan = $request->status_persetujuan;
            $tugasAkhir->save();

            return redirect()->route('tugas-akhir.show', ['tugasAkhir' => $tugasAkhir])->with('success', 'Berhasil mengubah status persetujuan');
        }
        return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah data tugas akhir ini.');
    }
}
