@extends('layouts.main')

@section('title')
    {{ __('Property') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="d-flex  flex-row-reverse text-center flex-sm-row-reverse flex-md-row justify-content-between">
            <div class="align-self-center order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="order-md-2  order-first d-flex justify-content-end align-items-baseline">
                <div class=" d-flex me-2 justify-content-end">
                    {!! Form::open(['route' => 'property.create']) !!}
                    {{ method_field('get') }}
                    {{ Form::submit(__('Add Property'), ['class' => 'btn btn-primary ']) }}
                    {!! Form::close() !!}
                </div>

                <div class="btn-group d-flex gap-2" style="position: relative">
                    <button id="exportButton" type="button" class="btn text-light "
                        style="border-color:#cac8c9; background-color: #ffffff; border-radius:6px; width:40px; height:40px;">
                        <i class="bi bi-download text-danger"></i>

                    </button>
                    <div id="sideDrawer" class="side-drawer">
                        <form action="{{ url('export/property/') }}" method="GET" class="p-3">
                            @csrf
                            <span class="fw-bold mb-1">Select Date Range</span>

                            <!-- Start Month Input -->
                            <div class="mb-3">
                                <label for="start_month" class="form-label">Start Month</label>
                                <input type="date" id="start_month" name="start_month" class="form-control" required>
                                @error('start_month')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- End Month Input -->
                            <div class="mb-3">
                                <label for="end_month" class="form-label">End Month</label>
                                <input type="date" id="end_month" name="end_month" class="form-control" required>
                                @error('end_month')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Export Button -->
                            <button type="submit" class="btn btn-primary w-100">Export</button>
                        </form>
                    </div>

                    <button type="button" style="border-color:#cac8c9;background-color: #fff; border-radius:6px"
                        class="btn text-light dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fas fa-trash-alt fs-6 text-danger"></i> <!-- Three-dot icon -->
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>

                </div>

            </div>

        </div>
    </div>
@endsection





@section('content')
    <section class="section">
        <div class="card">
            @if (has_permissions('create', 'property'))
                <div class="card-header">
                    <div class="row  justify-content-center align-items-center">

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
                                <option value="0">{{ __('Commercial') }}</option>
                                <option value="1">{{ __('Residential') }}</option>

                            </select>
                        </div>
                        {{-- Add Property Button --}}


                    </div>
                </div>
            @endif

            <hr>
            <div class="card-body">
                <div class="row" id="toolbar">
                    <div class="col-6 col-md-6 col-sm-6 d-flex  justify-content-start align-items-center">
                        <div class="form-check  ms-3">
                            <input type="checkbox" class="form-check-input" id="selectAllCheck">
                            <label class="form-check-label" for="flexCheckDefault">
                                Select All
                            </label>
                        </div>

                    </div>
                    <div class="col-6 col-md-6 col-sm-6  d-flex gap-1">
                        <select id="bulkAction" class="form-select">
                            <option value="" selected>{{ __('Action') }}</option>
                            <option value="delete">{{ __('Delete Selected') }}</option>
                            <option value="activate">{{ __('Activate Selected') }}</option>
                            <option value="deactivate">{{ __('Deactivate Selected') }}</option>
                        </select>

                        <button id="applyBulkAction" class="btn btn-sm btn-danger">{{ __('Apply') }}</button>
                    </div>


                </div>

                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped" id="table_list" data-toggle="table"
                            data-url="{{ url('getPropertyList') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>

                                    <th data-field="id" scope="col" data-align="center" data-formatter="bulkAction">
                                        All
                                    </th>
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
                                    <th scope="col" data-field="featured_property" data-align="center"
                                        data-formatter="enableDisableFeatureSwitchFormatter" data-sortable="false">
                                        {{ __('Is Featured') }}</th>
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
                                    <th scope="col" data-field="otp_verified"
                                        data-formatter="PropertyCustomerStatusFormatter" data-sortable="false"
                                        data-align="center">
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
                        style="width: 100%; height: 80vh; object-fit: contain;" class="img-fluid">
                </div>
            </div>
        </div>
    </div>


@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const exportButton = document.getElementById('exportButton');
            const drawer = document.getElementById('sideDrawer');

            exportButton.addEventListener('click', function() {
                drawer.classList.toggle('active'); // Toggle the drawer's visibility
            });
        });
    </script>

    <script></script>
    <script>
        const selectAllCheckbox = $('#selectAllCheck');

        // Event listener for the Select All checkbox
        selectAllCheckbox.on('click', function() {
            const isChecked = this.checked;
            console.log("Select All clicked. Checked:", isChecked); // Debugging log

            // Check/uncheck all checkboxes
            $('input[name="checkId"]').prop('checked', isChecked);
            console.log(isChecked ? "All rows checked." : "All rows unchecked."); // Debugging log
        });

        // Update Select All checkbox based on individual checkbox changes
        $('input[name="checkId"]').on('change', function() {
            const totalCheckboxes = $('input[name="checkId"]').length;
            const checkedCheckboxes = $('input[name="checkId"]:checked').length;
            selectAllCheckbox.prop('checked', totalCheckboxes === checkedCheckboxes);
            console.log("Checkboxes checked:", checkedCheckboxes, "of", totalCheckboxes); // Debugging log
        });
        $('#applyBulkAction').click(function() {
            var action = $('#bulkAction').val();
            var selectedIds = [];

            // Collect selected IDs
            $('input[name="checkId"]:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if ((action === 'delete' || action === 'activate' || action === 'deactivate') && selectedIds.length >
                0) {
                // Use SweetAlert2 for confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send AJAX request to perform bulk action
                        $.ajax({
                            url: "{{ route('property.BulkUpdate') }}", // Update this route in your web.php
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                action: action,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                console.log('AJAX Success:', response); // Debugging output
                                if (response && response.success) {
                                    Toastify({
                                        text: response.message,
                                        duration: 3000,
                                        gravity: "top",
                                        position: "right",
                                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                                    }).showToast();

                                    $('#table_list').bootstrapTable('refresh');
                                } else {
                                    console.error('Response failure:',
                                        response); // More detailed debugging
                                    Toastify({
                                        text: response.message || "Failed to Update",
                                        duration: 3000,
                                        gravity: "top",
                                        position: "right",
                                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                                    }).showToast();
                                    $('#table_list').bootstrapTable('refresh');
                                }
                            },
                            error: function(xhr) {
                                console.error('AJAX Error:',
                                    xhr); // Log the full error response
                                Toastify({
                                    text: "An error occurred while performing the bulk action.",
                                    duration: 3000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                                }).showToast();
                            }
                        });
                    }
                });
            } else if (selectedIds.length === 0) {
                // Use SweetAlert2 for alert
                Swal.fire({
                    icon: 'info',
                    title: 'No Records Selected',
                    text: 'Please select at least one record.'
                });
            }

        });

        function bulkAction(value, row, index) {
            return ` <input type="checkbox" class="form-check-input" name="checkId" value="${row.id}">`;
        }

        function propertyFeatureFormatter(value, row) {
            // console.log(row.featured_property);

            if (row.featured_property == 0) {
                return '<div class="sell_type btn btn-sm btn-secondary fw-bold">UnFeatured</div>';
            } else if (row.featured_property == 1) {
                return '<div class="rent_type btn btn-sm btn-danger fw-bold">Featured</div>';
            }
        }

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
                        '"><img class="rounded avatar-md shadow " alt="" src="' + imageUrl +
                        '" style="object-fit:cover" width="40px" height="40px"></a>';
                }
            } else {
                // If no image, return an empty string
                return '';
            }
        }

        var baseImageUrl = "{{ asset('images/property_document/') }}";

        function imageFormatterDoc(value, row) {
            // Log the value for debugging

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
                        '"><img class="rounded avatar-md shadow " alt="" src="' + imageUrl +
                        '" style="object-fit:cover" width="40px" height="40px"></a>';
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

        function PropertyCustomerStatusFormatter(value, row, index) {
            return value == 1 ?
                '<span class="bg-success p-2 rounded-1 text-light fw-bold">Verified</span>' :
                '<span class=" p-2 rounded-1 text-light fw-bold" style="background-color:#f75454">Unverified</span>';
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
