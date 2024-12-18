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

        // Check if any search parameter is provided
        $isSearching = $request->filled('search_dui') ||
            $request->filled('search_name') ||
            $request->filled('search_email') ||
            $request->filled('search_phone');

        if ($isSearching) {
            // Search by DUI if provided
            if ($request->filled('search_dui')) {
                $dui = $request->search_dui;
                // Validate DUI format (00000000-0)
                if (preg_match('/^[0-9]{8}-[0-9]$/', $dui)) {
                    $query->where('document_number', $dui);
                }
            }

            // Search by name if provided
            if ($request->filled('search_name')) {
                $query->where('full_name', 'ILIKE', '%' . $request->search_name . '%');
            }

            // Search by email if provided
            if ($request->filled('search_email')) {
                $email = trim($request->search_email);
                $query->where('email', 'ILIKE', '%' . $email . '%');
            }

            // Search by phone if provided
            if ($request->filled('search_phone')) {
                $phone = trim($request->search_phone);

                // Remove hyphen if present
                $cleanPhone = str_replace('-', '', $phone);

                // Check if the phone number is in valid format (with or without hyphen)
                if (preg_match('/^[0-9]{8}$/', $cleanPhone)) {
                    // Search for both formats: with and without hyphen
                    $formattedPhone = substr($cleanPhone, 0, 4) . '-' . substr($cleanPhone, 4);

                    $query->where(function($q) use ($cleanPhone, $formattedPhone) {
                        $q->where('phone', $formattedPhone)  // Format: 0000-0000
                        ->orWhere('phone', $cleanPhone);   // Format: 00000000
                    });
                }
            }

            // Get all matching results for search
            $transactions = $query->orderBy('start_date', 'desc')->get();
        } else {
            // If no search parameters, only get the 50 most recent transactions
            $transactions = $query->latest('start_date')
                ->limit(50)
                ->get();
        }

        // Pass results to view
        return view('transacciones', compact('transactions'));
    }

}
