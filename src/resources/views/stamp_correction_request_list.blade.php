{{-- resources/views/stamp_correction_request_list.blade.php --}}
@extends('layouts.app')

@section('title', '申請一覧')

@section('content')
<h2>申請一覧</h2>
<table>
  <thead><tr><th>状態</th><th>対象日時</th><th>理由</th><th>申請日時</th><th>詳細</th></tr></thead>
  <tbody>
    @foreach($revisionRequests as $r)
      <tr>
        <td>{{ $r->status }}</td>
        <td>{{ $r->targetDatetime }}</td>
        <td>{{ $r->reason }}</td>
        <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
        <td><a href="{{ route('attendance.detail', $r->id) }}?approval=pending">詳細</a></td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection