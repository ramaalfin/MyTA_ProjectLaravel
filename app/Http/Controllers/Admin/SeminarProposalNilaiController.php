<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SeminarProposalNilai;
use Illuminate\Http\Request;

class SeminarProposalNilaiController extends Controller
{
    public function index()
    {
        return view('pages.admin.seminar_proposal_nilai.index', [
            'seminar_proposal_nilais' => SeminarProposalNilai::all(),
        ]);
    }
}
