@extends('layouts.admin')

@section('title')
    Seminar Proposal
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <h2 class="content-title">Seminar Proposal</h2>
        </div>

        <table id="example" class="display" style="width:100%">
            <thead>
              <tr>
                <th>No</th>
                <th>Mahasiswa</th>
                <th>Judul</th>
                <th>Tempat</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($seminarProposals as $seminarProposal)
            {{-- Berikan warna yang berbeda untuk seminar proposal yang sudah atau belum di nilai --}}
            @if ($pengujiNilai->contains($seminarProposal->id))
                <tr class="bg-light">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $seminarProposal->tugas_akhir->mahasiswa->user->nama }}</a></td>
                    <td>{{ $seminarProposal->tugas_akhir->judul }}</td>
                    <td>{{ $seminarProposal->tempat }}</td>
                    <td>{{ $seminarProposal->tanggal }}</td>
                    <td>{{ $seminarProposal->waktu }}</td>
                    <td><a href="{{ route('seminar-proposal.show', ['seminarProposal' => $seminarProposal->id]) }}" class="btn btn-warning font-bold">Edit</a></td>
                </tr>
            @else
                <tr class="bg-danger">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $seminarProposal->tugas_akhir->mahasiswa->user->nama }}</a></td>
                    <td>{{ $seminarProposal->tugas_akhir->judul }}</td>
                    <td>{{ $seminarProposal->tempat }}</td>
                    <td>{{ $seminarProposal->tanggal }}</td>
                    <td>{{ $seminarProposal->waktu }}</td>
                    <td><a href="{{ route('seminar-proposal.show', ['seminarProposal' => $seminarProposal->id]) }}" class="btn btn-warning font-bold">Edit</a></td>
                </tr>
            @endif
            @endforeach
        </tbody>
        </table>
    </div>
</div>
@endsection
