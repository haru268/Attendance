{{-- resources/views/admin_stamp_correction_request_approve.blade.php --}}
@extends('layouts.app')
@section('title','修正申請 承認')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_request_approve.css') }}">
@endpush

@section('content')
<form action="{{ route('admin.revision.approve',$detail->id) }}" method="POST">
  @csrf

  {{-- 申請内容は読み取り専用で表示… --}}

  {{-- 承認コメント --}}
  <label>承認コメント</label>
  <textarea name="approval_comment"
            rows="3">{{ old('approval_comment',$detail->approvalComment) }}</textarea>
  @error('approval_comment') <span class="error">{{ $message }}</span> @enderror

  <button type="submit">承認</button>
</form>
@endsection
