@extends('layouts.admin')
@section('page-title')
    {{__('Manage Purchased Product Return')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Product Return')}}</li>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#bill', function () {

            var id = $(this).val();
            var url = "{{route('bill.get')}}";

            $.ajax({
                url: url,
                type: 'get',
                cache: false,
                data: {
                    'bill_id': id,

                },
                success: function (data) {
                    $('#amount').val(data)
                },

            });

        })
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
{{--        @can('create debit note')--}}
            <a href="#"   data-title="{{__('Purchase Return')}}" data-bs-toggle="modal" data-bs-target="#billReturnInvoiceId" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus">Purchase Return</i>
            </a>

{{--        @endcan--}}
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Bill')}}</th>
                                <th> {{__('Vendor')}}</th>
                                <th> {{__('Mobile No')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Description')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($purchaseReturn as $item)
                                <tr class="font-style">
{{--                                    <td>{{ AUth::user()->billNumberFormat($item->alias_id) }}</td>--}}
                                    <td> <a href="{{ route('return.product.show',\Crypt::encrypt($item->alias_id)) }}" class="btn btn-outline-primary">{{ AUth::user()->billNumberFormat($item->alias_id) }}</a></td>
                                    <td>{{$item->bill->vender->name}}</td>
                                    <td>{{$item->bill->vender->contact}}</td>
                                    <td>{{\Carbon\Carbon::parse($item->created_at)->format('d-m-Y')}}</td>
                                    <td>--</td>
                                    <td>{{$item->alias}}</td>


                                    <td class="action text-end">
                                            <div class="action-btn bg-primary ms-2">
{{--                                                <a href="{{route('edit.return.product',$item->id)}}" class="mx-3 btn btn-sm align-items-center" data-url="#"  data-title="{{__('Edit Payment')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">--}}
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <button href="#"  onclick="myFunction({{$item->id}})" id="deleteRecord" class="mx-3 btn btn-sm align-items-center  " >
                                                    <i class="ti ti-trash text-white"></i>
                                                </button>
                                            </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="billReturnInvoiceId" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create new Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productBillReturnId" method="post" action="{{route('purchase-return-details')}}"  >
                        @csrf
                        <div class="input-group">
                            <select class="form-control" name="bill_id">
                                <option value="">Select One</option>
                                @foreach($bills as $bill)
                                    <option value="{{$bill->bill_id}}">{{ AUth::user()->billNumberFormat($bill->bill_id) }}</option>
                                @endforeach
                            </select>
                            <button  type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </div>

@endsection
@push('script-page')
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"> </script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function myFunction(id){
          var id = id;
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax(
                        {
                            url: `/delete/return/product/`+id,
                            type: 'get',
                            dataType:"json",

                            success: function (){
                                window.location.reload();
                                console.log("it Works");
                            }
                        });
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )
                }
            })

        }

    </script>
@endpush
