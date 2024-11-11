@extends('layouts.main')

@section('title')
    {{ __('Add Industry') }}
@endsection
<!-- add before </body> -->

{{-- <script src="https://unpkg.com/filepond/dist/filepond.js"></script> --}}
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
                            <a href="{{ route('project.index') }}" id="subURL">{{ __('View Industry') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Add') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection
@section('content')
    {!! Form::open([
        'route' => ['project.update', $project->id],
        'method' => 'PATCH',
        'data-parsley-validate',
        'files' => true,
        'id' => 'myForm',
    ]) !!}
    <div class='row'>
        {{-- Category --}}
        <div class='col-md-6'>
            <div class="card">
                {{-- Category --}}
                <div class="card-body">
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                        <select name="category_id" class="form-select form-control-sm" data-parsley-minSelect='1'
                            id="cat" required>
                            <option value="" selected>{{ __('Choose Category') }}</option>
                            @foreach ($categorys as $row)
                                <option value="{{ $row->id }}" data-parametertypes="{{ $row->parameter_types }}"
                                    {{ old('category_id', $project->category_id) == $row->id ? 'selected' : '' }}>
                                    {{ $row->category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Title --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('title', old('title', $project->title ?? ''), [
                            'class' => 'form-control ',
                            'placeholder' => __('Title'),
                            'required' => 'true',
                            'id' => 'title',
                        ]) }}
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::textarea('description', old('description', $project->description ?? ''), [
                            'class' => 'form-control mb-3',
                            'rows' => '5',
                            'required' => 'true',
                            'placeholder' => __('Description'),
                        ]) }}
                    </div>

                    {{-- Square Yard --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('square_yd', __('Square Yard'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('square_yd', old('square_yd', $project->square_yd ?? ''), [
                            'class' => 'form-control ',
                            'placeholder' => __('Square Yard'),
                            'required' => 'true',
                            'id' => 'square_yd',
                        ]) }}
                    </div>

                    {{-- Property Type --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('propery_type', __('Property Type'), ['class' => 'form-label col-12']) }}

                        <div class="form-check">
                            {{ Form::radio('propery_type', 0, old('propery_type', $project->propery_type ?? 0) == 0, ['class' => 'form-check-input', 'id' => 'commercial']) }}
                            <label class="form-check-label" for="commercial">{{ __('Commercial') }}</label>
                        </div>

                        <div class="form-check">
                            {{ Form::radio('propery_type', 1, old('propery_type', $project->propery_type ?? 1) == 1, ['class' => 'form-check-input', 'id' => 'residential']) }}
                            <label class="form-check-label" for="residential">{{ __('Residential') }}</label>
                        </div>
                    </div>

                    {{-- Duration for Price --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('Duration', __('Duration For Price'), ['class' => 'form-label col-12 ']) }}
                        <select name="rentduration" id="price_duration" class="choosen-select form-select form-control-sm"
                            data-parsley-minSelect='1'>
                            <option value="Daily"
                                {{ old('rentduration', $project->rentduration ?? '') == 'Daily' ? 'selected' : '' }}>Daily
                            </option>
                            <option value="Monthly"
                                {{ old('rentduration', $project->rentduration ?? '') == 'Monthly' ? 'selected' : '' }}>
                                Monthly</option>
                            <option value="Yearly"
                                {{ old('rentduration', $project->rentduration ?? '') == 'Yearly' ? 'selected' : '' }}>
                                Yearly</option>
                            <option value="Quarterly"
                                {{ old('rentduration', $project->rentduration ?? '') == 'Quarterly' ? 'selected' : '' }}>
                                Quarterly</option>
                        </select>
                    </div>

                    {{-- Price --}}
                    <div class="control-label col-12 form-group mt-2 mandatory">
                        {{ Form::label('price', __('Price'), '', ['class' => 'form-label col-12 ']) }}
                        {{ Form::number('price', old('price', $project->price ?? ''), [
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
        {{-- SEO --}}
        <div class='col-md-6'>
            <div class="card">
                <h3 class="card-header">{{ __('SEO Details') }}</h3>
                <hr>
                <div class="row card-body">

                    {{-- SEO Title --}}
                    <div class="col-md-6 col-sm-12 form-group">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label text-center']) }}
                        <textarea id="meta_title" name="meta_title" class="form-control"
                            oninput="getWordCount('meta_title','meta_title_count','12.9px arial')" rows="2" style="height: 75px"
                            placeholder="{{ __('Title') }}">{{ isset($project->meta_title) ? $project->meta_title : '' }}</textarea>
                        <br>
                        <h6 id="meta_title_count">{{ strlen(old('meta_title', '')) }}</h6>
                    </div>

                    {{-- SEO Image --}}
                    <div class="col-md-6 col-sm-12 form-group card">
                        {{ Form::label('image', __('Image'), ['class' => 'form-label']) }}
                        <input type="file" name="meta_image" id="meta_image" class="filepond form-control"
                            placeholder="{{ __('Image') }}">

                        @if ($project->meta_image)
                            <div class="mt-3">
                                <img src="{{ asset('storage/' . $project->meta_image) }}" alt="Current Image"
                                    style="max-width: 100%; height: auto;">
                            </div>
                        @endif

                        <div class="img_error"></div>
                    </div>

                    {{-- SEO Description --}}
                    <div class="col-md-12 col-sm-12 form-group">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label text-center']) }}
                        <textarea id="meta_description" name="meta_description" class="form-control"
                            oninput="getWordCount('meta_description','meta_description_count','12.9px arial')" rows="3"
                            placeholder="{{ __('Description') }}">{{ isset($project->meta_description) ? $project->meta_description : '' }}</textarea>
                        <br>
                        <h6 id="meta_description_count">{{ strlen(old('meta_description', '')) }}</h6>
                    </div>

                    {{-- SEO Keywords --}}
                    <div class="col-md-12 col-sm-12 form-group">
                        {{ Form::label('keywords', __('Keywords'), ['class' => 'form-label']) }}
                        <textarea name="keywords" id="" class="form-control" rows="3" placeholder="{{ __('Keywords') }}">{{ isset($project->meta_keywords) ? $project->meta_keywords : '' }}</textarea>
                        (add comma separated keywords)
                    </div>

                </div>
            </div>

        </div>
        {{-- Map --}}
        <div class='col-md-12'>

            <div class="card">
                <h3 class="card-header">{{ __('Location') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        <!-- Google Map Section -->
                        <div class='col-md-6'>
                            <div class="card col-md-12" id="map" style="height: 90%">
                                <!-- Google map -->
                            </div>
                        </div>
                        <!-- Location Information Form -->
                        <div class='col-md-6'>
                            <div class="row">
                                <!-- City Field -->
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('city', old('city', $project->city ?? ''), [
                                        'class' => 'form-control ',
                                        'placeholder' => __('City'),
                                        'id' => 'city',
                                        'required' => true,
                                    ]) }}
                                </div>

                                <!-- Country Field -->
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('country', old('country', $project->country ?? ''), [
                                        'class' => 'form-control ',
                                        'placeholder' => __('Country'),
                                        'id' => 'country',
                                        'required' => true,
                                    ]) }}
                                </div>

                                <!-- State Field -->
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('state', old('state', $project->state ?? ''), [
                                        'class' => 'form-control ',
                                        'placeholder' => __('State'),
                                        'id' => 'state',
                                        'required' => true,
                                    ]) }}
                                </div>

                                <!-- Latitude Field -->
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('latitude', __('Latitude'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::text('latitude', old('latitude', $project->latitude ?? ''), [
                                        'class' => 'form-control',
                                        'id' => 'latitude',
                                        'step' => 'any',
                                        'required' => true,
                                        'placeholder' => __('Latitude'),
                                    ]) !!}
                                </div>

                                <!-- Longitude Field -->
                                <div class="col-md-6 form-group mandatory">
                                    {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label col-12 ']) }}
                                    {!! Form::text('longitude', old('longitude', $project->longitude ?? ''), [
                                        'class' => 'form-control',
                                        'id' => 'longitude',
                                        'step' => 'any',
                                        'required' => true,
                                        'placeholder' => __('Longitude'),
                                    ]) !!}
                                </div>

                                <!-- Client Address Field -->
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('client_address', __('Client Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea(
                                        'client_address',
                                        old('client_address', $project->client_address ?? (system_setting('company_address') ?? '')),
                                        [
                                            'class' => 'form-control ',
                                            'placeholder' => __('Client Address'),
                                            'rows' => '4',
                                            'id' => 'client-address',
                                            'autocomplete' => 'off',
                                            'required' => true,
                                        ],
                                    ) }}
                                </div>

                                <!-- Address Field -->
                                <div class="col-md-12 col-12 form-group mandatory">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea('address', old('address', $project->address ?? ''), [
                                        'class' => 'form-control ',
                                        'placeholder' => __('Address'),
                                        'rows' => '4',
                                        'id' => 'address',
                                        'autocomplete' => 'off',
                                        'required' => true,
                                    ]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {{-- OutDoors Facilities --}}
        <div class="col-md-12" id="outdoor_facility">
            <div class="card">
                <h3 class="card-header">{{ __('Near By Places') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        @foreach ($outdoor as $facility)
                        @php
                            // Find the assigned facility for the current project
                            $assignedFacility = $facilit->firstWhere('facility_id', $facility->id);
                            $isChecked = $assignedFacility ? true : false; // Check if the facility is assigned
                            $distance = $assignedFacility ? $assignedFacility->distance : ''; // Get the assigned distance if available
                        @endphp

                        <div class="col-md-3 form-group">
                            <!-- Checkbox for each facility -->
                            {{ Form::checkbox(
                                'facility_distance[' . $facility->id . ']',
                                $facility->id,
                                $isChecked, // Set checkbox to checked if the facility is assigned
                                [
                                    'class' => 'form-check-input',
                                    'id' => 'chk' . $facility->id,
                                    'onclick' => 'toggleDistanceInput(' . $facility->id . ')',
                                ]
                            ) }}

                            {{ Form::label('chk' . $facility->id, $facility->name, ['class' => 'form-check-label']) }}

                            <!-- Text input for distance if the checkbox is selected -->
                            {{ Form::text(
                                'facility_distance[' . $facility->id . ']',
                                old('facility_distance.' . $facility->id, $distance), // Populate with old input or assigned distance
                                [
                                    'class' => 'form-control mt-3',
                                    'placeholder' => 'Distance',
                                    'id' => 'dist' . $facility->id,
                                    'disabled' => !$isChecked, // Disable if the checkbox is not checked
                                ]
                            ) }}
                        </div>
                    @endforeach







                    </div>
                </div>
            </div>

        </div>
        {{-- Indoor --}}
        <div class="col-md-12" id="facility">
            <div class="card">
                <h3 class="card-header"> {{ __('Facilities') }}</h3>
                <hr>

                <div id="parameter_type" class="row card-body">
                    @foreach ($parameters as $parameter)
                        {{-- {{dd($parameters)}} --}}
                        <div class="col-md-3 form-group">
                            <!-- Checkbox for each parameter -->
                            {{ Form::checkbox(
                                'par_' . $parameter->id,
                                $parameter->id,
                                in_array($parameter->id, $assignedParameters->pluck('parameter_id')->toArray()), // Check if the parameter is assigned to this project
                                ['class' => 'form-check-input', 'id' => 'chk' . $parameter->id],
                            ) }}

                            {{ Form::label('chk' . $parameter->id, $parameter->name, ['class' => 'form-check-label']) }}

                            <!-- Text input for each parameter if the checkbox is selected -->
                            {{ Form::text(
                                'par_' . $parameter->id,
                                old(
                                    'par_' . $parameter->id,
                                    // Get the value for the parameter from the assigned parameters table
                                    $assignedParameters->firstWhere('parameter_id', $parameter->id)->value ?? '',
                                ),
                                [
                                    'class' => 'form-control mt-3',
                                    'placeholder' => 'Value',
                                    'id' => 'dist' . $parameter->id,
                                    'disabled' => !in_array($parameter->id, $assignedParameters->pluck('parameter_id')->toArray()), // Disable if checkbox is not checked
                                ],
                            ) }}
                        </div>
                    @endforeach




                </div>
            </div>
        </div>
        {{-- Image --}}
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Images') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-md-3 col-sm-12 form-group mandatory card title_card">
                                    {{ Form::label('title_image', __('Title Image'), ['class' => 'form-label col-12']) }}
                                    <input type="file" class="filepond" id="filepond" name="image" >

                                    @if ($project->image)
                                        <div class="card1 title_img">
                                            <img src="{{ $project->image }}" alt="Image" class="card1-img">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-3 col-sm-12 card">
                                    {{ Form::label('doc_image', __('Document Image'), ['class' => 'form-label col-12']) }}
                                    <input type="file" multiple class="filepond" id="filepond" name="documents[]">
                                    <?php $i = 0; ?>
                                    @if (!empty($images))
                                        <div class="col-md-6 col-sm-12">
                                            <div class="card1" style="height:90%;">
                                                @foreach ($images as $row)
                                                    @if ($row->type == 'doc')
                                                        <img src="{{ $row->name }}" width="100" alt="">
                                                    @endif
                                                    <button data-rowid="{{ $row->id }}"
                                                        class="RemoveBtn1 RemoveBtngallary">x</button>
                                                    <?php $i++; ?>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-3 col-sm-12 card">
                                    {{ Form::label('gallary_image', __('Gallery Images'), ['class' => 'form-label col-12']) }}
                                    <input type="file" class="filepond" id="filepond "
                                        accept="image/jpg,image/png,image/jpeg" name="gallery_images[]" multiple>
                                    <?php $i = 0; ?>
                                    @if (!empty($images))
                                        <div class="col-md-6 col-sm-12">
                                            <div class="card1" style="height:90%;">
                                                @foreach ($images as $row)
                                                    {{-- {{dd($row)}} --}}
                                                    @if ($row->type == 'image')
                                                        <img src="{{ $row->name }}" width="100" alt="">
                                                    @endif
                                                    <button data-rowid="{{ $row->id }}"
                                                        class="RemoveBtn1 RemoveBtngallary">x</button>

                                                    <?php $i++; ?>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-3 col-sm-12 card">
                                    {{ Form::label('project_plans', __('Project Plans'), ['class' => 'form-label col-12']) }}
                                    <input type="file" multiple class="filepond" id="filepond" name="plans[]">
                                    <?php $i = 0; ?>
                                    @if (!empty($plans))
                                    <div class="col-md-6 col-sm-12">
                                        <div class="card1" style="height:90%;">
                                            @foreach ($plans as $row)
                                                    <img src="{{ $row->document }}" width="100" alt="">
                                                <button data-rowid="{{ $row->id }}"
                                                    class="RemoveBtn1 RemoveBtngallary">x</button>

                                                <?php $i++; ?>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                </div>

                                <div class="col-md-5">
                                    {{ Form::label('video_link', __('Video Link'), ['class' => 'form-label col-12']) }}
                                    {{ Form::text('video_link', old('video_link', isset($project->video_link) ? $project->video_link : ''), [
                                        'class' => 'form-control',
                                        'placeholder' => 'Video Link',
                                        'id' => 'address',
                                        'autocomplete' => 'off',
                                    ]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-md-12" id="facility">
            <div class="card">
                <h3 class="card-header">{{ __('Property') }}</h3>
                <hr>
                <div id="parameter_type" class="row card-body">
                    <div class='col-md-3 form-select-sm'>
                        {{ Form::select('type', ['0' => 'Underprocess', '1' => 'Upcoming'], $project->type ?? '', [
                            'class' => 'form-control',
                            'required' => 'required',
                        ]) }}
                    </div>
                </div>
            </div>
        </div>


        <div class='col-md-12 d-flex justify-content-end mb-3'>
            <input type="submit" class="btn btn-primary" value="Update">
            &nbsp;
            &nbsp;

            <button class="btn btn-secondary" type="button" onclick="myForm.reset();">{{ __('Reset') }}</button>
        </div>

    </div>
@endsection
@section('script')
    <script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?libraries=places&key={{ env('PLACE_API_KEY') }}&callback=initMap"
        async defer></script>
    <script>
        $(document).ready(function() {
            $("#is_premium_switch").on('change', function() {
                $("#is_premium_switch").is(':checked') ? $("#is_premium").val(1) : $("#is_premium").val(0);
            });
            getWordCount("meta_title", "meta_title_count", "19.9px arial");
            getWordCount("meta_description", "meta_description_count", "12.9px arial");
        });

        function initMap() {
            let defaultLatitude = parseInt($("#default-latitude").val() ?? -33.8688);
            let defaultLongitude = parseInt($("#default-longitude").val() ?? 151.2195);

            var map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: defaultLatitude,
                    lng: defaultLongitude
                },
                zoom: 8
            });
            var input = document.getElementById('searchInput');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                draggable: true,
                position: {
                    lat: defaultLatitude,
                    lng: defaultLongitude
                },
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
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
                            $('#searchInput').val(city);
                            $('#city').val(city);
                            $('#country').val(country);
                            $('#state').val(state);
                            $('#address').val(full_address);
                            $('#latitude').val(event.latLng.lat());
                            $('#longitude').val(event.latLng.lng());
                        }
                    }
                });
            });

            autocomplete.addListener('place_changed', function() {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) return;

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
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

                for (var i = 0; i < place.address_components.length; i++) {
                    if (place.address_components[i].types[0] == 'locality') {
                        $('#city').val(place.address_components[i].long_name);
                    }
                    if (place.address_components[i].types[0] == 'country') {
                        $('#country').val(place.address_components[i].long_name);
                    }
                    if (place.address_components[i].types[0] == 'administrative_area_level_1') {
                        $('#state').val(place.address_components[i].long_name);
                    }
                }

                $('#address').val(place.formatted_address);
                $('#latitude').val(place.geometry.location.lat());
                $('#longitude').val(place.geometry.location.lng());
            });
        }

        jQuery(document).ready(function() {
            initMap();
            $('#map').append('<iframe src="https://maps.google.com/maps?q=' + 20.593684 + ',' + 78.96288 +
                '&hl=en&amp;z=18&amp;output=embed" height="375px" width="800px"></iframe>');
            $('.select2').prepend('<option value="" selected></option>');
        });

        $(document).ready(function() {
            FilePond.registerPlugin(FilePondPluginImagePreview, FilePondPluginFileValidateSize,
                FilePondPluginFileValidateType);

            $('#meta_image').filepond({
                credits: null,
                allowFileSizeValidation: "true",
                maxFileSize: '300KB',
                labelMaxFileSizeExceeded: 'File is too large',
                labelMaxFileSize: 'Maximum file size is {filesize}',
                allowFileTypeValidation: true,
                acceptedFileTypes: ['image/*'],
                labelFileTypeNotAllowed: 'File of invalid type',
                fileValidateTypeLabelExpectedTypes: 'Expects {allButLastType} or {lastType}',
                storeAsFile: true,
                allowPdfPreview: true,
                pdfPreviewHeight: 320,
                pdfComponentExtraParams: 'toolbar=0&navpanes=0&scrollbar=0&view=fitH',
                allowVideoPreview: true,
                allowAudioPreview: true,
            });
        });
    </script>
@endsection
