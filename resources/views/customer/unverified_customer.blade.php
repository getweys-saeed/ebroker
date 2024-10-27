@extends('layouts.main')

@section('title')
    {{ __('UnVerified Customer') }}
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
                        <table class="table table-striped" id="table_list"
                            data-toggle="table" data-url="{{ url('customerListUnverified') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams" data-show-export="true"
                            data-export-options='{ "fileName": "data-list-<?= date(' d-m-y') ?>" }'>
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
                                    <th scope="col" data-field="address" data-sortable="false" data-align="center">
                                        {{ __('Address') }}</th>
                                        <th scope="col" data-field="otp_verified" data-sortable="false" data-formatter="otpStatusFormatter" data-align="center">
                                            {{ __('OTP Verification') }}</th>
                                    <th scope="col" data-field="doc_verification_status" data-formatter="docVerificationStatusFormatter" data-sortable="false"
                                        data-align="center">
                                        {{ __('Document Verification') }}</th>
                                    <th scope="col" data-field="isActive" data-formatter="enableDisableSwitchFormatter"
                                        data-sortable="false" data-align="center">
                                        {{ __('Enable/Disable') }}
                                    </th>

                                    <!--<th scope="col" data-field="operate" data-sortable="false" data-align="center">-->
                                    <!--    {{ __('Action') }}</th>-->
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
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }

        function docVerificationStatusFormatter(value, row, index) {
    return value == 1 ? '<span class="bg-success p-2 rounded-1 text-light fw-bold">Approved</span>' : '<span class=" p-2 rounded-1 text-light fw-bold" style="background-color:#f75454">Unproved</span>';
}
function otpStatusFormatter(value, row, index) {
    return value == 1 ? '<span class="bg-success p-2 rounded-1 text-light fw-bold">Verified</span>' : '<span class=" p-2 rounded-1 text-light fw-bold" style="background-color:#f75454">UnVerified</span>';
}

    </script>
@endsection
