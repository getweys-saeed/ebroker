@extends('layouts.main')

@section('title')
    {{ __('Update Article') }}
@endsection

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
                            <a href="{{ route('article.index') }}" id="subURL">{{ __('View Article') }}</a>
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
    <section class="section">
        <div class="row">
            <div class="col-md-7 col-sm-12">
                <div class="card article_form">
                    <div class="card-header add_article_header">
                        Update Article
                    </div>
                    <hr>
                    {!! Form::open([
                        'route' => ['article.update', $id],
                        'data-parsley-validate',
                        'files' => true,
                        'method' => 'PATCH',
                    ]) !!}
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-12 col-sm-12 form-group mandatory">

                                {{ Form::label('title', __('Title'), ['class' => 'form-label col-12']) }}
                                {{ Form::text('title', $list->title, [
                                    'class' => 'form-control ',
                                    'placeholder' => 'Title',
                                    'data-parsley-required' => 'true',
                                    'id' => 'title',
                                ]) }}

                            </div>

                            <div class="col-md-12 col-12 form-group mandatory">
                                {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                                <select name="category" class="form-select form-control-sm" data-parsley-minSelect='1'
                                    required>
                                    <option value="0"> General </option>
                                    @foreach ($category as $row)
                                        <option value="{{ $row->id }}"
                                            data-parametertypes='{{ $row->parameter_types }}'
                                            {{ $row->id == $list->category_id ? 'selected' : '' }}>
                                            {{ $row->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 col-sm-12 form-group mandatory">

                                {{ Form::label('image', __('Image'), ['class' => 'col-12 form-label']) }}

                                <input accept="image/*" name='image' type='file' class="filepond" id="edit_image" />
                                <div class="edit_article_img">
                                    <img src="{{ $list->image }}" alt="" class="edit_img" height="300px"
                                        width="500px">
                                </div>

                            </div>

                        </div>
                        <div class="row  mt-4">

                            <div class="col-md-12 col-sm-12 form-group mandatory">
                                {{ Form::label('description', __('Description'), ['class' => 'form-label col-12']) }}

                                {{ Form::textarea('description', $list->description, [
                                    'class' => 'form-control ',
                                    'id' => 'tinymce_editor',
                                    'data-parsley-required' => 'true',
                                ]) }}

                            </div>
                        </div>

                        <div
                            class="col-md-12 col-sm-12 form-group {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'mandatory' : '' }}">
                            {{ Form::label('title', __('Meta Title'), ['class' => 'form-label text-center']) }}

                            <input type="text" name="edit_meta_title" class="form-control" id="edit_meta_title"
                                oninput="getWordCount('edit_meta_title','edit_meta_title_count','19.9px arial')"
                                placeholder="{{ __('Meta title') }}" value=" {{ $list->meta_title }}"
                                {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}>
                            <h6 id="edit_meta_title_count">0</h6>
                        </div>
                        <div
                            class="col-md-12 col-sm-12 form-group {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'mandatory' : '' }}">
                            {{ Form::label('title', __('Meta Keywords'), ['class' => 'form-label text-center']) }}

                            <input type="text" name="meta_keywords" class="form-control" id="meta_keywords"
                                placeholder="{{ __('Meta Keywords') }}" value=" {{ $list->meta_keywords }}"
                                {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}>

                        </div>
                        <div
                            class="col-md-12 col-sm-12 form-group {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'mandatory' : '' }}">
                            {{ Form::label('description', __('Meta Description'), ['class' => 'form-label text-center']) }}

                            <textarea id="edit_meta_description" name="edit_meta_description" class="form-control"
                                oninput="getWordCount('edit_meta_description','edit_meta_description_count','12.9px arial')"
                                {{ system_setting('seo_settings') != '' && system_setting('seo_settings') == 1 ? 'required' : '' }}>{{ $list->meta_description }}</textarea>
                            <h6 id="edit_meta_description_count">0</h6>

                        </div>

                        <div class="card-footer">
                            <div class="col-12 d-flex justify-content-end">

                                {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1']) }}
                            </div>

                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-md-5 col-sm-12">

                <div class="card edit_recent_articles">
                    <div class="card-header add_article_header">
                        Recent Articles
                    </div>
                    <hr>

                    <div class="card-body">

                        <div class="row">
                            {{-- {{ print_r($recent_articles) }} --}}
                            @foreach ($recent_articles as $row)
                                <div class="col-md-12 d-flex recent_articles">

                                    <img class="article_img"
                                        src="{{ $row->image != '' ? $row->image : url('assets/images/bg/Login_BG.jpg') }}"
                                        alt="">
                                    <div class="article_details">
                                        <div class="article_category">
                                            {{ $row->category ? $row->category->category : 'General' }}
                                        </div>
                                        <div class="article_title">
                                            {{ $row->title }}
                                        </div>
                                        <div class="article_description">
                                            @php
                                                echo Str::substr(strip_tags($row->description), 0, 180) . '...';
                                            @endphp
                                        </div>
                                        <div class="article_date">
                                            {{ date('d M Y', strtotime($row->created_at)) }}

                                        </div>

                                    </div>

                                </div>
                                {{--
                        <hr style="border: 1px dashed;"> --}}
                            @endforeach

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            getWordCount("edit_meta_title", "edit_meta_title_count", "19.9px arial");
            getWordCount(
                "edit_meta_description",
                "edit_meta_description_count",
                "12.9px arial"
            );
            $('#edit_image').on('click', function() {

                $('.edit_img').hide();
            });
        });
    </script>
@endsection
