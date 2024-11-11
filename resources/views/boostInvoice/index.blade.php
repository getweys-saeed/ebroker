@extends('layouts.main')

@section('title')
    {{ __('Boost Property Invoices') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
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
                            data-url="{{ url('listBoostPropertyInvoices') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-align="center" data-sortable="true">
                                        {{ __('ID') }}</th>



                                    <th scope="col" data-field="tilte" data-formatter="ti" data-align="center"
                                        data-sortable="true">
                                        {{ __('Property Name') }}</th>
                                    <th scope="col" data-field="name" data-align="center" data-sortable="true">
                                        {{ __('Customer Name') }}</th>


                                    <th scope="col" data-field="payment_screenshot" data-formatter="imageFormatter"
                                        data-align="center" data-sortable="false">
                                        {{ __('ScreenShot') }}
                                    </th>
                                    <th scope="col" data-field="order_id" 
                                        data-align="center" data-sortable="false">
                                        {{ __('payment_status') }}
                                    </th>
                                    <th scope="col" data-field="ispayed" data-align="center"
                                        data-formatter="booststatusFormator" data-sortable="false">
                                        {{ __('Payment') }}
                                    </th>
                                    <th scope="col" data-field="payment_status"
                                        data-formatter="enableDisableSwitchFormatter" data-sortable="false"
                                        data-align="center" data-width="5%">
                                        {{ __('Payed/UnPayed') }}</th>



                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        // Refresh table on status change
        $('#status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');
        });

        // Set URL parameters for server-side queries
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#status').val(),
            };
        }

        function ti(value, row) {

            console.log(row);
            return row.property.title;

        }




        // Formatter for the image column
    //     function imageFormatter(value, row) {
    //         console.log(value);
       
        
    //     if (value) {
    //         return '<img src="' + value + '" alt="Image" style="width: 50px; height: 50px;">';
    //     }
    //     return '-';
    // }

        // Formatter for the enable/disable switch column


        // Toggle enable/disable status


        $(document).ready(function() {
            // Pre-select status filter if URL has parameter
            var params = new URLSearchParams(window.location.search);
            if (params.get('status')) {
                $('#status').val(params.get('status')).trigger('change');
            }
        });

        function statusFormator(value, row) {
            let buttonHTML = ''; // Initialize an empty variable to store the button HTML
            // Check the value using if-else conditions
            if (value === 0) {
                buttonHTML = '<button class="btn btn-sm btn-primary">Pending</button>';
            } else if (value === 1) {
                buttonHTML = '<button class="btn btn-sm btn-success">Success</button>';
            } else if (value === 2) {
                buttonHTML = '<button class="btn btn-sm btn-danger">Rejected</button>';
            } else {
                buttonHTML = '<button class="btn btn-sm btn-warning">Unknown</button>';
            }

            // Return the button HTML
            return buttonHTML;
        }

        function booststatusFormator(value, row) {
            let buttonHTML = ''; // Initialize an empty variable to store the button HTML
            // Check the value using if-else conditions
            if (value === 0) {
                buttonHTML = '<button class="btn btn-sm btn-danger">UnPayed</button>';
            } else {
                buttonHTML = '<button class="btn btn-sm btn-success">Payed</button>';
            }

            // Return the button HTML
            return buttonHTML;
        }

        function paymentGatewayFormatter(value, row) {
            let buttonHTML = ''; // Initialize an empty variable to store the button HTML
            // Check the value using if-else conditions
            if (value === 0) {
                buttonHTML = '<button class="btn btn-sm btn-success">Easy Paisa</button>';
            } else if (value === 1) {
                buttonHTML = '<button class="btn btn-sm btn-primary">Jazz Cash</button>';
            } else if (value === 2) {
                buttonHTML = '<button class="btn btn-sm btn-secondary">Bank Account</button>';
            } else {
                buttonHTML = '<button class="btn btn-sm btn-warning">Unknown</button>';
            }

            // Return the button HTML
            return buttonHTML;
        }
    </script>
@endsection