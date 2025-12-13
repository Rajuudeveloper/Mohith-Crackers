<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\Customer;
use App\Models\Product;
use App\Models\EstimateItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EstimateController extends Controller
{
    public function viewPdf(Estimate $estimate)
    {
        $pdf = Pdf::loadView('pdf.estimate', compact('estimate'));

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Estimate-' . $estimate->estimate_no . '.pdf"',
        ]);
    }
    public function pdf(Estimate $estimate, $mode = 'view')
    {
        $html = view('pdf.estimate', [
            'estimate' => $estimate,
        ])->render();

        // echo"<pre>";print_r($html);exit;
       
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $disposition = $mode === 'download' ? 'attachment': 'inline';
        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="Estimate-' . $estimate->estimate_no . '.pdf"',
        ]);
    }

    public function create()
    {
        $customers = Customer::all();
        $products  = Product::all();

        $last = Estimate::latest('id')->first();

        if (! $last || ! $last->estimate_no) {
            $estimateNo = 'ES-1';
        } else {
            $number = (int) str_replace('ES-', '', $last->estimate_no);
            $estimateNo = 'ES-' . ($number + 1);
        }

        $data = [
            'customers' => $customers,
            'products' => $products,
            'estimateNo' => $estimateNo
        ];


        return view('estimates.create', $data);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $estimate = new Estimate();
            $estimate->customer_id = $data['customer_id'];
            $estimate->estimate_date = $data['estimate_date'] ?? now()->toDateString();
            $estimate->estimate_no = $data['estimate_no'] ?? null;

            $estimate->sub_total = 0;
            $estimate->tax_amt = 0;
            $estimate->tax_id = null;

            $estimate->packing_percent = $data['packing_percent'] ?? 0;
            $estimate->packing_charges = 0;

            $estimate->is_round_off = !empty($data['is_round_off']) ? 1 : 0;
            $estimate->round_off_amount = 0;
            $estimate->grand_total = 0;
            $estimate->save();

            $subTotal = 0;

            foreach ($data['line_items'] as $li) {
                $product = Product::findOrFail($li['product_id']);

                $packsPerCase = $product->packs_per_case;
                $cases = (float)$li['cases'];
                $qty = $cases * $packsPerCase;
                $price = (float)$li['price'];
                $lineTotal = $qty * $price;

                EstimateItem::create([
                    'estimate_id' => $estimate->id,
                    'product_id'  => $product->id,
                    'uom_name'    => $product->uom_name,
                    'cases'       => $cases,
                    'packs'       => $packsPerCase,
                    'qty'         => $qty,
                    'price'       => round($price, 2),
                    'total'       => round($lineTotal, 2),
                ]);

                $subTotal += $lineTotal;
            }

            // Packing
            $packingPercent = (float)($data['packing_percent'] ?? 0);
            $packingAmount  = ($subTotal * $packingPercent) / 100;

            // Tax (manual or %)
            $taxId  = $data['tax_id'] ?? null;
            $taxAmt = 0;

            if (!empty($data['tax_amt'])) {
                $taxAmt = (float)$data['tax_amt'];
            } elseif (!is_null($taxId)) {
                $taxAmt = ($subTotal * $taxId) / 100;
            }

            // Grand total
            $grand = $subTotal + $packingAmount + $taxAmt;

            $roundOffAmount = 0;
            if (!empty($data['is_round_off'])) {
                $rounded = round($grand);
                $roundOffAmount = $rounded - $grand;
                $grand = $rounded;
            }

            $estimate->sub_total        = round($subTotal, 2);
            $estimate->packing_percent = $packingPercent;
            $estimate->packing_charges = round($packingAmount, 2);
            $estimate->tax_id          = $taxId;
            $estimate->tax_amt         = round($taxAmt, 2);
            $estimate->round_off_amount = round($roundOffAmount, 2);
            $estimate->is_round_off    = !empty($data['is_round_off']) ? 1 : 0;
            $estimate->grand_total     = round($grand, 2);
            $estimate->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'estimate_id' => $estimate->id
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, Estimate $estimate)
    {
        $data = $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $estimate->customer_id   = $data['customer_id'];
            $estimate->estimate_date = $data['estimate_date'] ?? $estimate->estimate_date;
            $estimate->estimate_no   = $data['estimate_no'] ?? $estimate->estimate_no;
            $estimate->save();

            $estimate->items()->delete();

            $subTotal = 0;

            foreach ($data['line_items'] as $li) {
                $product = Product::findOrFail($li['product_id']);

                $packsPerCase = $product->packs_per_case;
                $cases = (float)$li['cases'];
                $qty = $cases * $packsPerCase;
                $price = (float)$li['price'];
                $lineTotal = $qty * $price;

                EstimateItem::create([
                    'estimate_id' => $estimate->id,
                    'product_id'  => $product->id,
                    'uom_name'    => $product->uom_name,
                    'cases'       => $cases,
                    'packs'       => $packsPerCase,
                    'qty'         => $qty,
                    'price'       => round($price, 2),
                    'total'       => round($lineTotal, 2),
                ]);

                $subTotal += $lineTotal;
            }

            $packingPercent = (float)($data['packing_percent'] ?? 0);
            $packingAmount  = ($subTotal * $packingPercent) / 100;

            $taxId  = $data['tax_id'] ?? null;
            $taxAmt = 0;

            if (!empty($data['tax_amt'])) {
                $taxAmt = (float)$data['tax_amt'];
            } elseif (!is_null($taxId)) {
                $taxAmt = ($subTotal * $taxId) / 100;
            }

            $grand = $subTotal + $packingAmount + $taxAmt;

            $roundOffAmount = 0;
            if (!empty($data['is_round_off'])) {
                $rounded = round($grand);
                $roundOffAmount = $rounded - $grand;
                $grand = $rounded;
            }

            $estimate->sub_total        = round($subTotal, 2);
            $estimate->packing_percent = $packingPercent;
            $estimate->packing_charges = round($packingAmount, 2);
            $estimate->tax_id          = $taxId;
            $estimate->tax_amt         = round($taxAmt, 2);
            $estimate->round_off_amount = round($roundOffAmount, 2);
            $estimate->is_round_off    = !empty($data['is_round_off']) ? 1 : 0;
            $estimate->grand_total     = round($grand, 2);
            $estimate->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'estimate_id' => $estimate->id
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Estimate $estimate)
    {
        $customers = Customer::all();
        $products  = Product::all();

        return view('estimates.create', compact('estimate', 'customers', 'products'));
    }

    protected function validateRequest(Request $request): array
    {
        $taxKeys = array_keys(config('taxes') ?: [0 => '0%']); // allowed tax keys from config

        return $request->validate([
            'estimate_no' => ['nullable', 'string'],
            'estimate_date' => ['nullable', 'date'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'packing_percent' => ['nullable', 'numeric', 'min:0'],
            'is_round_off' => ['nullable', 'boolean'],
            'round_off_amount' => ['nullable', 'numeric'],
            'tax_id' => ['nullable', Rule::in($taxKeys)],
            'tax_amt' => ['nullable', 'numeric', 'min:0'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'line_items.*.cases' => ['required', 'numeric', 'min:0'],
            'line_items.*.price' => ['required', 'numeric', 'min:0'],
            // other item fields optional
        ]);
    }
}
