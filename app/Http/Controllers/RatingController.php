<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController
{
    /**
     * Display a listing of the resource for Admin.
     */
    public function index(Request $request)
    {
        $query = Rating::with(['invoice.customer', 'barber']);

        // Optional filtering by barber
        if ($request->has('barber_id') && $request->barber_id) {
            $query->where('barber_id', $request->barber_id);
        }

        // Optional filtering by min shop rating
        if ($request->has('shop_rate') && $request->shop_rate) {
            $query->where('shop_rate', $request->shop_rate);
        }

        // Optional filtering by min barber rating
        if ($request->has('barber_rate') && $request->barber_rate) {
            $query->where('barber_rate', $request->barber_rate);
        }

        $ratings = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'تم عرض التقييمات بنجاح',
            'status' => 200,
            'data' => $ratings,
        ]);
    }

    /**
     * Get basic invoice details for rating.
     */
    public function getInvoiceForRating($invoice_id)
    {
        $invoice = Invoice::with(['customer', 'barber'])->find($invoice_id);

        if (!$invoice) {
            return response()->json([
                'message' => 'الفاتورة غير موجودة',
                'status' => 404,
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer?->customer_name ?? 'عميل زائر',
                'barber_name' => $invoice->barber?->barber_name ?? 'غير محدد',
                'rating_status' => $invoice->rating_status,
                'total_price' => (float) $invoice->total_price,
            ],
        ]);
    }

    /**
     * Store a newly created rating in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'shop_rate' => 'required|integer|min:1|max:5',
            'barber_rate' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::find($request->invoice_id);

        if ($invoice->rating_status === 'submitted') {
            return response()->json([
                'message' => 'عذراً، لقد قمت بتقييم هذه الفاتورة من قبل.',
                'status' => 400,
            ], 400);
        }

        $rating = DB::transaction(function () use ($request, $invoice) {
            $rating = Rating::create([
                'invoice_id' => $invoice->id,
                'barber_id' => $invoice->barber_id, // Store barber_id from invoice for easy querying
                'shop_rate' => $request->shop_rate,
                'barber_rate' => $request->barber_rate,
                'comment' => $request->comment,
            ]);

            $invoice->update([
                'rating_status' => 'submitted',
            ]);

            return $rating;
        });

        return response()->json([
            'message' => 'شكراً لك! تم إرسال تقييمك بنجاح.',
            'status' => 201,
            'data' => $rating,
        ], 201);
    }

    /**
     * Remove the specified rating from storage.
     */
    public function destroy($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json([
                'message' => 'التقييم غير موجود',
                'status' => 404,
            ], 404);
        }

        DB::transaction(function () use ($rating) {
            // Reset invoice rating status to open
            $invoice = Invoice::find($rating->invoice_id);
            if ($invoice) {
                $invoice->update([
                    'rating_status' => 'open',
                ]);
            }
            $rating->delete();
        });

        return response()->json([
            'message' => 'تم حذف التقييم بنجاح وإعادة فتح الفاتورة للتقييم',
            'status' => 200,
        ]);
    }
}
