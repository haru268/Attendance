@extends('layouts.app')
@section('title','管理者：勤怠一覧')
@section('content')
  <h2>管理者：勤怠一覧</h2>
  <div>
    <a href="{{ route('admin.attendance.list',['date'=>\Carbon\Carbon::parse(request('date'))->subDay()->format('Y-m-d')]) }}">前日</a>
    {{ \Carbon\Carbon::parse(request('date',now()))->format('Y/m/d') }}
    <a href="{{ route('admin.attendance.list',['date'=>\Carbon\Carbon::parse(request('date'))->addDay()->format('Y-m-d')]) }}">翌日</a>
  </div>
  <table border="1">
    <thead><tr><th>名前</th><th>出勤</th><th>退勤</th><th>休憩</th><th>合計</th><th>詳細</th></tr></thead>
    <tbody>
      @forelse($attendances as $a)
        <tr>
          <td>{{ $a->user->name }}</td>
          <td>{{ $a->clockIn }}</td>
          <td>{{ $a->clockOut }}</td>
          <td>{{ $a->breakTime }}</td>
          <td>{{ $a->totalTime }}</td>
          <td><a href="#">詳細</a></td>
        </tr>
      @empty
        <tr><td colspan="6">今日出勤している人はいません。</td></tr>
      @endforelse
    </tbody>
  </table>
@endsection
