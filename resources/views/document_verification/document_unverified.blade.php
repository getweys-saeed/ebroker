@extends('layouts.main')

@section('title')
    {{ __('Unproved Document') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <!-- Additional page actions or breadcrumbs can go here -->
            </div>
        </div>
    </div>
@endsection

@section('content')
<section class="section">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <table class="table table-striped" id="table_list" data-toggle="table"
                        data-url="{{ url('unverified-document') }}" data-click-to-select="true"
                        data-side-pagination="server" data-pagination="true"
                        data-page-list="[5, 10, 20, 50, 100, 200, All]" data-search="true" data-toolbar="#toolbar"
                        data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                        data-responsive="true" data-sort-name="id" data-sort-order="desc"
                        data-pagination-successively-size="3" data-query-params="queryParams" data-show-export="true"
                        data-export-options='{ "fileName": "data-list-<?= date('d-m-y') ?>" }'>
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-align="center">{{ __('ID') }}</th>
                                <th scope="col" data-field="customer_document" data-sortable="false" data-align="center" data-formatter="imageFormatter">{{ __('Document Image') }}</th>
                                <th scope="col" data-field="name" data-sortable="true" data-align="center">{{ __('Name') }}</th>
                                <th scope="col" data-field="mobile" data-sortable="true" data-align="center">{{ __('Number') }}</th>
                                <th scope="col" data-field="document_status" data-sortable="false" data-align="center">{{ __('Document Status') }}</th>
                                <th scope="col" data-field="customer_document_status" data-formatter="enableDisableSwitchFormatter" data-sortable="false" data-align="center">{{ __('Verified/Unproved') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for displaying document image -->
  <!-- Modal for displaying document image -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">{{ __('Document Image') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" alt="Document" style="width: 100%; height: 100vh; object-fit: contain;" class="img-fluid">
            </div>
        </div>
    </div>
</div>

</section>
@endsection

@section('script')
    <script>
        // Query parameters for server-side pagination and sorting
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }

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
        function imageFormatter(value, row) {
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
    </script>
@endsection
