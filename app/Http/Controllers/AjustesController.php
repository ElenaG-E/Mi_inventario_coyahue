<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AjustesController extends Controller
{
    /**
     * Muestra la página de ajustes del sistema
     */
    public function index()
    {
        return view('ajustes');
    }
}

