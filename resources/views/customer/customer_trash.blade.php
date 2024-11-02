@extends('layouts.main')

@section('title')
    {{ __('Trash Customer') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first d-flex justify-content-end">



            </div>
        </div>
    </div>
@endsection


@section('content')
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row " id="toolbar">
                    <div class="col-12 col-md-6 col-sm-12 col-lg-3 d-flex  justify-content-start align-items-center">
                        <div class="form-check  ms-3">
                            <input type="checkbox" class="form-check-input" id="selectAllCheck">
                            <label class="form-check-label" for="flexCheckDefault">
                                Select All
                            </label>
                        </div>

                    </div>
                    <div class="col-12 col-md-6 col-sm-12 col-lg-4 d-flex gap-1">
                        <select id="bulkAction" class="form-select">
                            <option value="" selected>{{ __('Action') }}</option>
                            <option value="delete">{{ __('Delete Selected') }}</option>

                        </select>

                        <button id="applyBulkAction" class="btn btn-sm btn-danger">{{ __('Apply') }}</button>
                    </div>




                    {{-- Filter Status --}}
                    <div class=" col-12 col-md-6 col-sm-12  col-lg-4">
                        <select id="statusCus" class="form-select" style="width:145px">
                            <option value="" selected>{{ __('Select Status') }}</option>
                            <option value="0">{{ __('Unverified') }}</option>
                            <option value="1">{{ __('Verified') }}</option>
                        </select>
                    </div>


                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped" id="table_list" data-toggle="table"
                            data-url="{{ url('customerListTrash') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200, All]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams" data-show-export="true"
                            data-export-options='{ "fileName": "data-list-<?= date(' d-m-y') ?>" }'>
                            <thead class="thead-dark">
                                <tr>

                                    <th data-field="id" scope="col" data-align="center" data-formatter="bulkAction">
                                        All
                                    </th>

                                    <th scope="col" data-field="id" data-sortable="true">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="profile" data-sortable="false" data-align="center"
                                        data-formatter="imageFormatter">
                                        {{ __('Profile') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true" data-align="center">
                                        {{ __('Name') }}</th>
                                    <th scope="col" data-field="mobile" data-sortable="true" data-align="center">
                                        {{ __('Number') }}</th>
                                    <th scope="col" data-field="address" data-sortable="false" data-align="center">
                                        {{ __('Address') }}</th>
                                    <th scope="col" data-field="otp_verified" data-sortable="false"
                                        data-formatter="otpStatusFormatter" data-align="center">
                                        {{ __('OTP Verification') }}</th>
                                    <th scope="col" data-field="doc_verification_status"
                                        data-formatter="docVerificationStatusFormatter" data-sortable="false"
                                        data-align="center">
                                        {{ __('Document Verification') }}</th>
                                    <th scope="col" data-field="isActive" data-formatter="enableDisableSwitchFormatter"
                                        data-sortable="false" data-align="center">
                                        {{ __('Enable/Disable') }}
                                    </th>

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </div>
@endsection

@section('script')
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
    </script>



    <script>
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#statusCus').val()
            };
        }






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
                            url: "{{ route('customer.BulkUpdate') }}", // Update this route in your web.php
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                action: action,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Toastify({
                                        text: response.message,
                                        duration: 3000,
                                        gravity: "top", // `top` or `bottom`
                                        position: "right", // `left`, `center` or `right`
                                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                                    }).showToast();

                                    $('#table_list').bootstrapTable('refresh');
                                } else {
                                    Toastify({
                                        text: "Failed to Update",
                                        duration: 3000,
                                        gravity: "top",
                                        position: "right",
                                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                                    }).showToast();
                                }
                            },
                            error: function(xhr) {
                                console.error('An error occurred:', xhr);

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




        $('#statusCus').on('change', function() {
            $('#table_list').bootstrapTable('refresh');
        });

        $(document).ready(function() {
            var params = new URLSearchParams(window.location.search);
            var statusParam = params.get('status');
            console.log("Retrieved status from URL:", statusParam); // Debug log
            if (statusParam !== null && statusParam !== '') {
                $('#statusCus').val(statusParam).trigger('change');
            }
        });

        function docVerificationStatusFormatter(value, row, index) {
            return value == 1 ?
                '<span class="bg-success p-2 rounded-1 text-light fw-bold">Approved</span>' :
                '<span class=" p-2 rounded-1 text-light fw-bold" style="background-color:#f75454">Unapproved</span>';
        }

        function otpStatusFormatter(value, row, index) {
            return value == 1 ?
                '<span class="bg-success p-2 rounded-1 text-light fw-bold">Verified</span>' :
                '<span class=" p-2 rounded-1 text-light fw-bold" style="background-color:#f75454">Unverified</span>';
        }

        function bulkAction(value, row, index) {
            return ` <input type="checkbox" class="form-check-input" name="checkId" value="${row.id}">`;
        }
    </script>
@endsection
