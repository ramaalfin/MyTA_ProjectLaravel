<?php

namespace App\Http\Controllers;

use App\Models\SeminarPenelitianNilai;
use Illuminate\Http\Request;

class SeminarPenelitianNilaiController extends Controller
{
    public function index()
    {
        return view('seminar_penelitian_nilai.index', [
            'seminar_penelitian_nilais' => SeminarPenelitianNilai::all(),
        ]);
    }
}