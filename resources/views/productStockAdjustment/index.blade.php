@extends('layouts.admin')
@section('page-title')
    {{__('Manage Product & Services')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Product & Services')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}
        <a href="#" data-size="md" data-bs-toggle="tooltip" title="{{__('Import')}}"
           data-url="{{ route('productservice.file.import') }}" data-ajax-popup="true"
           data-title="{{__('Import product CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('productservice.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}"
           class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>

        {{--        <a href="#" data-size="lg" data-url="{{ route('productservice.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Stock Adjustment')}}" class="btn btn-sm btn-primary">--}}
        {{--            <i class="ti ti-plus"></i>--}}
        {{--        </a>--}}
        <button href="#" data-size="lg" data-bs-toggle="modal" data-bs-target="#exampleModal"
                title="{{__('Stock Adjustment')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </button>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <form id="productAdjustment" onsubmit="validate()">
                @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add New Adjustment</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 ">
                                    <level>Product <span style="color: red">*</span></level>
                                    <select id="selectValue" class="js-example-basic-single form-control " name="selectValue">
                                    <option value="">Select Product</option>
                                    @foreach ($productServices as $productService)
                                    <option value="{{$productService->id}}">{{$productService->name}}</option>
                                    @endforeach
                                   </select>
                            </div>


                        </div>
                        <div class="row mt-3">
                            <div class="col-6 form-group">
                                <input type="number" class="form-control"  id="quantity" name="quantity" placeholder="Quantity"
                                       required>
                            </div>
                            <div class="col-6  form-group">
                                <select name="adjustment_type" class="form-control">
                                    <option value="add">Add</option>
                                    <option value="sub">Subtract</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
            </div>
            </form>
        </div>

    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 {{isset($_GET['category'])?'show':''}}" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['productservice.index'], 'method' => 'GET', 'id' => 'product_service']) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                    {{ Form::select('category', $category, null, ['class' => 'form-control select','id'=>'choices-multiple', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                   onclick="document.getElementById('product_service').submit(); return false;"
                                   data-bs-toggle="tooltip" title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('productservice.index') }}" class="btn btn-sm btn-danger"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off "></i></span>
                                </a>
                            </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Sku')}}</th>
                                <th>{{__('Sale Price')}}</th>
                                <th>{{__('Purchase Price')}}</th>
                                <th>{{__('Tax')}}</th>
                                <th>{{__('Category')}}</th>
                                <th>{{__('Unit')}}</th>
                                <th>{{__('Quantity')}}</th>
                                <th>{{__('Type')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($productServices as $productService)
                                <tr class="font-style">
                                    <td>{{ $productService->name}}</td>
                                    <td>{{ $productService->sku }}</td>
                                    <td>{{ \Auth::user()->priceFormat($productService->sale_price) }}</td>
                                    <td>{{  \Auth::user()->priceFormat($productService->purchase_price )}}</td>
                                    <td>
                                        @if(!empty($productService->tax_id))
                                            @php
                                                $taxes=\Utility::tax($productService->tax_id);
                                            @endphp

                                            @foreach($taxes as $tax)
                                                {{ !empty($tax)?$tax->name:''  }}<br>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ !empty($productService->category)?$productService->category->name:'' }}</td>
                                    <td>{{ !empty($productService->unit()->first())?$productService->unit()->first()->name:'' }}</td>
                                    <td>{{$productService->quantity}}</td>
                                    <td>{{ $productService->type }}</td>

                                    @if(Gate::check('edit product & service') || Gate::check('delete product & service'))
                                        <td class="Action">
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                   data-url="{{ route('productservice.detail',$productService->id) }}"
                                                   data-ajax-popup="true" data-bs-toggle="tooltip"
                                                   title="{{__('Warehouse Details')}}"
                                                   data-title="{{__('Warehouse Details')}}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                            @can('edit product & service')
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center"
                                                       data-url="{{ route('productservice.edit',$productService->id) }}"
                                                       data-ajax-popup="true" data-size="lg " data-bs-toggle="tooltip"
                                                       title="{{__('Edit')}}" data-title="{{__('Product Edit')}}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete product & service')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['productservice.destroy', $productService->id],'id'=>'delete-form-'.$productService->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para"
                                                       data-bs-toggle="tooltip" title="{{__('Delete')}}"><i
                                                            class="ti ti-trash text-white"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('script-page')
        <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>
        <script>
          let inputData = document.getElementById('quantity').val();
           console.log(inputData);
            $(document).on('submit', '#productAdjustment', function(event) {
                event.preventDefault();
                let About_data = new FormData($('#productAdjustment')[0]);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: `/stockadjustment/product/quentity`,
                    data: About_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $("#exampleModal").trigger("reset");
                        $("#exampleModal").trigger("click");
                        location.reload();
                        // Swal.fire({
                        //     position: 'top-end',
                        //     icon: 'success',
                        //     title: 'Your work has been saved',
                        //     showConfirmButton: false,
                        //     timer: 1500
                        // })

                    },
                });
            });

     function validate()
      {
        var phoneNumber = parseInt(document.getElementById('quantity').value);
        if (phoneNumber < 1) {
            alert("Quantity can not be a negative number or zero");
            $("#exampleModal").trigger("reset");
        }
      }
        </script>
    @endpush

@endsection
