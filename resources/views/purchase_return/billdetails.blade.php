@extends('layouts.admin')
@section('page-title')
    {{__('Bill Details')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Bill Details')}}</li>
@endsection
@push('script-page')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        const downloadPdfs = () => {

            let pdf = new jsPDF('l', 'pt', [1920, 640]);
            pdf.html(document.getElementById('tb_Logbook'), {
                callback: function (pdf) {
                    pdf.save('test.pdf');
                }
            });
        }
    </script>

@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        <h5>Billed From</h5></br>

                        <p>Vendor Name : {{optional($bill_details->vender)->name}}</p>
                        <p>Mobile Number : {{optional($bill_details->vender)->contact}}</p>
                        <p>Bill Date : {{ \Carbon\Carbon::parse($bill_details->created_at)->format('d-m-Y') }}</p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Product')}}</th>
                                <th>{{__('Quantity')}}</th>
                                <th>{{__('Unit')}}</th>
                                <th>{{__('Tax')}}</th>
                                <th>{{__('Discount')}}</th>
                                <th>{{__('Price')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $paymentpath=asset('storage/uploads/payment');
                            @endphp

                            @foreach ($bill_details->items as $item)
                                <tr class="font-style">
                                    <td><input type="checkbox" class="product-items" value="{{ $item->id }}"></td>
                                    <td>{{\Carbon\Carbon::parse($item->product->created_at)->format('d-m-Y') }}</td>
                                    <td class="prod-id" style="display: none">{{$item->product_id}}</td>
                                    <td class="prod-name">{{$item->product->name}}</td>
                                    <td class="prod-quantity">{{$item->quantity}}</td>
                                    <td>{{$item->product->unit->name}}</td>
                                    <td>{{ !empty($item->product->taxes) ? $item->product->taxes->name . ' ' . $item->product->taxes->rate . ' %' : '' }}</td>
                                    <td>{{ !empty($item->product->discount) ? $item->product->discount : '0.00' }}</td>
                                    <td class="prod-price">{{$item->product->purchase_price}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="product_list">
        <form method="post" action="{{ route('purchase-return-store') }}">
            @csrf
            <input type="hidden" name="bill_id" value="{{ $bill_details->bill_id }}">
            <div class="col-6">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Return Quentity</th>
                        <th scope="col">Price</th>
                        <th scope="col">Amount</th>
                    </tr>
                    </thead>
                    <tbody id="product_list_body">
                    </tbody>
                </table>
            </div>
            <div class="col-6 text-right">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </form>
    </div>

@endsection
@push('script-page')
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"
            integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous">
    </script>
    <script>
        $(function () {
            var addedItems = [];
            $(document).on('click', '.product-items', function () {
                let rowDt = [];
                rowDt['itemId'] = $(this).val();
                rowDt['prodId'] = $(this).closest('tr').find('.prod-id').text();
                rowDt['prodName'] = $(this).closest('tr').find('.prod-name').text();
                rowDt['prodQty'] = $(this).closest('tr').find('.prod-quantity').text();
                rowDt['prodPrice'] = $(this).closest('tr').find('.prod-price').text();
                rowDt['totalAmount'] = parseInt(rowDt['prodQty']) * parseInt(rowDt['prodPrice']);

                // find current row id
                if (addedItems.length > 0 && addedItems.includes(rowDt['itemId'])) {
                    $(`.item-row-id-${rowDt['itemId']}`).closest('tr').remove();
                    const index = addedItems.indexOf(rowDt['itemId']);
                    addedItems.splice(index, 1);
                } else {
                    addItems(rowDt);
                    addedItems.push(rowDt['itemId']);
                }
            });

            function addItems(rowDt) {
                $('#product_list_body').append(
                    `<tr>
                    <td class="item-row-id-${rowDt['itemId']}" style="display: none;"><input type="number" name="bill_product_id[]" class="form-control" value="${rowDt['itemId']}"></td>
                    <td style="display: none;"><input type="hidden" name="product_id[]" class="form-control" value="${rowDt['prodId']}"></td>
                    <td>${rowDt['prodName']}</td>
                    <td><input type="number" name="return_quantity[]" class="form-control" value="${rowDt['prodQty']}"></td>
                    <td>${rowDt['prodPrice']}</td>
                    <td>${rowDt['totalAmount']}</td>
                    </tr>`
                );
            }
        });

    </script>
@endpush
