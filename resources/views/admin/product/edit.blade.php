 @extends('layouts.master')
 @section('content')
     <div class="card border-0 shadow-sm">
         <h4 class="card-header">
            <b> Edit Product</b>
         </h4>
         <form action="{{ route('admin.product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
             @csrf
             <div class="card-body">
                 <div class="mb-2">
                     <label for="sector">Category*</label>
                     <select name="sector_id" id="sector" class="form-control @error('sector_id') is-invalid @enderror">
                         <option value="">Select a Category</option>
                         @foreach ($sector as $data)
                             <option value="{{ $data->id }}" {{ $data->id == $product->sector_id ? 'selected' : '' }}>
                                 {{ $data->title }}</option>
                         @endforeach
                     </select>
                     @error('sector_id')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>
                 <div class="mb-2">
                     <label for="subsector">SubCategory*</label>
                     <select name="subsector_id" id="getSubsector"
                         class="form-control @error('subsector_id') is-invalid @enderror">
                         <option value="">Select a SubCategory</option>
                     </select>
                     @error('subsector_id')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>

                 <div class="mb-2">
                     <label for="title">Title*</label>
                     <input type="text" id="title" name="title"
                         class="form-control @error('title') is-invalid @enderror"
                         value="{{ old('title', isset($product) ? $product->title : '') }}">
                     @error('title')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>
                 <div class="mb-2">
                     <label for="title">Meta Tag*</label>
                     <input type="text" id="meta_tag" name="meta_tag"
                         class="form-control @error('meta_tag') is-invalid @enderror "
                         value="{{ old('meta_tag', isset($product) ? $product->meta_tag : '') }}">
                     @error('meta_tag')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>
                 <div class="mb-2">
                     <label for="title">Short Description*</label>
                     <textarea name="description" id="Editor" cols="30" rows="3"
                         class="form-control @error('description') is-invalid @enderror">{{ old('description', isset($product) ? $product->description : '') }}</textarea>
                     @error('description')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>
                 {{-- <div class="mb-2">
                     <label for="dataset">Is high value dataset ?*</label>
                     <div class="form-check">
                         <input class="form-check-input @error('dataset') is-invalid @enderror" type="radio"
                             name="dataset" id="datasetYes" value="1"
                             {{ old('dataset', isset($product) && $product->dataset == 1 ? 'checked' : '') }}>
                         <label class="form-check-label" for="datasetYes">
                             Yes
                         </label>
                     </div>
                     <div class="form-check">
                         <input class="form-check-input @error('dataset') is-invalid @enderror" type="radio"
                             name="dataset" id="datasetNo" value="0"
                             {{ old('dataset', isset($product) && $product->dataset == 0 ? 'checked' : '') }}>
                         <label class="form-check-label" for="datasetNo">
                             No
                         </label>
                     </div>
                     @error('dataset')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div> --}}

                 <div class="mb-2">
                     <label for="image">Image*</label>
                     <input type="file" id="image" name="image"
                         class="form-control @error('image') is-invalid @enderror">
                     <img src="{{ asset('images/' . $product->image) }}" alt="Image Preview"
                         style="max-width: 80px; margin-top: 10px;">
                     @error('image')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>

                 {{-- <div class="mb-2">
                     <label for="title">Product File*(Excel)</label>
                     <input type="file" id="product_file" name="product_file"
                         class="form-control @error('product_file') is-invalid @enderror" accept=".xlsx,.xls">

                     @error('product_file')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div> --}}
                 <div class="mb-2">
                     <label for="title">Price*</label>
                     <input type="number" id="price" name="price"
                         class="form-control @error('price') is-invalid @enderror"
                         value="{{ old('price', isset($product) ? $product->price : '') }}">
                     @error('price')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>
                 <div class="mb-2">
                     <label for="title">Source*</label>
                     <input type="text" id="source" name="source"
                         class="form-control @error('source') is-invalid @enderror"
                         value="{{ old('source', isset($product) ? $product->source : '') }}">
                     @error('source')
                         <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                         </span>
                     @enderror
                 </div>

             </div>
             <div class="card-footer">
                 <button class="btn btn-primary me-2" type="submit">Update</button>
                 <a class="btn btn-secondary" href="{{ route('admin.product.index') }}">
                     Back to list
                 </a>
             </div>
         </form>
     </div>
 @endsection
 @section('scripts')
     <script type="text/javascript" src="{{ asset('ckeditor/ckeditor.js') }}"></script>
     <script>
         CKEDITOR.replace('Editor');
     </script>
 @endsection
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script>
     $(document).ready(function() {
         $(document).on('change', '#sector', function() {
             var sector_id = $(this).val();
             $.ajax({
                 type: "POST",
                 url: '{{ route('admin.getSubsector') }}',
                 data: {
                     sector_id: sector_id,
                     _token: "{{ csrf_token() }}"
                 },
                 dataType: 'json',
                 success: function(res) {
                     $('#getSubsector').empty();
                     $('#getSubsector').html('<option value="">Select</option>');
                     $.each(res, function(key, value) {
                         $('#getSubsector').append('<option value="' + value.id +
                             '">' + value.title + '</option>');
                     });
                 },

             });
         });
     });
 </script>
