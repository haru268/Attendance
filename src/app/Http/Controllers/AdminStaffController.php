<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staff = [
            (object)['id' => 1, 'name' => '山田太郎', 'email' => 'yamada@example.com'],
            (object)['id' => 2, 'name' => '佐藤花子', 'email' => 'sato@example.com'],
            (object)['id' => 3, 'name' => '鈴木次郎', 'email' => 'suzuki@example.com'],
        ];
        return view('admin_staff_list', compact('staff'));
    }
}
