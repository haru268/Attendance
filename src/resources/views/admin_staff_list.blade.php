{{-- resources/views/admin_staff_list.blade.php --}}
@extends('layouts.app')

@section('title', 'スタッフ一覧（管理者用）')

@section('content')
  <h2>スタッフ一覧</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th><th>名前</th><th>メールアドレス</th><th>詳細</th>
      </tr>
    </thead>
    <tbody>
      @foreach($staff as $member)
        <tr>
          <td>{{ $member->id }}</td>
          <td>{{ $member->name }}</td>
          <td>{{ $member->email }}</td>
          <td><a href="{{ route('admin.attendance.staff', $member->id) }}">詳細</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
