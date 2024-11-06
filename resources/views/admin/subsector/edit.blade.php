 @extends('layouts.master')
@section('content')

    <div class="card border-0 shadow-sm">
        <div class="card-header">
            Edit Subsector
        </div>
        <form action="{{ route('admin.subsector.update', $subsector->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="mb-2">
                    <label for="title">Sector*</label>
                    <select name="sector_id" class="form-control"  @error('sector_id') is-invalid @enderror>
                        <option value="">Select</option>
                        @foreach ($sector as $data )
                            <option value="{{ $data->id }}" {{ ($data->id == $subsector->sector_id) ? 'selected':''}}>{{ $data->title }}</option>
                        @endforeach
                    </select>
                    @error('sector_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="title">Title*</label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', isset($subsector) ? $subsector->title : '') }}">
                    @error('title')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="image">Image*</label>
                    <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror">
                    <img src="{{ asset('images/' . $subsector->image) }}" alt="Image Preview" style="max-width: 80px; margin-top: 10px;">
                    @error('image')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                
               
            </div>
            <div class="card-footer">
                <button class="btn btn-primary me-2" type="submit">Update</button>
                <a class="btn btn-secondary" href="{{ route('admin.subsector.index') }}">
                    Back to list
                </a>
            </div>
        </form>
    </div>
@endsection
 
