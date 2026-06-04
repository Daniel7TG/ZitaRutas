<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $screen = $request->query('screen', 'welcome');

        if (in_array($screen, ['routes', 'tracking', 'route-detail'])) {
            $rutas = Ruta::with(['puntosNavegacion' => function ($query) {
                $query->orderBy('id');
            }])->get();
        } else {
            $rutas = Ruta::all();
        }

        return view('welcome', compact('rutas'));
    }
}
