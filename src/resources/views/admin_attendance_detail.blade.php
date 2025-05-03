{{-- resources/views/admin_attendance_detail.blade.php --}}
@extends('layouts.app')
@section('title','勤怠詳細（管理者編集）')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endpush

@section('content')
@php $dt = \Carbon\Carbon::parse($detail->date); @endphp

<form action="{{ route('admin.attendance.detail',$detail->id) }}" method="POST">
  @csrf @method('PATCH')

  {{-- 日付 --}}
  <label>日付</label>
  <input type="date" name="date" value="{{ old('date',$dt->toDateString()) }}">
  @error('date') <span class="error">{{ $message }}</span> @enderror

  {{-- 出勤 --}}
  <label>出勤</label>
  <input type="text" name="clock_in" value="{{ old('clock_in',$detail->clockIn) }}">
  @error('clock_in') <span class="error">{{ $message }}</span> @enderror

  {{-- 退勤 --}}
  <label>退勤</label>
  <input type="text" name="clock_out" value="{{ old('clock_out',$detail->clockOut) }}">
  @error('clock_out') <span class="error">{{ $message }}</span> @enderror

  {{-- 備考 --}}
  <label>備考</label>
  <textarea name="remarks">{{ old('remarks',$detail->remarks) }}</textarea>
  @error('remarks') <span class="error">{{ $message }}</span> @enderror

  <button type="submit">修正</button>
</form>
@endsection
