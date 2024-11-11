{{-- {{dd($list->propery_type)}} --}}
@extends('layouts.main')
@section('title')
    {{ __('Update Product') }}
@endsection
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('property.index') }}" id="subURL">{{ __('View Property') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Update') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection
@section('content')
    {!! Form::open([
        'route' => ['property.update', $id],
        'method' => 'PATCH',
        'data-parsley-validate',
        'files' => true,
        'id' => 'myForm',
    ]) !!}

    <div class='row'>
        <div class='col-md-6'>

            <div class="card">

                <h3 class="card-header">{{ __('Details') }}</h3>
                <hr>

                {{-- Category --}}
                <div class="card-body" style="height: fit-content">
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('propery_type', __('Property Type'), ['class' => 'form-label col-12']) }}

                        <div class="form-check col-md-4 col-sm-12">
                            {{ Form::radio('property_type',  0, old('property_type', $list->propery_type) == 0, ['class' => 'form-check-input', 'id' => 'commercial']) }}
                            <label class="form-check-label" for="commercial">{{ __('Commercial') }}</label>
                        </div>

                        <div class="form-check col-md-4 col-sm-12">
                            {{ Form::radio('property_type', 1, old('property_type', $list->propery_type) == 1, ['class' => 'form-check-input', 'id' => 'residential']) }}
                            <label class="form-check-label" for="residential">{{ __('Residential') }}</label>
                        </div>

                        <div class="form-check col-md-4 col-sm-12">
                            {{ Form::radio('property_type', 2, old('property_type', $list->propery_type) == 2, ['class' => 'form-check-input', 'id' => 'industrial']) }}
                            <label class="form-check-label" for="industrial">{{ __('Industrial') }}</label>
                        </div>
                    </div>

                    <select name="category" class="form-select form-control-sm" id="category" required>
                        <option value="" selected>{{ __('Choose Category') }}</option>

                        <!-- Commercial Categories -->
                        @if ($commercialCategories->isEmpty())
                            <option value="" class="commercial-category" style="display: none;">
                                {{ __('No Commercial Category Available') }}
                            </option>
                        @endif
                        @foreach ($commercialCategories as $category)
                            <option value="{{ $category->id }}" class="commercial-category"
                                {{ $list->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->category }}
                            </option>
                        @endforeach

                        <!-- Residential Categories -->
                        @if ($residentialCategories->isEmpty())
                            <option value="" class="residential-category" style="display: none;">
                                {{ __('No Residential Category Available') }}
                            </option>
                        @endif
                        @foreach ($residentialCategories as $category)
                            <option value="{{ $category->id }}" class="residential-category"
                                {{ $list->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->category }}
                            </option>
                        @endforeach

                        <!-- Industrial Categories -->
                        @if ($industrialCategories->isEmpty())
                            <option value="" class="industrial-category" style="display: none;">
                                {{ __('No Industrial Category Available') }}
                            </option>
                        @endif
                        @foreach ($industrialCategories as $category)
                            <option value="{{ $category->id }}" class="industrial-category"
                                {{ $list->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->category }}
                            </option>
                        @endforeach
                    </select>

                    <script>
                        // Function to show only relevant categories based on selected property type
                        function showRelevantCategories() {
                            var selectedType = document.querySelector('input[name="property_type"]:checked').value;

                            // Hide all categories initially
                            document.querySelectorAll('.commercial-category').forEach(function(option) {
                                option.style.display = 'none';
                            });
                            document.querySelectorAll('.residential-category').forEach(function(option) {
                                option.style.display = 'none';
                            });
                            document.querySelectorAll('.industrial-category').forEach(function(option) {
                                option.style.display = 'none';
                            });

                            // Show categories based on selected property type
                            if (selectedType == '0') {
                                document.querySelectorAll('.commercial-category').forEach(function(option) {
                                    option.style.display = 'block';
                                });
                            } else if (selectedType == '1') {
                                document.querySelectorAll('.residential-category').forEach(function(option) {
                                    option.style.display = 'block';
                                });
                            } else if (selectedType == '2') {
                                document.querySelectorAll('.industrial-category').forEach(function(option) {
                                    option.style.display = 'block';
                                });
                            }
                        }

                        // Run the function on page load to show relevant categories based on the current property type
                        document.addEventListener('DOMContentLoaded', function() {
                            showRelevantCategories(); // Initial call on page load
                        });

                        // Attach the event listener to radio buttons to update categories when property type is changed
                        document.querySelectorAll('input[name="property_type"]').forEach(function(radio) {
                            radio.addEventListener('change', showRelevantCategories);
                        });
                    </script>




                    {{-- Title --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('title', isset($list->title) ? $list->title : '', ['class' => 'form-control ', 'placeholder' => __('Title'), 'required' => 'true', 'id' => 'title']) }}
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::textarea('description', isset($list->description) ? $list->description : '', ['class' => 'form-control mb-3', 'rows' => '3', 'id' => '', 'required' => 'true', 'placeholder' => __('Description')]) }}
                    </div>
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('featured_property', __('Featured Property'), ['class' => 'form-label col-12']) }}
                        <div class="form-check">
                            {{ Form::checkbox('featured_property', 1, old('featured_property', isset($list->featured_property) ? $list->featured_property : false), ['class' => 'form-check-input', 'id' => 'featured_property']) }}
                            <label class="form-check-label" for="featured_property">{{ __('Featured Property') }}</label>
                        </div>
                    </div>
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('square_yd', __('Square Yard'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('square_yd', isset($list->square_yd) ? $list->square_yd : '', [
                            'class' => 'form-control ',
                            'name' => 'square_yd',
                            'placeholder' => __('Square Yard'),
                            'required' => 'true',
                            'id' => 'square_yd',
                        ]) }}
                    </div>
                    {{-- <p> {{ $list }}s</p> --}}

                    {{-- Property Type --}}
                    {{-- <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('property_type', __('Property Type'), ['class' => 'form-label col-12']) }}
                        <div class="row ps-3 ">

                            <div class="form-check col-md-4 col-sm-12">
                                {{ Form::radio('property_type', 0, old('property_type', $list->propery_type) == 0, ['class' => 'form-check-input', 'id' => 'commercial']) }}
                                <label class="form-check-label" for="commercial">{{ __('Commercial') }}</label>
                            </div>

                            <div class="form-check col-md-4 col-sm-12">
                                {{ Form::radio('property_type', 1, old('property_type', $list->propery_type) == 1, ['class' => 'form-check-input', 'id' => 'residential']) }}
                                <label class="form-check-label" for="residential">{{ __('Residential') }}</label>
                            </div>
                        </div>
                    </div> --}}




                    {{-- Duration --}}
                    {{-- {{$list->rentduration}} --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('Duration', __('Duration For Price'), ['class' => 'form-label col-12 ']) }}
                        <select name="price_duration" id="price_duration" class="choosen-select form-select form-control-sm"
                            data-parsley-minSelect='1'>
                            <option value="Daily"
                                {{ old('price_duration', $list->rentduration ?? '') == 'Daily' ? 'selected' : '' }}>Daily
                            </option>
                            <option value="Monthly"
                                {{ old('price_duration', $list->rentduration ?? '') == 'Monthly' ? 'selected' : '' }}>
                                Monthly</option>
                            <option value="Yearly"
                                {{ old('price_duration', $list->rentduration ?? '') == 'Yearly' ? 'selected' : '' }}>
                                Yearly</option>
                            <option value="Quarterly"
                                {{ old('price_duration', $list->rentduration ?? '') == 'Quarterly' ? 'selected' : '' }}>
                                Quarterly</option>
                        </select>
                    </div>

                    {{-- Price --}}
                    <div class="control-label col-12 form-group mt-2 mandatory">
                        {{ Form::label('price', __('Price') . '(' . $currency_symbol . ')', ['class' => 'form-label col-12 ']) }}
                        {{ Form::number('price', old('price', $list->price), [
                            'class' => 'form-control mt-1',
                            'placeholder' => __('Price'),
                            'required' => 'true',
                            'min' => '1',
                            'id' => 'price',
                            'max' => '1000000000000',
                        ]) }}
                    </div>



                </div>
            </div>
        </div>
        <div class='col-md-6'>

            <div class="card">
                <h3 class="card-header">{{ __('SEO Details') }}</h3>
                <hr>
                <div class="row card-body">

                    {{-- Meta Title --}}
                    <div class="col-md-6 col-sm-12 form-group">
                        {{ Form::label('title', __('Meta Title'), ['class' => 'form-label text-center']) }}
                        <textarea id="edit_meta_title" name="edit_meta_title" class="form-control"
                            oninput="getWordCount('edit_meta_title','edit_meta_title_count','12.9px arial')" rows="2"
                            {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}
                            style="height: 75px" placeholder="{{ __('Meta Title') }}">{{ $list->meta_title }}</textarea>
                        <br>
                        <h6 id="edit_meta_title_count">0</h6>
                    </div>

                    {{-- Meta Image --}}
                    <div class="col-md-6 col-sm-12 form-group card">
                        {{ Form::label('title', __('Meta Image'), ['class' => 'form-label text-center']) }}
                        <input type="file" name="meta_image" id="meta_image" class="filepond">
                    </div>

                    {{-- Meta Image Show --}}
                    @if ($list->meta_image != '')
                        <div class="col-md-2 col-sm-12 text-center">
                            <img src="{{ $list->meta_image }}" alt="" height="100px" width="100px">
                        </div>
                    @endif

                    {{-- Meta Description --}}
                    <div class="col-md-12 col-sm-12 form-group">
                        {{ Form::label('description', __('Meta Description'), ['class' => 'form-label text-center']) }}
                        <textarea id="edit_meta_description" name="edit_meta_description" class="form-control"
                            oninput="getWordCount('edit_meta_description','edit_meta_description_count','12.9px arial')" rows="3"
                            placeholder="{{ __('Meta Description') }}">{{ $list->meta_description }}</textarea>
                        <br>
                        <h6 id="edit_meta_description_count">0</h6>
                    </div>

                    {{-- Meta Keywords --}}
                    <div class="col-md-12 col-sm-12 form-group">
                        {{ Form::label('keywords', __('Meta Keywords'), ['class' => 'form-label']) }}
                        <textarea name="Keywords" id="" class="form-control" rows="3" placeholder="{{ __('Meta Keywords') }}">{{ $list->meta_keywords }}</textarea>
                        ({{ __('Add Comma Separated Keywords') }})
                    </div>
                </div>

            </div>
        </div>

        {{-- Outdoor Facility --}}
        <div class="col-md-12" id="outdoor_facility">
            <div class="card">
                <h3 class="card-header">{{ __('Near By Places') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        @foreach ($facility as $key => $value)
                        <div class='col-md-3 form-group'>
                            {{ Form::checkbox('facility_checkbox_' . $value->id, $value->name, count($value->assign_facilities) > 0, [
                                'class' => 'form-check-input',
                                'id' => 'chk' . $value->id,
                                'onclick' => "toggleDistanceInput({$value->id})"
                            ]) }}
                            {{ Form::label('description', $value->name, ['class' => 'form-check-label']) }}

                            {{-- Checkbox - checked if facility is assigned --}}


                            {{-- Distance input - visible if facility is assigned --}}
                            @if (count($value->assign_facilities))
                                {{ Form::text('facility' . $value->id, $value->assign_facilities[0]['distance'], [
                                    'class' => 'form-control mt-3',
                                    'placeholder' => __('Distance'),
                                    'id' => 'dist' . $value->id,
                                    'style' => 'display: block;' // Visible by default if checked
                                ]) }}
                            @else
                                {{ Form::text('facility' . $value->id, '', [
                                    'class' => 'form-control mt-3',
                                    'placeholder' => __('Distance'),
                                    'id' => 'dist' . $value->id,
                                    'style' => 'display: none;' // Hidden by default if unchecked
                                ]) }}
                            @endif
                        </div>
                    @endforeach


                    </div>
                </div>
            </div>
        </div>
        <script>
            function toggleDistanceInput(id) {
                const checkbox = document.getElementById('chk' + id);
                const distanceInput = document.getElementById('dist' + id);

                // Toggle the display of the distance input based on checkbox state
                if (checkbox.checked) {
                    distanceInput.style.display = 'block';
                } else {
                    distanceInput.style.display = 'none';
                }
            }

            // On page load, ensure visibility matches the initial state
            document.addEventListener("DOMContentLoaded", function() {
                @foreach ($facility as $value)
                    toggleDistanceInput({{ $value->id }});
                @endforeach
            });
        </script>

        {{-- Facility --}}
        <div class="col-md-12" id="facility">
            <div class="card">
                <h3 class="card-header">{{ __('Facilities') }}</h3>
                <hr>

                <div id="parameter_type" class="row card-body">
                    @foreach ($parameters as $key => $value)
                        <div class='col-md-3 form-group'>
                            @php
                                // Retrieve the assignment for the parameter if it exists
                                $assignParameter = $list->assignParameter->where('parameter_id', $value->id)->first();
                            @endphp

                            {{-- Checkbox for selecting a parameter --}}
                            <input type="checkbox" name="par_{{ $value->id }}" id="chk{{ $value->id }}" class="form-check-input"
                                value="{{ $value->id }}" {{ $assignParameter && $assignParameter->value !== null ? 'checked' : '' }}
                                onchange="handleCheckboxChange(this, '{{ $value->id }}')">

                            <label for="chk{{ $value->id }}" class="form-check-label">{{ $value->name }}</label>

                            {{-- Hidden input to handle unchecked checkbox and send null --}}
                            <input type="hidden" name="par_{{ $value->id }}_null" id="par_{{ $value->id }}_null" value="null">

                            {{-- Text input for the parameter value --}}
                            <input type="text" name="par_{{ $value->id }}" class="form-control mt-3" placeholder="Facility"
                                id="dist{{ $value->id }}" value="{{ old('par_' . $value->id, $assignParameter ? $assignParameter->value : '') }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <script>
            function handleCheckboxChange(checkbox, parameterId) {
                // Get the hidden input field
                const nullInput = document.getElementById('par_' + parameterId + '_null');

                if (checkbox.checked) {
                    // If checkbox is checked, remove the hidden input field value for 'null'
                    nullInput.value = '';
                } else {
                    // If checkbox is unchecked, set the hidden input field to 'null'
                    nullInput.value = 'null';
                }
            }
        </script>



        <div class='col-md-12'>

            <div class="card">
                <h3 class="card-header">{{ __('Location') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        {{-- Google Map --}}
                        <div class='col-md-6'>
                            {{-- Map View --}}
                            <div class="card col-md-12" id="map" style="height: 90%">
                                <!-- Google map -->
                            </div>
                        </div>

                        {{-- Details of Map --}}
                        <div class='col-md-6'>
                            <div class="row">

                                {{-- City --}}
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label col-12 ']) }}
                                    {{-- {!! Form::hidden('city', isset($list->city) ? $list->city : '', ['class' => 'form-control ', 'id' => 'city']) !!}
                                    <input id="searchInput" value="{{ isset($list->city) ? $list->city : '' }}"
                                        class="controls form-control" type="text" placeholder="{{ __('City') }}"
                                        required> --}}
                                    {{ Form::text('city', isset($list->city) ? $list->city : '', ['class' => 'form-control ', 'placeholder' => 'City', 'id' => 'city']) }}
                                </div>

                                {{-- Country --}}
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('country', isset($list->country) ? $list->country : '', ['class' => 'form-control ', 'placeholder' => trans('Country'), 'id' => 'country', 'required' => true]) }}
                                </div>

                                {{-- State --}}
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('state', isset($list->state) ? $list->state : '', ['class' => 'form-control ', 'placeholder' => trans('State'), 'id' => 'state', 'required' => true]) }}
                                </div>


                                {{-- Latitude --}}
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('latitude', __('Latitude'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::text('latitude', isset($list->latitude) ? $list->latitude : '', [
                                        'class' => 'form-control ',
                                        'id' => 'latitude',
                                        'step' => 'any',
                                        'readonly' => true,
                                        'required' => true,
                                        'placeholder' => trans('Latitude'),
                                    ]) !!}
                                </div>

                                {{-- Longitude --}}
                                <div class="col-md-6 form-group  mandatory">
                                    {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::text('longitude', isset($list->longitude) ? $list->longitude : '', [
                                        'class' => 'form-control ',
                                        'id' => 'longitude',
                                        'step' => 'any',
                                        'readonly' => true,
                                        'required' => true,
                                        'placeholder' => trans('Longitude'),
                                    ]) !!}
                                </div>

                                {{-- Client Address --}}
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('address', __('Client Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea('client_address', isset($list->client_address) ? $list->client_address : system_setting('company_address') ?? '', ['class' => 'form-control ', 'placeholder' => trans('Client Address'), 'rows' => '4', 'id' => 'client-address', 'autocomplete' => 'off', 'required' => 'true']) }}
                                </div>

                                {{-- Address --}}
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea('address', isset($list->address) ? $list->address : '', ['class' => 'form-control ', 'placeholder' => trans('Address'), 'rows' => '4', 'id' => 'address', 'autocomplete' => 'off', 'required' => 'true']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Images --}}
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Images') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        {{-- Title Image --}}
                        <div class="col-md-3 col-sm-12 form-group mandatory card title_card">
                            {{ Form::label('filepond_title', __('Title Image'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" id="filepond_title" name="title_image"
                                {{ $list->title_image == '' ? 'required' : '' }} accept="image/png,image/jpg,image/jpeg">
                            @if ($list->title_image)
                                <div class="card1 title_img">
                                    <img src="{{ $list->title_image }}" alt="Image" class="card1-img">
                                </div>
                            @endif
                        </div>

                        {{-- 3D Image --}}
                        <div class="col-md-3 col-sm-12 card">
                            {{ Form::label('filepond_3d', __('3D Image'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" id="filepond_3d" name="3d_image">
                            @if ($list->three_d_image)
                                <div class="card1 3d_img">
                                    <img src="{{ $list->three_d_image }}" alt="Image" class="card1-img"
                                        id="3d_img">
                                </div>
                            @endif
                        </div>


                        {{-- Document Image --}}
                        <div class="col-md-3 col-sm-12 card">
                            {{ Form::label('filepond_3d', __('Document Image'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" id="filepond_document" name="document">
                            @if ($list->document)
                                <div class="card1 document">
                                    <img src="{{ asset('images/property_document/' . $list->document) }}" alt="Image"
                                        class="card1-img" id="document">

                                </div>
                            @endif
                        </div>

                        {{-- Gallary Images --}}
                        <div class="col-md-3 col-sm-12 ">
                            <div class="row card" style="margin-bottom:0;">
                                {{ Form::label('filepond2', __('Gallary Images'), ['class' => 'form-label col-12 ']) }}
                                <input type="file" class="filepond" id="filepond2" name="gallery_images[]" multiple>
                            </div>
                            <div class="row mt-0">
                                <?php $i = 0; ?>
                                @if (!empty($list->gallery))
                                    @foreach ($list->gallery as $row)
                                        <div class="col-md-6 col-sm-12" id='{{ $row->id }}'>
                                            <div class="card1" style="height:90%;">

                                                <img src="{{ url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $list->id . '/' . $row->image }}"
                                                    alt="Image" class="card1-img">
                                                <button data-rowid="{{ $row->id }}"
                                                    class="RemoveBtn1 RemoveBtngallary">x</button>
                                            </div>
                                        </div>

                                        <?php $i++; ?>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('video_link', __('Video Link'), ['class' => 'form-label col-12 ']) }}
                            {{ Form::text('video_link', isset($list->video_link) ? $list->video_link : '', ['class' => 'form-control ', 'placeholder' => trans('Video Link'), 'id' => 'address', 'autocomplete' => 'off']) }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Accesibility') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="col-sm-12 col-md-12  col-xs-12 d-flex">
                        <label class="col-sm-1 form-check-label mandatory mt-3 ">{{ __('Is Private?') }}</label>
                        <div class="form-check form-switch mt-3">

                            <input type="hidden" name="is_premium" id="is_premium"
                                value=" {{ $list->is_premium ? 1 : 0 }}">
                            <input class="form-check-input" type="checkbox" role="switch"
                                {{ $list->is_premium ? 'checked' : '' }} id="is_premium_switch">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Property Contact') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="col-sm-12 col-md-12  col-xs-12 ">
                        {{-- Title --}}
                        <div class="col-md-12 col-12 form-group mandatory">
                            {{ Form::label('whatsapp_number', __('WhatsApp'), ['class' => 'form-label col-12 ']) }}
                            {{ Form::text('whatsapp_number', isset($list->whatsapp_number) ? $list->whatsapp_number : '', [
                                'class' => 'form-control ',
                                'name' => 'whatsapp_number',
                                'placeholder' => __('WhatsApp'),
                                'required' => 'true',
                                'id' => 'title',
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class='col-md-12 d-flex justify-content-end mb-3'>
            <input type="submit" class="btn btn-primary" value="{{ __('Save') }}">
            &nbsp;
            &nbsp;
            <button class="btn btn-secondary" type="button" onclick="formname.reset();">{{ __('Reset') }}</button>
        </div>
        {!! Form::close() !!}

    </div>
@endsection
@section('script')
    <script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?libraries=places&key={{ env('PLACE_API_KEY') }}&callback=initMap"
        async defer></script>

    <script>
        function initMap() {
            var latitude = parseFloat($('#latitude').val());
            var longitude = parseFloat($('#longitude').val());
            var map = new google.maps.Map(document.getElementById('map'), {

                center: {
                    lat: latitude,
                    lng: longitude
                },


                zoom: 13
            });
            var marker = new google.maps.Marker({
                position: {
                    lat: latitude,
                    lng: longitude
                },
                map: map,
                draggable: true,
                title: 'Marker Title'
            });
            google.maps.event.addListener(marker, 'dragend', function(event) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'latLng': event.latLng
                }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            var address_components = results[0].address_components;
                            var city, state, country, full_address;

                            for (var i = 0; i < address_components.length; i++) {
                                var types = address_components[i].types;
                                if (types.indexOf('locality') != -1) {
                                    city = address_components[i].long_name;
                                } else if (types.indexOf('administrative_area_level_1') != -1) {
                                    state = address_components[i].long_name;
                                } else if (types.indexOf('country') != -1) {
                                    country = address_components[i].long_name;
                                }
                            }

                            full_address = results[0].formatted_address;

                            // Do something with the city, state, country, and full address
                            $('#searchInput').val(city);
                            $('#city').val(city);
                            $('#country').val(country);
                            $('#state').val(state);
                            $('#address').val(full_address);
                            $('#latitude').val(event.latLng.lat());
                            $('#longitude').val(event.latLng.lng());

                        } else {
                            console.log('No results found');
                        }
                    } else {
                        console.log('Geocoder failed due to: ' + status);
                    }
                });
            });
            var input = document.getElementById('searchInput');
            // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
            });
            autocomplete.addListener('place_changed', function() {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("Autocomplete's returned place contains no geometry");
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                marker.setIcon(({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                var address = '';
                if (place.address_components) {
                    address = [
                        (place.address_components[0] && place.address_components[0].short_name || ''),
                        (place.address_components[1] && place.address_components[1].short_name || ''),
                        (place.address_components[2] && place.address_components[2].short_name || '')
                    ].join(' ');
                }

                infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                infowindow.open(map, marker);

                // Location details
                for (var i = 0; i < place.address_components.length; i++) {
                    console.log(place);

                    if (place.address_components[i].types[0] == 'locality') {
                        $('#city').val(place.address_components[i].long_name);


                    }
                    if (place.address_components[i].types[0] == 'country') {
                        $('#country').val(place.address_components[i].long_name);


                    }
                    if (place.address_components[i].types[0] == 'administrative_area_level_1') {
                        console.log(place.address_components[i].long_name);
                        $('#state').val(place.address_components[i].long_name);


                    }
                }
                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng();
                $('#address').val(place.formatted_address);
                $('#latitude').val(place.geometry.location.lat());
                $('#longitude').val(place.geometry.location.lng());
            });
        }

        $(document).ready(function() {
            if ($('input[name="property_type"]:checked').val() == 0) {
                $('#duration').hide();
                $('#price_duration').removeAttr('required');
            } else {
                $('#duration').show();

            }
            getWordCount("edit_meta_title", "edit_meta_title_count", "19.9px arial");
            getWordCount("edit_meta_description", "edit_meta_description_count", "12.9px arial");

        });
        $('input[name="property_type"]').change(function() {
            // Get the selected value
            var selectedType = $('input[name="property_type"]:checked').val();

            // Perform actions based on the selected value

            if (selectedType == 1) {
                $('#duration').show();
                $('#price_duration').attr('required', 'true');
            } else {
                $('#duration').hide();
                $('#price_duration').removeAttr('required');
            }
        });
        $(".RemoveBtngallary").click(function(e) {
            e.preventDefault();
            var id = $(this).data('rowid');
            Swal.fire({
                title: 'Are You Sure Want to Remove This Image',
                icon: 'error',
                showDenyButton: true,

                confirmButtonText: 'Yes',
                denyCanceButtonText: `No`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('property.removeGalleryImage') }}",

                        type: "POST",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            "id": id
                        },
                        success: function(response) {

                            if (response.error == false) {
                                Toastify({
                                    text: 'Image Delete Successful',
                                    duration: 6000,
                                    close: !0,
                                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                                }).showToast();
                                $("#" + id).html('');
                            } else if (response.error == true) {
                                Toastify({
                                    text: 'Something Wrong !!!',
                                    duration: 6000,
                                    close: !0,
                                    backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                                }).showToast()
                            }
                        },
                        error: function(xhr) {}
                    });
                }
            })

        });
        $(document).on('click', '#filepond_3d', function(e) {

            $('.3d_img').hide();
        });
        $(document).on('click', '#filepond_title', function(e) {

            $('.title_img').hide();
        });
        jQuery(document).ready(function() {
            initMap();

            $('#map').append('<iframe src="https://maps.google.com/maps?q=' + $('#latitude').val() + ',' + $(
                    '#longitude').val() +
                '&hl=en&amp;z=18&amp;output=embed" height="375px" width="800px"></iframe>');
        });
        $(document).ready(function() {
            $('.parsley-error filled,.parsley-required').attr("aria-hidden", "true");
            $('.parsley-error filled,.parsley-required').hide();

        });
        $(document).ready(function() {



            $("#is_premium_switch").on('change', function() {
                $("#is_premium_switch").is(':checked') ? $("#is_premium").val(1) : $(
                        "#is_premium")
                    .val(0);
            });

            FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize,
                FilePondPluginFileValidateType);

            $('#meta_image').filepond({
                credits: null,
                allowFileSizeValidation: "true",
                maxFileSize: '3KB',
                labelMaxFileSizeExceeded: 'File is too large',
                labelMaxFileSize: 'Maximum file size is {filesize}',
                allowFileTypeValidation: true,
                acceptedFileTypes: ['image/*'],
                labelFileTypeNotAllowed: 'File of invalid type',
                fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
                storeAsFile: true,
                pdfComponentExtraParams: 'toolbar=0&navpanes=0&scrollbar=0&view=fitH',

            });
        });
    </script>
    <style>
        .error-message {
            color: #dc3545;
            margin-top: 5px;
            font-size: 15px;
        }
    </style>
@endsection
