<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function library_define() {
        return view('user.library.define');
    }

    public function library_essay() {
        return view('user.library.essay');
    }

    public function library_multiple() {
        return view('user.library.multiple');
    }
}
