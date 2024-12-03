<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // ... tus métodos
    public function index(Request $request)
    {
        $query = Transaction::query();

        // Búsqueda por DUI si se proporciona
        if ($request->filled('search_dui')) {
            $dui = $request->search_dui;
            if (preg_match('/^[0-9]{8}-[0-9]$/', $dui)) {
                $query->where('document_number', $dui);
            }
        }

        // Búsqueda por nombre si se proporciona
        if ($request->filled('search_name')) {
            $query->where('full_name', 'ILIKE', '%' . $request->search_name . '%');
        }

        // Obtener resultados paginados
        $transactions = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Pasar los resultados a la vista
        return view('transacciones', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = Transaction::findOrFail($id);
        return view('transacciones.show', compact('transaction'));
    }
}
