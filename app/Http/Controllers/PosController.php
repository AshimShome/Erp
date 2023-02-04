<?php

namespace App\Http\Controllers;

use App\Mail\SelledInvoice;
use App\Models\Customer;
use App\Models\Pos;
use App\Models\PosPayment;
use App\Models\PosProduct;
use App\Models\ProductService;
use App\Models\User;
use App\Models\Utility;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (Auth::user()->can('manage pos'))
        {
            $customers      = Customer::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'name');
            $customers->prepend('Walk-in-customer', '');
            $warehouses = warehouse::select('*', \DB::raw("CONCAT(name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
//            dd($warehouses);
            //            $warehouses->prepend('Select Warehouse', '');
            $user = Auth::user();
            $details = [
                'pos_id' => $user->posNumberFormat($this->invoicePosNumber()),
                'customer' => $customers != null ? $customers->toArray() : [],
                'user' => $user != null ? $user->toArray() : [],
                'date' => date('Y-m-d'),
                'pay' => 'show',
            ];
//            dd($details['pos_id']);



            return view('pos.index',compact('customers','warehouses','details'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $sess = session()->get('pos');

        if (Auth::user()->can('manage pos') && isset($sess) && !empty($sess) && count($sess) > 0) {
            $user = Auth::user();

            $settings = Utility::settings();

            $customer = Customer::where('name', '=', $request->vc_name)->where('created_by', $user->creatorId())->first();
            $warehouse = warehouse::where('id', '=', $request->warehouse_name)->where('created_by', $user->creatorId())->first();

            $details = [
                'pos_id' => $user->posNumberFormat($this->invoicePosNumber()),
                'customer' => $customer != null ? $customer->toArray() : [],
                'warehouse' => $warehouse != null ? $warehouse->toArray() : [],
                'user' => $user != null ? $user->toArray() : [],
                'date' => date('Y-m-d'),
                'pay' => 'show',
            ];

            if (!empty($details['customer']))
            {
                $warehousedetails = '<h7 class="text-dark">' . ucfirst($details['warehouse']['name'])  . '</p></h7>';
                $details['customer']['billing_state'] = $details['customer']['billing_state'] != '' ? ", " . $details['customer']['billing_state'] : '';
                $details['customer']['shipping_state'] = $details['customer']['shipping_state'] != '' ? ", " . $details['customer']['shipping_state'] : '';

                $customerdetails = '<h6 class="text-dark">' . ucfirst($details['customer']['name']) . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_phone'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_address'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_city'] . $details['customer']['billing_state'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_country'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_zip'] . '</p></h6>';

                $shippdetails = '<h6 class="text-dark"><b>' . ucfirst($details['customer']['name']) . '</b>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_phone'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_address'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_city'] . $details['customer']['shipping_state'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_country'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_zip'] . '</p></h6>';

            }
            else {
                $customerdetails = '<h2 class="h6"><b>' . __('Walk-in Customer') . '</b><h2>';
                $warehousedetails = '<h7 class="text-dark">' . ucfirst($details['warehouse']['name'])  . '</p></h7>';
                $shippdetails = '-';

            }


            $settings['company_telephone'] = $settings['company_telephone'] != '' ? ", " . $settings['company_telephone'] : '';
            $settings['company_state']     = $settings['company_state'] != '' ? ", " . $settings['company_state'] : '';

            $userdetails = '<h6 class="text-dark"><b>' . ucfirst($details['user']['name']) . ' </b> <h2  class="font-weight-normal">' . '<p class="m-0 font-weight-normal">' . $settings['company_name'] . $settings['company_telephone'] . '</p>' . '<p class="m-0 font-weight-normal">' . $settings['company_address'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $settings['company_city'] . $settings['company_state'] . '</p>' . '<p class="m-0 font-weight-normal">' . $settings['company_country'] . '</p>' . '<p class="m-0 font-weight-normal">' . $settings['company_zipcode'] . '</p></h2>';

            $details['customer']['details'] = $customerdetails;
            $details['warehouse']['details'] = $warehousedetails;
//
            $details['customer']['shippdetails'] = $shippdetails;

            $details['user']['details'] = $userdetails;

            $mainsubtotal = 0;
            $sales        = [];

            foreach ($sess as $key => $value) {
                $subtotal = $value['price'] * $value['quantity'];
                $tax      = ($subtotal * $value['tax']) / 100;

                $sales['data'][$key]['name']       = $value['name'];
                $sales['data'][$key]['quantity']   = $value['quantity'];
                $sales['data'][$key]['price']      = Auth::user()->priceFormat($value['price']);
                $sales['data'][$key]['tax']        = $value['tax'] . '%';
                $sales['data'][$key]['tax_amount'] = Auth::user()->priceFormat($tax);
                $sales['data'][$key]['subtotal']   = Auth::user()->priceFormat($value['subtotal']);
                $mainsubtotal                      += $value['subtotal'];
            }

            $discount=!empty($request->discount)?$request->discount:0;
            $sales['discount'] = Auth::user()->priceFormat($discount);
            $total= $mainsubtotal-$discount;
            $sales['sub_total'] = Auth::user()->priceFormat($mainsubtotal);
            $sales['total'] = Auth::user()->priceFormat($total);

            return view('pos.show', compact('sales', 'details'));
        } else {
            return response()->json(
                [
                    'error' => __('Add some products to cart!'),
                ],
                '404'
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $discount=$request->discount;

//        dd($request->all());
        if (Auth::user()->can('manage pos')) {
            $user_id = Auth::user()->creatorId();
            $customer_id      = Customer::customer_id($request->vc_name);
//            dd($customer_id);
            $warehouse_id      = warehouse::warehouse_id($request->warehouse_name);
//            dd($warehouse_id);

            $pos_id       = $this->invoicePosNumber();
            $sales            = session()->get('pos');

            if (isset($sales) && !empty($sales) && count($sales) > 0) {
                $result = DB::table('pos')->where('pos_id', $pos_id)->where('created_by', $user_id)->get();
                if (count($result) > 0) {
                    return response()->json(
                        [
                            'code' => 200,
                            'success' => __('Payment is already completed!'),
                        ]
                    );
                } else {
                    $pos = new Pos();
                    $pos->pos_id       = $pos_id;
                    $pos->customer_id      = $customer_id;
                    $pos->warehouse_id      = $request->warehouse_name;
                    $pos->created_by       = $user_id;
                    $pos->save();

                    foreach ($sales as $key => $value) {
                        $product_id = $value['id'];

                        $product = ProductService::whereId($product_id)->where('created_by', $user_id)->first();

                        $original_quantity = ($product == null) ? 0 : (int)$product->quantity;

                        $product_quantity = $original_quantity - $value['quantity'];


                        if ($product != null && !empty($product)) {
                            ProductService::where('id', $product_id)->update(['quantity' => $product_quantity]);
                        }

                        $tax_id = ProductService::tax_id($product_id);


                        $positems = new PosProduct();
                        $positems->pos_id    = $pos->id;
                        $positems->product_id = $product_id;
                        $positems->price      = $value['price'];
                        $positems->quantity   = $value['quantity'];
                        $positems->tax     = $tax_id;
                        $positems->tax        = $value['tax'];
                        $positems->save();
                    }

                    $posPayment                 = new PosPayment();
                    $posPayment->pos_id          =$pos->id;
                    $posPayment->date           = $request->date;

                    $mainsubtotal = 0;
                    $sales        = [];

                    $sess = session()->get('pos');

                    foreach ($sess as $key => $value) {
                        $subtotal = $value['price'] * $value['quantity'];
                        $tax      = ($subtotal * $value['tax']) / 100;
                        $sales['data'][$key]['price']      = Auth::user()->priceFormat($value['price']);
                        $sales['data'][$key]['tax']        = $value['tax'] . '%';
                        $sales['data'][$key]['tax_amount'] = Auth::user()->priceFormat($tax);
                        $sales['data'][$key]['subtotal']   = Auth::user()->priceFormat($value['subtotal']);
                        $mainsubtotal                      += $value['subtotal'];
                    }
                    $amount = $mainsubtotal;
                    $posPayment->amount         = $amount;
                    $total= $mainsubtotal- $discount;
                    $posPayment->discount         = $discount;
                    $posPayment->discount_amount       = $total;
                    $posPayment->save();

                    session()->forget('pos');

                    return response()->json(
                        [
                            'code' => 200,
                            'success' => __('Payment completed successfully!'),
                        ]
                    );
                }
            } else {
                return response()->json(
                    [
                        'code' => 404,
                        'success' => __('Items not found!'),
                    ]
                );
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function invoicePosNumber()
    {
        if (Auth::user()->can('manage pos')) {
            $latest = Pos::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();


            return $latest ? $latest->pos_id + 1 : 1;
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function report()
    {
        if(\Auth::user()->can('manage pos'))
        {

//            $posPayments = PosProduct::join('pos', 'pos.id', '=', 'pos_products.pos_id')
//                ->with('product')
//                ->get(['pos.*']);
          $posPayments = Pos::where('pos.created_by',  '=', \Auth::user()->creatorId())->join('pos_products', 'pos_products.pos_id', '=', 'pos.id')->join('product_services', 'product_services.id', '=', 'pos_products.product_id')->select('pos.*', 'product_services.name')->get();
//          dd($posPayments);

//          $posProducts = PosProduct::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('pos.report',compact('posPayments'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    function barcode()
    {
        if(\Auth::user()->can('manage pos'))
        {
            $productServices = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get();
            $barcode  = [
                'barcodeType' => Auth::user()->barcodeType() ,
                'barcodeFormat' => Auth::user()->barcodeFormat(),
            ];

            return view('pos.barcode',compact('productServices','barcode'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    public function setting()
    {
        if(\Auth::user()->can('manage pos'))
        {
            $settings                = Utility::settings();

            return view('pos.setting',compact('settings'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    public function BarcodesettingStore(Request $request)
    {

        $request->validate(
                [

                    'barcode_type' => 'required',
                    'barcode_format' => 'required',
                ]
            );

        $post['barcode_type'] = $request->barcode_type;
        $post['barcode_format'] = $request->barcode_format;

        foreach($post as $key => $data)
        {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', $arr
            );
        }
        return redirect()->back()->with('success', 'Barcode setting successfully updated.');

    }

    public function printBarcode()
    {
        if(\Auth::user()->can('manage pos'))
        {
            $warehouses = warehouse::select('*', \DB::raw("CONCAT(name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');


            return view('pos.print',compact('warehouses'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }

    public function getproduct(Request $request)
    {
//        dd($request->all());
        if($request->warehouse_id == 0)
        {
            $productServices = WarehouseProduct::where('product_id', '=', $request->warehouse_id)->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        }
        else
        {
            $productServicesId = WarehouseProduct::where('created_by', '=', \Auth::user()->creatorId())->where('warehouse_id', $request->warehouse_id)->get()->pluck('product_id')->toArray();
            $productServices = ProductService::whereIn('id', $productServicesId )->get()->pluck('name', 'id')->toArray();
        }

        return response()->json($productServices);
    }

    public function receipt(Request $request)
    {

        if(!empty($request->product_id))
        {
            $productServices = ProductService::whereIn('id',$request->product_id)->get();
            $quantity  = $request->quantity;
            $barcode  = [
                'barcodeType' => Auth::user()->barcodeType() == '' ? 'code128' : Auth::user()->barcodeType(),
                'barcodeFormat' => Auth::user()->barcodeFormat() == '' ? 'css' : Auth::user()->barcodeFormat(),
            ];
        }
        else
        {
            return redirect()->back()->with('error', 'Product is required.');

        }

        return view('pos.receipt',compact('productServices','barcode','quantity'));

    }


    public function cartdiscount(Request $request){
        $sess = session()->get('pos');
        $subtotal = array_sum(array_column($sess, 'subtotal'));
        $discount = $request->discount;
        $total = $subtotal - $discount;
        $total = User::priceFormats($total);

        return response()->json(['total' => $total], '200');


    }


}
