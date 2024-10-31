@extends('layouts.main')

@section('title')
    {{ __('Document Verification') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first d-flex justify-content-end">


                <div class="btn-group">
                    <button id="exportButton" type="button" class="btn text-light me-1"
                        style="border-color:#cac8c9; background-color: #ffffff; border-radius:6px; width:40px; height:40px;">
                        <i class="bi bi-download text-danger"></i>
                    </button>

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
<div id="sideDrawer" class="side-drawer">
    <form action="{{ url('document/export') }}"  class="p-3">
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

<style>
  /* Right-Side Sliding Drawer Styling */
.side-drawer {
    position: fixed;
    top: 190px; /* Aligns with button height */
    right: -300px; /* Initially hidden off-screen on the right */
    width: 220px; /* Adjust width as needed */
    height: auto;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    transition: right 0.4s ease; /* Smooth slide effect */
    z-index: 1050;
    padding: 15px;
}

.side-drawer.active {
    right: 30px; /* Slides in next to the button */
}

</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportButton = document.getElementById('exportButton');
        const drawer = document.getElementById('sideDrawer');

        exportButton.addEventListener('click', function() {
            drawer.classList.toggle('active'); // Toggle the drawer's visibility
        });
    });
</script>



@section('content')
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row" id="toolbar">
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

                            <option value="activate">{{ __('Verify Selected') }}</option>
                            <option value="deactivate">{{ __('Unprove Selected') }}</option>
                        </select>

                        <button id="applyBulkAction" class="btn btn-sm btn-danger">{{ __('Apply') }}</button>
                    </div>
                    {{-- Filter Status --}}
                    <div class=" col-3">
                        <select id="statusCus" class="form-select ">
                            <option value="" selected disabled>{{ __('Select Status') }}</option>
                            <option value="0">{{ __('Unverified') }}</option>
                            <option value="1">{{ __('Verified') }}</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped" id="table_list" data-toggle="table"
                            data-url="{{ url('document-verification') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200, All]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams" data-show-export="true"
                            data-export-options='{ "fileName": "data-list-<?= date('d-m-y') ?>" }'>
                            <thead class="thead-dark">
                                <tr>

                                    <th data-field="id" scope="col" data-align="center" data-formatter="bulkAction">
                                        All
                                    </th>
                                    <th scope="col" data-field="id" data-sortable="true" data-align="center">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="customer_document" data-sortable="false"
                                        data-align="center" data-formatter="imageFormatterd">{{ __('Document Image') }}
                                    </th>
                                    <th scope="col" data-field="name" data-sortable="true" data-align="center">
                                        {{ __('Name') }}</th>
                                    <th scope="col" data-field="mobile" data-sortable="true" data-align="center">
                                        {{ __('Number') }}</th>
                                    <th scope="col" data-field="document_status" data-sortable="false"
                                        data-align="center">{{ __('Document Status') }}</th>
                                    <th scope="col" data-field="customer_document_status"
                                        data-formatter="enableDisableSwitchFormatter" data-sortable="false"
                                        data-align="center">{{ __('Verified/Unproved') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for displaying document image -->
        <!-- Modal for displaying document image -->
        <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel"
            aria-hidden="true">
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

    </section>
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
        // Query parameters for server-side pagination and sorting
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

            if ((action === 'activate' || action === 'deactivate') && selectedIds.length >
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
                            url: "{{ route('document.bulkUpdate') }}", // Update this route in your web.php
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



        // Function to handle the switch state change
        function chk(checkbox) {
            const id = checkbox.id; // Get the ID from the checkbox
            if (checkbox.checked) {
                activate(id); // Call the activate function
            } else {
                deactivate(id); // Call the deactivate function
            }
        }

        // Formatter for Enable/Disable switch
        // Formatter for Enable/Disable switch
        function enableDisableSwitchFormatter(value, row) {
            let status = row.doc_verification_status == 1 ? "checked" : ""; // Check if document is verified
            return `<div class="form-check form-switch" style="padding-left: 5.2rem;">
                <input class="form-check-input switch1" id="${row.id}" onclick="chk(this);" type="checkbox" role="switch" ${status} value="${value}">
            </div>`;
        }


        // Formatter for Document Image with modal trigger
        function imageFormatterd(value, row) {
            return `<img src="${row.user_document}" alt="Document" class="img-thumbnail" style="width: 50px; height: 50px; cursor: pointer;" onclick="showImageModal('${row.user_document}')">`;
        }

        // Function to show modal with document image
        function showImageModal(imageUrl) {
            $('#modalImage').attr('src', imageUrl);
            $('#documentModal').modal('show');
        }

        // Activate and Deactivate functions using AJAX
        function deactivate(id) {
            $.ajax({
                url: "{{ route('document.document_status') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 0,
                },
                cache: false,
                success: function(result) {
                    handleResponse(result, 'Document deactivated successfully');
                },
                error: function(xhr) {
                    console.error(xhr);
                    showErrorNotification(xhr);
                }
            });
        }

        function activate(id) {
            $.ajax({
                url: "{{ route('document.document_status') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 1,
                },
                cache: false,
                success: function(result) {
                    handleResponse(result, 'Document activated successfully');
                },
                error: function(xhr) {
                    console.error(xhr);
                    showErrorNotification(xhr);
                }
            });
        }

        // Handle AJAX response and show Toast notifications
        function handleResponse(result, successMessage) {
            if (result.error == false) {
                Toastify({
                    text: successMessage,
                    duration: 6000,
                    close: true,
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                }).showToast();
                $('#table_list').bootstrapTable('refresh');
            } else {
                Toastify({
                    text: result.message,
                    duration: 6000,
                    close: true,
                    backgroundColor: '#dc3545'
                }).showToast();
            }
        }

        // Show error notification on AJAX error
        function showErrorNotification(xhr) {
            Toastify({
                text: `Error: ${xhr.statusText}`,
                duration: 6000,
                close: true,
                backgroundColor: '#dc3545'
            }).showToast();
        }

        function bulkAction(value, row, index) {
            return ` <input type="checkbox" class="form-check-input" name="checkId" value="${row.id}">`;
        }
    </script>
@endsection
