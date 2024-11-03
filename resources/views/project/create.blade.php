@extends('layouts.main')

@section('title')
    {{ __('Add Property') }}
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
                            <a href="{{ route('project.index') }}" id="subURL">{{ __('View Project') }}</a>
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
    {!! Form::open(['route' => 'project.store', 'data-parsley-validate', 'id' => 'myForm', 'files' => true]) !!}
    <div class='row'>
        <div class='col-md-6'>
            <div class="card">
                {{-- Category --}}
                <div class="card-body">
                    {{-- <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                        <select name="category_id" class="form-select form-control-sm" data-parsley-minSelect='1'
                            id="category" required>
                            <option value="" selected>{{ __('Choose Category') }}</option>
                            @foreach ($category as $row)
                                <option value="{{ $row->id }}" data-parametertypes='{{ $row->parameter_types }}'>
                                    {{ $row->category }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    {{-- Title --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('title', '', [
                            'class' => 'form-control ',
                            'placeholder' => __('Title'),
                            'required' => 'true',
                            'id' => 'title',
                        ]) }}
                    </div>

                    {{-- Description --}}
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::textarea('description', '', [
                            'class' => 'form-control mb-3',
                            'rows' => '5',
                            'id' => '',
                            'required' => 'true',
                            'placeholder' => __('Description'),
                        ]) }}
                    </div>
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('square_yd', __('Square Yard'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('square_yd', '', [
                            'class' => 'form-control ',
                            'name' => 'square_yd',
                            'placeholder' => __('Square Yard'),
                            'required' => 'true',
                            'id' => 'square_yd',
                        ]) }}
                    </div>

                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('property_type', __('Property Type'), ['class' => 'form-label col-12']) }}

                        <div class="form-check">
                            {{ Form::radio('property_type', 0, isset($list->property_type) && $list->property_type == 0, ['class' => 'form-check-input', 'id' => 'commercial']) }}
                            <label class="form-check-label" for="commercial">{{ __('Commercial') }}</label>
                        </div>

                        <div class="form-check">
                            {{ Form::radio('property_type', 1, isset($list->property_type) && $list->property_type == 1, ['class' => 'form-check-input', 'id' => 'residential']) }}
                            <label class="form-check-label" for="residential">{{ __('Residential') }}</label>
                        </div>
                    </div>





                    {{-- City --}}
                    <div class="control-label col-12 form-group mt-2 mandatory">
                        {{ Form::label('city', __('City') , ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('city','', [
                            'class' => 'form-control mt-1',
                            'placeholder' => __('City'),
                            'required' => 'true',
                            'min' => '1',
                            'id' => 'city',

                        ]) }}
                    </div>
                    <div class="control-label col-12 form-group mt-2 mandatory">
                        {{ Form::label('state', __('State') , ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('state','', [
                            'class' => 'form-control mt-1',
                            'placeholder' => __('State'),
                            'required' => 'true',
                            'min' => '1',
                            'id' => 'state',

                        ]) }}
                    </div>
                    <div class="control-label col-12 form-group mt-2 mandatory">
                        {{ Form::label('country', __('country') , ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('country','', [
                            'class' => 'form-control mt-1',
                            'placeholder' => __('country'),
                            'required' => 'true',
                            'min' => '1',
                            'id' => 'country',

                        ]) }}
                    </div>



                </div>
            </div>
        </div>

        <div class="col-md-12" id="outdoor_facility">
            <div class="card">
                <h3 class="card-header">{{ __('Near By Places') }}</h3>
                <hr>
                {{-- <div class="card-body">
                    <div class="row">
                        @foreach ($facility as $key => $value)
                            <div class='col-md-3  form-group'>
                                {{ Form::checkbox($value->id, $value->name, false, ['class' => 'form-check-input', 'id' => 'chk' . $value->id]) }}
                                {{ Form::label('description', $value->name, ['class' => 'form-check-label']) }}
                                {{ Form::text('facility' . $value->id, '', [
                                    'class' => 'form-control mt-3',
                                    'placeholder' => 'distance',
                                    'id' => 'dist' . $value->id,
                                ]) }}
                            </div>
                        @endforeach
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="col-md-12" id="facility">
            {{-- <div class="card">

                <h3 class="card-header"> {{ __('Facilities') }}</h3>
                <hr>
                {{ Form::hidden('category_count[]', $category, ['id' => 'category_count']) }}
                {{ Form::hidden('parameter_count[]', $parameters, ['id' => 'parameter_count']) }}
                {{ Form::hidden('facilities[]', $facility, ['id' => 'facilities']) }}

                {{ Form::hidden('parameter_add', '', ['id' => 'parameter_add']) }}
                <div id="parameter_type" class="row card-body"></div>

            </div> --}}
        </div>

        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Images') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-sm-12">
                            <div class="row">

                                <style>

                                </style>
                                <div class="col-md-6 col-sm-12  form-group mandatory card title_card">
                                    {{ Form::label('title_image', __('Title Image'), ['class' => 'form-label col-12 ']) }}

                                    <input type="file" class="filepond" id="filepond_title" name="title_image"
                                        accept="image/jpg,image/png,image/jpeg" required>
                                </div>

                                <div class="col-md-6 col-sm-12 card">
                                    {{ Form::label('title_image', __('3D Image'), ['class' => 'form-label col-12 ']) }}

                                    <input type="file" class="filepond" id="filepond2" name="3d_image">

                                </div>
                                <div class="col-md-6 col-sm-12 card">
                                    {{ Form::label('title_image', __('Document Image'), ['class' => 'form-label col-12 ']) }}

                                    <input type="file" class="filepond" id="filepond2" name="document">

                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-12 ">
                            <div class="row card" style="margin-bottom:0;">
                                {{ Form::label('title_image', __('Gallary Images'), ['class' => 'form-label col-12 ']) }}

                                <input type="file" class="filepond" id="filepond2" name="gallery_images[]" multiple>
                            </div>
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('video_link', __('Video Link'), ['class' => 'form-label col-12 ']) }}
                            {{ Form::text('video_link', isset($list->video_link) ? $list->video_link : '', [
                                'class' => 'form-control ',
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

    {!! Form::close() !!}
@endsection
