@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header card-header-primary">
        <h4 class="card-title">
            {{ $title }}
        </h4>
    </div>

    <div class="card-body">
        <form action="{{ route("dashboard.sliders.store") }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value="{{isset($data)?$data->id:''}}" name="id">

            <div class="form-group mb-3 {{ $errors->has('title') ? 'has-error' : '' }}">
                <label class="form-label" for="name">title</label>
                <input type="text" id="title" name="title" placeholder="Title" class="form-control" value="{{ old('title', isset($data) ? $data->title : '') }}" >
                @if($errors->has('title'))
                    <p class="help-block">
                        {{ $errors->first('title') }}
                    </p>
                @endif
            </div>
            
            <div class="form-group mb-3 {{ $errors->has('url') ? 'has-error' : '' }}">
                <label class="form-label" for="name">url</label>
                <input type="text" id="url" name="url" placeholder="Url" class="form-control" value="{{ old('url', isset($data) ? $data->url : '') }}" >
                @if($errors->has('url'))
                    <p class="help-block">
                        {{ $errors->first('url') }}
                    </p>
                @endif
            </div>
            <div class="form-group mb-3 {{ $errors->has('image') ? 'has-error' : '' }}">
                <label class="form-label" for="name">image*</label>
                <input type="file" id="image" name="image"  class="form-control" value="{{ old('image', isset($data) ? $data->image : '') }}" @if(!isset($data)) required @endif>
                @if($errors->has('image'))
                    <p class="help-block">
                        {{ $errors->first('image') }}
                    </p>
                @endif
            </div>
            @if(isset($data) && !empty($data->image))
            <img src="{{url('uploads/sliders',$data->image)}}" alt="" class="mt-2 mb-2" width="140" height="140">
            <input type="hidden" value="{{$data->image ?? ''}}" name="old_image">
            @endif
           
            <div class="form-group mb-3 {{ $errors->has('status') ? 'has-error' : '' }}">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select class="form-control" id="status" name="status">
                    <option value="1" {{isset($data) && $data->status?'selected':''}}>Publish</option>
                    <option value="0" {{isset($data) && !$data->status?'selected':''}}>Pending</option>
                </select>
                @if($errors->has('status'))
                    <p class="help-block">
                        {{ $errors->first('status') }}
                    </p>
                @endif
            </div>

            <div>
                <input class="btn btn-success" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>
@endsection
