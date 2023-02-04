<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\ProductServiceReturn;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $bills = Bill::where('created_by', \Auth::user()->creatorId())->get();

        $purchaseReturn = PurchaseReturn::with('bill.vender', 'purchaseReturnProduct')->get();

        return view('purchase_return.index', compact('bills', 'purchaseReturn'));
    }

    public function returnDetails(Request $request)
    {
        $bill_details = Bill::where('id', $request->bill_id)->with(["items.product.unit", "vender", "items.product.taxes"])->first();

        return view('purchase_return.billdetails', compact('bill_details'));
    }

    public function store(Request $request)
    {
//       dd($request->all());
        try {
            $returnProducts = $request->all();
            if (count($returnProducts['bill_product_id']) > 0) {
                $purchaseReturn = new PurchaseReturn();
                $purchaseReturn->bill_id = $returnProducts['bill_id'];
                $purchaseReturn->created_by = Auth::user()->id;
                if ($purchaseReturn->save()) {
                    for ($indx = 0; $indx < count($returnProducts['bill_product_id']); $indx++) {
                        // Get product tax, discount, price from Bill Products
                        $billProduct = BillProduct::select('tax', 'discount', 'price')
                            ->where([
                                'bill_id' => $returnProducts['bill_id'],
                                'product_id' => $returnProducts['product_id'][$indx]
                            ])->first();

                        $purchaseReturnProduct = new PurchaseReturnProduct();
                        $purchaseReturnProduct->product_id = $returnProducts['product_id'][$indx];
                        $purchaseReturnProduct->return_quentity = $returnProducts['return_quantity'][$indx];
                        $purchaseReturnProduct->tax = $billProduct->tax;
                        $purchaseReturnProduct->discount = $billProduct->discount;
                        $purchaseReturnProduct->price = $billProduct->price;
                        $purchaseReturnProduct->total_price = ((int)$returnProducts['return_quantity'][$indx] * (int)$billProduct->price);
                        $purchaseReturn->purchaseReturnProduct()->save($purchaseReturnProduct);
                    }
                }
            }

            return redirect()->route('purchase-return-index')->with('success', __('Purchase Return saved.'));
        } catch (Exception $exception) {
            Log::debug("Failed to store purchase return. " . $exception->getMessage());
            return redirect()->back()->with('error', __('Purchase Return save failed.'));
        }
    }

    public function productshowPDF($id)
    {
        $id = Crypt::decrypt($id);

    }
}
