@extends('layouts.master')
@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            Customer User
        </div>
        <form action="{{ route('admin.user.customer-store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="name">Name*</label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <input type="hidden" name="password">
                        <div class="mb-2">
                            <label for="email">Email*</label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="mobilenumber">Phone*</label>
                            <input type="number" id="mobilenumber" name="mobilenumber"
                                class="form-control @error('mobilenumber') is-invalid @enderror" required>
                            @error('mobilenumber')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="address">Address*</label>
                            <input type="text" id="address" name="address"
                                class="form-control @error('address') is-invalid @enderror" required>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="gender">Gender*</label>
                            <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror"
                                required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label for="city">City*</label>
                            <input type="text" id="city" name="city"
                                class="form-control @error('city') is-invalid @enderror" required>
                            @error('city')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="state">State*</label>
                            <input type="text" id="state" name="state"
                                class="form-control @error('state') is-invalid @enderror" required>
                            @error('state')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="zip">Zip*</label>
                            <input type="number" id="zip" name="zip"
                                class="form-control @error('zip') is-invalid @enderror" required>
                            @error('zip')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="sponsor_id">Sponsor Id*</label>
                            <select id="sponsor_id" name="sponsor_id"
                                class="form-control @error('sponsor_id') is-invalid @enderror">
                                <option value="">Select a Sponsor</option>
                                @foreach ($userData as $sponsor)
                                    <option value="{{ $sponsor->id }}"
                                        {{ old('sponsor_id') == $sponsor->id ? 'selected' : '' }}>
                                        {{ $sponsor->name }}({{ $sponsor->user_id }} )
                                        <!-- or whatever you want to display -->
                                    </option>
                                @endforeach
                            </select>
                            @error('sponsor_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        {{-- <div class="mb-2">
                        <label for="parent_level">Parent FOR LEVEL*</label>
                        <input type="text" id="parent_level" name="parent_level" class="form-control @error('parent_level') is-invalid @enderror" required>
                        @error('parent_level')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div> --}}
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary me-2" type="submit">Register</button>
                <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">
                    Back to list
                </a>
            </div>
        </form>
    </div>
@endsection
