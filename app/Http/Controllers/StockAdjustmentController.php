<?php

namespace App\Http\Controllers;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
//return view('productStockAdjustment.index');

    public function index()
    {
//dd("ok");

        if (\Auth::user()->can('manage product & service')) {
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 0)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            if (!empty($request->category)) {

                $productServices = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('category_id', $request->category)->get();
            } else {
                $productServices = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('productStockAdjustment.index', compact('productServices', 'category'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function productQty(Request  $request)
    {

        if (\Auth::user()->can('manage product & service')) {
            $productServicesQty = ProductService::findOrFail($request->selectValue);

            if ($request->adjustment_type == "add") {
                $qty = $productServicesQty->quantity + $request->quantity;
                $productServicesQty->quantity = $qty;
                $productServicesQty->save();

            } elseif ($request->adjustment_type == "sub") {
                $qty = $productServicesQty->quantity - $request->quantity;
                $productServicesQty->quantity = $qty;
                $productServicesQty->save();
            }

            return response()->json([
                'message' => 'Product SuccessFully Adjustment '
            ]);
        }
    }
}
