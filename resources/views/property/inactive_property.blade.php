@extends('layouts.main')

@section('title')
    {{ __('InActive Property') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first"> </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <div class="card">
            @if (has_permissions('create', 'property'))
                <div class="card-header">
                    <div class="row ">
                        {{-- Add Property Button --}}
                        <div class="col-12 col-xs-12 d-flex justify-content-end">
                            {!! Form::open(['route' => 'property.create']) !!}
                            {{ method_field('get') }}
                            {{ Form::submit(__('Add Property'), ['class' => 'btn btn-primary']) }}
                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            @endif

            <hr>
            <div class="card-body">
                <div class="row" id="toolbar">
                    {{-- Filter Category --}}
                    <div class="col-sm-4">
                        <select class="form-select form-control-sm" id="filter_category">
                            <option value="">{{ __('Select Category') }}</option>
                            @if (isset($category))
                                @foreach ($category as $row)
                                    <option value="{{ $row->id }}">{{ $row->category }} </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    {{-- Filter Status --}}
                    <div class="col-sm-4">
                        <select id="status" class="form-select form-control-sm">
                            <option value="">{{ __('Select Status') }} </option>
                            <option value="0">{{ __('InActive') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                        </select>
                    </div>
                    {{-- Filter Status --}}
                    <div class="col-sm-4">
                        <select id="property-type-filter" class="form-select form-control-sm">
                            <option value="">{{ __('Select Type') }} </option>
                            <option value="0">{{ __('Sale') }}</option>
                            <option value="1">{{ __('Rent') }}</option>
                            <option value="2">{{ __('Sold') }}</option>
                            <option value="3">{{ __('Rented') }}</option>
                        </select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped" id="table_list" data-toggle="table"
                            data-url="{{ url('getPropertyListInactive') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-align="center" data-sortable="true">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="added_by" data-align="center" data-sortable="false">
                                        {{ __('Client Name') }}</th>
                                    <th scope="col" data-field="mobile" data-align="center" data-sortable="false">
                                        {{ __('Mobile') }} </th>
                                    <th scope="col" data-field="client_address" data-align="center"
                                        data-sortable="false">{{ __('Client Address') }}</th>
                                    <th scope="col" data-field="title" data-align="center" data-sortable="false">
                                        {{ __('Title') }}</th>
                                    <th scope="col" data-field="address" data-align="center" data-sortable="false">
                                        {{ __('Address') }}</th>
                                    <th scope="col" data-field="category.category" data-align="center"
                                        data-sortable="true"> {{ __('Category') }}</th>
                                    <th scope="col" data-field="propery_type" data-formatter="propertyTypeFormatter"
                                        data-align="center" data-sortable="true"> {{ __('Type') }}</th>
                                    <th scope="col" data-field="document" data-sortable="false" data-align="center"
                                        data-formatter="imageFormatterDoc">{{ __('Document Image') }}</th>
                                    <th scope="col" data-field="status" data-align="center" data-sortable="false">
                                        {{ __('Status') }}</th>
                                    <th scope="col" data-field="title_image" data-formatter="imageFormatter"
                                        data-align="center" data-sortable="false"> {{ __('Image') }}</th>
                                    <th scope="col" data-field="3d_image" data-formatter="imageFormatter3D"
                                        data-align="center" data-sortable="false"> {{ __('3D Image') }}</th>
                                    <th scope="col" data-field="interested_users" data-align="center"
                                        data-sortable="false" data-events="actionEvents">
                                        {{ __('Total Interested Users') }}</th>
                                    <th scope="col" data-field="status" data-sortable="false" data-align="center"
                                        data-width="5%" data-formatter="enableDisableSwitchFormatter">
                                        {{ __('Enable/Disable') }}</th>
                                    <th scope="col" data-field="featured_property"
                                    data-align="center" data-sortable="false"> {{ __('Is Featured') }}</th>
                                    <th scope="col" data-field="is_premium" data-formatter="premium_status_switch"
                                        data-align="center" data-sortable="false"> {{ __('Private/Public') }}</th>
                                    @if (has_permissions('update', 'property_inquiry'))
                                        <th scope="col" data-field="operate" data-align="center"
                                            data-sortable="false"> {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="myModalLabel1">{{ __('Interested Users') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped" id="customer_table_list" data-toggle="table"
                            data-url="{{ url('customerList') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-show-columns="true"
                            data-show-refresh="true" data-trim-on-search="false" data-responsive="true"
                            data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3"
                            data-query-params="customerqueryParams" data-show-export="true"
                            data-export-options='{ "fileName": "data-list-<?= date(' d-m-y') ?>"
                            }'>
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-align="center">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="profile" data-sortable="false" data-align="center"
                                        data-formatter="imageFormatter">
                                        {{ __('Profile') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true" data-align="center">
                                        {{ __('Name') }}</th>
                                    <th scope="col" data-field="mobile" data-sortable="true" data-align="center">
                                        {{ __('Number') }}</th>
                                    <th scope="col" data-field="email" data-sortable="false" data-align="center">
                                        {{ __('Email') }}</th>
                                        <th scope="col" data-field="otp_verified" data-sortable="false" data-align="center">
                                            {{ __('Status') }}</th>



                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>

            </div>

        </div>
        <input type="hidden" id="property_id">

    </section>
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">{{ __('Document Image') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Document"
                        style="width: 100%; height: 100vh; object-fit: contain;" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $('#status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });

        $('#filter_category').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });
        $('#property-type-filter').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });


        // Function to show modal with document image
        function showImageModal(imageUrl) {
            $('#modalImage').attr('src', imageUrl);
            $('#documentModal').modal('show');
        }

        function imageFormatter3D(value, row) {
            // Define the base path for 3D images
            console.log(row.three_d_image);

            // console.log(value)
            // console.log()
            // Ensure the value is not null or empty
            if (row) {
                // Construct the full image path
                var imageUrl = row.three_d_image;

                // Check if the image is an SVG
                if (imageUrl.split('.').pop() === 'svg') {
                    return '<embed class="svg-img" src="' + imageUrl + '">';
                    // For SVG, use the embed tag
                } else {
                    // For other image formats, use the img tag wrapped in an anchor for pop-up
                    return '<a class="image-popup-no-margins" href="' + imageUrl +
                        '"><img class="rounded avatar-md shadow " alt="" src="' + imageUrl + '" style="object-fit:cover" width="45px" height="45px"></a>';
                }
            } else {
                // If no image, return an empty string
                return '';
            }
        }

        var baseImageUrl = "{{ asset('images/property_document/') }}";

        function imageFormatterDoc(value, row) {
            // Log the value for debugging
            console.log(value);

            // Ensure the row and document are valid
            if (row && row.document) {
                // Construct the full image URL using the base path from Blade
                var imageUrl = baseImageUrl + '/' + row.document;

                // Check if the image is an SVG file
                if (imageUrl.split('.').pop() === 'svg') {
                    // For SVG, use the embed tag
                    return '<embed class="svg-img" src="' + imageUrl + '">';
                } else {
                    // For other image formats, use the img tag wrapped in an anchor for pop-up
                    return '<a class="image-popup-no-margins" href="' + imageUrl +
                        '"><img class="rounded avatar-md shadow " alt="" src="' + imageUrl + '" style="object-fit:cover" width="45px" height="45px"></a>';
                }
            } else {
                // If no image is available, return an empty string
                return '';
            }
        }


        $(document).ready(function() {


            var params = new window.URLSearchParams(window.location.search);
            if (params.get('status') != 'null') {
                $('#status').val(params.get('status')).trigger('change');
            }
            if (params.get('type') != 'null') {
                $('#type').val(params.get('type'));
            }
        });


        function queryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#status').val(),
                category: $('#filter_category').val(),
                property_type: $('#property-type-filter').val()
            };
        }




        window.actionEvents = {
            'click .edit_btn': function(e, value, row, index) {

                $('#property_id').val(row.id);
                $('#customer_table_list').bootstrapTable('refresh');

            }
        }

        function customerqueryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                property_id: $('#property_id').val(),
            };
        }
    </script>
@endsection
