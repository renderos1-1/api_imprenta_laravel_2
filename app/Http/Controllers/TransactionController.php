<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;


// In your controller:


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Start building the query
        $query = Transaction::query();

        // If there's a search query (either DUI or name), we'll search the entire database
        if ($request->filled('search_dui') || $request->filled('search_name')) {

            // Search by DUI if provided
            if ($request->filled('search_dui')) {
                $dui = $request->search_dui;
                if (preg_match('/^[0-9]{8}-[0-9]$/', $dui)) {
                    $query->where('document_number', $dui);
                }
            }

            // Search by name if provided
            if ($request->filled('search_name')) {
                $query->where('full_name', 'ILIKE', '%' . $request->search_name . '%');
            }

            // Get all matching results for search
            $transactions = $query->orderBy('created_at', 'desc')->get();
        } else {
            // If no search parameters, only get the 50 most recent transactions
            $transactions = $query->latest('created_at')
                ->limit(50)
                ->get();
        }

        // Pass results to view
        return view('transacciones', compact('transactions'));
    }

}
